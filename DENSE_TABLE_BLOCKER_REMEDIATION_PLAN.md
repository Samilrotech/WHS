# Dense Table Blocker Remediation Plan

**Document Purpose**: Translate audit findings into concrete remediation actions to unblock the Dense Table UI/UX Rollout Plan

**Audit Reference**: `DENSE_TABLE_PLAN_CROSS_AUDIT.md` (9,585 lines)
**Implementation Plan**: `DENSE_TABLE_ROLLOUT_PLAN.md` (1,577 lines)
**Audit Score**: 94/100 (PASS WITH MINOR RECOMMENDATIONS)
**Status**: 5 blocking issues require remediation before Phase 1 implementation

---

## Executive Overview

### Audit Summary
The Dense Table Rollout Plan audit identified **5 critical blocking issues** that must be resolved before Phase 1 implementation can proceed:

1. **SQL Injection Risk** (CRITICAL) - Unvalidated sort parameter in TeamController
2. **Export Security Gap** (CRITICAL) - No GDPR/PII compliance for employee data exports
3. **Offline PWA Sync Conflicts** (HIGH) - Missing conflict detection and resolution strategy
4. **Feature Flag Strategy** (HIGH) - Laravel Pennant vs custom implementation undecided
5. **Incomplete Blade Components** (MEDIUM) - `<x-whs.table-cell>` and `<x-whs.side-drawer>` templates missing bodies

### Impact Assessment
- **Pre-Phase 1 Work Required**: 10 developer-days (parallelizable to 5 calendar days with 2 developers)
- **Timeline Extension**: 6 weeks → 7 weeks (1 week added for Pre-Phase 1)
- **Readiness Improvement**: Current 84/100 → Post-remediation 94/100 → Production-ready 98/100

### Resource Allocation
| Role | Pre-Phase Effort | Phase 1-6 Effort | Total |
|------|------------------|-------------------|-------|
| Backend Team Lead | 4 days | 8 days | 12 days |
| Frontend Specialist | 2.5 days | 12 days | 14.5 days |
| Security Engineer | 2 days | 2 days | 4 days |
| QA Engineer | 1.5 days | 8 days | 9.5 days |

---

## Blocker #1: SQL Injection Vulnerability (CRITICAL)

### Context
**Severity**: CRITICAL
**Risk Score**: 9/10
**Discovery**: Audit Section 2.4 "Backend/Data Strategy Analysis"
**Affected Component**: TeamController::index() pagination and sorting

**Current State**:
- File: `app/Modules/TeamManagement/Controllers/TeamController.php`
- Line 82: `$users = $query->orderBy('name')->paginate(12)->withQueryString();`
- Current implementation: Hardcoded `'name'` column, safe but inflexible
- Proposed change (from rollout plan): Add `$request->input('sort')` without validation

**Vulnerability**:
```php
// PROPOSED (UNSAFE):
$sortColumn = $request->input('sort', 'name');
$users = $query->orderBy($sortColumn)->paginate(50)->withQueryString();

// ATTACK VECTOR:
// /team?sort=1;DROP TABLE users;--
// /team?sort=(SELECT password FROM users LIMIT 1)
```

**Root Cause**: No whitelist validation for user-controlled column names in `orderBy()` clause.

### Proposed Solution

**1. Whitelist Validation Approach**

Add column whitelist validation immediately after filter extraction (line 49):

```php
// app/Modules/TeamManagement/Controllers/TeamController.php
// ADD AFTER LINE 49:

// Define allowed sort columns (whitelist)
$allowedSortColumns = [
    'name',
    'email',
    'phone',
    'employee_id',
    'employment_status',
    'created_at',
    'updated_at',
];

// Get sort parameters with validation
$sortColumn = $request->input('sort', 'name');
$sortDirection = strtolower($request->input('direction', 'asc'));

// Validate sort column against whitelist
if (!in_array($sortColumn, $allowedSortColumns, true)) {
    $sortColumn = 'name'; // Fallback to safe default
}

// Validate sort direction (asc/desc only)
if (!in_array($sortDirection, ['asc', 'desc'], true)) {
    $sortDirection = 'asc'; // Fallback to safe default
}
```

**2. Update Pagination Logic (Line 82)**

Replace current hardcoded sort:

```php
// REPLACE LINE 82:
// OLD: $users = $query->orderBy('name')->paginate(12)->withQueryString();

// NEW:
$users = $query
    ->orderBy($sortColumn, $sortDirection)
    ->paginate(50) // Increase from 12 to 50 per rollout plan
    ->withQueryString();
```

**3. Add Rate Limiting Middleware**

Protect against DoS attacks on sort/filter endpoints:

```php
// routes/web.php or route definition file
// ADD rate limiting to team management routes:

Route::middleware(['auth', 'throttle:60,1'])->group(function () {
    Route::get('/team', [TeamController::class, 'index'])->name('team.index');
    // ... other team routes
});
```

### Affected Files

| File | Lines | Change Type | Estimated Effort |
|------|-------|-------------|------------------|
| `app/Modules/TeamManagement/Controllers/TeamController.php` | 49-82 | Modify (add validation + update pagination) | 2 hours |
| `routes/web.php` or module routes | Team management section | Add (rate limiting) | 30 minutes |
| `tests/Feature/TeamControllerTest.php` | New file | Create (unit tests) | 3 hours |
| `tests/Feature/TeamControllerSecurityTest.php` | New file | Create (security tests) | 2 hours |

**Total Effort**: 1 day (7.5 hours)

### Verification Checklist

**Unit Tests Required**:
1. ✅ Valid sort column with ascending direction → Success
2. ✅ Valid sort column with descending direction → Success
3. ❌ Invalid sort column (e.g., `password`) → Falls back to `name`
4. ❌ SQL injection attempt (e.g., `1;DROP TABLE`) → Falls back to `name`
5. ❌ Empty sort parameter → Falls back to `name`
6. ❌ Invalid direction (e.g., `random`) → Falls back to `asc`
7. ✅ Pagination 50 per page → Returns 50 records
8. ✅ Rate limiting → Returns 429 after 60 requests in 1 minute

**PHPUnit Test Command**:
```bash
php artisan test --filter TeamControllerTest
php artisan test --filter TeamControllerSecurityTest
```

**Manual Security Validation**:
```bash
# Test SQL injection protection:
curl "http://whs.test/team?sort=1%3BDROP+TABLE+users%3B--"
# Expected: Returns results sorted by 'name' (fallback), no SQL error

# Test whitelist enforcement:
curl "http://whs.test/team?sort=password"
# Expected: Returns results sorted by 'name' (fallback), not 'password'

# Test rate limiting:
for i in {1..65}; do curl "http://whs.test/team?sort=name&direction=asc"; done
# Expected: First 60 succeed, requests 61-65 return HTTP 429
```

### Owner & Timeline
- **Owner**: Backend Team Lead
- **Estimated Effort**: 1 day
- **Dependencies**: None (blocking for Phase 1)
- **Sequencing**: Complete before Phase 1 Day 1

---

## Blocker #2: Export Security Gap (CRITICAL)

### Context
**Severity**: CRITICAL
**Risk Score**: 9/10
**Discovery**: Audit Section 2.4 "Backend/Data Strategy Analysis" + Regulatory Compliance
**Affected Component**: Employee data export functionality (not yet implemented)

**Current State**:
- **No export functionality exists** in `app/Modules/TeamManagement/Controllers/TeamController.php`
- Rollout plan proposes adding CSV/Excel export (Section 4.3 "DenseTableManager")
- No mention of GDPR, PII protection, audit logging, or access controls

**Regulatory Requirements**:
- **GDPR Article 30**: Data processing must be logged (who exported what, when)
- **GDPR Article 32**: Personal data must be protected during export (encryption, access control)
- **GDPR Article 5**: Data minimization (only export necessary fields)
- **PII Fields in Employee Data**: name, email, phone, address, date_of_birth, emergency_contact

**Risk Vectors**:
1. Unauthorized export of sensitive employee data
2. No audit trail for compliance investigations
3. Exported files contain unencrypted PII
4. No role-based access control (RBAC) for export feature

### Proposed Solution

**1. Role-Based Access Control (RBAC)**

Create export permission and assign to authorized roles:

```php
// database/seeders/PermissionSeeder.php
// ADD to permissions array:

'team_export' => [
    'name' => 'team.export',
    'display_name' => 'Export Employee Data',
    'description' => 'Export employee list to CSV/Excel with PII data',
    'category' => 'Team Management',
    'risk_level' => 'high', // Requires audit logging
],

// Assign to roles:
'hr_manager' => ['team.index', 'team.show', 'team.export'],
'admin' => ['team.*'],
// Note: Regular users and supervisors do NOT get team.export
```

**2. Export Controller Method with RBAC Check**

Add export method to TeamController:

