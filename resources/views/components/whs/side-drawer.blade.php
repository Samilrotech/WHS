{{--
  WHS Side Drawer Component

  Purpose: Accessible slide-in panel for detailed views, forms, and contextual information
  Accessibility: WCAG 2.1 AA compliant with keyboard navigation, focus trap, and ARIA attributes

  Props:
  - id: string - Unique identifier for the drawer
  - title: string - Drawer header title
  - size: string (sm|md|lg|xl) - Drawer width
  - position: string (left|right) - Drawer slide-in direction
  - backdrop: bool - Show backdrop overlay (default: true)
  - closeButton: bool - Show close button (default: true)
  - footer: string - Optional footer content slot

  Usage:
  <x-whs.side-drawer id="employeeDetails" title="Employee Details" size="md">
    <p>Drawer content goes here</p>
    <x-slot:footer>
      <button class="btn btn-primary">Save</button>
    </x-slot:footer>
  </x-whs.side-drawer>

  Trigger:
  <button data-drawer-target="employeeDetails">Open Drawer</button>
--}}

@props([
    'id' => 'sideDrawer',
    'title' => 'Details',
    'size' => 'md',
    'position' => 'right',
    'backdrop' => true,
    'closeButton' => true,
])

@php
$sizeClass = match($size) {
    'sm' => 'drawer-sm',
    'md' => 'drawer-md',
    'lg' => 'drawer-lg',
    'xl' => 'drawer-xl',
    default => 'drawer-md',
};

$positionClass = $position === 'left' ? 'drawer-left' : 'drawer-right';
@endphp

{{-- Backdrop Overlay --}}
@if($backdrop)
<div
    id="{{ $id }}-backdrop"
    class="drawer-backdrop"
    data-drawer-backdrop="{{ $id }}"
    aria-hidden="true"
    style="display: none;"
></div>
@endif

{{-- Side Drawer Panel --}}
<aside
    id="{{ $id }}"
    class="side-drawer {{ $sizeClass }} {{ $positionClass }}"
    role="dialog"
    aria-modal="true"
    aria-labelledby="{{ $id }}-title"
    aria-hidden="true"
    tabindex="-1"
    style="display: none;"
    {{ $attributes }}
>
    {{-- Header --}}
    <header class="drawer-header">
        <h2 id="{{ $id }}-title" class="drawer-title">{{ $title }}</h2>

        @if($closeButton)
        <button
            type="button"
            class="drawer-close"
            data-drawer-close="{{ $id }}"
            aria-label="Close {{ $title }}"
        >
            <i class="bx bx-x" aria-hidden="true"></i>
        </button>
        @endif
    </header>

    {{-- Content --}}
    <div class="drawer-body" role="document">
        {{ $slot }}
    </div>

    {{-- Footer (Optional) --}}
    @isset($footer)
    <footer class="drawer-footer">
        {{ $footer }}
    </footer>
    @endisset
</aside>

{{-- JavaScript for Side Drawer Functionality --}}
@once
@push('scripts')
<script>
/**
 * Side Drawer Component
 * Accessible, keyboard-navigable slide-in panel
 */
(function() {
  'use strict';

  const activeDrawers = new Set();
  let previouslyFocusedElement = null;

  /**
   * Initialize all drawer triggers
   */
  function initDrawers() {
    document.querySelectorAll('[data-drawer-target]').forEach(trigger => {
      trigger.addEventListener('click', function(e) {
        e.preventDefault();
        const drawerId = this.getAttribute('data-drawer-target');
        openDrawer(drawerId);
      });
    });

    // Close button listeners
    document.querySelectorAll('[data-drawer-close]').forEach(btn => {
      btn.addEventListener('click', function() {
        const drawerId = this.getAttribute('data-drawer-close');
        closeDrawer(drawerId);
      });
    });

    // Backdrop click listeners
    document.querySelectorAll('[data-drawer-backdrop]').forEach(backdrop => {
      backdrop.addEventListener('click', function() {
        const drawerId = this.getAttribute('data-drawer-backdrop');
        closeDrawer(drawerId);
      });
    });
  }

  /**
   * Open drawer with animation and focus management
   */
  function openDrawer(drawerId) {
    const drawer = document.getElementById(drawerId);
    const backdrop = document.getElementById(`${drawerId}-backdrop`);

    if (!drawer) return;

    // Store currently focused element for restoration
    previouslyFocusedElement = document.activeElement;

    // Show drawer and backdrop
    drawer.style.display = 'block';
    if (backdrop) {
      backdrop.style.display = 'block';
      setTimeout(() => backdrop.classList.add('show'), 10);
    }

    // Trigger reflow for animation
    drawer.offsetHeight;
    drawer.classList.add('show');
    drawer.setAttribute('aria-hidden', 'false');

    // Focus first focusable element
    setTimeout(() => {
      const firstFocusable = drawer.querySelector(
        'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
      );
      if (firstFocusable) {
        firstFocusable.focus();
      } else {
        drawer.focus();
      }
    }, 100);

    // Add to active set
    activeDrawers.add(drawerId);

    // Prevent body scroll
    document.body.style.overflow = 'hidden';

    // Add keyboard listeners
    drawer.addEventListener('keydown', handleKeyDown);
  }

  /**
   * Close drawer with animation
   */
  function closeDrawer(drawerId) {
    const drawer = document.getElementById(drawerId);
    const backdrop = document.getElementById(`${drawerId}-backdrop`);

    if (!drawer) return;

    // Remove show classes for animation
    drawer.classList.remove('show');
    if (backdrop) {
      backdrop.classList.remove('show');
    }

    // Wait for animation to complete
    setTimeout(() => {
      drawer.style.display = 'none';
      if (backdrop) {
        backdrop.style.display = 'none';
      }
      drawer.setAttribute('aria-hidden', 'true');

      // Restore focus
      if (previouslyFocusedElement) {
        previouslyFocusedElement.focus();
        previouslyFocusedElement = null;
      }

      // Remove from active set
      activeDrawers.delete(drawerId);

      // Restore body scroll if no active drawers
      if (activeDrawers.size === 0) {
        document.body.style.overflow = '';
      }
    }, 300); // Match CSS transition duration

    // Remove keyboard listener
    drawer.removeEventListener('keydown', handleKeyDown);
  }

  /**
   * Handle keyboard navigation (ESC, Tab trap)
   */
  function handleKeyDown(e) {
    const drawer = e.currentTarget;
    const focusableElements = drawer.querySelectorAll(
      'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
    );

    const firstFocusable = focusableElements[0];
    const lastFocusable = focusableElements[focusableElements.length - 1];

    // ESC key: Close drawer
    if (e.key === 'Escape') {
      e.preventDefault();
      const drawerId = drawer.getAttribute('id');
      closeDrawer(drawerId);
      return;
    }

    // Tab key: Focus trap
    if (e.key === 'Tab') {
      if (focusableElements.length === 0) {
        e.preventDefault();
        return;
      }

      if (e.shiftKey) {
        // Shift+Tab: Move backwards
        if (document.activeElement === firstFocusable) {
          e.preventDefault();
          lastFocusable.focus();
        }
      } else {
        // Tab: Move forwards
        if (document.activeElement === lastFocusable) {
          e.preventDefault();
          firstFocusable.focus();
        }
      }
    }
  }

  /**
   * Initialize on DOM ready
   */
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initDrawers);
  } else {
    initDrawers();
  }

  /**
   * Public API for programmatic control
   */
  window.SideDrawer = {
    open: openDrawer,
    close: closeDrawer
  };
})();
</script>
@endpush
@endonce

