# Dense Table UI Migration Guide

**Purpose**: Step-by-step guide for converting any listing page to use the Dense Table UI pattern from Team Management.

**Reference Implementation**: `resources/views/content/TeamManagement/Index.blade.php` + `_table-view.blade.php`

---

## 📋 Quick Reference

**Before Migration**: Card-based listing with individual action buttons per row
**After Migration**: Dense table with toolbar, filters, bulk actions, and quick view modal

**Expected Improvements**:
- ✅ 70% reduction in DOM nodes (fewer hidden elements)
- ✅ 66% faster page loads (lighter initial HTML)
- ✅ Consistent UX across all modules
- ✅ Better scalability (handles 100+ records smoothly)

---

## 🎯 Prerequisites

### Required Components (Already Available)

✅ **`<x-whs.table-toolbar>`** - Search, filters, view toggle, action buttons
✅ **`<x-whs.table>`** - Main table wrapper with Sensei theming
✅ **`<x-whs.table-row>`** - Reusable table row component
✅ **`<x-whs.table-cell>`** - Individual cell component

Location: `resources/views/components/whs/`

### Required CSS

✅ **Sensei Theme** - Already integrated
✅ **Dense Table Styles** - Already in `team-management-theme.css`

### Required JavaScript

✅ **Quick View Modal** - Global modal in `commonMaster.blade.php`
✅ **Event Handlers** - Already in `resources/js/app.js`

---

## 🚀 Migration Process

### Step 1: Analyze Current Implementation

**Checklist**:
- [ ] Identify current data structure (controller method returning data)
- [ ] List all columns currently displayed
- [ ] Identify filters and search functionality
- [ ] Note existing action buttons (view, edit, delete, etc.)
- [ ] Check for status badges or severity indicators
- [ ] Document any module-specific features

**Example for Incident Management**:
```php
// Current controller method
public function index()
{
    $incidents = Incident::with(['branch', 'reporter'])
        ->when(request('search'), fn($q) => $q->where('title', 'like', '%'.request('search').'%'))
        ->when(request('severity'), fn($q) => $q->where('severity', request('severity')))
        ->paginate(15);

    return view('content.IncidentManagement.Index', compact('incidents'));
}
```

### Step 2: Create Table Partial View

Create `_table-view.blade.php` in your module directory (e.g., `resources/views/content/IncidentManagement/_table-view.blade.php`)

