<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $secretariat = Department::withTrashed()->firstOrCreate(['code' => 'SEC'], [
            'name' => 'Secretariat', 'slug' => 'secretariat', 'short_description' => 'The coordinating administrative office of UGHI.', 'display_order' => 0,
        ]);
        if ($secretariat->trashed()) $secretariat->restore();

        $departments = [
            ['EXEC', 'Executive Office'], ['PROG', 'Programmes'], ['MEL', 'Monitoring, Evaluation & Learning'],
            ['RKM', 'Research & Knowledge Management'], ['FIN', 'Finance & Administration'], ['HR', 'Human Resources'],
            ['COMMS', 'Communications & Partnerships'], ['ICT', 'ICT / Digital Systems'],
        ];
        foreach ($departments as $order => [$code, $name]) {
            $department = Department::withTrashed()->firstOrCreate(['code' => $code], [
                'name' => $name, 'slug' => str($name)->slug(), 'parent_id' => $secretariat->id, 'display_order' => $order + 1,
            ]);
            if ($department->trashed()) $department->restore();
            if ($department->parent_id !== $secretariat->id) $department->update(['parent_id' => $secretariat->id]);
        }
    }
}
