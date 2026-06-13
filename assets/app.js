// AI 修改：集中管理前台互動，讓期末 Demo 不需要額外套件也有操作回饋
document.addEventListener('DOMContentLoaded', () => {
    const formatMoney = (value) => `$${Number(value || 0).toFixed(2)}`;

    // AI 修改：全站繁中 / English 即時切換，並記住使用者偏好
    const zhTranslations = {
        'Staffless Instant Noodle Store': '無人泡麵商店',
        'Grab → Scan → Pay → Go!': '拿取 → 掃碼 → 付款 → 取餐！',
        'Home': '首頁',
        'How It Works': '運作流程',
        'Kiosk Demo': '自助機台',
        'AI Recommendation': 'AI 推薦',
        'Menu': '菜單',
        'Dashboard': '點餐台',
        'Admin': '管理後台',
        'Logout': '登出',
        'Login': '登入',
        'Register': '註冊',
        'Store': '商店首頁',
        'Orders': '訂單',
        'Profile': '個人資料',
        'Welcome to the Future of Noodle Shopping': '歡迎來到未來的泡麵購物方式',
        'A 24/7 staffless instant noodle store where customers scan shelf codes, pay online, and pick up with a secure code.': '全天候 24/7 無人泡麵商店，掃描貨架代碼、線上付款，再以安全取餐碼完成取餐。',
        'Grab your favorite instant noodles from our shelves': '從貨架拿取喜愛的泡麵',
        'Enter the noodle code and quantity on our website': '在網站輸入商品代碼與數量',
        'Pay online with your credit/debit card': '使用信用卡或金融卡線上付款',
        'Show your pickup code and enjoy your noodles!': '出示取餐碼，享用你的泡麵！',
        'Start Shopping →': '開始點餐 →',
        'Try Kiosk Demo': '體驗自助機台',
        'STAFFLESS FLOW': '無人商店流程',
        'From shelf to pickup code in four steps': '從貨架到取餐碼，只要四個步驟',
        'Scan shelf QR': '掃描貨架 QR Code',
        'Each noodle slot has a code and QR tag so customers can identify products without staff.': '每個泡麵貨位都有商品代碼與 QR 標籤，不需店員也能辨識商品。',
        'Add by code': '輸入代碼加入',
        'The website checks noodle code, quantity, price and stock before adding to cart.': '網站會先檢查商品代碼、數量、價格與庫存，再加入購物車。',
        'Pay online': '線上付款',
        'Checkout simulates card payment and records transaction status for the admin panel.': '結帳頁模擬刷卡付款，並將交易狀態記錄至管理後台。',
        'Pickup unlock': '解鎖取餐',
        'After payment, the order receives a pickup code that represents the unmanned pickup counter.': '付款後產生專屬取餐碼，模擬無人取餐櫃解鎖流程。',
        'INTERACTIVE DEMO': '互動展示',
        'Self-service kiosk simulation': '自助點餐機模擬',
        'Choose a noodle below and watch the kiosk preview update. This is a lightweight demo for presentation, while the real order flow continues through login, cart and checkout.': '選擇下方泡麵即可查看機台即時更新。這是展示用互動模擬，完整訂購流程仍會經過登入、購物車與結帳。',
        'Waiting for QR scan...': '等待掃描 QR Code...',
        'No noodle selected': '尚未選擇泡麵',
        'Tap a scan button to simulate a customer at the shelf.': '點擊掃描按鈕，模擬顧客在貨架前操作。',
        'Popular Instant Noodles': '熱門泡麵菜單',
        'Preview Code': '預覽代碼',
        'Order This': '立即點餐',
        'In Stock': '庫存',
        'LIVE STOCK': '即時庫存',
        'AI TASTE MATCH': 'AI 口味配對',
        'Find your ramen personality': '找出你的拉麵人格',
        'Choose three preferences and let the local recommendation model find your best match.': '選擇三項偏好，讓本機推薦模型找出最適合你的口味。',
        'Flavor style': '風味類型',
        'Spice level': '辣度',
        'Current mood': '現在的心情',
        'Rich and creamy': '濃郁滑順',
        'Classic and balanced': '經典均衡',
        'Sour and aromatic': '酸香開胃',
        'Dry noodle texture': '乾拌口感',
        'Mild': '不辣',
        'Medium': '中辣',
        'Hot': '重辣',
        'Need comfort': '想要療癒',
        'Need a quick meal': '想快速吃飽',
        'Need energy': '需要補充能量',
        'Want an adventure': '想嘗試新風味',
        'Treat myself': '想犒賞自己',
        'Generate My Match': '產生我的推薦',
        'Demo recommendation runs locally and does not upload personal data.': '推薦功能在本機執行，不會上傳個人資料。',
        'AI advisor is ready': 'AI 顧問已就緒',
        'Order Recommendation': '點選推薦餐點',
        'Create an Account': '建立帳號',
        'Login to Your Account': '登入你的帳號',
        'Username': '使用者名稱',
        'Email': '電子郵件',
        'Password': '密碼',
        'Confirm Password': '確認密碼',
        'Username or Email': '使用者名稱或電子郵件',
        'Password (min 6 characters)': '密碼（至少 6 個字元）',
        "Don't have an account?": '還沒有帳號嗎？',
        'Register here': '立即註冊',
        'Already have an account?': '已經有帳號嗎？',
        'Login here': '前往登入',
        'Back to store introduction': '返回商店介紹',
        'Demo Credentials:': '展示帳號：',
        'Fill User Demo': '填入使用者帳號',
        'Fill Admin Demo': '填入管理員帳號',
        'Ordering progress': '點餐進度',
        'Code': '代碼',
        'Code:': '代碼：',
        'Enter noodle code': '輸入泡麵代碼',
        'Cart': '購物車',
        'Confirm quantity': '確認數量',
        'Pay': '付款',
        'Card simulation': '刷卡模擬',
        'Pickup': '取餐',
        'Show pickup code': '出示取餐碼',
        'Enter Noodle Code': '輸入泡麵代碼',
        'Scan the shelf QR code or tap a quick code to simulate the self-service kiosk.': '掃描貨架 QR Code，或點選快速代碼模擬自助機台。',
        'Add to Cart': '加入購物車',
        'Scanner ready': '掃描器已就緒',
        'Tap a noodle code below to load it into the kiosk.': '點選下方泡麵代碼載入機台。',
        'Available Noodle Codes:': '可用泡麵代碼：',
        'Your Cart': '你的購物車',
        'Your cart is empty.': '購物車目前是空的。',
        'Enter a noodle code above to start shopping!': '輸入上方泡麵代碼開始購物！',
        'Name': '名稱',
        'Price': '價格',
        'Qty': '數量',
        'Quantity': '數量',
        'Subtotal': '小計',
        'Total:': '總計：',
        'Clear All': '全部清除',
        'View Full Cart': '查看完整購物車',
        'Proceed to Checkout →': '前往結帳 →',
        'Your Shopping Cart': '你的購物車',
        'Continue Shopping': '繼續購物',
        'Shopping progress': '購物進度',
        'Your cart is empty': '購物車目前是空的',
        "Looks like you haven't added any noodles yet.": '你還沒有加入任何泡麵。',
        'Browse Noodles': '瀏覽泡麵',
        'Product': '商品',
        'Remove': '移除',
        'Update Cart': '更新購物車',
        'Clear Cart': '清空購物車',
        'Proceed to Checkout': '前往結帳',
        'Checkout': '結帳',
        'Back to Cart': '返回購物車',
        'Checkout progress': '結帳進度',
        'Order Summary': '訂單摘要',
        'Confirm your items before the self-service shelf unlocks the pickup code.': '請確認商品內容，付款完成後系統將產生取餐碼。',
        'Payment Method': '付款方式',
        'Card Number': '卡號',
        'Expiry Date': '有效期限',
        'CVV': '安全碼',
        'Self-service kiosk waiting for secure payment...': '自助機台正在等待安全付款...',
        'Cancel & Back to Cart': '取消並返回購物車',
        'Fill Demo Card': '填入展示卡號',
        'ORDER SUCCESSFUL!': '訂單完成！',
        'Thank you for your purchase!': '感謝你的購買！',
        'Order Number:': '訂單編號：',
        'Pickup Code:': '取餐碼：',
        'Total Paid:': '付款總額：',
        'Date:': '日期：',
        'How to pick up your noodles:': '如何領取泡麵：',
        'Payment verified': '付款已驗證',
        'Locker unlocking': '取餐櫃解鎖中',
        'Ready for pickup': '可以取餐',
        'Digital receipt ready': '電子收據已備妥',
        'Scan at the unmanned pickup locker': '請在無人取餐櫃掃描',
        'EMAIL CONFIRMATION': '電子郵件確認',
        'Order confirmation is prepared for': '訂單確認信已為此信箱備妥：',
        'Demo mode does not send an external email.': '展示模式不會寄送外部郵件。',
        'Order More Noodles': '繼續點餐',
        'View My Orders': '查看我的訂單',
        'My Order History': '我的訂單紀錄',
        'No Orders Yet': '尚無訂單',
        "You haven't placed any orders. Start shopping now!": '你還沒有建立訂單，現在就開始購物吧！',
        'Start Shopping': '開始購物',
        'Items:': '商品：',
        'Total Amount:': '總金額：',
        'Payment Status:': '付款狀態：',
        'Order Status:': '訂單狀態：',
        'My Profile': '我的個人資料',
        'Profile Information': '個人資料',
        'Email Address': '電子郵件',
        'Account Type': '帳號類型',
        'Member Since': '加入日期',
        'Update Profile': '更新資料',
        'Change Password': '變更密碼',
        'Current Password': '目前密碼',
        'New Password': '新密碼',
        'Confirm New Password': '確認新密碼'
    };

    const placeholderTranslations = {
        'Username': '使用者名稱',
        'Email': '電子郵件',
        'Username or Email': '使用者名稱或電子郵件',
        'Password': '密碼',
        'Confirm Password': '確認密碼',
        'Password (min 6 characters)': '密碼（至少 6 個字元）',
        'Example: N001': '例如：N001',
        'Qty': '數量',
        'MM/YY': '月/年'
    };

    const originalTextNodes = new WeakMap();
    const originalAttributes = new WeakMap();
    const originalDocumentTitle = document.title;
    let currentLanguage = localStorage.getItem('staffless-language') || 'en';

    const translateDynamicText = (text) => {
        const rules = [
            [/^Welcome,\s*(.+)!$/, '歡迎，$1！'],
            [/^Cart \((\d+)\)$/, '購物車 ($1)'],
            [/^In Stock:\s*(\d+)$/, '庫存：$1'],
            [/^(\d+)\s+left$/, '剩餘 $1'],
            [/^(\d+)x\s+(.+)$/, '$1 份 $2'],
            [/^Pay \$(.+)$/, '付款 $$1'],
            [/^Scan (N\d+)$/, '掃描 $1']
        ];
        for (const [pattern, replacement] of rules) {
            if (pattern.test(text)) return text.replace(pattern, replacement);
        }
        return null;
    };

    const applyLanguage = (language) => {
        currentLanguage = language;
        localStorage.setItem('staffless-language', language);
        document.documentElement.lang = language === 'zh-TW' ? 'zh-Hant' : 'en';
        document.title = language === 'zh-TW'
            ? originalDocumentTitle
                .replace('Staffless Instant Noodle Store', '無人泡麵商店')
                .replace('Noodle Store', '無人泡麵商店')
            : originalDocumentTitle;

        const walker = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT);
        const nodes = [];
        while (walker.nextNode()) nodes.push(walker.currentNode);

        nodes.forEach((node) => {
            const parentTag = node.parentElement?.tagName;
            if (!node.parentElement || ['SCRIPT', 'STYLE'].includes(parentTag)) return;
            if (!originalTextNodes.has(node)) originalTextNodes.set(node, node.nodeValue);
            const original = originalTextNodes.get(node);

            if (language === 'en') {
                node.nodeValue = original;
                return;
            }

            const trimmed = original.trim();
            const iconParts = trimmed.match(/^([^\p{L}\p{N}]*)(.*)$/u);
            const iconPrefix = iconParts?.[1] || '';
            const textWithoutIcon = iconParts?.[2] || trimmed;
            const translated = zhTranslations[trimmed]
                || (zhTranslations[textWithoutIcon] ? `${iconPrefix}${zhTranslations[textWithoutIcon]}` : null)
                || translateDynamicText(trimmed);
            if (translated) {
                const leading = original.match(/^\s*/)?.[0] || '';
                const trailing = original.match(/\s*$/)?.[0] || '';
                node.nodeValue = `${leading}${translated}${trailing}`;
            }
        });

        document.querySelectorAll('input[placeholder], textarea[placeholder]').forEach((field) => {
            if (!originalAttributes.has(field)) {
                originalAttributes.set(field, { placeholder: field.placeholder });
            }
            const original = originalAttributes.get(field).placeholder;
            field.placeholder = language === 'zh-TW'
                ? (placeholderTranslations[original] || original)
                : original;
        });

        document.querySelectorAll('[data-language-option]').forEach((button) => {
            const isActive = button.dataset.languageOption === language;
            button.classList.toggle('is-active', isActive);
            button.setAttribute('aria-pressed', String(isActive));
        });

        document.dispatchEvent(new CustomEvent('staffless:languagechange', { detail: { language } }));
    };

    const languageSwitch = document.createElement('div');
    languageSwitch.className = 'language-switch';
    languageSwitch.setAttribute('role', 'group');
    languageSwitch.setAttribute('aria-label', 'Language');
    languageSwitch.innerHTML = `
        <button type="button" data-language-option="en" aria-pressed="false">EN</button>
        <button type="button" data-language-option="zh-TW" aria-pressed="false">繁中</button>
    `;

    const languageTarget = document.querySelector('header .nav-links, header nav');
    if (languageTarget) {
        languageTarget.appendChild(languageSwitch);
    } else {
        const authForm = document.querySelector('.auth-form');
        if (authForm) authForm.insertAdjacentElement('afterbegin', languageSwitch);
    }

    languageSwitch.querySelectorAll('[data-language-option]').forEach((button) => {
        button.addEventListener('click', () => applyLanguage(button.dataset.languageOption));
    });

    applyLanguage(currentLanguage);

    document.querySelectorAll('a[href^="#"]').forEach((link) => {
        link.addEventListener('click', (event) => {
            const target = document.querySelector(link.getAttribute('href'));
            if (!target) return;
            event.preventDefault();
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });

    document.querySelectorAll('header').forEach((header) => {
        const nav = header.querySelector('.nav-links, nav');
        if (!nav || header.querySelector('.nav-toggle')) return;

        const toggle = document.createElement('button');
        toggle.type = 'button';
        toggle.className = 'nav-toggle';
        toggle.setAttribute('aria-label', 'Toggle navigation');
        toggle.setAttribute('aria-expanded', 'false');
        toggle.textContent = '☰';
        toggle.addEventListener('click', () => {
            const isOpen = nav.classList.toggle('is-open');
            toggle.setAttribute('aria-expanded', String(isOpen));
            toggle.textContent = isOpen ? '✕' : '☰';
        });
        header.appendChild(toggle);

        nav.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', () => {
                nav.classList.remove('is-open');
                toggle.setAttribute('aria-expanded', 'false');
                toggle.textContent = '☰';
            });
        });
    });

    document.querySelectorAll('[data-fill-demo-login]').forEach((button) => {
        button.addEventListener('click', () => {
            const form = document.querySelector('[data-login-form]');
            if (!form) return;
            const isAdmin = button.dataset.fillDemoLogin === 'admin';
            form.querySelector('[data-login-username]').value = isAdmin ? 'admin' : 'john_doe';
            form.querySelector('[data-login-password]').value = isAdmin ? 'admin123' : 'user123';
        });
    });

    document.querySelectorAll('[data-demo-choice]').forEach((button) => {
        button.addEventListener('click', () => {
            const [code, name, desc] = button.dataset.demoChoice.split('|');
            const demo = document.querySelector('[data-kiosk-demo]');
            if (!demo) return;

            demo.querySelector('[data-demo-code]').textContent = code;
            demo.querySelector('[data-demo-name]').textContent = name;
            demo.querySelector('[data-demo-desc]').textContent = desc || 'Ready to add this noodle by code.';
            demo.querySelector('.kiosk-status').textContent = currentLanguage === 'zh-TW' ? 'QR Code 掃描成功' : 'QR scan accepted';
            demo.classList.add('is-scanned');
            setTimeout(() => demo.classList.remove('is-scanned'), 700);
        });
    });

    document.querySelectorAll('[data-noodle-code]').forEach((button) => {
        button.addEventListener('click', () => {
            const input = document.querySelector('[data-noodle-code-input]');
            const screen = document.querySelector('[data-kiosk-screen]');
            if (!input) return;

            input.value = button.dataset.noodleCode;
            input.focus();

            if (screen) {
                const loaded = currentLanguage === 'zh-TW' ? '已載入' : 'loaded';
                const ready = currentLanguage === 'zh-TW' ? '可以加入購物車。' : 'is ready to add.';
                screen.innerHTML = `<span class="kiosk-dot"></span><strong>${button.dataset.noodleCode} ${loaded}</strong><p>${button.dataset.noodleName} ${ready}</p>`;
                screen.classList.add('is-ready');
            }
        });
    });

    const recommender = document.querySelector('[data-ai-recommender]');
    if (recommender) {
        const resultPanel = document.querySelector('[data-ai-result]');
        const catalog = [
            { code: 'N005', name: 'Maruchan Chicken', style: 'classic', spice: 'mild', mood: 'comfort', image: 'assets/images/noodles/N005-chicken.webp',
                reasonEn: 'Classic chicken broth is gentle, balanced and made for a comforting meal.',
                reasonZh: '經典雞湯風味溫和均衡，很適合需要療癒的一餐。' },
            { code: 'N006', name: 'Cup Noodles', style: 'light', spice: 'mild', mood: 'quick', image: 'assets/images/noodles/N006-cup.webp',
                reasonEn: 'A light, convenient cup is the fastest match when you want a simple meal.',
                reasonZh: '清爽便利的杯麵，最適合想快速享用簡單一餐的時刻。' },
            { code: 'N007', name: 'Sapporo Ichiban', style: 'classic', spice: 'mild', mood: 'comfort', image: 'assets/images/noodles/N007-shoyu.webp',
                reasonEn: 'Balanced shoyu flavor brings familiar Japanese comfort without heavy spice.',
                reasonZh: '均衡醬油風味帶來熟悉的日式療癒感，辣度也相當溫和。' },
            { code: 'N002', name: 'Indomie Mi Goreng', style: 'dry', spice: 'medium', mood: 'energy', image: 'assets/images/noodles/N002-mi-goreng.webp',
                reasonEn: 'Savory dry noodles and a medium kick deliver a lively energy boost.',
                reasonZh: '鹹香乾拌麵搭配中等辣度，能帶來充滿活力的滿足感。' },
            { code: 'N003', name: 'Mama Tom Yum', style: 'sour', spice: 'hot', mood: 'adventure', image: 'assets/images/noodles/N003-tom-yum.webp',
                reasonEn: 'Bright tom yum aroma and bold heat are ideal for an adventurous appetite.',
                reasonZh: '鮮明冬蔭功酸香與強烈辣度，最適合想冒險嘗鮮的你。' },
            { code: 'N008', name: 'Nissin Raoh', style: 'rich', spice: 'medium', mood: 'reward', image: 'assets/images/noodles/N008-tonkotsu.webp',
                reasonEn: 'Rich tonkotsu broth is a rewarding match for a premium ramen moment.',
                reasonZh: '濃厚豚骨湯頭很有犒賞感，適合享受高級拉麵時刻。' }
        ];
        let currentRecommendation = catalog[5];

        const renderRecommendation = (item, confidence = 96) => {
            currentRecommendation = item;
            resultPanel.dataset.recommendationCode = item.code;
            resultPanel.querySelector('[data-ai-result-image]').src = item.image;
            resultPanel.querySelector('[data-ai-result-image]').alt = item.name;
            resultPanel.querySelector('[data-ai-result-name]').textContent = item.name;
            resultPanel.querySelector('[data-ai-confidence]').textContent =
                currentLanguage === 'zh-TW' ? `${confidence}% 配對` : `${confidence}% MATCH`;
            resultPanel.querySelector('[data-ai-result-reason]').textContent =
                currentLanguage === 'zh-TW' ? item.reasonZh : item.reasonEn;
            resultPanel.querySelector('.ai-result-status').textContent =
                currentLanguage === 'zh-TW' ? `AI 推薦完成 · ${item.code}` : `AI match complete · ${item.code}`;
            const orderLink = resultPanel.querySelector('[data-ai-order-link]');
            if (orderLink) {
                const target = `dashboard.php?code=${encodeURIComponent(item.code)}`;
                orderLink.href = orderLink.dataset.authenticated === '1'
                    ? target
                    : `login.php?next=${encodeURIComponent(target)}`;
            }
            resultPanel.classList.add('is-matched');
            setTimeout(() => resultPanel.classList.remove('is-matched'), 700);
        };

        recommender.addEventListener('submit', (event) => {
            event.preventDefault();
            const preferences = new FormData(recommender);
            const result = catalog
                .map((item) => ({
                    item,
                    score: (item.style === preferences.get('taste') ? 5 : 0)
                        + (item.spice === preferences.get('spice') ? 3 : 0)
                        + (item.mood === preferences.get('mood') ? 4 : 0)
                }))
                .sort((a, b) => b.score - a.score)[0];

            resultPanel.querySelector('.ai-result-status').textContent =
                currentLanguage === 'zh-TW' ? 'AI 正在分析你的口味...' : 'AI is analyzing your taste...';
            setTimeout(() => renderRecommendation(result.item, Math.min(99, 82 + result.score)), 500);
        });

        document.addEventListener('staffless:languagechange', () => {
            renderRecommendation(currentRecommendation, Number(resultPanel.querySelector('[data-ai-confidence]').textContent.replace(/\D/g, '')) || 96);
        });
        renderRecommendation(currentRecommendation, 96);
    }

    document.querySelectorAll('[data-live-subtotal]').forEach((input) => {
        input.addEventListener('input', () => {
            const row = input.closest('tr');
            const subtotal = row?.querySelector('[data-line-subtotal]');
            if (!subtotal) return;
            subtotal.textContent = formatMoney(Number(input.dataset.price) * Number(input.value || 0));

            // AI 修改：數量變動時同步更新購物車總額，不必先送出表單才看得到結果
            const total = [...document.querySelectorAll('[data-live-subtotal]')]
                .reduce((sum, item) => sum + (Number(item.dataset.price) * Number(item.value || 0)), 0);
            const totalNode = document.querySelector('[data-cart-total]');
            if (totalNode) totalNode.textContent = formatMoney(total);
        });
    });

    const paymentForm = document.querySelector('[data-payment-form]');
    if (paymentForm) {
        const cardNumber = paymentForm.querySelector('[data-card-number]');
        const expiry = paymentForm.querySelector('[data-card-expiry]');
        const cvv = paymentForm.querySelector('[data-card-cvv]');
        const preview = document.querySelector('[data-payment-preview]');

        const updatePreview = (message) => {
            if (preview) preview.querySelector('strong').textContent = message;
        };

        paymentForm.querySelectorAll('input[name="card_type"]').forEach((radio) => {
            radio.addEventListener('change', () => {
                updatePreview(`${radio.value.toUpperCase()} reader selected`);
                if (cvv) cvv.maxLength = radio.value === 'amex' ? 4 : 3;
            });
        });

        cardNumber?.addEventListener('input', () => {
            const digits = cardNumber.value.replace(/\D/g, '').slice(0, 16);
            cardNumber.value = digits.replace(/(.{4})/g, '$1 ').trim();
        });

        expiry?.addEventListener('input', () => {
            const digits = expiry.value.replace(/\D/g, '').slice(0, 4);
            expiry.value = digits.length > 2 ? `${digits.slice(0, 2)}/${digits.slice(2)}` : digits;
        });

        cvv?.addEventListener('input', () => {
            cvv.value = cvv.value.replace(/\D/g, '').slice(0, Number(cvv.maxLength || 4));
        });

        document.querySelector('[data-fill-demo-card]')?.addEventListener('click', () => {
            const selected = paymentForm.querySelector('input[name="card_type"]:checked') || paymentForm.querySelector('input[name="card_type"]');
            selected.checked = true;
            selected.dispatchEvent(new Event('change'));

            if (cardNumber) cardNumber.value = selected.dataset.demoCard.replace(/(.{4})/g, '$1 ').trim();
            if (expiry) expiry.value = '12/28';
            if (cvv) cvv.value = selected.value === 'amex' ? '1234' : '123';
            updatePreview('Demo payment details filled');
        });
    }

    const tracker = document.querySelector('[data-pickup-tracker]');
    if (tracker) {
        const stages = [...tracker.querySelectorAll('.pickup-stage')];
        stages.forEach((stage, index) => {
            setTimeout(() => {
                stages.forEach((item, itemIndex) => item.classList.toggle('is-active', itemIndex <= index));
            }, index * 900);
        });
    }

    const pickupQr = document.querySelector('[data-pickup-qr]');
    if (pickupQr) {
        // AI 修改：用取餐碼生成固定驗證矩陣，模擬無人取餐櫃掃描畫面
        const token = pickupQr.dataset.pickupQr || 'STAFFLESS';
        const size = 15;
        const finderCells = new Set();
        const markFinder = (offsetX, offsetY) => {
            for (let y = 0; y < 5; y += 1) {
                for (let x = 0; x < 5; x += 1) {
                    const edge = x === 0 || y === 0 || x === 4 || y === 4;
                    const center = x >= 2 && x <= 2 && y >= 2 && y <= 2;
                    if (edge || center) finderCells.add(`${offsetX + x}:${offsetY + y}`);
                }
            }
        };
        markFinder(0, 0);
        markFinder(size - 5, 0);
        markFinder(0, size - 5);

        let seed = [...token].reduce((sum, character, index) => sum + character.charCodeAt(0) * (index + 3), 0);
        for (let y = 0; y < size; y += 1) {
            for (let x = 0; x < size; x += 1) {
                seed = (seed * 9301 + 49297) % 233280;
                const cell = document.createElement('span');
                if (finderCells.has(`${x}:${y}`) || seed / 233280 > 0.52) {
                    cell.classList.add('is-dark');
                }
                pickupQr.appendChild(cell);
            }
        }
    }

    const revealItems = document.querySelectorAll('.noodle-card, .timeline-item, .step, .order-card, .stat-card');
    if (revealItems.length && 'IntersectionObserver' in window) {
        revealItems.forEach((item) => item.classList.add('reveal-on-scroll'));
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.15 });
        revealItems.forEach((item) => observer.observe(item));
    }

    const noodleCodeInput = document.querySelector('[data-noodle-code-input]');
    if (noodleCodeInput) {
        noodleCodeInput.addEventListener('input', () => {
            noodleCodeInput.value = noodleCodeInput.value.toUpperCase();
        });
    }
});