```php
// app/Modules/TeamManagement/Controllers/TeamController.php
// ADD new method:

/**
 * Export employees to CSV with GDPR compliance
 *
 * @param Request $request
 * @return \Symfony\Component\HttpFoundation\StreamedResponse
 * @throws \Illuminate\Auth\Access\AuthorizationException
 */
public function export(Request $request)
{
    // RBAC check (throws 403 if unauthorized)
    $this->authorize('team.export');

    // Validate export request
    $validated = $request->validate([
        'format' => 'required|in:csv,xlsx',
        'fields' => 'array', // Optional: allow field selection
        'filters' => 'array', // Optional: apply same filters as index
    ]);

    // Log export for GDPR audit trail
    activity()
        ->causedBy(auth()->user())
        ->withProperties([
            'format' => $validated['format'],
            'fields' => $validated['fields'] ?? 'all',
            'filters' => $validated['filters'] ?? [],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ])
        ->log('team_export_initiated');

    // Build query with same filters as index()
    $query = $this->buildFilteredQuery($request);

    // Execute export
    return $this->generateExport($query, $validated['format'], $validated['fields'] ?? []);
}

/**
 * Generate export file with data minimization
 */
private function generateExport($query, string $format, array $fields)
{
    // Define exportable fields with PII classification
    $exportableFields = [
        'employee_id' => ['label' => 'Employee ID', 'pii' => false],
        'name' => ['label' => 'Full Name', 'pii' => true],
        'email' => ['label' => 'Email', 'pii' => true],
        'phone' => ['label' => 'Phone', 'pii' => true],
        'position' => ['label' => 'Position', 'pii' => false],
        'branch' => ['label' => 'Branch', 'pii' => false],
        'employment_status' => ['label' => 'Status', 'pii' => false],
        'start_date' => ['label' => 'Start Date', 'pii' => false],
    ];

    // If no specific fields requested, export all (with warning)
    if (empty($fields)) {
        $fields = array_keys($exportableFields);

        // Log PII export warning
        activity()
            ->causedBy(auth()->user())
            ->log('team_export_all_pii_warning');
    }

    // Validate requested fields
    $fields = array_intersect($fields, array_keys($exportableFields));

    // Generate export based on format
    if ($format === 'csv') {
        return $this->exportCsv($query, $fields, $exportableFields);
    } else {
        return $this->exportExcel($query, $fields, $exportableFields);
    }
}

/**
 * Export to CSV with streaming (memory-efficient)
 */
private function exportCsv($query, array $fields, array $exportableFields)
{
    $filename = 'employees_' . now()->format('Y-m-d_His') . '.csv';

    $headers = [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => "attachment; filename=\"$filename\"",
        'Cache-Control' => 'no-store, no-cache',
        'X-Content-Type-Options' => 'nosniff',
    ];

    return response()->stream(function () use ($query, $fields, $exportableFields) {
        $handle = fopen('php://output', 'w');

        // Write CSV header row
        $headerRow = array_map(fn($field) => $exportableFields[$field]['label'], $fields);
        fputcsv($handle, $headerRow);

        // Stream data rows (memory-efficient)
        $query->chunk(100, function ($users) use ($handle, $fields) {
            foreach ($users as $user) {
                $row = [];
                foreach ($fields as $field) {
                    $row[] = $this->formatFieldValue($user, $field);
                }
                fputcsv($handle, $row);
            }
        });

        fclose($handle);
    }, 200, $headers);
}
```

**3. Audit Logging Configuration**

Install and configure Laravel Activitylog package:

```bash
composer require spatie/laravel-activitylog
php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider" --tag="activitylog-migrations"
php artisan migrate
```

Configure activity log:

```php
// config/activitylog.php
return [
    'enabled' => env('ACTIVITY_LOG_ENABLED', true),
    'table_name' => 'activity_log',
    'delete_records_older_than_days' => 365 * 7, // 7 years retention for GDPR
    'subject_returns_soft_deleted_models' => true,
    'log_name' => 'default',
];
```

**4. Export Route with Middleware**

```php
// routes/web.php or module routes
// ADD export route with rate limiting:

Route::middleware(['auth', 'can:team.export', 'throttle:10,1'])->group(function () {
    Route::post('/team/export', [TeamController::class, 'export'])
        ->name('team.export');
});
```

**5. Frontend Export Button (RBAC-aware)**

```blade
{{-- resources/views/content/TeamManagement/Index.blade.php --}}
{{-- ADD export button with permission check: --}}

@can('team.export')
<div class="mb-3">
    <button
        type="button"
        class="btn btn-outline-primary"
        onclick="openExportModal()"
    >
        <i class="bx bx-download"></i>
        Export Employees
    </button>
</div>

<div class="modal fade" id="exportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Export Employee Data</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('team.export') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bx bx-info-circle"></i>
                        <strong>GDPR Notice:</strong> This export contains personal data.
                        All exports are logged for audit purposes.
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Format</label>
                        <select name="format" class="form-select" required>
                            <option value="csv">CSV</option>
                            <option value="xlsx">Excel (XLSX)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Fields to Export</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="fields[]" value="employee_id" checked>
                            <label class="form-check-label">Employee ID</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="fields[]" value="name" checked>
                            <label class="form-check-label">Name <span class="badge bg-warning">PII</span></label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="fields[]" value="email" checked>
                            <label class="form-check-label">Email <span class="badge bg-warning">PII</span></label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="fields[]" value="phone">
                            <label class="form-check-label">Phone <span class="badge bg-warning">PII</span></label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="fields[]" value="position" checked>
                            <label class="form-check-label">Position</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="fields[]" value="branch" checked>
                            <label class="form-check-label">Branch</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="fields[]" value="employment_status" checked>
                            <label class="form-check-label">Status</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-download"></i>
                        Export
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openExportModal() {
    new bootstrap.Modal(document.getElementById('exportModal')).show();
}
</script>
@endcan
```

### Affected Files

| File | Change Type | Estimated Effort |
|------|-------------|------------------|
| `composer.json` | Add `spatie/laravel-activitylog` dependency | 15 min |
| `database/seeders/PermissionSeeder.php` | Add `team.export` permission | 30 min |
| `app/Modules/TeamManagement/Controllers/TeamController.php` | Add export methods (350+ lines) | 4 hours |
| `routes/web.php` or module routes | Add export route with middleware | 15 min |
| `resources/views/content/TeamManagement/Index.blade.php` | Add export button with RBAC check | 1 hour |
| `tests/Feature/TeamExportTest.php` | Create export functionality tests | 3 hours |
| `tests/Feature/TeamExportSecurityTest.php` | Create RBAC and GDPR tests | 2 hours |
| `config/activitylog.php` | Configure audit logging | 30 min |

**Total Effort**: 2 days (11.5 hours)

### Verification Checklist

**Functional Tests**:
1. ✅ Authorized user (HR Manager) can export → Success
2. ❌ Unauthorized user (regular employee) cannot export → HTTP 403
3. ✅ CSV export with selected fields → Contains only requested columns
4. ✅ CSV export with all fields → Contains all columns + PII warning logged
5. ✅ XLSX export → Valid Excel file generated
6. ✅ Export respects current filters (search, branch, status) → Filtered data only
7. ✅ Large dataset export (500+ users) → Streams without memory error
8. ✅ Rate limiting → 10 exports per minute maximum

**GDPR Compliance Tests**:
1. ✅ Audit log entry created for each export → `activity_log` table populated
2. ✅ Audit log contains: user ID, timestamp, IP, user agent, format, fields → All metadata present
3. ✅ PII warning logged when exporting all fields → `team_export_all_pii_warning` event
4. ✅ No sensitive fields in activity log → No PII values in log, only field names
5. ✅ Activity log retention configured (7 years) → `delete_records_older_than_days = 2555`

**Security Tests**:
1. ❌ Attempt export without `team.export` permission → HTTP 403
2. ❌ Attempt export with invalid format → Validation error
3. ❌ Attempt export with SQL injection in filters → Sanitized/safe
4. ✅ Exported file has security headers → `X-Content-Type-Options: nosniff`

**PHPUnit Test Commands**:
```bash
php artisan test --filter TeamExportTest
php artisan test --filter TeamExportSecurityTest

# Manual GDPR audit:
php artisan tinker
>>> \Spatie\Activitylog\Models\Activity::where('description', 'team_export_initiated')->get()
```

**Manual Validation**:
```bash
# Test RBAC:
curl -X POST http://whs.test/team/export \
  -H "Authorization: Bearer {regular_user_token}" \
  -d "format=csv"
# Expected: HTTP 403 Forbidden

# Test export as authorized user:
curl -X POST http://whs.test/team/export \
  -H "Authorization: Bearer {hr_manager_token}" \
  -d "format=csv&fields[]=name&fields[]=email" \
  -o employees.csv
# Expected: HTTP 200 + CSV file downloaded

# Verify audit log:
mysql> SELECT * FROM activity_log WHERE description = 'team_export_initiated' ORDER BY created_at DESC LIMIT 5;
```

### Owner & Timeline
- **Owner**: Backend Team Lead (primary) + Security Engineer (review)
- **Estimated Effort**: 2 days backend + 0.5 days security review
- **Dependencies**: Blocker #1 (SQL injection) must be fixed first for filter safety
- **Sequencing**: Complete before Phase 2 (after basic table working)

---

## Blocker #3: Offline PWA Sync Conflicts (HIGH)

### Context
**Severity**: HIGH
**Risk Score**: 6/10
**Discovery**: Audit Section 2.6 "Risk Register Analysis" + Code Inspection
**Affected Component**: PWA offline sync strategy (service-worker.js, sync-manager.js, offline-db.js)

**Current State**:
- **PWA Implementation Exists**: Service worker with Workbox, IndexedDB with Dexie.js
- **Files Involved**:
  - `public/service-worker.js` - Cache strategies, background sync
  - `public/js/offline-db.js` - IndexedDB schema and operations (497 lines)
  - `public/js/sync-manager.js` - Auto-sync on connection restore (476 lines)
  - `public/js/offline-indicator.js` - UI status indicators (412 lines)

**Current Sync Behavior** (from sync-manager.js analysis):
- Line 8: Comment says "Conflict resolution (server wins by default)"
- Lines 126-198: `processSyncItem()` function
- **No actual conflict detection**: Code blindly accepts server response
- **No version checking**: Doesn't verify if server record was modified
- Lines 248-253: Error handling exists but no 409 Conflict handling

**Risk Scenario**:
```
Timeline:
T0: User A goes offline, loads employee record #42 (version 1, name="John Smith")
T1: User B (online) edits employee #42 → name="John Doe" (version 2)
T2: User A (offline) edits employee #42 → name="John Miller" (still version 1)
T3: User A reconnects, sync-manager.js pushes changes
Result: User A's change overwrites User B's change (data loss)
Correct: System should detect conflict (version mismatch) and show resolution UI
```

### Proposed Solution

**1. Backend: Add Optimistic Locking to Users Table**

Add version column for conflict detection:

```php
// database/migrations/2025_XX_XX_XXXXXX_add_version_to_users_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('version')->default(1)->after('updated_at');
            $table->index('version');
        });

        // Initialize existing records
        DB::table('users')->update(['version' => 1]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('version');
        });
    }
};
```

**2. Backend: Update Controller to Check Version**

Modify TeamController update method to detect conflicts:

```php
// app/Modules/TeamManagement/Controllers/TeamController.php
// MODIFY update() method:

public function update(Request $request, User $user)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'phone' => 'nullable|string|max:20',
        'employment_status' => 'required|in:active,inactive,on_leave',
        'version' => 'required|integer', // Client must send current version
    ]);

    // Optimistic locking check
    if ($user->version !== $validated['version']) {
        // Conflict detected: server version doesn't match client version
        return response()->json([
            'error' => 'Conflict detected',
            'message' => 'This record was modified by another user',
            'server_version' => $user->version,
            'server_data' => $user->toArray(),
            'client_version' => $validated['version'],
        ], 409); // HTTP 409 Conflict
    }

    // No conflict: proceed with update
    $user->fill($validated);
    $user->version++; // Increment version on save
    $user->save();

    return response()->json([
        'success' => true,
        'data' => $user,
        'version' => $user->version, // Return new version to client
    ]);
}
```

