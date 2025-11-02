# Dense Table View Implementation - Phase 1 Complete

## Overview

Successfully implemented comprehensive dense table view system for WHS5 application, starting with Team Management module as the reference implementation.

**Status**: ✅ Phase 1 Complete - Ready for Testing
**Implementation Date**: November 2, 2025
**Module**: Team Management (Teams Index)

---

## What Was Implemented

### 1. Shared Foundation Components

#### **`x-whs.table-toolbar`** - Unified Table Toolbar
**Location**: `resources/views/components/whs/table-toolbar.blade.php`

**Features**:
- View mode toggle (Cards/Table) with active state highlighting
- Search bar with clear button
- Items per page selector (25/50/100)
- Density toggle button
- Bulk actions bar (shows when items selected)
- Active filter chips with remove buttons
- Responsive layout for mobile
- Theme-aware styling (light/dark)

**Usage**:
```blade
<x-whs.table-toolbar
  title="Team members"
  :view-mode="request('view', 'cards')"
  :items-per-page="$paginator->perPage()"
  :search-action="route('teams.index')"
  search-placeholder="Search members..."
  :search-value="$filters['search'] ?? ''"
  :show-bulk-actions="true"
>
  <!-- Optional slots: filters, bulkActions, actions -->
</x-whs.table-toolbar>
```

#### **`x-whs.table`** - Dense Table Wrapper
**Location**: `resources/views/components/whs/table.blade.php`

**Features**:
- Three density modes: compact, normal, comfortable
- Striped rows with hover effects
- Responsive wrapper with horizontal scroll
- Sticky header support
- Sortable columns with visual indicators
- Theme-aware styling
- Custom scrollbar styling

**Usage**:
```blade
<x-whs.table
  density="normal"
  :striped="true"
  :hover="true"
  :responsive="true"
  :sticky-header="true"
  :sortable="true"
>
  <x-slot:thead><!-- headers --></x-slot:thead>
  <x-slot:tbody><!-- rows --></x-slot:tbody>
</x-whs.table>
```

#### **`x-whs.table-row`** - Enhanced Table Row
**Location**: `resources/views/components/whs/table-row.blade.php`

**Features**:
- Selectable rows with checkbox
- Row severity highlighting (info/warning/danger/success)
- Clickable rows with href support
- Hover states
- Selected state styling

**Usage**:
```blade
<x-whs.table-row
  :selectable="true"
  :row-id="$member['id']"
  :severity="$severity"
>
  <!-- table cells -->
</x-whs.table-row>
```

### 2. Team Management Dense View

#### **Teams Table View Partial**
**Location**: `resources/views/content/TeamManagement/_table-view.blade.php`

**Columns Implemented**:
1. **Checkbox** - Bulk selection
2. **Employee ID** - Sortable text field
3. **Member** - Avatar with name and email subtext
4. **Role** - Employee role badge
5. **Branch** - Branch name
6. **Status** - Employment status badge (active/inactive/on_leave)
7. **Contact** - Email (linked) and phone
8. **Vehicle** - Current assigned vehicle with details
9. **Last Inspection** - Inspection result with badge and date
10. **Certs** - Certification count with expiring warning
11. **Last Active** - Relative date with human-readable format
12. **Actions** - Quick view, edit, and dropdown menu

**Features**:
- All 3 team members displayed with full data
- Active filter chips showing current filters
- Bulk action buttons (export, assign branch, mark leave, delete)
- Dropdown menu for each row with additional actions
- Empty state with "Add first member" button
- Pagination with item count display

### 3. Backend Enhancements

#### **TeamController Updates**
**Location**: `app/Modules/TeamManagement/Controllers/TeamController.php`

**Changes**:
```php
// Added branch_id to sortable columns
$allowedSortColumns = [
    'name', 'email', 'phone', 'employee_id',
    'employment_status', 'created_at', 'updated_at',
    'branch_id', // NEW
];

// Added per_page parameter support
$perPage = (int) $request->input('per_page', 50);
$perPage = in_array($perPage, [25, 50, 100], true) ? $perPage : 50;

// Updated pagination
$users = $query->orderBy($sortColumn, $sortDirection)
    ->paginate($perPage)->withQueryString();
```

