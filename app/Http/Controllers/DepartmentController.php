<?php

namespace App\Http\Controllers;

use App\Models\Department;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::published()->with(['parent', 'head', 'children' => fn ($q) => $q->published()])->withCount(['teamMembers', 'programmes', 'projects'])->get();
        return view('pages/departments', compact('departments'));
    }

    public function show(Department $department)
    {
        abort_unless($department->active && $department->show_on_website, 404);
        $department->load(['parent', 'head.position', 'teamMembers' => fn ($q) => $q->published()->with('position'), 'positions' => fn ($q) => $q->where('active', true)->orderBy('display_order')->orderBy('title'), 'programmes' => fn ($q) => $q->published(), 'projects' => fn ($q) => $q->published()->with('programme')->latest('published_at')->limit(12)]);
        return view('pages/department', compact('department'));
    }
}