**3. Frontend: Update offline-db.js to Store Version**

Modify incidents table schema to include version:

```javascript
// public/js/offline-db.js
// MODIFY line 52 (incidents schema):

db.version(DB_VERSION).stores({
    // Add version field to schema:
    incidents: '++localId, id, type, severity, status, incident_datetime, user_id, branch_id, created_at, synced, version',

    // Keep other tables as-is
    riskAssessments: '++localId, id, title, status, risk_score, likelihood, consequence, user_id, branch_id, created_at, synced, version',
    journeys: '++localId, id, destination, status, planned_departure, user_id, branch_id, created_at, synced, version',
    inspections: '++localId, id, vehicle_id, inspection_type, status, inspection_date, user_id, branch_id, created_at, synced, version',

    // ... rest unchanged
});

// MODIFY addIncident() to store version (line 113):
async function addIncident(incidentData) {
    const incident = {
        ...incidentData,
        created_at: new Date().toISOString(),
        synced: false,
        localId: Date.now(),
        version: incidentData.version || 1, // Store version from server
    };

    const localId = await db.incidents.add(incident);
    await addToSyncQueue('incident', localId, 'CREATE', incident);
    return localId;
}
```

**4. Frontend: Update sync-manager.js for Conflict Detection**

Modify sync logic to handle 409 Conflict responses:

```javascript
// public/js/sync-manager.js
// MODIFY syncIncident() method (lines 207-254):

async function syncIncident(operation, data, localId) {
    console.log('[SyncManager] Syncing incident:', operation, data);

    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    let url, method, body;

    // Include version in UPDATE requests for optimistic locking
    switch (operation) {
        case 'CREATE':
            url = '/incidents';
            method = 'POST';
            body = JSON.stringify(data);
            break;

        case 'UPDATE':
            url = `/incidents/${data.id}`;
            method = 'PUT';
            // IMPORTANT: Include version for conflict detection
            body = JSON.stringify({
                ...data,
                version: data.version, // Send current version
            });
            break;

        case 'DELETE':
            url = `/incidents/${data.id}`;
            method = 'DELETE';
            body = null;
            break;

        default:
            throw new Error(`Unknown operation: ${operation}`);
    }

    const response = await fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: body
    });

    // Handle conflict (409) responses
    if (response.status === 409) {
        const conflictData = await response.json();

        // Store conflict for user resolution
        await handleSyncConflict(operation, data, localId, conflictData);

        // Throw error to mark sync as failed (will retry later)
        throw new Error('Conflict detected - user resolution required');
    }

    if (!response.ok) {
        const errorText = await response.text();
        throw new Error(`Server error: ${response.status} - ${errorText}`);
    }

    const result = await response.json();

    // Update local version to match server
    if (result.version) {
        const localRecord = await OfflineDB.getIncident(localId);
        if (localRecord) {
            await OfflineDB.updateIncident(localId, { version: result.version });
        }
    }

    return result;
}

/**
 * Handle sync conflict by storing for user resolution
 * @param {string} operation - Operation type
 * @param {Object} clientData - Client's data
 * @param {number} localId - Local record ID
 * @param {Object} serverResponse - Server's conflict response
 */
async function handleSyncConflict(operation, clientData, localId, serverResponse) {
    console.warn('[SyncManager] Conflict detected:', {
        operation,
        clientData,
        serverResponse
    });

    // Store conflict in a new IndexedDB table for UI display
    await OfflineDB.db.syncConflicts.add({
        entity_type: 'incident',
        entity_id: localId,
        operation: operation,
        client_data: clientData,
        client_version: clientData.version,
        server_data: serverResponse.server_data,
        server_version: serverResponse.server_version,
        detected_at: new Date().toISOString(),
        resolved: false,
    });

    // Show notification to user
    showConflictNotification();
}

/**
 * Show conflict notification to user
 */
function showConflictNotification() {
    if (typeof OfflineIndicator !== 'undefined') {
        OfflineIndicator.showNotification(
            '⚠️ Sync Conflict',
            'Some changes conflict with server updates. Click to resolve.',
            'warning'
        );
    }

    // Update sync indicator to show conflicts pending
    // TODO: Add conflict count to offline indicator badge
}
```

**5. Frontend: Add Conflict Resolution UI**

Create conflict resolution modal:

```javascript
// public/js/conflict-resolver.js (NEW FILE)
/**
 * Rotech WHS Conflict Resolver - UI for resolving sync conflicts
 */

const ConflictResolver = (function() {
    'use strict';

    /**
     * Show conflict resolution UI
     */
    async function showConflicts() {
        const conflicts = await OfflineDB.db.syncConflicts
            .where('resolved').equals(false)
            .toArray();

        if (conflicts.length === 0) {
            alert('No conflicts to resolve');
            return;
        }

        // Build conflict resolution modal
        const modal = buildConflictModal(conflicts);
        document.body.appendChild(modal);

        // Show modal
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();
    }

    /**
     * Build conflict resolution modal
     */
    function buildConflictModal(conflicts) {
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.id = 'conflictResolverModal';
        modal.innerHTML = `
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bx bx-error-circle text-warning"></i>
                            Resolve Sync Conflicts
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <strong>What happened?</strong> While you were offline, someone else modified these records.
                            Choose which version to keep for each conflict.
                        </div>

                        <div id="conflictList">
                            ${conflicts.map((conflict, index) => buildConflictItem(conflict, index)).join('')}
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" onclick="ConflictResolver.resolveAll()">
                            Resolve All
                        </button>
                    </div>
                </div>
            </div>
        `;

        return modal;
    }

    /**
     * Build individual conflict item
     */
    function buildConflictItem(conflict, index) {
        return `
            <div class="card mb-3" data-conflict-id="${conflict.id}">
                <div class="card-header">
                    <strong>Conflict #${index + 1}:</strong> ${conflict.entity_type}
                    <span class="badge bg-secondary">${conflict.operation}</span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Your Changes (Offline)</h6>
                            <pre class="bg-light p-2 rounded">${JSON.stringify(conflict.client_data, null, 2)}</pre>
                            <button
                                class="btn btn-sm btn-outline-primary"
                                onclick="ConflictResolver.resolveConflict(${conflict.id}, 'client')"
                            >
                                Keep My Version
                            </button>
                        </div>
                        <div class="col-md-6">
                            <h6>Server Version (Current)</h6>
                            <pre class="bg-light p-2 rounded">${JSON.stringify(conflict.server_data, null, 2)}</pre>
                            <button
                                class="btn btn-sm btn-outline-success"
                                onclick="ConflictResolver.resolveConflict(${conflict.id}, 'server')"
                            >
                                Keep Server Version
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    /**
     * Resolve single conflict
     */
    async function resolveConflict(conflictId, resolution) {
        const conflict = await OfflineDB.db.syncConflicts.get(conflictId);

        if (resolution === 'client') {
            // Keep client version: retry sync with force flag
            // (Server must accept client version by incrementing version)
            // This requires backend support for forced updates
            console.log('[ConflictResolver] Keeping client version:', conflict.client_data);

            // Re-add to sync queue with higher priority
            await OfflineDB.addToSyncQueue(
                conflict.entity_type,
                conflict.entity_id,
                conflict.operation,
                conflict.client_data
            );

        } else {
            // Keep server version: discard client changes
            console.log('[ConflictResolver] Keeping server version:', conflict.server_data);

            // Update local record with server data
            await OfflineDB.updateIncident(conflict.entity_id, {
                ...conflict.server_data,
                version: conflict.server_version,
                synced: true,
            });

            // Remove from sync queue
            await OfflineDB.syncQueue
                .where('entity_id').equals(conflict.entity_id)
                .delete();
        }

        // Mark conflict as resolved
        await OfflineDB.db.syncConflicts.update(conflictId, { resolved: true });

        // Remove from UI
        const card = document.querySelector(`[data-conflict-id="${conflictId}"]`);
        if (card) {
            card.remove();
        }

        // Check if all conflicts resolved
        const remaining = await OfflineDB.db.syncConflicts
            .where('resolved').equals(false)
            .count();

        if (remaining === 0) {
            alert('All conflicts resolved!');
            bootstrap.Modal.getInstance(document.getElementById('conflictResolverModal')).hide();
        }
    }

    /**
     * Auto-resolve all conflicts (use with caution)
     */
    async function resolveAll() {
        const conflicts = await OfflineDB.db.syncConflicts
            .where('resolved').equals(false)
            .toArray();

        for (const conflict of conflicts) {
            // Default: keep server version (safer)
            await resolveConflict(conflict.id, 'server');
        }
    }

    return {
        showConflicts,
        resolveConflict,
        resolveAll,
    };
})();
```

**6. Update offline-db.js to Add Conflict Table**

```javascript
// public/js/offline-db.js
// MODIFY line 50 (add syncConflicts table):

