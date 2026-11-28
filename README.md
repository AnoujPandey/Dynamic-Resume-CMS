# Dynamic Resume CMS

Web Programming II — Assignment 2. A PHP/MySQL version of the existing Web Programming I portfolio. The public page reads the database; the protected dashboard edits profile information and resume entries and uploads a profile photo.

## Requirements

- XAMPP with PHP 8+, Apache and MySQL/MariaDB
- PHP extensions: `pdo_mysql`, `gd`, `fileinfo`, `mbstring`
- A writable `uploads/` directory

## Installation

1. Copy the project to XAMPP's `htdocs/Dynamic-Resume-CMS` directory and start Apache and MySQL.
2. For a fresh installation, open `http://localhost/phpmyadmin` and import `resume_db.sql` from the server-level Import tab. It creates `resume_db` and its three tables, then inserts the administrator, eight resume entries and five skills. **Do not import it over an existing database.** If you already have `resume_db`, back up the original project and database, retain the existing records and credentials, and copy over the updated PHP/CSS files only.
3. Check `config/database.php`. The default local XAMPP connection uses `localhost`, `resume_db`, `root` and an empty MySQL password. These settings are for local development only.
4. On macOS XAMPP, if necessary, allow the Apache user to write to `uploads/`:

   ```bash
   sudo chgrp daemon uploads
   sudo chmod 2775 uploads
   sudo -u daemon test -w uploads && echo 'Upload directory writable'
   ```

   Use the actual Apache group if your installation differs. Do not use world-writable permissions. Set PHP `upload_max_filesize` to at least 2M and `post_max_size` above 2M; the application enforces the 2 MB image limit.

5. Open `http://localhost/Dynamic-Resume-CMS/public/index.php`. Choose **Admin Login** and use the seeded administrator account. The fresh SQL installer seeds the administrator email `20253692@deu.ac.kr` with a dedicated demo password supplied separately to the evaluator. The SQL stores only the hash. Existing installations retain their current credentials if the database is not re-imported. Do not use demo credentials on a public deployment.

## Project structure

```text
Dynamic-Resume-CMS/
├── config/database.php
├── includes/
│   ├── functions.php
│   ├── header.php
│   └── footer.php
├── public/
│   ├── index.php
│   ├── login.php
│   ├── logout.php
│   ├── admin.php
│   ├── entry_form.php
│   └── delete_entry.php
├── assets/css/style.css
├── uploads/                 # Generated 150 × 150 JPEG thumbnails
├── my.jpeg                  # Neutral fallback image
├── resume_db.sql
└── README.md
```

## Database and PHP flow

- `users`: profile, email, password hash and avatar path.
- `resume_entries`: experience, education and projects linked to the user by a foreign key; optional GitHub link and end date.
- `skills`: skill name, level and display order; read dynamically on the public page.
- `database.php`: PDO exceptions, associative fetches and native prepared statements.
- `index.php`: SELECT queries and foreach loops; informative empty states.
- `login.php` / `logout.php`: password verification, regenerated session ID and session/cookie cleanup.
- `admin.php`: protected dashboard, profile update, photo upload and one shared table template.
- `entry_form.php`: one sticky form for INSERT and UPDATE with server-side validation; `delete_entry.php`: POST-only deletion after confirmation.
- `functions.php`: reusable escaping, login guard, CSRF, field validation and GD thumbnail processing.

## Security and upload checks

All dynamic SQL uses prepared statements. HTML values use `escape()` (a wrapper around `htmlspecialchars`). Admin forms check a session token (CSRF), and record updates/deletes verify the owner. The file upload checks the upload error, real HTTP upload, size, MIME type and dimensions, generates a random filename, crops/resizes with GD and stores only the thumbnail. The old image is removed after the database saves the new path. The application accepts JPEG/PNG only (2 MB; maximum 4096 pixels on either side).

## Local demonstration

Test login with invalid and valid credentials; create, edit and delete a temporary entry; verify the public page; update the profile; upload a valid image and reject an invalid file; then log out and confirm protected pages redirect to login. Skills are displayed from the database; CRUD applies to resume entries.

The included SQL is a fresh-install script reconstructed from the verified existing schema and corrected resume records. A valid 150 × 150 portrait from the existing project is included. The original full-size photo was not needed; a neutral fallback image is provided. If you make further local changes, export the current database before submission (the dump contains personal resume records and a password hash):

```bash
/Applications/XAMPP/xamppfiles/bin/mysqldump -u root --single-transaction --default-character-set=utf8mb4 --databases resume_db > resume_db.sql
```

Submit the complete project with the SQL dump and current images, using the professor's filename convention `StudentID_LastName_DynamicResume.zip`. Provide the evaluator the demo login credentials separately. Before submission, verify the database import, login, CRUD and a real GD image upload in XAMPP. Keep the submission and backups private unless you intentionally use public demo data and credentials.

**Author:** Pandey Anouj — Intelligence Computing, Dong-eui University.
