# Phase 3: Programme and project operations

This phase extends existing project records without replacing public project pages or programme relationships.

## Project records

- Add optional project code and project manager fields.
- Keep the existing `project_budget` field as the current project budget estimate.
- Add one lead department and zero or more supporting departments on the existing `department_project` pivot.
- Department assignment is restricted to the user's active department memberships/leadership; `organization.full_access` permits organization-wide assignment.
- The lead department controls operational edits. Supporting departments can view project details but cannot mutate activities, milestones or risks.
- Existing `SUSPENDED` status values remain valid. `ON_HOLD` and `CANCELLED` are added to the status options.

## Project operations

The project record page includes management tabs for:

- Activities, with dates, department, responsible team member, participants, status and completion percentage.
- Milestones, with due date, completion date, responsible user and status.
- Risks, with likelihood and impact rated 1–5, mitigation, owner and status. Risk level is calculated on save from the likelihood-impact score.

Writes are checked on the server against `DepartmentAccessService::canManageProject`, including when a user submits a forged Filament request. Activity `budget_line_id` and project `grant_id` are nullable references reserved for later finance work; no finance tables are introduced in this phase.

## Upgrade

Run the additive migration and clear cached routes/permissions as usual:

```sh
php artisan migrate
php artisan permission:cache-reset
```

The migration creates `project_activities`, `project_milestones`, and `project_risks`, and adds nullable project columns. It does not modify existing department assignments, statuses or project content.

## Next phase

Phase 4 is Monitoring, Evaluation & Learning: indicators, targets, achievements, reporting periods, evidence and privacy-conscious beneficiary aggregation.
