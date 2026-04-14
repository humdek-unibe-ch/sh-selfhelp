(function () {
    var CONSENT_STORAGE_KEY = 'sh_cookie_consent_v1';
    var ADS_TAG_ID = 'AW-18055701906';
    var GTAG_SCRIPT_ID = 'sh-google-gtag-loader';
    var BANNER_ID = 'sh-cookie-banner';
    var SETTINGS_BUTTON_ID = 'sh-cookie-settings-btn';

    function getStoredConsent() {
        try {
            return localStorage.getItem(CONSENT_STORAGE_KEY);
        } catch (e) {
            return null;
        }
    }

    function setStoredConsent(value) {
        try {
            localStorage.setItem(CONSENT_STORAGE_KEY, value);
        } catch (e) {
            // no-op (private mode / blocked storage)
        }
    }

    function ensureSettingsButton() {
        if (document.getElementById(SETTINGS_BUTTON_ID)) {
            return;
        }
        var button = document.createElement('button');
        button.id = SETTINGS_BUTTON_ID;
        button.type = 'button';
        button.textContent = 'Cookie settings';
        button.setAttribute('aria-label', 'Open cookie settings');
        button.style.position = 'fixed';
        button.style.left = '16px';
        button.style.bottom = '16px';
        button.style.zIndex = '2147483646';
        button.style.padding = '8px 12px';
        button.style.border = '1px solid #ccc';
        button.style.borderRadius = '999px';
        button.style.background = '#fff';
        button.style.color = '#222';
        button.style.fontSize = '12px';
        button.style.cursor = 'pointer';
        button.style.boxShadow = '0 2px 10px rgba(0,0,0,0.12)';
        button.addEventListener('click', showBanner);
        document.body.appendChild(button);
    }

    function hideBanner() {
        var banner = document.getElementById(BANNER_ID);
        if (banner && banner.parentNode) {
            banner.parentNode.removeChild(banner);
        }
    }

    function showBanner() {
        hideBanner();

        var banner = document.createElement('div');
        banner.id = BANNER_ID;
        banner.setAttribute('role', 'dialog');
        banner.setAttribute('aria-live', 'polite');
        banner.style.position = 'fixed';
        banner.style.left = '16px';
        banner.style.right = '16px';
        banner.style.bottom = '16px';
        banner.style.maxWidth = '640px';
        banner.style.margin = '0 auto';
        banner.style.padding = '14px';
        banner.style.background = '#ffffff';
        banner.style.border = '1px solid #d8d8d8';
        banner.style.borderRadius = '10px';
        banner.style.boxShadow = '0 8px 30px rgba(0,0,0,0.18)';
        banner.style.zIndex = '2147483647';
        banner.style.fontFamily = 'Arial, sans-serif';
        banner.style.fontSize = '14px';
        banner.style.lineHeight = '1.4';
        banner.style.color = '#1f1f1f';

        var text = document.createElement('div');
        text.textContent = 'We use cookies for ads measurement. You can accept or reject non-essential tracking.';
        text.style.marginBottom = '10px';

        var buttons = document.createElement('div');
        buttons.style.display = 'flex';
        buttons.style.gap = '8px';
        buttons.style.flexWrap = 'wrap';

        var acceptBtn = document.createElement('button');
        acceptBtn.type = 'button';
        acceptBtn.textContent = 'Accept';
        acceptBtn.style.padding = '8px 14px';
        acceptBtn.style.border = '1px solid #0f766e';
        acceptBtn.style.borderRadius = '6px';
        acceptBtn.style.background = '#0f766e';
        acceptBtn.style.color = '#fff';
        acceptBtn.style.cursor = 'pointer';

        var rejectBtn = document.createElement('button');
        rejectBtn.type = 'button';
        rejectBtn.textContent = 'Reject';
        rejectBtn.style.padding = '8px 14px';
        rejectBtn.style.border = '1px solid #bdbdbd';
        rejectBtn.style.borderRadius = '6px';
        rejectBtn.style.background = '#fff';
        rejectBtn.style.color = '#222';
        rejectBtn.style.cursor = 'pointer';

        acceptBtn.addEventListener('click', function () {
            setStoredConsent('granted');
            hideBanner();
            loadGoogleAdsTag();
        });

        rejectBtn.addEventListener('click', function () {
            setStoredConsent('denied');
            hideBanner();
        });

        buttons.appendChild(acceptBtn);
        buttons.appendChild(rejectBtn);
        banner.appendChild(text);
        banner.appendChild(buttons);
        document.body.appendChild(banner);
    }

    function loadGoogleAdsTag() {
        if (document.getElementById(GTAG_SCRIPT_ID)) {
            return;
        }

        window.dataLayer = window.dataLayer || [];
        window.gtag = window.gtag || function () {
            dataLayer.push(arguments);
        };

        // Default denied before library and config are applied.
        window.gtag('consent', 'default', {
            ad_storage: 'denied',
            analytics_storage: 'denied',
            ad_user_data: 'denied',
            ad_personalization: 'denied'
        });

        var script = document.createElement('script');
        script.id = GTAG_SCRIPT_ID;
        script.async = true;
        script.src = 'https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(ADS_TAG_ID);
        script.onload = function () {
            window.gtag('js', new Date());
            window.gtag('consent', 'update', {
                ad_storage: 'granted',
                analytics_storage: 'granted',
                ad_user_data: 'granted',
                ad_personalization: 'granted'
            });
            window.gtag('config', ADS_TAG_ID);
        };
        document.head.appendChild(script);
    }

    function initConsentFlow() {
        ensureSettingsButton();

        var consent = getStoredConsent();
        if (consent === 'granted') {
            loadGoogleAdsTag();
            return;
        }

        if (consent === 'denied') {
            return;
        }

        showBanner();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initConsentFlow);
    } else {
        initConsentFlow();
    }
})();
