# aiMailSaas: AI-Powered Professional Email Outreach Platform

**aiMailSaas** is a robust, secure, and highly scalable SaaS platform designed for high-conversion email outreach. By leveraging the latest LLMs (OpenAI GPT-4o & Gemini 1.5 Pro), it generates 100% humanized, catch-free, and professional email content that bypasses AI detectors.

---

## 🚀 Key Features

- **Dual LLM Integration:** Choose between OpenAI and Gemini for your outreach.
- **Humanized Tone Enforcement:** Strict system prompts ensure a soft, catchy, and non-robotic tone.
- **Variable Substitution:** Personalize emails using `[contact_name]`, `[company]`, `[designation]`, etc.
- **Cron-Based Queue:** Background sending system that respects hourly limits.
- **Manual Overrides:** Generate and send AI emails instantly from the dashboard.
- **Secure Data Management:** Smart CSV import with automatic column mapping.
- **Enterprise-Grade Security:** AES-256-CBC encryption for API keys/SMTP passwords and CSRF protection on all endpoints.

---

## 🛠 Tech Stack
- **Backend:** Core PHP 8.1+ (Object-Oriented)
- **Database:** MySQL 8.0 / MariaDB
- **Frontend:** Bootstrap 5, DataTables, jQuery
- **Security:** AES-256 encryption, CSRF Protection, .htaccess Hardening

---

## 📥 Installation Guide (cPanel / Shared Hosting)

### Step 1: Upload Files
1. Upload all files directly to your `public_html` directory.
2. Ensure the `.htaccess` file is present in the root to protect your core folders.

### Step 2: Database Setup
1. Create a new MySQL Database and User in your cPanel.
2. Grant the user **ALL PRIVILEGES** to the database.

### Step 3: Configuration (.env)
1. Rename `.env.example` to `.env`.
2. Edit the `.env` file with your database credentials:
   ```env
   DB_HOST=localhost
   DB_NAME=your_db_name
   DB_USER=your_db_user
   DB_PASS=your_db_password
   ADMIN_KEY=your_secure_random_key (Required for encryption)
   APP_URL=https://yourdomain.com/
   ```

### Step 4: Run Database Migrations
Initialize your database by visiting the secure migration endpoint:
`https://yourdomain.com/migrate.php?key=YOUR_ADMIN_KEY`
*(Replace YOUR_ADMIN_KEY with the value set in your .env file)*

---

## ⏰ Cron Job Setup (CRITICAL)

To automate the email queue, you must set up a Cron Job in your cPanel.

1. In cPanel, search for **Cron Jobs**.
2. Set the frequency to **Once Per Minute** (`* * * * *`).
3. Enter the following command (adjust the path to your cPanel username):
   ```bash
   /usr/local/bin/php /home/your_username/public_html/cron/send_queue.php > /dev/null 2>&1
   ```
   *Note: Ensure the PHP version used in the cron command matches your website's PHP version (8.1 or 8.2).*

---

## 🏁 How to Get Started

1. **Login:** Use the default credentials at `https://yourdomain.com/login.php`
   - **Email:** `admin@example.com`
   - **Password:** `admin_pass`
   - *Note: Change your password immediately after logging in.*
2. **Configure Settings:** Go to **Settings** and enter your OpenAI/Gemini API keys and SMTP credentials. These will be encrypted automatically.
3. **Upload Your List:** Upload a CSV file via the **Dashboard**. (Required columns: `contact name`, `email`).
4. **Start Sending:** 
   - Use **AI Generate** on any row to test a single email.
   - Click **Start Queue** to begin background sending via the cron job.

---

## 🧪 Testing

To verify the core logic (encryption, variable replacement, auth) is working:
```bash
php tests/run_tests.php
```

---

## 🛡 Security
- **Sensitive Folders:** Access to `src/`, `config/`, `cron/`, etc., is blocked via `.htaccess`.
- **Encryption:** All third-party API keys and SMTP passwords are encrypted in the database.
- **CSRF:** All forms and AJAX requests are protected with CSRF tokens.
- **Migration:** Delete or rename `migrate.php` after the initial setup.

---

## 🛡 License
This project is licensed under the MIT License.
