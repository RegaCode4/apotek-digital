<aside>
📋

Dokumen acuan implementasi (locked). Kerangka utama: **Smart Table** + drawer detail untuk multi-batch. Stack: **Laravel + Livewire Flux + Tailwind v4**. Gaya visual: **Soft Neubrutal Mint**.

</aside>

## 1. Konteks & Masalah

Menu Stok Opname (SO) saat ini menampilkan satu tabel flat urut A–Z tanpa pagination. Ini melawan alur kerja nyata apotek yang menghitung stok **fisik** mengikuti pengelompokan, bukan abjad global.

Masalah yang dipecahkan:

- Tabel flat A–Z membuat petugas loncat-loncat saat hitung fisik.
- Tidak ada grouping walau data punya field **kategori**.
- Tidak ada kontrol kadaluarsa (ED) / FEFO.
- Item dengan selisih ≠ 0 tenggelam di antara ratusan baris "0".
- Tanpa pagination & indikator progres — SO tak bisa dijeda-lanjut.
- Sorting berbasis teks, bukan angka ("250 mg" muncul sebelum "5 mg").

## 2. Tujuan (Prioritas)

1. Mempercepat proses hitung fisik.
2. Memudahkan deteksi selisih / kehilangan stok.
3. Kontrol kadaluarsa (FEFO).
4. Tampilan rapi & mudah dibaca.

## 3. Kondisi Data Saat Ini

- Field obat: nama, **kategori**, stok sistem.
- **Belum ada** field lokasi/rak.
- **Belum ada** field Expired Date (ED) maupun batch.
- Menu SO: kolom Nama Obat | Stok Sistem | Stok Fisik (input) | Selisih (auto) + field "Alasan Penyesuaian" global.

## 4. Keputusan Arsitektur

- Kerangka utama: **Smart Table** (data flat cerdas) dengan sticky filter header.
- **Kategori** berfungsi sebagai proxy pengganti rak untuk memandu hitung fisik.
- Sisipkan **panel detail (drawer / expandable row)** hanya untuk obat yang butuh input multi-batch/ED — hindari split-view penuh yang memperlambat kasus umum.

## 5. Koreksi Final Wajib

<aside>
⚠️

Empat koreksi ini menyentuh fondasi (performa & integritas data) yang mahal diubah belakangan. **Prioritaskan #1 dan #3.**

</aside>

### #1 — `StockOpnameRow` BUKAN komponen Livewire penuh per baris

Ratusan komponen Livewire bersarang = ratusan payload state + round-trip → membunuh tujuan kecepatan.

- **Koreksi:** baris = **Blade partial + Alpine.js**. Semua micro-interaction (auto-select, hitung selisih, navigasi Enter, highlight dim/pink) ditangani **Alpine di client** tanpa hit server. Cukup **satu** komponen Livewire `StockOpnameTable` yang sinkron saat blur/jeda.

### #2 — `wire:model.defer` sudah usang di Livewire 3 (Flux)

Di Livewire 3, `wire:model` sudah *deferred by default*; `.defer` tidak ada lagi.

- **Koreksi:** gunakan `wire:model` (deferred) atau `wire:model.blur`. Hindari `wire:model.live` pada input fisik.

### #3 — Penyelesaian SO menulis ke ledger mutasi, bukan menimpa stok

Menimpa stok langsung menghapus jejak audit (Permenkes 73/2016 = tertib administrasi). Sudah ada menu **Mutasi Stok**.

- **Koreksi:** saat SO selesai, buat **record penyesuaian stok (adjustment)** ke tabel mutasi — nilai selisih + alasan + user + timestamp. Stok terhitung dari ledger. Sekaligus memenuhi tujuan deteksi selisih secara auditable.

### #4 — Natural sort: hindari sort runtime PHP untuk data besar

`sortBy(SORT_NATURAL)` rapuh saat SKU tumbuh.

- **Koreksi (opsional, bersih):** tambah kolom **`sort_key`** (nama dinormalisasi / dosis diangkakan dengan padding) yang di-generate saat simpan data obat, lalu `ORDER BY sort_key` di DB.

## 6. Rencana Eksekusi Bertahap

### FASE 1 — Tanpa mengubah struktur data (kerjakan lebih dulu)

