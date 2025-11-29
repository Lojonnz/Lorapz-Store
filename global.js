// global.js — theme toggle & safe DOM ready
document.addEventListener('DOMContentLoaded', function () {

  // Theme toggle: attach safely (works even when navbar is included)
  const tbtn = document.getElementById('theme-toggle');
  if (tbtn) {
    tbtn.addEventListener('click', function () {
      document.body.classList.toggle('dark');
      // persist choice
      try {
        localStorage.setItem('lorapz_theme', document.body.classList.contains('dark') ? 'dark' : 'light');
      } catch (e) { /* ignore storage errors */ }
    });
  }

  // apply saved theme on load
  try {
    if (localStorage.getItem('lorapz_theme') === 'dark') {
      document.body.classList.add('dark');
    }
  } catch (e) { /* ignore */ }

});
