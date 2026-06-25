// AI 修改：客製化拉麵即時計價、風味強度與配料預覽
document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('[data-ramen-customizer]');
    if (!form) return;

    const bowl = document.querySelector('[data-ramen-bowl]');
    const total = document.querySelector('[data-custom-total]');
    const code = document.querySelector('[data-custom-code]');
    const level = document.querySelector('[data-flavor-level]');
    const meter = document.querySelector('[data-flavor-meter]');
    const prepTime = document.querySelector('[data-prep-time]');

    const selectedPrice = (selector) => {
        const field = form.querySelector(selector);
        if (!field) return 0;
        if (field.tagName === 'SELECT') {
            return Number(field.selectedOptions[0]?.dataset.price || 0);
        }
        return Number(field.dataset.price || 0);
    };

    const update = () => {
        const base = form.querySelector('[data-base-noodle]');
        const baseOption = base.selectedOptions[0];
        const quantity = Number(form.querySelector('[data-custom-quantity]').value || 1);
        let unitPrice = Number(baseOption.dataset.price || 0);

        form.querySelectorAll('select[data-custom-price]').forEach((select) => {
            unitPrice += Number(select.selectedOptions[0]?.dataset.price || 0);
        });
        form.querySelectorAll('input[type="radio"]:checked, input[type="checkbox"]:checked').forEach((input) => {
            unitPrice += Number(input.dataset.price || 0);
        });

        const spice = Number(form.querySelector('input[name="spice"]:checked')?.value || 0);
        const toppingCount = form.querySelectorAll('input[name="toppings[]"]:checked').length;
        const intensity = Math.min(100, 32 + spice * 13 + toppingCount * 6);
        const seconds = 180 + toppingCount * 12 + (form.elements.size.value === 'large' ? 25 : 0);

        total.textContent = window.stafflessFormatMoney
            ? window.stafflessFormatMoney(unitPrice * quantity)
            : `$${(unitPrice * quantity).toFixed(2)}`;
        code.textContent = baseOption.dataset.code;
        level.textContent = `${intensity}%`;
        meter.style.width = `${intensity}%`;
        prepTime.textContent = `${String(Math.floor(seconds / 60)).padStart(2, '0')}:${String(seconds % 60).padStart(2, '0')}`;

        bowl.dataset.broth = form.elements.broth.value;
        bowl.dataset.spice = String(spice);
        ['egg', 'corn', 'seaweed', 'chashu', 'scallion'].forEach((topping) => {
            bowl.classList.toggle(`has-${topping}`, Boolean(form.querySelector(`input[value="${topping}"]:checked`)));
        });
    };

    form.addEventListener('change', update);
    form.addEventListener('input', update);
    document.addEventListener('staffless:currencychange', update);
    update();
});
