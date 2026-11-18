# UGHI Department Management

Department Management is an internal organizational module. Departments remain separate from public health programmes and can support many programmes and projects.

## Internal structure

The module provides editable departments, positions and team members. Departments may nest under a parent, positions may report to another position, and a team member may have a department, position and optional CMS user account. A public team profile does not create a login.

The starter seeder creates Secretariat plus eight editable child departments. It does not create staff or department heads.

## Database

The migration creates `departments`, `positions`, `team_members`, `department_programme`, `department_project` and `contact_messages`. Department and position records are soft-deletable. Project associations support an `is_lead` pivot flag. Contact messages are stored with department routing and statuses NEW, ASSIGNED, IN_PROGRESS, RESOLVED and SPAM.

## CMS

Administrators manage Departments, Positions, Team Members and Contact Messages under Organization. Add actions open in modal dialogs; longer department, position and team forms use steps. Department relationships can be assigned from programme and project forms when the user has the corresponding permission.

## Public pages

- `/about/departments` lists active, website-visible departments.
- `/about/departments/{slug}` shows a public department only when it is active and website-visible.
- `/contact` routes submitted messages to active departments that accept public messages.

## Permissions

Department permissions include `department.view`, `department.create`, `department.update`, `department.delete`, `department.restore`, `department.assign_programme`, `department.assign_project` and `department.manage_staff`. Position CRUD permissions are provided through `position.*`; team-member access uses the existing `team.*` permissions. Super Admin and Administrator receive the management grants. Programme and M&E officers receive limited read/assignment access.

## Upgrade

```sh
php artisan migrate
php artisan db:seed --class=DepartmentPermissionSeeder
php artisan db:seed --class=DepartmentSeeder
npm run build
```

The migration is additive and does not replace existing records.
