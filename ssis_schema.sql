-- =====================================================================
-- CuyoTech University Student Services Information System (SSIS)
-- Database Schema (3NF)
-- Compatible with MySQL / MariaDB (e.g. via XAMPP)
-- =====================================================================

CREATE DATABASE IF NOT EXISTS ssis_db;
USE ssis_db;

-- ---------------------------------------------------------------------
-- DEPARTMENT
-- ---------------------------------------------------------------------
CREATE TABLE Department (
    DepartmentID    INT AUTO_INCREMENT PRIMARY KEY,
    DepartmentName  VARCHAR(100) NOT NULL,
    HeadName        VARCHAR(100)
);

-- ---------------------------------------------------------------------
-- STUDENT
-- ---------------------------------------------------------------------
CREATE TABLE Student (
    StudentID       INT AUTO_INCREMENT PRIMARY KEY,
    FirstName       VARCHAR(50)  NOT NULL,
    LastName        VARCHAR(50)  NOT NULL,
    Email           VARCHAR(100) NOT NULL UNIQUE,
    PasswordHash    VARCHAR(255) NOT NULL,
    Program         VARCHAR(100),
    YearLevel       INT,
    Status          VARCHAR(20) DEFAULT 'Active',   -- e.g. Active, Inactive, Graduated
    Address         VARCHAR(255) DEFAULT 'Brgy. San Pedro, Puerto Princesa City, Palawan 5300'
);

-- ---------------------------------------------------------------------
-- ADMINUSER  (Registrar, Cashier, Department staff, Super Admin)
-- ---------------------------------------------------------------------
CREATE TABLE AdminUser (
    AdminID         INT AUTO_INCREMENT PRIMARY KEY,
    Username        VARCHAR(50)  NOT NULL UNIQUE,
    PasswordHash    VARCHAR(255) NOT NULL,
    Role            VARCHAR(30)  NOT NULL,   -- e.g. Registrar, Cashier, DepartmentStaff, SuperAdmin
    DepartmentID    INT NULL,
    CONSTRAINT fk_adminuser_department
        FOREIGN KEY (DepartmentID) REFERENCES Department(DepartmentID)
        ON DELETE SET NULL
        ON UPDATE CASCADE
);

-- ---------------------------------------------------------------------
-- COURSE
-- ---------------------------------------------------------------------
CREATE TABLE Course (
    CourseID        INT AUTO_INCREMENT PRIMARY KEY,
    CourseCode      VARCHAR(20)  NOT NULL UNIQUE,
    CourseName      VARCHAR(100) NOT NULL,
    Units           INT NOT NULL,
    DepartmentID    INT NOT NULL,
    CONSTRAINT fk_course_department
        FOREIGN KEY (DepartmentID) REFERENCES Department(DepartmentID)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
);

-- ---------------------------------------------------------------------
-- ENROLLMENT  (resolves Student <-> Course many-to-many)
-- ---------------------------------------------------------------------
CREATE TABLE Enrollment (
    EnrollmentID    INT AUTO_INCREMENT PRIMARY KEY,
    StudentID       INT NOT NULL,
    CourseID        INT NOT NULL,
    SchoolYear      VARCHAR(9)  NOT NULL,   -- e.g. '2026-2027'
    Semester        VARCHAR(10) NOT NULL,   -- e.g. '1st', '2nd', 'Summer'
    Grade           VARCHAR(5),
    CONSTRAINT fk_enrollment_student
        FOREIGN KEY (StudentID) REFERENCES Student(StudentID)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT fk_enrollment_course
        FOREIGN KEY (CourseID) REFERENCES Course(CourseID)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,
    CONSTRAINT uq_enrollment UNIQUE (StudentID, CourseID, SchoolYear, Semester)
);