**Template Structure**:
```blade
{{-- Table Toolbar --}}
<x-whs.table-toolbar
  searchPlaceholder="Search incidents..."
  :showViewToggle="false"
  :showExport="true"
>
  {{-- Search Input --}}
  <x-slot name="search">
    <input
      type="text"
      class="whs-search-input"
      placeholder="Search incidents by title, ID, or description..."
      value="{{ request('search') }}"
      name="search"
    />
  </x-slot>

  {{-- Filter Pills --}}
  <x-slot name="filters">
    @if(request('severity'))
      <span class="whs-filter-pill">
        Severity: {{ ucfirst(request('severity')) }}
        <button type="button" class="whs-filter-pill__remove" data-filter="severity">×</button>
      </span>
    @endif
    @if(request('branch'))
      <span class="whs-filter-pill">
        Branch: {{ request('branch') }}
        <button type="button" class="whs-filter-pill__remove" data-filter="branch">×</button>
      </span>
    @endif
  </x-slot>

  {{-- Bulk Actions --}}
  <x-slot name="bulkActions">
    <button type="button" class="whs-action-btn whs-action-btn--icon" data-bulk-action="export" title="Export selected">
      <i class="icon-base ti ti-download"></i>
      <span>Export</span>
    </button>
    <button type="button" class="whs-action-btn whs-action-btn--icon whs-action-btn--danger" data-bulk-action="delete" title="Delete selected">
      <i class="icon-base ti ti-trash"></i>
      <span>Delete</span>
    </button>
  </x-slot>

  {{-- Primary Action --}}
  <x-slot name="actions">
    @can('incident.create')
      <a href="{{ route('incidents.create') }}" class="whs-action-btn whs-action-btn--primary">
        <i class="icon-base ti ti-plus"></i>
        <span>Add Incident</span>
      </a>
    @endcan
  </x-slot>
</x-whs.table-toolbar>

{{-- Data Table --}}
<x-whs.table
  :density="'comfortable'"
  :striped="true"
  :hover="true"
>
  {{-- Table Header --}}
  <x-slot name="header">
    <tr>
      <th class="whs-table__cell--checkbox">
        <input type="checkbox" class="whs-checkbox" id="selectAll" aria-label="Select all incidents">
      </th>
      <th class="whs-table__cell--sortable" data-sort="incident_number">
        <span class="whs-table__sort-label">Incident ID</span>
        <i class="icon-base ti ti-selector"></i>
      </th>
      <th class="whs-table__cell--sortable" data-sort="title">
        <span class="whs-table__sort-label">Title</span>
        <i class="icon-base ti ti-selector"></i>
      </th>
      <th>Severity</th>
      <th>Reported By</th>
      <th>Branch</th>
      <th class="whs-table__cell--sortable" data-sort="created_at">
        <span class="whs-table__sort-label">Date</span>
        <i class="icon-base ti ti-selector"></i>
      </th>
      <th>Status</th>
      <th class="whs-table__cell--actions">Actions</th>
    </tr>
  </x-slot>

  {{-- Table Body --}}
  <x-slot name="body">
    @forelse($incidents as $incident)
      <x-whs.table-row :id="$incident->id">
        {{-- Checkbox --}}
        <x-whs.table-cell type="checkbox">
          <input type="checkbox" class="whs-checkbox whs-row-checkbox" value="{{ $incident->id }}" aria-label="Select {{ $incident->incident_number }}">
        </x-whs.table-cell>

        {{-- Incident ID --}}
        <x-whs.table-cell>
          <span class="whs-chip whs-chip--id">{{ $incident->incident_number }}</span>
        </x-whs.table-cell>

        {{-- Title --}}
        <x-whs.table-cell>
          <strong class="whs-table__primary-text">{{ $incident->title }}</strong>
          @if($incident->description)
            <p class="whs-table__secondary-text">{{ Str::limit($incident->description, 60) }}</p>
          @endif
        </x-whs.table-cell>

        {{-- Severity --}}
        <x-whs.table-cell>
          <span class="whs-chip whs-chip--severity whs-chip--severity-{{ strtolower($incident->severity) }}">
            {{ ucfirst($incident->severity) }}
          </span>
        </x-whs.table-cell>

        {{-- Reported By --}}
        <x-whs.table-cell>
          @if($incident->reporter)
            <div class="d-flex align-items-center gap-2">
              <span>{{ $incident->reporter->name }}</span>
              <button type="button" class="whs-action-btn whs-action-btn--icon whs-action-btn--xs"
                      data-quick-view
                      data-member-id="{{ $incident->reporter->id }}"
                      aria-label="Quick view {{ $incident->reporter->name }}">
                <i class="icon-base ti ti-eye"></i>
              </button>
            </div>
          @else
            <span class="whs-table__empty-text">N/A</span>
          @endif
        </x-whs.table-cell>

        {{-- Branch --}}
        <x-whs.table-cell>
          {{ $incident->branch->name ?? 'N/A' }}
        </x-whs.table-cell>

        {{-- Date --}}
        <x-whs.table-cell>
          <time datetime="{{ $incident->created_at->toIso8601String() }}">
            {{ $incident->created_at->format('d/m/Y') }}
          </time>
          <p class="whs-table__secondary-text">{{ $incident->created_at->diffForHumans() }}</p>
        </x-whs.table-cell>

        {{-- Status --}}
        <x-whs.table-cell>
          <span class="whs-chip whs-chip--status whs-chip--status-{{ strtolower($incident->status) }}">
            {{ ucfirst($incident->status) }}
          </span>
        </x-whs.table-cell>

        {{-- Actions --}}
        <x-whs.table-cell type="actions">
          <div class="whs-table__actions">
            <a href="{{ route('incidents.show', $incident) }}"
               class="whs-action-btn whs-action-btn--icon"
               aria-label="View incident {{ $incident->incident_number }}">
              <i class="icon-base ti ti-eye"></i>
              <span>View</span>
            </a>
            @can('incident.edit')
              <a href="{{ route('incidents.edit', $incident) }}"
                 class="whs-action-btn whs-action-btn--icon"
                 aria-label="Edit incident {{ $incident->incident_number }}">
                <i class="icon-base ti ti-pencil"></i>
                <span>Edit</span>
              </a>
            @endcan
            <button type="button" class="whs-action-btn whs-action-btn--icon" data-action-menu="{{ $incident->id }}">
              <i class="icon-base ti ti-dots-vertical"></i>
            </button>
          </div>
        </x-whs.table-cell>
      </x-whs.table-row>
    @empty
      <tr>
        <td colspan="9" class="whs-table__empty">
          <div class="whs-empty-state">
            <i class="icon-base ti ti-file-alert"></i>
            <p class="whs-empty-state__title">No incidents found</p>
            <p class="whs-empty-state__description">Try adjusting your search or filters</p>
          </div>
        </td>
      </tr>
    @endforelse
  </x-slot>

  {{-- Pagination --}}
  <x-slot name="footer">
    <div class="whs-table__pagination">
      <div class="whs-table__pagination-info">
        Showing {{ $incidents->firstItem() ?? 0 }} - {{ $incidents->lastItem() ?? 0 }} of {{ $incidents->total() }}
      </div>
      <div class="whs-table__pagination-controls">
        {{ $incidents->links() }}
      </div>
    </div>
  </x-slot>
</x-whs.table>
```