### 4. JavaScript Functionality

#### **View Toggle System**
**Location**: `TeamManagement/Index.blade.php` @ page-script section

**Features**:
- Toggle between Cards and Table view
- Persist preference to localStorage (`whsPreferredView`)
- Update URL query parameter (`?view=table`)
- Active button highlighting
- Auto-restore preference on page load

**Code**:
```javascript
// View toggle with localStorage persistence
viewButtons.forEach(button => {
  button.addEventListener('click', function() {
    const view = this.dataset.view;
    const url = new URL(window.location);
    url.searchParams.set('view', view);
    localStorage.setItem('whsPreferredView', view);
    window.location.href = url.toString();
  });
});
```

#### **Bulk Selection System**

**Features**:
- Select all checkbox with indeterminate state
- Individual row checkboxes
- Bulk actions bar appears when items selected
- Selected count display
- Clear selection button
- Bulk action handlers:
  - Export selected
  - Assign branch
  - Mark as on leave
  - Delete selected

**Code**:
```javascript
// Select all with indeterminate state
selectAllCheckbox.addEventListener('change', function() {
  const isChecked = this.checked;
  rowCheckboxes.forEach(checkbox => {
    checkbox.checked = isChecked;
  });
  updateBulkActions();
});

// Update bulk actions visibility
function updateBulkActions() {
  const selectedCount = Array.from(rowCheckboxes).filter(cb => cb.checked).length;
  if (selectedCount > 0) {
    bulkActionsBar.hidden = false;
  } else {
    bulkActionsBar.hidden = true;
  }
}
```

### 5. Filter Chips Component

**Features**:
- Display active filters as removable chips
- Visual distinction with icons
- Click to remove individual filter
- "Clear all filters" button when multiple filters active
- Maintains URL query parameters

**Filters Supported**:
- Search query
- Branch
- Role
- Status

### 6. Styling and Theming

#### **CSS Tokens and Variables**
All components use Sensei theme tokens:
```css
--sensei-bg-surface
--sensei-border-base
--sensei-text-primary
--sensei-text-secondary
--sensei-brand-primary
```

#### **Light Theme Support**
Full light theme overrides implemented:
```css
[data-bs-theme='light'] .whs-dense-view { }
[data-bs-theme='light'] .whs-filter-chip { }
[data-bs-theme='light'] .whs-table__row--selected { }
```

#### **Responsive Design**
- Mobile-first approach
- Horizontal scroll for table on small screens
- Toolbar stacks vertically on mobile
- Touch-friendly button sizes (min 44px)

---

## How to Use

### Access Table View

1. **Manual Toggle**:
   - Navigate to Teams page: `http://127.0.0.1:8002/teams`
   - Click "Table" button in view toggle
   - URL updates to: `http://127.0.0.1:8002/teams?view=table`

2. **Direct URL**:
   - Add `?view=table` to any teams URL
   - Example: `http://127.0.0.1:8002/teams?view=table&per_page=100`

3. **Auto-Restore**:
   - Preference saved to localStorage
   - Returns to last used view on page reload

### Use Filters

1. **Search**:
   - Type in search bar
   - Searches: name, email, employee_id, phone
   - Click X to clear

2. **Filter by Branch/Role/Status**:
   - Use existing filter controls
   - Active filters appear as chips
   - Click chip X to remove
   - Click "Clear all filters" to reset

### Bulk Actions

1. **Select Items**:
   - Check individual rows OR
   - Use "Select All" checkbox in header

2. **Perform Action**:
   - Bulk action bar appears
   - Choose action: Export, Assign Branch, Mark Leave, Delete
   - Confirm action in dialog

3. **Clear Selection**:
   - Click "Clear selection" in bulk bar OR
   - Uncheck all items manually

### Change Density

1. Click density toggle button (three horizontal lines icon)
2. Cycles through: compact → normal → comfortable
3. Preference saved to localStorage