db.version(DB_VERSION).stores({
    incidents: '++localId, id, type, severity, status, incident_datetime, user_id, branch_id, created_at, synced, version',
    riskAssessments: '++localId, id, title, status, risk_score, likelihood, consequence, user_id, branch_id, created_at, synced, version',
    journeys: '++localId, id, destination, status, planned_departure, user_id, branch_id, created_at, synced, version',
    inspections: '++localId, id, vehicle_id, inspection_type, status, inspection_date, user_id, branch_id, created_at, synced, version',
    syncQueue: '++id, entity_type, entity_id, operation, data, created_at, attempts, last_attempt',
    photos: '++id, entity_type, entity_id, file_name, base64_data, mime_type, file_size, created_at, synced',
    apiCache: 'key, data, timestamp, expires_at',
    preferences: 'key, value',

    // NEW: Conflict tracking table
    syncConflicts: '++id, entity_type, entity_id, operation, detected_at, resolved',
});
```

### Affected Files

| File | Change Type | Lines | Estimated Effort |
|------|-------------|-------|------------------|
| `database/migrations/2025_XX_XX_add_version_to_users.php` | Create (migration) | ~40 | 1 hour |
| `app/Modules/TeamManagement/Controllers/TeamController.php` | Modify update() method | ~30 | 2 hours |
| `public/js/offline-db.js` | Modify schema + add conflict table | ~20 | 1 hour |
| `public/js/sync-manager.js` | Add conflict detection logic | ~80 | 3 hours |
| `public/js/conflict-resolver.js` | Create (new conflict UI) | ~250 | 4 hours |
| `resources/views/layouts/app.blade.php` | Include conflict-resolver.js | ~2 | 15 min |
| `tests/Feature/OptimisticLockingTest.php` | Create (backend tests) | ~150 | 3 hours |
| `tests/Browser/ConflictResolutionTest.php` | Create (Dusk E2E tests) | ~200 | 4 hours |

**Total Effort**: 3 days (18 hours)

### Verification Checklist

**Backend Tests (PHPUnit)**:
1. ✅ Update with matching version → Success, version incremented
2. ❌ Update with old version → HTTP 409 Conflict with server data
3. ❌ Update with future version → HTTP 409 Conflict
4. ✅ Create new record → Version initialized to 1
5. ✅ Concurrent updates (simulated) → Second update gets 409

**Frontend Tests (Browser/Manual)**:
1. ✅ Offline edit + online edit + sync → Conflict detected and modal shown
2. ✅ Resolve conflict (keep client) → Client data synced, version updated
3. ✅ Resolve conflict (keep server) → Server data adopted, local updated
4. ✅ Multiple conflicts → All displayed in modal, individually resolvable
5. ✅ Conflict notification → Badge appears with count

**Integration Tests (Laravel Dusk)**:
```php
// tests/Browser/ConflictResolutionTest.php
public function test_conflict_resolution_workflow()
{
    // User A goes offline, edits record
    // User B (online) edits same record
    // User A reconnects
    // Assert: Conflict modal appears
    // User A chooses "Keep Server Version"
    // Assert: Local data updated, no sync error
}
```

**Manual Test Scenario**:
```
Step 1: Browser A - Go offline (Dev Tools > Network > Offline)
Step 2: Browser A - Edit employee #1, change name to "Alice Test"
Step 3: Browser B - Edit employee #1, change name to "Bob Test", save
Step 4: Browser A - Go online
Step 5: Browser A - Observe sync indicator, expect conflict notification
Step 6: Browser A - Click notification, open conflict modal
Step 7: Browser A - See side-by-side comparison of "Alice Test" vs "Bob Test"
Step 8: Browser A - Choose "Keep Server Version"
Step 9: Browser A - Verify local data now shows "Bob Test"
Step 10: MySQL - Verify version = 2, no data loss
```

### Owner & Timeline
- **Owner**: Frontend Specialist (primary) + Backend Team Lead (optimistic locking)
- **Estimated Effort**: 2 days frontend + 1 day backend
- **Dependencies**: None (can work in parallel with other blockers)
- **Sequencing**: Complete before Phase 3 (before PWA offline features enabled)

---

## Blocker #4: Feature Flag Strategy (HIGH)

### Context
**Severity**: HIGH
**Risk Score**: 5/10
**Discovery**: Audit Section 2.8 "Documentation & Enablement" + Rollout Plan Section 10
**Affected Component**: Phased rollout mechanism for dense table UI

**Current State**:
- **Rollout Plan Mentions**: Section 10.3 "Rollout Controls" suggests feature flags
- **No Decision Made**: Laravel Pennant vs custom implementation not chosen
- **Risk**: Without flags, rollback requires code deployment (high risk)
- **Alternative**: Use branch-based rollout (lower risk but less flexible)

**Requirements**:
1. **Gradual Rollout**: Enable dense table for specific branches first (e.g., Adelaide HQ)
2. **A/B Testing**: Compare old card layout vs new dense table (optional)
3. **Emergency Rollback**: Disable feature instantly if bugs found (critical)
4. **User-Level Flags**: Allow opt-in for beta testing (nice-to-have)
5. **Performance**: Flag checks must be <5ms overhead per request

### Solution Comparison

| Criteria | Laravel Pennant | Custom Implementation |
|----------|----------------|----------------------|
| **Setup Time** | 1 hour (composer + migrate) | 4 hours (build tables + logic) |
| **Maintenance** | Low (official package) | Medium (custom code to maintain) |
| **Flexibility** | High (built-in scopes) | Medium (depends on design) |
| **Performance** | <2ms (cached) | <5ms (depends on caching) |
| **A/B Testing** | ✅ Built-in lottery() | ❌ Need to build |
| **User-Level Flags** | ✅ Built-in for() | ✅ Custom but simple |
| **Branch-Level Flags** | ✅ Custom scope | ✅ Native design |
| **Emergency Disable** | ✅ artisan pennant:purge | ✅ DB update |
| **Learning Curve** | Low (Laravel docs) | None (custom logic) |
| **Vendor Lock-in** | Medium (Laravel) | None |

**Recommendation**: **Use Laravel Pennant** for official support, performance, and built-in A/B testing.

### Proposed Solution

**1. Install Laravel Pennant**

```bash
composer require laravel/pennant
php artisan vendor:publish --tag=pennant-migrations
php artisan migrate
```

**2. Create Dense Table Feature Class**

```php
// app/Features/DenseTableFeature.php
<?php

namespace App\Features;

use App\Models\User;
use Illuminate\Support\Lottery;

class DenseTableFeature
{
    /**
     * Define feature resolution logic
     *
     * Rollout strategy:
     * - Phase 1 (Week 1): Adelaide HQ only (branch_id = 1)
     * - Phase 2 (Week 2-3): 50% of all branches (A/B test)
     * - Phase 3 (Week 4): 100% rollout
     * - Emergency: Can disable via artisan pennant:purge
     */
    public function resolve(User $user): mixed
    {
        // Emergency kill switch (check first)
        if (config('features.dense_table_emergency_disable', false)) {
            return false;
        }

        // Phase 1: Adelaide HQ only (branch_id = 1)
        if (now()->isBefore('2025-02-01')) {
            return $user->branch_id === 1;
        }

        // Phase 2: 50% rollout for A/B testing
        if (now()->isBefore('2025-02-15')) {
            return Lottery::odds(1, 2)
                ->winner(fn () => true)
                ->loser(fn () => false)
                ->choose();
        }

        // Phase 3: 100% rollout
        return true;
    }
}
```

**3. Register Feature in Pennant Config**

```php
// config/pennant.php
<?php

use App\Features\DenseTableFeature;

return [
    'features' => [
        'dense-table' => DenseTableFeature::class,
    ],

    // Cache feature flags for performance
    'store' => 'database', // or 'array' for testing
    'cache' => [
        'store' => 'redis', // Fast cache layer
        'ttl' => 3600, // 1 hour cache
    ],
];
```

**4. Update TeamController to Use Feature Flag**

```php
// app/Modules/TeamManagement/Controllers/TeamController.php
// MODIFY index() method:

use Laravel\Pennant\Feature;

public function index(Request $request): View
{
    // Check if user has dense table feature enabled
    $useDenseTable = Feature::active('dense-table');

    $filters = [
        'search' => trim((string) $request->input('search')),
        'branch' => $request->input('branch'),
        'role' => $request->input('role'),
        'status' => $request->input('status'),
    ];

    $query = User::query()
        ->with(['branch', 'roles', 'currentVehicleAssignment.vehicle'])
        ->withCount(['incidents']);

    // Apply filters (same for both layouts)
    // ... filter logic ...

    // Choose pagination based on feature flag
    $perPage = $useDenseTable ? 50 : 12;
    $users = $query->orderBy('name')->paginate($perPage)->withQueryString();

    // Pass feature flag to view
    return view('content.TeamManagement.Index', [
        'users' => $users,
        'filters' => $filters,
        'branches' => Branch::all(),
        'roles' => Role::all(),
        'useDenseTable' => $useDenseTable, // NEW: Let view decide layout
    ]);
}
```

**5. Update Blade View for Conditional Rendering**

```blade
{{-- resources/views/content/TeamManagement/Index.blade.php --}}

@if($useDenseTable)
    {{-- NEW: Dense table layout --}}
    <x-whs.dense-table
        :data="$users"
        :columns="['avatar', 'name', 'email', 'phone', 'position', 'status', 'actions']"
        :sortable="['name', 'email', 'employment_status']"
        :filterable="$filters"
    />
@else
    {{-- EXISTING: Card-based layout --}}
    <div class="row g-4">
        @foreach($users as $user)
            <div class="col-xl-3 col-lg-4 col-md-6">
                <x-whs.card>
                    {{-- Current card implementation --}}
                </x-whs.card>
            </div>
        @endforeach
    </div>
@endif

{{-- Pagination (same for both) --}}
<div class="mt-4">
    {{ $users->links() }}
</div>
```

**6. Emergency Disable Mechanism**

```bash
# Method 1: Artisan command (instant)
php artisan pennant:purge dense-table

# Method 2: Config flag (requires cache clear)
# Set in .env:
FEATURES_DENSE_TABLE_EMERGENCY_DISABLE=true
php artisan config:clear
php artisan cache:clear
```

**7. Admin UI for Feature Flag Management (Optional)**

```blade
{{-- resources/views/admin/features.blade.php --}}

<div class="card">
    <div class="card-header">
        <h5>Feature Flags</h5>
    </div>
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>Feature</th>
                    <th>Status</th>
                    <th>Rollout Phase</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <strong>Dense Table UI</strong>
                        <br>
                        <small class="text-muted">New compact employee list layout</small>
                    </td>
                    <td>
                        @if(Feature::active('dense-table'))
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-secondary">Inactive</span>
                        @endif
                    </td>
                    <td>
                        @if(now()->isBefore('2025-02-01'))
                            Phase 1: Adelaide HQ Only
                        @elseif(now()->isBefore('2025-02-15'))
                            Phase 2: 50% A/B Test
                        @else
                            Phase 3: 100% Rollout
                        @endif
                    </td>
                    <td>
                        <form action="{{ route('admin.features.purge', 'dense-table') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-danger">
                                Emergency Disable
                            </button>
                        </form>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
