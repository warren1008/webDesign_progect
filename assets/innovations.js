// AI 修改：Word 更新清單的共用互動集中於此，避免各頁重複貼上相同程式。
(() => {
    const path = location.pathname.replace(/\\/g, '/');
    const file = path.split('/').pop() || 'index.php';
    const isAdmin = path.includes('/admin/');
    const apiUrl = isAdmin ? '../api.php' : 'api.php';
    const language = () => localStorage.getItem('staffless-language') || 'en';
    const zh = () => language() === 'zh-TW';
    const text = (zhText, enText) => zh() ? zhText : enText;
    const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content
        || document.querySelector('[name="csrf_token"]')?.value || '';

    const postApi = async (action, payload = {}) => {
        const response = await fetch(apiUrl, {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'X-Requested-With': 'fetch'},
            body: JSON.stringify({action, csrf_token: csrf(), ...payload})
        });
        const data = await response.json().catch(() => ({success: false, message: 'Invalid server response.'}));
        if (!response.ok || !data.success) throw new Error(data.message || 'Request failed.');
        return data;
    };

    const toast = (message, type = 'success') => {
        const node = document.createElement('div');
        node.className = `innovation-toast is-${type}`;
        node.setAttribute('role', 'status');
        node.textContent = message;
        document.body.appendChild(node);
        requestAnimationFrame(() => node.classList.add('is-visible'));
        setTimeout(() => {
            node.classList.remove('is-visible');
            setTimeout(() => node.remove(), 250);
        }, 3200);
    };

    const modal = ({title, content, actions = [], className = ''}) => {
        const shell = document.createElement('div');
        shell.className = `innovation-modal ${className}`;
        shell.innerHTML = `
            <div class="innovation-modal__backdrop" data-close-modal></div>
            <section class="innovation-modal__panel" role="dialog" aria-modal="true" aria-labelledby="innovation-modal-title">
                <button type="button" class="innovation-modal__close" data-close-modal aria-label="${text('關閉', 'Close')}">&times;</button>
                <h2 id="innovation-modal-title">${title}</h2>
                <div class="innovation-modal__content"></div>
                <div class="innovation-modal__actions"></div>
            </section>`;
        const contentNode = shell.querySelector('.innovation-modal__content');
        if (typeof content === 'string') contentNode.innerHTML = content;
        else if (content) contentNode.appendChild(content);
        const actionNode = shell.querySelector('.innovation-modal__actions');
        actions.forEach((action) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = action.className || 'btn btn-primary';
            button.textContent = action.label;
            button.addEventListener('click', () => action.onClick?.(shell));
            actionNode.appendChild(button);
        });
        const close = () => {
            shell.classList.remove('is-open');
            setTimeout(() => shell.remove(), 220);
        };
        shell.querySelectorAll('[data-close-modal]').forEach((item) => item.addEventListener('click', close));
        shell.closeModal = close;
        document.body.appendChild(shell);
        requestAnimationFrame(() => shell.classList.add('is-open'));
        shell.querySelector('.innovation-modal__close').focus();
        return shell;
    };

    const createQr = (token, size = 21) => {
        const grid = document.createElement('div');
        grid.className = 'generated-qr';
        grid.style.setProperty('--qr-size', size);
        let seed = [...String(token)].reduce((sum, char, index) => sum + char.charCodeAt(0) * (index + 7), 0);
        const finder = (x, y) => (x < 7 && y < 7) || (x >= size - 7 && y < 7) || (x < 7 && y >= size - 7);
        for (let y = 0; y < size; y += 1) {
            for (let x = 0; x < size; x += 1) {
                seed = (seed * 9301 + 49297) % 233280;
                const cell = document.createElement('i');
                const localX = x < 7 ? x : x >= size - 7 ? x - (size - 7) : -1;
                const localY = y < 7 ? y : y >= size - 7 ? y - (size - 7) : -1;
                const finderDark = finder(x, y) && (
                    localX === 0 || localY === 0 || localX === 6 || localY === 6
                    || (localX >= 2 && localX <= 4 && localY >= 2 && localY <= 4)
                );
                if (finderDark || (!finder(x, y) && seed / 233280 > 0.5)) cell.className = 'is-dark';
                grid.appendChild(cell);
            }
        }
        grid.setAttribute('aria-label', `QR ${token}`);
        return grid;
    };

    const createBarcode = (token) => {
        const barcode = document.createElement('div');
        barcode.className = 'generated-barcode';
        [...String(token)].forEach((char, index) => {
            const bar = document.createElement('i');
            bar.style.width = `${1 + ((char.charCodeAt(0) + index) % 4)}px`;
            barcode.appendChild(bar);
        });
        return barcode;
    };

    const animateNumber = (node, target, suffix = '') => {
        if (!node) return;
        const start = performance.now();
        const duration = 700;
        const step = (now) => {
            const ratio = Math.min(1, (now - start) / duration);
            node.textContent = `${Math.round(target * (1 - Math.pow(1 - ratio, 3)))}${suffix}`;
            if (ratio < 1) requestAnimationFrame(step);
        };
        requestAnimationFrame(step);
    };

    const speak = (zhText, enText) => {
        if (!('speechSynthesis' in window) || localStorage.getItem('staffless-voice') === 'off') return;
        speechSynthesis.cancel();
        const utterance = new SpeechSynthesisUtterance(text(zhText, enText));
        utterance.lang = zh() ? 'zh-TW' : 'en-US';
        utterance.rate = 1.05;
        speechSynthesis.speak(utterance);
    };

    const initFaq = () => {
        const t = (key, zhFallback, enFallback) => window.stafflessI18n?.t(key) || text(zhFallback, enFallback);
        const clamp = (value, min, max) => Math.min(Math.max(value, min), max);
        const readPosition = (key) => {
            try {
                const parsed = JSON.parse(localStorage.getItem(key) || 'null');
                return parsed && Number.isFinite(parsed.left) && Number.isFinite(parsed.top) ? parsed : null;
            } catch {
                return null;
            }
        };
        const savePosition = (key, left, top) => {
            localStorage.setItem(key, JSON.stringify({left: Math.round(left), top: Math.round(top)}));
        };
        const applyPosition = (node, left, top) => {
            const rect = node.getBoundingClientRect();
            const nextLeft = clamp(left, 8, window.innerWidth - rect.width - 8);
            const nextTop = clamp(top, 8, window.innerHeight - rect.height - 8);
            node.style.left = `${nextLeft}px`;
            node.style.top = `${nextTop}px`;
            node.style.right = 'auto';
            node.style.bottom = 'auto';
            node.classList.add('is-positioned');
        };
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'faq-launcher';
        button.innerHTML = '<span aria-hidden="true">?</span>';
        button.setAttribute('aria-label', text('開啟智慧 FAQ', 'Open Smart FAQ'));
        button.title = text('智慧 FAQ', 'Smart FAQ');
        button.setAttribute('aria-expanded', 'false');
        const panel = document.createElement('aside');
        panel.className = 'faq-panel';
        panel.setAttribute('aria-live', 'polite');
        panel.innerHTML = `
            <div class="faq-panel__header">
                <div><strong data-i18n="faq.title">Smart FAQ</strong><small data-i18n="faq.assistant">DRAGGABLE ASSISTANT</small></div>
                <button type="button" data-faq-close aria-label="${text('關閉', 'Close')}">&times;</button>
            </div>
            <p class="faq-disclaimer" data-i18n="faq.demo"></p>
            <span class="faq-drag-hint" data-i18n="faq.drag_hint">Drag to move</span>
            <div class="faq-questions">
                <button type="button" data-faq="machine" data-i18n="faq.machine"></button>
                <button type="button" data-faq="partner" data-i18n="faq.partner"></button>
                <button type="button" data-faq="order" data-i18n="faq.order"></button>
            </div>
            <div class="faq-answer" data-faq-answer>${text('請選擇問題。', 'Choose a question.')}</div>`;
        document.body.append(button, panel);
        window.stafflessI18n?.apply(document);
        const toggle = (open) => {
            if (open && !panel.classList.contains('is-positioned')) {
                const rect = button.getBoundingClientRect();
                requestAnimationFrame(() => {
                    const panelRect = panel.getBoundingClientRect();
                    const top = rect.top > panelRect.height + 18 ? rect.top - panelRect.height - 12 : rect.bottom + 12;
                    applyPosition(panel, rect.left, top);
                });
            }
            panel.classList.toggle('is-open', open);
            button.setAttribute('aria-expanded', String(open));
            if (open) panel.querySelector('[data-faq]')?.focus();
        };
        const savedButton = readPosition('staffless-faq-button-position');
        if (savedButton) requestAnimationFrame(() => applyPosition(button, savedButton.left, savedButton.top));
        const savedPanel = readPosition('staffless-faq-panel-position');
        if (savedPanel) requestAnimationFrame(() => applyPosition(panel, savedPanel.left, savedPanel.top));

        const makeDraggable = (node, handle, key) => {
            let start = null;
            handle.addEventListener('pointerdown', (event) => {
                if (event.target.closest('button, a, input, textarea, select')) return;
                const rect = node.getBoundingClientRect();
                start = {
                    pointerId: event.pointerId,
                    x: event.clientX,
                    y: event.clientY,
                    left: rect.left,
                    top: rect.top,
                    moved: false
                };
                node.classList.add('is-dragging');
                handle.setPointerCapture?.(event.pointerId);
            });
            handle.addEventListener('pointermove', (event) => {
                if (!start || start.pointerId !== event.pointerId) return;
                const dx = event.clientX - start.x;
                const dy = event.clientY - start.y;
                if (Math.abs(dx) + Math.abs(dy) > 4) start.moved = true;
                applyPosition(node, start.left + dx, start.top + dy);
            });
            const stop = (event) => {
                if (!start || start.pointerId !== event.pointerId) return;
                const rect = node.getBoundingClientRect();
                savePosition(key, rect.left, rect.top);
                if (start.moved) node.dataset.wasDragged = '1';
                node.classList.remove('is-dragging');
                handle.releasePointerCapture?.(event.pointerId);
                window.setTimeout(() => { delete node.dataset.wasDragged; }, 0);
                start = null;
            };
            handle.addEventListener('pointerup', stop);
            handle.addEventListener('pointercancel', stop);
        };

        makeDraggable(button, button, 'staffless-faq-button-position');
        makeDraggable(panel, panel.querySelector('.faq-panel__header'), 'staffless-faq-panel-position');
        button.addEventListener('click', () => {
            if (button.dataset.wasDragged === '1') return;
            toggle(!panel.classList.contains('is-open'));
        });
        panel.querySelector('[data-faq-close]').addEventListener('click', () => toggle(false));
        panel.querySelectorAll('[data-faq]').forEach((question) => question.addEventListener('click', () => {
            const key = question.dataset.faq;
            panel.querySelector('[data-faq-answer]').textContent = t(`faq.${key}_answer`, '', '');
            if (key === 'machine' && document.body.dataset.loggedIn === '1' && !panel.querySelector('[data-create-case]')) {
                const caseButton = document.createElement('button');
                caseButton.type = 'button';
                caseButton.className = 'btn btn-secondary btn-small';
                caseButton.dataset.createCase = '1';
                caseButton.textContent = text('建立客服案件', 'Create support case');
                caseButton.addEventListener('click', async () => {
                    try {
                        const data = await postApi('support_case');
                        toast(data.message);
                        caseButton.disabled = true;
                    } catch (error) { toast(error.message, 'error'); }
                });
                panel.querySelector('[data-faq-answer]').append(document.createElement('br'), caseButton);
            }
        }));
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') toggle(false);
        });
        window.addEventListener('resize', () => {
            [button, panel].forEach((node) => {
                if (!node.classList.contains('is-positioned')) return;
                const rect = node.getBoundingClientRect();
                applyPosition(node, rect.left, rect.top);
            });
        });
    };

    const initDashboard = () => {
        document.querySelectorAll('.code-item').forEach((item) => {
            const stock = Number(item.querySelector('.stock-badge')?.textContent.match(/\d+/)?.[0] || 0);
            if (stock < 10) {
                item.classList.add('is-critical-stock');
                item.querySelector('.stock-badge')?.insertAdjacentHTML('beforeend', ` <b>${text('搶購中', 'HOT')}</b>`);
            }
            item.addEventListener('click', () => {
                document.querySelectorAll('.code-item').forEach((other) => other.classList.remove('is-selected'));
                item.classList.add('is-selected');
                const code = item.dataset.noodleCode;
                const name = item.dataset.noodleName;
                const recommendations = {
                    N004: text('推薦起司片與溏心蛋，中和辣度更美味。', 'Try cheese and ajitama egg to balance the heat.'),
                    N007: text('推薦加溏心蛋與海苔，讓醬油湯頭更有層次。', 'Ajitama egg and seaweed pair well with the shoyu broth.'),
                    N008: text('推薦叉燒與蔥花，打造濃厚豚骨風味。', 'Chashu and scallion complete the rich tonkotsu profile.')
                };
                const screen = document.querySelector('[data-kiosk-screen]');
                if (screen && recommendations[code]) {
                    let note = screen.querySelector('.chef-recommendation');
                    if (!note) {
                        note = document.createElement('p');
                        note.className = 'chef-recommendation';
                        screen.appendChild(note);
                    }
                    note.textContent = `AI CHEF · ${recommendations[code]}`;
                }
                speak(`已為您載入 ${name}。${recommendations[code] || '可搭配溏心蛋增加口感。'}`, `${name} is loaded. ${recommendations[code] || 'Try adding an ajitama egg.'}`);
            });
        });
        document.querySelector('form button[name="add_to_cart"]')?.closest('form')?.addEventListener('submit', () => {
            const selected = document.querySelector('.code-item.is-selected');
            const badge = selected?.querySelector('.stock-badge');
            if (badge) {
                const value = Number(badge.textContent.match(/\d+/)?.[0] || 0);
                badge.textContent = `${Math.max(0, value - 1)} ${text('份', 'left')}`;
                badge.classList.add('stock-tick');
            }
        });
        document.querySelector('a[href="checkout.php"]')?.addEventListener('click', () => {
            speak('請至自助機台完成付款，謝謝光臨。', 'Please complete the payment at the self-service kiosk. Thank you.');
        });
    };

    const initPromotions = () => {
        document.querySelectorAll('[data-promotion-action]').forEach((button) => button.addEventListener('click', async () => {
            try {
                const data = await postApi('add_bundle', {code: button.dataset.code, quantity: Number(button.dataset.quantity || 1)});
                toast(data.message);
                setTimeout(() => location.href = button.dataset.destination || 'dashboard.php', 500);
            } catch (error) { toast(error.message, 'error'); }
        }));
        document.querySelectorAll('[data-promotion-countdown]').forEach((node) => {
            const end = new Date(node.dataset.promotionCountdown).getTime();
            const update = () => {
                const remaining = Math.max(0, end - Date.now());
                const hours = Math.floor(remaining / 3600000);
                const minutes = Math.floor(remaining % 3600000 / 60000);
                const seconds = Math.floor(remaining % 60000 / 1000);
                node.textContent = `${hours}h ${String(minutes).padStart(2, '0')}m ${String(seconds).padStart(2, '0')}s`;
            };
            update();
            setInterval(update, 1000);
        });
    };

    const initProfile = () => {
        document.querySelectorAll('[data-profile-qr]').forEach((node) => node.appendChild(createQr(node.dataset.profileQr)));
        document.querySelector('[data-reorder-last]')?.addEventListener('click', async () => {
            try {
                const data = await postApi('reorder_last');
                toast(data.message);
                setTimeout(() => location.href = 'dashboard.php', 650);
            } catch (error) { toast(error.message, 'error'); }
        });
    };

    const initOrderHistory = () => {
        document.querySelectorAll('[data-show-pickup-qr]').forEach((button) => button.addEventListener('click', () => {
            const token = button.dataset.pickupCode;
            const content = document.createElement('div');
            content.className = 'credential-display';
            content.append(createQr(token));
            content.insertAdjacentHTML('beforeend', `<strong>${token}</strong><p>${text('請在無人取餐機台出示此 QR Code。', 'Show this QR code at the pickup kiosk.')}</p>`);
            modal({title: text('手機掃碼取餐', 'Mobile Pickup QR'), content});
        }));
        document.querySelectorAll('[data-review-order]').forEach((button) => button.addEventListener('click', () => {
            const form = document.createElement('form');
            form.className = 'review-form';
            form.innerHTML = `
                <div class="star-picker" role="radiogroup">
                    ${[1,2,3,4,5].map((value) => `<button type="button" data-rating="${value}" aria-label="${value} stars">★</button>`).join('')}
                </div>
                <textarea maxlength="255" placeholder="${text('分享這碗麵的口感（選填）', 'Share your ramen experience (optional)')}"></textarea>`;
            let rating = 5;
            const paint = () => form.querySelectorAll('[data-rating]').forEach((star) => star.classList.toggle('is-active', Number(star.dataset.rating) <= rating));
            form.querySelectorAll('[data-rating]').forEach((star) => star.addEventListener('click', () => { rating = Number(star.dataset.rating); paint(); }));
            paint();
            modal({
                title: text('美味評價 · 完成可獲 10 點', 'Rate this ramen · Earn 10 points'),
                content: form,
                actions: [{
                    label: text('送出評價', 'Submit Review'),
                    onClick: async (shell) => {
                        try {
                            const data = await postApi('review_order', {order_id: Number(button.dataset.reviewOrder), rating, comment: form.querySelector('textarea').value});
                            toast(data.message);
                            button.disabled = true;
                            button.textContent = text('已完成評價', 'Reviewed');
                            shell.closeModal();
                        } catch (error) { toast(error.message, 'error'); }
                    }
                }]
            });
        }));
    };

    const initPartners = () => {
        document.querySelectorAll('[data-partner-target]').forEach((node) => node.addEventListener('click', () => {
            const card = document.getElementById(node.dataset.partnerTarget);
            card?.scrollIntoView({behavior: 'smooth', block: 'center'});
            card?.classList.add('is-located');
            setTimeout(() => card?.classList.remove('is-located'), 2000);
        }));
        document.querySelectorAll('[data-unlock-partner]').forEach((button) => button.addEventListener('click', async () => {
            try {
                const data = await postApi('partner_unlock');
                button.classList.add('is-unlocked');
                button.textContent = text('已解鎖：出示取餐碼享優惠', 'Unlocked: show pickup code');
                const barcode = createBarcode(data.pickup_code);
                button.closest('.partner-card')?.appendChild(barcode);
            } catch (error) { toast(error.message, 'error'); }
        }));
    };

    const initMemberCenter = () => {
        const points = document.querySelector('[data-points-count]');
        if (points) animateNumber(points, Number(points.dataset.pointsCount), zh() ? ' 點' : ' P');
        document.querySelectorAll('[data-voucher-code]').forEach((button) => button.addEventListener('click', () => {
            const code = button.dataset.voucherCode;
            const content = document.createElement('div');
            content.className = 'credential-display';
            content.append(createQr(code), createBarcode(code));
            content.insertAdjacentHTML('beforeend', `<strong>${code}</strong><p>${text('請至合作店家出示此條碼核銷優惠。', 'Show this code at the partner store.')}</p>`);
            modal({title: text('會員兌換券', 'Member Voucher'), content});
        }));
    };

    const initRewards = () => {
        document.querySelectorAll('[data-redeem-reward]').forEach((button) => button.addEventListener('click', async (event) => {
            event.preventDefault();
            if (button.disabled) return;
            try {
                button.disabled = true;
                const data = await postApi('redeem_reward', {reward_id: Number(button.dataset.redeemReward)});
                const balance = document.querySelector('[data-reward-balance]');
                if (balance) animateNumber(balance, Number(data.balance), zh() ? ' 點' : ' P');
                const stock = button.closest('.reward-card')?.querySelector('[data-reward-stock]');
                if (stock) {
                    const next = Math.max(0, Number(stock.dataset.rewardStock) - 1);
                    stock.dataset.rewardStock = next;
                    const zhPrefix = stock.dataset.stockPrefixZh || '剩餘';
                    const enPrefix = stock.dataset.stockPrefixEn || 'Available';
                    stock.dataset.zh = `${zhPrefix} ${next}`;
                    stock.dataset.en = `${enPrefix} ${next}`;
                    stock.textContent = `${text(zhPrefix, enPrefix)} ${next}`;
                }
                modal({title: text('兌換成功', 'Redemption Complete'), content: `<div class="credential-display"><strong>${data.code}</strong><p>${data.message}</p></div>`});
                button.textContent = text('已兌換', 'Redeemed');
            } catch (error) {
                button.disabled = false;
                toast(error.message, 'error');
            }
        }));
    };

    const initAuth = () => {
        const typeInto = async (input, value) => {
            input.value = '';
            input.classList.add('typing-glow');
            const delay = Math.max(12, 300 / value.length);
            for (const character of value) {
                input.value += character;
                await new Promise((resolve) => setTimeout(resolve, delay));
            }
            setTimeout(() => input.classList.remove('typing-glow'), 500);
        };
        document.querySelectorAll('[data-fill-demo-login]').forEach((button) => {
            button.addEventListener('click', async (event) => {
                event.preventDefault();
                event.stopImmediatePropagation();
                const user = button.dataset.fillDemoLogin === 'admin' ? ['admin', 'admin123'] : ['john_doe', 'user123'];
                await Promise.all([
                    typeInto(document.querySelector('[data-login-username]'), user[0]),
                    typeInto(document.querySelector('[data-login-password]'), user[1])
                ]);
            }, {capture: true});
        });
        const loginForm = document.querySelector('[data-login-form]');
        if (loginForm && !document.querySelector('[data-face-login]')) {
            const faceButton = document.createElement('button');
            faceButton.type = 'button';
            faceButton.className = 'btn btn-secondary simulated-face-button';
            faceButton.dataset.faceLogin = '1';
            faceButton.textContent = text('啟動快速身分驗證', 'Start Quick Identity Check');
            loginForm.after(faceButton);
            faceButton.addEventListener('click', () => {
                const scan = document.createElement('div');
                scan.className = 'face-scan';
                scan.innerHTML = `<div class="face-grid"><i></i></div><strong>${text('正在進行快速身分驗證，不會上傳攝影機影像', 'Quick identity check in progress. No camera image is uploaded.')}</strong>`;
                const shell = modal({title: text('無人機台身分驗證', 'Kiosk Identity Check'), content: scan});
                setTimeout(async () => {
                    await Promise.all([
                        typeInto(document.querySelector('[data-login-username]'), 'john_doe'),
                        typeInto(document.querySelector('[data-login-password]'), 'user123')
                    ]);
                    shell.closeModal();
                    loginForm.requestSubmit();
                }, 2500);
            });
        }
        if (document.querySelector('.auth-form .error')) document.querySelector('.auth-form')?.classList.add('auth-shake');

        const resetForm = document.querySelector('[data-password-reset-form]');
        document.querySelector('[data-fill-demo-email]')?.addEventListener('click', () => {
            const email = resetForm?.querySelector('input[type="email"]');
            if (email) email.value = 'john@example.com';
        });
        if (resetForm) resetForm.addEventListener('submit', (event) => {
            if (resetForm.dataset.ready === '1') return;
            event.preventDefault();
            const overlay = document.createElement('div');
            overlay.className = 'matrix-overlay';
            overlay.innerHTML = `<div class="matrix-terminal"><strong>${text('正在建立安全連線...', 'Establishing secure connection...')}</strong><div><i></i></div><p>${text('正在產生數位簽章 · 郵件加密發送中', 'Generating digital signature · Encrypting email')}</p></div>`;
            document.body.appendChild(overlay);
            setTimeout(() => {
                resetForm.dataset.ready = '1';
                resetForm.submit();
            }, 2500);
        });

        const randomButton = document.querySelector('[data-random-register]');
        randomButton?.addEventListener('click', () => {
            const value = Math.floor(100 + Math.random() * 900);
            const form = randomButton.closest('.auth-form')?.querySelector('form');
            form.querySelector('[name="username"]').value = `ramen_fan_${value}`;
            form.querySelector('[name="email"]').value = `test_${value}@ramen.com`;
            form.querySelector('[name="password"]').value = 'Ramen123456';
            form.querySelector('[name="confirm_password"]').value = 'Ramen123456';
            form.querySelectorAll('input').forEach((input) => input.classList.add('typing-glow'));
        });
        const registerForm = document.querySelector('[data-register-form]');
        if (registerForm) registerForm.addEventListener('submit', (event) => {
            if (registerForm.dataset.ready === '1') return;
            event.preventDefault();
            const overlay = document.createElement('div');
            overlay.className = 'matrix-overlay';
            overlay.innerHTML = `<div class="matrix-terminal"><strong>${text('正在建立智慧會員聯網...', 'Initializing smart member network...')}</strong><div><i></i></div><p>${text('配置拉麵金幣電子錢包 · 權限分級開通中', 'Configuring ramen wallet · Activating access level')}</p></div>`;
            document.body.appendChild(overlay);
            setTimeout(() => {
                registerForm.dataset.ready = '1';
                registerForm.submit();
            }, 1800);
        });
        const resetSuccess = document.querySelector('[data-reset-success]');
        if (resetSuccess) {
            const link = resetSuccess.querySelector('a');
            modal({
                title: text('系統安全通知', 'Security Notice'),
                content: `<p>${text('加密重設憑證已成功建立，請繼續完成密碼更新。', 'An encrypted reset credential is ready. Continue to update your password.')}</p>`,
                actions: [{
                    label: text('開啟安全重設連結', 'Open Secure Reset Link'),
                    onClick: () => { if (link) location.href = link.href; }
                }]
            });
        }
    };

    const initStoreInfo = () => {
        const cards = [...document.querySelectorAll('.service-status-card')];
        const anyClosed = cards.some((card) => card.querySelector('.is-closed'));
        const pulse = document.querySelector('.store-pulse strong');
        if (anyClosed && pulse) {
            pulse.textContent = text('夜間省電模式', 'NIGHT ECO MODE');
            pulse.closest('.store-pulse').classList.add('is-eco');
        }
    };

    const initSafetyBanner = () => {
        const banner = document.querySelector('[data-safety-banner]');
        if (!banner) return;
        if (sessionStorage.getItem('staffless-safety-hidden') === '1') {
            banner.remove();
            return;
        }
        banner.querySelector('[data-safety-pause]')?.addEventListener('click', (event) => {
            banner.classList.toggle('is-paused');
            event.currentTarget.textContent = banner.classList.contains('is-paused') ? '▶' : 'Ⅱ';
        });
        banner.querySelector('[data-safety-close]')?.addEventListener('click', () => {
            sessionStorage.setItem('staffless-safety-hidden', '1');
            banner.remove();
        });
    };

    const initAdmin = () => {
        if (!isAdmin) return;
        if (file === 'index.php') {
            const stats = [...document.querySelectorAll('.stat-card')];
            const sales = stats.find((card) => /Total Sales|總營業額/.test(card.textContent));
            const orders = stats.find((card) => /Total Orders|訂單總數/.test(card.textContent));
            const lowStock = stats.find((card) => /Low Stock|低庫存/.test(card.textContent));
            if (sales && !sales.querySelector('.live-monitor')) {
                sales.insertAdjacentHTML('afterbegin', '<span class="live-monitor">● Live</span>');
            }
            lowStock?.addEventListener('click', () => {
                const progress = document.createElement('div');
                progress.className = 'restock-progress';
                progress.innerHTML = `<p>${text('偵測到低庫存貨架，是否啟動自動補貨？', 'Low-stock shelf detected. Start automated restock?')}</p><div><i></i></div>`;
                modal({
                    title: text('IoT 自動補貨指令', 'IoT Automated Restock'),
                    content: progress,
                    actions: [{
                        label: text('啟動自動補貨', 'Start Restock'),
                        onClick: (shell) => {
                            progress.classList.add('is-running');
                            setTimeout(() => {
                                const number = lowStock.querySelector('.stat-number');
                                if (number) number.textContent = '0';
                                lowStock.classList.remove('danger');
                                lowStock.classList.add('is-safe');
                                shell.closeModal();
                                toast(text('補貨流程完成，貨架恢復安全庫存。', 'Restock workflow complete.'));
                            }, 1600);
                        }
                    }]
                });
            });
        }

        if (file === 'analytics.php') {
            document.querySelectorAll('.status-chart-row i').forEach((bar) => {
                const width = bar.style.width;
                bar.style.setProperty('--target-width', width);
                bar.style.width = '0';
                requestAnimationFrame(() => bar.classList.add('is-charging'));
            });
            const insight = document.querySelector('.analytics-insight');
            if (insight) {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'btn btn-secondary btn-small';
                button.textContent = text('啟動 AI 深度經營分析', 'Generate AI Operations Report');
                insight.appendChild(button);
                button.addEventListener('click', () => {
                    const output = insight.querySelector('p');
                    const report = text(
                        '【AI 智慧分析報告】偵測到近期夜間點餐率上升 18%。熱銷商品目前庫存偏低，建議啟動補貨流程，並於明晚低溫時段派發「火辣夜限定」優惠券。',
                        '[AI OPERATIONS REPORT] Night orders rose 18%. A top-selling item is approaching low stock. Start a restock workflow and schedule the Fire Night offer for tomorrow evening.'
                    );
                    output.textContent = '';
                    button.disabled = true;
                    setTimeout(() => {
                        let index = 0;
                        const timer = setInterval(() => {
                            output.textContent += report[index++] || '';
                            if (index >= report.length) {
                                clearInterval(timer);
                                button.disabled = false;
                                speak('已為您生成本週智慧營運報告。', 'Your weekly operations report is ready.');
                            }
                        }, 18);
                    }, 700);
                });
            }
        }

        if (file === 'products.php') {
            const form = document.querySelector('.add-product-form form');
            if (form) {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'btn btn-secondary';
                button.textContent = text('帶入新品資料', 'Fill Product Data');
                form.querySelector('button[type="submit"]')?.after(button);
                button.addEventListener('click', () => {
                    const values = {code: 'N009', name: 'Ichiran Classic Ramen', brand: 'Ichiran', price: '7.50', stock: '80', description: '極致濃郁豚骨湯頭伴隨特製辣醬'};
                    Object.entries(values).forEach(([name, value]) => { if (form.elements[name]) form.elements[name].value = value; });
                });
            }
            document.querySelectorAll('[data-product-row]').forEach((row) => {
                row.querySelector('[data-inline-edit]')?.addEventListener('click', () => {
                    if (row.classList.contains('is-editing')) return;
                    row.classList.add('is-editing');
                    const priceCell = row.querySelector('[data-product-price]');
                    const stockCell = row.querySelector('[data-product-stock]');
                    const original = {price: priceCell.dataset.productPrice, stock: stockCell.dataset.productStock};
                    priceCell.innerHTML = `<input type="number" min="0" step="0.01" value="${original.price}">`;
                    stockCell.innerHTML = `<input type="number" min="0" value="${original.stock}">`;
                    const actions = row.querySelector('[data-row-actions]');
                    actions.innerHTML = `<button type="button" class="btn-small" data-save>儲存</button><button type="button" class="btn-small btn-danger" data-cancel>取消</button>`;
                    actions.querySelector('[data-cancel]').addEventListener('click', () => location.reload());
                    actions.querySelector('[data-save]').addEventListener('click', async () => {
                        try {
                            const price = Number(priceCell.querySelector('input').value);
                            const stock = Number(stockCell.querySelector('input').value);
                            await postApi('admin_product_update', {id: Number(row.dataset.productRow), price, stock});
                            priceCell.textContent = `$${price.toFixed(2)}`;
                            stockCell.textContent = String(stock);
                            row.classList.remove('is-editing');
                            row.classList.add('row-saved');
                            actions.innerHTML = `<button type="button" class="btn-small" onclick="location.reload()">完成</button>`;
                        } catch (error) { toast(error.message, 'error'); }
                    });
                });
                row.querySelector('[data-product-delete]')?.addEventListener('click', () => {
                    modal({
                        title: text('確認移除商品', 'Confirm Product Removal'),
                        content: `<p>${text('此操作會從資料庫刪除商品，確定繼續？', 'This removes the product from the database. Continue?')}</p>`,
                        actions: [{
                            label: text('確認刪除', 'Delete'),
                            className: 'btn btn-danger',
                            onClick: async (shell) => {
                                try {
                                    await postApi('admin_product_delete', {id: Number(row.dataset.productRow)});
                                    shell.closeModal();
                                    row.classList.add('row-dissolve');
                                    setTimeout(() => row.remove(), 600);
                                } catch (error) { toast(error.message, 'error'); }
                            }
                        }]
                    });
                });
            });
        }

        if (file === 'orders.php') {
            const table = document.querySelector('.data-table');
            const simulate = document.querySelector('[data-simulate-order]');
            simulate?.addEventListener('click', () => {
                const context = new (window.AudioContext || window.webkitAudioContext)();
                const oscillator = context.createOscillator();
                oscillator.frequency.value = 880;
                oscillator.connect(context.destination);
                oscillator.start();
                oscillator.stop(context.currentTime + 0.1);
                const row = document.createElement('tr');
                row.className = 'row-injected';
                row.innerHTML = `<td><strong>LIVE-${Date.now().toString().slice(-6)}</strong></td><td>walk_in_guest<br><small>kiosk@staffless-ramen.com</small></td><td>${new Date().toLocaleTimeString()}</td><td>2</td><td>$12.44</td><td><span class="status-badge status-paid">Paid</span></td><td><span class="status-badge status-preparing">機器人烹飪中</span></td><td><code>PICK2026</code></td><td>Preview</td>`;
                table.querySelector('tbody')?.prepend(row);
            });
            document.querySelectorAll('[data-order-status-form]').forEach((form) => {
                const select = form.querySelector('select');
                select.addEventListener('change', async (event) => {
                    event.preventDefault();
                    const status = select.value;
                    const row = form.closest('tr');
                    const content = document.createElement('div');
                    content.className = 'dispense-progress is-running';
                    content.innerHTML = `<p>${text('正在傳送出餐協定至智慧取餐櫃...', 'Sending dispense protocol to the smart locker...')}</p><div><i></i></div>`;
                    const shell = modal({title: text('無人店硬體連線中', 'Connecting Store Hardware'), content});
                    try {
                        await new Promise((resolve) => setTimeout(resolve, 1500));
                        await postApi('admin_order_status', {id: Number(form.dataset.orderStatusForm), status});
                        const badge = row.querySelector('[data-order-status-badge]');
                        badge.className = `status-badge status-${status}`;
                        badge.textContent = status;
                        content.innerHTML = `<p class="success">${text('出餐協定同步成功，取餐密鑰已更新。', 'Dispense protocol synchronized and pickup key updated.')}</p>`;
                        setTimeout(() => shell.closeModal(), 900);
                    } catch (error) {
                        shell.closeModal();
                        toast(error.message, 'error');
                    }
                });
            });
        }

        if (file === 'users.php') {
            const tooltip = document.createElement('div');
            tooltip.className = 'user-tracker-tooltip';
            document.body.appendChild(tooltip);
            document.querySelectorAll('[data-user-track]').forEach((name) => {
                name.addEventListener('mouseenter', () => {
                    tooltip.innerHTML = `<strong>${name.textContent}</strong><span>● ${text('機台狀態：在線（A-01）', 'Kiosk: online (A-01)')}</span><span>● ${text('購物車：三養辣雞麵 + 起司片', 'Cart: Buldak + cheese')}</span>`;
                    const rect = name.getBoundingClientRect();
                    tooltip.style.left = `${rect.left}px`;
                    tooltip.style.top = `${rect.bottom + 8}px`;
                    tooltip.classList.add('is-visible');
                });
                name.addEventListener('mouseleave', () => tooltip.classList.remove('is-visible'));
            });
            document.querySelectorAll('[data-user-delete]').forEach((button) => button.addEventListener('click', () => {
                const row = button.closest('tr');
                modal({
                    title: text('權限防禦警告', 'Access Defense Warning'),
                    content: `<p>${text('確定取消此使用者的數位通行權限？', 'Remove this user digital access?')}</p>`,
                    actions: [{
                        label: text('取消權限', 'Remove Access'),
                        className: 'btn btn-danger',
                        onClick: async (shell) => {
                            try {
                                await postApi('admin_user_delete', {id: Number(button.dataset.userDelete)});
                                shell.closeModal();
                                row.classList.add('user-glitch-out');
                                setTimeout(() => row.remove(), 700);
                            } catch (error) { toast(error.message, 'error'); }
                        }
                    }]
                });
            }));
        }

        if (file === 'payments.php') {
            document.querySelector('[data-simulate-payment]')?.addEventListener('click', () => {
                const body = document.querySelector('.data-table tbody');
                const row = document.createElement('tr');
                row.className = 'row-injected';
                row.innerHTML = `<td><button class="transaction-link" data-transaction="TXN-LIVE">TXN-LIVE</button></td><td>ORD-LIVE</td><td>kiosk_guest</td><td>$12.44</td><td>Visa</td><td><span class="status-badge status-success">Success</span></td><td>${new Date().toLocaleString()}</td>`;
                body?.prepend(row);
                toast(text('收到一筆新付款！金額：$12.44（Visa）', 'New payment received: $12.44 (Visa)'));
                bindTransactions(row);
            });
            const bindTransactions = (root = document) => root.querySelectorAll('[data-transaction]').forEach((button) => button.addEventListener('click', () => {
                const row = button.closest('tr');
                const cells = row.querySelectorAll('td');
                const token = button.dataset.transaction;
                const content = document.createElement('div');
                content.className = 'cyber-receipt';
                content.innerHTML = `<p>TRANSACTION: ${token}</p><p>ORDER: ${cells[1]?.textContent}</p><p>AMOUNT: ${cells[3]?.textContent}</p><p>GATEWAY: TLS 1.3</p><p>SIGNATURE: VERIFIED</p>`;
                content.appendChild(createBarcode(token));
                modal({title: text('加密電子發票憑證', 'Encrypted Payment Receipt'), content});
            }));
            bindTransactions();
        }

        if (file === 'profile.php') {
            const info = document.querySelector('.profile-info');
            info?.insertAdjacentHTML('beforeend', `<section class="security-panel"><strong>${text('系統安全級別', 'System Security Level')}</strong><p>${text('權限等級：Root 最高權限者', 'Access: Root Administrator')}</p><p class="security-live">${text('安全協定：Level 5 矩陣防禦', 'Protocol: Level 5 Matrix Defense')}</p><p>${text('金鑰每 24 小時自動重構', 'Keys rotate every 24 hours')}</p></section>`);
            const form = document.querySelector('.change-password form');
            form?.addEventListener('submit', (event) => {
                if (form.dataset.ready === '1') return;
                event.preventDefault();
                form.closest('.change-password').classList.add('key-rewrite');
                const button = form.querySelector('button[type="submit"]');
                button.disabled = true;
                button.textContent = text('正在重新寫入核心密鑰...', 'Rewriting core key...');
                setTimeout(() => {
                    form.dataset.ready = '1';
                    form.submit();
                }, 1500);
            });
        }
    };

    const initCommerceHeader = () => {
        const header = document.querySelector('[data-commerce-header]');
        if (!header) return;

        // AI 修改：新增可關閉活動列與智慧點餐 Mega Menu，對應 Word 版面參考。
        const announcement = header.querySelector('[data-commerce-announcement]');
        const announcementKey = 'staffless-commerce-announcement-hidden';
        if (announcement && localStorage.getItem(announcementKey) === '1') {
            announcement.hidden = true;
        }
        announcement?.querySelector('[data-close-announcement]')?.addEventListener('click', () => {
            localStorage.setItem(announcementKey, '1');
            announcement.hidden = true;
        });

        const trigger = header.querySelector('[data-mega-trigger]');
        const menu = header.querySelector('[data-mega-menu]');
        if (!trigger || !menu) return;

        let closeTimer = 0;
        const openMenu = () => {
            window.clearTimeout(closeTimer);
            menu.classList.add('is-open');
            trigger.classList.add('is-open');
            trigger.setAttribute('aria-expanded', 'true');
        };
        const closeMenu = () => {
            menu.classList.remove('is-open');
            trigger.classList.remove('is-open');
            trigger.setAttribute('aria-expanded', 'false');
        };
        const queueClose = () => {
            window.clearTimeout(closeTimer);
            closeTimer = window.setTimeout(closeMenu, 180);
        };

        trigger.addEventListener('mouseenter', openMenu);
        trigger.addEventListener('focus', openMenu);
        trigger.addEventListener('click', (event) => {
            event.preventDefault();
            if (menu.classList.contains('is-open')) closeMenu();
            else openMenu();
        });
        header.addEventListener('mouseleave', queueClose);
        menu.addEventListener('mouseenter', openMenu);
        menu.addEventListener('mouseleave', queueClose);
        document.addEventListener('click', (event) => {
            if (!header.contains(event.target)) closeMenu();
        });
        window.addEventListener('scroll', closeMenu, {passive: true});
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') closeMenu();
        });
    };

    const initFooter = () => {
        const footer = document.querySelector('[data-site-footer]');
        if (!footer) return;
        const reveal = () => footer.classList.add('show-footer');
        if ('IntersectionObserver' in window && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        reveal();
                        observer.disconnect();
                    }
                });
            }, {threshold: 0.18});
            observer.observe(footer);
        } else {
            reveal();
        }

        const form = footer.querySelector('[data-footer-subscribe]');
        form?.addEventListener('submit', (event) => {
            event.preventDefault();
            const email = form.querySelector('input[type="email"]')?.value.trim();
            if (!email) return;
            localStorage.setItem('staffless-footer-alert-email', email);
            toast(text('已加入通知名單。', 'Added to the alert list.'));
            form.reset();
        });
    };

    const initHomePromoModal = () => {
        if (file !== 'index.php') return;
        const key = 'staffless-home-promo-seen-at';
        const last = Number(localStorage.getItem(key) || 0);
        if (Date.now() - last < 24 * 60 * 60 * 1000) return;
        window.setTimeout(() => {
            const content = `
                <div class="home-promo-modal">
                    <span class="status-pill is-live">${text('未來泡麵節上線中', 'Future Noodle Fest is live')}</span>
                    <p>${text('深夜加料優惠、滿額點數加倍與新客點數已整合到活動與點數頁。', 'Late-night topping deals, threshold point bonuses and new-member points are connected to Deals and Rewards.')}</p>
                    <div class="promo-modal-grid">
                        <span>${text('滿額折抵', 'Spend discount')}</span>
                        <span>${text('指定品項優惠', 'Item bundle')}</span>
                        <span>${text('會員點數兌換', 'Reward exchange')}</span>
                    </div>
                </div>`;
            const shell = modal({
                title: text('智慧無人店本週快閃活動', 'This Week in the Smart Store'),
                content,
                className: 'home-entry-modal',
                actions: [
                    {label: text('查看活動優惠', 'View deals'), className: 'btn btn-primary', onClick: () => { location.href = 'promotions.php'; }},
                    {label: text('前往點餐', 'Start order'), className: 'btn btn-success', onClick: () => { location.href = 'dashboard.php'; }},
                    {label: text('稍後再看', 'Later'), className: 'btn btn-secondary', onClick: (node) => node.closeModal?.()}
                ]
            });
            localStorage.setItem(key, String(Date.now()));
            const closeOnEsc = (event) => {
                if (event.key === 'Escape') {
                    shell.closeModal?.();
                    document.removeEventListener('keydown', closeOnEsc);
                }
            };
            document.addEventListener('keydown', closeOnEsc);
        }, window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 0 : 850);
    };

    const initLocationCommand = () => {
        const shell = document.querySelector('[data-location-command]');
        if (!shell) return;
        const iframe = shell.querySelector('[data-location-map]');
        const title = shell.querySelector('[data-selected-location]');
        const loading = shell.querySelector('[data-map-loading]');
        const switchMap = (query, name) => {
            if (!iframe || !query) return;
            shell.querySelectorAll('[data-map-query]').forEach((card) => card.classList.toggle('is-active', card.dataset.locationName === name));
            title.textContent = name;
            loading.textContent = text('地圖資料同步中...', 'Syncing map data...');
            iframe.src = `https://www.google.com/maps?q=${encodeURIComponent(query)}&output=embed`;
            window.setTimeout(() => {
                loading.textContent = text('地圖資料已同步。', 'Map data synced.');
            }, 650);
        };
        shell.querySelectorAll('[data-map-query]').forEach((card) => {
            card.addEventListener('click', () => switchMap(card.dataset.mapQuery, card.dataset.locationName));
        });
        shell.querySelector('[data-hq-query]')?.addEventListener('click', (event) => {
            const button = event.currentTarget;
            shell.querySelectorAll('[data-map-query]').forEach((card) => card.classList.remove('is-active'));
            switchMap(button.dataset.hqQuery, button.dataset.hqName);
        });
    };

    const boot = () => {
        document.body.dataset.loggedIn = document.body.dataset.loggedIn || (document.querySelector('a[href*="logout.php"]') ? '1' : '0');
        initFaq();
        initSafetyBanner();
        initCommerceHeader();
        initFooter();
        initHomePromoModal();
        initLocationCommand();
        if (file === 'dashboard.php') initDashboard();
        if (file === 'promotions.php') initPromotions();
        if (file === 'profile.php' && !isAdmin) initProfile();
        if (file === 'order-history.php') initOrderHistory();
        if (file === 'partners.php') initPartners();
        if (file === 'member-center.php') initMemberCenter();
        if (file === 'rewards.php') initRewards();
        if (['login.php', 'forgot-password.php', 'register.php'].includes(file)) initAuth();
        if (file === 'store-info.php') initStoreInfo();
        initAdmin();
    };
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot);
    else boot();
})();