### Sort Columns

1. Click any column header with sort indicator
2. First click: ascending
3. Second click: descending
4. Third click: back to ascending

### Change Items Per Page

1. Use dropdown selector: 25, 50, or 100
2. Page reloads with new count
3. URL updates: `?per_page=100`

---

## File Structure

```
resources/
├── views/
│   ├── components/
│   │   └── whs/
│   │       ├── table-toolbar.blade.php    # NEW - Toolbar component
│   │       ├── table.blade.php            # NEW - Table wrapper
│   │       ├── table-row.blade.php        # NEW - Row component
│   │       └── table-cell.blade.php       # EXISTING - Cell component
│   └── content/
│       └── TeamManagement/
│           ├── Index.blade.php            # UPDATED - Added view toggle
│           └── _table-view.blade.php      # NEW - Table view partial

app/
└── Modules/
    └── TeamManagement/
        └── Controllers/
            └── TeamController.php         # UPDATED - Added per_page support

public/
└── assets/
    └── img/
        └── branding/                      # Logo files for theme switching
```

---

## Technical Decisions

### Why Blade Components Over Vue/Alpine?

**Decision**: Use Blade components with vanilla JavaScript
**Rationale**:
- Consistent with existing WHS5 architecture
- No additional build complexity
- Server-side rendering for better SEO and initial load
- Progressive enhancement approach
- Easier maintenance for Laravel developers

### Why Separate Card and Table Views?

**Decision**: Two distinct view modes instead of hybrid
**Rationale**:
- Cards optimal for overview and mobile
- Tables optimal for data comparison and bulk actions
- Different use cases require different UX patterns
- Easier to maintain and extend separately

### Why LocalStorage for View Preference?

**Decision**: Store view preference client-side
**Rationale**:
- Instant preference restoration
- No database queries required
- No server-side session management
- Works across devices if user logs in
- Fallback to URL parameter if storage unavailable

---

## Next Steps - Phase 2

### Module Rollout

1. **Vehicles Module** (Fleet Management)
   - Adapt columns: registration, make/model, status, compliance, assignments
   - Add frozen columns for horizontal scroll
   - Vehicle-specific filters

2. **Branches Module**
   - Simpler structure: name, location, contact, staff count
   - Map view integration option

3. **Inspections & Training**
   - Date range filters
   - Outcome/certification filters
   - Row expansion for detail view

4. **Contractors, Equipment, Documents**
   - Reuse components with column adaptations
   - Module-specific bulk actions

### Enhancements

1. **Column Picker**
   - Allow users to show/hide columns
   - Save column preferences per user
   - Drag-and-drop column reordering

2. **Saved Views**
   - Named filter combinations
   - Shareable view URLs
   - Default view per user/role

3. **Advanced Filters**
   - Date range picker component
   - Multi-select dropdowns
   - Custom filter builder

4. **Export**
   - CSV export with selected columns
   - Excel export with formatting
   - PDF reports

### Testing & QA

1. **Accessibility Audit**
   - Run axe DevTools
   - Keyboard navigation testing
   - Screen reader testing
   - ARIA roles validation

2. **Performance Testing**
   - Large dataset testing (1000+ rows)
   - Query optimization
   - Pagination performance
   - Frontend rendering speed

3. **Cross-Browser Testing**
   - Chrome, Firefox, Safari, Edge
   - Mobile browsers
   - Responsive breakpoints

4. **User Acceptance Testing**
   - Gather feedback from operations team
   - Track usage metrics
   - Identify pain points

---

## Known Limitations

### Current Phase 1 Limitations

1. **Bulk Actions Placeholder**
   - Bulk action handlers show alerts/console.log
   - Need backend routes for actual operations
   - Export functionality pending

2. **Column Picker Not Implemented**
   - All columns always visible
   - No user customization yet
   - Fixed column order

3. **Saved Views Not Implemented**
   - Can't save filter combinations
   - No shareable view URLs
   - No per-user defaults

4. **Mobile Optimization**
   - Horizontal scroll required on small screens
   - Could benefit from column hiding
   - Touch gestures for swipe actions

