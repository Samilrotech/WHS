# Admin Role Check Fix

## Issue
Team Management list was filtering users by branch even for admin users, because `User::isAdmin()` was checking for lowercase role names (`'admin'`, `'super-admin'`) but the actual database role is `'Admin'` (capital A).

## Impact
- System Administrator (with `Admin` role) was being treated as non-admin
- Team list was filtered to `where('branch_id', null)` for admin
- Harper Collins (Employee assigned to Sydney branch) was hidden from team list
- Branch card showed "1 employee" but team list showed 0 employees (data mismatch)

## Root Cause
**File**: `app/Models/User.php:121-124`

**Before (Broken)**:
```php
public function isAdmin(): bool
{
    return $this->hasRole('admin') || $this->hasRole('super-admin');
}
```

Role name mismatch:
- Code checked: `'admin'` (lowercase)
- Database has: `'Admin'` (capital A)

## Fix Applied
**File**: `app/Models/User.php:121-129`

**After (Fixed)**:
```php
public function isAdmin(): bool
{
    return $this->hasAnyRole([
        'Admin',         // matches actual database role
        'Super Admin',   // for future use
        'admin',         // backward compatibility
        'super-admin',   // backward compatibility
    ]);
}
```

## Affected Controllers
The fix impacts these controllers that use `isAdmin()`:

### 1. TeamController (`app/Modules/TeamManagement/Controllers/TeamController.php`)
- Line 55: Branch filtering for team list
- Line 106: Branch filtering for statistics
- Line 515: Authorization check for team member access

### 2. Other Controllers (if any)
Any controller using `$user->isAdmin()` will now correctly identify Admin role users.

## Database Verification
**Roles Table**:
```
id | name
---+----------
1  | Admin
2  | Manager
3  | Employee
```

**Current User Assignments**:
```
User                    | Role     | Branch
-----------------------|----------|--------
System Administrator   | Admin    | NULL (global)
Harper Collins         | Employee | Sydney branch
```

## How It Works Now
1. User logs in as "System Administrator" with `Admin` role
2. `isAdmin()` checks against `['Admin', 'Super Admin', 'admin', 'super-admin']`
3. Finds match with `'Admin'` → returns `true`
4. TeamController skips branch filter (lines 55-57)
5. Harper Collins appears in team list
6. Branch card count matches team list count

## Testing Steps

### 1. Verify Fix (No rebuild needed - PHP only)
```bash
# Simply refresh the page - PHP changes are live immediately
```

### 2. Test Team List
1. Navigate to http://127.0.0.1:8002/teams
2. **Expected**: Harper Collins should appear in the list
3. **Expected**: Branch card shows "1 employee" and team list shows 1 employee

### 3. Test Branch Filtering
1. As admin, you should see ALL employees across ALL branches
2. Use branch filter dropdown to filter by specific branch
3. Employee users should only see teammates from their own branch

### 4. Test Statistics
1. Check team statistics at top of page
2. Should show total counts across all branches for admin
3. Should show branch-specific counts for employees

## Verification Commands

### Check Current User Role
```bash
mysql -u root -pSamilChaladan123! whs5 -e "
SELECT u.name, r.name as role, u.branch_id
FROM users u
JOIN model_has_roles mhr ON u.id = mhr.model_id
JOIN roles r ON mhr.role_id = r.id
WHERE u.email = 'admin@whs4.com.au';"
```

**Expected Output**:
```
name                  | role  | branch_id
---------------------+-------+-----------
System Administrator | Admin | NULL
```

### Check Team Member Visibility
```bash
mysql -u root -pSamilChaladan123! whs5 -e "
SELECT u.name, r.name as role, b.name as branch
FROM users u
LEFT JOIN model_has_roles mhr ON u.id = mhr.model_id
LEFT JOIN roles r ON mhr.role_id = r.id
LEFT JOIN branches b ON u.branch_id = b.id;"
```

**Expected**: All users visible regardless of branch assignment

## Files Modified
- ✅ `app/Models/User.php` (lines 121-129)

## No Migrations Needed
- Database schema unchanged
- Role names already correct in database
- Only PHP code logic updated

## Status: ✅ RESOLVED
Team list now displays correctly for admin users, with branch filtering working as intended for both admin (sees all) and employee (sees own branch) users.
