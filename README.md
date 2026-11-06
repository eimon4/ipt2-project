# SwiftTrack: Web-Based School Reporting and Management System

SwiftTrack is a lightweight, clean, and modern school incident and reporting platform built with **HTML5, Tailwind CSS, JavaScript (Vanilla JS), and Supabase (PostgreSQL & Storage)**.

---

## 🌟 Features Implemented (Matching Proposal Requirements)

1. **User Authentication & Role Management:**
   - Student & Administrator registration and login.
   - Role-based redirection (`feed.html` for students, `admin.html` for administrators).
   - Secure session handling via Supabase Auth.

2. **Centralized School Reports Feed (`feed.html`):**
   - Displays all approved and resolved school reports.
   - Real-time search bar (by keywords in title, description, category, or reporter).
   - Category filter pills: **All**, **Lost & Found**, **Incident**, **Emergency**, and **Other**.
   - Displays attached supporting images and relative timestamps.

3. **Report Submission & Status Tracking (`report.html`):**
   - Allows students to submit a report with:
     - Title
     - Category dropdown
     - Supporting image upload (with image preview)
     - Description
   - Initial status automatically set to `pending`.
   - **My Reports** live table displaying the student's submitted reports and their real-time status (`Pending`, `Approved`, `Rejected`, `Resolved`).

4. **Administrator Dashboard (`admin.html`):**
   - Summary statistics cards: Total Reports, Pending Review, Approved, and Resolved.
   - Filter by status and category, plus live search.
   - One-click actions:
     - **Approve**: Immediately publishes the report to the student feed.
     - **Reject**: Marks the report as rejected (hidden from public feed).
     - **Mark Resolved**: Marks an addressed report as resolved.

5. **Detailed Report View (`view_report.html`):**
   - Comprehensive view of a single incident.
   - High-resolution supporting image view with click-to-zoom.
   - Administrator action controls right on the report page.

---

## 🚀 Quick Setup Guide (Takes ~3 Minutes)

### Step 1: Create a Supabase Project
1. Go to [https://supabase.com](https://supabase.com) and create a free account (or log in).
2. Click **"New Project"**, name it `SwiftTrack`, set a database password, and click **Create new project**.

### Step 2: Run the Database Setup Script
1. In your Supabase Dashboard, click on **SQL Editor** in the left menu.
2. Click **New query**.
3. Open `supabase_schema.sql` from this folder, copy all contents, paste them into the SQL Editor, and click **Run**.
   *(This automatically creates the `profiles` table, `reports` table, triggers, Row Level Security policies, and the `report-images` storage bucket!)*

### Step 3: Put Your Supabase Keys in `supabase-config.js`
1. In your Supabase Dashboard, go to **Project Settings** (gear icon at the bottom left) -> **API**.
2. Find:
   - **Project URL**
   - **Project API Keys** -> `anon` / `public`
3. Open `supabase-config.js` in a text editor and replace the placeholder values:
   ```javascript
   const SUPABASE_URL = 'https://your-project-id.supabase.co';
   const SUPABASE_ANON_KEY = 'eyJhbGciOi...';
   ```

### Step 4: Run the Website
You can run SwiftTrack directly in any browser:
- Simply double-click `index.html` to open it in your browser, **OR**
- Run a simple local server using Node.js:
  ```powershell
  npx serve .
  ```
  and open `http://localhost:3000` in your browser.

---

## 📁 File Structure

```text
src/
├── index.html            # Login page
├── register.html         # User & Administrator registration
├── feed.html             # Centralized school reports feed with search & filters
├── report.html           # Report submission & "My Reports" tracker
├── admin.html            # Administrator dashboard & report management
├── view_report.html      # Individual report detail view
├── supabase-config.js    # Centralized Supabase credentials
├── auth.js               # Shared authentication and navbar helpers
├── supabase_schema.sql   # Complete PostgreSQL schema & storage setup
├── bg.jpg                # Background image asset
└── legacy_php/           # Backup of original PHP prototype files
```

---

## 💡 Administrator vs Student Roles
- **Student Registration**: Anyone registering on `register.html` is automatically created as a Student.
- **Admin Accounts (Database-only)**:
  To promote an account to School Administrator:
  1. Have the user create an account normally on `register.html`.
  2. In your Supabase Dashboard, go to **Table Editor** -> **profiles**, and change `role` to `'admin'`.
  *(Or execute `make_admin.sql` in the Supabase SQL Editor).*
- When an admin logs in, they are automatically directed to the Administrator Dashboard (`admin.html`).

