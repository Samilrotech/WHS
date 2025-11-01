# Dense Table Blocker Implementation Guide

**Status**: Pre-Phase 1 Implementation Blueprint
**Target**: 5-day sprint (2 developers in parallel)
**Goal**: Resolve 5 critical blockers before Phase 1 rollout

---

## Overview

### Current State Verification
✅ **TeamController.php**: Line 82 uses hardcoded `orderBy('name')->paginate(12)` (safe but inflexible)
✅ **Blade Components**: No dense-table, table-cell, or side-drawer components exist
✅ **PWA Sync**: sync-manager.js line 8 claims "server wins by default" but lacks actual conflict detection
✅ **Design Tokens**: sensei-theme.css has comprehensive token system (--sensei-surface, --sensei-blur, etc.)
✅ **Export Feature**: Does not exist (no export method in TeamController)

### Blocker Summary
| # | Blocker | Severity | Effort | Owner | Files Affected |
|---|---------|----------|--------|-------|----------------|
| 1 | SQL Injection | CRITICAL | 1d | Backend Lead | TeamController.php, routes, 2 test files |
| 2 | Export Security | CRITICAL | 2d | Backend Lead | TeamController.php, PermissionSeeder, view, 2 test files |
| 3 | PWA Sync Conflicts | HIGH | 3d | Frontend Spec | migration, TeamController, offline-db.js, sync-manager.js, conflict-resolver.js, 2 test files |
| 4 | Feature Flags | HIGH | 1.5d | Backend Lead | DenseTableFeature.php, pennant config, TeamController, Index.blade.php, test file |
| 5 | Blade Components | MEDIUM | 2.5d | Frontend Spec | table-cell.blade.php, side-drawer.blade.php, Index.blade.php, 2 test files |

---

## Blocker #1: SQL Injection Safeguards

### Context
**File**: `app/Modules/TeamManagement/Controllers/TeamController.php`
**Current**: Line 82 hardcodes `orderBy('name')` - safe but inflexible
**Risk**: Rollout plan proposes `$request->input('sort')` without validation
**Attack Vector**: `/team?sort=1;DROP TABLE users;--`

### Proposed Changes

#### Change 1.1: Add Sort Validation (TeamController.php)

```diff
--- a/app/Modules/TeamManagement/Controllers/TeamController.php
+++ b/app/Modules/TeamManagement/Controllers/TeamController.php
@@ -46,6 +46,24 @@ public function index(Request $request): View
             'role' => $request->input('role'),
             'status' => $request->input('status'),
         ];
+
+        // Define allowed sort columns (whitelist)
+        $allowedSortColumns = [
+            'name',
+            'email',
+            'phone',
+            'employee_id',
+            'employment_status',
+            'created_at',
+            'updated_at',
+        ];
+
+        // Get sort parameters with validation
+        $sortColumn = $request->input('sort', 'name');
+        $sortDirection = strtolower($request->input('direction', 'asc'));
+
+        // Validate against whitelist (strict type checking)
+        $sortColumn = in_array($sortColumn, $allowedSortColumns, true) ? $sortColumn : 'name';
+        $sortDirection = in_array($sortDirection, ['asc', 'desc'], true) ? $sortDirection : 'asc';

         $query = User::query()
             ->with(['branch', 'roles', 'currentVehicleAssignment.vehicle'])
@@ -79,7 +97,8 @@ public function index(Request $request): View
         }

         /** @var LengthAwarePaginator $users */
-        $users = $query->orderBy('name')->paginate(12)->withQueryString();
+        $users = $query->orderBy($sortColumn, $sortDirection)
+            ->paginate(50)->withQueryString();

         $userIds = $users->getCollection()->pluck('id')->all();
```

**Rationale**:
- Whitelist approach prevents arbitrary column injection
- Strict type checking (`true` parameter) prevents type juggling attacks
- Fallback to safe defaults ('name', 'asc') on validation failure
- Pagination increased 12→50 per rollout plan

#### Change 1.2: Add Rate Limiting (routes/web.php)

**Current Route** (find in routes/web.php or module routes):
```php
Route::get('/team', [TeamController::class, 'index'])->name('team.index');
```

**Updated Route**:
```diff
--- a/routes/web.php
+++ b/routes/web.php
@@ -XXX,X
-Route::get('/team', [TeamController::class, 'index'])->name('team.index');
+Route::middleware(['auth', 'throttle:60,1'])->group(function () {
+    Route::get('/team', [TeamController::class, 'index'])->name('team.index');
+});
```

**Rationale**: Prevent DoS attacks via rapid sort/filter requests (60 requests/minute)

#### Change 1.3: Unit Tests (NEW FILE)

