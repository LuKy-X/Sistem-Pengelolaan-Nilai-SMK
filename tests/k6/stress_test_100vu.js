import http from 'k6/http';
import { check, group, sleep } from 'k6';

// Konfigurasi Pengujian Beban 100 Virtual Users (100 VU)
// Didesain bertahap (25 -> 50 -> 75 -> 100 VU) agar aman terhadap batas proses OpenVZ (Webuzo)
export const options = {
    stages: [
        { duration: '1m', target: 25 },     // Tahap 1: Ramp-up ke 25 VU
        { duration: '1m30s', target: 25 },  // Stabilkan di 25 VU
        { duration: '1m', target: 50 },     // Tahap 2: Naik ke 50 VU
        { duration: '1m30s', target: 50 },  // Stabilkan di 50 VU
        { duration: '1m', target: 75 },     // Tahap 3: Naik ke 75 VU
        { duration: '1m30s', target: 75 },  // Stabilkan di 75 VU
        { duration: '1m', target: 100 },    // Tahap 4: Puncak Beban 100 VU
        { duration: '2m', target: 100 },    // Pertahankan beban 100 VU
        { duration: '30s', target: 0 },     // Ramp-down / Pendinginan server
    ],
    thresholds: {
        // Toleransi error maksimum di bawah 2% pada beban 100 VU
        http_req_failed: ['rate<0.02'],
        // 95% request selesai di bawah 3.5 detik (termasuk waktu antrean FastCGI)
        http_req_duration: ['p(95)<3500'],
    },
};

const BASE_URL = __ENV.TARGET_URL || 'https://sivana.my.id';

const PUBLIC_PAGES = [
    { name: 'Profil Sekolah', path: '/profil' },
    { name: 'Jurusan / Keahlian', path: '/jurusan' },
    { name: 'Berita Sekolah', path: '/berita' },
    { name: 'Prestasi Siswa', path: '/prestasi' },
    { name: 'Alumni', path: '/alumni' },
    { name: 'Info PPDB', path: '/ppdb' },
    { name: 'Produk Siswa (TeFa)', path: '/produk-siswa' },
    { name: 'Bursa Kerja (Karier)', path: '/karier' },
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

    // Simulasi jeda baca pengunjung (1 - 2 detik)
    sleep(Math.random() * 1 + 1);

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

    sleep(Math.random() * 1 + 1);

    // 3. Jelajahi 2 halaman publik secara dinamis
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

    sleep(Math.random() * 1 + 1);

    group(`04_Public_Browse_${randomPage2.name}`, function () {
        const res = http.get(`${BASE_URL}${randomPage2.path}`, {
            headers: { 'Accept': 'text/html' },
            tags: { page: randomPage2.name }
        });
        check(res, {
            'status is 200': (r) => r.status === 200,
        });
    });

    sleep(Math.random() * 1.5 + 1);
}
