## Rotech WHS Team Management Theme Review

### 1. Overview
The current dark theme presents a sleek professional style, but the readability and contrast hierarchy are too low. Users experience visual fatigue due to similar tonal ranges between backgrounds, text, and accent colors.

---

### 2. Observed Issues

| Area | Problem | Effect |
|------|----------|--------|
| Sidebar text | Insufficient contrast | Hard to read labels |
| Card background vs page background | Nearly identical tones | Cards visually blend into one another |
| Button set (Open, Edit, Certs, etc.) | All gray with little depth | No visual hierarchy, hard to distinguish primary actions |
| Secondary text | Too desaturated | Low readability on dark base |
| "Common Roles" section | Light-gray overlay on dark background | Causes visual haze and poor text sharpness |
| Status pills | Dark green and muted teal | Inconsistent contrast, appear dull |

---

### 3. Recommended Palette

Adopt a **neutral dark theme** with layered depth and clear tonal spacing:

```css
:root {
  --bg-page: #0f172a;          /* main background */
  --bg-surface: #1a2238;       /* cards and panels */
  --bg-hover: #222c46;         /* hover surfaces */
  --text-primary: #e9eef7;     /* main text */
  --text-secondary: #a8b2c6;   /* labels, metadata */
  --text-muted: #7d8598;       /* subdued */
  --accent: #56c3ff;           /* brighter cyan */
  --success: #3dd68c;          /* positive */
  --danger: #ff6b6b;           /* alert */
  --border-light: rgba(255,255,255,0.08);
}
```

This palette balances blue-gray depth with neutral whites for optimal WCAG readability.

---

### 4. Typography and Spacing
| Element | Current | Recommended |
|----------|----------|-------------|
| Body | ~14 px | 15–16 px, line-height 1.55 |
| Headings | same color as body | lighter tone #F1F6FC, weight 600 |
| Labels | similar tone | use #A8B2C6 with +0.2 px letter spacing |

Improve readability with distinct font weights and a slightly warmer text tone.

---

### 5. Card and Sidebar Depth
Use clear elevation and layering:

```css
.card {
  background: var(--bg-surface);
  border: 1px solid var(--border-light);
  box-shadow: 0 2px 8px rgba(0,0,0,0.3);
  border-radius: 12px;
}

.card:hover {
  background: var(--bg-hover);
  transition: background 0.25s ease;
}

.sidebar {
  background: #111827;
  border-right: 1px solid rgba(255,255,255,0.05);
}
```

These adjustments make the interface feel layered and structured.

---

### 6. Buttons and Status Pills
| Type | Color | Style |
|------|--------|--------|
| Primary (Open/Edit) | `#56C3FF` | Filled, white text |
| Secondary (Certs/Training) | `rgba(255,255,255,0.08)` | Outlined |
| Danger (Delete) | `#ff6b6b` | Filled, white text |
| Status Pill (Active) | `#3DD68C` background, `#052E18` text | Rounded, subtle glow |

Add `filter: brightness(1.1)` for hover feedback.

---

### 7. Common Roles Panel
Avoid foggy overlays by using semi-transparent layers and clear edges:

```css
.role-card {
  background: rgba(255,255,255,0.03);
  border: 1px solid rgba(255,255,255,0.07);
  color: var(--text-primary);
}

.role-card:hover {
  background: rgba(255,255,255,0.07);
}
```

Each role card becomes distinct while maintaining a consistent visual rhythm.

---

### 8. Visual Guidelines
Reference designs for readability and balance:
- **Material Design Dark Theme** – structured depth and clear hierarchy.
- **Shadcn/UI dark variant** – excellent tonal separation.
- **GitHub Dark Dimmed** – proof that contrast doesn’t have to mean harsh white text.

---

### 9. Summary
Main readability issues stem from low contrast and color proximity. By spacing tones, brightening text, defining layer depth, and establishing accent hierarchy, your Rotech WHS dashboard will achieve professional clarity and ease of use without losing its dark aesthetic.