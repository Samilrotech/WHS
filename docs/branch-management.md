## Branch Management Module

The Branch Management area is now fully implemented and ready for end‑to‑end use. The feature set includes:

- Location dashboard with aggregate metrics, keyword search (`name`, `code`, `city`, `state`, `address`, `postcode`) and status filters.
- CRUD flows (create, edit, soft delete) with validation aligned to the underlying schema.
- Status toggling for quick activation/deactivation.
- Detailed branch profile page showing contact data, activity chips and the roster of assigned employees.

### Data Model

| Field         | Type    | Notes                                 |
|---------------|---------|---------------------------------------|
| `name`        | string  | Required, unique by name              |
| `code`        | string  | Required, unique, max 50 chars        |
| `address`     | string  | Required                               |
| `city`        | string  | Required                               |
| `state`       | string  | Required, Australian state/territory  |
| `postcode`    | string  | Required                               |
| `phone`       | string  | Optional                               |
| `email`       | string  | Optional, valid email                 |
| `manager_name`| string  | Optional                               |
| `is_active`   | boolean | Defaults to `true`                    |

### Seeder

`InitialDataSeeder` now creates three flagship branches (Sydney, Brisbane, Perth) so the interface is populated immediately after `php artisan db:seed`.

### Automated Tests

`tests/Feature/BranchManagementTest.php` covers:

- Index filtering
- Create / update operations
- Delete constraints & soft delete behaviour
- Status toggling

Run the suite with:

```bash
php artisan test --filter=BranchManagementTest
```