### Step 3: Update Main Index View

Update your main index file (e.g., `resources/views/content/IncidentManagement/Index.blade.php`)

**Changes Needed**:

1. **Add view toggle support** (if you want optional card view):
```blade
<div class="whs-layout{{ request('view', 'table') !== 'cards' ? ' whs-layout--full-width' : '' }}">
  <div class="whs-main">
    <div class="whs-section-heading">
      <div>
        <h2>Incident Management</h2>
        <p>Track and manage workplace safety incidents.</p>
      </div>
      <div class="whs-section-heading__actions">
        {{-- View Toggle (Optional) --}}
        <div class="btn-group whs-view-toggle" role="group" aria-label="View mode">
          <button type="button" class="btn btn-outline-secondary btn-sm" data-view="cards" {{ request('view', 'table') === 'cards' ? 'aria-pressed="true"' : '' }}>
            <i class="ti ti-layout-grid"></i>
            <span class="d-none d-md-inline ms-1">Cards</span>
          </button>
          <button type="button" class="btn btn-outline-secondary btn-sm" data-view="table" {{ request('view', 'table') === 'table' ? 'aria-pressed="true"' : '' }}>
            <i class="ti ti-table"></i>
            <span class="d-none d-md-inline ms-1">Table</span>
          </button>
        </div>
        <span class="whs-updated ms-3">Updated {{ now()->format('H:i') }}</span>
      </div>
    </div>

    {{-- Dense Table View (Default) --}}
    @if(request('view', 'table') !== 'cards')
      @include('content.IncidentManagement._table-view')
    @else
      {{-- Card View (Optional) --}}
      {{-- Keep your existing card view code here if you want to support it --}}
    @endif
  </div>
</div>
```

2. **Or make table view the only option** (simpler):
```blade
<div class="whs-layout whs-layout--full-width">
  <div class="whs-main">
    <div class="whs-section-heading">
      <div>
        <h2>Incident Management</h2>
        <p>Track and manage workplace safety incidents.</p>
      </div>
      <div class="whs-section-heading__actions">
        <span class="whs-updated">Updated {{ now()->format('H:i') }}</span>
      </div>
    </div>

    @include('content.IncidentManagement._table-view')
  </div>
</div>
```

### Step 4: Update Controller (If Needed)

Most controllers won't need changes, but ensure pagination is consistent:

```php
public function index()
{
    $incidents = Incident::with(['branch', 'reporter'])
        ->when(request('search'), function($query) {
            $query->where('title', 'like', '%'.request('search').'%')
                  ->orWhere('incident_number', 'like', '%'.request('search').'%')
                  ->orWhere('description', 'like', '%'.request('search').'%');
        })
        ->when(request('severity'), fn($q) => $q->where('severity', request('severity')))
        ->when(request('branch'), fn($q) => $q->where('branch_id', request('branch')))
        ->when(request('status'), fn($q) => $q->where('status', request('status')))
        ->orderByDesc('created_at')
        ->paginate(25); // Increase from 15 to 25 for table view

    return view('content.IncidentManagement.Index', compact('incidents'));
}
```

### Step 5: Add Quick View Integration (Optional but Recommended)

If your module has user references (reporter, assignee, inspector, etc.), integrate the global quick view modal:

```blade
{{-- Reported By column --}}
<x-whs.table-cell>
  @if($incident->reporter)
    <div class="d-flex align-items-center gap-2">
      <span>{{ $incident->reporter->name }}</span>
      <button type="button" class="whs-action-btn whs-action-btn--icon whs-action-btn--xs"
              data-quick-view
              data-member-id="{{ $incident->reporter->id }}"
              aria-label="Quick view {{ $incident->reporter->name }}">
        <i class="icon-base ti ti-eye"></i>
      </button>
    </div>
  @else
    <span class="whs-table__empty-text">N/A</span>
  @endif
</x-whs.table-cell>
```

