# Resume Registry

Resume Registry is an educational PHP/MySQL web application developed as part of the Coursera **Web Applications for Everybody** coursework. It lets signed-in users manage their own résumé profiles, including education and work experience.

## Features

- Session-based login and logout with password verification (`password_verify`)
- User-specific résumé profiles with create, read, update, and delete (CRUD) operations
- Dynamic education and work-position fields using jQuery
- Institution autocomplete through jQuery UI and a JSON endpoint
- Server-side form validation and PDO prepared statements
- Database transactions when creating or updating profiles and related records
- CSRF tokens on login, logout, add, edit, and delete forms
- Ownership checks to restrict access to another user's profiles

## Technology stack

- PHP 8 (tested syntax with the PHP CLI; local database export reports PHP 8.3.30)
- MySQL 8 (the provided export reports MySQL 8.0.44)
- PDO with `pdo_mysql`
- HTML, JavaScript, jQuery, jQuery UI, JSON, Bootstrap 3
- Apache and MySQL through MAMP for local development

The frontend libraries are loaded from public CDNs in `head.php`; an internet connection is needed for those resources unless they are hosted locally.

## Quick start

1. Install and start MAMP (Apache and MySQL).
2. Put the project files in the configured Apache Document Root.
3. Import `database.sql` into a **fresh** local database setup, followed by `sample_data.sql` if you want the fictional demo records.
4. Copy `config.example.php` to `config.php` and enter your local MySQL credentials and port.
5. Open the application in your browser. If the Document Root is the project folder itself and Apache uses port `8888`, visit **http://localhost:8888/index.php**. If the project is a subfolder of the Document Root, include that subfolder in the URL.

See [INSTALLATION.md](INSTALLATION.md) for detailed macOS/Windows instructions and troubleshooting.

### Local demo account

If you import `sample_data.sql`, use:

- Email: `demo@example.com`
- Password: `demo123`

**For local demonstration only.** Do not publish this account on an internet-facing deployment.

## Main project files

| File | Purpose |
| --- | --- |
| `index.php` | Lists profiles belonging to the signed-in user |
| `add.php`, `edit.php`, `view.php`, `delete.php` | Profile CRUD operations |
| `login.php`, `logout.php` | Authentication and session logout |
| `util.php` | Form validation, related-record insertion, CSRF helpers |
| `school.php` | JSON institution autocomplete |
| `pdo.php` | PDO database connection using local configuration |
| `head.php` | Frontend CDN includes |
| `database.sql` | Database schema and sample institution names |
| `sample_data.sql` | Fictional local demo account and profiles |
| `config.example.php` | Template for local database settings |

## Security and limitations

The project uses hashed passwords, prepared SQL statements, session ID regeneration after login, user ownership checks, CSRF validation for important POST operations, and database transactions for multi-table writes. Logout requires a POST request with a valid CSRF token.

This is a **coursework demonstration, not a production-ready service**. Further work would include stronger input validation, login rate limiting, hardened session/cookie settings, broader automated testing, and review/upgrades of older CDN frontend dependencies. The example credentials must not be used on a public server.

Never commit `config.php` or real credentials. The supplied `.gitignore` excludes `config.php`.

## Verification notes

The supplied PHP files passed `php -l` syntax checks. The demo password was verified against the supplied hash. The developer also manually tested the core CRUD and CSRF flows in MAMP. A complete independent integration/security test against a running MySQL and web server was **not** performed as part of this documentation review.
