# Sensei Theme Integration - Dense Table System

## CSS Token Mapping Analysis

### Available Sensei Tokens

#### Background System
- `--sensei-bg-page` - Main page background (dark: #0f172a, light: #f8fafc)
- `--sensei-surface` - Card backgrounds (dark: rgba(26,34,56,0.75), light: rgba(255,255,255,0.90))
- `--sensei-surface-strong` - Emphasized panels (dark: rgba(30,38,60,0.88), light: rgba(255,255,255,0.95))
- `--sensei-surface-hover` - Hover states (dark: rgba(34,44,70,0.92), light: #fff)

#### Text Hierarchy (WCAG AAA Compliant)
- `--sensei-text-primary` - Headings, names (dark: rgba(255,255,255,0.95), light: #0f172a)
- `--sensei-text-secondary` - Subtitles, roles (dark: rgba(255,255,255,0.75), light: #334155)
- `--sensei-text-tertiary` - Labels, metadata (dark: rgba(255,255,255,0.60), light: #475569)
- `--sensei-text-metadata` - Timestamps (dark: rgba(255,255,255,0.65), light: #64748b)
- `--sensei-text-muted` - Empty states (dark: rgba(255,255,255,0.50), light: #94a3b8)
- `--sensei-text-link` - Links, IDs (dark: #60a5fa, light: #2563eb)

#### Border System
- `--sensei-border` - Default borders (dark: rgba(255,255,255,0.12), light: rgba(15,23,42,0.12))
- `--sensei-border-light` - Lighter accents (dark: rgba(255,255,255,0.20), light: rgba(15,23,42,0.20))

#### Accent Colors
- `--sensei-accent` - Primary brand (dark: #56c3ff, light: #0ea5e9)
- `--sensei-accent-soft` - Soft background (dark/light: rgba with 12-20% opacity)
- `--sensei-success` / `--sensei-success-soft`
- `--sensei-warning` / `--sensei-warning-soft`
- `--sensei-alert` / `--sensei-alert-soft`
- `--sensei-info` / `--sensei-info-soft`

#### Shadows
- `--sensei-shadow-card` - Card elevation
- `--sensei-shadow-hover` - Hover elevation
- `--sensei-shadow-modal` - Modal/dropdown

#### Spacing
- `--sensei-spacing-2xs` (8px) → `--sensei-spacing-xl` (48px)
- `--sensei-spacing` - Default (24px)

#### Border Radius
- `--sensei-radius-sm` (12px)
- `--sensei-radius` (16px)
- `--sensei-radius-lg` (20px)

#### Effects
- `--sensei-blur` (dark: 26px, light: 16px)
- `--sensei-transition` (220ms ease)

---

## Current Hard-Coded Values → Token Replacements

### table-toolbar.blade.php

| Current Value | Sensei Token | Usage |
|--------------|--------------|-------|
| `var(--sensei-bg-surface)` | ✅ Already correct | Toolbar background |
| `var(--sensei-border-base)` | ❌ **Change to** `var(--sensei-border)` | Toolbar border |
| `var(--sensei-text-primary)` | ✅ Already correct | Title text |
| `var(--sensei-text-secondary)` | ✅ Already correct | Description text |
| `rgba(90, 139, 255, 0.08)` | `var(--sensei-accent-soft)` | Bulk actions bg |
| `rgba(90, 139, 255, 0.25)` | Custom border blend | Bulk actions border |
| `var(--sensei-brand-primary)` | `var(--sensei-accent)` | Count text |
| `rgba(255, 255, 255, 0.95)` | `var(--sensei-surface-strong)` | Light theme toolbar |
| `rgba(15, 23, 42, 0.12)` | `var(--sensei-border)` | Light theme border |
| `rgba(59, 130, 246, 0.08)` | `var(--sensei-accent-soft)` | Light bulk actions |
| `rgba(59, 130, 246, 0.25)` | Custom light border | Light bulk border |

### table.blade.php

| Current Value | Sensei Token | Usage |
|--------------|--------------|-------|
| `var(--sensei-text-primary)` | ✅ Already correct | Table text |
| `var(--sensei-bg-surface)` | ✅ Already correct | Header background |
| `var(--sensei-border-base)` | ❌ **Change to** `var(--sensei-border)` | Header border |
| `var(--sensei-text-secondary)` | ✅ Already correct | Header labels |
| `var(--sensei-border-base)` | ❌ **Change to** `var(--sensei-border)` | Cell borders |
| Hard-coded padding | ✅ Keep as-is | Density variants |
| `var(--sensei-brand-primary)` | `var(--sensei-accent)` | Sort indicators |

### _table-view.blade.php

| Current Value | Sensei Token | Usage |
|--------------|--------------|-------|
| `var(--sensei-bg-surface)` | ✅ Already correct | Dense view wrapper |
| `var(--sensei-border-base)` | ❌ **Change to** `var(--sensei-border)` | Wrapper border |
| `rgba(90, 139, 255, 0.12)` | `var(--sensei-accent-soft)` | Filter chip bg |
| `rgba(90, 139, 255, 0.25)` | Custom accent border | Filter chip border |
| `rgba(90, 139, 255, 0.2)` | Darker accent-soft | Remove button bg |
| `rgba(90, 139, 255, 0.3)` | Hover variant | Remove hover |
| `var(--sensei-brand-primary)` | `var(--sensei-accent)` | Links, counts |
| `rgba(255, 255, 255, 0.95)` | `var(--sensei-surface-strong)` | Light theme wrapper |
| `rgba(15, 23, 42, 0.12)` | `var(--sensei-border)` | Light theme border |
| `rgba(59, 130, 246, 0.12)` | `var(--sensei-accent-soft)` | Light filter chips |

---

## Implementation Plan

### Phase 1: Token Standardization
1. Replace all `--sensei-border-base` with `--sensei-border`
2. Replace all `--sensei-brand-primary` with `--sensei-accent`
3. Replace all `--sensei-bg-surface` consistency checks

### Phase 2: Accent Color Integration
1. Replace hard-coded `rgba(90, 139, 255, ...)` with `--sensei-accent-soft`
2. Create custom accent border token for intermediate opacity
3. Replace light theme `rgba(59, 130, 246, ...)` with light theme tokens

### Phase 3: Light Theme Overrides
1. Consolidate light theme overrides using `[data-bs-theme='light']`
2. Remove redundant hard-coded rgba values
3. Use Sensei light theme tokens consistently

### Phase 4: Validation
1. Test in dark mode - verify all colors use tokens
2. Test in light mode - verify proper contrast
3. Check accessibility (WCAG AA minimum)
4. Validate responsive behavior

---

## Custom Token Additions Needed

Since Sensei doesn't have intermediate opacity variants for accent borders, we'll use CSS color-mix or calculated opacity:

```css
/* Custom accent border token (slightly stronger than soft) */
.whs-filter-chip {
  border-color: color-mix(in srgb, var(--sensei-accent) 25%, transparent);
}

/* Or use inline rgba calculation */
.whs-filter-chip {
  border: 1px solid rgba(from var(--sensei-accent) r g b / 0.25);
}

/* Fallback for older browsers */
.whs-filter-chip {
  border: 1px solid var(--sensei-accent);
  border-opacity: 0.25;
}
```

---

## Benefits of Sensei Integration

✅ **Theme Consistency**: All components use unified design tokens
✅ **Automatic Theme Switching**: Dark/light modes work seamlessly
✅ **WCAG Compliance**: All text meets AA/AAA contrast standards
✅ **Maintainability**: Single source of truth for colors
✅ **Future-Proof**: Easy to update entire theme from one file
✅ **Performance**: CSS variables are highly optimized

---

## Testing Checklist

- [ ] Dark mode: All backgrounds visible
- [ ] Dark mode: All text readable (WCAG AA)
- [ ] Dark mode: Hover states work correctly
- [ ] Light mode: All backgrounds visible
- [ ] Light mode: All text readable (WCAG AA)
- [ ] Light mode: Hover states work correctly
- [ ] Filter chips display correctly in both themes
- [ ] Bulk actions bar displays correctly
- [ ] Table rows have proper hover effects
- [ ] Sortable column indicators visible
- [ ] Responsive behavior maintained
- [ ] Keyboard navigation works
- [ ] Screen reader compatibility

---

**Created**: 2025-11-02
**Purpose**: Guide Sensei theme integration for Dense Table View system
**Target Files**: table-toolbar.blade.php, table.blade.php, _table-view.blade.php
