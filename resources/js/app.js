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

  const themeToggle = document.querySelector('[data-theme-toggle]');
  const html = document.documentElement;

  if (themeToggle) {
    themeToggle.addEventListener('click', () => {
      const current = html.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
      html.setAttribute('data-bs-theme', current);
      localStorage.setItem('sensei-theme', current);
    });

    const storedTheme = localStorage.getItem('sensei-theme');
    if (storedTheme) {
      html.setAttribute('data-bs-theme', storedTheme);
    }
  }

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
});