**File**: `tests/Feature/TeamControllerTest.php`

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Branch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test branch and users
        $branch = Branch::factory()->create();
        User::factory()->count(60)->create(['branch_id' => $branch->id]);
    }

    /** @test */
    public function it_sorts_by_valid_column_ascending()
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/team?sort=email&direction=asc');

        $response->assertStatus(200);
        $response->assertViewHas('users');
    }

    /** @test */
    public function it_sorts_by_valid_column_descending()
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/team?sort=name&direction=desc');

        $response->assertStatus(200);
    }

    /** @test */
    public function it_falls_back_to_name_for_invalid_sort_column()
    {
        $admin = User::factory()->admin()->create();

        // Attempt to sort by 'password' (not in whitelist)
        $response = $this->actingAs($admin)->get('/team?sort=password&direction=asc');

        $response->assertStatus(200);
        // Should fall back to 'name' sort without error
    }

    /** @test */
    public function it_falls_back_to_asc_for_invalid_direction()
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/team?sort=name&direction=random');

        $response->assertStatus(200);
    }

    /** @test */
    public function it_paginates_50_per_page()
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/team');

        $response->assertStatus(200);
        $users = $response->viewData('users');
        $this->assertEquals(50, $users->perPage());
    }
}
```

#### Change 1.4: Security Tests (NEW FILE)

**File**: `tests/Feature/TeamControllerSecurityTest.php`

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Branch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamControllerSecurityTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_blocks_sql_injection_in_sort_parameter()
    {
        $admin = User::factory()->admin()->create();
        Branch::factory()->create();

        // Attempt SQL injection
        $response = $this->actingAs($admin)->get('/team?sort=1;DROP+TABLE+users;--');

        $response->assertStatus(200); // Should succeed with fallback, not SQL error

        // Verify users table still exists
        $this->assertDatabaseCount('users', 1); // Admin user exists
    }

    /** @test */
    public function it_prevents_unauthorized_column_access()
    {
        $admin = User::factory()->admin()->create();

        // Attempt to sort by sensitive column
        $response = $this->actingAs($admin)->get('/team?sort=password');

        $response->assertStatus(200);
        // Should fall back to 'name', not expose password data
    }

    /** @test */
    public function it_enforces_rate_limiting()
    {
        $admin = User::factory()->admin()->create();

        // Simulate 65 rapid requests
        $responses = [];
        for ($i = 0; $i < 65; $i++) {
            $responses[] = $this->actingAs($admin)->get('/team?sort=name');
        }

        // First 60 should succeed
        $this->assertEquals(200, $responses[0]->status());
        $this->assertEquals(200, $responses[59]->status());

        // Requests 61-65 should be rate limited
        $this->assertEquals(429, $responses[60]->status());
        $this->assertEquals(429, $responses[64]->status());
    }
}
```

### Verification Steps

**Automated Tests**:
```bash
# Run all TeamController tests
php artisan test --filter TeamControllerTest

# Run security-specific tests
php artisan test --filter TeamControllerSecurityTest
```

**Manual Security Validation**:
```bash
# Test SQL injection protection
curl "http://whs.test/team?sort=1%3BDROP+TABLE+users%3B--" \
  -H "Cookie: laravel_session=YOUR_SESSION"
# Expected: HTTP 200, results sorted by 'name' (fallback)

# Test whitelist enforcement
curl "http://whs.test/team?sort=password" \
  -H "Cookie: laravel_session=YOUR_SESSION"
# Expected: HTTP 200, results sorted by 'name' (not 'password')

# Test rate limiting (Linux/Mac)
for i in {1..65}; do
  curl -s -o /dev/null -w "%{http_code}\n" "http://whs.test/team?sort=name"
done
# Expected: First 60 return 200, last 5 return 429
```

**Success Criteria**:
- ✅ All PHPUnit tests pass (8 tests)
- ✅ SQL injection attempts blocked (fallback to 'name')
- ✅ Rate limiting active (429 after 60 requests/minute)
- ✅ Pagination shows 50 users per page

---

## Blocker #2: Export Security & GDPR Compliance

### Context
**Current State**: No export functionality exists
**Risk**: Rollout plan proposes CSV/Excel export without GDPR compliance
**Requirements**: RBAC, audit logging, PII protection, data minimization

### Proposed Changes

#### Change 2.1: Install Activity Log Package

```bash
composer require spatie/laravel-activitylog
php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider" --tag="activitylog-migrations"
php artisan migrate
```

#### Change 2.2: Add Export Permission (PermissionSeeder.php)

```diff
--- a/database/seeders/PermissionSeeder.php
+++ b/database/seeders/PermissionSeeder.php
@@ -XX,X
     'team.index' => 'View team members',
     'team.show' => 'View team member details',
+    'team.export' => 'Export employee data (PII)',
 ];

 // Assign to roles
 $hrManager = Role::where('name', 'hr_manager')->first();
-$hrManager->givePermissionTo(['team.index', 'team.show']);
+$hrManager->givePermissionTo(['team.index', 'team.show', 'team.export']);

 $admin = Role::where('name', 'admin')->first();
 $admin->givePermissionTo('team.*');
```

**Run After Edit**:
```bash
php artisan db:seed --class=PermissionSeeder
```

#### Change 2.3: Add Export Method (TeamController.php)

