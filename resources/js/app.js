import './bootstrap';

document.addEventListener('DOMContentLoaded', () => {
  const sidebar = document.querySelector('[data-sensei-sidebar]');
  const openToggle = document.querySelector('[data-sidebar-toggle]');
  const closeToggle = document.querySelector('[data-sidebar-close]');
  const body = document.body;

  const toggleSidebar = () => {
    if (!sidebar) return;
    sidebar.classList.toggle('is-open');
    body.classList.toggle('sensei-sidebar-open');
  };

  if (openToggle) {
    openToggle.addEventListener('click', toggleSidebar);
  }

  if (closeToggle) {
    closeToggle.addEventListener('click', toggleSidebar);
  }

  document.addEventListener('keydown', event => {
    if (event.key === 'Escape' && sidebar?.classList.contains('is-open')) {
      toggleSidebar();
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
});
