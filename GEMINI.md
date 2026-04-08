Here is the completely revised prompt, built on the 8-step framework and carefully updated to include every single feature you listed. It enforces the exact tech stack limitations (no npm/node) and sets the specific AI tone parameters perfectly.

You can copy and paste this directly into Claude:

***

**Task context:** You are a senior software architect helping an experienced developer build a secure, AI-powered SaaS mailing platform.

**Tone context:** Direct, highly technical, focused on secure architecture, shared-hosting compatibility, and clean API integrations.

**Background data, documents, and images:**
* **Backend:** Object-oriented, structured Core PHP + MySQL. 
* **Frontend:** HTML, CSS, JS, Bootstrap, DataTables. (Strictly NO Node.js, NO npm, NO `node_modules`. Must be fully mobile responsive).
* **Environment:** Docker for local development, but architected to be easily deployable on shared cPanel hosting.
* **LLMs:** Latest OpenAI models and latest Gemini models (e.g., Gemini 2.5 Pro/Flash, GPT-4o, or newer).

**Detailed task description and rules:**
Design the architecture and core logic for the platform with the following features:
1. **Database Migrations:** Create a custom PHP migration system executed via a specific URL, secured so only the Admin can run it.
2. **Admin Role:** View all users and toggle active/inactive status. Set global platform limits per user: max emails sent per hour, total maximum emails, maximum file upload size (for attachments), and maximum rows allowed in an uploaded Excel list.
3. **User Role - Settings:** Users securely manage their OpenAI or Gemini API keys, choose their preferred LLM, and manage/test their personal SMTP credentials. 
4. **User Role - Data Management:** Users upload Excel files to populate their mailing list. **Rule:** Every new upload entirely replaces/clears the existing list. UI must clearly instruct users on columns (Required: `contact name`, `email`. Optional: `company`, `designation`, `company type`). Users manage this list via DataTables with search, sort, and sent/unsent status filters.
5. **User Role - Attachments:** Users can upload a single attachment per campaign (restricted strictly to PDF or DOCX).
6. **User Role - Campaign Generation:**
    * *Manual Mode:* Guide users on using variables like `[contact_name]` and `[company]` in their written email bodies.
    * *AI Mode:* User writes a base prompt. The backend *must* append this strict system context: "Write with a 100% humanized, soft tone. Do not use emojis. Never use the long dash character; use a period instead. Ensure it is clear, catchy, and highly readable so no one can predict it is AI-written." The backend must force the LLM to return a strict JSON object containing `subject`, `body`, and `footer`.
7. **User Role - Sending & Queues:** User sets a personal per-hour send limit (cannot exceed Admin's limit) and can manually Start/Stop the cron queue. 
8. **User Role - Manual Overrides:** On the DataTables list view, each row must have a "Generate AI Email" and a "Send Email" button, allowing the user to generate and fire off a single email instantly without depending on the cron queue.
9. **Analytics:** User dashboard must display counters: Total list size, Emails sent, Emails currently in queue.

**Examples:**
* *Migration Endpoint:* `https://domain.com/migrate.php?key=SECURE_ADMIN_KEY`
* *AI JSON Output:* `{"subject": "...", "body": "...", "footer": "..."}`
* *Manual Override:* User clicks "Send Email" on row 5. The system immediately fires the SMTP request for that specific contact, updates the status to "Sent" in the database, and bypasses the background queue.

**Conversation history:** "I have 8 years of professional development experience with PHP and Generative AI projects. I need clean, object-oriented code without the bloat of npm packages. The project must be structured perfectly for a shared hosting environment."

**Immediate task description or request:**
Write the initial technical foundation for this project. Include:
1. The `docker-compose.yml` and `Dockerfile` for a clean PHP/MySQL environment.
2. The custom PHP migration script logic and a comprehensive SQL database schema covering users, admin limits, user settings, the dynamic mailing list, and attachments.
3. The core PHP class for the LLM integration (handling dynamic key selection, enforcing the JSON output, and injecting the strict humanized tone rules).
4. The frontend UI boilerplate (HTML/JS) for the DataTables list view, demonstrating the manual "Generate AI Email" and "Send Email" action buttons.

**Think step by step.**

***