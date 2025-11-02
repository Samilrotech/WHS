import './bootstrap';

document.addEventListener('DOMContentLoaded', () => {
  // Sidebar toggle functionality
  const sidebar = document.getElementById('sensei-sidebar');
  const toggleButtons = document.querySelectorAll('[data-sidebar-toggle]');
  const closeButtons = document.querySelectorAll('[data-sidebar-close]');
  const body = document.body;

  function openSidebar() {
    if (!sidebar) return;
    sidebar.classList.add('is-open');
    body.classList.add('sensei-sidebar-open');
    toggleButtons.forEach(btn => btn.setAttribute('aria-expanded', 'true'));
  }

  function closeSidebar() {
    if (!sidebar) return;
    sidebar.classList.remove('is-open');
    body.classList.remove('sensei-sidebar-open');
    toggleButtons.forEach(btn => btn.setAttribute('aria-expanded', 'false'));
  }

  function toggleSidebar() {
    if (sidebar?.classList.contains('is-open')) {
      closeSidebar();
    } else {
      openSidebar();
    }
  }

  // Toggle buttons (hamburger, More tab)
  toggleButtons.forEach(button => {
    button.addEventListener('click', toggleSidebar);
  });

  // Close buttons
  closeButtons.forEach(button => {
    button.addEventListener('click', closeSidebar);
  });

  // Click overlay to close
  if (sidebar) {
    sidebar.addEventListener('click', (e) => {
      if (e.target === sidebar && sidebar.classList.contains('is-open')) {
        closeSidebar();
      }
    });
  }

  // ESC key to close
  document.addEventListener('keydown', event => {
    if (event.key === 'Escape' && sidebar?.classList.contains('is-open')) {
      closeSidebar();
    }
  });

  // ================================================
  // THEME TOGGLE - Enhanced with light as default
  // ================================================
  const DEFAULT_THEME = 'light';
  const themeToggle = document.querySelector('[data-theme-toggle]');
  const html = document.documentElement;

  // Update toggle icon based on current theme
  const updateToggleIcon = (theme) => {
    if (!themeToggle) return;

    const icon = themeToggle.querySelector('i');
    if (icon) {
      // Sun icon for dark mode (click to get light)
      // Moon icon for light mode (click to get dark)
      icon.className = theme === 'dark'
        ? 'bx bx-sun'
        : 'bx bx-moon';
    }

    // Update aria-label for accessibility
    themeToggle.setAttribute(
      'aria-label',
      theme === 'dark' ? 'Switch to light theme' : 'Switch to dark theme'
    );
  };

  // Initialize theme on page load
  const initTheme = () => {
    const storedTheme = localStorage.getItem('sensei-theme');
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

    // Priority: stored preference > system preference > default (light)
    const initialTheme = storedTheme || (prefersDark ? 'dark' : DEFAULT_THEME);

    html.setAttribute('data-bs-theme', initialTheme);
    updateToggleIcon(initialTheme);
  };

  // Handle theme toggle click
  if (themeToggle) {
    themeToggle.addEventListener('click', () => {
      const current = html.getAttribute('data-bs-theme');
      const next = current === 'dark' ? 'light' : 'dark';

      html.setAttribute('data-bs-theme', next);
      localStorage.setItem('sensei-theme', next);
      updateToggleIcon(next);

      // Add smooth transition class
      document.body.classList.add('theme-transitioning');
      setTimeout(() => {
        document.body.classList.remove('theme-transitioning');
      }, 300);
    });
  }

  // Listen for system theme changes (only if user hasn't set preference)
  window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
    // Only auto-switch if user hasn't manually set a preference
    if (!localStorage.getItem('sensei-theme')) {
      const newTheme = e.matches ? 'dark' : 'light';
      html.setAttribute('data-bs-theme', newTheme);
      updateToggleIcon(newTheme);
    }
  });

  // Initialize theme
  initTheme();

  const searchForm = document.querySelector('.sensei-search');
  const searchInput = searchForm?.querySelector('.sensei-search__input');

  if (searchForm && searchInput) {
    const toggleFocusState = isFocused => {
      searchForm.classList.toggle('is-focused', isFocused);
    };

    searchInput.addEventListener('focus', () => toggleFocusState(true));
    searchInput.addEventListener('blur', () => toggleFocusState(false));

    // Support quick focus on Ctrl/Cmd + K and escape to exit
    document.addEventListener('keydown', event => {
      const key = event.key.toLowerCase();

      if ((event.ctrlKey || event.metaKey) && key === 'k') {
        event.preventDefault();
        searchInput.focus();
        searchInput.select();
      }

      if (key === 'escape' && document.activeElement === searchInput) {
        searchInput.blur();
      }
    });
  }

  const userMenus = document.querySelectorAll('[data-user-menu]');

  userMenus.forEach(menu => {
    const trigger = menu.querySelector('[data-user-menu-trigger]');
    const panel = menu.querySelector('[data-user-menu-panel]');

    if (!trigger || !panel) return;

    const closeMenu = () => {
      panel.hidden = true;
      menu.classList.remove('is-open');
      trigger.setAttribute('aria-expanded', 'false');
    };

    const openMenu = () => {
      panel.hidden = false;
      menu.classList.add('is-open');
      trigger.setAttribute('aria-expanded', 'true');
    };

    trigger.addEventListener('click', event => {
      event.stopPropagation();

      if (panel.hidden) {
        userMenus.forEach(otherMenu => {
          if (otherMenu !== menu) {
            const otherPanel = otherMenu.querySelector('[data-user-menu-panel]');
            const otherTrigger = otherMenu.querySelector('[data-user-menu-trigger]');
            if (otherPanel && otherTrigger) {
              otherPanel.hidden = true;
              otherMenu.classList.remove('is-open');
              otherTrigger.setAttribute('aria-expanded', 'false');
            }
          }
        });

        openMenu();
      } else {
        closeMenu();
      }
    });

    // Prevent panel clicks from closing the dropdown prematurely
    panel.addEventListener('click', event => {
      event.stopPropagation();
    });

    document.addEventListener('click', event => {
      if (!menu.contains(event.target)) {
        closeMenu();
      }
    });

    document.addEventListener('keydown', event => {
      if (event.key === 'Escape' && menu.classList.contains('is-open')) {
        closeMenu();
        trigger.focus();
      }
    });
  });

  document.querySelectorAll('[data-logout-trigger]').forEach(button => {
    button.addEventListener('click', event => {
      event.preventDefault();
      event.stopPropagation();

      const form = button.closest('form');
      if (!form) {
        console.error('Logout button: Form not found');
        return;
      }

      console.log('Logout triggered, submitting form...');

      // Use requestSubmit for proper form validation, fallback to submit()
      if (typeof form.requestSubmit === 'function') {
        form.requestSubmit();
      } else {
        form.submit();
      }
    });
  });

  /**
   * Employee Quick View Modal - AJAX Loading
   */
  const quickViewModal = document.getElementById('employeeQuickViewModal');
  if (quickViewModal) {
    const modalInstance = new window.bootstrap.Modal(quickViewModal);
    const loadingEl = document.getElementById('employeeQuickViewLoading');
    const contentEl = document.getElementById('employeeQuickViewContent');
    const viewProfileBtn = document.getElementById('employeeViewProfileBtn');
    const editBtn = document.getElementById('employeeEditBtn');

    // Listen for quick view button clicks
    document.addEventListener('click', function(e) {
      const trigger = e.target.closest('[data-quick-view]');
      if (!trigger) return;

      e.preventDefault();
      const memberId = trigger.dataset.memberId;
      if (!memberId) return;

      // Show modal with loading state
      loadingEl.style.display = 'block';
      contentEl.style.display = 'none';
      modalInstance.show();

      // Fetch employee data via AJAX
      fetch(`/teams/${memberId}/quick-view`, {
        method: 'GET',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        }
      })
      .then(response => {
        if (!response.ok) throw new Error('Network response was not ok');
        return response.json();
      })
      .then(data => {
        // Hide loading, show content
        loadingEl.style.display = 'none';
        contentEl.innerHTML = data.html;
        contentEl.style.display = 'block';

        // Update footer links
        viewProfileBtn.href = data.view_url;
        editBtn.href = data.edit_url;
      })
      .catch(error => {
        console.error('Error loading employee quick view:', error);
        contentEl.innerHTML = `
          <div class="alert alert-danger">
            <i class="bx bx-error-circle me-2"></i>
            Failed to load employee details. Please try again.
          </div>
        `;
        contentEl.style.display = 'block';
        loadingEl.style.display = 'none';
      });
    });

    // Clear content when modal closes
    quickViewModal.addEventListener('hidden.bs.modal', function() {
      contentEl.innerHTML = '';
      contentEl.style.display = 'none';
      loadingEl.style.display = 'block';
    });
  }
});
