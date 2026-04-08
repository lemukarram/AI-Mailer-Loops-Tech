# cPanel Deployment Guide for aiMailSaas

This guide explains how to deploy this application on a standard shared hosting environment using cPanel.

## Prerequisites
- **PHP Version:** 8.1 or 8.2 (Recommended)
- **PHP Extensions:** `pdo_mysql`, `curl`, `openssl`, `mbstring`, `gd`, `zip`
- **MySQL:** Version 8.0 or MariaDB equivalent

---

## 1. Upload Files
1. Compress your project folder into a `.zip` file (excluding `node_modules`, `tests`, and `.git` if present).
2. Open cPanel **File Manager**.
3. Upload the `.zip` to your `public_html` or a subdirectory.
4. Extract the files.

## 2. Set the Document Root (CRITICAL)
The application is designed to serve content from the `public/` directory for security.
- **If using a Subdomain:** Set the Document Root to `/public_html/your-folder/public`.
- **If using the Primary Domain:** 
    - You may need to move the contents of `public/` to `public_html` and the other folders (`src`, `config`, etc.) to one level *above* `public_html` for maximum security.
    - Alternatively, add a `.htaccess` in `public_html` to redirect all traffic to the `public/` folder.

## 3. Database Setup
1. Go to **MySQL® Database Wizard** in cPanel.
2. Create a database (e.g., `username_aimailsaas`).
3. Create a user and generate a strong password.
4. Add the user to the database with **ALL PRIVILEGES**.

## 4. Configuration (.env)
1. In the root of your project folder, find `.env.example`.
2. Rename it to `.env`.
3. Edit the `.env` file with your production details:
   ```env
   DB_HOST=localhost
   DB_NAME=username_aimailsaas
   DB_USER=username_dbuser
   DB_PASS=your_strong_password
   ADMIN_KEY=A_VERY_LONG_RANDOM_STRING_FOR_ENCRYPTION
   APP_URL=https://yourdomain.com/
   ```
   *Note: `ADMIN_KEY` is used for encrypting your API keys in the database. If you change this later, existing keys will become unreadable.*

## 5. Run Database Migrations
Open your browser and navigate to:
`https://yourdomain.com/migrate.php?key=YOUR_ADMIN_KEY`
(Replace `YOUR_ADMIN_KEY` with the string you set in the `.env` file).

If successful, you will see a list of tables created and a message: "Migration completed successfully."

## 6. Configure Cron Job
To automate the email queue, you must set up a Cron Job in cPanel:
1. Go to **Cron Jobs** in cPanel.
2. Select **Once Per Minute** (`* * * * *`) for the frequency.
3. Enter the following command (adjust path to your cPanel username):
   ```bash
   /usr/local/bin/php /home/username/public_html/cron/send_queue.php > /dev/null 2>&1
   ```
   *Note: Check your cPanel "PHP Selector" to ensure the command line PHP version matches your website's version.*

---

## Security Best Practices for Production
- **Delete `migrate.php`:** After the initial setup, it is highly recommended to delete `public/migrate.php` or rename it to something impossible to guess.
- **SSL:** Ensure your site is running on `HTTPS`.
- **API Keys:** Only enter your OpenAI/Gemini keys through the application's Settings page (they will be encrypted).
- **Permissions:** Ensure folder permissions are set to `755` and files to `644`.
