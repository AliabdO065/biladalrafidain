(() => {
    'use strict';
    const guide = document.querySelector('[data-guide]');
    if (!guide) return;
    const input = guide.querySelector('#guide-search');
    const clear = guide.querySelector('[data-guide-clear]');
    const count = guide.querySelector('[data-guide-count]');
    const empty = guide.querySelector('[data-guide-empty]');
    const results = guide.querySelector('[data-guide-results]');
    const normalize = value => value.toLowerCase().normalize('NFKD')
        .replace(/[\u0300-\u036f\u064b-\u065f\u0670\u0640]/g, '')
        .replace(/[أإآٱ]/g, 'ا').replace(/ى/g, 'ي').replace(/ة/g, 'ه');
    const chapters = Array.from(guide.querySelectorAll('[data-guide-section]')).map(section => ({
        section,
        text: normalize(section.textContent),
        link: guide.querySelector(`[data-guide-link="${section.dataset.guideSection}"]`),
    }));
    const fromHash = () => chapters.find(({ section }) => `#${section.id}` === location.hash);
    let selected = fromHash() || chapters[0];
    const filter = () => {
        const terms = normalize(input.value).trim().split(/\s+/).filter(Boolean);
        const searching = terms.length > 0;
        let matches = 0;
        chapters.forEach(chapter => {
            const { section, text, link } = chapter;
            const matchesSearch = terms.every(term => text.includes(term));
            const visible = searching ? matchesSearch : chapter === selected;
            section.hidden = !visible;
            link.hidden = searching && !matchesSearch;
            if (!searching && chapter === selected) link.setAttribute('aria-current', 'location');
            else link.removeAttribute('aria-current');
            if (matchesSearch) matches++;
            section.querySelectorAll('details').forEach(detail => {
                detail.open = searching && visible;
            });
        });
        count.textContent = String(matches);
        results.hidden = !searching;
        empty.hidden = !searching || matches > 0;
        clear.hidden = input.value.length === 0;
    };
    const showSelection = (moveFocus = false) => {
        input.value = '';
        filter();
        if (moveFocus && selected) {
            selected.section.querySelector('h2').focus({ preventScroll: true });
            selected.section.scrollIntoView({ block: 'start', behavior: matchMedia('(prefers-reduced-motion: reduce)').matches ? 'instant' : 'smooth' });
        }
    };
    chapters.forEach(chapter => chapter.link.addEventListener('click', event => {
        if (event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
        event.preventDefault();
        selected = chapter;
        if (location.hash !== `#${chapter.section.id}`) history.pushState(null, '', `#${chapter.section.id}`);
        showSelection(true);
    }));
    window.addEventListener('hashchange', () => {
        selected = fromHash() || chapters[0];
        showSelection(true);
    });
    input.addEventListener('input', filter);
    clear.addEventListener('click', () => { input.value = ''; filter(); input.focus(); });
    input.addEventListener('keydown', event => {
        if (event.key === 'Escape') { input.value = ''; filter(); }
    });
    filter();
})();