5. **Performance**
   - Not tested with 1000+ rows
   - No virtual scrolling
   - No lazy loading

### Accessibility Gaps

1. **Keyboard Navigation**
   - Basic support implemented
   - Advanced keyboard shortcuts pending
   - Focus management needs enhancement

2. **Screen Reader Support**
   - ARIA labels present
   - Live region updates pending
   - Table navigation hints needed

---

## API Contract (For Frontend Reuse)

### Required Backend Response

```php
return view('module.index', [
    'members' => [          // or 'items', 'records', etc.
        'data' => $collection->all(),
        'total' => $paginator->total(),
        'current_page' => $paginator->currentPage(),
        'last_page' => $paginator->lastPage(),
    ],
    'filters' => [          // Current active filters
        'search' => $request->input('search'),
        'branch' => $request->input('branch'),
        'role' => $request->input('role'),
        'status' => $request->input('status'),
    ],
    'branches' => $branches, // For filter dropdowns
    'paginator' => $paginator,
    'useDenseTable' => $featureFlag, // Optional feature flag
]);
```

### Required URL Parameters

- `view` - 'cards' or 'table'
- `per_page` - 25, 50, or 100
- `sort` - column name from whitelist
- `direction` - 'asc' or 'desc'
- `search` - search query string
- Module-specific filters (branch, role, status, etc.)

### Required Controller Methods

```php
// Pagination with per_page
$perPage = in_array((int) $request->input('per_page', 50), [25, 50, 100], true)
    ? (int) $request->input('per_page')
    : 50;

// Sort with whitelist
$allowedSortColumns = ['name', 'email', 'created_at', 'updated_at'];
$sortColumn = in_array($request->input('sort', 'name'), $allowedSortColumns, true)
    ? $request->input('sort')
    : 'name';

$sortDirection = in_array($request->input('direction', 'asc'), ['asc', 'desc'], true)
    ? $request->input('direction')
    : 'asc';

// Pagination
$items = $query->orderBy($sortColumn, $sortDirection)
    ->paginate($perPage)
    ->withQueryString();
```

---

## Testing Checklist

### Functionality
- [ ] View toggle switches between cards and table
- [ ] View preference persists after page reload
- [ ] Search filters results correctly
- [ ] Filter chips display active filters
- [ ] Filter chips remove correctly
- [ ] Clear all filters works
- [ ] Column sorting works (asc/desc)
- [ ] Pagination works (next/prev/numbers)
- [ ] Items per page selector works
- [ ] Select all checkbox selects all rows
- [ ] Individual row selection works
- [ ] Bulk actions bar appears/hides correctly
- [ ] Selected count updates correctly
- [ ] Clear selection works
- [ ] Density toggle cycles through modes
- [ ] Row actions dropdown works
- [ ] Quick view drawer opens
- [ ] Edit link navigates correctly

### Accessibility
- [ ] Tab navigation works through all controls
- [ ] Keyboard shortcuts work (Space for checkboxes)
- [ ] Focus indicators visible
- [ ] ARIA labels present on buttons
- [ ] Table has proper semantic markup
- [ ] Screen reader announces changes
- [ ] Color contrast meets WCAG AA
- [ ] Touch targets are 44px minimum

### Performance
- [ ] Page loads in <2 seconds
- [ ] Filtering responds in <500ms
- [ ] Sorting responds in <500ms
- [ ] View toggle responds in <100ms
- [ ] No layout shifts during load
- [ ] Smooth scrolling on all devices

### Responsive
- [ ] Works on mobile (320px width)
- [ ] Works on tablet (768px width)
- [ ] Works on desktop (1920px width)
- [ ] Horizontal scroll works on small screens
- [ ] Toolbar stacks correctly on mobile
- [ ] Buttons remain accessible on touch

### Browser Compatibility
- [ ] Chrome (latest)
- [ ] Firefox (latest)
- [ ] Safari (latest)
- [ ] Edge (latest)
- [ ] Mobile Safari
- [ ] Mobile Chrome

---

## Success Metrics

