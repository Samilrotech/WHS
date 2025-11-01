# Visual Demo Guide - All Implemented Features

**URL**: `http://127.0.0.1:8002/teams`

This guide shows you exactly what to look for and how to test all 5 implemented blockers.

---

## 🎯 Quick Checklist

Visit `http://127.0.0.1:8002/teams` and you should see:

- ✅ Export Employee button (if you have permission)
- ✅ Feature flag info banner (if you're in Sydney branch)
- ✅ **"Quick View" button** on each employee card (NEW!)
- ✅ Dense table preview section (if feature flag active)
- ✅ Side drawer when clicking Quick View (NEW!)

---

## **Blocker #1: SQL Injection Safeguards**

### What Changed
Backend security improvements - no visual changes but you're now protected.

### How to Test

1. **Test Sorting (Should Work):**
   - Click any column header to sort
   - URL will show: `?sort=name&direction=asc`
   - Try different columns: email, phone, employee_id

2. **Test SQL Injection (Should Fail Safely):**
   - Try malicious URL: `http://127.0.0.1:8002/teams?sort=1;DROP+TABLE+users;--`
   - Result: Page still loads (falls back to 'name' column)
   - No SQL error, no data loss

3. **Test Rate Limiting:**
   - Refresh page rapidly 65+ times in 1 minute
   - After 60 requests: You'll see HTTP 429 (Too Many Requests)
   - Wait 1 minute and it resets

### Visual Indicators
- ✅ Sorting works normally
- ✅ Malicious input ignored (no errors)
- ✅ Rate limit kicks in after 60 requests/minute

---

## **Blocker #2: Export Security & GDPR Compliance**

### What to Look For

**1. Export Button:**
- Location: Above the team member cards
- Button text: "Export Employees"
- Icon: Download icon

### How to Test

1. **Click "Export Employees" button**
   - Modal opens with title: "Export Employee Data"
   - GDPR warning banner visible (yellow alert)

2. **Check GDPR Features:**
   - ⚠️ Warning: "This export contains personal data. All exports are logged."
   - 🏷️ PII badges on: Name, Email, Phone fields
   - ✅ Checkboxes to select which fields to export

3. **Perform Export:**
   - Select fields you want
   - Click "Export CSV"
   - CSV file downloads to your computer
   - Check backend logs: Export is logged in activity_log table

### Visual Indicators
- ✅ Yellow GDPR warning banner
- ✅ Orange "PII" badges on sensitive fields
- ✅ Field selection checkboxes
- ✅ CSV downloads successfully

### Verification
```sql
-- Check that export was logged
SELECT * FROM activity_log
WHERE description = 'team_export_initiated'
ORDER BY created_at DESC
LIMIT 1;
```

---

## **Blocker #3: PWA Sync Conflict Resolution**

### What Changed
Optimistic locking for offline sync - only visible when conflicts occur.

### How to Trigger Conflict

**Method 1: Simulate Conflict (Requires 2 Browser Windows)**

1. **Window 1:** Login and open employee edit page
2. **Window 2:** Login (different user) and edit SAME employee
3. **Window 2:** Save changes (version increments to 2)
4. **Window 1:** Try to save (still has version 1)
5. **Result:** HTTP 409 Conflict response with conflict data

**Method 2: Check Version Column**

```sql
-- Check that version column exists and increments
SELECT id, name, email, version FROM users LIMIT 5;

-- Edit a user via the UI
-- Run query again - version should have incremented
```

### Visual Indicators (When Conflict Occurs)
- ✅ Toastify notification: "⚠️ Sync Conflict: Some changes conflict..."
- ✅ Conflict resolver modal opens (side-by-side comparison)
- ✅ Options: "Keep My Changes" or "Use Server Version"

### Files to Check
- `public/js/conflict-resolver.js` - Resolution UI
- `public/js/sync-manager.js` - Conflict detection
- `public/js/offline-db.js` - Conflict storage (IndexedDB)

---

## **Blocker #4: Feature Flags with Laravel Pennant**

### What to Look For

**If you're in Sydney Operations Centre branch:**

1. **Blue Info Banner at Top:**
   ```
   🧪 New Dense Table UI Active

   You're using the new high-density table interface as part
   of our gradual rollout. Learn more →
   ```

2. **Dense Table Section:**
   - Card with title: "Dense Table Preview (New Component)"
   - Table showing employees with new styling
   - Avatar cells, badge cells, action buttons

3. **Info Modal:**
   - Click "Learn more" in banner
   - Modal shows rollout phases and features

### How to Test Phases

**Current Phase: 1 (Sydney Only)**
- Sydney branch users: Banner visible ✅
- Other branches: No banner ❌

**To Test Other Phases:**

Edit `app/Features/DenseTableFeature.php` line 35:
```php
// For Phase 2 testing (50% A/B test):
if (now()->isAfter('2025-11-08')) {  // Change to '2025-10-01'
    return Lottery::odds(1, 2)->choose();
}

// For Phase 3 testing (100% rollout):
if (now()->isAfter('2025-11-22')) {  // Change to '2025-10-01'
    return true;
}
```

**Emergency Disable:**
```bash
# Add to .env file:
DENSE_TABLE_ENABLED=false

# Restart server
# Banner should disappear for ALL users
```

### Visual Indicators
- ✅ Blue banner with "New Dense Table UI Active"
- ✅ "Learn more" link opens modal
- ✅ Dense table section appears below cards
- ✅ Modal shows current phase and schedule

---

## **Blocker #5: Complete Blade Components** ⭐ NEW!

### 1. **Side Drawer Component**

**How to Open:**
1. Find any employee card
2. Click **"Quick View"** button (eye icon)
3. Side drawer slides in from the right

**What You'll See:**
- ✅ Smooth slide-in animation (300ms)
- ✅ Dark backdrop overlay
- ✅ Employee info in drawer panel
- ✅ Avatar with initials
- ✅ Contact information
- ✅ Activity summary
- ✅ Footer with action buttons

**How to Test Accessibility:**

1. **Keyboard Navigation:**
   - Press `Tab` key: Focus moves through elements
   - Press `Shift + Tab`: Focus moves backwards
   - Press `Esc` key: Drawer closes
   - Focus returns to "Quick View" button

2. **Focus Trap:**
   - With drawer open, press `Tab` repeatedly
   - Focus stays INSIDE drawer (doesn't escape)
   - Last element → Tab → First element (cycles)

3. **Screen Reader:**
   - Right-click drawer → Inspect
   - Check ARIA attributes:
     - `role="dialog"`
     - `aria-modal="true"`
     - `aria-labelledby="employeeDrawerXXX-title"`

4. **Close Methods:**
   - ✅ Click backdrop
   - ✅ Click "Close" button
   - ✅ Press ESC key
   - ✅ All three methods work

**Visual Indicators:**
- ✅ Smooth slide-in from right
- ✅ Backdrop darkens page
- ✅ Body scroll locked (can't scroll behind drawer)
- ✅ Focus visible on interactive elements
- ✅ Drawer width: 640px (large size)

---

### 2. **Table Cell Component**

**Where to See It:**
Only appears if **feature flag is active** (Sydney branch users).

**Location:**
Scroll down to "Dense Table Preview (New Component)" card.

**What You'll See:**

1. **Avatar Cells:**
   - Circular badge with employee initials
   - Employee name next to avatar
   - ARIA label: "Employee name"

2. **Badge Cells:**
   - Color-coded status badges:
     - Green: "Active"
     - Gray: "Inactive"
     - Yellow: "On Leave"
   - ARIA label: "Employment status"

3. **Date Cells:**
   - Formatted date: "Nov 1, 2025"
   - Optional time: "3:45 PM"
   - Semantic `<time>` element with `datetime` attribute

4. **Numeric Cells:**
   - Right-aligned numbers
   - Monospace font
   - ARIA label: "Incident count"

5. **Action Cells:**
   - View button (eye icon)
   - Edit button (pencil icon)
   - ARIA label: "Available actions"
   - `role="group"` for button group

**How to Test Accessibility:**

1. **Inspect HTML:**
   ```html
   <td class="whs-table-cell" aria-label="Employee name">
     <div class="avatar">...</div>
     <span>John Doe</span>
   </td>
   ```

2. **Keyboard Navigation:**
   - Click in table
   - Press `Tab`: Moves to action buttons
   - Focus indicator visible (blue outline)

3. **Responsive Design:**
   - Resize browser to 375px width (mobile)
   - Table should stay readable
   - Cells adjust padding

**Visual Indicators:**
- ✅ Proper alignment (left/center/right)
- ✅ Color-coded badges
- ✅ Monospace numbers
- ✅ Focus indicators visible
- ✅ Icons in action buttons

---

## 🎬 Complete Test Flow

**Start-to-Finish Demo:**

1. **Login** to `http://127.0.0.1:8002`

2. **Navigate** to Team Management (`/teams`)

3. **Check Banner:**
   - If Sydney branch: Blue banner visible ✅
   - If other branch: No banner ❌

4. **Test Export:**
   - Click "Export Employees"
   - Check GDPR warning
   - Check PII badges
   - Export CSV

5. **Test Side Drawer:**
   - Click "Quick View" on any employee
   - Drawer slides in from right
   - Check employee details
   - Press `ESC` to close
   - Check focus returns to button

6. **Test Dense Table (if flag active):**
   - Scroll to "Dense Table Preview"
   - Check avatar cells
   - Check badge cells
   - Check date cells
   - Click view button in actions column
   - Drawer opens again

7. **Test Keyboard Navigation:**
   - Press `Tab` through all elements
   - Check focus indicators visible
   - Press `Esc` to close drawer
   - Focus returns correctly

8. **Test Responsive:**
   - Resize to mobile (375px)
   - Drawer becomes full-width
   - Table cells adjust padding
   - Everything still readable

---

## 🐛 Troubleshooting

### "I don't see the Export button"
**Solution:** Your user needs the `team.export` permission.
```sql
-- Grant permission (run in database)
INSERT INTO model_has_permissions (permission_id, model_type, model_id)
SELECT p.id, 'App\\Models\\User', YOUR_USER_ID
FROM permissions p
WHERE p.name = 'team.export';
```

### "I don't see the feature flag banner"
**Solution:** You're not in Sydney Operations Centre branch.

**Check your branch:**
```sql
SELECT u.name, b.name as branch
FROM users u
JOIN branches b ON u.branch_id = b.id
WHERE u.id = YOUR_USER_ID;
```

**Option 1:** Create a Sydney branch user
**Option 2:** Temporarily change feature logic to include your branch

### "Side drawer doesn't open"
**Check:**
1. JavaScript console for errors (F12)
2. Make sure you clicked "Quick View" button
3. Check if `public/js/side-drawer.js` is loaded

### "Dense table not showing"
**Check:**
1. Feature flag is active for your user
2. You have team members in the database
3. JavaScript console for errors

---

## 📊 Summary of Visual Changes

| Feature | Location | Visibility |
|---------|----------|------------|
| **Export Button** | Above cards | ✅ Always (with permission) |
| **Export Modal** | Click button | ✅ Always (with permission) |
| **Feature Banner** | Top of page | 🎯 Sydney branch only |
| **Dense Table** | Below cards | 🎯 Sydney branch only |
| **Side Drawer** | Click Quick View | ✅ Always |
| **Table Cells** | In dense table | 🎯 Sydney branch only |

---

## 🎯 Next Steps

1. **Test Everything** using this guide
2. **Take Screenshots** of working features
3. **Test Accessibility** with keyboard navigation
4. **Check Database** for activity logs
5. **Try Edge Cases** (rate limiting, conflicts, etc.)

---

## 📝 Feature Flag Testing Matrix

| Branch | Phase 1 (Now) | Phase 2 (Nov 8+) | Phase 3 (Nov 22+) |
|--------|---------------|------------------|-------------------|
| Sydney | ✅ Enabled | ✅ Enabled | ✅ Enabled |
| Brisbane | ❌ Disabled | 🎲 50% Random | ✅ Enabled |
| Perth | ❌ Disabled | 🎲 50% Random | ✅ Enabled |

---

**Ready to test? Visit:** `http://127.0.0.1:8002/teams`

All features are now live and ready for demonstration! 🚀
