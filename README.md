# Billing & Receipt Generator

A fast PHP + MySQL billing app for small businesses with mobile-friendly invoice entry and print-ready receipts.

## Features
- Dynamic invoice item rows with quantity, price, and per-item discount.
- Automatic subtotal, GST/tax, and grand total calculations.
- Optional customer name and phone capture.
- Customer invoice lookup by phone number.
- Invoice record search by date and invoice number.
- Reprint support in A4 and thermal receipt views.
- Basic secure form handling with CSRF token and escaped output.

## Stack
- PHP 8+
- MySQL 8+
- Plain HTML/CSS/JS

## Setup
1. Create database tables:
   ```bash
   mysql -u root -p < database/schema.sql
   ```
2. Configure DB credentials with env vars (optional):
   - `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`
3. Start the app:
   ```bash
   php -S 0.0.0.0:8080 -t public
   ```
4. Open `http://localhost:8080`.

## Project Structure
- `public/` UI routes (`index.php`, `records.php`, `customers.php`, `print.php`)
- `src/` database and business logic
- `config/` app + DB config
- `database/schema.sql` MySQL schema

