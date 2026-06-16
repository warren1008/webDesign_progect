// AI 修改：新功能頁共用中英文內容切換與密碼強度回饋
document.addEventListener('DOMContentLoaded', () => {
    const applyFeatureLanguage = (language) => {
        document.querySelectorAll('[data-en][data-zh]').forEach((element) => {
            element.textContent = language === 'zh-TW' ? element.dataset.zh : element.dataset.en;
        });
        document.querySelectorAll('[data-placeholder-en][data-placeholder-zh]').forEach((element) => {
            element.placeholder = language === 'zh-TW'
                ? element.dataset.placeholderZh
                : element.dataset.placeholderEn;
        });
    };

    applyFeatureLanguage(localStorage.getItem('staffless-language') || 'en');
    document.addEventListener('staffless:languagechange', (event) => {
        applyFeatureLanguage(event.detail.language);
    });

    const password = document.querySelector('[data-new-password]');
    const meter = document.querySelector('[data-password-strength]');
    if (password && meter) {
        const segments = [...meter.children];
        const updateStrength = () => {
            const value = password.value;
            let score = 0;
            if (value.length >= 8) score++;
            if (/[A-Z]/i.test(value) && /\d/.test(value)) score++;
            if (value.length >= 12) score++;
            if (/[^A-Za-z0-9]/.test(value)) score++;
            segments.forEach((segment, index) => {
                segment.classList.toggle('is-active', index < score);
            });
        };
        password.addEventListener('input', updateStrength);
    }
});
