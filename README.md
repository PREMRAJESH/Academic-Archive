# Nexus: Academic Archive & Publication Management System

Nexus is a premium research publication management ecosystem designed for scholarly communities, university presses, and independent researchers. It streamlines the entire academic lifecycle—from manuscript submission and peer review to final digital preservation and public dissemination.

![Academic Archive Banner](nexus/assets/images/hero-library.png)

---

## 🚀 Core Features

### 🏛️ Digital Repository

- **Public Access**: Guest users can browse, search, and read published research papers without an account.
- **Advanced Search**: Filter publications by category, author, or keywords.
- **Rich Metadata**: Every paper includes abstracts, keywords, and institutional attribution.

### 📝 Researcher Portal

- **Manuscript Submission**: Seamlessly upload research papers (PDF/Doc) with structured metadata.
- **Workflow Tracking**: Real-time status updates (Pending, Approved, Rejected).
- **Portfolio Management**: A personal dashboard to manage all past and current submissions.

### 🛡️ Administrative Suite

- **Editorial Review**: Admins can review submissions, provide feedback, and manage publishing states.
- **User Management**: Control researcher access and institutional permissions.
- **System Analytics**: Real-time "System Overview" dashboard tracking publication metrics and researcher growth.

---

## 🛠️ Technology Stack

- **Backend**: PHP 8.x (Procedural/Functional architecture)
- **Database**: MySQL / MariaDB
- **Frontend**:
  - **TailwindCSS**: Modern, utility-first styling.
  - **Google Fonts**: Newsreader (Serif) & Inter (Sans-serif) for academic legibility.
  - **Material Symbols**: Refined iconography for UI interaction.
- **Architecture**: Secure Session-based authentication and role-based access control (RBAC).

---

## 📦 Installation & Setup

### Prerequisites

- PHP 7.4 or higher
- MySQL / MariaDB
- Apache or Nginx (XAMPP/WAMP recommended for local development)

### 1. Database Setup

1. Create a new database named `nexus_db` in your MySQL environment.
2. Import the provided schema:
   ```bash
   mysql -u [user] -p nexus_db < nexus_db.sql
   ```
   _Alternatively, use the SQL content from `nexus_db.sql` in phpMyAdmin._

### 2. Configuration

Verify your database credentials in `config/db.php`:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'nexus_db');
```

### 3. File Permissions

Ensure the `uploads/` directory is writable by the web server:

```bash
chmod -R 755 uploads/
```

---

## 🔑 Default Credentials

| Role                     | Email             | Password   |
| :----------------------- | :---------------- | :--------- |
| **System Administrator** | `admin@nexus.com` | `admin123` |

---

## 📂 Project Structure

```text
nexus/
├── admin/          # Administrative dashboard & logic
├── assets/         # CSS, JS, and high-res image assets
├── auth/           # Login, Registration, and Session management
├── config/         # Database & environment configuration
├── includes/       # Shared UI components (header, footer, auth_check)
├── uploads/        # Secure storage for research manuscripts
├── user/           # Researcher portal and submission logic
├── index.php       # Public Landing Page
└── nexus_db.sql    # Core database schema & seed data
```

---

## 📜 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---

_Dedicated to the preservation and dissemination of rigorous academic inquiry._
