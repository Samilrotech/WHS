# Browser Cache Debugging Guide

## Problem
Light theme CSS is compiled correctly but browser is displaying dark theme.

## Root Cause
Browser cache is serving old CSS file instead of new version with light theme overrides.

## Solution Steps

### 1. Hard Refresh (Try First)
```
Windows/Linux: Ctrl + Shift + R
Mac: Cmd + Shift + R
```

### 2. Empty Cache and Hard Reload (Chrome/Edge)
1. Open DevTools (F12)
2. **Right-click refresh button** (while DevTools open)
3. Select **"Empty Cache and Hard Reload"**

### 3. Manual Cache Clear
```
1. Ctrl + Shift + Delete
2. Check "Cached images and files"
3. Time range: "Last hour"
4. Click "Clear data"
5. Close browser completely
6. Reopen and test
```

### 4. Verify CSS Is Fresh (Not Cached)
**DevTools → Network Tab:**
- Look for `sensei-theme-BEYm7_DD.css`
- Size column should show actual bytes (e.g., "83.7 kB")
- Should NOT show "(disk cache)" or "(memory cache)"

### 5. Verify CSS Variables Are Set
**DevTools → Console:**
```javascript
const html = document.documentElement;
const style = window.getComputedStyle(html);
console.log('Theme:', html.getAttribute('data-bs-theme'));
console.log('Menu BG:', style.getPropertyValue('--sensei-menu-bg'));
console.log('Surface:', style.getPropertyValue('--sensei-surface'));
```

**Expected (Light Theme):**
```
Theme: light
Menu BG: rgba(255, 255, 255, 0.9)
Surface: rgba(255, 255, 255, 0.9)
```

**Wrong (Dark Theme - means cache not cleared):**
```
Theme: light
Menu BG: rgba(10, 12, 15, 0.72)
Surface: rgba(26, 34, 56, 0.75)
```

### 6. Test in Incognito Mode
```
Ctrl + Shift + N (Chrome/Edge)
Navigate to: http://127.0.0.1:8002/
```

If light theme works in Incognito, confirms cache issue in regular mode.

### 7. Nuclear Option: Clear All Site Data
**Chrome/Edge DevTools:**
1. F12 → Application tab
2. Left sidebar → Storage → **Clear site data**
3. Check all boxes
4. Click "Clear site data"
5. Close DevTools
6. Refresh page

## Verification Checklist

After clearing cache, verify:

- [ ] HTML has `data-bs-theme="light"` (DevTools → Elements)
- [ ] CSS file loads fresh (Network tab, no cache indicators)
- [ ] CSS variables show light values (Console test above)
- [ ] Sidebar has light background
- [ ] Tables have light backgrounds
- [ ] All panels/cards have light backgrounds
- [ ] Theme toggle button is visible
- [ ] Clicking toggle switches to dark theme correctly

## Technical Details

**CSS File:** `public/build/assets/sensei-theme-BEYm7_DD.css`
**Size:** 83.71 kB
**Contains:** 24 `[data-bs-theme='light']` selectors

**Light Theme Token Values:**
```css
[data-bs-theme='light'] {
  --sensei-menu-bg: rgba(255, 255, 255, 0.90);
  --sensei-surface: rgba(255, 255, 255, 0.90);
  --sensei-border: rgba(15, 23, 42, 0.12);
  --sensei-text-primary: #0f172a;
}
```

**Component Overrides Present:**
- `.layout-menu` - sidebar
- `.layout-navbar` - top nav
- `.sensei-table` - data tables
- `.whs-empty` - empty states
- `.sensei-glass` - glassmorphic surfaces

## If Still Not Working

Run this diagnostic in Console:
```javascript
// Comprehensive theme diagnostic
const html = document.documentElement;
const sidebar = document.querySelector('.layout-menu');
const table = document.querySelector('.sensei-table');

console.group('Theme Diagnostic');
console.log('1. HTML Theme Attribute:', html.getAttribute('data-bs-theme'));

const htmlStyle = window.getComputedStyle(html);
console.group('2. CSS Variables on <html>');
console.log('--sensei-menu-bg:', htmlStyle.getPropertyValue('--sensei-menu-bg'));
console.log('--sensei-surface:', htmlStyle.getPropertyValue('--sensei-surface'));
console.log('--sensei-text-primary:', htmlStyle.getPropertyValue('--sensei-text-primary'));
console.groupEnd();

if (sidebar) {
  const sidebarStyle = window.getComputedStyle(sidebar);
  console.group('3. Sidebar Computed Styles');
  console.log('Element:', sidebar);
  console.log('background:', sidebarStyle.background);
  console.log('background-color:', sidebarStyle.backgroundColor);
  console.groupEnd();
}

if (table) {
  const tableStyle = window.getComputedStyle(table);
  console.group('4. Table Computed Styles');
  console.log('Element:', table);
  console.log('background:', tableStyle.background);
  console.log('background-color:', tableStyle.backgroundColor);
  console.groupEnd();
}

console.groupEnd();
```

Copy and paste the console output to help debug further.
