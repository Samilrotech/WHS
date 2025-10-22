## Sensei Migration Plan – WHS5

### 1. Objectives & Scope
- Deliver a cohesive Sensei design system across the entire WHS5 application (Laravel + Blade).
- Preserve existing backend functionality, route structure, and data models.
- Replace all Vuexy/Bootstrap/Tailwind remnants with Sensei components, tokens, and interactions (dark & light variants).
- Provide a reusable component toolkit so future features ship with the Sensei look by default.

### 2. Module Inventory & Priority

| Priority | Module / Area | Routes (prefix) | Primary Views | Key Sensei Requirements |
|----------|----------------|-----------------|---------------|-------------------------|
| P1 | **Branch Management** | `/branches` | `content/branches/*` | Sensei breadcrumb, forms, action buttons, directory list/table, delete confirmation, metrics. |
| P1 | **Incident Management** | `/incidents` | `content/incidents/*` | Sensei hero, filter pills, incident cards, timeline/log, photo gallery, severity chips, forms. |
| P1 | **Risk Assessment** | `/risk` | `content/risk/*`, `content/risk-assessment/*` | Heatmap/matrix, severity chips, Sensei forms, legend/tooltips, summary tables. |
| P2 | Emergency Response | `/emergency` | `content/emergency/*` | Alert hero, response timeline, action cards, escalation chips. |
| P2 | Vehicle Management | `/vehicles` | `content/vehicles/*` | Asset cards, status chips, Sensei tables, map wrapper. |
| P2 | Journey Management | `/journeys` | `content/JourneyManagement/*` | Map panel, itinerary timeline, risk level chips. |
| P2 | Maintenance Scheduling | `/maintenance` | `content/MaintenanceScheduling/*` | Calendar theme, job cards, priority chips, scheduler modals. |
| P2 | Warehouse Equipment | `/warehouse-equipment` | `content/WarehouseEquipment/*` | Inventory table, inspection log, upload dropzone. |
| P3 | Inspection Management & Safety Inspections | `/inspections`, `/safety-inspections` | Respective folders | Checklist component, inspection timeline, compliance scorecards. |
| P3 | Permit to Work | `/permit` | `content/PermitToWork/*` | Multi-step wizard, approval chips, attachments. |
| P3 | CAPA Management | `/capa` | `content/CAPAManagement/*` | Corrective action board, evidence attachments. |
| P3 | Team Management | `/team` | `content/TeamManagement/*` | People directory, role badges, filters. |
| P3 | Training Management | `/training/*` | `content/Training*`, `content/TrainingManagement/*` | Course cards, session calendar, certification chips. |
| P3 | Contractor Management | `/contractors` | `content/ContractorManagement/*` | Contractor profile cards, sign-in/out log. |
| P3 | Document Management | `/documents` | `content/DocumentManagement/*` | Library cards, preview modal, version history. |
| P3 | Compliance Reporting | `/compliance` | `content/ComplianceReporting/*` | Report cards, filter drawer, export actions. |
| P3 | Vehicle Reporting Dashboard | `/vehicle-reporting` | `content/VehicleReportingDashboard/*` | Tabbed analytics, charts, tables. |
| P3 | Account Settings | — | `content/pages/pages-account-settings-*` | Sensei forms, toggles, password meter. |
| P3 | Auth & Misc | — | `auth/*`, `offline.blade.php`, `content/pages/pages-misc-*` | Login/offline/error pages in Sensei style. |
| P3 | System (Users/Roles) | TBD | locate in views | Identify and migrate user & roles management. |

> **Next step**: Confirm locations for user/role/permissions views and add them to the matrix.

### 3. Sensei Component Roadmap

