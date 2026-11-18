<?php

namespace App\Services;

use App\Models\DepartmentTask;
use App\Models\DepartmentTaskAttachment;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;

class DepartmentTaskAttachmentService
{
    private const ALLOWED = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'image/png', 'image/jpeg'];

    public function store(DepartmentTask $task, array $paths, int $userId): void
    {
        foreach ($paths as $path) {
            if (! is_string($path) || ! str_starts_with($path, 'private/department-tasks/') || ! Storage::disk('local')->exists($path)) continue;
            $file = new File(Storage::disk('local')->path($path));
            $mime = $file->getMimeType();
            if (! in_array($mime, self::ALLOWED, true) || $file->getSize() > 10 * 1024 * 1024) {
                Storage::disk('local')->delete($path);
                continue;
            }
            DepartmentTaskAttachment::firstOrCreate(['task_id' => $task->id, 'path' => $path], ['uploaded_by_user_id' => $userId, 'disk' => 'local', 'original_name' => basename($path), 'mime_type' => $mime, 'size' => $file->getSize()]);
        }
    }
}
