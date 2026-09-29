const SISWA = [ {
  id: 1,
  nama: "Ahmad Fauzi Rahman",
  kelas: "XII RPL 1"
}, {
  id: 2,
  nama: "Bunga Ayu Lestari",
  kelas: "XI TA 2"
}, {
  id: 3,
  nama: "Candra Wijaya Kusuma",
  kelas: "XII OA 1"
}, {
  id: 4,
  nama: "Dewi Kartika Sari",
  kelas: "X MA 2"
}, {
  id: 5,
  nama: "Eka Saputra",
  kelas: "XI RPL 2"
}, {
  id: 6,
  nama: "Fitriani Nur Azizah",
  kelas: "XII TA 1"
}, {
  id: 7,
  nama: "Galih Pratama",
  kelas: "X OA 1"
}, {
  id: 8,
  nama: "Hana Puspita",
  kelas: "XI MA 1"
} ];

function siswaById(id) {
  return SISWA.find(s => s.id === id);
}

let IZIN = [ {
  id: 101,
  siswaId: 1,
  alasan: "Sakit, izin ke UKS lalu pulang",
  jamKeluar: "08:15",
  rencanaMenit: 90,
  status: "Menunggu",
  sedangDiluar: false
}, {
  id: 102,
  siswaId: 2,
  alasan: "Menjemput adik yang sakit di rumah",
  jamKeluar: "09:40",
  rencanaMenit: 60,
  status: "Disetujui",
  sedangDiluar: true,
  mulaiTs: Date.now() - 42 * 6e4
}, {
  id: 103,
  siswaId: 5,
  alasan: "Keperluan administrasi di kelurahan",
  jamKeluar: "07:50",
  rencanaMenit: 45,
  status: "Disetujui",
  sedangDiluar: true,
  mulaiTs: Date.now() - 50 * 6e4
}, {
  id: 104,
  siswaId: 6,
  alasan: "Kontrol rutin ke dokter gigi",
  jamKeluar: "10:00",
  rencanaMenit: 30,
  status: "Ditolak",
  sedangDiluar: false,
  catatan: "Tidak melampirkan surat orang tua"
}, {
  id: 105,
  siswaId: 8,
  alasan: "Ambil berkas lomba di kantor pos",
  jamKeluar: "11:20",
  rencanaMenit: 40,
  status: "Menunggu",
  sedangDiluar: false
}, {
  id: 106,
  siswaId: 3,
  alasan: "Sudah kembali ke sekolah",
  jamKeluar: "07:30",
  rencanaMenit: 30,
  status: "Disetujui",
  sedangDiluar: false,
  selesai: true
} ];

let BANDING = [ {
  id: 201,
  siswaId: 4,
  tanggal: "2026-09-08",
  alasan: "Angkutan umum mogok di jalan raya",
  status: "Menunggu Validasi"
}, {
  id: 202,
  siswaId: 7,
  tanggal: "2026-09-09",
  alasan: "Membantu mengantar orang tua ke Puskesmas",
  status: "Menunggu Validasi"
}, {
  id: 203,
  siswaId: 1,
  tanggal: "2026-09-05",
  alasan: "Bangun kesiangan, tidak ada bukti pendukung",
  status: "Ditolak",
  sanksi: "Poin pelanggaran +5 & surat pernyataan"
}, {
  id: 204,
  siswaId: 6,
  tanggal: "2026-09-04",
  alasan: "Ban motor bocor, ada foto bengkel",
  status: "Diterima"
} ];

let PELANGGARAN = [ {
  id: 301,
  siswaId: 3,
  tanggal: "2026-08-20",
  jenis: "Terlambat masuk sekolah",
  poin: 5,
  catatan: "Terlambat 20 menit"
}, {
  id: 302,
  siswaId: 3,
  tanggal: "2026-08-27",
  jenis: "Atribut seragam tidak lengkap",
  poin: 5,
  catatan: "Tidak memakai dasi"
}, {
  id: 303,
  siswaId: 3,
  tanggal: "2026-09-01",
  jenis: "Bolos jam pelajaran",
  poin: 15,
  catatan: "Tidak hadir jam ke-5 & 6"
}, {
  id: 304,
  siswaId: 3,
  tanggal: "2026-09-07",
  jenis: "Merokok di lingkungan sekolah",
  poin: 40,
  catatan: "Ditemukan di kantin belakang"
}, {
  id: 305,
  siswaId: 5,
  tanggal: "2026-09-02",
  jenis: "Terlambat masuk sekolah",
  poin: 5,
  catatan: "Terlambat 10 menit"
}, {
  id: 306,
  siswaId: 5,
  tanggal: "2026-09-06",
  jenis: "Bolos jam pelajaran",
  poin: 15,
  catatan: "Tidak hadir jam ke-3"
}, {
  id: 307,
  siswaId: 2,
  tanggal: "2026-09-03",
  jenis: "Terlambat masuk sekolah",
  poin: 5,
  catatan: "Terlambat 5 menit"
}, {
  id: 308,
  siswaId: 6,
  tanggal: "2026-08-30",
  jenis: "Atribut seragam tidak lengkap",
  poin: 5,
  catatan: "Sepatu tidak sesuai"
} ];

const JENIS_PELANGGARAN = [ {
  label: "Terlambat masuk sekolah",
  poin: 5
}, {
  label: "Atribut seragam tidak lengkap",
  poin: 5
}, {
  label: "Tidak mengerjakan tugas",
  poin: 5
}, {
  label: "Bolos jam pelajaran",
  poin: 15
}, {
  label: "Berkata tidak sopan",
  poin: 15
}, {
  label: "Merokok di lingkungan sekolah",
  poin: 40
}, {
  label: "Berkelahi dengan siswa lain",
  poin: 50
}, {
  label: "Membawa benda tajam / terlarang",
  poin: 75
} ];

function totalPoin(siswaId) {
  return PELANGGARAN.filter(p => p.siswaId === siswaId).reduce((sum, p) => sum + p.poin, 0);
}

function poinBadgeClass(total) {
  if (total >= 75) return "badge-red";
  if (total >= 40) return "badge-yellow";
  return "badge-green";
}

function poinLabel(total) {
  if (total >= 75) return "Kritis";
  if (total >= 40) return "Waspada";
  return "Aman";
}
