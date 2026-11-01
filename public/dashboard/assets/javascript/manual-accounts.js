(() => {
    'use strict';
    const key = 'manual-accounts:' + document.currentScript.dataset.manualAccountKey;
    let done;
    try { const saved = JSON.parse(localStorage.getItem(key) || '[]'); done = new Set(Array.isArray(saved) ? saved : []); }
    catch (_) { done = new Set(); }
    const rows = [...document.querySelectorAll('[data-manual-row]')].filter(row => row.querySelector('[data-manual-open]'));
    const next = document.querySelector('[data-manual-next]');
    const complete = document.querySelector('[data-manual-complete]');
    if (!next) return;
    const refresh = () => {
        const remaining = rows.some(row => !row.querySelector('[data-manual-done]').checked);
        next.disabled = !remaining;
        complete.hidden = remaining;
    };
    rows.forEach(row => {
        const checkbox = row.querySelector('[data-manual-done]');
        checkbox.checked = done.has(row.dataset.manualRow);
        checkbox.disabled = !checkbox.checked;
        row.querySelector('[data-manual-open]').addEventListener('click', () => {
            // Opening the app never counts as sending; the operator must confirm.
            checkbox.disabled = false;
        });
        checkbox.addEventListener('change', () => {
            if (checkbox.checked) done.add(row.dataset.manualRow);
            else done.delete(row.dataset.manualRow);
            try { localStorage.setItem(key, JSON.stringify([...done])); } catch (_) { /* Browser storage may be unavailable. */ }
            refresh();
        });
    });
    next.addEventListener('click', () => {
        const row = rows.find(item => !item.querySelector('[data-manual-done]').checked);
        if (!row) return;
        row.scrollIntoView({ block: 'center', behavior: matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth' });
        row.querySelector('[data-manual-open]').click();
    });
    refresh();
})();
