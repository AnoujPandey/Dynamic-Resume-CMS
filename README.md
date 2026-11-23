# Dynamic Resume CMS

A database-driven portfolio and resume Content Management System built for Web Programming II using PHP 8+, MySQL, PDO, and PHP GD.

The project re-engineers a static Web Programming I resume into a dynamic web application with:

- A public read-only portfolio
- Administrator authentication
- Full CRUD operations
- Dynamic education, experience, projects, and skills
- Secure profile image upload
- Server-side 150 × 150 thumbnail generation using PHP GD

---

## Technologies Used

- PHP 8+
- MySQL / MariaDB
- PDO
- PHP GD
- HTML5
- CSS3
- XAMPP
- phpMyAdmin

---

## Main Features

### Public Resume

The public portfolio displays data dynamically from MySQL, including:

- Full name
- Professional / academic headline
- Career objective
- Profile image
- Education
- Work experience
- Projects
- Technical skills
- GitHub project links

Resume entries and skills are rendered using PHP loops rather than hardcoded HTML.

Empty sections display informative messages instead of PHP warnings or broken layouts.

---

### Administrator Authentication

The administrator can log in using the registered email address and password.

Authentication includes:

- Email validation
- PDO prepared statements
- `password_verify()`
- Session-based authentication
- `session_regenerate_id(true)`
- Protected admin pages
- Secure logout and session destruction

The administrator password is stored only as a password hash in MySQL.

---

### Admin Dashboard

The protected admin dashboard allows the administrator to:

- View resume entries
- Create new entries
- Edit existing entries
- Delete entries
- Update profile information
- Upload and replace the profile image
- Access the public resume
- Log out securely

---

## CRUD Operations

The application provides complete CRUD functionality for resume entries.

### Create

New education, experience, or project entries can be created using the admin form.

### Read

Resume records are retrieved from MySQL and displayed in both the public resume and admin dashboard.

### Update

Existing entries can be loaded using a validated integer ID and updated using PDO prepared statements.

### Delete

Entries are deleted only through POST requests after client-side confirmation and server-side ID validation.

---

## Profile Image Processing

Profile image uploads are processed securely on the server.

The upload system:

- Accepts JPEG and PNG images only
- Checks `UPLOAD_ERR_OK`
- Uses `is_uploaded_file()`
- Limits images to 2 MB
- Verifies MIME type using `finfo`
- Does not trust the original filename
- Generates a random filename using `random_bytes()`
- Generates a real 150 × 150 thumbnail using PHP GD
- Stores the image inside `uploads/`
- Saves the relative image path in MySQL
- Deletes the previous avatar when it is replaced

The generated thumbnail is created using GD functions such as:

- `imagecreatefromjpeg()`
- `imagecreatefrompng()`
- `imagecreatetruecolor()`
- `imagecopyresampled()`
- `imagejpeg()`

---

## Security

The application includes several security controls:

- PDO prepared statements
- Disabled PDO emulated prepares
- `PDO::ERRMODE_EXCEPTION`
- `PDO::FETCH_ASSOC`
- Password hashing
- Password verification
- Session authentication
- Session ID regeneration
- POST-only deletion
- Integer ID validation
- Server-side upload validation
- Secure random upload filenames
- XSS protection using:

```php
htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
```

---

## Project Structure

```text
Dynamic-Resume-CMS/
│
├── config/
│   └── database.php
│
├── includes/
│   ├── functions.php
│   ├── header.php
│   └── footer.php
│
├── public/
│   ├── index.php
│   ├── login.php
│   ├── logout.php
│   ├── admin.php
│   ├── entry_form.php
│   └── delete_entry.php
│
├── assets/
│   └── css/
│       └── style.css
│
├── uploads/
│   ├── .gitkeep
│   └── generated-avatar.jpg
│
├── resume_db.sql
├── resume.html
├── my.jpeg
└── README.md
```

---

## Database

Database name:

```text
resume_db
```

Character set and collation:

```text
utf8mb4
utf8mb4_unicode_ci
```

The main tables are:

### users

Stores:

- Administrator profile
- Email
- Password hash
- Headline
- Career summary
- Profile image path

### resume_entries

Stores:

- Education
- Experience
- Projects
- Organizations
- Dates
- Descriptions
- GitHub project links

### skills

Stores technical skills associated with the resume owner.

---

## Installation

### 1. Install XAMPP

Make sure Apache and MySQL are running.

The project was developed using XAMPP with PHP 8.2.

PHP GD and file information support must be enabled.

---

### 2. Copy the Project

Place the project inside the XAMPP `htdocs` directory.

Example on macOS:

```text
/Applications/XAMPP/xamppfiles/htdocs/Dynamic-Resume-CMS
```

---

### 3. Import the Database

Open phpMyAdmin:

```text
http://localhost/phpmyadmin
```

Import:

```text
resume_db.sql
```

The SQL dump contains the required database tables and sample resume records.

---

### 4. Database Configuration

The PDO connection is configured in:

```text
config/database.php
```

Default local XAMPP settings used by this project:

```text
Host: localhost
Database: resume_db
Username: root
Password: empty
```

Update these settings if your MySQL configuration is different.

---

### 5. Upload Folder Permissions

The `uploads/` directory must be writable by the web server.

On a local macOS XAMPP development environment, permissions can be adjusted if necessary:

```bash
chmod 777 uploads
```

This setting is intended for local development only.

---

## Running the Application

### Public Resume

Open:

```text
http://localhost/Dynamic-Resume-CMS/public/index.php
```

### Administrator Login

Open:

```text
http://localhost/Dynamic-Resume-CMS/public/login.php
```

Use the administrator credentials stored for the project.

The password itself is not documented in this repository.

### Admin Dashboard

After successful authentication:

```text
http://localhost/Dynamic-Resume-CMS/public/admin.php
```

Unauthenticated access to the admin pages redirects to the login page.

---

## Required PHP Extensions

The project requires:

- `pdo_mysql`
- `gd`
- `fileinfo`

GD can be checked with:

```bash
/Applications/XAMPP/xamppfiles/bin/php -m | grep -i gd
```

---

## GD Thumbnail Verification

Generated profile images are stored inside:

```text
uploads/
```

The required thumbnail size is:

```text
150 × 150 pixels
```

On macOS, the dimensions can be verified with:

```bash
sips -g pixelWidth -g pixelHeight uploads/*.jpg
```

---

## Database Export

The database can be exported using XAMPP's `mysqldump` command:

```bash
/Applications/XAMPP/xamppfiles/bin/mysqldump \
-u root \
--default-character-set=utf8mb4 \
--databases resume_db \
> resume_db.sql
```

---

## Assignment Purpose

This project was developed for Web Programming II.

Its purpose is to demonstrate:

- PHP server-side programming
- MySQL database integration
- PDO
- Authentication and sessions
- Full CRUD operations
- File upload security
- GD image processing
- Dynamic HTML rendering
- XSS mitigation
- Modular PHP application structure

---

## Author

**Pandey Anouj**

B.Sc. Intelligence Computing  
Dong-eui University  
Busan, South Korea