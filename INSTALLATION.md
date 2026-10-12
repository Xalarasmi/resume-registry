# Resume Registry — Local Installation

This guide describes a local development setup for **macOS** and **Windows** using MAMP. The project uses PHP, Apache, MySQL, and the PHP `pdo_mysql` extension. MAMP is not required in principle, but these steps use it for consistency.

## 1. Install MAMP

Download the appropriate macOS or Windows installer from the official [MAMP website](https://www.mamp.info/). Follow the installer instructions for your operating system, then launch MAMP and start **Apache** and **MySQL**.

MAMP's interface and default paths can vary by version and operating system. Check **Preferences / Settings** for your actual Apache Document Root and ports rather than assuming the defaults.

On macOS, the commonly used installation directory is `/Applications/MAMP/`. On Windows, it is commonly `C:\MAMP\`.

## 2. Place the project in the Document Root

Copy the repository files into the directory served by Apache, or change Apache's Document Root to point directly to the repository folder.

Two common configurations:

- **Project folder is the Document Root:** `http://localhost:8888/index.php`
- **Project is a `resume-registry` subfolder of the Document Root:** `http://localhost:8888/resume-registry/index.php`

Replace `8888` if Apache is configured to use a different port.

## 3. Prepare the database

Open phpMyAdmin from MAMP or connect to the local MySQL server with another SQL client.

1. Use a **fresh local environment** without an existing `resume_registry_test` schema containing the project's tables.
2. Import `database.sql`. It creates/uses the `resume_registry_test` database, tables, relationships, and sample institution names.
3. Optionally import `sample_data.sql` **after** `database.sql` to insert fictional profiles and the local demo account.

**Important:** The schema contains `CREATE TABLE` statements without `IF NOT EXISTS`. Do not re-import it over populated project tables; take a backup first. The sample-data script also inserts explicit IDs and is not designed for repeated imports.

The provided schema export was generated from MySQL 8.0.44. Other compatible MySQL versions may work but have not been independently verified here.

## 4. Configure the PHP database connection

Copy `config.example.php` to `config.php` in the project root. Set values matching your local MySQL setup:

```php
<?php
return [
    'host' => 'localhost',
    'port' => '8889',
    'dbname' => 'resume_registry_test',
    'username' => 'YOUR_LOCAL_DB_USER',
    'password' => 'YOUR_LOCAL_DB_PASSWORD'
];
```

Port `8889` is common for MAMP MySQL but is configurable. Use the actual credentials and port shown in your MAMP settings. `pdo.php` reads this file and creates the PDO connection using UTF-8 (`utf8mb4`).

**Never commit `config.php`.** It is excluded by `.gitignore`.

## 5. Open and test the app

Open the URL matching your Document Root configuration. If you imported the optional sample data, sign in with:

- Email: `demo@example.com`
- Password: `demo123`

For a quick smoke test, view the seeded profiles, add a temporary profile, edit it, delete it, and log out. Only the signed-in user's profiles should be visible.

The `head.php` file loads Bootstrap, jQuery, and jQuery UI from CDNs; internet access is needed for the dynamic frontend features unless these assets are hosted locally.

## Troubleshooting

| Symptom | Check |
| --- | --- |
| Browser shows 404 | Confirm the Apache Document Root, folder location, and Apache port |
| Database connection fails | Confirm MySQL is running; verify `config.php`, credentials, database name, and MySQL port |
| `could not find driver` | Enable/install PHP's `pdo_mysql` extension for the PHP version Apache is using |
| Tables already exist during import | Import into a fresh local schema; back up existing data before changing anything |
| Autocomplete or dynamic fields fail | Check internet/CDN access and the browser developer console |
| `Invalid CSRF token` | Reload the form and submit it normally with the current session cookie |
| Login fails for demo user | Ensure `sample_data.sql` was imported after `database.sql` and use the documented demo credentials |

## Linux / other local stacks

An official MAMP Linux edition is not assumed here. On Linux, configure an Apache/PHP/MySQL-compatible stack with PHP 8 and `pdo_mysql`, then follow the database and application configuration steps above. Linux setup has **not** been independently tested for this project.

## Scope

This installation guide is for **local development and coursework demonstration**. It does not provide a secure public deployment configuration.
