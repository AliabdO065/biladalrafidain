(() => {
    const boot = () => {
        const arabic = document.documentElement.lang === 'ar';
        const escape = value => String(value ?? '').replace(/[&<>"']/g, character => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[character]));
        const decimal = (value, money = false) => {
            if (value === null || value === '') return '—';
            const match = String(value).match(/^(-?)(\d+)(?:\.(\d+))?$/);
            if (!match) return escape(value);
            const whole = match[2].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            const fraction = money ? (match[3] || '').padEnd(2, '0').slice(0, 2) : (match[3] || '').replace(/0+$/, '');
            return match[1] + whole + (fraction ? '.' + fraction : '');
        };
        const numbers = ['id','shipments_count','shipment_count','packages_count','packages_total','weight_kg','weight_total','item_price','shipping_fee','paid_amount','balance','total'];
        const money = ['shipping_fee','paid_amount','balance','total'];
        const render = key => (data, type, row) => {
            if (type !== 'display') return data;
            const text = data === null || data === '' || data === undefined ? '—' : String(data);
            if (key === 'actions') return data || '';
            if (key === 'status') {
                const codes = ['received','customs','in_transit','out_for_delivery','delivered','on_hold'];
                const code = codes.includes(row.status_code) ? row.status_code : 'received';
                return '<span class="ops-status ops-status--' + code + '"><span></span>' + escape(text) + '</span>';
            }
            if (key === 'reference' || key === 'trip_reference') {
                const content = text === '—' ? '—' : '<span class="ops-trip-tag"><i class="oi oi-map" aria-hidden="true"></i><bdi>#' + escape(text) + '</bdi></span>';
                const url = key === 'reference' ? row.view_url : row.trip_url;
                return url ? '<a class="ops-reference-link" href="' + escape(url) + '">' + content + '</a>' : content;
            }
            if (key === 'tracking_code') return '<span class="ops-tracking-code" dir="ltr">' + escape(text) + '</span>';
            if (key === 'receiver_name') {
                const initial = Array.from(text.trim())[0] || '—';
                return '<span class="ops-person"><span class="ops-person-avatar" aria-hidden="true">' + escape(initial) + '</span><span class="ops-person-name">' + escape(text) + '</span></span>';
            }
            if (key === 'receiver_phone') return '<bdi class="ops-phone">' + escape(text) + '</bdi>';
            if (key === 'currency') return '<span class="ops-currency">' + escape(text) + '</span>';
            if (numbers.includes(key)) {
                const classes = ['shipments_count','shipment_count','packages_count','packages_total'].includes(key) ? 'ops-count-value' : money.includes(key) ? 'ops-money' : 'ops-number';
                const credit = key === 'balance' && String(data).startsWith('-') ? ' ops-money-credit' : '';
                const warning = key === 'balance' && row.incomplete ? '<span class="ops-amount-warning" title="' + (arabic ? 'بيانات مالية ناقصة' : 'Incomplete amounts') + '"><i class="oi oi-warning" aria-hidden="true"></i></span>' : '';
                return '<span class="' + classes + credit + '" dir="ltr">' + decimal(data, money.includes(key)) + '</span>' + warning;
            }
            return escape(text);
        };

        document.querySelectorAll('table[data-operations-table]').forEach(table => {
            const card = table.closest('.ops-grid') || table.parentElement;
            const errorBox = document.getElementById(table.dataset.error);
            const filter = document.getElementById(table.dataset.filter);
            if (typeof window.DataTable === 'undefined') {
                if (errorBox) errorBox.hidden = false;
                return;
            }
            DataTable.ext.errMode = 'none';
            const keys = JSON.parse(table.dataset.fieldKeys);
            const basic = table.dataset.basicColumns ? JSON.parse(table.dataset.basicColumns) : keys;
            const detailIndexes = keys.map((key, index) => basic.includes(key) ? -1 : index).filter(index => index !== -1);
            const extras = table.dataset.extraFilters ? JSON.parse(table.dataset.extraFilters) : {};
            const columns = keys.map(key => ({
                data:key, defaultContent:'—', visible:basic.includes(key), orderable:key !== 'actions', searchable:key !== 'actions',
                render:render(key), className:key === 'actions' ? 'ops-actions-cell' : numbers.includes(key) ? 'ops-numeric-cell' : '',
                createdCell:cell => cell.dataset.cell = key
            }));
            $(table).on('dt-error.dt', () => { if (errorBox) errorBox.hidden = false; card.setAttribute('aria-busy','false'); });
            $(table).on('preXhr.dt', () => card.setAttribute('aria-busy','true'));
            $(table).on('xhr.dt', (_event, _settings, json) => {
                if (errorBox) errorBox.hidden = !!json;
                card.setAttribute('aria-busy','false');
                if (json) document.querySelectorAll('[data-table-total]').forEach(badge => {
                    if (badge.dataset.tableTotal === table.id) badge.textContent = decimal(json.recordsFiltered);
                });
            });
            const instance = new DataTable(table, {
                processing:true, serverSide:true, searchDelay:300, scrollX:true, autoWidth:false,
                pageLength:25, lengthMenu:[10,25,50,100],
                order:[[Number(table.dataset.defaultOrder || 0),table.dataset.defaultDirection || 'desc']],
                search:{search:table.dataset.initialSearch || ''},
                layout:{topStart:null,topEnd:null,bottomStart:['pageLength','info'],bottomEnd:'paging'},
                columns,
                ajax:{url:table.dataset.url,data:data => {
                    Object.assign(data,extras);
                    if (filter && filter.value) data[table.dataset.filterParam || 'trip_id'] = filter.value;
                }},
                language:arabic ? {
                    lengthMenu:'عرض _MENU_',info:'_START_–_END_ من _TOTAL_ سجل',infoEmpty:'0 سجل',infoFiltered:'(من _MAX_)',
                    emptyTable:table.dataset.emptyMessage || 'لا توجد بيانات بعد. ابدأ بإضافة سجل جديد.',zeroRecords:'لا توجد نتائج مطابقة لبحثك',
                    processing:'جارٍ التحميل…',loadingRecords:'جارٍ التحميل…',
                    paginate:{first:'الأول',previous:'السابق',next:'التالي',last:'الأخير'}
                } : {
                    lengthMenu:'Show _MENU_',info:'_START_–_END_ of _TOTAL_ records',infoEmpty:'0 records',
                    emptyTable:table.dataset.emptyMessage || 'No records yet. Add your first record.'
                }
            });
            const fallback = card.querySelector('.ops-grid-fallback-pagination');
            if (fallback) fallback.hidden = true;
            if (filter) filter.addEventListener('change', () => instance.ajax.reload());
            document.querySelectorAll('[data-table-search]').forEach(input => {
                if (input.dataset.tableSearch !== table.id) return;
                let timer;
                input.addEventListener('input', () => {
                    clearTimeout(timer);
                    timer = setTimeout(() => instance.search(input.value).draw(),300);
                });
                input.addEventListener('keydown',event => {
                    if (event.key === 'Enter') { event.preventDefault(); clearTimeout(timer); instance.search(input.value).draw(); }
                });
            });
            document.querySelectorAll('[data-table-details]').forEach(button => {
                if (button.dataset.tableDetails !== table.id) return;
                button.addEventListener('click', () => {
                    const expanded = button.getAttribute('aria-pressed') !== 'true';
                    instance.columns(detailIndexes).visible(expanded,false);
                    instance.columns.adjust().draw(false);
                    button.setAttribute('aria-pressed',String(expanded));
                    const label = expanded ? button.dataset.hideLabel : button.dataset.showLabel;
                    button.querySelector('span').textContent = label;
                    button.setAttribute('aria-label',label);
                });
            });
        });
        const modal = document.querySelector('.operations-modal[data-reopen="1"]');
        if (modal) bootstrap.Modal.getOrCreateInstance(modal).show();
    };
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded',boot,{once:true});
    else boot();
})();
