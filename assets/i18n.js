
(() => {
    const dictionaries = {
        en: {
            'faq.title': 'Smart FAQ',
            'faq.assistant': 'Draggable Assistant',
            'faq.drag_hint': 'Drag to move',
            'faq.demo': 'Drag me anywhere. Your chat stays private and is not uploaded with personal data.',
            'faq.machine': 'The kiosk did not dispense my order',
            'faq.partner': 'How do I use a partner voucher?',
            'faq.order': 'How do I check order and pickup status?',
            'faq.machine_answer': 'Keep your order number and pickup code, then check Order History first. If the issue continues, create a customer support case.',
            'faq.partner_answer': 'Open the voucher barcode in Member Center, or verify your latest pickup code on the Partners page.',
            'faq.order_answer': 'After login, open Order History and select Kitchen & Locker Status to view cooking and pickup progress.',
            'common.close': 'Close'
        },
        'zh-TW': {
            'faq.title': '智慧 FAQ',
            'faq.assistant': '可拖曳助理',
            'faq.drag_hint': '可拖曳移動',
            'faq.demo': '可拖曳到順手的位置；對話內容與個人資料不會上傳保存。',
            'faq.machine': '機台故障未出餐',
            'faq.partner': '如何使用合作店家兌換券',
            'faq.order': '如何查看訂單與取餐狀態',
            'faq.machine_answer': '請保留訂單編號與取餐碼，先到「訂單紀錄」確認狀態；若仍有問題，可建立客服案件。',
            'faq.partner_answer': '到會員中心開啟兌換券條碼，或在合作店家頁驗證最新取餐碼。',
            'faq.order_answer': '登入後前往訂單紀錄，點選製作與取餐資訊即可查看機器人烹調與智取櫃狀態。',
            'common.close': '關閉'
        }
    };

    const language = () => localStorage.getItem('staffless-language') || 'en';
    const t = (key) => {
        const selected = dictionaries[language()] || dictionaries.en;
        if (selected[key]) return selected[key];
        if (dictionaries.en[key]) return dictionaries.en[key];
        console.warn(`[i18n] Missing translation key: ${key}`);
        return key;
    };

    const apply = (root = document) => {
        root.querySelectorAll('[data-i18n]').forEach((node) => {
            node.textContent = t(node.dataset.i18n);
        });
        root.querySelectorAll('[data-i18n-placeholder]').forEach((node) => {
            node.placeholder = t(node.dataset.i18nPlaceholder);
        });
        root.querySelectorAll('[data-i18n-aria-label]').forEach((node) => {
            node.setAttribute('aria-label', t(node.dataset.i18nAriaLabel));
        });
    };

    window.stafflessI18n = { t, apply, language };
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => apply());
    } else {
        apply();
    }
    document.addEventListener('staffless:languagechange', () => apply());
})();
