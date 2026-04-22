# Deployment Notes - aiMailSaas v1.1

The following database changes are required for this update. Please run these SQL commands in your database manager (like phpMyAdmin) or execute the `migrate.php` script with your `ADMIN_KEY`.

### SQL Migrations

```sql
-- Update admin_limits table
ALTER TABLE admin_limits ADD COLUMN IF NOT EXISTS master_gemini_key VARCHAR(255) AFTER max_excel_rows;

-- Update user_settings table
ALTER TABLE user_settings ADD COLUMN IF NOT EXISTS purpose ENUM('job_hunt', 'business_leads') DEFAULT 'job_hunt' AFTER user_id;
ALTER TABLE user_settings ADD COLUMN IF NOT EXISTS wizard_completed TINYINT(1) DEFAULT 0 AFTER purpose;
ALTER TABLE user_settings ADD COLUMN IF NOT EXISTS master_ai_used TINYINT(1) DEFAULT 0 AFTER wizard_completed;
ALTER TABLE user_settings ADD COLUMN IF NOT EXISTS gmail_oauth_token TEXT AFTER master_ai_used;

-- Update user_profiles table
ALTER TABLE user_profiles ADD COLUMN IF NOT EXISTS resume_text TEXT AFTER other_info;
ALTER TABLE user_profiles ADD COLUMN IF NOT EXISTS business_profile_text TEXT AFTER resume_text;
ALTER TABLE user_profiles ADD COLUMN IF NOT EXISTS resume_path VARCHAR(255) AFTER business_profile_text;
ALTER TABLE user_profiles ADD COLUMN IF NOT EXISTS business_profile_path VARCHAR(255) AFTER resume_path;
```

### Script Execution
If you prefer running the script:
`https://yourdomain.com/migrate.php?key=YOUR_ADMIN_KEY`
