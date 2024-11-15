ganti upload foto menjadi php native


Dari review kode yang ada, beberapa file yang perlu diperhatikan untuk fitur foto:

1. File yang sudah ada dan perlu dimodifikasi:
- `/admin/views/anggota-detail.php` - Menampilkan foto anggota
- `/admin/views/anggota-form.php` - Form upload foto
- `/includes/class-dpw-rui-foto.php` - Class utama pengelolaan foto

2. File CSS/JS terkait foto:
- `/admin/css/foto.css` - Styling komponen foto
- `/admin/js/foto.js` - Handling upload dan preview

3. Template foto:
- `/admin/views/templates/foto-preview.php` - Template preview foto

4. File baru yang perlu dibuat:
- `/admin/views/anggota-foto.php` - Halaman khusus manajemen foto

