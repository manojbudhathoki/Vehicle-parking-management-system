/* SmartPark — vanilla JavaScript UI helpers.
   Bootstrap JavaScript is intentionally not used. */
(function () {
  'use strict';

  // Mobile public navbar
  const navToggler = document.getElementById('navbarToggler');
  const nav = document.getElementById('nav');
  if (navToggler && nav) {
    navToggler.addEventListener('click', function () {
      const open = nav.classList.toggle('open');
      navToggler.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
    nav.querySelectorAll('a').forEach(function (link) {
      link.addEventListener('click', function () {
        nav.classList.remove('open');
        navToggler.setAttribute('aria-expanded', 'false');
      });
    });
    document.addEventListener('click', function (event) {
      if (!nav.contains(event.target) && !navToggler.contains(event.target)) {
        nav.classList.remove('open');
        navToggler.setAttribute('aria-expanded', 'false');
      }
    });
  }

  // Dismissible alerts
  document.querySelectorAll('[data-alert-dismiss]').forEach(function (button) {
    button.addEventListener('click', function () {
      const alert = button.closest('.alert');
      if (!alert) return;
      alert.classList.add('hide');
      window.setTimeout(function () { alert.remove(); }, 220);
    });
  });

  // Confirmation forms
  document.querySelectorAll('form[data-confirm]').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      if (!window.confirm(form.dataset.confirm)) e.preventDefault();
    });
  });

  // Dashboard sidebar on small screens
  const sidebarToggler = document.getElementById('sidebarToggler');
  const sidebar = document.getElementById('panelSidebar');
  const backdrop = document.getElementById('sidebarBackdrop');
  const closeSidebar = function () {
    if (sidebar) sidebar.classList.remove('open');
    if (backdrop) backdrop.classList.remove('open');
  };
  if (sidebarToggler && sidebar && backdrop) {
    sidebarToggler.addEventListener('click', function () {
      sidebar.classList.toggle('open');
      backdrop.classList.toggle('open');
    });
    backdrop.addEventListener('click', closeSidebar);
    sidebar.querySelectorAll('a').forEach(function (link) {
      link.addEventListener('click', closeSidebar);
    });
  }

  // Escape closes open navigation/sidebar.
  document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
      if (nav) nav.classList.remove('open');
      if (navToggler) navToggler.setAttribute('aria-expanded', 'false');
      closeSidebar();
    }
  });
})();
