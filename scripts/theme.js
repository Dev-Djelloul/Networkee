(function () {
  'use strict';

  const STORAGE_KEY = 'networkee-theme';
  const DARK_CLASS = 'dark';

  function getSavedTheme() {
    try {
      return localStorage.getItem(STORAGE_KEY);
    } catch (e) {
      return null;
    }
  }

  function getSystemTheme() {
    return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
  }

  function applyTheme(theme) {
    const isDark = theme === 'dark';
    document.body.classList.toggle(DARK_CLASS, isDark);
    updateToggleIcon(theme);
  }

  function saveTheme(theme) {
    try {
      localStorage.setItem(STORAGE_KEY, theme);
    } catch (e) {
      // ignore storage errors
    }
  }

  function getCurrentTheme() {
    return document.body.classList.contains(DARK_CLASS) ? 'dark' : 'light';
  }

  function toggleTheme() {
    const next = getCurrentTheme() === 'dark' ? 'light' : 'dark';
    applyTheme(next);
    saveTheme(next);
  }

  function updateToggleIcon(theme) {
    const button = document.getElementById('theme-toggle');
    if (!button) return;

    const isDark = theme === 'dark';
    button.setAttribute('aria-label', isDark ? 'Passer au mode clair' : 'Passer au mode sombre');
    button.title = isDark ? 'Passer au mode clair' : 'Passer au mode sombre';

    // Sun icon for dark mode (click to switch to light), moon icon for light mode (click to switch to dark)
    button.innerHTML = isDark
      ? '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>'
      : '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>';
  }

  function init() {
    const saved = getSavedTheme();
    const theme = saved || getSystemTheme();
    applyTheme(theme);

    document.addEventListener('click', function (e) {
      const button = e.target.closest('#theme-toggle');
      if (button) {
        e.preventDefault();
        toggleTheme();
      }
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