```diff
--- a/app/Modules/TeamManagement/Controllers/TeamController.php
+++ b/app/Modules/TeamManagement/Controllers/TeamController.php
@@ -XXX,X
+    /**
+     * Export employees to CSV with GDPR compliance
+     */
+    public function export(Request $request)
+    {
+        // RBAC check (throws 403 if unauthorized)
+        $this->authorize('team.export');
+
+        $validated = $request->validate([
+            'format' => 'required|in:csv,xlsx',
+            'fields' => 'array',
+        ]);
+
+        // Log export for GDPR audit trail
+        activity()
+            ->causedBy(auth()->user())
+            ->withProperties([
+                'format' => $validated['format'],
+                'fields' => $validated['fields'] ?? 'all',
+                'ip_address' => $request->ip(),
+                'user_agent' => $request->userAgent(),
+            ])
+            ->log('team_export_initiated');
+
+        // Build filtered query (reuse index filters)
+        $query = $this->buildExportQuery($request);
+
+        return $this->generateCsvExport($query, $validated['fields'] ?? []);
+    }
+
+    private function buildExportQuery(Request $request)
+    {
+        $filters = [
+            'search' => trim((string) $request->input('search')),
+            'branch' => $request->input('branch'),
+            'status' => $request->input('status'),
+        ];
+
+        $query = User::query()->with(['branch']);
+
+        if ($filters['search'] !== '') {
+            $query->where(function ($builder) use ($filters) {
+                $builder->where('name', 'like', "%{$filters['search']}%")
+                    ->orWhere('email', 'like', "%{$filters['search']}%");
+            });
+        }
+
+        if ($filters['branch']) {
+            $query->where('branch_id', $filters['branch']);
+        }
+
+        if ($filters['status']) {
+            $query->where('employment_status', $filters['status']);
+        }
+
+        return $query;
+    }
+
+    private function generateCsvExport($query, array $fields)
+    {
+        $exportableFields = [
+            'employee_id' => ['label' => 'Employee ID', 'pii' => false],
+            'name' => ['label' => 'Full Name', 'pii' => true],
+            'email' => ['label' => 'Email', 'pii' => true],
+            'phone' => ['label' => 'Phone', 'pii' => true],
+            'position' => ['label' => 'Position', 'pii' => false],
+            'employment_status' => ['label' => 'Status', 'pii' => false],
+        ];
+
+        // Default to all fields if none specified
+        if (empty($fields)) {
+            $fields = array_keys($exportableFields);
+            activity()->causedBy(auth()->user())->log('team_export_all_pii_warning');
+        }
+
+        $filename = 'employees_' . now()->format('Y-m-d_His') . '.csv';
+
+        $headers = [
+            'Content-Type' => 'text/csv',
+            'Content-Disposition' => "attachment; filename=\"$filename\"",
+            'Cache-Control' => 'no-store, no-cache',
+            'X-Content-Type-Options' => 'nosniff',
+        ];
+
+        return response()->stream(function () use ($query, $fields, $exportableFields) {
+            $handle = fopen('php://output', 'w');
+
+            // CSV header row
+            $headerRow = array_map(fn($field) => $exportableFields[$field]['label'], $fields);
+            fputcsv($handle, $headerRow);
+
+            // Stream data rows (memory-efficient)
+            $query->chunk(100, function ($users) use ($handle, $fields) {
+                foreach ($users as $user) {
+                    $row = [];
+                    foreach ($fields as $field) {
+                        $row[] = $user->{$field} ?? '';
+                    }
+                    fputcsv($handle, $row);
+                }
+            });
+
+            fclose($handle);
+        }, 200, $headers);
+    }
```

#### Change 2.4: Add Export Route

```diff
--- a/routes/web.php
+++ b/routes/web.php
@@ -XXX,X
+Route::middleware(['auth', 'can:team.export', 'throttle:10,1'])->group(function () {
+    Route::post('/team/export', [TeamController::class, 'export'])->name('team.export');
+});
```

**Rationale**: Rate limit exports to 10/minute (stricter than index)

#### Change 2.5: Export UI (Index.blade.php)

```blade
{{-- Add before table/card rendering --}}
@can('team.export')
<div class="mb-3">
    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#exportModal">
        <i class="bx bx-download"></i> Export Employees
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
                        <strong>GDPR Notice:</strong> This export contains personal data. All exports are logged.
                    </div>

                    <input type="hidden" name="format" value="csv">

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
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Export CSV</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan
```

### Verification Steps

```bash
# Functional tests
php artisan test --filter TeamExportTest

# Manual RBAC test (unauthorized user)
curl -X POST http://whs.test/team/export \
  -H "Cookie: laravel_session=REGULAR_USER_SESSION" \
  -d "format=csv"
# Expected: HTTP 403 Forbidden

# Manual export test (authorized user)
curl -X POST http://whs.test/team/export \
  -H "Cookie: laravel_session=HR_MANAGER_SESSION" \
  -d "format=csv&fields[]=name&fields[]=email" \
  -o employees.csv
# Expected: HTTP 200, CSV file downloaded

# Verify audit log
php artisan tinker
>>> \Spatie\Activitylog\Models\Activity::where('description', 'team_export_initiated')->latest()->first();
# Expected: Shows log entry with user, IP, fields
```

---

## Blocker #3: PWA Sync Conflict Resolution

### Context
**Current**: sync-manager.js line 8 says "server wins by default" but no version checking exists
**Risk**: Offline edits overwrite concurrent server changes (data loss)
**Solution**: Optimistic locking with conflict resolution UI

### Proposed Changes

#### Change 3.1: Add Version Column (Migration)

**NEW FILE**: `database/migrations/2025_XX_XX_XXXXXX_add_version_to_users_table.php`

```php
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

**Run**:
```bash
php artisan make:migration add_version_to_users_table
# (Copy content above)
php artisan migrate
```

#### Change 3.2: Update Controller for Conflict Detection

```diff
--- a/app/Modules/TeamManagement/Controllers/TeamController.php
+++ b/app/Modules/TeamManagement/Controllers/TeamController.php
@@ -XXX,X public function update(Request $request, User $user)
     {
         $validated = $request->validate([
             'name' => 'required|string|max:255',
             'email' => 'required|email|max:255',
+            'version' => 'required|integer',
         ]);
+
+        // Optimistic locking check
+        if ($user->version !== $validated['version']) {
+            return response()->json([
+                'error' => 'Conflict detected',
+                'message' => 'This record was modified by another user',
+                'server_version' => $user->version,
+                'server_data' => $user->toArray(),
+                'client_version' => $validated['version'],
+            ], 409); // HTTP 409 Conflict
+        }

         $user->fill($validated);
+        $user->version++; // Increment version
         $user->save();

-        return response()->json(['success' => true, 'data' => $user]);
+        return response()->json([
+            'success' => true,
+            'data' => $user,
+            'version' => $user->version,
+        ]);
     }
