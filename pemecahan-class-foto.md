
Berikutnya saya akan buat struktur folder template baru dan memindahkan file yang sudah ada:

1. Buat folder baru:
```
/admin/views/templates/foto/
```

2. File yang akan dipindahkan:
```
FROM: /admin/views/templates/foto-preview.php
TO:   /admin/views/templates/foto/preview.php
```

3. File baru yang akan dibuat:
```
/admin/views/templates/foto/
  - upload-form.php
  - grid-manage.php
  - message-display.php
```

Apakah saya boleh lanjut membuat component template-template tersebut?