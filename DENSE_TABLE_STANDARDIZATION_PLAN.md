# Dense Table UI Standardization Plan

## 🎯 Objective

Standardize all listing pages across the WHS application to use the Dense Table View design pattern from Team Management, creating a consistent, professional, and high-performance UI.

---

## 📊 Scope - 19 Modules to Standardize

### ✅ Already Implemented (Reference)
1. **Team Management** (`/teams?view=table`) - Reference implementation

### 🔄 Priority 1: High-Traffic Core Modules (Implement First)
2. **Incident Management** (`/incidents`)
3. **Vehicle Management** (`/vehicles`)
4. **Inspection Management** (`/inspections`)
5. **Risk Assessment** (`/risk`)
6. **Branch Management** (`/branches`)

### 🔄 Priority 2: Essential Operations Modules
7. **Safety Inspections** (`/safety-inspections`)
8. **CAPA Management** (`/capa`)
9. **Journey Management** (`/journey`)
10. **Maintenance Scheduling** (`/maintenance`)
11. **Emergency Response** (`/emergency`)

### 🔄 Priority 3: Administrative Modules
12. **Contractor Management** (`/contractors`)
13. **Document Management** (`/documents`)
14. **Training Management** (`/training`)
15. **Warehouse Equipment** (`/warehouse-equipment`)
16. **Permit to Work** (`/permit-to-work`)

### 🔄 Priority 4: Reporting & Audit Modules
17. **Compliance Reporting** (`/compliance`)
18. **Audit Management** (`/audit`)
19. **Vehicle Reporting Dashboard** (`/vehicle-reporting`)

---

## 🏗️ Architecture Strategy

### Phase 1: Component Library Creation
**Goal**: Extract reusable components from Team Management table view

#### Components to Create:
1. **`<x-whs.dense-table>`** - Main table wrapper with Sensei theming
2. **`<x-whs.table-toolbar>`** - Search, filters, view toggle, action buttons
3. **`<x-whs.table-row>`** - Reusable table row with consistent styling
4. **`<x-whs.action-menu>`** - Standardized action buttons (view, edit, delete)
5. **`<x-whs.status-badge>`** - Status indicators with color coding
6. **`<x-whs.empty-state>`** - Consistent empty state messaging

### Phase 2: CSS Architecture
**Goal**: Create module-agnostic table styling system

#### CSS Files:
- `resources/css/dense-table-system.css` - Core table styles
- Integration with existing Sensei theme tokens

### Phase 3: JavaScript Enhancements
**Goal**: Add interactive features to all tables

#### Features:
- Row selection (checkboxes)
- Bulk actions
- Column sorting
- Inline filtering
- Quick actions menu
- Keyboard navigation

---

## 🎨 Design System Specifications

### Table Layout
```
┌─────────────────────────────────────────────────────────────┐
│  [Search]  [Filter Pills]  [View Toggle]  [+ Add Button]   │
├─────────────────────────────────────────────────────────────┤
│  ☐  ID      | Name       | Status  | Branch  | Actions     │
├─────────────────────────────────────────────────────────────┤
│  ☐  DRV-001 | Harper C.  | Active  | Sydney  | [👁️][✏️][⋮] │
│  ☐  EM0001  | Samil C.   | Active  | Sydney  | [👁️][✏️][⋮] │
│  ☐  ADMIN01 | System A.  | Active  | N/A     | [👁️][✏️][⋮] │
├─────────────────────────────────────────────────────────────┤
│  Showing 1-3 of 3 | [← 1 →]                                 │
└─────────────────────────────────────────────────────────────┘
```

### Responsive Behavior
- **Desktop (>1024px)**: Full table with all columns
- **Tablet (768-1024px)**: Condensed columns, scrollable
- **Mobile (<768px)**: Card view with expandable rows

---

## 📋 Implementation Checklist Per Module

### For Each Module:
- [ ] Analyze existing data structure and columns
- [ ] Map fields to dense table column definitions
- [ ] Create module-specific table partial
- [ ] Add search and filter logic
- [ ] Implement action buttons (view, edit, delete)
- [ ] Add status badges with appropriate colors
- [ ] Test pagination
- [ ] Verify responsive behavior
- [ ] Test dark/light theme compatibility
- [ ] Add keyboard navigation
- [ ] Test with 0, 1, 10, 100+ records

---

## 🔧 Module-Specific Configurations

### Incident Management
**Columns**: ID, Title, Severity, Reported By, Branch, Date, Status, Actions
**Special Features**: Severity color coding, overdue indicators

### Vehicle Management
**Columns**: Registration, Make/Model, Branch, Assigned To, Status, Next Service, Actions
**Special Features**: Service due warnings, assignment status

### Inspection Management
**Columns**: Number, Vehicle, Inspector, Date, Result, Actions
**Special Features**: Pass/Fail badges, overdue inspections

