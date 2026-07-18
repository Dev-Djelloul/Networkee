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

    // Icône soleil en mode sombre (clic pour repasser en clair), icône lune en mode clair (clic pour passer en sombre)
    const iconsBase = (window.NETWORKEE_BASE_URL || '') + 'icons/';
    const src = isDark ? iconsBase + 'icons8-sun-50.png' : iconsBase + 'icons8-moon-100.png';
    button.innerHTML = '<img src="' + src + '" alt="" width="30" height="30">';
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