- [ ]  Setup design tokens Soft Neubrutal Mint + layout dasar (Header, Sticky Filter, Area Tabel).
- [ ]  State kosong / "Belum pernah SO" + tombol "Mulai SO Baru".
- [ ]  Alur jeda-lanjut: draf di server (kanonik) + banner sesi berjalan (progress %, [Lanjutkan] [Buang Draf]).
- [ ]  Smart Table + sticky filter; sorting default Kategori → alfabetis (natural). Tidak re-sort otomatis saat baris diedit.
- [ ]  Filter pill neubrutal: Status [Semua][Belum Dihitung][Sesuai][Ada Selisih] + filter Kategori.
- [ ]  Opsi sort: Selisih terbesar. (Sort ED ditunda ke Fase 2.)
- [ ]  Highlight selisih (blok pink) + dim untuk baris sesuai.
- [ ]  Micro-interaction (Alpine): tombol "Match", auto-select text, navigasi Enter ke baris bawah.
- [ ]  Alasan per-item (dropdown mini) hanya pada baris selisih ≠ 0; pertahankan alasan global sebagai catatan sesi.
- [ ]  Progress bar "blocky" dinamis.
- [ ]  Penyelesaian SO → tulis adjustment ke ledger Mutasi Stok (koreksi #3).
- [ ]  Penanda kontras Narkotika/Psikotropika (jika field golongan tersedia).

### FASE 2 — Setelah menambah field ED & batch

- [ ]  Migrasi DB: tambah kolom/tabel Batch & Expired Date.
- [ ]  Badge ED pada baris: `[!] ED < 3 Bln` (merah bata), `ED > 1 Thn` (hijau mint).
- [ ]  Opsi sort "ED terdekat" (FEFO).
- [ ]  Drawer detail untuk input multi-batch + ED per batch.

## 7. Struktur File & Komponen

<aside>
✅

**Fase 1 — cukup SATU file Livewire.** Pertahankan pendekatan single-file yang sudah ada; jangan dipecah dulu. Yang dihindari koreksi #1 adalah menjadikan **tiap baris** komponen Livewire terpisah (anti-pattern), BUKAN satu file besar. Untuk input responsif tanpa lag, ganti `wire:model.live` per input → `wire:model.blur`, atau tangani di Alpine dan sinkron saat blur/simpan.

</aside>

Tabel di bawah adalah **opsi refactor saat skala tumbuh (Fase 2+)**, bukan keharusan Fase 1. Bila drawer batch, filter kompleks, atau kebutuhan reuse muncul, pecah jadi **Blade partial (`@include`)** lebih dulu — bukan komponen Livewire bersarang.

| Komponen | Tipe | Tanggung jawab |
| --- | --- | --- |
| `StockOpnameManager` | Livewire (root) | State sesi global: ID sesi, draf, jeda/lanjut, progres global |
| `StockOpnameHeader` | Blade | Progress bar blocky, alasan global, tombol "Jeda" / "Selesaikan SO" |
| `StockOpnameFilter` | Blade (sticky) | Pill filter Status & Kategori; trigger re-sort manual |
| `StockOpnameTable` | **Livewire (satu-satunya)** | Loop data, terima filter, sinkron saat blur/jeda |
| `StockOpnameRow` | **Blade partial + Alpine** | Micro-interaction per baris, highlight, navigasi keyboard (client-side) |
| `StockOpnameBatchDrawer` | Livewire (Fase 2) | Panel slide-out / expandable untuk obat multi-batch |

## 8. Design Tokens (Tailwind v4 — Soft Neubrutal Mint)

```css
@theme {
  /* Colors */
  --color-neo-mint: #A7F3D0;       /* Mint utama (background/hero) */
  --color-neo-mint-light: #D1FAE5; /* Mint pastel (baris Sesuai) */
  --color-neo-pink: #FECDD3;       /* Pink pastel (baris Selisih) */
  --color-neo-yellow: #FEF08A;     /* Kuning (banner Jeda/Peringatan) */
  --color-neo-black: #171717;      /* Hitam tegas: teks & border */

  /* Borders & Radius */
  --border-width-neo: 2px;
  --border-color-neo: var(--color-neo-black);
  --border-radius-neo: 12px;

  /* Hard Shadows (ciri neubrutalism) */
  --shadow-neo-sm: 2px 2px 0px var(--color-neo-black);
  --shadow-neo-md: 4px 4px 0px var(--color-neo-black);
  --shadow-neo-lg: 6px 6px 0px var(--color-neo-black);
}
```

## 9. Definisi State Per Baris

| State | Kondisi | Perilaku Visual |
| --- | --- | --- |
| **Belum Dihitung** | Input kosong / belum disentuh | Background putih. Border bottom 1px. Teks normal. |
| **Dihitung (Sesuai)** | `stok_fisik == stok_sistem` | Dim (opacity 60–70%), background `neo-mint-light`, ikon centang tebal. Tidak meloncat/pindah. |
| **Dihitung (Selisih)** | `stok_fisik != stok_sistem` | Pop-out: background `neo-pink`, border kotak 2px hitam, opacity 100%. Dropdown "Alasan" wajib. |
| **Narkotika (Flag)** | Golongan = Narkotika/Psikotropika | Ikon/lencana kontras di samping nama. (Butuh field golongan.) |

## 10. Risiko & Mitigasi

| Risiko | Mitigasi |
| --- | --- |
| Beban server dari autosave per Enter | Handle input di Alpine (client); sinkron ke Livewire hanya saat blur/jeda; draf server kanonik, localStorage buffer |
| Natural sort lambat untuk data besar | Kolom `sort_key` ter-generate + `ORDER BY` di DB (koreksi #4) |
| Bingung "sudah input sampai mana" (no re-sort real-time) | Dim + mint pastel untuk baris selesai; tombol "Rapikan / Terapkan Filter" untuk re-sort manual |
| Integritas data & audit | Tulis adjustment ke ledger Mutasi Stok, jangan timpa stok (koreksi #3) |

- [ ]  Perkiraan jumlah SKU (menentukan strategi sort & pagination).

## 11. Perlu Diverifikasi

- [ ]  Apakah field **kategori** memuat **golongan** (bebas/keras/narkotika/psikotropika)? Jika tidak, flag narkotika masuk Fase 2.
- [ ]  Source of truth draf = tabel server (localStorage hanya buffer).
- [ ]  Ketersediaan field barcode & perangkat scanner (opsional, prioritas rendah).