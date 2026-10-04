<script>
document.addEventListener('DOMContentLoaded', function () {
    // Confirm destructive actions consistently.
    document.addEventListener('click', function (event) {
        const button = event.target.closest('[data-confirm]');
        if (!button) return;
        const message = button.getAttribute('data-confirm') || 'Вы уверены?';
        if (!window.confirm(message)) {
            event.preventDefault();
            event.stopImmediatePropagation();
        }
    }, true);

    // Prevent accidental double submission for ordinary forms.
    document.addEventListener('submit', function (event) {
        const form = event.target;
        if (!(form instanceof HTMLFormElement)) return;
        if (event.defaultPrevented) return;
        if (form.hasAttribute('data-ajax-form')) return;
        if (form.dataset.submitting === '1') {
            event.preventDefault();
            return;
        }
        if (!form.checkValidity()) {
            return;
        }
        form.dataset.submitting = '1';
        const buttons = form.querySelectorAll('[type="submit"]');
        buttons.forEach(button => {
            button.dataset.originalText = button.innerHTML;
            button.disabled = true;
        });
    });
});
</script>
