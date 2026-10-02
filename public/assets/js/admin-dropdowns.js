/**
 * SMK NEGERI 2 KARANGANYAR — ADMIN DROPDOWN ENHANCEMENT
 * Khusus Panel Role Admin: Custom Popover Dropdowns & Searchable Comboboxes
 * 100% SVG Icons, Zero Emoji, No Scrollbars, Two-way Sync
 * Bebas dari menu abu-abu default browser
 */

(function () {
    'use strict';

    // Pastikan hanya berjalan di konteks admin
    if (!document.body.classList.contains('teacher-portal')) {
        return;
    }

    const ICONS = {
        search: `<svg class="admin-searchable-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>`,
        chevron: `<svg class="admin-searchable-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>`,
        clear: `<svg class="w-2.5 h-2.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>`,
        check: `<svg class="admin-searchable-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>`,
        teacher: `<svg class="admin-searchable-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>`,
        subject: `<svg class="admin-searchable-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>`,
        schoolClass: `<svg class="admin-searchable-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>`,
        company: `<svg class="admin-searchable-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="2" width="16" height="20" rx="2"/><path d="M9 22v-4h6v4"/><path d="M8 6h.01"/><path d="M16 6h.01"/><path d="M12 6h.01"/><path d="M12 10h.01"/><path d="M12 14h.01"/><path d="M16 10h.01"/><path d="M16 14h.01"/><path d="M8 10h.01"/><path d="M8 14h.01"/></svg>`,
        student: `<svg class="admin-searchable-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>`,
        calendar: `<svg class="admin-searchable-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>`,
        status: `<svg class="admin-searchable-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>`,
        gradeLevel: `<svg class="admin-searchable-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/></svg>`,
        gender: `<svg class="admin-searchable-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="7" r="4"/><path d="M6 21v-2a6 6 0 0 1 12 0v2"/></svg>`,
        category: `<svg class="admin-searchable-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>`,
        role: `<svg class="admin-searchable-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>`,
        filter: `<svg class="admin-searchable-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>`,
        empty: `<svg class="admin-searchable-empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="8" y1="11" x2="14" y2="11"/></svg>`,
        defaultIcon: `<svg class="admin-searchable-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>`
    };

    function getFieldIcon(select) {
        const key = `${select.name || ''} ${select.id || ''} ${select.className || ''}`.toLowerCase();
        if (/teacher|guru|wali|homeroom/i.test(key)) return ICONS.teacher;
        if (/subject|mapel|pelajaran/i.test(key)) return ICONS.subject;
        if (/grade_level|tingkat|level/i.test(key)) return ICONS.gradeLevel;
        if (/class|rombel|kelas/i.test(key)) return ICONS.schoolClass;
        if (/student|siswa/i.test(key)) return ICONS.student;
        if (/company|mitra|perusahaan/i.test(key)) return ICONS.company;
        if (/year|academic|semester|tahun|periode/i.test(key)) return ICONS.calendar;
        if (/status/i.test(key)) return ICONS.status;
        if (/gender|kelamin/i.test(key)) return ICONS.gender;
        if (/category|kategori/i.test(key)) return ICONS.category;
        if (/role/i.test(key)) return ICONS.role;
        if (/type|tipe|scope/i.test(key)) return ICONS.filter;
        return ICONS.defaultIcon;
    }

    /**
     * Tentukan apakah dropdown perlu fitur search box di popovernya
     * Sesuai request: data yang banyak (guru, mapel, kelas, siswa, perusahaan, ops >= 6) ada search,
     * sedangkan data sedikit (status, tingkat, gender, dll) TIDAK ada search tapi tetap pakai custom popover!
     */
    function checkHasSearch(select) {
        if (select.dataset.searchable === 'true') return true;
        if (select.dataset.searchable === 'false' || select.dataset.noSearch !== undefined) return false;

        const key = `${select.name || ''} ${select.id || ''}`.toLowerCase();
        if (/teacher|guru|wali|homeroom|subject|mapel|pelajaran|class_id|source_class|target_class|rombel|student_id|siswa|company_id|perusahaan|mitra|teaching_assignment|assignment_id/i.test(key)) {
            return true;
        }

        if (select.options.length >= 6) {
            return true;
        }

        return false;
    }

    /**
     * Upgrade SEMUA select di panel admin agar tidak ada pilihan abu-abu default bawaan browser
     */
    function shouldUpgradeSelect(select) {
        if (!select || select.nodeName !== 'SELECT') return false;
        if (select._adminSearchableSelect) return false;
        if (select.multiple) return false;
        if (select.classList.contains('word-select')) return false; // WYSIWYG editor text style
        return true;
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text == null ? '' : text;
        return div.innerHTML;
    }

    function highlightMatch(text, query) {
        if (!query) return escapeHtml(text);
        const escaped = escapeHtml(text);
        const qEscaped = query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        const regex = new RegExp(`(${qEscaped})`, 'gi');
        return escaped.replace(regex, '<mark>$1</mark>');
    }

    // Active open dropdown tracker
    let activeInstance = null;

    class AdminSearchableSelect {
        constructor(select) {
            this.select = select;
            this.select._adminSearchableSelect = this;
            this.icon = getFieldIcon(select);
            this.hasSearch = checkHasSearch(select);
            this.isOpen = false;
            this.highlightedIndex = -1;
            this.filteredOptions = [];
            this.popover = null;

            this.initDOM();
            this.bindEvents();
            this.sync();
        }

        initDOM() {
            // Container wrapper
            this.wrap = document.createElement('div');
            this.wrap.className = 'admin-searchable-wrap';

            // Preserve width & padding classes from original select
            const classList = Array.from(this.select.classList);
            classList.forEach(cls => {
                if (/^(w-|max-w-|min-w-|flex-|col-|text-|py-)/.test(cls)) {
                    this.wrap.classList.add(cls);
                }
            });

            // Trigger Button
            this.trigger = document.createElement('button');
            this.trigger.type = 'button';
            this.trigger.className = 'admin-searchable-trigger';
            this.trigger.setAttribute('aria-haspopup', 'listbox');
            this.trigger.setAttribute('aria-expanded', 'false');

            if (this.select.disabled) {
                this.trigger.disabled = true;
            }

            this.trigger.innerHTML = `
                <div class="admin-searchable-left">
                    ${this.icon}
                    <span class="admin-searchable-value"></span>
                </div>
                <div class="admin-searchable-right">
                    <span class="admin-searchable-clear" style="display:none;" title="Kosongkan Pilihan">${ICONS.clear}</span>
                    ${ICONS.chevron}
                </div>
            `;

            this.valueLabel = this.trigger.querySelector('.admin-searchable-value');
            this.clearBtn = this.trigger.querySelector('.admin-searchable-clear');

            // Hide native select visually but keep in DOM for forms
            this.select.classList.add('admin-searchable-hidden');
            this.select.parentNode.insertBefore(this.wrap, this.select);
            this.wrap.appendChild(this.trigger);
            this.wrap.appendChild(this.select);
        }

        bindEvents() {
            // Trigger click
            this.trigger.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                if (this.select.disabled) return;
                this.toggle();
            });

            // Clear button click
            this.clearBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.selectValue('', true);
            });

            // Delegate focus on native select to trigger button
            this.select.focus = (opts) => {
                this.trigger.focus(opts);
            };

            // HTML5 validation error handling
            this.select.addEventListener('invalid', () => {
                this.trigger.classList.add('is-invalid');
                this.trigger.focus();
            });

            this.select.addEventListener('input', () => {
                this.trigger.classList.remove('is-invalid');
            });

            // Native select change event listener
            this.select.addEventListener('change', () => {
                this.sync();
            });

            // Native select reset from form
            if (this.select.form) {
                this.select.form.addEventListener('reset', () => {
                    setTimeout(() => this.sync(), 20);
                });
            }

            // MutationObserver on native select (tracks dynamic options additions/removals/attribute changes)
            this.observer = new MutationObserver(() => {
                if (this.select.disabled !== this.trigger.disabled) {
                    this.trigger.disabled = this.select.disabled;
                }
                this.hasSearch = checkHasSearch(this.select);
                this.sync();
            });
            this.observer.observe(this.select, {
                childList: true,
                subtree: true,
                attributes: true,
                attributeFilter: ['disabled', 'class']
            });
        }

        getOptionsData() {
            return Array.from(this.select.options).map((opt, idx) => ({
                index: idx,
                value: opt.value,
                text: (opt.textContent || '').trim(),
                selected: opt.selected,
                disabled: opt.disabled,
                isPlaceholder: opt.value === '' || opt.disabled && idx === 0
            }));
        }

        sync() {
            const selectedOpt = this.select.options[this.select.selectedIndex];
            if (selectedOpt && selectedOpt.value !== '') {
                this.valueLabel.textContent = selectedOpt.textContent.trim();
                this.valueLabel.classList.remove('admin-searchable-placeholder');

                // Tampilkan tombol clear jika ada opsi kosong dan bukan required
                const hasEmptyOpt = Array.from(this.select.options).some(o => o.value === '');
                if (hasEmptyOpt && !this.select.disabled && !this.select.required) {
                    this.clearBtn.style.display = 'inline-flex';
                } else {
                    this.clearBtn.style.display = 'none';
                }
            } else {
                const placeholderText = selectedOpt ? selectedOpt.textContent.trim() : '-- Pilih --';
                this.valueLabel.textContent = placeholderText || '-- Pilih --';
                this.valueLabel.classList.add('admin-searchable-placeholder');
                this.clearBtn.style.display = 'none';
            }
        }

        toggle() {
            if (this.isOpen) {
                this.close();
            } else {
                this.open();
            }
        }

        open() {
            if (activeInstance && activeInstance !== this) {
                activeInstance.close();
            }

            this.isOpen = true;
            activeInstance = this;
            this.trigger.classList.add('is-open');
            this.trigger.setAttribute('aria-expanded', 'true');

            this.createPopover();
            this.updatePosition();
            this.renderList('');

            if (this.hasSearch) {
                requestAnimationFrame(() => {
                    if (this.searchInput) {
                        this.searchInput.focus();
                    }
                });
            } else {
                requestAnimationFrame(() => {
                    if (this.popover) {
                        this.popover.focus();
                    }
                });
            }
        }

        close() {
            if (!this.isOpen) return;
            this.isOpen = false;
            if (activeInstance === this) activeInstance = null;

            this.trigger.classList.remove('is-open');
            this.trigger.setAttribute('aria-expanded', 'false');

            if (this.popover && this.popover.parentNode) {
                this.popover.parentNode.removeChild(this.popover);
            }
            this.popover = null;
        }

        createPopover() {
            this.popover = document.createElement('div');
            this.popover.className = 'admin-searchable-popover' + (this.hasSearch ? '' : ' no-search');
            this.popover.setAttribute('role', 'listbox');
            this.popover.tabIndex = -1;

            if (this.hasSearch) {
                this.popover.innerHTML = `
                    <div class="admin-searchable-header">
                        <div class="admin-searchable-searchbox">
                            ${ICONS.search}
                            <input type="text" class="admin-searchable-input" placeholder="Ketik untuk mencari...">
                            <span class="admin-searchable-input-clear" style="display:none;" title="Hapus">${ICONS.clear}</span>
                        </div>
                    </div>
                    <div class="admin-searchable-options"></div>
                    <div class="admin-searchable-footer">
                        <span class="admin-searchable-count">Menampilkan 0 data</span>
                        <span>Tekan <kbd>↵</kbd> pilih &bull; <kbd>Esc</kbd> batal</span>
                    </div>
                `;

                this.searchInput = this.popover.querySelector('.admin-searchable-input');
                this.inputClearBtn = this.popover.querySelector('.admin-searchable-input-clear');
                this.optionsContainer = this.popover.querySelector('.admin-searchable-options');
                this.countLabel = this.popover.querySelector('.admin-searchable-count');

                // Search input typing
                this.searchInput.addEventListener('input', () => {
                    const val = this.searchInput.value;
                    this.inputClearBtn.style.display = val ? 'inline-flex' : 'none';
                    this.renderList(val.trim());
                });

                // Input clear button
                this.inputClearBtn.addEventListener('click', () => {
                    this.searchInput.value = '';
                    this.inputClearBtn.style.display = 'none';
                    this.renderList('');
                    this.searchInput.focus();
                });

                // Keyboard navigation
                this.searchInput.addEventListener('keydown', (e) => {
                    this.handleKeydown(e);
                });
            } else {
                // Dropdown sedikit opsi (Status, Tingkat, Gender, dll) — Tanpa search input & tanpa footer count
                this.popover.innerHTML = `<div class="admin-searchable-options"></div>`;
                this.optionsContainer = this.popover.querySelector('.admin-searchable-options');
                this.searchInput = null;
                this.inputClearBtn = null;
                this.countLabel = null;

                this.popover.addEventListener('keydown', (e) => {
                    this.handleKeydown(e);
                });
            }

            document.body.appendChild(this.popover);
        }

        updatePosition() {
            if (!this.popover) return;
            const rect = this.trigger.getBoundingClientRect();
            const popoverHeight = this.popover.offsetHeight || (this.hasSearch ? 260 : 150);
            const spaceBelow = window.innerHeight - rect.bottom;
            const openUpwards = spaceBelow < popoverHeight && rect.top > popoverHeight;

            const popoverWidth = Math.max(rect.width, 160);
            this.popover.style.width = `${popoverWidth}px`;

            // Horizontally align with trigger, but keep within viewport
            let left = rect.left;
            if (left + popoverWidth > window.innerWidth - 12) {
                left = window.innerWidth - popoverWidth - 12;
            }
            if (left < 12) left = 12;
            this.popover.style.left = `${left}px`;

            if (openUpwards) {
                this.popover.classList.add('open-upwards');
                this.popover.style.bottom = `${window.innerHeight - rect.top + 4}px`;
                this.popover.style.top = 'auto';
            } else {
                this.popover.classList.remove('open-upwards');
                this.popover.style.top = `${rect.bottom + 4}px`;
                this.popover.style.bottom = 'auto';
            }
        }

        renderList(query) {
            const all = this.getOptionsData();
            const q = query.toLowerCase();

            this.filteredOptions = all.filter(item => {
                if (!q) return true;
                return item.text.toLowerCase().includes(q) || item.value.toLowerCase().includes(q);
            });

            if (this.countLabel) {
                this.countLabel.innerHTML = `Menampilkan <strong>${this.filteredOptions.length}</strong> dari ${all.length} data`;
            }

            this.optionsContainer.innerHTML = '';
            this.highlightedIndex = -1;

            if (this.filteredOptions.length === 0) {
                const emptyEl = document.createElement('div');
                emptyEl.className = 'admin-searchable-empty';
                emptyEl.innerHTML = `
                    ${ICONS.empty}
                    <div class="admin-searchable-empty-title">Tidak ada saran yang cocok</div>
                    <div class="admin-searchable-empty-desc">Tidak ditemukan opsi dengan kata kunci "${escapeHtml(query)}"</div>
                `;
                this.optionsContainer.appendChild(emptyEl);
                return;
            }

            this.filteredOptions.forEach((item, idx) => {
                const optEl = document.createElement('div');
                optEl.className = 'admin-searchable-option';
                optEl.setAttribute('role', 'option');
                optEl.dataset.index = idx;

                if (item.selected) {
                    optEl.classList.add('is-selected');
                }

                const highlightedText = highlightMatch(item.text, query);
                optEl.innerHTML = `
                    <div class="admin-searchable-option-text">${highlightedText}</div>
                    ${item.selected ? ICONS.check : ''}
                `;

                optEl.addEventListener('mouseenter', () => {
                    this.setHighlightedIndex(idx, false);
                });

                optEl.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    this.selectValue(item.value, true);
                });

                this.optionsContainer.appendChild(optEl);
            });

            // Highlight selected item or first item
            const currentSelectedIdx = this.filteredOptions.findIndex(o => o.selected);
            if (currentSelectedIdx >= 0) {
                this.setHighlightedIndex(currentSelectedIdx, true);
            } else if (this.filteredOptions.length > 0) {
                this.setHighlightedIndex(0, true);
            }
        }

        setHighlightedIndex(idx, scrollIntoView) {
            const items = this.optionsContainer.querySelectorAll('.admin-searchable-option');
            items.forEach(el => el.classList.remove('is-highlighted'));

            if (idx >= 0 && idx < items.length) {
                this.highlightedIndex = idx;
                const activeEl = items[idx];
                activeEl.classList.add('is-highlighted');

                if (scrollIntoView) {
                    activeEl.scrollIntoView({ block: 'nearest' });
                }
            } else {
                this.highlightedIndex = -1;
            }
        }

        handleKeydown(e) {
            if (e.key === 'Escape') {
                e.preventDefault();
                this.close();
                this.trigger.focus();
                return;
            }

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                if (this.filteredOptions.length === 0) return;
                const nextIdx = (this.highlightedIndex + 1) % this.filteredOptions.length;
                this.setHighlightedIndex(nextIdx, true);
                return;
            }

            if (e.key === 'ArrowUp') {
                e.preventDefault();
                if (this.filteredOptions.length === 0) return;
                const prevIdx = (this.highlightedIndex - 1 + this.filteredOptions.length) % this.filteredOptions.length;
                this.setHighlightedIndex(prevIdx, true);
                return;
            }

            if (e.key === 'Enter') {
                e.preventDefault();
                if (this.highlightedIndex >= 0 && this.highlightedIndex < this.filteredOptions.length) {
                    const item = this.filteredOptions[this.highlightedIndex];
                    this.selectValue(item.value, true);
                }
                return;
            }
        }

        selectValue(value, triggerEvents = true) {
            this.select.value = value;
            this.trigger.classList.remove('is-invalid');
            this.sync();
            this.close();

            if (triggerEvents) {
                // Dispatch native events
                const changeEvent = new Event('change', { bubbles: true });
                const inputEvent = new Event('input', { bubbles: true });
                this.select.dispatchEvent(changeEvent);
                this.select.dispatchEvent(inputEvent);

                // Run inline onchange if present
                if (typeof this.select.onchange === 'function') {
                    this.select.onchange(changeEvent);
                }
            }

            this.trigger.focus();
        }
    }

    // Intercept property descriptor on HTMLSelectElement.prototype.value to keep custom UI synced
    try {
        const desc = Object.getOwnPropertyDescriptor(HTMLSelectElement.prototype, 'value');
        if (desc && desc.set) {
            const originalSet = desc.set;
            desc.set = function (val) {
                originalSet.call(this, val);
                if (this._adminSearchableSelect) {
                    this._adminSearchableSelect.sync();
                }
            };
            Object.defineProperty(HTMLSelectElement.prototype, 'value', desc);
        }
    } catch (e) {
        // Fallback gracefully
    }

    // Global document listeners
    document.addEventListener('click', (e) => {
        if (!activeInstance) return;
        const inTrigger = activeInstance.trigger.contains(e.target);
        const inPopover = activeInstance.popover && activeInstance.popover.contains(e.target);
        if (!inTrigger && !inPopover) {
            activeInstance.close();
        }
    });

    window.addEventListener('resize', () => {
        if (activeInstance) activeInstance.updatePosition();
    });

    window.addEventListener('scroll', (e) => {
        if (activeInstance) {
            // Jika scroll terjadi di dalam dropdown options itu sendiri, jangan tutup atau geser
            if (activeInstance.popover && activeInstance.popover.contains(e.target)) {
                return;
            }
            activeInstance.updatePosition();
        }
    }, true);

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && activeInstance) {
            activeInstance.close();
        }
    });

    // Scanner to initialize all matching selects
    function initSearchableSelects(root = document) {
        const selects = root.querySelectorAll('select');
        selects.forEach(sel => {
            if (shouldUpgradeSelect(sel)) {
                new AdminSearchableSelect(sel);
            }
        });
    }

    // Auto-scan on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => initSearchableSelects());
    } else {
        initSearchableSelects();
    }

    // MutationObserver to auto-upgrade dynamically added selects (e.g. modals)
    const domObserver = new MutationObserver((mutations) => {
        mutations.forEach(m => {
            m.addedNodes.forEach(node => {
                if (node.nodeType === Node.ELEMENT_NODE) {
                    if (node.nodeName === 'SELECT') {
                        if (shouldUpgradeSelect(node)) new AdminSearchableSelect(node);
                    } else {
                        initSearchableSelects(node);
                    }
                }
            });
        });
    });

    domObserver.observe(document.body, {
        childList: true,
        subtree: true
    });

    // Public API
    window.AdminDropdowns = {
        init: initSearchableSelects,
        sync: (select) => {
            if (select && select._adminSearchableSelect) {
                select._adminSearchableSelect.sync();
            }
        },
        upgrade: (select) => {
            if (select && !select._adminSearchableSelect) {
                return new AdminSearchableSelect(select);
            }
            return select ? select._adminSearchableSelect : null;
        }
    };
})();
