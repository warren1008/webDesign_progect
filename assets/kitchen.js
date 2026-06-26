
document.addEventListener('DOMContentLoaded', () => {
    const shell = document.querySelector('[data-kitchen-status]');
    if (!shell) return;

    const duration = Number(shell.dataset.duration || 28);
    const serverElapsed = Number(shell.dataset.elapsed || 0);
    const forcedReady = ['ready', 'completed'].includes(shell.dataset.orderStatus);
    const startedAt = Date.now() - serverElapsed * 1000;
    const countdown = shell.querySelector('[data-countdown]');
    const progress = shell.querySelector('[data-prep-progress]');
    const state = shell.querySelector('[data-kitchen-state]');
    const message = shell.querySelector('[data-kitchen-message]');
    const locker = shell.querySelector('[data-locker-door]');
    const lockerStatus = shell.querySelector('[data-locker-status]');
    const stages = [...shell.querySelectorAll('[data-stage]')];

    const localized = (en, zh) => (localStorage.getItem('staffless-language') === 'zh-TW' ? zh : en);

    const render = () => {
        const elapsed = forcedReady ? duration : Math.floor((Date.now() - startedAt) / 1000);
        const ratio = Math.min(1, elapsed / duration);
        const remaining = Math.max(0, duration - elapsed);
        const currentStage = Math.min(3, Math.floor(ratio * 4));

        countdown.textContent = `00:${String(remaining).padStart(2, '0')}`;
        progress.style.width = `${ratio * 100}%`;
        stages.forEach((stage, index) => {
            stage.classList.toggle('is-active', index <= currentStage);
        });

        if (ratio >= 1) {
            state.textContent = localized('Ready for pickup', '可以取餐');
            message.textContent = localized(
                'Preparation is complete. Scan your pickup code at the illuminated locker.',
                '製作完成，請在亮起的取餐櫃掃描取餐碼。'
            );
            locker.classList.add('is-unlocked');
            lockerStatus.textContent = localized('UNLOCKED', '已解鎖');
            countdown.textContent = 'READY';
        } else if (ratio >= 0.66) {
            state.textContent = localized('Flavor assembly', '組合風味');
            message.textContent = localized(
                'Broth, noodles and toppings are being assembled.',
                '正在組合湯底、麵體與配料。'
            );
        } else if (ratio >= 0.33) {
            state.textContent = localized('Heating water', '加熱注水');
            message.textContent = localized(
                'The machine is controlling water temperature and timing.',
                '機台正在控制水溫與時間。'
            );
        }
    };

    render();
    const timer = setInterval(() => {
        render();
        if (shell.querySelector('[data-locker-door]').classList.contains('is-unlocked')) {
            clearInterval(timer);
        }
    }, 1000);
});