```

### Affected Files

| File | Change Type | Estimated Effort |
|------|-------------|------------------|
| `composer.json` | Add `laravel/pennant` dependency | 5 min |
| `app/Features/DenseTableFeature.php` | Create (feature class) | 1 hour |
| `config/pennant.php` | Configure features | 30 min |
| `app/Modules/TeamManagement/Controllers/TeamController.php` | Add feature flag check (10 lines) | 30 min |
| `resources/views/content/TeamManagement/Index.blade.php` | Add conditional rendering | 1 hour |
| `resources/views/admin/features.blade.php` | Create (admin UI) | 2 hours |
| `tests/Feature/DenseTableFeatureFlagTest.php` | Create (test suite) | 2 hours |
| `tests/Feature/DenseTableRolloutTest.php` | Create (A/B test validation) | 2 hours |

**Total Effort**: 1.5 days (9 hours)

### Verification Checklist

**Functional Tests**:
1. ✅ User in Adelaide HQ (branch_id=1) → Sees dense table
2. ❌ User in other branch (Phase 1) → Sees card layout
3. ✅ A/B test (Phase 2) → ~50% users see dense table
4. ✅ Full rollout (Phase 3) → 100% users see dense table
5. ✅ Emergency disable → All users see card layout immediately

**Performance Tests**:
1. ✅ Feature flag check overhead <5ms → Measured with Laravel Telescope
2. ✅ Redis cache hit rate >95% → Check `redis-cli INFO stats`
3. ✅ Database queries not increased → Same query count regardless of flag

**Admin Tests**:
1. ✅ Admin can view feature status → Shows correct phase and percentage
2. ✅ Admin can emergency disable → Instant rollback to cards
3. ✅ Audit log records flag changes → Activity log entry created

**PHPUnit Test Commands**:
```bash
php artisan test --filter DenseTableFeatureFlagTest
php artisan test --filter DenseTableRolloutTest

# Manual performance test:
php artisan tinker
>>> Benchmark::dd(fn () => Feature::active('dense-table'), iterations: 1000);
// Expected: <5ms average
```

**Manual Test Scenarios**:

**Scenario 1: Phase 1 Rollout (Adelaide HQ Only)**
```bash
# Set phase 1 date:
# In DenseTableFeature.php, change date to today + 7 days

# Test user from Adelaide HQ:
curl http://whs.test/team -H "Cookie: session={adelaide_user_session}"
# Expected: Dense table HTML with 50 rows

# Test user from other branch:
curl http://whs.test/team -H "Cookie: session={perth_user_session}"
# Expected: Card layout HTML with 12 per page
```

**Scenario 2: Emergency Disable**
```bash
# Phase 3 (100% rollout) active
# All users see dense table

# Emergency disable:
php artisan pennant:purge dense-table

# Test all users:
curl http://whs.test/team -H "Cookie: session={any_user_session}"
# Expected: Card layout for all users immediately
```

### Owner & Timeline
- **Owner**: Backend Team Lead (primary) + Frontend Specialist (view changes)
- **Estimated Effort**: 1 day backend + 0.5 days frontend
- **Dependencies**: None (can implement early)
- **Sequencing**: Complete before Phase 1 Day 1 (critical path)

---

## Blocker #5: Incomplete Blade Component Templates (MEDIUM)

### Context
**Severity**: MEDIUM
**Risk Score**: 4/10
**Discovery**: Audit Section 2.3 "Component Architecture Validation"
**Affected Component**: `<x-whs.table-cell>` and `<x-whs.side-drawer>` Blade components

**Current State**:
- **Rollout Plan Section 4**: Lists 3 Blade components
  1. `<x-whs.dense-table>` - 95% complete (props + full template specified)
  2. `<x-whs.table-cell>` - 70% complete (props only, missing template body)
  3. `<x-whs.side-drawer>` - 60% complete (props only, missing ESC handler, backdrop, animations)

**Issues**:
1. **table-cell component**: Specification lists props but doesn't show template body for avatar+badge, status badges, action menus
2. **side-drawer component**: Missing:
   - ESC key handler for accessibility
   - Backdrop click-to-close
   - CSS animations for slide-in/slide-out
   - Focus trap for keyboard navigation
   - ARIA attributes for screen readers

### Proposed Solution

**1. Complete `<x-whs.table-cell>` Template**

```blade
{{-- resources/views/components/whs/table-cell.blade.php --}}

@props([
    'type' => 'text',        // text|avatar|badge|status|actions
    'value' => null,
    'user' => null,          // For avatar type
    'status' => null,        // For status type
    'actions' => [],         // For actions type
    'align' => 'left',       // left|center|right
    'width' => null,         // Optional: 150px, 200px, etc
])

@php
    // Alignment classes
    $alignClass = match($align) {
        'center' => 'text-center',
        'right' => 'text-end',
        default => 'text-start',
    };

    // Width style
    $widthStyle = $width ? "width: {$width};" : '';
@endphp

<td
    {{ $attributes->merge([
        'class' => "dense-table-cell {$alignClass}",
        'style' => $widthStyle,
    ]) }}
>
    @switch($type)
        @case('avatar')
            {{-- Avatar + Name + Badge --}}
            <div class="d-flex align-items-center">
                @if($user->avatar_url)
                    <img
                        src="{{ $user->avatar_url }}"
                        alt="{{ $user->name }}"
                        class="rounded-circle me-2"
                        style="width: 32px; height: 32px; object-fit: cover;"
                    >
                @else
                    <div
                        class="avatar-initials rounded-circle me-2 d-flex align-items-center justify-content-center bg-primary text-white"
                        style="width: 32px; height: 32px; font-size: 14px; font-weight: 500;"
                    >
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                @endif

                <div class="flex-grow-1 overflow-hidden">
                    <div class="d-flex align-items-center">
                        <span class="text-truncate me-1">{{ $user->name }}</span>

                        @if($user->hasRole('admin'))
                            <span class="badge bg-danger badge-sm" title="Administrator">
                                <i class="bx bx-shield-alt"></i>
                            </span>
                        @elseif($user->hasRole('supervisor'))
                            <span class="badge bg-primary badge-sm" title="Supervisor">
                                <i class="bx bx-user-check"></i>
                            </span>
                        @endif
                    </div>

                    @if($user->employee_id)
                        <small class="text-muted">{{ $user->employee_id }}</small>
                    @endif
                </div>
            </div>
            @break

        @case('badge')
            {{-- Simple badge with value --}}
            <span class="badge bg-{{ $attributes->get('variant', 'secondary') }}">
                {{ $value }}
            </span>
            @break

        @case('status')
            {{-- Status badge with icons --}}
            @php
                $statusConfig = [
                    'active' => ['class' => 'bg-success', 'icon' => 'bx-check-circle'],
                    'inactive' => ['class' => 'bg-secondary', 'icon' => 'bx-x-circle'],
                    'on_leave' => ['class' => 'bg-warning', 'icon' => 'bx-time-five'],
                    'suspended' => ['class' => 'bg-danger', 'icon' => 'bx-block'],
                ];

                $config = $statusConfig[$status] ?? ['class' => 'bg-secondary', 'icon' => 'bx-question-mark'];
            @endphp

            <span class="badge {{ $config['class'] }} d-inline-flex align-items-center">
                <i class="bx {{ $config['icon'] }} me-1"></i>
                {{ ucfirst(str_replace('_', ' ', $status)) }}
            </span>
            @break

        @case('actions')
            {{-- Action dropdown menu --}}
            <div class="dropdown">
                <button
                    class="btn btn-sm btn-icon btn-text-secondary rounded-pill dropdown-toggle hide-arrow"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                >
                    <i class="bx bx-dots-vertical-rounded"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    @foreach($actions as $action)
                        @if($action['type'] === 'divider')
                            <li><hr class="dropdown-divider"></li>
                        @else
                            <li>
                                <a
                                    class="dropdown-item {{ $action['class'] ?? '' }}"
                                    href="{{ $action['url'] ?? '#' }}"
                                    @if($action['confirm'] ?? false)
                                        onclick="return confirm('{{ $action['confirm'] }}')"
                                    @endif
                                >
                                    @if($action['icon'] ?? false)
                                        <i class="bx {{ $action['icon'] }} me-2"></i>
                                    @endif
                                    {{ $action['label'] }}
                                </a>
                            </li>
                        @endif
                    @endforeach
                </ul>
            </div>
            @break

        @case('text')
        @default
            {{-- Default: plain text with truncation --}}
            <span class="text-truncate d-block" title="{{ $value }}">
                {{ $value }}
            </span>
            @break
    @endswitch
</td>
```

**2. Complete `<x-whs.side-drawer>` Template**

```blade
{{-- resources/views/components/whs/side-drawer.blade.php --}}

@props([
    'id' => 'sideDrawer',
    'title' => 'Details',
    'size' => 'medium',      // small (400px), medium (600px), large (800px)
    'position' => 'right',   // left|right
    'backdrop' => true,
    'keyboard' => true,      // Allow ESC to close
])

@php
    // Size mapping
    $widthClass = match($size) {
        'small' => 'side-drawer-sm',
        'large' => 'side-drawer-lg',
        default => 'side-drawer-md',
    };

    // Position class
    $positionClass = $position === 'left' ? 'side-drawer-left' : 'side-drawer-right';
@endphp

{{-- Backdrop --}}
<div
    id="{{ $id }}Backdrop"
    class="side-drawer-backdrop"
    style="display: none;"
    @if($backdrop)
        onclick="SideDrawer.close('{{ $id }}')"
    @endif
></div>

{{-- Drawer --}}
<div
    id="{{ $id }}"
    class="side-drawer {{ $widthClass }} {{ $positionClass }}"
    role="dialog"
    aria-labelledby="{{ $id }}Title"
    aria-hidden="true"
    tabindex="-1"
    {{ $attributes->merge(['class' => '']) }}
>
    {{-- Header --}}
    <div class="side-drawer-header">
        <h5 id="{{ $id }}Title" class="side-drawer-title">
            {{ $title }}
        </h5>
        <button
            type="button"
            class="btn-close"
            onclick="SideDrawer.close('{{ $id }}')"
            aria-label="Close"
        ></button>
    </div>

    {{-- Body --}}
    <div class="side-drawer-body">
        {{ $slot }}
    </div>

    {{-- Footer (optional) --}}
    @if(isset($footer))
        <div class="side-drawer-footer">
            {{ $footer }}
        </div>
    @endif
</div>

{{-- Side Drawer Controller (Auto-init) --}}
@once
<script>
/**
 * Side Drawer Controller - Keyboard navigation and focus management
 */
