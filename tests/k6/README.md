# Panduan Menjalankan Baseline Stress Test (k6)

Dokumen ini adalah panduan lengkap untuk melakukan pengujian beban (stress test) tahap awal pada aplikasi **Sistem Pengelolaan Nilai SMK** yang di-host pada VPS **OpenVZ / Virtuozzo** dengan panel Webuzo.

---

## ⚠️ Prinsip Penting Sebelum Memulai

1. **Jalankan k6 dari komputer lokal (laptop / PC) Anda, BUKAN dari dalam VPS!**
   - Jika k6 dijalankan dari dalam VPS yang sama, CPU dan RAM VPS akan terkuras oleh proses k6 itu sendiri, sehingga hasil tes menjadi tidak akurat dan berisiko memicu batas `numproc` (limit proses OpenVZ).
2. **Pengujian Bertahap (Progressive)**:
   - Skrip k6 ini didesain berjenjang:
     - **Tahap 1**: 10 Virtual Users (VU) selama 3 menit (pemanasan & verifikasi kestabilan).
     - **Tahap 2**: 25 Virtual Users (VU) selama 3 menit (beban normal jam operasional sekolah).
     - **Tahap 3**: 50 Virtual Users (VU) selama 3 menit (beban puncak/peak baseline).
     - **Ramp-down**: 30 detik menuju 0 VU.
3. **Patuhi Batas OpenVZ**:
   - Ingat bahwa limit proses (`numproc`) OpenVZ Anda adalah **500** dan proses idle saat ini sudah sekitar **306–309**. Jangan menaikkan `pm.max_children` PHP-FPM secara agresif.

---

## 1. Instalasi k6 di Komputer Lokal

### Windows
Jika menggunakan Windows, Anda bisa menginstal via winget, choco, atau download installer:
```powershell
# Menggunakan winget (Windows Package Manager)
winget install k6 --source winget

# Atau menggunakan Chocolatey
choco install k6
```
Atau unduh installer resmi `.msi` dari: https://k6.io/docs/get-started/installation/

### Linux (Ubuntu/Debian)
```bash
sudo gpg -k
sudo gpg --no-default-keyring --keyring /usr/share/keyrings/k6-archive-keyring.gpg --keyserver hkp://keyserver.ubuntu.com:80 --recv-keys C5AD17C747E3415A3642D57D77C6C491D6AC1D69
echo "deb [signed-by=/usr/share/keyrings/k6-archive-keyring.gpg] https://dl.k6.io/deb stable main" | sudo tee /etc/apt/sources.list.d/k6.list
sudo apt-get update
sudo apt-get install k6
```

### macOS
```bash
brew install k6
```

---

## 2. Cara Menjalankan Uji Beban

Buka terminal di komputer Anda, arahkan ke folder proyek, dan jalankan perintah berikut:

### A. Uji Coba Cepat (Smoke Test - 1 VU selama 10 detik)
Sebelum menjalankan tes penuh selama ~10 menit, pastikan domain dapat diakses dengan baik oleh k6:
```bash
k6 run --vus 1 --duration 10s -e TARGET_URL=https://sivana.my.id tests/k6/baseline_stress_test.js
```

### B. Menjalankan Baseline Stress Test Penuh (10 -> 25 -> 50 VU)
```bash
k6 run -e TARGET_URL=https://sivana.my.id tests/k6/baseline_stress_test.js
```

*(Catatan: Anda juga bisa menguji ke localhost jika ingin membandingkan: `-e TARGET_URL=http://127.0.0.1:8000`)*

---

## 3. Yang Harus Dipantau di VPS Saat Pengujian Berlangsung

Buka 2 tab SSH terminal ke VPS Anda saat k6 sedang berjalan:

### Terminal SSH 1: Pantau user_beancounters (Krusial untuk OpenVZ)
Jalankan perintah ini secara berulang atau dengan `watch`:
```bash
watch -n 2 "cat /proc/user_beancounters | grep -E 'numproc|physpages'"
```
**Perhatikan kolom `failcnt`:**
- Jika `numproc failcnt` bertambah (lebih dari 0), artinya VPS kehabisan kuota proses OpenVZ (mencapai 500).
- Jika `physpages failcnt` bertambah, artinya RAM VPS telah mencapai batas kuota container.
- **Kondisi ideal**: `failcnt` tetap **0** selama seluruh tahapan pengujian.

### Terminal SSH 2: Pantau Penggunaan CPU & RAM
```bash
htop
# atau jika htop belum terpasang:
top
```
- Perhatikan apakah CPU load spike mendekati 100% pada semua core.
- Perhatikan apakah ada proses PHP-FPM atau MySQL yang lambat merespons.

### Memantau Koneksi Database MariaDB / MySQL
Di terminal VPS:
```bash
mysql -e "SHOW STATUS LIKE 'Threads_connected'; SHOW STATUS LIKE 'Max_used_connections';"
```

---

## 4. Evaluasi Hasil Uji Beban k6

Setelah pengujian selesai, k6 akan menampilkan ringkasan metrik di terminal:

| Metrik | Ambang Batas Target | Penjelasan |
| :--- | :--- | :--- |
| `http_req_failed` | `< 1.00%` | Persentase request yang gagal (502 Bad Gateway, 500, timeout). |
| `http_req_duration (p95)` | `< 2000 ms` | 95% request pengunjung harus direspon di bawah 2 detik. |
| `http_reqs` | Semakin tinggi semakin baik | Total throughput (requests per second / RPS). |

### Diagnosa Kendala Umum:
1. **Muncul HTTP 502 Bad Gateway / 504 Gateway Timeout pada 50 VU**:
   - Biasanya terjadi jika antrean proses PHP-FPM penuh (`pm.max_children` terlalu kecil) atau Webuzo Nginx reverse proxy mencapai batas `fastcgi_read_timeout`.
2. **Peningkatan drastis response time (latency spike)**:
   - Terjadi bottleneck pada I/O disk atau query MySQL yang belum terindeks (index baru yang telah ditambahkan membantu meminimalkan ini).
3. **numproc failcnt meningkat**:
   - Jika failcnt bertambah, jangan naikkan `pm.max_children` lebih lanjut tanpa berdiskusi dengan penyedia VPS untuk menaikkan limit beancounters container Anda.
