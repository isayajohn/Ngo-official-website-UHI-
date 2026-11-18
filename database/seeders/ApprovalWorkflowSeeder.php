<?php

namespace Database\Seeders;

use App\Models\ApprovalWorkflow;
use Illuminate\Database\Seeder;

class ApprovalWorkflowSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            ['name' => 'Project Proposal (Default)', 'code' => 'PROJECT_PROPOSAL', 'module' => 'project', 'description' => 'Default review route: department manager, department head, Executive Secretary.', 'steps' => [['Programme Manager', 'DEPARTMENT_MANAGER', 'MANAGER'], ['Department Head', 'DEPARTMENT_HEAD', 'HEAD'], ['Executive Secretary', 'EXECUTIVE_SECRETARY', null]]],
            ['name' => 'Department Report (Default)', 'code' => 'DEPARTMENT_REPORT', 'module' => 'department_report', 'description' => 'Default review route for department reports.', 'steps' => [['Department Head', 'DEPARTMENT_HEAD', 'HEAD'], ['Executive Secretary', 'EXECUTIVE_SECRETARY', null]]],
            ['name' => 'Procurement Request (Default)', 'code' => 'PROCUREMENT_REQUEST', 'module' => 'procurement', 'description' => 'Default review route for procurement requests.', 'steps' => [['Department Head', 'DEPARTMENT_HEAD', 'HEAD'], ['Executive Secretary', 'EXECUTIVE_SECRETARY', null]]],
            ['name' => 'Publication (Default)', 'code' => 'PUBLICATION', 'module' => 'publication', 'description' => 'Default publication review route.', 'steps' => [['Communications Reviewer', 'PERMISSION', null]]],
            ['name' => 'Programme Activity (Default)', 'code' => 'PROGRAMME_ACTIVITY', 'module' => 'programme_activity', 'description' => 'Default programme activity review route.', 'steps' => [['Department Manager', 'DEPARTMENT_MANAGER', 'MANAGER'], ['Department Head', 'DEPARTMENT_HEAD', 'HEAD']]],
        ];

        foreach ($defaults as $definition) {
            $steps = $definition['steps']; unset($definition['steps']);
            $workflow = ApprovalWorkflow::firstOrCreate(['code' => $definition['code']], $definition);
            if ($workflow->wasRecentlyCreated) {
                foreach ($steps as $index => [$name, $type, $role]) {
                    $workflow->steps()->create(['step_order' => $index + 1, 'name' => $name, 'approver_type' => $type, 'required_leadership_role' => $role, 'required_permission' => $type === 'PERMISSION' ? 'news.publish' : null, 'department_scoped' => $type !== 'EXECUTIVE_SECRETARY']);
                }
            }
        }
    }
}