const SideDrawer = (function() {
    'use strict';

    let openDrawers = new Set();
    let previousFocus = null;

    /**
     * Open drawer
     */
    function open(drawerId) {
        const drawer = document.getElementById(drawerId);
        const backdrop = document.getElementById(drawerId + 'Backdrop');

        if (!drawer) {
            console.error('[SideDrawer] Drawer not found:', drawerId);
            return;
        }

        // Store current focus
        previousFocus = document.activeElement;

        // Show backdrop
        if (backdrop) {
            backdrop.style.display = 'block';
            setTimeout(() => backdrop.classList.add('show'), 10);
        }

        // Show drawer
        drawer.style.display = 'block';
        setTimeout(() => {
            drawer.classList.add('show');
            drawer.setAttribute('aria-hidden', 'false');
        }, 10);

        // Focus first focusable element
        setTimeout(() => {
            const firstFocusable = drawer.querySelector('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
            if (firstFocusable) {
                firstFocusable.focus();
            } else {
                drawer.focus();
            }
        }, 350); // After animation

        // Add to open set
        openDrawers.add(drawerId);

        // Add ESC handler
        if (drawer.getAttribute('data-keyboard') !== 'false') {
            document.addEventListener('keydown', handleEscape);
        }

        // Prevent body scroll
        document.body.style.overflow = 'hidden';

        // Emit open event
        drawer.dispatchEvent(new CustomEvent('drawer:open', { detail: { drawerId } }));
    }

    /**
     * Close drawer
     */
    function close(drawerId) {
        const drawer = document.getElementById(drawerId);
        const backdrop = document.getElementById(drawerId + 'Backdrop');

        if (!drawer) {
            console.error('[SideDrawer] Drawer not found:', drawerId);
            return;
        }

        // Hide drawer
        drawer.classList.remove('show');
        drawer.setAttribute('aria-hidden', 'true');

        // Hide backdrop
        if (backdrop) {
            backdrop.classList.remove('show');
        }

        // Wait for animation, then hide
        setTimeout(() => {
            drawer.style.display = 'none';
            if (backdrop) {
                backdrop.style.display = 'none';
            }

            // Restore previous focus
            if (previousFocus && typeof previousFocus.focus === 'function') {
                previousFocus.focus();
                previousFocus = null;
            }
        }, 300); // Match CSS transition duration

        // Remove from open set
        openDrawers.delete(drawerId);

        // Remove ESC handler if no drawers open
        if (openDrawers.size === 0) {
            document.removeEventListener('keydown', handleEscape);
            document.body.style.overflow = '';
        }

        // Emit close event
        drawer.dispatchEvent(new CustomEvent('drawer:close', { detail: { drawerId } }));
    }

    /**
     * Toggle drawer
     */
    function toggle(drawerId) {
        const drawer = document.getElementById(drawerId);
        if (drawer && drawer.classList.contains('show')) {
            close(drawerId);
        } else {
            open(drawerId);
        }
    }

    /**
     * Handle ESC key
     */
    function handleEscape(event) {
        if (event.key === 'Escape' && openDrawers.size > 0) {
            // Close most recently opened drawer
            const lastDrawer = Array.from(openDrawers).pop();
            close(lastDrawer);
        }
    }

    /**
     * Focus trap inside drawer
     */
    function trapFocus(drawer) {
        const focusableElements = drawer.querySelectorAll(
            'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
        );
        const firstElement = focusableElements[0];
        const lastElement = focusableElements[focusableElements.length - 1];

        drawer.addEventListener('keydown', function(event) {
            if (event.key !== 'Tab') return;

            if (event.shiftKey) {
                // Shift + Tab
                if (document.activeElement === firstElement) {
                    lastElement.focus();
                    event.preventDefault();
                }
            } else {
                // Tab
                if (document.activeElement === lastElement) {
                    firstElement.focus();
                    event.preventDefault();
                }
            }
        });
    }

    // Initialize focus traps on all drawers
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.side-drawer').forEach(drawer => {
            trapFocus(drawer);
        });
    });

    // Public API
    return {
        open,
        close,
        toggle,
    };
})();
</script>
@endonce

{{-- Side Drawer Styles --}}
@once
<style>
/* Side Drawer Base Styles */
.side-drawer {
    position: fixed;
    top: 0;
    bottom: 0;
    z-index: 1050;
    background: var(--sensei-surface, rgba(255, 255, 255, 0.95));
    backdrop-filter: blur(var(--sensei-blur, 10px));
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    display: none;
    overflow-y: auto;
}

/* Size Variants */
.side-drawer-sm { width: 400px; }
.side-drawer-md { width: 600px; }
.side-drawer-lg { width: 800px; }

/* Position Variants */
.side-drawer-right {
    right: 0;
    transform: translateX(100%);
}

.side-drawer-left {
    left: 0;
    transform: translateX(-100%);
}

.side-drawer.show {
    transform: translateX(0);
}

/* Backdrop */
.side-drawer-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1040;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.side-drawer-backdrop.show {
    opacity: 1;
}

/* Header */
.side-drawer-header {
    padding: 1.5rem;
    border-bottom: 1px solid var(--sensei-border, rgba(0, 0, 0, 0.1));
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.side-drawer-title {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 500;
}

/* Body */
.side-drawer-body {
    padding: 1.5rem;
    flex: 1;
}

/* Footer */
.side-drawer-footer {
    padding: 1rem 1.5rem;
    border-top: 1px solid var(--sensei-border, rgba(0, 0, 0, 0.1));
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
}

/* Responsive */
@media (max-width: 768px) {
    .side-drawer-sm,
    .side-drawer-md,
    .side-drawer-lg {
        width: 90vw;
        max-width: 500px;
    }
}
</style>
@endonce
```

**3. Usage Example in TeamManagement/Index.blade.php**

```blade
{{-- resources/views/content/TeamManagement/Index.blade.php --}}

{{-- Dense table with side drawer integration --}}
<x-whs.dense-table :data="$users">
    @foreach($users as $user)
        <tr onclick="SideDrawer.open('userDetailDrawer{{ $user->id }}')">
            <x-whs.table-cell type="checkbox" />
            <x-whs.table-cell type="avatar" :user="$user" width="220px" />
            <x-whs.table-cell type="text" :value="$user->position" width="160px" />
            <x-whs.table-cell type="text" :value="$user->email" width="200px" />
            <x-whs.table-cell type="text" :value="$user->phone" width="130px" />
            <x-whs.table-cell type="status" :status="$user->employment_status" width="150px" />
            <x-whs.table-cell
                type="actions"
                :actions="[
                    ['label' => 'View', 'url' => route('team.show', $user), 'icon' => 'bx-show'],
                    ['label' => 'Edit', 'url' => route('team.edit', $user), 'icon' => 'bx-edit'],
                    ['type' => 'divider'],
                    ['label' => 'Delete', 'url' => route('team.destroy', $user), 'icon' => 'bx-trash', 'class' => 'text-danger', 'confirm' => 'Delete this user?'],
                ]"
                width="120px"
            />
        </tr>

        {{-- Side drawer for this user --}}
        <x-whs.side-drawer
            id="userDetailDrawer{{ $user->id }}"
            title="{{ $user->name }}"
            size="medium"
        >
            {{-- User details content --}}
            <div class="mb-3">
                <h6>Contact Information</h6>
                <dl class="row">
                    <dt class="col-sm-4">Email</dt>
                    <dd class="col-sm-8">{{ $user->email }}</dd>

                    <dt class="col-sm-4">Phone</dt>
                    <dd class="col-sm-8">{{ $user->phone ?? 'N/A' }}</dd>

                    <dt class="col-sm-4">Employee ID</dt>
                    <dd class="col-sm-8">{{ $user->employee_id }}</dd>
                </dl>
            </div>

            <div class="mb-3">
                <h6>Employment Details</h6>
                <dl class="row">
                    <dt class="col-sm-4">Position</dt>
                    <dd class="col-sm-8">{{ $user->position }}</dd>

                    <dt class="col-sm-4">Branch</dt>
                    <dd class="col-sm-8">{{ $user->branch->name }}</dd>

                    <dt class="col-sm-4">Status</dt>
                    <dd class="col-sm-8">
                        <x-whs.table-cell type="status" :status="$user->employment_status" />
                    </dd>

                    <dt class="col-sm-4">Start Date</dt>
                    <dd class="col-sm-8">{{ $user->start_date?->format('d M Y') ?? 'N/A' }}</dd>
                </dl>
            </div>

            {{-- Footer with actions --}}
            <x-slot name="footer">
                <button type="button" class="btn btn-secondary" onclick="SideDrawer.close('userDetailDrawer{{ $user->id }}')">
                    Close
                </button>
                <a href="{{ route('team.edit', $user) }}" class="btn btn-primary">
                    Edit User
                </a>
            </x-slot>
        </x-whs.side-drawer>
    @endforeach
</x-whs.dense-table>
```

### Affected Files

| File | Change Type | Lines | Estimated Effort |
|------|-------------|-------|------------------|
| `resources/views/components/whs/table-cell.blade.php` | Complete template body | ~150 | 3 hours |
| `resources/views/components/whs/side-drawer.blade.php` | Complete template + JS + CSS | ~350 | 5 hours |
| `resources/views/content/TeamManagement/Index.blade.php` | Add side drawer integration | ~50 | 1 hour |
| `tests/Browser/TableCellComponentTest.php` | Create (Dusk visual tests) | ~100 | 2 hours |
| `tests/Browser/SideDrawerComponentTest.php` | Create (Dusk keyboard/focus tests) | ~150 | 3 hours |

**Total Effort**: 2.5 days (14 hours)

### Verification Checklist

**Table Cell Component Tests**:
1. ✅ Avatar cell with image → Image displayed + initials fallback
2. ✅ Avatar cell with admin badge → Badge shown with shield icon
3. ✅ Status cell (active) → Green badge with check icon
4. ✅ Status cell (on_leave) → Yellow badge with clock icon
5. ✅ Actions cell → Dropdown menu with all actions
6. ✅ Text cell with long content → Truncated with ellipsis + title tooltip
7. ✅ Alignment (left, center, right) → Correct CSS classes applied

**Side Drawer Component Tests (WCAG Compliance)**:
1. ✅ ESC key closes drawer → Drawer closes, focus returns
2. ✅ Backdrop click closes drawer → Drawer closes
3. ✅ Focus trap works → Tab cycles through drawer elements only
4. ✅ Shift+Tab reverse cycles → Last → First element
5. ✅ First element auto-focused on open → Close button has focus
6. ✅ Previous focus restored on close → Original button regains focus
7. ✅ ARIA attributes present → `role="dialog"`, `aria-labelledby`, `aria-hidden`
8. ✅ Body scroll prevented when open → No page scrolling
9. ✅ Animations smooth → 300ms cubic-bezier transition
10. ✅ Multiple drawers stack → Most recent closes first on ESC

**Accessibility Tests (axe DevTools)**:
```bash
# Run axe scan on dense table page:
npm run axe http://whs.test/team

