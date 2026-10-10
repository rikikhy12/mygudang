@php
    $canCreateContact = auth()->check() && (auth()->user()->hasRole('admin') || auth()->user()->hasPermission('create_master_contacts'));
    $canCreateItem = auth()->check() && (auth()->user()->hasRole('admin') || auth()->user()->hasPermission('create_master_items'));
    $canCreateCoa = auth()->check() && (auth()->user()->hasRole('admin') || auth()->user()->hasPermission('create_master_coa'));
@endphp

<!-- ================= JAVASCRIPT QUICK CREATION VIA WINDOW OPENER ================= -->
<script>
    let currentContactTargetSelect = null;
    let currentContactSuccessCallback = null;
    let currentItemTargetCallback = null;
    let currentCoaTargetSelect = null;
    let currentCoaSuccessCallback = null;

    /**
     * Buka Halaman Tambah Supplier / Customer / Kontak Secara Window Opener Popup
     */
    window.openQuickContactModal = function(type = 'supplier', targetSelectSelector = null, callback = null) {
        @if(!$canCreateContact)
            alert('Anda tidak memiliki izin akses untuk menambah data Kontak / Supplier / Customer.');
            return;
        @else
            currentContactTargetSelect = targetSelectSelector;
            currentContactSuccessCallback = callback;

            const w = 920;
            const h = 760;
            const left = Math.max(0, (window.screen.width - w) / 2);
            const top = Math.max(0, (window.screen.height - h) / 2);
            const url = "{{ route('contacts.create') }}?type=" + encodeURIComponent(type || 'supplier') + "&popup=1";
            
            const win = window.open(url, "AddContactWindow", `width=${w},height=${h},top=${top},left=${left},scrollbars=yes,resizable=yes,status=no,location=no,toolbar=no,menubar=no`);
            if (win) {
                win.focus();
            } else {
                alert('Popup diblokir oleh browser. Mohon izinkan popup untuk situs ini.');
            }
        @endif
    };

    /**
     * Buka Halaman Tambah Master Barang / Jasa Secara Window Opener Popup
     */
    window.openQuickItemModal = function(targetCallbackOrSelect = null) {
        @if(!$canCreateItem)
            alert('Anda tidak memiliki izin akses untuk menambah data Master Barang.');
            return;
        @else
            currentItemTargetCallback = targetCallbackOrSelect;

            const w = 1120;
            const h = 820;
            const left = Math.max(0, (window.screen.width - w) / 2);
            const top = Math.max(0, (window.screen.height - h) / 2);
            const url = "{{ route('items.create') }}?popup=1";
            
            const win = window.open(url, "AddItemWindow", `width=${w},height=${h},top=${top},left=${left},scrollbars=yes,resizable=yes,status=no,location=no,toolbar=no,menubar=no`);
            if (win) {
                win.focus();
            } else {
                alert('Popup diblokir oleh browser. Mohon izinkan popup untuk situs ini.');
            }
        @endif
    };

    /**
     * Buka Halaman Tambah Master Bagan Akun COA Secara Window Opener Popup
     */
    window.openQuickCoaModal = function(targetSelectSelector = null, callback = null) {
        @if(!$canCreateCoa)
            alert('Anda tidak memiliki izin akses untuk menambah data Akun COA.');
            return;
        @else
            currentCoaTargetSelect = targetSelectSelector;
            currentCoaSuccessCallback = callback;

            const w = 880;
            const h = 720;
            const left = Math.max(0, (window.screen.width - w) / 2);
            const top = Math.max(0, (window.screen.height - h) / 2);
            const url = "{{ route('coa.create') }}?popup=1";
            
            const win = window.open(url, "AddCoaWindow", `width=${w},height=${h},top=${top},left=${left},scrollbars=yes,resizable=yes,status=no,location=no,toolbar=no,menubar=no`);
            if (win) {
                win.focus();
            } else {
                alert('Popup diblokir oleh browser. Mohon izinkan popup untuk situs ini.');
            }
        @endif
    };

    /**
     * Callback Handler Universal saat form di child window berhasil di-submit
     */
    window.handleWindowOpenerCallback = function(data) {
        if (!data || !data.type) return;

        // 1. DATA KONTAK / SUPPLIER / CUSTOMER BARU
        if (data.type === 'contact_created' && data.contact) {
            const contact = data.contact;
            const optText = contact.label || (contact.name + (contact.code ? ' (' + contact.code + ')' : ''));

            if (currentContactTargetSelect) {
                const selects = document.querySelectorAll(currentContactTargetSelect);
                selects.forEach(select => {
                    const newOption = new Option(optText, contact.id, true, true);
                    select.add(newOption);
                    select.value = contact.id;
                    if (window.$) $(select).trigger('change');
                });
            } else {
                // Fallback auto-detection target select
                const commonSelects = ['#supplier_id', '#customer_id', '#posCustomerSelect', '#cashInContactSelect', '#cashOutContactSelect', 'select[name="supplier_id"]', 'select[name="customer_id"]', 'select[name="contact_id"]'];
                commonSelects.forEach(sel => {
                    const el = document.querySelector(sel);
                    if (el) {
                        const newOption = new Option(optText, contact.id, true, true);
                        el.add(newOption);
                        el.value = contact.id;
                        if (window.$) $(el).trigger('change');
                    }
                });
            }

            if (typeof currentContactSuccessCallback === 'function') {
                currentContactSuccessCallback(contact);
            }
        }

        // 2. DATA BARANG / JASA BARU
        else if (data.type === 'item_created' && data.item) {
            const item = data.item;
            const optText = item.label || `${item.code || ''} - ${item.name}`;

            if (typeof currentItemTargetCallback === 'function') {
                currentItemTargetCallback(item);
            } else if (typeof currentItemTargetCallback === 'string') {
                const selects = document.querySelectorAll(currentItemTargetCallback);
                selects.forEach(select => {
                    const newOption = new Option(optText, item.id, true, true);
                    select.add(newOption);
                    select.value = item.id;
                    if (window.$) $(select).trigger('change');
                });
            } else if (typeof addItemRow === 'function') {
                // Fungsi penambah baris pada form PO / SO / PR / Work Order
                addItemRow(item.id, item.name, item.code || '', item.unit_name || item.unit_symbol || '', item.purchase_price || item.selling_price || 0, item.selling_price || 0);
            } else if (typeof addToCart === 'function') {
                // Fungsi keranjang POS
                addToCart(item);
            } else {
                const itemSelects = ['#item_id', 'select[name="item_id"]', '.item-select'];
                itemSelects.forEach(sel => {
                    const els = document.querySelectorAll(sel);
                    els.forEach(el => {
                        const newOption = new Option(optText, item.id, true, true);
                        el.add(newOption);
                        el.value = item.id;
                        if (window.$) $(el).trigger('change');
                    });
                });
            }
        }

        // 3. DATA AKUN COA BARU
        else if (data.type === 'coa_created' && data.account) {
            const account = data.account;
            const optText = account.label || `${account.code} - ${account.name}`;

            if (currentCoaTargetSelect) {
                const selects = document.querySelectorAll(currentCoaTargetSelect);
                selects.forEach(select => {
                    const newOption = new Option(optText, account.id, true, true);
                    select.add(newOption);
                    select.value = account.id;
                    if (window.$) $(select).trigger('change');
                });
            } else {
                const coaSelects = ['#cashInAccountSelect', '#cashOutAccountSelect', '#paymentMethodSelect', 'select[name="account_id"]', 'select[name="debit_account_id"]', 'select[name="credit_account_id"]', '.journal-account-select', '.contra-account-select'];
                coaSelects.forEach(sel => {
                    const els = document.querySelectorAll(sel);
                    els.forEach(el => {
                        const newOption = new Option(optText, account.id, true, true);
                        el.add(newOption);
                        el.value = account.id;
                        if (window.$) $(el).trigger('change');
                    });
                });
            }

            if (typeof currentCoaSuccessCallback === 'function') {
                currentCoaSuccessCallback(account);
            }
        }
    };

    // Dengarkan pesan postMessage dari child window (window.opener)
    window.addEventListener('message', function(event) {
        if (event.data && typeof event.data === 'object' && event.data.type) {
            window.handleWindowOpenerCallback(event.data);
        }
    });
</script>
