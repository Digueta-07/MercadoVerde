document.addEventListener('click', function (event) {
    const stepButton = event.target.closest('[data-quantity-step]');
    if (stepButton) {
        const control = stepButton.closest('.quantity-control');
        const input = control ? control.querySelector('input[type="number"]') : null;
        if (input) {
            const step = Number(stepButton.dataset.quantityStep || 0);
            const min = Number(input.min || 0);
            const max = Number(input.max || 9999);
            input.value = Math.max(min, Math.min(max, Number(input.value || 0) + step));
        }
    }
});

document.addEventListener('submit', function (event) {
    const form = event.target;
    const submitter = event.submitter;
    const message = submitter && submitter.dataset.confirm;
    if (message && !window.confirm(message)) event.preventDefault();
});
