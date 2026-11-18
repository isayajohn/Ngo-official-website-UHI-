<?php

namespace App\Http\Controllers;

use App\Models\DepartmentTask;
use App\Models\DepartmentTaskAttachment;
use App\Services\DepartmentTaskService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DepartmentTaskController extends Controller
{
    public function complete(Request $request, DepartmentTask $task, DepartmentTaskService $service)
    {
        $this->authorize('view', $task);
        $service->complete($request->user(), $task, $request->validate(['completion_note' => ['nullable', 'string', 'max:5000']])['completion_note'] ?? null);
        return back()->with('status', 'Task marked complete.');
    }

    public function comment(Request $request, DepartmentTask $task, DepartmentTaskService $service)
    {
        $this->authorize('view', $task);
        $data = $request->validate(['comment' => ['required', 'string', 'max:10000']]);
        $service->comment($request->user(), $task, $data['comment']);
        return back()->with('status', 'Comment added.');
    }

    public function attachment(Request $request, DepartmentTaskAttachment $attachment)
    {
        $this->authorize('view', $attachment->task);
        abort_unless(Storage::disk($attachment->disk)->exists($attachment->path), 404);
        return Storage::disk($attachment->disk)->download($attachment->path, $attachment->original_name);
    }
}
