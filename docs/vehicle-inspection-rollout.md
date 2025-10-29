# Vehicle Inspection Rollout

## Environment Snapshot
- App running via Laravel dev server (`php artisan serve`) + Vite (`npm run dev`)
- MySQL active with credentials: password `SamilChaladan123!`

## Implementation Approach
- Core data model already supports the workflow: branches group users and vehicles (`app/Http/Controllers/BranchController.php:14` orchestrates CRUD), employees live on `users` with branch and role metadata, vehicle ownership is tracked through assignments and inspections (`app/Modules/VehicleManagement/Models/Vehicle.php:27`, `app/Modules/InspectionManagement/Models/Inspection.php:29`).
- Finalise the Branch admin so operations can stand up the structure, using the existing controller/views for listing, toggling status, and drill-ins (`app/Http/Controllers/BranchController.php:36`, `:119`); seed a minimal set of branches for testing.
- Replace the stubbed employee screens in `TeamController` with real queries against `User`, filtering by branch/status/role and surfacing vehicle assignment context (`app/Modules/TeamManagement/Controllers/TeamController.php:13`); wire create/edit forms to validation, role assignment, and branch selection.
- Vehicles are already managed end-to-end via `VehicleController` (index, create/edit, assign/return, QR code) so focus on tying assignments to the employee UI and surfacing quick links for daily inspections (`app/Modules/VehicleManagement/Controllers/VehicleController.php:18`, `:148`).
- Enable drivers to submit their inspection reports through the dedicated flow (`app/Modules/InspectionManagement/Controllers/DriverVehicleInspectionController.php:22`), which pulls the active assignment, validates checklist input, and persists via `InspectionService` (`app/Modules/InspectionManagement/Services/InspectionService.php:59`); extend the Blade view to be mobile friendly for on-site usage.

## Sequenced Plan
- Harden data: confirm migrations/factories for `branches`, `users`, `vehicles`, `vehicle_assignments`, `inspections`; add seeders for sample branches/employees/vehicles.
- Branch ops: finish Blade screens, policies, and tests to ensure only admins manage branches.
- Employee module: build listing, detail, and forms; integrate Spatie roles and branch scoping; add tests around branch isolation.
- Vehicle module: surface assignments and inspection stats on show screens; expose driver inspection entry points (QR code, dashboard prompt).
- Inspection intake: polish driver checklist view, add notifications/workflows for supervisors, write feature tests covering happy path and validation failures.
- Reporting: include inspection summaries on branch and vehicle dashboards so leadership can monitor compliance from day one.

## Next Steps
- Seed a demo scenario (one branch, a handful of drivers/vehicles) and walk through the inspection submission end-to-end (`php artisan migrate --seed`, `php artisan serve`, `npm run dev`), adjusting UX and validation feedback from that dry run.