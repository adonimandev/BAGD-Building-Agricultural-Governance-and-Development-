// theme.js — global theme toggler that persists across pages
(function(){
    const themeToggle = document.getElementById('themeToggle');
  
    function setTheme(mode){
      document.documentElement.setAttribute('data-theme', mode);
      localStorage.setItem('theme', mode);
  
      // swap icon if present
      if(themeToggle){
        themeToggle.textContent = mode === 'dark' ? '🌙' : '☀️';
        themeToggle.setAttribute('aria-label', mode === 'dark' ? 'Switch to light mode' : 'Switch to dark mode');
      }
    }
  
    function toggleTheme(){
      const current = localStorage.getItem('theme') || 'dark';
      setTheme(current === 'dark' ? 'light' : 'dark');
    }
  
    // apply theme on load and wire click handler (safely)
    document.addEventListener('DOMContentLoaded', () => {
      const saved = localStorage.getItem('theme') || 'dark';
      setTheme(saved);
  
      if(themeToggle){
        themeToggle.addEventListener('click', toggleTheme);
      }
  
      // extra: allow keyboard 't' to toggle for convenience
      window.addEventListener('keydown', (e) => { if(e.key === 't') toggleTheme(); });
    });
  })();
  