const storedTheme = localStorage.getItem('theme');
const prefersDarkMode = window.matchMedia('(prefers-color-scheme: dark)').matches;
var theme = storedTheme || (prefersDarkMode ? 'dark' : 'light');
theme = theme == 'auto' ? (prefersDarkMode ? 'dark' : 'light') : storedTheme;
document.documentElement.setAttribute('data-bs-theme', theme);