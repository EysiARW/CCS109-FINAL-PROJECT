# CuyoTech SSIS — Pure PHP + MySQL Database

A standalone PHP web application of the **Student Services Information System (SSIS)** for CuyoTech University, integrated with the 3NF MySQL relational database schema defined in `ssis_schema.sql`.

No Node.js, no TypeScript, no npm, and no build steps required.

---

## 🌐 Live Interactive Demo (GitHub Pages)

Experience the live system directly in your browser without requiring a local PHP/MySQL or XAMPP setup:  
👉 **[Launch CuyoTech SSIS Live Showcase](https://eysiarw.github.io/CCS109-FINAL-PROJECT/)**

- **Quick Persona Switcher**: Instantly switch between Student (Juan / Maria), Registrar (M. Santiago), Cashier (Pedro Reyes), Department Staff, and Administrator (Ana Reyes).
- **Interactive Modules**: Test document requesting with dynamic fee calculation, grade encoding, payment recording with printable official receipts, and departmental clearance.
- **System Specifications**: Built-in 3NF relational database schema viewer, QA test report (36/36 passed), and 15 UI/UX high-resolution wireframe screens.

---

## 📁 Project Structure

```
CCS109-final-project/
├── index.php            ← Application entrypoint, routing & action dispatcher
├── ssis_schema.sql      ← 3NF Database schema & sample seed data (MySQL / MariaDB)
├── DATABASE_DESIGN.md   ← Full ERD & 3NF Normalization document (Part B requirement)
├── README.md            ← Project documentation & credentials guide
├── config/
│   └── Database.php     ← PDO connection manager with Singleton pattern & port probing
├── src/
│   ├── Controllers/     ← MVC Controllers (Auth, Student, Registrar, Cashier, Dept, Admin)
│   ├── Models/          ← Entity classes (Student, Course, Enrollment, Payment, etc.)
│   ├── Repositories/    ← Data Access Layer (PDO queries with demo fallbacks)
│   ├── Services/        ← Domain business logic (Auth, Requests)
│   └── Utils/           ← View renderer & Icon SVG generator
├── views/
│   ├── layouts/         ← Application master layouts (main app shell & auth layout)
│   ├── auth/            ← Sign-in page
│   ├── student/         ← Student portal (profile, grades, subjects, requests)
│   ├── registrar/       ← Registrar module (enrollment, grades encoding)
│   ├── cashier/         ← Cashier module (payments, receipts)
│   ├── department/      ← Department clearance evaluation
│   └── admin/           ← Administrator module (user accounts, request processing)
├── assets/
│   └── style.css        ← Responsive University CSS stylesheets & design tokens
└── data/                ← Reference worksheets & schema documentation
```

---

## 📋 Section B: Database Design (Normalization Required)
*Requirement: ERD + 3NF table designs (at least 6 tables).*

See **[DATABASE_DESIGN.md](DATABASE_DESIGN.md)** for the complete deliverable, including:
1. **Full Mermaid Entity-Relationship Diagram (ERD)** with crow's foot notation, primary/foreign keys, and cardinalities.
2. **Step-by-step Normalization Analysis** (UNF $\to$ 1NF $\to$ 2NF $\to$ 3NF) with functional dependencies.
3. **8 Table 3NF Specifications** (exceeding the 6-table minimum requirement).
4. **Data Dictionary** with field names, types, constraints, and descriptions.

---

## 🗄️ Database Architecture (`ssis_schema.sql`)

The database is normalized to **Third Normal Form (3NF)**:
- **`Department`**: University colleges and offices (CCS, Registrar, Cashier, Library)
- **`Student`**: Student accounts, program, year level, encrypted password hashes
- **`AdminUser`**: Administrator accounts with roles (Registrar, Cashier, SuperAdmin)
- **`Course`**: Course catalog (CourseCode, CourseName, Units, Department)
- **`Enrollment`**: Student-to-Course many-to-many relationship with term grades
- **`Payment`**: Student tuition and fee payments with official receipt (OR) tracking
- **`DocumentRequest`**: Student document requests (TOR, COR, Certification) and fulfillment workflow
- **`Clearance`**: Student-to-Department clearance clearance statuses (Cleared, Pending, Hold)

---

## 🚀 How to Run

### Step 1: Start MySQL in XAMPP
Open the XAMPP Control Panel and start the **MySQL Database** service. This project defaults to port `3307`, user `root`, and no password; if your XAMPP MySQL configuration uses a different port or credentials, set `DB_PORT`, `DB_USER`, and `DB_PASS` accordingly.

### Step 2: Import the Database (`ssis_schema.sql`)
You have three easy ways to set up the database:

- **Option A (One-click in browser):**  
  Simply start the PHP app, and click the **"Connect / Initialize"** or **"Re-seed SQL"** link shown on the status banner at the top of any page. It will automatically create `ssis_db` and all tables.
  
- **Option B (Via phpMyAdmin):**  
  Open `http://localhost/phpmyadmin`, create or click **Import**, select `ssis_schema.sql`, and click **Import**.

- **Option C (Via Terminal):**  
  ```bash
  /Applications/XAMPP/xamppfiles/bin/mysql -u root < ssis_schema.sql
  ```

### Step 3: Start the PHP Server

```bash
cd "/Users/manalili/Downloads/CCS109 final project"

# If php is in your PATH:
php -S localhost:8080

# Or using XAMPP's PHP directly:
/Applications/XAMPP/xamppfiles/bin/php -S localhost:8080
```

Then open your browser at **http://localhost:8080**.

*(Note: If MySQL is not running, the application gracefully operates in demo mode using fallback data so you can still view all pages without crashes.)*

---

## 🔑 Login Accounts (from `ssis_schema.sql`)

All default test accounts use the password: **`password123`**

### Administrative & Staff Accounts
| Role | Username | Password | Department / Office | Accessible Modules |
|---|---|---|---|---|
| **Administrator (SuperAdmin)** | `ana.reyes` | `password123` | Registrar's Office | Full Access (User Accounts, Enrollment, Grades Encoding, Payments, Receipts, Clearance, Requests) |
| **Registrar** | `m.santiago` | `password123` | Registrar's Office | Enrollment, Grades Encoding, Document Requests |
| **Cashier** | `cashier01` | `password123` | Cashier's Office | Counter Payments, Official Receipts |
| **Department Staff** | `dept.staff` | `password123` | College of Computing Studies | Departmental Clearance Processing |

### Student Accounts
| Student Name | Student ID | Email / Login | Password | Program & Year |
|---|---|---|---|---|
| **Juan Dela Cruz** | `2021-00123` | `juan.delacruz@cuyotech.edu` or `2021-00123` | `password123` | BS Information Technology (3rd Year) |
| **Maria Santos** | `2021-00002` (or `2024-00412`) | `2024-00412@cuyotech.edu` or `2024-00412` | `password123` | BS Accountancy (1st Year) |
| **Paolo Gomez** | `2021-00003` (or `2020-00087`) | `2020-00087@cuyotech.edu` or `2020-00087` | `password123` | BS Information Technology (4th Year) |

> **Note:** You can log in using either the **Student ID number** (e.g. `2021-00123`) or the **Email address** (e.g. `juan.delacruz@cuyotech.edu`). Staff and administrators log in using their **Username** (e.g. `ana.reyes`).

---

## ⚡ System Modules & Sections

1. **Student Portal**
   - **Profile** (`?page=profile`): View student information, curriculum year level, and secure password updates.
   - **Grades** (`?page=grades`): Term-by-term grade breakdown with automated semester GPA calculations.
   - **Subjects** (`?page=subjects`): Enrolled subjects directory with units, schedules, rooms, and faculty.

2. **Registrar Module**
   - **Enrollment** (`?page=enrollment`): Register students into academic courses and manage course enrollments.
   - **Grades Encoding** (`?page=grades-encoding`): Encode, review, and finalize student grades across all course sections.

3. **Cashier Module**
   - **Payments** (`?page=payments`): Record student counter payments with automated Official Receipt (OR) generation.
   - **Receipts** (`?page=receipts`): Search, view, and print university Official Receipts with payment breakdown and cashier verification.

4. **Department Module**
   - **Clearance Processing** (`?page=clearance`): Review and update departmental clearances (Cleared, Hold, Pending).

5. **Admin Module**
   - **User Account Management** (`?page=admin-users`): Create, list, search, and manage student and staff accounts.

6. **Document Request System**
   - **TOR, COR, Certifications** (`?page=requests` & `?page=admin-requests`): Students request documents with live fee calculations; staff process fulfillment from pending to released.

1. **Authentication**: Verifies credentials against `Student` or `AdminUser` tables with bcrypt password hashing (`password_verify()`).
2. **Document Requests (TOR, COR, Certifications)**:
   - Submitting a request inserts a new record into `DocumentRequest`.
   - The Request History table reads directly from `DocumentRequest`.
   - Fulfillment updates request status from pending to processing, ready for release, and released.
3. **Subjects & Grades**: Queries `Course` and `Enrollment` tables grouped by semester with GPA calculation.
4. **Registrar Operations**: Student course enrollment and term grades encoding into `Enrollment`.
5. **Cashier Operations**: Student payment processing and official receipt issuance into `Payment`.
6. **Department Clearance**: Student clearance review, approval, and hold management in `Clearance`.
7. **Profile & Security**: Real-time student details display and live password updates to `Student.PasswordHash`.
8. **Admin User Accounts**: Lists and manages accounts from `Student` and `AdminUser`.
