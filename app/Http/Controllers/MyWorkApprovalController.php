<?php

namespace App\Http\Controllers;

use App\Models\ApprovalRequest;
use App\Services\ApprovalWorkflowService;
use App\Services\DepartmentAccessService;
use Illuminate\Http\Request;

class MyWorkApprovalController extends Controller
{
    public function __invoke(Request $request, ApprovalRequest $approvalRequest, DepartmentAccessService $access, ApprovalWorkflowService $workflow)
    {
        abort_unless($access->canApprove($request->user(), $approvalRequest), 403);

        $data = $request->validate([
            'action' => ['required', 'in:APPROVED,RETURNED,REJECTED'],
            'comment' => ['nullable', 'string', 'max:10000'],
        ]);

        $workflow->act($approvalRequest, $request->user(), $data['action'], $data['comment'] ?? null);

        return back()->with('status', match ($data['action']) {
            'APPROVED' => 'Approval action recorded.',
            'RETURNED' => 'Item returned with your comments.',
            'REJECTED' => 'Item rejected with your comments.',
        });
    }
}
