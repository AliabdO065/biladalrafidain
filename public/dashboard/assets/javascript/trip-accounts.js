document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('trip-account-form');
    if (!form) return;
    form.addEventListener('submit', () => {
        const recipients = {};
        form.querySelectorAll('[data-trip-recipient]').forEach(row => {
            const values = {};
            row.querySelectorAll('[data-recipient-field]').forEach(input => {
                values[input.dataset.recipientField] = input.value;
                input.removeAttribute('name');
            });
            recipients[row.dataset.tripRecipient] = values;
        });
        // One JSON field avoids PHP's max_input_vars truncating large trips.
        let payload = form.querySelector('input[name="recipients"]');
        if (!payload) {
            payload = document.createElement('input');
            payload.type = 'hidden';
            payload.name = 'recipients';
            form.append(payload);
        }
        payload.value = JSON.stringify(recipients);
    });
});