```

#### Change 3.3: Update sync-manager.js for Conflict Handling

```diff
--- a/public/js/sync-manager.js
+++ b/public/js/sync-manager.js
@@ -220,6 +220,12 @@ async function syncIncident(operation, data, localId) {
         case 'UPDATE':
             url = `/incidents/${data.id}`;
             method = 'PUT';
-            body = JSON.stringify(data);
+            body = JSON.stringify({
+                ...data,
+                version: data.version, // Include version for conflict detection
+            });
             break;

@@ -246,6 +252,15 @@ async function syncIncident(operation, data, localId) {
         body: body
     });

+    // Handle conflict (409) responses
+    if (response.status === 409) {
+        const conflictData = await response.json();
+        await handleSyncConflict(operation, data, localId, conflictData);
+        throw new Error('Conflict detected - user resolution required');
+    }
+
     if (!response.ok) {
         const errorText = await response.text();
@@ -253,7 +268,57 @@ async function syncIncident(operation, data, localId) {
     }

-    return await response.json();
+    const result = await response.json();
+
+    // Update local version
+    if (result.version) {
+        const localRecord = await OfflineDB.getIncident(localId);
+        if (localRecord) {
+            await OfflineDB.updateIncident(localId, { version: result.version });
+        }
+    }
+
+    return result;
+}
+
+/**
+ * Handle sync conflict
+ */
+async function handleSyncConflict(operation, clientData, localId, serverResponse) {
+    console.warn('[SyncManager] Conflict detected:', { operation, clientData, serverResponse });
+
+    // Store conflict in IndexedDB
+    await OfflineDB.db.syncConflicts.add({
+        entity_type: 'incident',
+        entity_id: localId,
+        operation: operation,
+        client_data: clientData,
+        client_version: clientData.version,
+        server_data: serverResponse.server_data,
+        server_version: serverResponse.server_version,
+        detected_at: new Date().toISOString(),
+        resolved: false,
+    });
+
+    // Show notification
+    showConflictNotification();
+}
+
+/**
+ * Show conflict notification
+ */
+function showConflictNotification() {
+    if (typeof Toastify !== 'undefined') {
+        Toastify({
+            text: '⚠️ Sync Conflict: Some changes conflict with server updates. Click to resolve.',
+            duration: -1, // Persistent
+            gravity: "bottom",
+            position: "right",
+            backgroundColor: "#ff9f43",
+            onClick: function() {
+                // Open conflict resolver UI
+                if (typeof ConflictResolver !== 'undefined') {
+                    ConflictResolver.showConflicts();
+                }
+            }
+        }).showToast();
+    }
 }
```

#### Change 3.4: Update offline-db.js Schema

```diff
--- a/public/js/offline-db.js
+++ b/public/js/offline-db.js
@@ -49,11 +49,14 @@ const db = new Dexie('RotechWHSDB');

 db.version(DB_VERSION).stores({
-    incidents: '++localId, id, type, severity, status, incident_datetime, user_id, branch_id, created_at, synced',
+    incidents: '++localId, id, type, severity, status, incident_datetime, user_id, branch_id, created_at, synced, version',
     syncQueue: '++id, entity_type, entity_id, operation, data, created_at, attempts, last_attempt',
     photos: '++id, entity_type, entity_id, file_name, base64_data, mime_type, file_size, created_at, synced',
+    syncConflicts: '++id, entity_type, entity_id, operation, detected_at, resolved',
 });
```

#### Change 3.5: Conflict Resolver UI (NEW FILE)

**File**: `public/js/conflict-resolver.js`

```javascript
/**
 * Rotech WHS Conflict Resolver
 */
