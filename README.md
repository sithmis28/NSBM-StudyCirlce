# NSBM NewsDesk – Campus News Management System
**NSBM Green University – Faculty of Computing**  
**Module:** Web and Mobile Application Development  
**Batch:** 26.1 IFSL  
**Assignment:** Final Project Assignment (Project Topic 1)

---

## 🔗 Project Submission & Live Linksd

| Deliverable | URL / Reference | Description |
| :--- | :--- | :--- |
| 🌐 **Live Hosted Web Application** | [http://nsbmnewsdesk.free.je/newsdesk/landing.php](http://nsbmnewsdesk.free.je/newsdesk/landing.php) *(හෝ [http://nsbmnewsdesk.free.je/](http://nsbmnewsdesk.free.je/))* | Deployed online on InfinityFree live PHP/MySQL host for public evaluation |
| 💻 **GitHub Source Repository** | `https://github.com/your-username/nsbm-newsdesk` | Complete version-controlled codebase |
| 🎥 **System Video Demonstration** | `https://youtu.be/your-demo-video-id` *(Unlisted YouTube link)* | Complete walkthrough covering Admin, Student, CRUD, and Live system |

---

## 1. Project Overview
**NSBM NewsDesk** is a comprehensive, production-ready web application engineered for NSBM Green University to publish, organize, and manage campus news, departmental circulars, faculty breakthroughs, athletic events, and student club activities.

All content, branding, color schemes (`#008751` NSBM Green, `#002147` NSBM Navy), and high-resolution photography are authentically sourced from the official university website ([www.nsbm.ac.lk](https://www.nsbm.ac.lk)), featuring October 2026 milestones including the university's 10th anniversary, new laboratory openings, and national tech forums.

---

## 2. Technology Stack
- **Frontend:** HTML5, CSS3, JavaScript (ES6+), Bootstrap 5.3, Bootstrap Icons, Chart.js (Analytics)
- **Backend:** PHP 8+ (Modular architecture, Prepared Statements, Secure Sessions, Flash Notifications)
- **Database:** MySQL / MariaDB (Relational schema with foreign keys, indexes, and cascade rules)
- **Deployment & Hosting:** Apache HTTP Server, `.htaccess` security rewrites, cPanel / InfinityFree / Alwaysdata / Render compatibility
- **Security:** Bcrypt Password Hashing (`password_hash`), XSS sanitization (`htmlspecialchars`), CSRF protection, SQL Injection mitigation via PDO Prepared Statements, and hidden `.sql`/config file protection via `.htaccess`.

---

## 3. Implemented Features Matrix

### 3.1 Administrator Features
1. **Secure Admin Authentication & Access Control:**
   - Role-based access control protecting back-office resources (`admin/`).
   - Secure login verification preventing unauthorized user elevation.
2. **News Articles CRUD:**
   - **Create:** Publish new campus articles with title, category, excerpt, HTML body, and cover photo.
   - **Read:** Paginated data table with search and category filtering.
   - **Update:** Edit existing stories, update text, replace image, and change visibility status (Published / Draft).
   - **Delete:** Remove outdated articles with cascade cleanup.
   - **Interactive Toggles:** 1-click toggle for Homepage Hero Feature (`is_featured`) and Breaking News Ticker (`is_breaking`).
3. **Categories Management (CRUD):**
   - Manage university categories (Academic, Computing & Tech, Events & Festivals, Sports & Fitness, Student Life & Clubs, General Announcements).
   - Add new categories, edit descriptions, adjust color badges, and delete unused categories.
4. **Student Submissions Review & Approval Workflow:**
   - Review stories and proposals submitted by campus societies and undergraduates.
   - **Approve:** Automatically publishes the student submission into the live campus news feed.
   - **Reject:** Marks submission as rejected with optional editorial feedback for the student.
5. **Campus Announcements & Priority Broadcasts:**
   - Publish campus circulars with priorities: **Urgent** (Red), **Important** (Amber), and **Normal** (Green).
   - Target audience categorization (e.g. All Students, Faculty of Computing, Final Year).
6. **Reader Comments Moderation:**
   - Review reader feedback across all articles.
   - Approve, hide/reject, or delete inappropriate remarks.
7. **Readership & Engagement Reports:**
   - Visual Doughnut Chart (Chart.js) showing traffic distribution across categories.
   - Total impressions, average views per article, and comment engagement rate.
   - Top 10 most-read articles leaderboard.
   - **Export to CSV:** Downloadable real-time report spreadsheet.
   - **Print Report:** Dedicated printer-friendly styling.
8. **User Account Administration:**
   - View registered students, student IDs, and faculties.
   - Switch user roles between Student and Administrator.

### 3.2 Student / Reader Features
1. **Modern Video Landing Page (`landing.php`):**
   - Immersive background campus video billboard.
   - Clean navigation bar: **News Feed**, **Archive & Categories**, **Circulars**, **Events**, and **Student Portal**.
   - Dark / Light mode toggle with instant persistence.
   - Sage-green surfaces in light mode across navigation, feature cards, footer, and sign-in/register dialogs.
   - Quick login & registration modal popups.
2. **Student Registration & Login:**
   - Form validation for Name, Student ID, Email, Faculty, and Password confirmation.
   - Auto-subscription to faculty and general announcements upon signup.
3. **Campus News Discovery & Exploration:**
   - **Breaking News Ticker:** Dynamic live ticker at the top of the homepage.
   - **Hero Section:** Large featured headlines with author, date, and views count.
   - **Filter & Search:** Filter by category pills, keyword search, and sorting (Newest, Most Popular, Oldest).
4. **Interactive Article Reading (`article.php`):**
   - Full story view with authentic photography from `nsbm.ac.lk`.
   - Real-time pageview counter increment.
   - Social media share shortcuts (Facebook, X/Twitter, LinkedIn).
   - Related stories recommendations from the same faculty/category.
5. **1-Click Bookmark System:**
   - AJAX-powered save/unsave toggle without page reloading.
   - Dedicated **Saved Reading List** page (`user/bookmarks.php`).
6. **Discussion & Comments:**
   - Logged-in students can leave feedback, insights, or congratulatory remarks.
   - Comment threads display commenter name, faculty, and elapsed time ("2 hours ago").
7. **Submit Story for Approval (`user/submit_article.php`):**
   - Student clubs can submit event reports with cover photos.
   - Track approval status and editorial feedback in **My Submissions** (`user/my_submissions.php`).
8. **Personalized Category Subscriptions (`user/subscriptions.php`):**
   - Follow specific categories (Computing, Sports, Academic, etc.).
   - Access a customized personalized news feed inside the **Student Dashboard** (`user/dashboard.php`).
9. **Profile & Security Settings (`user/profile.php`):**
   - Update full name, faculty, and student registration number.
   - Update account password with current password verification.

---

## 4. Authentic NSBM News Catalog (Current Database)
All news entries are sourced directly from the official NSBM Green University portal ([www.nsbm.ac.lk](https://www.nsbm.ac.lk)):

| ID | Article Title | Category | Date | Badge | Cover Image |
| :-: | :--- | :--- | :--- | :--- | :--- |
| **1** | NSBM Green University Celebrates 10 Years of Excellence and National Impact | General Announcements | 2026-10-26 | Featured / Breaking | `nsbm_10_years_celebration.jpg` |
| **2** | NSBM Hosts Dr. Hans Wijayasuriya for Sri Lanka’s Digital Future Session | Computing & Tech | 2026-10-29 | Featured / Breaking | `nsbm_dr_hans_digital_future.jpg` |
| **3** | NSBM Opens Rs. 720 Million Civil Engineering Laboratory Complex | Academic | 2026-10-26 | Featured | `nsbm_civil_engineering_complex.jpg` |
| **4** | ICOBI 2026 Explores Advancing Human-Centric AI and Sustainable Business Innovation | Events & Festivals | 2026-10-16 | Featured | `nsbm_icobi_2026.jpg` |
| **5** | NSBM Green University Achieves GREENSL® Platinum Building Certification | General Announcements | 2026-10-29 | Standard | `nsbm_greensl_platinum.jpg` |
| **6** | NSBM Green University Welcomes Final Undergraduate Intake for 2026 | Student Life & Clubs | 2026-10-20 | Standard | `nsbm_undergraduate_intake_2026.jpg` |
| **7** | NSBM Volleyball Team Emerges Champions at UOC Vice Chancellor’s Challenge Trophy 2026 | Sports & Fitness | 2026-10-14 | Standard | `nsbm_volleyball_champions.jpg` |
| **8** | NSBM FIT Programme Introduces Computing Students to Generative and Agentic AI | Computing & Tech | 2026-10-13 | Standard | `nsbm_generative_agentic_ai.jpg` |
| **9** | NSBM and SCU Sign MOU For Sri Lanka’s First Double Degree | Academic | 2026-10-29 | Standard | `nsbm_scu_double_degree.jpg` |
| **10** | NSBM Hockey Teams Achieve Titles at KDU Leaders’ Trophy 2026 | Sports & Fitness | 2026-10-19 | Standard | `nsbm_hockey_kdu_leaders.jpg` |

---

## 5. Demo & Evaluation Accounts

Use these pre-seeded credentials to evaluate the different roles and permissions of the application:

| Role | Email | Password | Access & Privileges |
| :--- | :--- | :--- | :--- |
| **Administrator** | `admin@gmail.com` | `admin123` | Full access to Admin Panel (`/admin/dashboard.php`), Content Moderation & Analytics |
| **Student (Computing)** | `kavindu@gmail.com` | `student123` | Undergrad, Faculty of Computing (ID: 26.1-IFSL-045) |
| **Student (Business)** | `sanduni@gmail.com` | `student123` | Undergrad, Faculty of Business (ID: 26.1-BM-112) |

---

## 6. Live Hosting & Deployment Guide

The application is engineered to deploy seamlessly on standard PHP/MySQL hosting platforms such as **InfinityFree**, **Alwaysdata**, **000webhost**, or any **cPanel** shared host.

### Step 1: Create a Free Hosting Account
1. Visit a free PHP/MySQL hosting provider like [InfinityFree](https://www.infinityfree.com/) or [Alwaysdata](https://www.alwaysdata.com/).
2. Create an account and set up a new website account (e.g. `nsbm-newsdesk.infinityfreeapp.com`).
3. Note your **Control Panel (vPanel / cPanel)** credentials.

### Step 2: Create a MySQL Database on the Live Server
1. Go to your hosting control panel and open **MySQL Databases**.
2. Create a new database (e.g., `if0_xxxxxxx_nsbm_newsdesk`).
3. Note the following generated connection parameters:
   - **MySQL Host / Server:** (e.g., `sql101.infinityfree.com` or `localhost`)
   - **Database Name:** (e.g., `if0_xxxxxxx_nsbm_newsdesk`)
   - **MySQL Username:** (e.g., `if0_xxxxxxx`)
   - **MySQL Password:** *(Your hosting/vPanel account password)*
   - **MySQL Port:** `3306`

### Step 3: Import `database_live.sql` via phpMyAdmin
> [!IMPORTANT]
> Shared hosts do not allow the SQL query `CREATE DATABASE`. Therefore, always import **`database_live.sql`** (not `database.sql`), which contains clean table schemas and complete authentic seed data without the root database creation wrapper.

1. In your hosting control panel, click **phpMyAdmin**.
2. Select your newly created database from the left-hand navigation.
3. Click the **Import** tab at the top.
4. Choose the file **`database_live.sql`** from your project directory.
5. Click **Go** / **Import**. Verify that all 8 tables (`users`, `categories`, `news`, `announcements`, `article_submissions`, `comments`, `bookmarks`, `subscriptions`, `events`) are imported.

### Step 4: Configure Live Database Connection
The application has built-in smart configuration in `config/database.php`. You have two easy ways to set your live credentials:

#### Option A: Using `config/database.live.php` (Recommended)
1. In the `config/` folder, create a file named `database.live.php` (a template `database.live.php.example` is provided):
```php
<?php
define('DB_HOST', 'sql101.infinityfree.com'); // Your live host
define('DB_USER', 'if0_xxxxxxx');            // Your live database user
define('DB_PASS', 'YourLivePasswordHere');    // Your live database password
define('DB_NAME', 'if0_xxxxxxx_nsbm_newsdesk'); // Your live database name
define('DB_PORT', 3306);
```
2. When this file exists, `config/database.php` automatically uses these credentials. Local XAMPP continues to work whenever `database.live.php` is absent!

#### Option B: Environment Variables (For Cloud / Docker / Render)
Set environment variables on your platform:
- `DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME`, `DB_PORT`

### Step 5: Upload Files to Live Web Root
1. Open your hosting **Online File Manager** or connect via **FTP (FileZilla)**.
2. Navigate to the public document root directory (usually **`htdocs`** or **`public_html`**).
3. Upload all files and folders of the project (`admin/`, `assets/`, `config/`, `includes/`, `user/`, `index.php`, `landing.php`, `.htaccess`, etc.).
4. Ensure the `assets/uploads/` directory has write permissions (`chmod 755` or `777`) so users can upload article thumbnails.

### Step 6: Test Your Live System
1. Open your live domain in the browser (e.g. `https://nsbm-newsdesk.infinityfreeapp.com`).
2. The `.htaccess` file will automatically direct visitors to `landing.php` / `index.php`.
3. Test signing in as Administrator (`admin@gmail.com` / `admin123`) to verify admin privileges and live database connectivity.
4. Test signing in as Student (`kavindu@gmail.com` / `student123`) to verify interactive reader features.

---

## 7. Local Setup & Execution Guide (XAMPP)

### Step 1: Start XAMPP
1. Open the **XAMPP Control Panel**.
2. Start **Apache** and **MySQL**.
   *(Note: The database connection helper in `config/database.php` automatically supports multi-port connection across port 3306 and 3307 with user `root` and password `""`)*.

### Step 2: Database Setup
1. Open **phpMyAdmin** (`http://localhost/phpmyadmin/`).
2. Create a new database named `nsbm_newsdesk` (if it does not exist).
3. Select `nsbm_newsdesk` and click **Import**.
4. Choose the file `database.sql` from the project root directory and click **Go** / **Import**.

### Step 3: Access the Web Application
Open your browser and navigate to either:
- **Landing Page:** `http://localhost/newsdesk/landing.php`
- **News Feed:** `http://localhost/newsdesk/index.php`

Or if running via PHP built-in server from the project root:
```bash
php -S localhost:8000
```
Then visit: `http://localhost:8000/landing.php` or `http://localhost:8000/index.php`

---

## 8. Directory Structure
```text
newsdesk/
├── .htaccess                      # Apache security & directory index configuration
├── landing.php                    # Modern landing page with video background & quick login
├── index.php                      # Homepage (Breaking news ticker, Hero featured slider, Latest news grid)
├── news.php                       # All News archive with filters, search, pagination
├── article.php                    # Full article view, view counter, bookmarking, comments
├── category.php                   # Category topic feed with subscription toggle
├── announcements.php              # Campus circulars bulletin board with priority filters
├── events.php                     # Campus conferences & calendar events
├── search.php                     # Multi-resource keyword search
├── login.php                      # Unified login portal (Admin & Student)
├── register.php                   # Student registration with validation
├── logout.php                     # Secure session termination
├── database.sql                   # MySQL database schema & authentic NSBM 2026 seed data (Local)
├── database_live.sql              # Clean live export without CREATE DATABASE wrapper (Live Hosting)
├── config/
│   ├── database.php               # Dual-mode local & live PDO database connection helper
│   └── database.live.php.example  # Template for live hosting database credentials
├── includes/
│   ├── header.php                 # Brand navbar, utility bar, user status
│   ├── footer.php                 # University footer, contacts, social links
│   └── functions.php              # Helper utilities (sanitization, dates, time_ago, image URL resolution)
├── user/
│   ├── dashboard.php              # Student Dashboard with personalized feed
│   ├── submit_article.php         # Student / Club story submission form
│   ├── my_submissions.php         # Submission approval history & feedback
│   ├── bookmarks.php              # Saved articles reading list
│   ├── subscriptions.php          # Category subscription manager
│   ├── profile.php                # Profile & security settings
│   └── toggle_bookmark.php        # AJAX bookmark toggle endpoint
├── admin/
│   ├── dashboard.php              # Admin overview with Chart.js analytics & queues
│   ├── news/                      # News CRUD (index, create, edit, delete)
│   ├── categories/                # Category CRUD management
│   ├── submissions/               # Student story review & 1-click publishing
│   ├── announcements/             # Announcements & broadcast manager
│   ├── comments/                  # Reader comments moderation
│   ├── reports/                   # Readership analytics, CSV export, print
│   ├── users/                     # User management & role switching
│   └── includes/                  # Admin header, sidebar & footer
└── assets/
    ├── css/style.css              # Custom responsive stylesheet (NSBM Green `#008751` & Navy `#002147`)
    ├── js/main.js                 # AJAX handlers, validation, toast notifications
    ├── images/                    # Authentic images downloaded directly from www.nsbm.ac.lk
    │   ├── nsbm_10_years_celebration.jpg
    │   ├── nsbm_dr_hans_digital_future.jpg
    │   ├── nsbm_civil_engineering_complex.jpg
    │   ├── nsbm_icobi_2026.jpg
    │   ├── nsbm_greensl_platinum.jpg
    │   ├── nsbm_undergraduate_intake_2026.jpg
    │   ├── nsbm_volleyball_champions.jpg
    │   ├── nsbm_generative_agentic_ai.jpg
    │   ├── nsbm_scu_double_degree.jpg
    │   └── nsbm_hockey_kdu_leaders.jpg
    ├── uploads/                   # Storage for user and admin uploaded article media
    └── videos/                    # Background videos for hero billboard
```

---

## 9. Evaluation Criteria & Assignment Rubric Compliance

| Rubric Criteria | Weight | Implementation Details in NSBM NewsDesk |
| :--- | :---: | :--- |
| **UI Design & Frontend** | **20%** | Authentic NSBM branding (`#008751` & `#002147`), video billboard landing page, fully responsive Bootstrap 5.3 layout, Dark/Light theme switcher with instant localStorage persistence. |
| **Backend & Database Integration** | **20%** | PHP 8+ architecture with PDO Prepared Statements, complete relational database with Foreign Keys (`ON DELETE CASCADE / SET NULL`), dual-environment configuration (Local XAMPP & Live hosting). |
| **Admin Panel Functionality** | **15%** | Complete CRUD operations for News Articles, Categories, Campus Announcements, Student Submissions Review Workflow, Comment Moderation, Role switching, and Chart.js Readership Analytics with CSV export & print reports. |
| **Student / User Panel** | **15%** | Student registration, session login, interactive article reader, real-time bookmarking (AJAX), category subscriptions, club article submissions with approval tracking, and personal profile security. |
| **Project Report, GitHub & Live Hosting** | **15%** | Ready for live deployment via `database_live.sql`, `.htaccess`, dual-mode `config/database.php`, with complete live hosting guide and evaluation credentials. |
| **Video Demonstration & Teamwork** | **15%** | Highlighting end-to-end features, login roles, CRUD operations, database architecture, and student submission workflows. |

---

## 10. Submission Checklist for Batch 26.1 IFSL
- [x] **Topic 1:** NSBM NewsDesk chosen and fully developed.
- [x] **Landing Page & Navigation:** Clean navbar on `landing.php` without exposed administrative links.
- [x] **Separate Interfaces:** Dedicated Admin Panel and Student/User interfaces with role gating.
- [x] **CRUD Operations:** Complete operations across News, Categories, Announcements, Comments, and Submissions.
- [x] **Authentic Content:** Real campus news stories, dates, and photography extracted from `www.nsbm.ac.lk`.
- [x] **Live Hosting Compatibility:** Prepared `database_live.sql`, `config/database.live.php.example`, and `.htaccess` for live hosting (InfinityFree / cPanel).
- [x] **Database Integration:** PDO MySQL connection with prepared statements, foreign key cascades, and dual-mode configuration.
- [x] **Tested & Operational:** Verified on local XAMPP and structured for online cloud/shared hosting.
