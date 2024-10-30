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

/**
 * Initializes the user theme by setting the theme according to user preference 
 * stored in `localStorage` or defaulting to system settings.
 * Sets up the theme selector UI, default options, and listeners for user interaction.
 */
function init_user_theme() {
    // Get the saved theme preference or fallback to system preference if none is stored.
    const savedTheme = getSystemTheme();
    
    // Select the theme dropdown and initialize if not yet set.
    var themeButton = $('#defaultTheme select')[0];
    if (!$(themeButton).val()) {
        // Set default theme options in dropdown with icon styling.
        $(themeButton).val(savedTheme);
        $('#defaultTheme option[value=""]').remove();  // Remove any empty option
        $('#defaultTheme option[value="light"]').attr(
            'data-content',
            "<span class='text-nowrap'><i class='fas fa-sun' style='color: var(--bs-warning);'></i> Light</span>"
        );
        $('#defaultTheme option[value="dark"]').attr(
            'data-content',
            "<span class='text-nowrap'><i class='fas fa-moon' style='color: var(--bs-secondary);'></i> Dark</span>"
        );
        $('#defaultTheme option[value="auto"]').attr(
            'data-content',
            "<span class='text-nowrap'><i class='fas fa-adjust' style='color: var(--bs-secondary);'></i> Auto</span>"
        );

        // Destroy any previous selectpicker instance and initialize with a tick mark for selection.
        $(themeButton).selectpicker('destroy');
        $(themeButton).selectpicker({
            showTick: true // Show a tick mark next to the selected theme.
        });
    }

    const htmlElement = document.documentElement;

    // Apply the initial theme based on saved preference or system default.
    loadTheme(getTheme());

    /**
     * Saves the selected theme preference in `localStorage`.
     * @param {string} theme - The theme to save ('light', 'dark', or 'auto').
     */
    function saveTheme(theme) {
        localStorage.setItem('theme', theme);
    }

    /**
     * Applies the specified theme by setting it in the HTML attribute.
     * @param {string} theme - The theme to apply.
     */
    function loadTheme(theme) {
        htmlElement.setAttribute('data-bs-theme', theme);
    }

    /**
     * Retrieves the saved theme from `localStorage`.
     * @returns {string} - The saved theme ('dark', 'light', or 'auto').
     */
    function getSystemTheme() {
        var theme = localStorage.getItem('theme');
        return theme === 'dark' || theme === 'light' ? theme : 'auto';
    }

    /**
     * Retrieves the current theme setting, falling back to system preference if 'auto' is selected.
     * @returns {string} - The active theme ('dark' or 'light') based on user or system settings.
     */
    function getTheme() {
        var theme = localStorage.getItem('theme');
        if (theme === 'dark' || theme === 'light') {
            return theme;
        } else {
            return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        }
    }

    // Listen for changes in system color scheme preference, updating the theme if set to 'auto'.
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
        if (getSystemTheme() === 'auto') {
            loadTheme(getTheme());
        }
    });

    // Update theme when the user changes the selection in the dropdown.
    $('#defaultTheme select').on('change', function () {
        const theme = $(this).val(); // Get the selected theme from dropdown
        saveTheme(theme);            // Save the user-selected theme
        loadTheme(getTheme());       // Apply the selected or system theme
    });
}