### Adoption Metrics
- % of users who switch to table view
- Average session time in table view vs cards
- Number of bulk actions performed per session

### Performance Metrics
- Page load time < 2s
- Time to interactive < 3s
- Filter response time < 500ms
- Sort response time < 500ms

### User Satisfaction
- Reduced clicks to complete common tasks
- Increased data density visible at once
- Faster information scanning

---

## Developer Playbook

### Adding Dense Table to New Module

1. **Create Table View Partial**: `_table-view.blade.php`

```blade
<div class="whs-dense-view" data-view-mode="table">
  <x-whs.table-toolbar
    title="Module Items"
    :view-mode="request('view', 'cards')"
    :items-per-page="$paginator->perPage()"
    :search-action="route('module.index')"
    search-placeholder="Search..."
    :search-value="$filters['search'] ?? ''"
  />

  <x-whs.table density="normal" :striped="true" :hover="true" :sortable="true">
    <x-slot:thead>
      <tr>
        <th>Column 1</th>
        <!-- More columns -->
      </tr>
    </x-slot:thead>

    <x-slot:tbody>
      @foreach($items['data'] as $item)
        <x-whs.table-row :selectable="true" :row-id="$item['id']">
          <td>{{ $item['field'] }}</td>
          <!-- More cells -->
        </x-whs.table-row>
      @endforeach
    </x-slot:tbody>
  </x-whs.table>
</div>
```

2. **Update Index View**: Add view toggle

```blade
<div class="whs-section-heading">
  <div>
    <h2>Module Title</h2>
    <p>Description</p>
  </div>
  <div class="whs-section-heading__actions">
    <div class="btn-group whs-view-toggle">
      <button data-view="cards">Cards</button>
      <button data-view="table">Table</button>
    </div>
  </div>
</div>

@if(request('view') === 'table')
  @include('module._table-view')
@else
  <!-- Card view -->
@endif
```

3. **Update Controller**: Add pagination support

```php
$perPage = in_array((int) $request->input('per_page', 50), [25, 50, 100], true)
    ? (int) $request->input('per_page')
    : 50;

$items = $query->orderBy($sortColumn, $sortDirection)
    ->paginate($perPage)
    ->withQueryString();
```

4. **Add JavaScript**: Copy view toggle and bulk selection scripts from TeamManagement/Index.blade.php

---

## Troubleshooting

### View Toggle Not Working
- Check JavaScript console for errors
- Verify `data-view` attributes on buttons
- Ensure URL parameter is being set
- Check localStorage permission

### Sorting Not Working
- Verify column name in `data-sortable` attribute
- Check if column is in controller's `$allowedSortColumns` whitelist
- Inspect URL for `?sort=` and `?direction=` parameters

### Bulk Actions Not Appearing
- Verify `:show-bulk-actions="true"` on toolbar component
- Check if checkboxes have `whs-row-select` class
- Inspect `[data-bulk-actions]` element exists
- Check browser console for JavaScript errors

### Filters Not Working
- Verify filter chips are rendering
- Check if filters array is passed to view
- Inspect URL parameters being set
- Verify backend is reading filter parameters

### Styling Issues
- Clear browser cache (Ctrl+Shift+R)
- Check if Vite is running
- Verify theme CSS variables are defined
- Inspect element to see applied styles

---

## Support

For questions or issues:
1. Check this documentation first
2. Review existing Teams implementation as reference
3. Inspect browser console for errors
4. Check Vite terminal for compilation errors
5. Review Laravel logs for backend errors

---

## Changelog

### Phase 1 - November 2, 2025
- ✅ Created shared table foundation components
- ✅ Implemented Team Management dense view
- ✅ Added view toggle with localStorage persistence
- ✅ Implemented bulk selection system
- ✅ Added filter chips with remove functionality
- ✅ Updated backend for per_page and branch_id sorting
- ✅ Added comprehensive JavaScript functionality
- ✅ Implemented theme-aware styling
- ✅ Added responsive layout support

---

**Implementation Complete** ✅
Ready for user testing and Phase 2 planning.
