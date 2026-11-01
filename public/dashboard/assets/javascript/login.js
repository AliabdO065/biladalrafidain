document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('auth-login-form');
    const password = document.getElementById('password');
    const toggle = document.getElementById('auth-password-toggle');
    if (!form || !password || !toggle) return;

    toggle.addEventListener('click', () => {
        const visible = password.type === 'password';
        password.type = visible ? 'text' : 'password';
        toggle.setAttribute('aria-pressed', String(visible));
        toggle.setAttribute('aria-label', visible ? toggle.dataset.hideLabel : toggle.dataset.showLabel);
        toggle.title = toggle.getAttribute('aria-label');
        toggle.querySelector('[data-eye]').hidden = visible;
        toggle.querySelector('[data-eye-off]').hidden = !visible;
    });

    const submit = form.querySelector('[type="submit"]');
    const label = submit.querySelector('[data-submit-label]');
    const spinner = submit.querySelector('[data-submit-spinner]');
    const arrow = submit.querySelector('[data-submit-arrow]');
    const restore = () => {
        submit.disabled = false;
        label.textContent = form.dataset.readyLabel;
        spinner.hidden = true;
        arrow.hidden = false;
        form.removeAttribute('aria-busy');
    };
    form.addEventListener('submit', () => {
        submit.disabled = true;
        label.textContent = form.dataset.loadingLabel;
        spinner.hidden = false;
        arrow.hidden = true;
        form.setAttribute('aria-busy', 'true');
    });
    window.addEventListener('pageshow', restore);
});
