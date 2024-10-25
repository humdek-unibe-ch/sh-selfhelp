$(document).ready(function () {

    init_user_language();
    init_user_theme();


});

function init_user_language() {
    $('#defaultLanguage select').on('change', function () {
        var id_languages = $(this).val();
        $.post(
            BASE_PATH + '/request/AjaxLanguage/ajax_set_user_language',
            { id_languages: id_languages },
            function (data) {
                if (data.success) {
                    location.reload();
                }
                else {
                    console.log(data);
                }
            },
            'json'
        );
    });
}

function init_user_theme() {
    // Retrieve saved theme from localStorage or use system preference
    const savedTheme = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');    
    var themeButton = $('#defaultTheme select')[0];
    if (!$(themeButton).val()) {
        // initialize default theme
        $(themeButton).val(savedTheme);
        $(themeButton).selectpicker('destroy');
        $(themeButton).selectpicker('render');
    }

    // Select the <html> element for setting the theme attribute
    const htmlElement = document.documentElement;

    // Set initial theme
    setTheme(savedTheme);

    // Function to set the theme and save it in localStorage
    function setTheme(theme) {
        htmlElement.setAttribute('data-bs-theme', theme);
        localStorage.setItem('theme', theme);
    }

    // Function to update the theme based on the system preference
    function updateTheme() {
        const isDarkMode = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;

        // Determine the theme based on system settings
        const theme = isDarkMode ? 'dark' : 'light';
        setTheme(theme);
    }

    // Function to toggle between light and dark themes
    function toggleTheme() {
        const currentTheme = htmlElement.getAttribute('data-bs-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        setTheme(newTheme);
    }

    // Add an event listener to monitor changes in the user's system theme preferences
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', updateTheme);

    // Add an event listener to change the theme when the dropdown value changes
    $('#defaultTheme select').on('change', function () {
        const theme = $(this).val(); // Get selected value from dropdown
        setTheme(theme);
    });

    // Optional: If you want to use toggleTheme() somewhere, you can bind it to a button, e.g.,
    // $('#themeToggleButton').on('click', toggleTheme);
}