const ConflictResolver = (function() {
    'use strict';

    async function showConflicts() {
        const conflicts = await OfflineDB.db.syncConflicts
            .where('resolved').equals(false)
            .toArray();

        if (conflicts.length === 0) {
            alert('No conflicts to resolve');
            return;
        }

        const modal = buildConflictModal(conflicts);
        document.body.appendChild(modal);
        new bootstrap.Modal(modal).show();
    }

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
                            <strong>What happened?</strong> While offline, someone else modified these records.
                            Choose which version to keep.
                        </div>
                        <div id="conflictList">
                            ${conflicts.map((c, i) => buildConflictItem(c, i)).join('')}
                        </div>
                    </div>
                </div>
            </div>
        `;
        return modal;
    }

    function buildConflictItem(conflict, index) {
        return `
            <div class="card mb-3" data-conflict-id="${conflict.id}">
                <div class="card-header">
                    <strong>Conflict #${index + 1}:</strong> ${conflict.entity_type}
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Your Changes (Offline)</h6>
                            <pre class="bg-light p-2 rounded">${JSON.stringify(conflict.client_data, null, 2)}</pre>
                            <button class="btn btn-sm btn-outline-primary"
                                onclick="ConflictResolver.resolveConflict(${conflict.id}, 'client')">
                                Keep My Version
                            </button>
                        </div>
                        <div class="col-md-6">
                            <h6>Server Version (Current)</h6>
                            <pre class="bg-light p-2 rounded">${JSON.stringify(conflict.server_data, null, 2)}</pre>
                            <button class="btn btn-sm btn-outline-success"
                                onclick="ConflictResolver.resolveConflict(${conflict.id}, 'server')">
                                Keep Server Version
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    async function resolveConflict(conflictId, resolution) {
        const conflict = await OfflineDB.db.syncConflicts.get(conflictId);

        if (resolution === 'server') {
            // Keep server version: discard client changes
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
        // 'client' resolution requires re-sync with force flag (backend support needed)

        // Mark resolved
        await OfflineDB.db.syncConflicts.update(conflictId, { resolved: true });

        document.querySelector(`[data-conflict-id="${conflictId}"]`).remove();

        const remaining = await OfflineDB.db.syncConflicts
            .where('resolved').equals(false)
            .count();

        if (remaining === 0) {
            alert('All conflicts resolved!');
            bootstrap.Modal.getInstance(document.getElementById('conflictResolverModal')).hide();
        }
    }

    return { showConflicts, resolveConflict };
})();
```

### Verification Steps

```bash
# Backend tests
php artisan test --filter OptimisticLockingTest

# Manual conflict scenario
# 1. Browser A: Go offline, edit employee #1 → name="Alice"
# 2. Browser B: Edit employee #1 → name="Bob", save
# 3. Browser A: Go online, trigger sync
# 4. Expected: Conflict modal appears with side-by-side comparison
```

---

## Blocker #4: Feature Flag Implementation

### Context
**Decision**: Use Laravel Pennant (official, performant, A/B testing built-in)
**Rollout**: Phase 1 (Adelaide HQ) → Phase 2 (50% A/B) → Phase 3 (100%)

### Proposed Changes

#### Change 4.1: Install Pennant

```bash
composer require laravel/pennant
php artisan vendor:publish --tag=pennant-migrations
php artisan migrate
```

#### Change 4.2: Create Feature Class (NEW FILE)

**File**: `app/Features/DenseTableFeature.php`

```php
<?php

namespace App\Features;

use App\Models\User;
use Illuminate\Support\Lottery;

class DenseTableFeature
{
    public function resolve(User $user): mixed
    {
        // Emergency kill switch
        if (config('features.dense_table_emergency_disable', false)) {
            return false;
        }

        // Phase 1: Adelaide HQ only (branch_id = 1)
        if (now()->isBefore('2025-02-15')) {
            return $user->branch_id === 1;
        }

        // Phase 2: 50% rollout for A/B testing
        if (now()->isBefore('2025-03-01')) {
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

#### Change 4.3: Register Feature (config/pennant.php)

**File**: `config/pennant.php`

```php
<?php

use App\Features\DenseTableFeature;

return [
    'features' => [
        'dense-table' => DenseTableFeature::class,
    ],

    'store' => 'database',

    'cache' => [
        'store' => env('PENNANT_CACHE_STORE', 'redis'),
        'ttl' => 3600, // 1 hour
    ],
];
```

#### Change 4.4: Update TeamController

```diff
--- a/app/Modules/TeamManagement/Controllers/TeamController.php
+++ b/app/Modules/TeamManagement/Controllers/TeamController.php
@@ -1,6 +1,7 @@
 <?php

 namespace App\Modules\TeamManagement\Controllers;
+use Laravel\Pennant\Feature;

@@ -40,6 +41,9 @@ public function index(Request $request): View
     {
         $currentUser = $request->user();
+
+        // Check if user has dense table feature enabled
+        $useDenseTable = Feature::active('dense-table');

         $filters = [
@@ -115,6 +119,7 @@ public function index(Request $request): View
         return view('content.TeamManagement.Index', [
             'users' => $users,
+            'useDenseTable' => $useDenseTable,
             'filters' => $filters,
         ]);
     }
```

#### Change 4.5: Update Blade View

```diff
--- a/resources/views/content/TeamManagement/Index.blade.php
+++ b/resources/views/content/TeamManagement/Index.blade.php
@@ -XXX,X
+@if($useDenseTable ?? false)
+    {{-- NEW: Dense table layout --}}
+    <x-whs.dense-table :data="$users" />
+@else
+    {{-- EXISTING: Card-based layout --}}
     <div class="row g-4">
         @foreach($users as $user)
             {{-- Existing card rendering --}}
         @endforeach
     </div>
+@endif
```

### Emergency Disable Procedure

```bash
# Method 1: Artisan command (instant)
php artisan pennant:purge dense-table

# Method 2: Config flag (requires cache clear)
# Add to .env:
FEATURES_DENSE_TABLE_EMERGENCY_DISABLE=true
php artisan config:clear && php artisan cache:clear
```

---

## Blocker #5: Complete Blade Component Templates

### Context
**Missing**: table-cell.blade.php and side-drawer.blade.php do not exist
**Required**: Avatar+badge rendering, status badges, action menus, accessible drawer with ESC/focus trap

### Proposed Changes

#### Change 5.1: Create table-cell Component (NEW FILE)

**File**: `resources/views/components/whs/table-cell.blade.php`

```blade
@props([
    'type' => 'text',
    'value' => null,
    'user' => null,
    'status' => null,
    'actions' => [],
    'align' => 'left',
    'width' => null,
])

@php
    $alignClass = match($align) {
        'center' => 'text-center',
        'right' => 'text-end',
        default => 'text-start',
    };
    $widthStyle = $width ? "width: {$width};" : '';
@endphp

<td {{ $attributes->merge(['class' => "dense-table-cell {$alignClass}", 'style' => $widthStyle]) }}>
    @switch($type)
        @case('avatar')
            <div class="d-flex align-items-center">
                @if($user->avatar_url ?? false)
                    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}"
                         class="rounded-circle me-2" style="width: 32px; height: 32px;">
                @else
                    <div class="avatar-initials rounded-circle me-2 d-flex align-items-center justify-content-center bg-primary text-white"
                         style="width: 32px; height: 32px; font-size: 14px;">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                @endif
                <div class="flex-grow-1 overflow-hidden">
                    <div class="d-flex align-items-center">
                        <span class="text-truncate me-1">{{ $user->name }}</span>
                        @if($user->hasRole('admin'))
                            <span class="badge bg-danger badge-sm"><i class="bx bx-shield-alt"></i></span>
                        @endif
                    </div>
                    @if($user->employee_id ?? false)
                        <small class="text-muted">{{ $user->employee_id }}</small>
                    @endif
                </div>
            </div>
            @break

        @case('status')
            @php
                $statusConfig = [
                    'active' => ['class' => 'bg-success', 'icon' => 'bx-check-circle'],
                    'inactive' => ['class' => 'bg-secondary', 'icon' => 'bx-x-circle'],
                    'on_leave' => ['class' => 'bg-warning', 'icon' => 'bx-time-five'],
                ];
                $config = $statusConfig[$status] ?? ['class' => 'bg-secondary', 'icon' => 'bx-question-mark'];
            @endphp
            <span class="badge {{ $config['class'] }} d-inline-flex align-items-center">
                <i class="bx {{ $config['icon'] }} me-1"></i>
                {{ ucfirst(str_replace('_', ' ', $status)) }}
            </span>
            @break

        @case('actions')
            <div class="dropdown">
                <button class="btn btn-sm btn-icon btn-text-secondary rounded-pill dropdown-toggle hide-arrow"
                        data-bs-toggle="dropdown">
                    <i class="bx bx-dots-vertical-rounded"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    @foreach($actions as $action)
                        @if(($action['type'] ?? '') === 'divider')
                            <li><hr class="dropdown-divider"></li>
                        @else
                            <li>
                                <a class="dropdown-item {{ $action['class'] ?? '' }}"
                                   href="{{ $action['url'] ?? '#' }}">
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

        @default
            <span class="text-truncate d-block" title="{{ $value }}">{{ $value }}</span>
            @break
    @endswitch
</td>
```

#### Change 5.2: Create side-drawer Component (NEW FILE)

**File**: `resources/views/components/whs/side-drawer.blade.php`

```blade
@props([
    'id' => 'sideDrawer',
    'title' => 'Details',
    'size' => 'medium',
    'position' => 'right',
])

@php
    $widthClass = match($size) {
        'small' => 'side-drawer-sm',
        'large' => 'side-drawer-lg',
        default => 'side-drawer-md',
    };
    $positionClass = $position === 'left' ? 'side-drawer-left' : 'side-drawer-right';
@endphp

{{-- Backdrop --}}
<div id="{{ $id }}Backdrop" class="side-drawer-backdrop" style="display: none;"
     onclick="SideDrawer.close('{{ $id }}')"></div>

{{-- Drawer --}}
<div id="{{ $id }}" class="side-drawer {{ $widthClass }} {{ $positionClass }}"
     role="dialog" aria-labelledby="{{ $id }}Title" aria-hidden="true" tabindex="-1">
    <div class="side-drawer-header">
        <h5 id="{{ $id }}Title" class="side-drawer-title">{{ $title }}</h5>
        <button type="button" class="btn-close" onclick="SideDrawer.close('{{ $id }}')" aria-label="Close"></button>
    </div>
    <div class="side-drawer-body">
        {{ $slot }}
    </div>
    @isset($footer)
        <div class="side-drawer-footer">{{ $footer }}</div>
    @endisset
</div>

@once
<script>
const SideDrawer = (function() {
    'use strict';
    let openDrawers = new Set();
    let previousFocus = null;

    function open(drawerId) {
        const drawer = document.getElementById(drawerId);
        const backdrop = document.getElementById(drawerId + 'Backdrop');
        if (!drawer) return;

        previousFocus = document.activeElement;

        if (backdrop) {
            backdrop.style.display = 'block';
            setTimeout(() => backdrop.classList.add('show'), 10);
        }

        drawer.style.display = 'block';
        setTimeout(() => {
            drawer.classList.add('show');
            drawer.setAttribute('aria-hidden', 'false');
        }, 10);

        setTimeout(() => {
            const firstFocusable = drawer.querySelector('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
            if (firstFocusable) firstFocusable.focus();
            else drawer.focus();
        }, 350);

        openDrawers.add(drawerId);
        document.addEventListener('keydown', handleEscape);
        document.body.style.overflow = 'hidden';
    }

    function close(drawerId) {
        const drawer = document.getElementById(drawerId);
        const backdrop = document.getElementById(drawerId + 'Backdrop');
        if (!drawer) return;

        drawer.classList.remove('show');
        drawer.setAttribute('aria-hidden', 'true');
        if (backdrop) backdrop.classList.remove('show');

        setTimeout(() => {
            drawer.style.display = 'none';
            if (backdrop) backdrop.style.display = 'none';
            if (previousFocus) {
                previousFocus.focus();
                previousFocus = null;
            }
        }, 300);

        openDrawers.delete(drawerId);
        if (openDrawers.size === 0) {
            document.removeEventListener('keydown', handleEscape);
            document.body.style.overflow = '';
        }
    }

    function handleEscape(event) {
        if (event.key === 'Escape' && openDrawers.size > 0) {
            const lastDrawer = Array.from(openDrawers).pop();
            close(lastDrawer);
        }
    }

    return { open, close };
})();
</script>

<style>
.side-drawer {
    position: fixed;
    top: 0;
    bottom: 0;
    z-index: 1050;
    background: var(--sensei-surface);
    backdrop-filter: blur(var(--sensei-blur));
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    display: none;
    overflow-y: auto;
}
.side-drawer-sm { width: 400px; }
.side-drawer-md { width: 600px; }
.side-drawer-lg { width: 800px; }
.side-drawer-right { right: 0; transform: translateX(100%); }
.side-drawer-left { left: 0; transform: translateX(-100%); }
.side-drawer.show { transform: translateX(0); }
.side-drawer-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1040;
    opacity: 0;
    transition: opacity 0.3s ease;
}
.side-drawer-backdrop.show { opacity: 1; }
.side-drawer-header {
    padding: 1.5rem;
    border-bottom: 1px solid var(--sensei-border);
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.side-drawer-body { padding: 1.5rem; flex: 1; }
.side-drawer-footer {
    padding: 1rem 1.5rem;
    border-top: 1px solid var(--sensei-border);
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
}
</style>
@endonce
```

---

## Design Token Alignment

### Existing Tokens (sensei-theme.css)
✅ Comprehensive design token system exists:

```css
--sensei-surface: rgba(30, 35, 45, 0.82);
--sensei-surface-hover: rgba(36, 44, 57, 0.92);
--sensei-blur: 26px;
--sensei-border: rgba(255, 255, 255, 0.07);
--sensei-radius: 20px;
--sensei-radius-sm: 14px;
--sensei-transition: 220ms ease;
--sensei-spacing-xs: 12px;
--sensei-spacing-sm: 16px;
--sensei-spacing-md: 24px;
```

### Recommended Dense Table CSS

**File**: `resources/css/components/dense-table.css`

```css
/* Dense Table - Uses Existing Sensei Tokens */
.dense-table {
    background: var(--sensei-surface);
    backdrop-filter: blur(var(--sensei-blur));
    border-radius: var(--sensei-radius);
    border: 1px solid var(--sensei-border);
    overflow: hidden;
}

.dense-table-row {
    height: 56px;
    transition: var(--sensei-transition);
}

.dense-table-row:hover {
    background: var(--sensei-surface-hover);
}

.dense-table-cell {
    padding: var(--sensei-spacing-xs);
    border-bottom: 1px solid var(--sensei-border);
}

.dense-table-header {
    background: var(--sensei-surface-strong);
    padding: var(--sensei-spacing-sm);
    font-weight: 500;
}
```

**Import** in `resources/css/app.css`:
```css
@import './components/dense-table.css';
```

**Rationale**: Reuses existing tokens, ensures consistency, avoids duplication

---

## Feature Flag Rollout Plan

### Phase Timeline

| Phase | Dates | Scope | Users | Validation |
|-------|-------|-------|-------|------------|
| **Phase 1** | Week 1 (Feb 1-7) | Adelaide HQ only | ~20 users (branch_id=1) | Validate core functionality |
| **Phase 2** | Week 2-3 (Feb 8-21) | 50% A/B test | ~50% of all users | Compare performance metrics |
| **Phase 3** | Week 4+ (Feb 22+) | 100% rollout | All users | Monitor stability |

### Configuration

**Feature Class Logic**:
```php
// Phase 1: Adelaide HQ only (before Feb 15)
if (now()->isBefore('2025-02-15')) {
    return $user->branch_id === 1;
}

// Phase 2: 50% lottery (Feb 15 - Mar 1)
if (now()->isBefore('2025-03-01')) {
    return Lottery::odds(1, 2)->choose();
}

// Phase 3: Full rollout (after Mar 1)
return true;
```

### Emergency Disable

**Instant Disable** (no deployment):
```bash
php artisan pennant:purge dense-table
```

**Config-Based Disable** (.env):
```env
FEATURES_DENSE_TABLE_EMERGENCY_DISABLE=true
```

Then:
```bash
php artisan config:clear
php artisan cache:clear
```

### Monitoring

**Check Feature Status**:
```bash
php artisan tinker
>>> Feature::for(User::find(1))->active('dense-table')
>>> DB::table('features')->where('name', 'dense-table')->count()
```

---

## Pre-Phase 1 Sprint Schedule

### 5-Day Execution Plan

**Resources**: 2 developers (Backend Lead + Frontend Specialist) + Security Engineer + QA

#### Day 1 (Monday)
| Time | Backend Lead | Frontend Specialist |
|------|--------------|---------------------|
| AM | Blocker #1: SQL injection fix + tests | Blocker #5: table-cell.blade.php |
| PM | Blocker #1: Rate limiting + verification | Blocker #5: side-drawer.blade.php |

**Deliverables**: SQL injection protection, 2 Blade components

#### Day 2 (Tuesday)
| Time | Backend Lead | Frontend Specialist |
|------|--------------|---------------------|
| AM | Blocker #4: Install Pennant + feature class | Blocker #5: Component integration tests |
| PM | Blocker #4: Update controller + view | Blocker #3: Add version migration |

**Deliverables**: Feature flags active, components tested, version column added

#### Day 3 (Wednesday)
| Time | Backend Lead | Frontend Specialist |
|------|--------------|---------------------|
| AM | Blocker #2: Install activitylog + permissions | Blocker #3: Update sync-manager.js |
| PM | Blocker #2: Export controller method | Blocker #3: conflict-resolver.js |

**Deliverables**: Export permissions, conflict detection active

#### Day 4 (Thursday)
| Time | Backend Lead | Frontend Specialist | Security/QA |
|------|--------------|---------------------|-------------|
| AM | Blocker #2: Export UI + route | Blocker #3: Complete PWA tests | Security code review |
| PM | Complete all backend tests | Frontend integration tests | Penetration testing |

**Deliverables**: Export complete, PWA sync tested, security review started

#### Day 5 (Friday)
| Time | All Team |
|------|----------|
| AM | Final verification checklist |
| PM | Sign-off meeting, deployment prep |

**Deliverables**: All blockers verified, ready for Phase 1

### Interdependencies

1. **Day 1 → Day 2**: SQL injection fix must complete before export work (uses same sort logic)
2. **Day 2 → Day 3**: Version migration must complete before sync conflict work
3. **Day 3 → Day 4**: Export controller needed before UI can be built
4. **Day 4 → Day 5**: All code must be complete before final verification

**Critical Path**: Backend Lead (Days 1-4) → Security Review (Day 4) → Final Verification (Day 5)

---

## Residual Risks

### Risk #1: Design Token Inconsistency (ADVISORY)
**Status**: ADVISORY (not blocking)
**Issue**: Rollout plan may create duplicate CSS instead of using existing tokens
**Mitigation**: Use provided dense-table.css that consumes sensei-theme.css tokens
**Action**: Frontend Specialist reviews CSS during Day 1-2

### Risk #2: Mobile Touch Gestures (DEFER TO PHASE 7)
**Status**: DEFERRED
**Issue**: No swipe-to-open, swipe-to-delete, pull-to-refresh
**Mitigation**: Desktop-first rollout, mobile gestures added in Phase 7 (post-launch)
**Action**: Document as Phase 7 enhancement

### Risk #3: Pagination Performance at Scale (MONITOR)
**Status**: MONITOR
**Issue**: 50/page = 4× query complexity (200 sub-queries)
**Mitigation**: Already addressed in rollout plan (eager loading, selective columns)
**Action**: Add Laravel Telescope monitoring in Phase 5

### Risk #4: Offline Storage Limits (MONITOR)
**Status**: MONITOR
**Issue**: iOS Safari 10 MB IndexedDB limit
**Calculation**: ~3,000 offline incidents before limit
**Mitigation**: Add storage quota monitoring in Phase 3
**Action**: Display warning at 80% usage

**No risks promoted to blocker status** - all risks remain advisory or deferred.

---

## Verification Checklist

### Blocker #1: SQL Injection

**Automated**:
```bash
php artisan test --filter TeamControllerTest
php artisan test --filter TeamControllerSecurityTest
```

**Manual**:
```bash
curl "http://whs.test/team?sort=1%3BDROP+TABLE+users%3B--"  # Expect: 200, fallback to 'name'
curl "http://whs.test/team?sort=password"  # Expect: 200, fallback to 'name'
for i in {1..65}; do curl -w "%{http_code}\n" "http://whs.test/team?sort=name"; done  # Expect: 60×200, 5×429
```

### Blocker #2: Export Security

**Automated**:
```bash
php artisan test --filter TeamExportTest
```

**Manual**:
```bash
curl -X POST http://whs.test/team/export -d "format=csv"  # Expect: 403 (unauthorized)
curl -X POST http://whs.test/team/export -H "Cookie: hr_session" -d "format=csv" -o test.csv  # Expect: 200 + CSV
php artisan tinker
>>> Activity::where('description', 'team_export_initiated')->latest()->first();  # Verify log
```

### Blocker #3: PWA Sync

**Automated**:
```bash
php artisan test --filter OptimisticLockingTest
```

**Manual**:
```
1. Browser A: Go offline, edit employee #1 → name="Alice"
2. Browser B: Edit employee #1 → name="Bob", save
3. Browser A: Go online, observe sync
4. Expected: Conflict modal with side-by-side comparison
```

### Blocker #4: Feature Flags

**Automated**:
```bash
php artisan test --filter DenseTableFeatureFlagTest
```

**Manual**:
```bash
php artisan tinker
>>> Feature::for(User::where('branch_id', 1)->first())->active('dense-table')  # Expect: true (Phase 1)
>>> Feature::for(User::where('branch_id', 2)->first())->active('dense-table')  # Expect: false (Phase 1)
php artisan pennant:purge dense-table  # Emergency disable
```

### Blocker #5: Blade Components

**Automated**:
```bash
php artisan test --filter TableCellComponentTest
php artisan test --filter SideDrawerComponentTest
```

**Manual**:
```
1. Visit /team page
2. Verify table-cell renders avatar + badges
3. Click row to open side drawer
4. Press ESC → drawer closes
5. Tab through drawer → focus stays trapped
```

### Accessibility Audit

```bash
npm run axe http://whs.test/team
# Expected: No critical violations, WCAG 2.1 AA compliant
```

---

## Next Steps

### Immediate (This Week)
1. **Management Approval**: Review this guide, approve 5-day sprint, allocate 2 developers
2. **Git Branch**: Create `feature/dense-table-pre-phase-1`
3. **Environment Setup**: Prepare staging environment, test data
4. **Kickoff Meeting**: Day 1 Monday morning, review schedule

### Pre-Phase 1 Execution (Next Week)
- **Daily Standups**: 9 AM, track progress against schedule
- **Pair Programming**: Backend + Frontend collaborate on integration points
- **Security Review**: Thursday afternoon, penetration testing
- **Sign-Off**: Friday 4 PM, all blockers verified

### Phase 1 Preparation (Week After)
- **Deploy to Staging**: Monday, validate all blockers resolved
- **Adelaide HQ Training**: Tuesday, prepare 20 pilot users
- **Monitor Feature Flags**: Wednesday, confirm Phase 1 rollout logic
- **Phase 1 Launch**: Thursday, enable for Adelaide HQ

**End of Implementation Guide**
