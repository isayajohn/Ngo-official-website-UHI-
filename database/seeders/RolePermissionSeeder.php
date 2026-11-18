<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $permissions = ['admin.access', 'homepage.manage', 'settings.manage', 'user.manage', 'user.view', 'user.create', 'user.update', 'user.disable', 'user.activate', 'user.assign_role', 'user.assign_department', 'user.reset_password', 'user.view_activity', 'user.manage_security', 'user.sessions.view', 'user.sessions.revoke', 'user.invite', 'user.invitation.view', 'user.invitation.resend', 'user.invitation.revoke', 'user.password_reset.send', 'role.manage', 'member.approve', 'safeguarding.view', 'safeguarding.update', 'news.publish', 'organization.full_access', 'department.assign_programme', 'department.assign_project', 'department.manage_staff', 'department.manage', 'department.manage_leadership', 'department.assign_task', 'department.view_reports', 'department.manage_projects', 'department.manage_programmes', 'department.approve', 'department.view_budget', 'department.manage_documents', 'department.manage_messages', 'department.view_activity'];
        $permissions = [...$permissions,
            'report.view', 'report.create', 'report.update', 'report.submit', 'report.review', 'report.approve', 'report.return', 'report.reject', 'report.export', 'report.manage_types',
            'performance.view', 'performance.view_organization', 'performance.view_department', 'performance.view_programme', 'performance.view_project', 'performance.manage_kpi', 'performance.manage_target', 'performance.enter_result', 'performance.verify_result', 'performance.manage_scale', 'performance.export',
            'staff_performance.view', 'staff_performance.review', 'staff_performance.approve',
            'mel.dashboard.view', 'mel.results_framework.view', 'mel.results_framework.manage', 'mel.indicator.view', 'mel.indicator.create', 'mel.indicator.update', 'mel.indicator.delete', 'mel.target.view', 'mel.target.manage'];
        foreach (['programme', 'project', 'department', 'position', 'news', 'event', 'story', 'publication', 'gallery', 'team', 'partner', 'member', 'volunteer', 'membership', 'contact', 'partnership', 'newsletter', 'donation', 'campaign', 'impact', 'location', 'page', 'media'] as $module) {
            foreach (['view', 'create', 'update', 'delete', 'restore'] as $action) {
                $permissions[] = "$module.$action";
            }
        }
        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }
        $roles = [
            // Technical administration does not automatically grant access to
            // confidential staff appraisal records.
            'Super Admin' => array_values(array_filter($permissions, fn ($permission) => ! str_starts_with($permission, 'staff_performance.'))),
            'Administrator' => array_values(array_filter($permissions, fn ($permission) => ! str_starts_with($permission, 'safeguarding.') && ! str_starts_with($permission, 'staff_performance.') && ! str_starts_with($permission, 'mel.') && ! in_array($permission, ['performance.manage_kpi', 'performance.manage_target', 'performance.enter_result', 'performance.verify_result'], true) && $permission !== 'role.manage')),
            'Executive Secretary' => ['admin.access', 'organization.full_access', 'department.view', 'department.view_reports', 'department.approve', 'project.view', 'programme.view', 'performance.view', 'performance.view_organization', 'mel.dashboard.view', 'mel.results_framework.view', 'mel.indicator.view', 'mel.target.view', 'report.view', 'report.export'],
            'Content Manager' => ['admin.access', 'homepage.manage', 'programme.view', 'programme.create', 'programme.update', 'programme.delete', 'programme.restore', 'page.view', 'page.create', 'page.update', 'news.view', 'news.create', 'news.update', 'news.delete', 'event.view', 'event.create', 'event.update', 'event.delete', 'publication.view', 'publication.create', 'publication.update', 'publication.delete', 'gallery.view', 'gallery.create', 'gallery.update', 'gallery.delete', 'partner.view', 'partner.create', 'partner.update', 'media.view', 'media.create', 'media.update'],
            'Programme Officer' => ['admin.access', 'programme.view', 'programme.update', 'project.view', 'project.create', 'project.update', 'department.view', 'position.view', 'department.assign_programme', 'department.assign_project', 'department.assign_task', 'performance.view', 'performance.view_programme', 'performance.view_project', 'mel.dashboard.view', 'mel.results_framework.view', 'mel.indicator.view', 'mel.target.view', 'report.view', 'report.create', 'report.submit'],
            'M&E Officer' => ['admin.access', 'impact.view', 'impact.create', 'impact.update', 'project.view', 'department.view', 'performance.view', 'performance.view_department', 'performance.view_programme', 'performance.view_project', 'performance.manage_kpi', 'performance.manage_target', 'performance.enter_result', 'performance.verify_result', 'performance.export', 'mel.dashboard.view', 'mel.results_framework.view', 'mel.results_framework.manage', 'mel.indicator.view', 'mel.indicator.create', 'mel.indicator.update', 'mel.target.view', 'mel.target.manage', 'report.view', 'report.create', 'report.submit', 'report.review', 'report.export'],
            'Communications Officer' => ['admin.access', 'news.view', 'news.create', 'news.update', 'news.publish', 'event.view', 'event.create', 'event.update', 'publication.view', 'publication.create', 'publication.update', 'gallery.view', 'gallery.create', 'gallery.update', 'partnership.view', 'partnership.update', 'story.view', 'story.create', 'story.update', 'page.view', 'page.update', 'partner.view', 'partner.create', 'partner.update', 'media.view', 'media.create', 'media.update'],
            'Membership Officer' => ['admin.access', 'member.view', 'member.update', 'member.approve', 'membership.view', 'membership.update', 'volunteer.view', 'volunteer.update'],
            'Safeguarding Officer' => ['admin.access', 'safeguarding.view', 'safeguarding.update'],
        ];
        foreach ($roles as $name => $grants) {
            $role = Role::firstOrCreate(['name' => $name, 'guard_name' => 'web']);

            // Set defaults when the role is first created. Once administrators start
            // managing permissions in the CMS, rerunning seeders must preserve edits.
            if ($role->wasRecentlyCreated) {
                $role->syncPermissions($grants);
            }

            if (in_array($name, ['Super Admin', 'Administrator'], true)) {
                $role->givePermissionTo(['user.sessions.view', 'user.sessions.revoke']);
            }
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
