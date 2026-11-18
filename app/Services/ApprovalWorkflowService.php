<?php

namespace App\Services;

use App\Models\ApprovalAction;
use App\Models\ApprovalRequest;
use App\Models\ApprovalWorkflow;
use App\Models\User;
use App\Notifications\DepartmentWorkflowNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

class ApprovalWorkflowService
{
    public function __construct(private DepartmentAccessService $access) {}

    public function submit(ApprovalWorkflow $workflow, Model $subject, User $actor, ?int $departmentId = null): ApprovalRequest
    {
        $firstStep = $workflow->steps()->orderBy('step_order')->first();
        abort_unless($workflow->active && $firstStep, 422, 'The approval workflow is inactive or has no steps.');
        return DB::transaction(function () use ($workflow, $subject, $actor, $departmentId, $firstStep): ApprovalRequest {
            $request = ApprovalRequest::create(['workflow_id' => $workflow->id, 'approvable_type' => $subject->getMorphClass(), 'approvable_id' => $subject->getKey(), 'department_id' => $departmentId, 'requested_by_user_id' => $actor->id, 'current_step' => $firstStep->step_order, 'status' => 'UNDER_REVIEW', 'submitted_at' => now()]);
            $request->actions()->create(['workflow_step_id' => $firstStep->id, 'user_id' => $actor->id, 'action' => 'SUBMITTED']);
            DepartmentActivity::record($departmentId, $actor->id, 'approval.submitted', $request);
            $this->notifyCurrentApprovers($request);
            return $request;
        });
    }

    public function act(ApprovalRequest $request, User $actor, string $action, ?string $comment = null): ApprovalRequest
    {
        $action = strtoupper($action);
        abort_unless(in_array($action, ['APPROVED', 'RETURNED', 'REJECTED'], true), 422);
        if (in_array($action, ['RETURNED', 'REJECTED'], true) && blank($comment)) throw ValidationException::withMessages(['comment' => 'A comment is required when returning or rejecting an item.']);
        return DB::transaction(function () use ($request, $actor, $action, $comment): ApprovalRequest {
            $locked = ApprovalRequest::with('workflow.steps')->lockForUpdate()->findOrFail($request->id);
            abort_unless(in_array($locked->status, ['SUBMITTED', 'UNDER_REVIEW'], true), 422, 'This request is no longer awaiting review.');
            abort_unless($this->access->canApprove($actor, $locked), 403);
            $step = $locked->workflow->steps->firstWhere('step_order', $locked->current_step);
            abort_unless($step, 422, 'The current workflow step is missing.');
            abort_unless($action !== 'RETURNED' || $step->can_return, 403);
            abort_unless($action !== 'REJECTED' || $step->can_reject, 403);
            ApprovalAction::create(['approval_request_id' => $locked->id, 'workflow_step_id' => $step->id, 'user_id' => $actor->id, 'action' => $action, 'comment' => $comment]);

            $event = 'approval.'.strtolower($action);
            if ($action === 'APPROVED') {
                $next = $locked->workflow->steps->first(fn ($candidate) => $candidate->step_order > $locked->current_step);
                if ($next) {
                    $locked->update(['current_step' => $next->step_order, 'status' => 'UNDER_REVIEW']);
                    $this->notifyCurrentApprovers($locked->fresh(['workflow.steps']));
                } else {
                    $locked->update(['status' => 'APPROVED', 'completed_at' => now()]);
                    $locked->requestedBy->notify(new DepartmentWorkflowNotification('APPROVAL_APPROVED', 'Your submission was approved.'));
                }
            } else {
                $locked->update(['status' => $action, 'completed_at' => now()]);
                $locked->requestedBy->notify(new DepartmentWorkflowNotification('APPROVAL_'. $action, "Your submission was {$action}."));
            }
            DepartmentActivity::record($locked->department_id, $actor->id, $event, $locked, ['comment' => $comment]);
            return $locked->fresh(['workflow', 'actions']);
        });
    }

    private function notifyCurrentApprovers(ApprovalRequest $request): void
    {
        $step = $request->workflow->steps->firstWhere('step_order', $request->current_step);
        if (! $step) return;
        $users = collect();
        if ($request->department && in_array($step->approver_type, ['DEPARTMENT_MANAGER', 'DEPARTMENT_HEAD'], true)) {
            $role = $step->required_leadership_role ?: ($step->approver_type === 'DEPARTMENT_HEAD' ? 'HEAD' : 'MANAGER');
            $users = $request->department->activeLeadership()->where('leadership_role', $role)->where('can_approve', true)->with('teamMember.user')->get()->pluck('teamMember.user')->filter();
        } elseif ($step->approver_type === 'EXECUTIVE_SECRETARY' || $step->approver_type === 'PERMISSION') {
            $permission = $step->required_permission ?: 'organization.full_access';
            $users = User::permission($permission)->get();
        } elseif ($step->approver_type === 'SPECIFIC_USER' && $step->specific_user_id) {
            $users = User::whereKey($step->specific_user_id)->get();
        }
        $users = $users->filter(fn (User $user) => $this->access->canApprove($user, $request));
        Notification::send($users->unique('id'), new DepartmentWorkflowNotification('APPROVAL_REQUIRED', "An item requires your review: {$request->workflow->name}", route('filament.admin.resources.approval-requests.index')));
    }
}
