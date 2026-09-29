// Shared demo data for the "guru" (teacher) dashboard pages.

const GURU_KELAS = [
  { kode: "XII RA", jurusan: "Rekayasa Perangkat Lunak", siswa: 36, hari: "Senin & Rabu" },
  { kode: "XII TA", jurusan: "Tekstil", siswa: 36, hari: "Senin & Selasa" },
  { kode: "XII OA", jurusan: "Ototronik", siswa: 35, hari: "Selasa & Rabu" },
  { kode: "XII MA", jurusan: "Teknik Mesin", siswa: 36, hari: "Rabu & Kamis" }
];

const GURU_SISWA = [
  "Ahyar Rosadi", "Amalia Lestari", "Baiq Seprita", "Danil Ansari", "Eka Hirmayani Agustina"
];

const GURU_RUBRIK = [
  {
    nama: "Rubrik Nilai Ulangan Harian 1",
    tipe: "Ulangan Harian",
    mapel: "Matematika",
    kriteria: [
      { nama: "Dijabarkan Cara Pengerjaannya", nilai: 50 },
      { nama: "Jawaban Benar", nilai: 40 },
      { nama: "Kejujuran", nilai: 10 }
    ]
  },
  {
    nama: "Rubrik Nilai Ulangan Harian 2",
    tipe: "Ulangan Harian",
    mapel: "Matematika",
    kriteria: [
      { nama: "Dijabarkan Cara Pengerjaannya", nilai: 40 },
      { nama: "Jawaban Benar", nilai: 50 },
      { nama: "Kejujuran", nilai: 10 }
    ]
  },
  {
    nama: "Rubrik Nilai Tugas 3",
    tipe: "Tugas",
    mapel: "Tugas",
    kriteria: [
      { nama: "Ketepatan Waktu", nilai: 20 },
      { nama: "Kelengkapan", nilai: 40 },
      { nama: "Kerapian", nilai: 40 }
    ]
  }
];
