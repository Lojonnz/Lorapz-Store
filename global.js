// global.js — smooth theme toggle & safe DOM ready
document.addEventListener('DOMContentLoaded', function () {

  const body = document.body;
  const tbtn = document.getElementById('theme-toggle');

  // =============================
  // APPLY SAVED THEME ON LOAD
  // =============================
  try {
    const saved = localStorage.getItem('lorapz_theme');
    if (saved === 'dark') {
      body.classList.add('dark');
    }
  } catch (e) { console.warn(e); }

  // =============================
  // THEME TOGGLE BUTTON
  // =============================
  if (tbtn) {
    tbtn.addEventListener('click', function () {
      // add temporary transition class
      body.classList.add('theme-transition');
      
      // toggle dark
      body.classList.toggle('dark');

      // save preference
      try {
        localStorage.setItem('lorapz_theme', body.classList.contains('dark') ? 'dark' : 'light');
      } catch(e) {}
      
      // remove transition after animation
      setTimeout(() => body.classList.remove('theme-transition'), 300);
    });
  }
});