# Expected results:
# - No critical violations
# - ARIA labels present on drawers
# - Keyboard navigation score: 100%
# - Focus management score: 100%
```

**Browser Tests (Laravel Dusk)**:
```php
// tests/Browser/SideDrawerComponentTest.php
public function test_keyboard_navigation()
{
    $this->browse(function (Browser $browser) {
        $browser->visit('/team')
            ->click('[data-drawer-trigger="userDetailDrawer1"]')
            ->waitFor('#userDetailDrawer1.show')

            // Test ESC closes drawer
            ->keys('body', '{escape}')
            ->waitUntilMissing('#userDetailDrawer1.show')

            // Test focus returns to trigger
            ->assertFocused('[data-drawer-trigger="userDetailDrawer1"]');
    });
}

public function test_focus_trap()
{
    $this->browse(function (Browser $browser) {
        $browser->visit('/team')
            ->click('[data-drawer-trigger="userDetailDrawer1"]')
            ->waitFor('#userDetailDrawer1.show')

            // Tab to last element
            ->keys('.side-drawer-footer button:last-child', '{tab}')

            // Should cycle back to first element (close button)
            ->assertFocused('.btn-close');
    });
}
```

### Owner & Timeline
- **Owner**: Frontend Specialist
- **Estimated Effort**: 2.5 days
- **Dependencies**: None (can work in parallel)
- **Sequencing**: Complete before Phase 1 Day 3 (component integration day)

---

## Timeline Adjustments

### Original Timeline (from Rollout Plan)
- **Total Duration**: 6 weeks (30 working days)
- **Phases**: 6 phases with specific day allocations

### Revised Timeline with Pre-Phase 1
- **Total Duration**: 7 weeks (35 working days)
- **New Phase Structure**: Pre-Phase 1 (5 days) + Phases 1-6 (30 days)

### Pre-Phase 1: Blocker Remediation (NEW)

**Duration**: 5 working days
**Parallelization**: 2 developers working concurrently

| Task | Owner | Effort | Day | Parallel Track |
|------|-------|--------|-----|----------------|
| **Blocker #1: SQL Injection Fix** | Backend Lead | 1 day | Day 1 | Track A |
| **Blocker #4: Feature Flag Setup** | Backend Lead | 1 day | Day 2 | Track A |
| **Blocker #2: Export Security (Backend)** | Backend Lead | 2 days | Day 3-4 | Track A |
| **Blocker #5: Blade Component Templates** | Frontend Spec | 2.5 days | Day 1-3 (half) | Track B |
| **Blocker #3: PWA Sync Conflicts (Frontend)** | Frontend Spec | 2 days | Day 3 (half)-5 | Track B |
| **Security Review & Testing** | Security Eng + QA | 1.5 days | Day 4-5 | Both Tracks |

**Critical Path**: Backend Lead (Day 1-4) → Security Review (Day 4-5)
**Total Parallel Effort**: 10 developer-days → 5 calendar days with 2 developers

### Updated Phase Timeline

| Phase | Original Days | Revised Days | Changes |
|-------|---------------|--------------|---------|
| **Pre-Phase 1** | 0 | 5 | ✅ NEW: Blocker remediation |
| **Phase 1** | 5 | 5 | No change (foundation) |
| **Phase 2** | 5 | 5 | No change (component build) |
| **Phase 3** | 5 | 5 | No change (integration) |
| **Phase 4** | 5 | 4 | ⚠️ REDUCE: Defer column drag-and-drop |
| **Phase 5** | 5 | 5 | No change (polish) |
| **Phase 6** | 5 | 6 | ✅ EXTEND: Add comprehensive security audit |
| **TOTAL** | 30 | 35 | +5 days (7 weeks) |

### Phase 4 Adjustment Rationale
**Original Phase 4** (5 days): Accessibility, responsive, performance, column drag-and-drop
**Issue**: 120% capacity (6 days of work in 5-day phase)
**Solution**: Defer column drag-and-drop to Phase 7 (post-launch enhancement)
**Revised Phase 4** (4 days): Accessibility, responsive, performance only

### Phase 6 Extension Rationale
**Original Phase 6** (5 days): QA testing, documentation, deployment prep
**Issue**: No comprehensive security audit for export feature (GDPR compliance)
**Solution**: Add 1 day for external security audit + penetration testing
**Revised Phase 6** (6 days): QA + Documentation + Security Audit + Deployment Prep

### Resource Allocation Summary

**Pre-Phase 1 (Week 1)**:
| Resource | Mon | Tue | Wed | Thu | Fri |
|----------|-----|-----|-----|-----|-----|
| Backend Lead | SQL | Feature Flags | Export Security → | → | Review |
| Frontend Spec | Blade Components → | → | PWA Sync → | → | |
| Security Eng | - | - | - | Review | Audit |
| QA Eng | - | - | - | Test | Test |

**Phase 1-6 (Weeks 2-7)**: Follow original rollout plan schedule with Phase 4 & 6 adjustments

---

## Additional Risks & Recommendations

### Additional Risk #1: Design Token Integration Gap (MEDIUM → PROMOTE TO HIGH)

**Context**:
- **Discovery**: Inspected `resources/css/sensei-theme.css` and found comprehensive design token system
- **Issue**: Rollout Plan Section 5 "Modular CSS" proposes creating new glassmorphism CSS without referencing existing tokens
- **Impact**: Code duplication, inconsistent styling, maintenance burden

**Existing Design Tokens (sensei-theme.css)**:
```css
--sensei-surface: rgba(30, 35, 45, 0.82);       /* Use instead of custom bg */
--sensei-surface-hover: rgba(36, 44, 57, 0.92); /* Use for row hover */
--sensei-blur: 26px;                            /* Use instead of fixed blur(10px) */
--sensei-border: rgba(255, 255, 255, 0.07);     /* Use for borders */
--sensei-radius: 20px;                          /* Use for border-radius */
--sensei-radius-sm: 14px;                       /* Use for smaller components */
--sensei-transition: 220ms ease;                /* Use for all transitions */
```

**Rollout Plan Proposed CSS** (Section 5):
```css
/* PROPOSED (DUPLICATES EXISTING TOKENS): */
.dense-table {
  background: rgba(255, 255, 255, 0.05);        /* ❌ Use var(--sensei-surface) */
  backdrop-filter: blur(10px);                  /* ❌ Use var(--sensei-blur) */
  border-radius: 16px;                          /* ❌ Use var(--sensei-radius) */
  border: 1px solid rgba(255, 255, 255, 0.1);  /* ❌ Use var(--sensei-border) */
}
```

**Recommended Fix**:
```css
/* CORRECTED (USES EXISTING TOKENS): */
.dense-table {
  background: var(--sensei-surface);
  backdrop-filter: blur(var(--sensei-blur));
  border-radius: var(--sensei-radius);
  border: 1px solid var(--sensei-border);
  transition: var(--sensei-transition);
}

.dense-table-row:hover {
  background: var(--sensei-surface-hover);
}

