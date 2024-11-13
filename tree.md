# DPW RUI Plugin File Structure

```
dpw-rui/
├── admin/
│   ├── css/
│   │   ├── sb-admin-2.css
│   │   └── foto.css            # New: CSS untuk komponen foto
│   ├── js/
│   │   ├── sb-admin-2.js
│   │   ├── dpw-rui-admin.js    # Revised: Added foto handling
│   │   ├── core.js 
│   │   └── foto.js             # New: JavaScript untuk handling foto
│   ├── views/
│   │   ├── anggota-detail.php  # Revised: Added foto card
│   │   ├── anggota-form.php
│   │   ├── anggota-foto.php    # New: Halaman manajemen foto
│   │   ├── anggota-list.php
│   │   └── templates/          # New: Directory for templates
│   │       └── foto-preview.php # New: Template preview foto
│   ├── settings.php
│   ├── general.php
│   ├── services.php
│   └── roles.php
├── includes/
│   ├── class-dpw-rui.php           # Revised: Added foto handling
│   ├── class-dpw-rui-admin-core.php
│   ├── class-dpw-rui-activator.php # Revised: Added foto table
│   ├── class-dpw-rui-deactivator.php
│   ├── class-dpw-rui-foto.php      # New: Class untuk manajemen foto
│   └── sql/                        # New: Directory for SQL files
│       └── create-foto-table.sql   # New: SQL untuk tabel foto
├── assets/                         # New: Directory for static assets
│   ├── images/
│   │   └── loading.gif            # New: Loading indicator
│   └── css/
│       └── foto-preview.css       # New: CSS untuk preview foto
├── languages/
│   └── dpw-rui.pot               # Updated with new strings
├── uploads/                       # New: Custom upload directory
│   └── .htaccess                 # New: Protection for uploads
├── changelog.txt                  # Updated with foto feature
├── dpw-rui.php                    # Revised: Added foto components
├── index.php
├── LICENSE.txt
├── README.txt                     # Updated with foto documentation
└── tree.md                        # This file, updated
```

## Key Changes:

1. New Files and Directories:
   - /admin/css/foto.css
   - /admin/js/foto.js
   - /admin/views/anggota-foto.php
   - /admin/views/templates/foto-preview.php
   - /includes/class-dpw-rui-foto.php
   - /includes/sql/create-foto-table.sql
   - /assets/* (new directory structure)
   - /uploads/* (new directory for protected uploads)

2. Modified Files:
   - class-dpw-rui.php (added foto handling)
   - class-dpw-rui-activator.php (added foto table)
   - anggota-detail.php (added foto card)
   - dpw-rui-admin.js (added foto handling)
   - changelog.txt & README.txt (updated docs)

3. Structural Changes:
   - Added /assets directory for static files
   - Added /uploads for protected user uploads
   - Added /templates for reusable view components
   - Added /sql for database management

This structure organizes the code for better:
- Separation of concerns
- File security
- Code reusability
- Asset management
- Template management