**Required attributes**:
- `data-quick-view` - Identifies trigger element
- `data-member-id="{user_id}"` - Employee/User ID to fetch

See `QUICK_VIEW_USAGE.md` for complete documentation.

---

## 📊 Module-Specific Column Configurations

### Incident Management
**Columns**: ID, Title, Severity, Reported By, Branch, Date, Status, Actions
**Special Features**: Severity color coding, overdue indicators
**Quick View**: Reporter, Investigator

### Vehicle Management
**Columns**: Registration, Make/Model, Branch, Assigned To, Status, Next Service, Actions
**Special Features**: Service due warnings, assignment status
**Quick View**: Assigned Driver

### Inspection Management
**Columns**: Number, Vehicle, Inspector, Date, Result, Actions
**Special Features**: Pass/Fail badges, overdue inspections
**Quick View**: Inspector

### Risk Assessment
**Columns**: ID, Description, Risk Level, Likelihood, Impact, Owner, Status, Actions
**Special Features**: Risk matrix visualization, priority sorting
**Quick View**: Risk Owner

### Branch Management
**Columns**: Code, Name, Location, Manager, Employees, Status, Actions
**Special Features**: Employee count, contact info quick view
**Quick View**: Branch Manager

### Safety Inspections
**Columns**: Number, Type, Location, Inspector, Date, Result, Actions
**Special Features**: Compliance status, checklist completion
**Quick View**: Inspector

### CAPA Management
**Columns**: ID, Title, Type, Assigned To, Due Date, Status, Actions
**Special Features**: Overdue indicators, priority levels
**Quick View**: Assigned Person

### Journey Management
**Columns**: Journey ID, Driver, Vehicle, Route, Date, Status, Actions
**Special Features**: Duration, distance tracking
**Quick View**: Driver

### Maintenance Scheduling
**Columns**: Task ID, Vehicle, Type, Scheduled Date, Completed Date, Status, Actions
**Special Features**: Upcoming/overdue indicators
**Quick View**: Technician (if assigned)

### Emergency Response
**Columns**: Incident ID, Type, Location, Response Team, Date, Status, Actions
**Special Features**: Urgency levels, response time tracking
**Quick View**: Response Team Leader

### Contractor Management
**Columns**: Company, Contact Person, Service Type, Status, Compliance, Actions
**Special Features**: Compliance expiry warnings
**Quick View**: Contact Person

### Document Management
**Columns**: Title, Type, Category, Uploaded By, Date, Status, Actions
**Special Features**: Document icons, file size
**Quick View**: Uploaded By

### Training Management
**Columns**: Course, Attendees, Trainer, Date, Status, Completion, Actions
**Special Features**: Completion percentage, expiry dates
**Quick View**: Trainer, Attendees

### Warehouse Equipment
**Columns**: ID, Name, Type, Location, Status, Last Inspection, Actions
**Special Features**: Inspection due dates, maintenance alerts
**Quick View**: Inspector, Assigned To

### Permit to Work
**Columns**: Permit ID, Type, Requestor, Location, Date, Status, Actions
**Special Features**: Approval workflow status, expiry warnings
**Quick View**: Requestor, Approver

---

## 🎨 Component API Reference

### `<x-whs.table-toolbar>`

**Props**:
- `searchPlaceholder` (string) - Placeholder text for search input
- `showViewToggle` (bool, default: false) - Show card/table toggle
- `showExport` (bool, default: false) - Show export button

**Slots**:
- `search` - Custom search input
- `filters` - Filter pills/chips
- `bulkActions` - Bulk action buttons
- `actions` - Primary action buttons (e.g., Add New)

### `<x-whs.table>`

**Props**:
- `density` ('compact'|'comfortable'|'spacious', default: 'comfortable')
- `striped` (bool, default: true) - Alternating row colors
- `hover` (bool, default: true) - Hover effect on rows

**Slots**:
- `header` - Table header (`<tr>` with `<th>` elements)
- `body` - Table body (use `<x-whs.table-row>` components)
- `footer` - Pagination and table info

### `<x-whs.table-row>`

**Props**:
- `id` (int) - Record ID for bulk selection

**Slots**:
- Default slot - Use `<x-whs.table-cell>` components

### `<x-whs.table-cell>`

**Props**:
- `type` ('checkbox'|'actions'|null, default: null)

