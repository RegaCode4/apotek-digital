# Design System — Dashboard Apotek Digital (Soft Neubrutal Mint)

> Single source of truth untuk TAMPILAN dashboard.
> Gaya: Soft Neubrutalism + layout "Bruddle" (sidebar gelap, konten krem, card border + offset shadow, aksen teal).
> RUANG LINGKUP: HANYA tampilan. Struktur tabel, data, logic, routes TIDAK berubah.
> Stack: Laravel + Blade + Livewire Flux (+ Flux Pro) + Tailwind v4 (@theme).

## 1. Design Principles
1. Clinical but bold — tegas (border + offset shadow) tapi tetap bersih & terpercaya.
2. Satu aksen utama — teal/mint konsisten; warna lain hanya untuk status.
3. Warna = makna — success/warning/danger/info hanya untuk status.
4. Soft brutal — border 2px + offset shadow (bukan blur), radius 10-14px.
5. Readability first — angka KPI besar, hierarki jelas, kontras tinggi.
6. Light-first — token rapi agar dark mode (sudah ada via Flux) tetap konsisten.

## 2. Design Tokens

### 2.1 Colors
| Token | Value | Pemakaian |
|---|---|---|
| bg | #F7FAF9 | Background area konten |
| surface | #FFFFFF | Card, panel, modal |
| surface-muted | #F1F5F9 | Header tabel, area sekunder |
| ink | #0F172A | Teks utama |
| muted | #64748B | Teks sekunder/label |
| brutal | #0B1220 | Outline brutal |
| border-soft | #E2E8F0 | Divider halus |
| sidebar | #0B1220 | Background sidebar |
| sidebar-text | #E2E8F0 | Teks sidebar |
| sidebar-muted | #94A3B8 | Label section sidebar |
| primary | #14B8A6 | Aksen utama (teal) |
| primary-hover | #0D9488 | Hover/active |
| primary-soft | #CCFBF1 | Badge bg, active menu, row hover |
| primary-contrast | #06221F | Teks di atas primary |
| success / -soft | #22C55E / #DCFCE7 | in-stock/selesai |
| warning / -soft | #F59E0B / #FEF3C7 | menunggu/near-expiry |
| danger / -soft | #EF4444 / #FEE2E2 | expired/stock-out/failed |
| info / -soft | #3B82F6 / #DBEAFE | informasi |

### 2.2 Typography (Instrument Sans)
| Token | Size/Line/Weight | Pemakaian |
|---|---|---|
| display | 30-32px / 1.1 / 600 | Angka KPI |
| h1 | 24px / 1.2 / 600 | Judul halaman |
| h2 | 20px / 1.3 / 600 | Judul section |
| h3 | 16px / 1.4 / 600 | Judul card |
| body | 14-16px / 1.5 / 400 | Teks umum |
| label | 12-13px / 1.4 / 500, uppercase | Label KPI/section |

### 2.3 Spacing / Radius / Border / Shadow
- Spacing: 4, 8, 12, 16, 20, 24, 32 px.
- Radius: sm 8px, base 12px, lg 14px.
- Border brutal: 2px (card besar boleh 3px).
- Offset shadow: sm 2px 2px 0, base 4px 4px 0, lg 6px 6px 0 (warna = brutal).

## 3. Component Specs
- 3.1 Sidebar (dark): bg sidebar, item aktif primary-soft / underline teal, ikon outline. UTAMAKAN komponen Flux yang ada.
- 3.2 Topbar: judul h1, tombol aksi pakai .btn-brutal .btn-primary.
- 3.3 KPI card: .card-brutal, label kecil -> angka display -> sub-info. Tinted soft untuk Stok Menipis (warning-soft) & Hampir Kedaluwarsa (danger-soft).
- 3.4 Card/panel: .card-brutal, chart container ikut .card-brutal.
- 3.5 Table (HANYA styling): container .card-brutal; header bg surface-muted + border bawah 2px; row hover primary-soft. Kolom/urutan/isi TIDAK berubah.
- 3.6 Buttons: primary (teal solid), secondary (putih+border), danger; pressed effect translate(2px,2px).
- 3.7 Form: .input-brutal, focus ring teal.
- 3.8 Badge status: .badge-brutal + warna soft sesuai status (selalu pakai ikon/teks).
- 3.9 Modal/Dropdown/Alert: .card-brutal-lg, overlay rgba(11,18,32,.4).
- 3.10 Charts: garis/bar pakai primary; sekunder info/success; grid border-soft.
- 3.11 Aturan Flux: warna brand via --color-accent (teal). JANGAN ganti/wrap ulang komponen Flux yang mengubah behavior; cukup tambah class presentational + manfaatkan accent. Jangan ubah props Flux (variant, size, wire:*).

## 4. Scope File (Fase 2)
Boleh diubah (presentational): resources/css/app.css; layout dashboard; partial sidebar/topbar; komponen Blade presentational; view halaman dashboard.
TIDAK boleh disentuh: logic Livewire/Blade, routes, controller, API, query, model, migration, struktur/urutan kolom tabel, isi data, label konten, project landing, dependency baru.

## 5. Acceptance Criteria
- Tampilan jadi Soft Neubrutal Mint (sidebar gelap, bg krem, card border + offset shadow, aksen teal).
- Semua fungsi (form submit, Livewire actions, sorting/filter/pagination) tetap bekerja sama persis.
- Tidak ada perubahan struktur tabel/kolom/data/logic/routes.
- Konsisten di seluruh halaman dashboard.