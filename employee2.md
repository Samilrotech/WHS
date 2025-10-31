## Rotech WHS Team Management UI Revamp

### 1. Core Goals
- Improve visibility of all employees without long scrolling.
- Enable quick search, sorting, and filtering.
- Maintain clean, readable layout in dark theme.
- Preserve core functionality (certifications, leave, contact info) while optimizing layout.

---

### 2. Recommended Layout Changes

#### 2.1 Switch to Table or Hybrid List View
- Replace card layout with a compact table view.
- Suggested columns:
  - Name
  - Branch / Office
  - Status (Active / On Leave / Inactive)
  - Last Submission / Inspection
  - Certifications Expiring
  - Contact (email or phone)
  - Actions (context menu)

#### 2.2 Pagination or Infinite Scroll
- Show 50 employees per page.
- Provide pagination controls (Prev / Next) or lazy-load with a “Load more” button.
- For larger datasets, enable virtualized scrolling for smooth performance.

#### 2.3 Search and Filter Controls
- Keep the main search bar but add:
  - Filter by Active / On Leave / Inactive.
  - Filter by Branch / Region.
  - Filter by Expiring Certifications.
- Add sorting by clicking on column headers.

#### 2.4 Inline Actions
- Replace the full button row (“Open”, “Edit”, “Certs”, etc.) with a compact context menu (⋮).
- Add bulk selection checkboxes for group actions (e.g., mark multiple on leave).

#### 2.5 Readability and Visual Hierarchy
- Alternate row background shading for clarity.
- Highlight rows on hover.
- Freeze header row when scrolling.
- Allow horizontal scroll or column selection when many columns exist.

#### 2.6 Side Drawer or Modal for Details
- When clicking a row, open a right-side drawer or modal showing full employee details.
- Include fields like: certifications, training, vehicle, and contact info.
- This avoids clutter in the main view.

#### 2.7 Performance Optimizations
- Lazy-load images if profile photos exist.
- Implement virtualization for long lists.
- Use efficient backend queries for filtering and sorting.

---

### 3. Example Layout Sketch
```
╔══════════════════════════ Team Management ══════════════════════════╗
│ Search [___________]  Filter: [Active ▼] [Branch ▼] [Cert Expiring ▼]  [ + Add Member ] │
├───────────────────────────────────────────────────────────────────────────┤
│ Columns: ☐ Name | Branch | Status | Last Submission | Certs Expiring | Actions │
├───────────────────────────────────────────────────────────────────────────┤
│ [ ] Aleisha Trewarn     NSW HQ        Active    1 day ago      0           ⋮ │
│ [ ] Angie Kate Ovenden  NT Office     Active    1 day ago      0           ⋮ │
│ [ ] Cameron Ronald Ovenden QLD        Active    21 hrs ago     PASS        ⋮ │
│ [ ] Damien Loudon        VIC Branch   Active    1 day ago      –           ⋮ │
│ ... more rows ...                                                          │
├───────────────────────────────────────────────────────────────────────────┤
│ ← Prev  | Page 1 of N | Next →                                              │
╚═══════════════════════════════════════════════════════════════════════════╝
```

---

### 4. Implementation Notes
- Use a data-table framework:
  - React: `react-table`, `ag-grid`, `Material-UI DataGrid`.
  - Vanilla JS: implement sorting/filtering via AJAX.
- Include accessibility features (keyboard navigation, ARIA labels).
- Keep consistent dark theme colors and font hierarchy.
- Test with large employee datasets to ensure smooth performance.

---

### 5. Visual References
- **Dribbble & Behance Examples**: modern HR dashboards with data-table UI.
- **Eleken & Justinmind Guides**: emphasize scan-ability, logical ordering, and compact list layouts.

---

### 6. Summary
Convert the current long-scroll card interface into a structured table-based directory with search, filter, and sort. Use modal or drawer for full profiles, and apply performance optimizations. The new UI should feel like a professional HR dashboard—clean, efficient, and scalable for hundreds of employees.

