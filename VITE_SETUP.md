# Setup Tailwind CSS dengan Vite

Aplikasi sekarang menggunakan Tailwind CSS via Vite (bukan CDN).

## File yang Diupdate

### 1. `tailwind.config.js`
- Menambahkan custom colors: `primary`, `secondary`, `dark`
- Menambahkan font Inter sebagai default sans-serif

### 2. `resources/css/app.css`
- Semua custom styles dipindahkan ke sini
- Google Fonts import
- Custom scrollbar styles
- Chart utilities (conic gradients)
- Dark mode styles
- Sidebar layout fixes
- Toggle switch styles

### 3. Semua View Files
- Menggunakan `@vite(['resources/css/app.css', 'resources/js/app.js'])` 
- CDN script tags dihapus
- Inline `tailwind.config` dihapus

## Development

```bash
# Run Vite dev server (untuk hot reload)
npm run dev

# Build untuk production
npm run build
```

## Production

Setelah `npm run build`, assets akan di-compile ke `public/build/` dan siap untuk production.

## Catatan

- Font Awesome masih menggunakan CDN (bisa diubah ke npm package jika diperlukan)
- Google Fonts menggunakan @import di CSS (sudah di-optimize oleh Vite)
- Semua custom styles sekarang di `app.css` untuk maintainability yang lebih baik

