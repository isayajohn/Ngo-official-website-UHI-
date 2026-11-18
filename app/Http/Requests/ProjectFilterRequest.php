<?php

namespace App\Http\Requests;

use App\Enums\ProjectStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProjectFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['q' => ['nullable', 'string', 'max:120'], 'programme' => ['nullable', 'string', 'max:255', Rule::exists('programmes', 'slug')], 'status' => ['nullable', Rule::enum(ProjectStatus::class)], 'region' => ['nullable', 'string', 'max:255', Rule::exists('regions', 'slug')], 'year' => ['nullable', 'integer', 'between:1900,'.(now()->year + 10)]];
    }
}