| Component / Utility | Description | Owner | Status |
|---------------------|-------------|-------|--------|
| Form elements | Inputs, textareas, selects, toggles, validation, sections | TBD | Done (v1) |
| Breadcrumb | Reusable Sensei breadcrumb partial | TBD | Done |
| Action buttons | Button/link variants with icon support (`default`, `ghost`, `danger`) | TBD | Done |
| Status chips | Standard severity/workflow badges (critical/high/medium/low, approved/pending/etc.) | TBD | ☐ |
| Filter pills / tabs | Pill navigation with active states | TBD | ☐ |
| Data table wrapper | Sensei-styled table (headers, hover, empty state) + integration strategy | TBD | ☐ |
| Modal / drawer | Glassmorphism modal with header/body/footer slots | TBD | ☐ |
| Timeline / activity log | Component for chronological events with icons and metadata | TBD | ☐ |
| Upload dropzone | Drag-and-drop file uploader + preview list | TBD | ☐ |
| Calendar theme | FullCalendar (or alternative) theme overrides | TBD | ☐ |
| Chart palette helper | Central config for Apex/Chart.js colors, typography | TBD | ☐ |
| Map wrapper | Sensei container for Mapbox/Leaflet maps | TBD | ☐ |
| Empty states | Standardized empty card with icon, copy, CTA | TBD | ☐ |

> Document each component’s Blade signature, props, states, and CSS tokens before implementation.

### 4. Module Conversion Checklist Template

For each module create a checklist (example for Branch Management):

- [ ] Replace layout wrapper with `<div class="whs-shell">` / `<x-whs.hero>`
- [ ] Swap metrics to `<x-whs.metric-card>`
- [ ] Convert breadcrumbs to `<x-whs.breadcrumb>`
- [ ] Replace forms with Sensei form components
- [ ] Replace tables/lists with Sensei table wrapper
- [ ] Replace action buttons with `<x-whs.action-button>`
- [ ] Update modals/dialogs to Sensei modal
- [ ] Replace status badges with Sensei chips
- [ ] Ensure empty states use `<x-whs.empty>`
- [ ] Remove legacy CSS/JS imports (Bootstrap, DataTables theme, etc.)
- [ ] Verify responsive breakpoints and focus states
- [ ] Capture before/after screenshots for QA

Maintain a tracker (`docs/SENSEI_PROGRESS.csv` or Notion) to mark modules as In Progress / QA / Done.

### 5. Testing & QA Plan

- **Visual QA Route**: create `/styleguide/sensei` route rendering every Sensei component for quick inspection.
- **Cross-browser**: Chrome, Edge, Safari (Mac), Firefox – desktop & responsive breakpoints.
- **Theme switch**: test both dark and light modes if supported by `data-bs-theme`.
- **Functional smoke tests**: run module workflows (create/edit/delete) after each conversion.
- **Automated snapshots** (optional): add Playwright screenshot diff for critical pages post-conversion.

### 6. Open Questions & Follow-ups

- Locate and document user/role/permissions management Blade views.  
- Confirm map providers (Leaflet/Mapbox) and design requirements for Sensei map wrapper.  
- Determine if we must retrofit Inertia/React requirement from original WHS brief or stay Blade-first.  
- Define accessibility targets (contrast ratios, keyboard navigation) – needed as part of Sensei spec.  
- Coordinate with backend for any new API data required by metrics (e.g., TRIFR, compliance scores).  

### 7. Execution Timeline (High-level)

1. **Week 1** – Build shared Sensei components; convert Branch Management.  
2. **Week 2** – Convert Incident Management (list + detail + forms).  
3. **Week 3** – Convert Risk Assessment (matrix + forms); update charts/tables helpers.  
4. **Weeks 4–5** – Convert Emergency Response, Vehicle Management, Journey/Maintenance modules.  
5. **Weeks 6–7** – Remaining WHS modules (Inspections, Permit, CAPA, Training, Contractor, Document, Compliance).  
6. **Week 8** – System/account/auth pages, QA sweep, accessibility checks, regression testing.  

> Adjust timeline as component work completes and priority feedback arrives.

---

**Document owner:** _To be assigned_  
**Last updated:** 2025-10-20