-- ---------------------------------------------------------------------
-- PAYMENT
-- ---------------------------------------------------------------------
CREATE TABLE Payment (
    PaymentID       INT AUTO_INCREMENT PRIMARY KEY,
    StudentID       INT NOT NULL,
    Amount          DECIMAL(10,2) NOT NULL,
    PaymentDate     DATE NOT NULL,
    ORNumber        VARCHAR(30) NOT NULL UNIQUE,
    PaymentType     VARCHAR(30) NOT NULL,   -- e.g. Tuition, Miscellaneous, Other
    CONSTRAINT fk_payment_student
        FOREIGN KEY (StudentID) REFERENCES Student(StudentID)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

-- ---------------------------------------------------------------------
-- DOCUMENTREQUEST
-- ---------------------------------------------------------------------
CREATE TABLE DocumentRequest (
    RequestID           INT AUTO_INCREMENT PRIMARY KEY,
    StudentID           INT NOT NULL,
    DocumentType        VARCHAR(30) NOT NULL,   -- e.g. TOR, COR, Certification
    Copies              INT NOT NULL DEFAULT 1,
    RequestDate         DATE NOT NULL,
    Status              VARCHAR(20) DEFAULT 'Pending',  -- Pending, Processing, Released
    ProcessedByAdminID  INT NULL,
    CONSTRAINT fk_docreq_student
        FOREIGN KEY (StudentID) REFERENCES Student(StudentID)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT fk_docreq_admin
        FOREIGN KEY (ProcessedByAdminID) REFERENCES AdminUser(AdminID)
        ON DELETE SET NULL
        ON UPDATE CASCADE
);

-- ---------------------------------------------------------------------
-- CLEARANCE  (resolves Student <-> Department many-to-many)
-- ---------------------------------------------------------------------
CREATE TABLE Clearance (
    ClearanceID     INT AUTO_INCREMENT PRIMARY KEY,
    StudentID       INT NOT NULL,
    DepartmentID    INT NOT NULL,
    Status          VARCHAR(20) DEFAULT 'Pending',  -- Pending, Cleared, Hold
    DateCleared     DATE NULL,
    CONSTRAINT fk_clearance_student
        FOREIGN KEY (StudentID) REFERENCES Student(StudentID)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT fk_clearance_department
        FOREIGN KEY (DepartmentID) REFERENCES Department(DepartmentID)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT uq_clearance UNIQUE (StudentID, DepartmentID)
);

-- =====================================================================
-- Sample seed data for CuyoTech SSIS
-- =====================================================================

-- 1. Departments
INSERT INTO Department (DepartmentID, DepartmentName, HeadName) VALUES
(1, 'College of Computing Studies', 'Dr. Juan Dela Cruz'),
(2, 'Registrar''s Office', 'Ms. Maria Santos'),
(3, 'Cashier''s Office', 'Mr. Pedro Reyes'),
(4, 'University Library', 'Mrs. Elena Ramos')
ON DUPLICATE KEY UPDATE DepartmentName=VALUES(DepartmentName), HeadName=VALUES(HeadName);

-- 2. Students (Default password: password123)
INSERT INTO Student (StudentID, FirstName, LastName, Email, PasswordHash, Program, YearLevel, Status, Address) VALUES
(1, 'Juan', 'Dela Cruz', 'juan.delacruz@cuyotech.edu', '$2y$10$mNBM.jF8gDY8UC8bFhvEYunvpKUH4kFIcBUtYgg0QHUJfPQe7rzWi', 'BS Information Technology', 3, 'Active', 'Brgy. San Pedro, Puerto Princesa City, Palawan 5300'),
(2, 'Maria', 'Santos', '2024-00412@cuyotech.edu', '$2y$10$mNBM.jF8gDY8UC8bFhvEYunvpKUH4kFIcBUtYgg0QHUJfPQe7rzWi', 'BS Accountancy', 1, 'Active', 'Brgy. Bancao-Bancao, Puerto Princesa City, Palawan 5300'),
(3, 'Paolo', 'Gomez', '2020-00087@cuyotech.edu', '$2y$10$mNBM.jF8gDY8UC8bFhvEYunvpKUH4kFIcBUtYgg0QHUJfPQe7rzWi', 'BS Information Technology', 4, 'Deactivated', 'Brgy. San Jose, Puerto Princesa City, Palawan 5300')
ON DUPLICATE KEY UPDATE FirstName=VALUES(FirstName);

-- 3. Admin Users (Default password: password123)
INSERT INTO AdminUser (AdminID, Username, PasswordHash, Role, DepartmentID) VALUES
(1, 'ana.reyes',   '$2y$10$y12j1f6qwbPO3gNhN7D9EORi0OJw9SyWIle0qEbqCZNdK..iYfxv2', 'Administrator', 2),
(2, 'm.santiago',  '$2y$10$y12j1f6qwbPO3gNhN7D9EORi0OJw9SyWIle0qEbqCZNdK..iYfxv2', 'Registrar', 2),
(3, 'cashier01',   '$2y$10$y12j1f6qwbPO3gNhN7D9EORi0OJw9SyWIle0qEbqCZNdK..iYfxv2', 'Cashier', 3),
(4, 'dept.staff',  '$2y$10$y12j1f6qwbPO3gNhN7D9EORi0OJw9SyWIle0qEbqCZNdK..iYfxv2', 'DepartmentStaff', 1)
ON DUPLICATE KEY UPDATE
    Username=VALUES(Username),
    PasswordHash=VALUES(PasswordHash),
    Role=VALUES(Role),
    DepartmentID=VALUES(DepartmentID);

-- 4. Courses
INSERT INTO Course (CourseID, CourseCode, CourseName, Units, DepartmentID) VALUES
(1, 'IT 311', 'Web Systems and Technologies', 3, 1),
(2, 'IT 312', 'Database Management Systems', 3, 1),
(3, 'IT 313', 'Information Assurance', 3, 1),
(4, 'IT 314', 'Systems Analysis and Design', 3, 1),
(5, 'IT 221', 'Object-Oriented Programming', 3, 1),
(6, 'IT 222', 'Data Structures and Algorithms', 3, 1)
ON DUPLICATE KEY UPDATE CourseName=VALUES(CourseName);

-- 5. Enrollments (Grades and course enrollments for Juan)
INSERT INTO Enrollment (StudentID, CourseID, SchoolYear, Semester, Grade) VALUES
(1, 1, '2025-2026', '1st', '1.25'),
(1, 2, '2025-2026', '1st', '1.50'),
(1, 3, '2025-2026', '1st', '1.75'),
(1, 4, '2025-2026', '1st', NULL),
(1, 5, '2024-2025', '2nd', '1.75'),
(1, 6, '2024-2025', '2nd', '2.00')
ON DUPLICATE KEY UPDATE Grade=VALUES(Grade);

-- 6. Clearance for Student 1
INSERT INTO Clearance (StudentID, DepartmentID, Status, DateCleared) VALUES
(1, 4, 'Cleared', '2025-10-01'),
(1, 3, 'Pending', NULL),
(1, 1, 'Cleared', '2025-09-28')
ON DUPLICATE KEY UPDATE Status=VALUES(Status);

-- 7. Payments for Student 1
INSERT INTO Payment (StudentID, Amount, PaymentDate, ORNumber, PaymentType) VALUES
(1, 1250.00, '2025-09-15', 'OR-2025-0182', 'Tuition')
ON DUPLICATE KEY UPDATE Amount=VALUES(Amount);

-- 8. Document Requests
INSERT INTO DocumentRequest (RequestID, StudentID, DocumentType, RequestDate, Status, ProcessedByAdminID) VALUES
(48, 1, 'Transcript of Records', '2025-10-02', 'Processing', 2),
(31, 1, 'Certification', '2025-09-18', 'Released', 2),
(24, 1, 'Certificate of Registration', '2025-09-10', 'Pending', NULL)
ON DUPLICATE KEY UPDATE Status=VALUES(Status);

-- =====================================================================
-- Notes:
-- 1. PasswordHash stores bcrypt hash of 'password123'.
-- 2. Schema is normalized to 3NF: no repeating groups (1NF), all non-key
--    attributes fully dependent on their table's whole primary key (2NF),
--    and no non-key attribute depends on another non-key attribute (3NF).
-- =====================================================================