.dense-table-cell {
  padding: var(--sensei-spacing-3); /* 12px */
}
```

**Action Required**:
1. Update rollout plan CSS sections (5.1, 5.2) to reference existing tokens
2. Create token documentation: `docs/design-tokens.md`
3. Add Stylelint rule to enforce token usage: `stylelint-config-rotech.js`
4. Estimated Effort: 0.5 days (4 hours)
5. Owner: Frontend Specialist
6. Timeline: Pre-Phase 1 or Phase 1 Day 1

**Severity Upgrade**: MEDIUM → HIGH
**Rationale**: Violates DRY principle, creates maintenance debt, inconsistent with existing design system

---

### Additional Risk #2: Mobile Touch Gesture Patterns (MEDIUM)

**Context**:
- **Discovery**: Rollout Plan Section 7 "Responsive Design" mentions "simplified table" and "hybrid list" for mobile
- **Issue**: No specification for mobile-specific gestures (swipe-to-open, swipe-to-delete, pull-to-refresh)
- **User Expectation**: Field workers expect native-like swipe gestures on mobile devices

**Missing Mobile Patterns**:
1. **Swipe-to-Open Detail**: Swipe right on table row to open side drawer (iOS/Android pattern)
2. **Swipe-to-Delete**: Swipe left to reveal delete action (common in list views)
3. **Pull-to-Refresh**: Pull down to refresh employee list (standard mobile pattern)
4. **Long-Press Context Menu**: Alternative to dropdown menu for touch devices

**Recommendation**:
1. **Phase 1**: Defer mobile gestures to Phase 7 (post-launch)
2. **Phase 7 Scope** (NEW):
   - Implement HammerJS for gesture detection
   - Add swipe-to-open for side drawer
   - Add swipe-to-delete with undo action
   - Add pull-to-refresh for table data
   - Estimated Effort: 2 days
3. **Alternative**: Basic touch support in Phase 4, advanced gestures in Phase 7

**Severity**: MEDIUM (not blocking for desktop-first rollout)
**Action**: Document as Phase 7 enhancement, do not promote to blocker

---

### Additional Risk #3: Pagination Performance at Scale (LOW → MONITOR)

**Context**:
- **Change**: Pagination increases from 12 → 50 per page (4.2× increase)
- **Current Query**: `User::with(['branch', 'roles', 'currentVehicleAssignment.vehicle'])->withCount(['incidents'])`
- **Estimated Complexity**: 50 users × 4 relationships = ~200 sub-queries per page

**Performance Projections**:
| Users in DB | Page Load (12/page) | Page Load (50/page) | Increase |
|-------------|---------------------|---------------------|----------|
| 100 | 150ms | 320ms | +113% |
| 500 | 180ms | 450ms | +150% |
| 1000 | 220ms | 650ms | +195% |

**Mitigation (Already in Rollout Plan)**:
- Section 2.4: Eager loading with `with()` (implemented ✅)
- Section 2.4: Selective column loading (proposed ✅)
- Section 2.4: Performance budget: <300ms for <500 users (✅)

**Additional Recommendations**:
1. **Monitor Performance**: Add Laravel Telescope query monitoring
2. **Add Index**: `users(name, employment_status, branch_id, created_at)` composite index
3. **Cache Count**: Cache `withCount(['incidents'])` for 1 hour
4. **Alternative**: Implement virtual scrolling if >100 employees (Phase 7)

**Severity**: LOW (already mitigated in rollout plan)
**Action**: Monitor performance in Phase 5, no immediate action required

---

### Additional Risk #4: Offline PWA Storage Limits (LOW)

**Context**:
- **Current Implementation**: IndexedDB with Dexie.js for offline storage
- **Blocker #3 Fix**: Adds `syncConflicts` table with full server + client data copies
- **Risk**: Storage quota exceeded for users with many offline edits

**IndexedDB Quota Limits**:
- **Desktop Browsers**: 10-50% of free disk space (typically 10-50 GB)
- **Mobile Browsers**: 10-50 MB (iOS Safari), 10% free space (Chrome Android)
- **Current Usage**: ~2 KB per incident, ~5 KB per conflict

**Calculation**:
- Worst case: 100 offline incidents + 20 conflicts = (100 × 2 KB) + (20 × 5 KB) = 300 KB
- Mobile limit: 10 MB (iOS Safari)
- **Capacity**: ~3,000 offline incidents before hitting iOS limit

**Recommendation**:
1. **Phase 3**: Add storage quota monitoring
2. **Phase 3**: Display warning at 80% quota usage
3. **Phase 5**: Implement quota exceeded error handling
4. **Phase 7**: Add "Sync and Clear" button for manual cleanup

**Severity**: LOW (unlikely to hit limits in normal usage)
**Action**: Add monitoring in Phase 3, no immediate blocker

---

## Verification Checklist

### Pre-Phase 1 Verification

**Blocker #1: SQL Injection (CRITICAL)**
- [ ] Unit tests pass for whitelist validation (8 test cases)
- [ ] Security tests pass for injection attempts
- [ ] Rate limiting configured and tested (60 req/min)
- [ ] Code review by Security Engineer
- [ ] Penetration test: SQL injection attempts blocked

**Blocker #2: Export Security (CRITICAL)**
- [ ] RBAC permission created and assigned
- [ ] Export method returns 403 for unauthorized users
- [ ] Audit log entries created for all exports
- [ ] GDPR notice displayed in export modal
- [ ] PII warning logged for full exports
- [ ] CSV/XLSX files generated correctly
- [ ] Security audit by external auditor

**Blocker #3: PWA Sync Conflicts (HIGH)**
- [ ] Migration adds `version` column to `users` table
- [ ] Backend returns HTTP 409 for version mismatch
- [ ] Frontend stores conflicts in `syncConflicts` table
- [ ] Conflict resolution modal displays correctly
- [ ] User can choose "Keep My Version" → Syncs with force
- [ ] User can choose "Keep Server Version" → Local updated
- [ ] Focus trap works in conflict modal (Tab/Shift+Tab)
- [ ] ESC key closes conflict modal

**Blocker #4: Feature Flags (HIGH)**
- [ ] Laravel Pennant installed and configured
- [ ] `DenseTableFeature` class created with 3-phase rollout
- [ ] TeamController uses feature flag to determine layout
- [ ] Blade view conditionally renders card vs dense table
- [ ] Phase 1: Only Adelaide HQ sees dense table
- [ ] Phase 2: ~50% users see dense table (A/B test)
- [ ] Phase 3: 100% users see dense table
- [ ] Emergency disable works instantly (`pennant:purge`)

**Blocker #5: Blade Components (MEDIUM)**
- [ ] `table-cell` component renders avatar + badge correctly
- [ ] `table-cell` component renders status badges with icons
- [ ] `table-cell` component renders action dropdowns
- [ ] `side-drawer` component slides in from right
- [ ] ESC key closes side drawer
- [ ] Backdrop click closes side drawer
- [ ] Focus trap prevents Tab from escaping drawer
- [ ] Previous focus restored on drawer close
- [ ] ARIA attributes present (`role="dialog"`, `aria-labelledby`, `aria-hidden`)

### Phase 1-6 Verification (from Rollout Plan)

**Phase 1: Foundation (5 days)**
- [ ] Pagination increased to 50 per page
- [ ] Sort parameter validated with whitelist
- [ ] Feature flag controls layout rendering
- [ ] Unit tests pass for all backend changes

**Phase 2: Component Build (5 days)**
- [ ] `<x-whs.dense-table>` component renders correctly
- [ ] `<x-whs.table-cell>` component all types work
- [ ] `<x-whs.side-drawer>` component keyboard accessible
- [ ] Component tests pass (Dusk)

**Phase 3: Integration (5 days)**
- [ ] Dense table integrated in TeamManagement
- [ ] Dense table integrated in ContractorManagement
- [ ] Side drawers work for user details
- [ ] PWA offline mode works with dense table

**Phase 4: Accessibility & Responsive (4 days)**
- [ ] WCAG 2.1 AA compliance verified (axe DevTools)
- [ ] Keyboard navigation works (Tab, Enter, ESC)
- [ ] Responsive breakpoints work (1024px, 768px, 480px)
- [ ] Performance budget met (<300ms for 500 users)

**Phase 5: Polish & Performance (5 days)**
- [ ] Row hover animations smooth
- [ ] Loading states implemented
- [ ] Error states handled gracefully
- [ ] Performance optimizations applied
- [ ] Lighthouse score: Performance 90+, Accessibility 100

**Phase 6: QA & Deployment (6 days)**
- [ ] All unit tests pass
- [ ] All integration tests pass
- [ ] All browser tests pass (Chrome, Firefox, Safari, Edge)
- [ ] Security audit completed (export + SQL injection)
- [ ] Documentation updated
- [ ] Deployment checklist completed
- [ ] Rollback plan tested

---

## Next Steps

### Immediate Actions (This Week)

1. **Management Approval**:
   - Review this remediation plan with project stakeholders
   - Approve 7-week timeline (extended from 6 weeks)
   - Approve 2-developer allocation for Pre-Phase 1
   - Approve external security audit budget (Phase 6)

2. **Resource Allocation**:
   - Assign Backend Team Lead to Blockers #1, #2, #4
   - Assign Frontend Specialist to Blockers #3, #5
   - Schedule Security Engineer for code review (Day 4-5)
   - Schedule QA Engineer for testing (Day 4-5)

3. **Preparation**:
   - Create Git feature branch: `feature/dense-table-blockers`
   - Set up Jira/Trello board with 13 blocker subtasks
   - Schedule daily standups for Pre-Phase 1 week
   - Prepare test environments (staging + QA)

### Pre-Phase 1 Execution (Week 1)

**Day 1 (Monday)**:
- Backend Lead: Start Blocker #1 (SQL injection fix)
- Frontend Spec: Start Blocker #5 (Blade component templates)
- Setup: Create feature branch, prepare test data

**Day 2 (Tuesday)**:
- Backend Lead: Complete Blocker #1, start Blocker #4 (feature flags)
- Frontend Spec: Continue Blocker #5

**Day 3 (Wednesday)**:
- Backend Lead: Complete Blocker #4, start Blocker #2 (export security)
- Frontend Spec: Complete Blocker #5, start Blocker #3 (PWA sync conflicts)

**Day 4 (Thursday)**:
- Backend Lead: Continue Blocker #2
- Frontend Spec: Continue Blocker #3
- Security Eng: Start code review
- QA Eng: Start testing completed blockers

**Day 5 (Friday)**:
- Backend Lead: Complete Blocker #2
- Frontend Spec: Complete Blocker #3
- Security Eng: Complete code review, document findings
- QA Eng: Complete testing, sign off on fixes
- All: Final verification checklist review

### Phase 1-6 Execution (Weeks 2-7)

Follow original rollout plan timeline with these modifications:
- **Phase 1**: Integrate Pre-Phase 1 fixes (no code changes, just configuration)
- **Phase 4**: Skip column drag-and-drop (defer to Phase 7)
- **Phase 6**: Add 1 day for external security audit

### Post-Launch Enhancements (Phase 7)

**Timeline**: Week 8-9 (2 weeks)
**Scope** (Deferred from Phases 4 & 6):
1. Column drag-and-drop reordering (2 days)
2. Mobile touch gesture patterns (2 days)
3. Virtual scrolling for >100 employees (2 days)
4. Advanced export formats (PDF, JSON) (1 day)
5. User preference persistence (1 day)
6. Performance monitoring dashboard (2 days)

---

## Conclusion

**Readiness Status**:
- **Current State**: 84/100 (8 blockers identified)
- **Post-Remediation**: 94/100 (5 blockers fixed, 3 deferred to Phase 7)
- **Production-Ready**: 98/100 (after Phase 1-6 completion)

**Confidence Level**:
- **Technical Feasibility**: HIGH (all blockers have proven solutions)
- **Timeline Realism**: HIGH (10 dev-days spread over 5 calendar days with 2 developers)
- **Quality Assurance**: HIGH (comprehensive verification checklist with 50+ test cases)

**Critical Success Factors**:
1. ✅ Security Engineer available for code review (Day 4-5)
2. ✅ Backend Lead and Frontend Specialist dedicated full-time (Week 1)
3. ✅ Management approves 7-week timeline extension
4. ✅ External security auditor scheduled for Phase 6

**Recommended Go/No-Go Decision**:
- **GO** if all 5 blockers pass verification checklist by end of Pre-Phase 1 Week
- **NO-GO** if any CRITICAL blocker (#1 or #2) fails security audit
- **CONDITIONAL GO** if HIGH blocker (#3 or #4) needs additional work (can continue with risk acceptance)

---

**Document Version**: 1.0
**Last Updated**: 2025-02-01
**Next Review**: After Pre-Phase 1 completion (Week 1 Friday)
**Owner**: Backend Team Lead (primary author) + Frontend Specialist (contributor)
**Approvers**: CTO, Security Lead, Project Manager
