# PWA Icons Directory

## Required Icons

Generate PWA icons in the following sizes and place them in this directory:

### App Icons
- `icon-72x72.png` - 72x72px
- `icon-96x96.png` - 96x96px
- `icon-128x128.png` - 128x128px
- `icon-144x144.png` - 144x144px
- `icon-152x152.png` - 152x152px
- `icon-192x192.png` - 192x192px (maskable)
- `icon-384x384.png` - 384x384px
- `icon-512x512.png` - 512x512px (maskable)

### Shortcut Icons
- `shortcut-incident.png` - 96x96px (Report Incident)
- `shortcut-emergency.png` - 96x96px (Emergency Alert)
- `shortcut-inspection.png` - 96x96px (Safety Inspection)

### Screenshots
- `screenshot-desktop.png` - 1920x1080px (Desktop view)
- `screenshot-mobile.png` - 750x1334px (Mobile view)

## Icon Generation Tools

**Recommended:**
- [PWA Asset Generator](https://github.com/elegantapp/pwa-asset-generator)
- [RealFaviconGenerator](https://realfavicongenerator.net/)
- [PWA Builder](https://www.pwabuilder.com/imageGenerator)

**Quick Generation:**
```bash
npm install -g pwa-asset-generator
pwa-asset-generator logo.svg ./public/images/pwa --icon-only
```

## Design Guidelines

### Main App Icon
- Use WHS4 logo with transparent background
- Ensure visibility on light and dark backgrounds
- Include safety-related imagery (helmet, shield, etc.)
- Use brand colors: Primary #7367f0 (purple)

### Maskable Icons
- Add 10% safe zone padding around important elements
- Icons should work as circles (iOS) and squares (Android)
- Test with maskable.app

### Shortcut Icons
- Incident: Red/orange alert icon
- Emergency: Red SOS/alarm icon
- Inspection: Blue/green checklist icon
