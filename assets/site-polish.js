(() => {
    const getLanguage = () => localStorage.getItem('staffless-language') || 'en';

    const applyStableLanguage = (language = getLanguage()) => {
        const isZh = language === 'zh-TW';
        document.documentElement.lang = isZh ? 'zh-Hant' : 'en';

        document.querySelectorAll('[data-en][data-zh]').forEach((element) => {
            element.textContent = isZh ? element.dataset.zh : element.dataset.en;
        });

        document.querySelectorAll('[data-placeholder-en][data-placeholder-zh]').forEach((field) => {
            field.placeholder = isZh ? field.dataset.placeholderZh : field.dataset.placeholderEn;
        });

        document.querySelectorAll('[data-language-option]').forEach((button) => {
            const active = button.dataset.languageOption === language;
            button.classList.toggle('is-active', active);
            button.setAttribute('aria-pressed', String(active));
        });
    };

    const scorePassword = (value) => {
        let score = 0;
        if (value.length >= 6) score += 1;
        if (value.length >= 10) score += 1;
        if (/[a-z]/.test(value) && /[A-Z]/.test(value)) score += 1;
        if (/\d/.test(value)) score += 1;
        if (/[^A-Za-z0-9]/.test(value)) score += 1;
        return Math.min(score, 5);
    };

    const setupPasswordTools = () => {
        document.querySelectorAll('[data-toggle-password]').forEach((button) => {
            const target = document.querySelector(button.dataset.togglePassword);
            if (!target) return;
            button.addEventListener('click', () => {
                const showing = target.type === 'text';
                target.type = showing ? 'password' : 'text';
                button.textContent = showing ? '👁' : '🙈';
                button.setAttribute('aria-label', showing ? 'Show password' : 'Hide password');
            });
        });

        const password = document.querySelector('[data-register-password]');
        const confirm = document.querySelector('[data-confirm-password]');
        const meter = document.querySelector('[data-password-meter]');
        const label = document.querySelector('[data-password-strength-label]');
        if (!password || !meter || !label) return;

        const update = () => {
            const score = scorePassword(password.value);
            const language = getLanguage();
            const labels = language === 'zh-TW'
                ? ['尚未輸入', '偏弱', '普通', '良好', '強', '非常強']
                : ['Empty', 'Weak', 'Fair', 'Good', 'Strong', 'Excellent'];
            meter.style.setProperty('--strength', `${score * 20}%`);
            meter.dataset.score = String(score);
            label.textContent = labels[score];
            if (confirm) {
                confirm.setCustomValidity(confirm.value && confirm.value !== password.value ? 'Passwords do not match' : '');
            }
        };

        password.addEventListener('input', update);
        if (confirm) confirm.addEventListener('input', update);
        document.addEventListener('staffless:languagechange', () => update());
        update();
    };

    const setupRegisterDemo = () => {
        const button = document.querySelector('[data-random-register]');
        const form = document.querySelector('[data-register-form]');
        if (!button || !form) return;
        button.addEventListener('click', () => {
            const stamp = Date.now().toString().slice(-5);
            form.username.value = `ramen_user_${stamp}`;
            form.email.value = `member${stamp}@example.com`;
            form.password.value = 'RamenPass!2026';
            form.confirm_password.value = 'RamenPass!2026';
            form.password.dispatchEvent(new Event('input', { bubbles: true }));
        });
    };

    const setupMegaMenu = () => {
        const trigger = document.querySelector('[data-mega-trigger]');
        const menu = document.querySelector('[data-mega-menu]');
        if (!trigger || !menu) return;
        if (trigger.closest('[data-commerce-header]')) return;
        trigger.addEventListener('click', () => {
            const open = !menu.classList.contains('is-open');
            menu.classList.toggle('is-open', open);
            trigger.classList.toggle('is-open', open);
            trigger.setAttribute('aria-expanded', String(open));
        });
    };

    const setupHeaderCollapse = () => {
        const header = document.querySelector('[data-commerce-header]');
        const button = document.querySelector('[data-nav-collapse-toggle]');
        if (!header || !button) return;

        const storageKey = 'staffless-header-compact';
        const setCollapsed = (collapsed) => {
            const isZh = getLanguage() === 'zh-TW';
            const label = button.querySelector('.nav-label');
            const nextText = collapsed
                ? (isZh ? button.dataset.expandZh : button.dataset.expandEn)
                : (isZh ? button.dataset.collapseZh : button.dataset.collapseEn);
            header.classList.toggle('is-compact', collapsed);
            button.setAttribute('aria-expanded', String(!collapsed));
            button.setAttribute('aria-label', nextText);
            if (label) {
                label.textContent = nextText;
            } else {
                button.textContent = nextText;
            }
            localStorage.setItem(storageKey, collapsed ? '1' : '0');
        };

        button.addEventListener('click', () => {
            setCollapsed(!header.classList.contains('is-compact'));
        });

        document.addEventListener('staffless:languagechange', () => {
            setCollapsed(header.classList.contains('is-compact'));
        });

        setCollapsed(localStorage.getItem(storageKey) === '1');
    };

    document.addEventListener('DOMContentLoaded', () => {
        applyStableLanguage();
        setupPasswordTools();
        setupRegisterDemo();
        setupMegaMenu();
        setupHeaderCollapse();
        setTimeout(() => applyStableLanguage(), 80);
        setTimeout(() => applyStableLanguage(), 400);
    });

    document.addEventListener('click', (event) => {
        const button = event.target.closest('[data-language-option]');
        if (!button) return;
        setTimeout(() => applyStableLanguage(button.dataset.languageOption), 0);
    });

    document.addEventListener('staffless:languagechange', (event) => {
        applyStableLanguage(event.detail?.language || getLanguage());
    });
})();
