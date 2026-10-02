/**
 * Realtime Unique Field Validator
 * Sistem Pengelolaan Nilai SMK
 * Memeriksa keunikan username, NIS, NISN, NIP, email, kode mapel/kelas secara realtime di bawah input.
 */

(function () {
    'use strict';

    const debounceTimers = new WeakMap();

    function getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function ensureFeedbackEl(input) {
        let feedback = input.parentElement.querySelector('.check-unique-feedback');
        if (!feedback) {
            feedback = document.createElement('div');
            feedback.className = 'check-unique-feedback text-[11px] mt-1 transition-all';
            // Sisipkan setelah input atau setelah wrapper input
            input.parentElement.appendChild(feedback);
        }
        return feedback;
    }

    function checkField(input) {
        const type = input.getAttribute('data-check-unique');
        const val = input.value.trim();
        const ignoreId = input.getAttribute('data-ignore-id') || '';
        const feedback = ensureFeedbackEl(input);

        // Jika kosong atau terlalu pendek (kurang dari 2 karakter)
        if (!val || val.length < 2) {
            feedback.innerHTML = '';
            input.classList.remove('border-emerald-500', 'focus:ring-emerald-500', 'border-rose-500', 'focus:ring-rose-500');
            return;
        }

        // Tampilkan indikator loading halus
        feedback.innerHTML = `
            <span class="text-bluedark/50 inline-flex items-center gap-1.5 text-[11px]">
                <svg class="animate-spin w-3 h-3 text-blueprim" viewBox="0 0 24 24" fill="none">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
                Memeriksa ketersediaan...
            </span>
        `;

        const csrfToken = getCsrfToken();
        const extraData = {};
        const yearSelect = document.getElementById('academic_year_id');
        if (yearSelect) {
            extraData.academic_year_id = yearSelect.value;
        }

        fetch('/admin/check-unique', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                type: type,
                value: val,
                ignore_id: ignoreId || null,
                extra: extraData
            })
        })
        .then(response => {
            if (!response.ok && response.status !== 422) {
                throw new Error('Gagal menghubungi server');
            }
            return response.json();
        })
        .then(data => {
            // Cek apakah nilai input masih sama saat respon tiba
            if (input.value.trim() !== val) return;

            if (data.available) {
                input.classList.remove('border-rose-500', 'focus:ring-rose-500');
                input.classList.add('border-emerald-500', 'focus:ring-emerald-500');
                feedback.innerHTML = `
                    <span class="text-emerald-700 font-medium inline-flex items-center gap-1.5 text-[11px]">
                        <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        ${data.message}
                    </span>
                `;
            } else {
                input.classList.remove('border-emerald-500', 'focus:ring-emerald-500');
                input.classList.add('border-rose-500', 'focus:ring-rose-500');
                feedback.innerHTML = `
                    <span class="text-rose-700 font-medium inline-flex items-center gap-1.5 text-[11px]">
                        <svg class="w-3.5 h-3.5 text-rose-600 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                        ${data.message}
                    </span>
                `;
            }
        })
        .catch(err => {
            console.error('Check unique error:', err);
            feedback.innerHTML = '';
        });
    }

    function setupInput(input) {
        if (input._checkUniqueAttached) return;
        input._checkUniqueAttached = true;

        input.addEventListener('input', function () {
            if (debounceTimers.has(input)) {
                clearTimeout(debounceTimers.get(input));
            }
            const timer = setTimeout(() => checkField(input), 300);
            debounceTimers.set(input, timer);
        });

        input.addEventListener('blur', function () {
            if (debounceTimers.has(input)) {
                clearTimeout(debounceTimers.get(input));
            }
            checkField(input);
        });
    }

    window.initCheckUnique = function () {
        const inputs = document.querySelectorAll('[data-check-unique]');
        inputs.forEach(setupInput);
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', window.initCheckUnique);
    } else {
        window.initCheckUnique();
    }
})();