{{-- CSS for Side Drawer Component --}}
@once
@push('styles')
<style>
/* Drawer Backdrop */
.drawer-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(4px);
    z-index: 1040;
    opacity: 0;
    transition: opacity 0.3s ease-in-out;
}

.drawer-backdrop.show {
    opacity: 1;
}

/* Side Drawer Base - Sensei Token Styling */
.side-drawer {
    position: fixed;
    top: 0;
    height: 100%;
    background: var(--sensei-surface);
    box-shadow: var(--sensei-shadow-hover);
    z-index: 1050;
    display: flex;
    flex-direction: column;
    transition: transform 0.3s ease-in-out;
}

/* Drawer Positions */
.drawer-right {
    right: 0;
    transform: translateX(100%);
}

.drawer-right.show {
    transform: translateX(0);
}

.drawer-left {
    left: 0;
    transform: translateX(-100%);
}

.drawer-left.show {
    transform: translateX(0);
}

/* Drawer Sizes */
.drawer-sm {
    width: 320px;
}

.drawer-md {
    width: 480px;
}

.drawer-lg {
    width: 640px;
}

.drawer-xl {
    width: 800px;
}

/* Drawer Header - Sensei Tokens */
.drawer-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.5rem;
    border-bottom: 1px solid var(--sensei-border);
    flex-shrink: 0;
    background: var(--sensei-surface-strong, var(--sensei-surface));
}

.drawer-title {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--sensei-text-primary);
}

.drawer-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    color: var(--sensei-text-secondary);
    cursor: pointer;
    padding: 0.25rem;
    line-height: 1;
    transition: all var(--sensei-transition);
    border-radius: var(--sensei-radius-sm);
}

.drawer-close:hover,
.drawer-close:focus {
    color: var(--sensei-accent);
    background: color-mix(in srgb, var(--sensei-accent) 10%, transparent);
    outline: 2px solid var(--sensei-accent);
    outline-offset: 2px;
}

/* Drawer Body - Sensei Tokens */
.drawer-body {
    flex: 1;
    overflow-y: auto;
    padding: 1.5rem;
    background: var(--sensei-surface);
}

/* Drawer Footer - Sensei Tokens */
.drawer-footer {
    padding: 1rem 1.5rem;
    border-top: 1px solid var(--sensei-border);
    background: var(--sensei-surface-strong, var(--sensei-surface));
    flex-shrink: 0;
    display: flex;
    gap: 0.75rem;
    justify-content: flex-end;
}

/* Accessibility: Focus visible styles - Sensei Tokens */
.side-drawer:focus {
    outline: 2px solid var(--sensei-accent);
    outline-offset: -2px;
}

.side-drawer button:focus-visible,
.side-drawer a:focus-visible,
.side-drawer input:focus-visible,
.side-drawer select:focus-visible,
.side-drawer textarea:focus-visible {
    outline: 2px solid var(--sensei-accent);
    outline-offset: 2px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .drawer-sm,
    .drawer-md,
    .drawer-lg,
    .drawer-xl {
        width: 90%;
    }
}

@media (max-width: 480px) {
    .drawer-sm,
    .drawer-md,
    .drawer-lg,
    .drawer-xl {
        width: 100%;
    }
}
</style>
@endpush
@endonce
