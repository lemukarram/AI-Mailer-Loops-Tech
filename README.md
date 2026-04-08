# aiMailSaas: AI-Powered Professional Email Outreach Platform

**aiMailSaas** is a robust, secure, and highly scalable SaaS platform designed for high-conversion email outreach. By leveraging the latest LLMs (OpenAI GPT-4o & Gemini 1.5 Pro), it generates 100% humanized, catch-free, and professional email content that bypasses AI detectors and resonates with recipients. Built with **Core PHP** for maximum compatibility and performance on shared or dedicated hosting environments.

---

## 🚀 Key Features

### 🤖 AI-Driven Campaign Generation
- **Dual LLM Integration:** Choose between OpenAI and Gemini for your outreach.
- **Humanized Tone Enforcement:** Strict system prompts ensure a soft, catchy, and non-robotic tone (no emojis, clean punctuation).
- **Variable Substitution:** Personalize emails using `[contact_name]`, `[company]`, `[designation]`, and more.
- **JSON Structured Output:** Ensures consistent subject, body, and footer formatting every time.

### 📧 Scalable Sending Engine
- **Cron-Based Queue:** A background sending system that respects hourly limits to protect your SMTP reputation.
- **Manual Overrides:** Send individual AI-generated emails instantly from the dashboard.
- **SMTP Management:** Securely test and manage multiple SMTP configurations.

### 👥 Multi-Role Management
- **Admin Dashboard:** Monitor all users, toggle account status, and set global platform limits (send rates, file sizes, list lengths).
- **User Dashboard:** Real-time analytics for total list size, emails sent, and queue status.

### 📂 Secure Data & List Management
- **Smart CSV Import:** Easily upload and replace mailing lists with automatic column mapping.
- **DataTables Integration:** Advanced search, sort, and status filtering for your contacts.
- **Admin Limits:** Enforce maximum file sizes and row counts for data integrity.

### 🔒 Enterprise-Grade Security
- **Role-Based Access Control (RBAC):** Secure Admin and User zones.
- **CSRF & SQL Injection Protection:** Built with security-first coding practices.
- **Secure Migration System:** One-click database initialization via a secured admin key.

---

## 🛠 Tech Stack
- **Backend:** Core PHP 8.2 (Object-Oriented)
- **Database:** MySQL 8.0
- **Frontend:** Bootstrap 5, DataTables, jQuery
- **Deployment:** Docker, Apache (mod_rewrite)

---

## ⚙️ Configuration

The project uses environment variables for easy configuration.

1. **Copy the example file:**
   ```bash
   cp .env.example .env
   ```
2. **Edit `.env`:** Update your database credentials and `ADMIN_KEY`.

For Docker deployments, the `docker-compose.yml` automatically uses the settings in your `.env` file if it exists.

---

## 📥 Installation Guide

### Prerequisites
- [Docker](https://www.docker.com/get-started)
- [Docker Compose](https://docs.docker.com/compose/install/)

### Step 1: Clone the Repository
```bash
git clone https://github.com/your-repo/aiMailSaas.git
cd aiMailSaas
```

### Step 2: Spin Up the Environment
```bash
docker-compose up -d
```
This will start:
- **App Service:** `http://localhost:8080`
- **phpMyAdmin:** `http://localhost:8081`
- **MySQL:** `localhost:3306`

### Step 3: Run Database Migrations
Initialize your database schema by visiting the secure migration endpoint:
`http://localhost:8080/migrate.php?key=SECURE_ADMIN_KEY`

---

## 🏁 How to Get Started

1. **Login:** Use the default credentials at `http://localhost:8080/login.php`
   - **Email:** `admin@example.com`
   - **Password:** `admin_pass`
2. **Configure Settings:** Navigate to the **Settings** page and enter your OpenAI/Gemini API keys and SMTP credentials.
3. **Upload Your List:** Prepare a CSV file with `contact name` and `email` columns and upload it via the **Dashboard**.
4. **Set Up a Campaign:** (Optional) Define your base AI prompt or manual template.
5. **Start Sending:** 
   - Use the **AI Generate** button on any contact for a manual send.
   - Click **Start Queue** to let the background engine handle the bulk sending.

---

## 🧪 Testing

The project includes a custom-built unit testing suite to ensure core logic (authentication, variable replacement, AI tone enforcement) is always functioning correctly.

To run the tests:
```bash
php tests/run_tests.php
```

---

## 🛡 License
This project is licensed under the MIT License - see the LICENSE file for details.

## 🤝 Contributing
Contributions are welcome! Please open an issue or submit a pull request for any improvements or bug fixes.