**Usage**:
```blade
<x-whs.table-cell>Regular cell content</x-whs.table-cell>
<x-whs.table-cell type="checkbox"><input type="checkbox" /></x-whs.table-cell>
<x-whs.table-cell type="actions">Action buttons</x-whs.table-cell>
```

---

## 🧪 Testing Checklist

For each migrated module, test:

### Data Scenarios
- [ ] 0 records (empty state with helpful message)
- [ ] 1 record (table renders correctly)
- [ ] 10-25 records (pagination not needed)
- [ ] 50+ records (pagination works)
- [ ] 100+ records (performance acceptable)

### Functionality
- [ ] Search works across relevant columns
- [ ] Filter pills display and remove correctly
- [ ] Bulk selection works (select all, individual)
- [ ] Bulk actions trigger correctly
- [ ] Sorting works on sortable columns
- [ ] Pagination links work
- [ ] Quick View modal opens (if applicable)
- [ ] Action buttons (view, edit, delete) work

### Responsive Design
- [ ] Desktop (>1024px) - All columns visible
- [ ] Tablet (768-1024px) - Table scrolls horizontally
- [ ] Mobile (<768px) - Consider card view or minimal table

### Theme Compatibility
- [ ] Light theme styling correct
- [ ] Dark theme styling correct
- [ ] Status badges use correct colors
- [ ] Severity chips use correct colors
- [ ] Icons display properly

### Accessibility
- [ ] Keyboard navigation works
- [ ] Screen reader labels present (`aria-label`, `aria-labelledby`)
- [ ] Focus indicators visible
- [ ] Color contrast meets WCAG AA

---

## 🚨 Common Issues & Solutions

### Issue: Table overflows on mobile
**Solution**: Add horizontal scroll or implement responsive card view:
```blade
<div class="table-responsive">
  <x-whs.table>...</x-whs.table>
</div>
```

### Issue: Quick View button doesn't work
**Solution**: Ensure:
1. Global modal exists in `commonMaster.blade.php`
2. JavaScript loaded (`resources/js/app.js`)
3. Hard refresh browser (`Ctrl + Shift + R`)
4. See `TROUBLESHOOTING_QUICK_VIEW.md`

### Issue: Styling looks wrong
**Solution**:
1. Clear view cache: `php artisan view:clear`
2. Rebuild assets: `npm run build`
3. Clear browser cache

### Issue: Filters don't persist
**Solution**: Use `request('filter_name')` to populate filter values:
```blade
<select name="severity">
  <option value="">All Severities</option>
  <option value="low" {{ request('severity') === 'low' ? 'selected' : '' }}>Low</option>
  <option value="medium" {{ request('severity') === 'medium' ? 'selected' : '' }}>Medium</option>
  <option value="high" {{ request('severity') === 'high' ? 'selected' : '' }}>High</option>
</select>
```

---

## 📈 Performance Optimization

### Eager Loading
Always eager load relationships to avoid N+1 queries:
```php
$incidents = Incident::with(['branch', 'reporter', 'assignedTo'])
    ->paginate(25);
```

### Pagination
Use 25 items per page for table view (better for scanning):
```php
->paginate(25)
```

### Selective Columns
For large tables, select only needed columns:
```php
$incidents = Incident::select(['id', 'incident_number', 'title', 'severity', 'status', 'created_at'])
    ->with(['branch:id,name', 'reporter:id,name'])
    ->paginate(25);
```

---

## 🔗 Related Documentation

- **Component Library**: See `resources/views/components/whs/` for component source code
- **Quick View Usage**: See `QUICK_VIEW_USAGE.md` for employee quick view integration
- **Troubleshooting**: See `TROUBLESHOOTING_QUICK_VIEW.md` for common issues
- **Standardization Plan**: See `DENSE_TABLE_STANDARDIZATION_PLAN.md` for rollout timeline

---

## ✅ Migration Completion Checklist

- [ ] Created `_table-view.blade.php` partial
- [ ] Updated main `Index.blade.php` view
- [ ] Updated controller for pagination (if needed)
- [ ] Added Quick View integration (if applicable)
- [ ] Tested all data scenarios (0, 1, 10, 50, 100+ records)
- [ ] Verified search and filter functionality
- [ ] Confirmed responsive behavior
- [ ] Validated dark/light theme compatibility
- [ ] Tested keyboard navigation
- [ ] Checked accessibility compliance
- [ ] Cleared caches and tested in browser
- [ ] Documented any module-specific customizations

---

**Ready to migrate!** 🚀

Use this guide as a blueprint for converting each of the 19 modules to the Dense Table UI standard.
