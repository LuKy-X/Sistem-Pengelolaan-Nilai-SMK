/**
 * Pencarian siswa untuk <select> di dalam modal BK.
 *
 * Opsi siswa sudah dirender server-side, jadi pencarian hanya menyaring
 * ulang opsi yang ada (tanpa request baru) dan digabung dengan filter
 * kelas bila elemennya diberikan.
 *
 * Pemakaian:
 *   initStudentPicker({
 *     search: document.getElementById('record_student_search'),
 *     select: document.getElementById('student_id_modal'),
 *     classFilter: document.getElementById('record_class_id'),
 *     feedback: document.getElementById('record_student_count')
 *   });
 *
 * Opsi siswa sebaiknya membawa `data-class` (id kelas) dan `data-search`
 * (NIS/NISN) supaya keduanya ikut dicari.
 */
function normalizeStudentQuery(value) {
    return String(value == null ? '' : value)
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '') // buang tanda baca agar huruf vokal panjang tetap cocok
        .replace(/[^a-z0-9]+/g, ' ')
        .trim();
}

function initStudentPicker(config) {
    var search = config.search;
    var select = config.select;
    var classFilter = config.classFilter || null;
    var feedback = config.feedback || null;

    if (!search || !select) return;

    // Opsi pertama adalah placeholder ("Pilih siswa") dan selalu dipertahankan.
    var entries = Array.prototype.slice.call(select.options).slice(1).map(function (option) {
        return {
            node: option.cloneNode(true),
            value: option.value,
            classId: option.dataset.class || '',
            haystack: normalizeStudentQuery(option.textContent + ' ' + (option.dataset.search || ''))
        };
    });

    function render() {
        var terms = normalizeStudentQuery(search.value).split(' ').filter(Boolean);
        var classId = classFilter ? classFilter.value : '';
        var previousValue = select.value;
        var visible = 0;

        while (select.options.length > 1) {
            select.remove(1);
        }

        entries.forEach(function (entry) {
            var classMatches = !classId || entry.classId === classId;
            var queryMatches = terms.every(function (term) {
                return entry.haystack.indexOf(term) !== -1;
            });

            if (!classMatches || !queryMatches) return;

            select.appendChild(entry.node.cloneNode(true));
            visible += 1;
        });

        var stillAvailable = Array.prototype.some.call(select.options, function (option) {
            return option.value === previousValue;
        });

        select.value = stillAvailable ? previousValue : '';

        if (feedback) {
            feedback.textContent = visible === 0
                ? 'Siswa tidak ditemukan. Ubah kata kunci atau pilihan kelas.'
                : visible + ' siswa tersedia';
        }

        // Beri tahu listener lain, mis. pembaru saldo poin di modal Surat Peringatan.
        select.dispatchEvent(new Event('change', { bubbles: true }));
    }

    search.addEventListener('input', render);

    if (classFilter) {
        classFilter.addEventListener('change', render);
    }

    render();
}

window.initStudentPicker = initStudentPicker;