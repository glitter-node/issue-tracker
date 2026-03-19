(function () {
    var storageKey = 'issue-tracker-theme';
    var themes = ['light', 'dark', 'dim'];
    var root = document.documentElement;

    function applyTheme(theme) {
        var nextTheme = themes.indexOf(theme) === -1 ? 'light' : theme;

        root.setAttribute('data-theme', nextTheme);
        try {
            localStorage.setItem(storageKey, nextTheme);
        } catch (error) {
        }

        document.querySelectorAll('[data-theme-switcher]').forEach(function (switcher) {
            if (switcher.value !== nextTheme) {
                switcher.value = nextTheme;
            }
        });
    }

    try {
        applyTheme(localStorage.getItem(storageKey) || root.getAttribute('data-theme') || 'light');
    } catch (error) {
        applyTheme(root.getAttribute('data-theme') || 'light');
    }

    document.addEventListener('change', function (event) {
        var target = event.target;

        if (! target || ! target.matches('[data-theme-switcher]')) {
            return;
        }

        applyTheme(target.value);
    });

    document.addEventListener('DOMContentLoaded', function () {
        applyTheme(root.getAttribute('data-theme') || 'light');
    });
})();