### Risk Assessment
**Columns**: ID, Description, Risk Level, Likelihood, Impact, Owner, Status, Actions
**Special Features**: Risk matrix visualization, priority sorting

### Branch Management
**Columns**: Code, Name, Location, Manager, Employees, Status, Actions
**Special Features**: Employee count, contact info quick view

### Safety Inspections
**Columns**: Number, Type, Location, Inspector, Date, Result, Actions
**Special Features**: Compliance status, checklist completion

### CAPA Management
**Columns**: ID, Title, Type, Assigned To, Due Date, Status, Actions
**Special Features**: Overdue indicators, priority levels

### Journey Management
**Columns**: Journey ID, Driver, Vehicle, Route, Date, Status, Actions
**Special Features**: Duration, distance tracking

### Maintenance Scheduling
**Columns**: Task ID, Vehicle, Type, Scheduled Date, Completed Date, Status, Actions
**Special Features**: Upcoming/overdue indicators

### Emergency Response
**Columns**: Incident ID, Type, Location, Response Team, Date, Status, Actions
**Special Features**: Urgency levels, response time tracking

### Contractor Management
**Columns**: Company, Contact Person, Service Type, Status, Compliance, Actions
**Special Features**: Compliance expiry warnings

### Document Management
**Columns**: Title, Type, Category, Uploaded By, Date, Status, Actions
**Special Features**: Document icons, file size

### Training Management
**Columns**: Course, Attendees, Trainer, Date, Status, Completion, Actions
**Special Features**: Completion percentage, expiry dates

### Warehouse Equipment
**Columns**: ID, Name, Type, Location, Status, Last Inspection, Actions
**Special Features**: Inspection due dates, maintenance alerts

### Permit to Work
**Columns**: Permit ID, Type, Requestor, Location, Date, Status, Actions
**Special Features**: Approval workflow status, expiry warnings

---

## 🚀 Implementation Timeline

### Week 1: Foundation (Days 1-2)
- ✅ Create reusable Blade components
- ✅ Build CSS architecture
- ✅ Set up JavaScript enhancements

### Week 1: Priority 1 Modules (Days 3-5)
- ✅ Incident Management
- ✅ Vehicle Management
- ✅ Inspection Management
- ✅ Risk Assessment
- ✅ Branch Management

### Week 2: Priority 2 Modules (Days 1-3)
- ✅ Safety Inspections
- ✅ CAPA Management
- ✅ Journey Management
- ✅ Maintenance Scheduling
- ✅ Emergency Response

### Week 2: Priority 3 Modules (Days 4-5)
- ✅ Contractor Management
- ✅ Document Management
- ✅ Training Management
- ✅ Warehouse Equipment
- ✅ Permit to Work

### Week 3: Priority 4 & Polish (Days 1-2)
- ✅ Compliance Reporting
- ✅ Audit Management
- ✅ Vehicle Reporting Dashboard
- ✅ Final testing and refinements

---

## 📈 Success Metrics

### Performance
- [ ] Page load time <1.5s for 100 records
- [ ] Smooth scrolling at 60fps
- [ ] Search response <200ms

### User Experience
- [ ] Consistent navigation across all modules
- [ ] Keyboard shortcuts work everywhere
- [ ] Mobile responsive on all pages
- [ ] Dark/light theme consistency

### Code Quality
- [ ] DRY - No duplicate table markup
- [ ] Components reused across 19 modules
- [ ] Consistent naming conventions
- [ ] Comprehensive documentation

---

## 🔄 Rollout Strategy

### Approach: Progressive Enhancement
1. **Backend Compatible**: All existing routes/controllers work unchanged
2. **View Layer Only**: Only Blade templates modified
3. **Feature Flag Ready**: Easy to roll back if needed
4. **Module by Module**: Test each before moving to next

### Testing Protocol
For each module:
1. Test with 0 records (empty state)
2. Test with 1 record
3. Test with 10-50 records (pagination)
4. Test with 100+ records (performance)
5. Test search and filters
6. Test all action buttons
7. Test dark/light theme
8. Test mobile responsiveness

---

## 📚 Documentation Deliverables

1. **Component Usage Guide** - How to use dense table components
2. **Module Migration Guide** - Step-by-step for each module
3. **Theme Customization Guide** - How to adjust colors, spacing
4. **Troubleshooting Guide** - Common issues and fixes

---

## 🎯 Next Steps

1. Create reusable Blade components in `resources/views/components/whs/`
2. Extract CSS into `resources/css/dense-table-system.css`
3. Build JavaScript enhancements in `resources/js/dense-table.js`
4. Start with Priority 1 modules
5. Iterate and refine based on feedback

---

**Ready to begin implementation!** 🚀
