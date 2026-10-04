import http from 'k6/http';
import { check, group, sleep } from 'k6';

// Konfigurasi Baseline Stress Test untuk VPS OpenVZ (Webuzo)
// Dijalankan secara bertahap (10 -> 25 -> 50 VU) agar aman terhadap numproc & RAM OpenVZ
export const options = {
    stages: [
        { duration: '1m', target: 10 },  // Tahap 1: Pemanasan / 10 Virtual Users
        { duration: '2m', target: 10 },  // Pertahankan 10 VU
        { duration: '1m', target: 25 },  // Tahap 2: Beban Menengah / 25 Virtual Users
        { duration: '2m', target: 25 },  // Pertahankan 25 VU
        { duration: '1m', target: 50 },  // Tahap 3: Beban Puncak Baseline / 50 Virtual Users
        { duration: '2m', target: 50 },  // Pertahankan 50 VU
        { duration: '30s', target: 0 },  // Ramp-down / Pendinginan
    ],
    thresholds: {
        // Toleransi error di bawah 1%
        http_req_failed: ['rate<0.01'],
        // 95% request selesai di bawah 2 detik
        http_req_duration: ['p(95)<2000'],
    },
};

const BASE_URL = __ENV.TARGET_URL || 'https://sivana.my.id';

const PUBLIC_PAGES = [
    { name: 'Home Landing', path: '/' },
    { name: 'Login Page', path: '/login' },
    { name: 'Berita Sekolah', path: '/berita' },
    { name: 'Agenda Sekolah', path: '/agenda' },
    { name: 'Galeri Kegiatan', path: '/galeri' },
    { name: 'Prestasi Siswa', path: '/prestasi' },
    { name: 'Ekstrakurikuler', path: '/ekstrakurikuler' },
    { name: 'Fasilitas', path: '/fasilitas' },
    { name: 'Jurusan / Kompetensi Keahlian', path: '/jurusan' },
    { name: 'Kontak', path: '/kontak' },
];

export default function () {
    // 1. Kunjungi Halaman Utama
    group('01_Home_Page', function () {
        const res = http.get(`${BASE_URL}/`, {
            headers: { 'Accept': 'text/html' },
            tags: { page: 'home' }
        });
        check(res, {
            'status is 200': (r) => r.status === 200,
            'body not empty': (r) => r.body && r.body.length > 500,
        });
    });

    // Simulasi jeda baca pengunjung (1 - 2.5 detik)
    sleep(Math.random() * 1.5 + 1);

    // 2. Kunjungi Halaman Login
    group('02_Login_Page', function () {
        const res = http.get(`${BASE_URL}/login`, {
            headers: { 'Accept': 'text/html' },
            tags: { page: 'login' }
        });
        check(res, {
            'status is 200': (r) => r.status === 200,
            'has csrf or form': (r) => r.body && (r.body.includes('csrf') || r.body.includes('form') || r.body.includes('password')),
        });
    });

    sleep(Math.random() * 1.5 + 1);

    // 3. Jelajahi 2 halaman publik secara acak
    const randomPage1 = PUBLIC_PAGES[Math.floor(Math.random() * PUBLIC_PAGES.length)];
    const randomPage2 = PUBLIC_PAGES[Math.floor(Math.random() * PUBLIC_PAGES.length)];

    group(`03_Public_Browse_${randomPage1.name}`, function () {
        const res = http.get(`${BASE_URL}${randomPage1.path}`, {
            headers: { 'Accept': 'text/html' },
            tags: { page: randomPage1.name }
        });
        check(res, {
            'status is 200': (r) => r.status === 200,
        });
    });

    sleep(Math.random() * 1.5 + 1);

    group(`04_Public_Browse_${randomPage2.name}`, function () {
        const res = http.get(`${BASE_URL}${randomPage2.path}`, {
            headers: { 'Accept': 'text/html' },
            tags: { page: randomPage2.name }
        });
        check(res, {
            'status is 200': (r) => r.status === 200,
        });
    });

    sleep(Math.random() * 2 + 1);
}
