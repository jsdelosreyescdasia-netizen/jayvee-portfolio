# Daily Report System

Laravel app for employee daily reports, weekly summaries, and monthly summaries.

## Performance Setup

For small local use, SQLite is fine. For many employees, use MySQL or MariaDB.

Recommended production database settings:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=daily_report_system
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password
```

After changing database settings, run:

```bash
php artisan migrate --force
```

## Indexes

The app includes indexes for:

- employee report date lookups
- report title and prepared-by filtering per employee
- issuance number searching
- issuance and gathered-link ordering

These keep daily, weekly, and monthly reports fast as the number of users grows.

## Development

```bash
composer install
php artisan migrate
php artisan serve
```
