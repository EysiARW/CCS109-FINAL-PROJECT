"""
Generate Deliverable 08: Presentation Deck (.pptx) (Aquino - Presentation Lead)
Outputs: PPTX in deliverables/08_Aquino_Presentation_Deck/SSIS_Final_Project_Presentation.pptx
"""

import os
from pptx import Presentation
from pptx.util import Inches, Pt
from pptx.dml.color import RGBColor
from pptx.enum.text import PP_ALIGN
from pptx.enum.shapes import MSO_SHAPE

OUT_DIR = os.path.abspath("deliverables/08_Aquino_Presentation_Deck")
os.makedirs(OUT_DIR, exist_ok=True)

# Colors
NAVY = RGBColor(30, 58, 138)       # #1e3a8a
GOLD = RGBColor(217, 119, 6)       # #d97706
DARK = RGBColor(15, 23, 42)        # #0f172a
WHITE = RGBColor(255, 255, 255)
LIGHT_BG = RGBColor(248, 250, 252) # #f8fafc
MUTED = RGBColor(100, 116, 139)    # #64748b
CARD_BORDER = RGBColor(203, 213, 225)

def create_slide_header(slide, title_text, category_text="CUYOTECH SSIS | CCS109 CASE STUDY DEFENSE"):
    # Category / Super-title
    cat_box = slide.shapes.add_textbox(Inches(0.8), Inches(0.4), Inches(11.7), Inches(0.4))
    tf_c = cat_box.text_frame
    tf_c.word_wrap = True
    p_c = tf_c.paragraphs[0]
    p_c.text = category_text.upper()
    p_c.font.size = Pt(10)
    p_c.font.bold = True
    p_c.font.color.rgb = GOLD

    # Main Slide Title
    title_box = slide.shapes.add_textbox(Inches(0.8), Inches(0.7), Inches(11.7), Inches(0.8))
    tf_t = title_box.text_frame
    tf_t.word_wrap = True
    p_t = tf_t.paragraphs[0]
    p_t.text = title_text
    p_t.font.size = Pt(24)
    p_t.font.bold = True
    p_t.font.color.rgb = NAVY

    # Divider bar
    line = slide.shapes.add_shape(MSO_SHAPE.RECTANGLE, Inches(0.8), Inches(1.5), Inches(11.7), Inches(0.04))
    line.fill.solid()
    line.fill.fore_color.rgb = GOLD
    line.line.color.rgb = GOLD

def add_card(slide, left, top, width, height, title, body_bullets, card_bg=LIGHT_BG):
    shape = slide.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, left, top, width, height)
    shape.fill.solid()
    shape.fill.fore_color.rgb = card_bg
    shape.line.color.rgb = CARD_BORDER
    shape.line.width = Pt(1)

    tf = shape.text_frame
    tf.word_wrap = True
    tf.margin_left = Inches(0.25)
    tf.margin_right = Inches(0.25)
    tf.margin_top = Inches(0.25)
    tf.margin_bottom = Inches(0.25)

    if title:
        p_t = tf.paragraphs[0]
        p_t.text = title
        p_t.font.bold = True
        p_t.font.size = Pt(14)
        p_t.font.color.rgb = NAVY
        p_t.space_after = Pt(8)
    
    first = True if not title else False
    for bullet in body_bullets:
        p = tf.paragraphs[0] if first else tf.add_paragraph()
        first = False
        p.text = bullet
        p.font.size = Pt(11)
        p.font.color.rgb = DARK
        p.space_after = Pt(4)
        p.level = 0

def generate_deck():
    prs = Presentation()
    prs.slide_width = Inches(13.333)
    prs.slide_height = Inches(7.5)
    blank_layout = prs.slide_layouts[6]

    logo_path = os.path.abspath("CuyoTech_University_Logo_sm.png")

    # ==================== SLIDE 1: Title Slide ====================
    s1 = prs.slides.add_slide(blank_layout)
    bg1 = s1.shapes.add_shape(MSO_SHAPE.RECTANGLE, 0, 0, Inches(13.333), Inches(7.5))
    bg1.fill.solid()
    bg1.fill.fore_color.rgb = NAVY
    bg1.line.fill.background()

    if os.path.exists(logo_path):
        s1.shapes.add_picture(logo_path, Inches(5.8), Inches(0.8), Inches(1.7), Inches(1.7))

    t_box = s1.shapes.add_textbox(Inches(1.0), Inches(2.7), Inches(11.333), Inches(2.5))
    tf1 = t_box.text_frame
    tf1.word_wrap = True
    
    p1 = tf1.paragraphs[0]
    p1.text = "Student Services Information System (SSIS)"
    p1.alignment = PP_ALIGN.CENTER
    p1.font.size = Pt(36)
    p1.font.bold = True
    p1.font.color.rgb = WHITE
    p1.space_after = Pt(8)

    p2 = tf1.add_paragraph()
    p2.text = "CuyoTech University | College of Computing Studies | CCS109 Case Study Defense"
    p2.alignment = PP_ALIGN.CENTER
    p2.font.size = Pt(16)
    p2.font.color.rgb = GOLD
    p2.space_after = Pt(24)

    p3 = tf1.add_paragraph()
    p3.text = "Team: Bautista (Lead) • Aquino (Scrum Master) • Baltazar & Capoy (Devs) • Balbero (QA)"
    p3.alignment = PP_ALIGN.CENTER
    p3.font.size = Pt(13)
    p3.font.color.rgb = RGBColor(226, 232, 240)

    # ==================== SLIDE 2: Executive Summary & Motivation ====================
    s2 = prs.slides.add_slide(blank_layout)
    create_slide_header(s2, "Executive Summary: The Digital Transformation of CuyoTech")
    add_card(s2, Inches(0.8), Inches(1.8), Inches(5.6), Inches(5.0), "The Legacy Dilemma (Manual Bottlenecks)", [
        "• Paper-Based Requests: Long student queues for grades, certifications, and TOR transcripts.",
        "• Disconnected Registrars: Physical grade encoding logbooks prone to transcription errors and misplacement.",
        "• Cashier Vulnerabilities: Manual receipt booklets without automated reconciliation or verification.",
        "• Clearance Silos: Fragmented department clearances requiring students to physically walk across campus.",
        "• Data Privacy Concerns: Plaintext record exposure violating statutory mandates under RA 10173."
    ])
    add_card(s2, Inches(6.8), Inches(1.8), Inches(5.6), Inches(5.0), "The SSIS Solution (Unified Web Ecosystem)", [
        "• Unified Role-Based Platform: Tailored portals for Students, Registrars, Cashiers, Dept Chairs, and Admins.",
        "• Pure PHP 8.2 & 3NF MySQL: Zero Node.js, zero build steps, ultra-fast sub-300ms execution with demo fallback.",
        "• Real-Time Academic Computation: Live semester GPA calculation and instant credential request workflows.",
        "• Official Receipt Generation: Sequentially tracked counter receipts with printable verification watermarks.",
        "• Automated Quality Assurance: 100% test pass rate across 36 automated unit, integration, and security checks."
    ])

    # ==================== SLIDE 3: System Roles & Team Structure ====================
    s3 = prs.slides.add_slide(blank_layout)
    create_slide_header(s3, "Project Governance: Scrum Roles & Responsibilities")
    add_card(s3, Inches(0.8), Inches(1.8), Inches(2.7), Inches(5.0), "Bautista", [
        "Role: Team Leader / Product Owner",
        "Key Deliverables:",
        "• Requirements Gathering (SRS)",
        "• Backlog Prioritization (MoSCoW)",
        "• Legal & Ethical Compliance Audit",
        "• Pre-Submission Final Review"
    ])
    add_card(s3, Inches(3.8), Inches(1.8), Inches(2.7), Inches(5.0), "Aquino", [
        "Role: SCRUM Master & Lead",
        "Key Deliverables:",
        "• Agile/Scrum SDLC Planning",
        "• 8-Week Gantt Chart Schedule",
        "• System Modeling (UML Diagrams)",
        "• Defense Presentation Lead"
    ])
    add_card(s3, Inches(6.8), Inches(1.8), Inches(2.7), Inches(5.0), "Baltazar & Capoy", [
        "Role: Developers / Designers",
        "Key Deliverables:",
        "• Pure PHP MVC Architecture",
        "• 3NF Relational Database Schema",
        "• 15 Figma Wireframes Catalog",
        "• University Responsive CSS Tokens"
    ])
    add_card(s3, Inches(9.8), Inches(1.8), Inches(2.7), Inches(5.0), "Balbero", [
        "Role: QA Engineer / Security Lead",
        "Key Deliverables:",
        "• IEEE 829 Software Test Plan",
        "• Automated QA Suite (36 Tests)",
        "• OWASP Penetration Audit",
        "• Master System Documentation"
    ])

    # ==================== SLIDE 4: Agile SDLC & 8-Week Schedule ====================
    s4 = prs.slides.add_slide(blank_layout)
    create_slide_header(s4, "SDLC Methodology & Sprint Timeline")
    add_card(s4, Inches(0.8), Inches(1.8), Inches(5.6), Inches(5.0), "Agile/Scrum Framework Highlights", [
        "• 4 Iterative Sprints: 2-week time-boxed cycles for adaptive delivery and continuous stakeholder feedback.",
        "• Ceremony Cadence: Bi-weekly Sprint Planning, Tri-weekly Standups, and End-of-Sprint Reviews with Live Demos.",
        "• Definition of Ready (DoR): User story format, acceptance criteria, story points, and schema readiness.",
        "• Definition of Done (DoD): Zero PHP lint errors, 100% PDO prepared statements, responsive UI, 100% test pass rate."
    ])
    add_card(s4, Inches(6.8), Inches(1.8), Inches(5.6), Inches(5.0), "Gantt Milestones & Critical Path", [
        "• Sprint 1 (Weeks 1-3): Architecture setup, 3NF database schema, core MVC engine, and secure login portal.",
        "• Sprint 2 (Weeks 4-5): Student profile, enrolled subjects directory, GPA computation, and document requests.",
        "• Sprint 3 (Weeks 5-6): Cashier counter payment recording, Official Receipt generation, and department clearance.",
        "• Sprint 4 (Weeks 6-8): Admin user management, automated test suite execution (36 tests), and final documentation.",
        "• Critical Path: Requirements -> 3NF Schema -> MVC Engine -> Modules -> QA Suite -> Final Defense."
    ])

    # ==================== SLIDE 5: 3NF Relational Database Design ====================
    s5 = prs.slides.add_slide(blank_layout)
    create_slide_header(s5, "Database Architecture: 3NF Relational Normalization")
    add_card(s5, Inches(0.8), Inches(1.8), Inches(5.6), Inches(5.0), "Normalization Progression (UNF -> 3NF)", [
        "• Unnormalized Form (UNF): Fragmented spreadsheets with repeating subject groups and duplicated student biodata.",
        "• 1NF Achieved: Eliminated repeating groups, enforced atomic attributes, defined primary keys on all entities.",
        "• 2NF Achieved: Eliminated partial functional dependencies by isolating Course and Student data from Enrollment junction.",
        "• 3NF Certified: Eliminated transitive dependencies (e.g. moving Department metadata and Payment details into distinct tables)."
    ])
    add_card(s5, Inches(6.8), Inches(1.8), Inches(5.6), Inches(5.0), "8 Normalized Tables in Production", [
        "1. Department: Academic colleges and offices (CCS, Registrar, Cashier).",
        "2. Student: Unique StudentID, programs, year levels, Bcrypt hashes.",
        "3. AdminUser: Administrative staff accounts with granular role strings.",
        "4. Course: Course catalog with course codes, units, and departments.",
        "5. Enrollment: Student-course many-to-many relationship with term grades.",
        "6. Payment: Counter payments with unique sequential Official Receipts.",
        "7. DocumentRequest: Fulfillment tracking (TOR, COR, Good Moral).",
        "8. Clearance: Cross-department evaluation (Cleared, Hold, Pending)."
    ])

    # ==================== SLIDE 6: Pure PHP MVC Architecture ====================
    s6 = prs.slides.add_slide(blank_layout)
    create_slide_header(s6, "Software Architecture: Clean MVC & Repository Pattern")
    add_card(s6, Inches(0.8), Inches(1.8), Inches(3.7), Inches(5.0), "Controllers Layer", [
        "Namespace: App\\Controllers",
        "• AuthController: Multi-role authentication & session initialization.",
        "• AcademicController: Enrolled courses & real-time GPA recalculation.",
        "• RequestController: Document requests submission & fee math.",
        "• CashierController: Counter payment recording & OR view.",
        "• RegistrarController: Enrollment roster & grade encoding.",
        "• DepartmentController: Clearance status evaluations.",
        "• AdminController: User CRUD & global request fulfillment."
    ])
    add_card(s6, Inches(4.8), Inches(1.8), Inches(3.7), Inches(5.0), "Repositories Layer", [
        "Namespace: App\\Repositories",
        "• StudentRepository: Parameterized student queries.",
        "• CourseRepository: Catalog and enrollment joins.",
        "• EnrollmentRepository: Grade mutation & rosters.",
        "• PaymentRepository: Counter ledger & OR generator.",
        "• RequestRepository: Multi-step fulfillment pipeline.",
        "• ClearanceRepository: Department hold matrix.",
        "• 100% PDO Prepared Statements: Zero dynamic SQL string concatenation."
    ])
    add_card(s6, Inches(8.8), Inches(1.8), Inches(3.7), Inches(5.0), "Presentation & Security", [
        "Namespace: App\\Utils & views/",
        "• Modular Layout Engine: Clean master layouts with view partials.",
        "• Output Sanitization: 100% user data filtered via htmlspecialchars().",
        "• Demo Mode Engine: Graceful memory fallback when MySQL is offline.",
        "• Zero External Dependencies: Fast execution on native PHP 8.2 without npm/Node."
    ])

    # ==================== SLIDE 7: System Modeling (UML Diagrams) ====================
    s7 = prs.slides.add_slide(blank_layout)
    create_slide_header(s7, "System Modeling: Use Case & Sequence Workflows")
    add_card(s7, Inches(0.8), Inches(1.8), Inches(5.6), Inches(5.0), "Actors & Use Case Distribution", [
        "• Student (Primary): Inspect profile, view grades & GPA, request documents, check clearance, view receipts.",
        "• Registrar (Staff): Encode term grades, manage course enrollment rosters, process academic credentials.",
        "• Cashier (Staff): Process tuition payments, issue official receipts (OR), search transaction ledger.",
        "• Department Staff: Evaluate collegiate obligations, apply/release departmental clearance holds.",
        "• Administrator (Superuser): Provision user accounts, assign roles, oversee university-wide requests."
    ])
    add_card(s7, Inches(6.8), Inches(1.8), Inches(5.6), Inches(5.0), "Document Request Lifecycle", [
        "1. Initiation: Student selects document type & copy count via modal (?page=requests).",
        "2. Fee Calculation: Dynamic pricing calculated client/server-side (₱50 to ₱150 per copy).",
        "3. Database Commit: Inserted into DocumentRequest table with status 'Pending'.",
        "4. Staff Processing: Registrar / Admin reviews request and updates status to 'Processing'.",
        "5. Final Release: Certificate generated; status updated to 'Released' with completion timestamp."
    ])

    # ==================== SLIDE 8: UI/UX Design System Showcase ====================
    s8 = prs.slides.add_slide(blank_layout)
    create_slide_header(s8, "UI/UX Design System & 15 Wireframe Catalog")
    add_card(s8, Inches(0.8), Inches(1.8), Inches(5.6), Inches(5.0), "Institutional Design Language", [
        "• CuyoTech Navy (#1e3a8a): Conveys academic authority; utilized in navigation, table headers, and primary actions.",
        "• Academic Gold (#d97706): Highlights active states, GPA milestones, and important badge indicators.",
        "• Canvas Gray (#f8fafc): Clean, low-contrast background preventing visual fatigue during dense data entry.",
        "• Slate Dark (#0f172a): Exceeds WCAG AAA contrast ratio guidelines for maximum readability.",
        "• Fully Responsive Layout: Mobile-adaptive viewports with collapsible navigation drawers."
    ])
    add_card(s8, Inches(6.8), Inches(1.8), Inches(5.6), Inches(5.0), "15 Captured Wireframes Breakdown", [
        "• Views 01-04: Sign-In Portal, Student Profile, Enrolled Subjects, Academic Grades & GPA.",
        "• Views 05-06: Document Request History & Dynamic Submission Modal.",
        "• Views 07-09: Registrar Enrollment Roster, Registration Modal, and Live Grade Encoding Grid.",
        "• Views 10-12: Cashier Payment Ledger, Payment Recording Modal, and Printable Official Receipt.",
        "• Views 13-15: Department Clearance Matrix, Admin User Management, and Document Fulfillment Queue."
    ])

    # ==================== SLIDE 9: Student Portal Showcase ====================
    s9 = prs.slides.add_slide(blank_layout)
    create_slide_header(s9, "Module Walkthrough: Student Self-Service Portal")
    add_card(s9, Inches(0.8), Inches(1.8), Inches(3.7), Inches(5.0), "Student Profile (?page=profile)", [
        "• Personal Biodata: Student ID (2021-00123), Full Name, Email, Address.",
        "• Academic Status: BSIT Program, 3rd Year Level, Active Standing.",
        "• Self-Service Security: In-portal password update with live Bcrypt mutation.",
        "• Institutional Identity: Profile avatar placeholder & college affiliation."
    ])
    add_card(s9, Inches(4.8), Inches(1.8), Inches(3.7), Inches(5.0), "Academic Grades (?page=grades)", [
        "• Term-by-Term Grid: Course Code, Course Description, Units, Grade, Remarks.",
        "• Automated GPA Engine: Mathematically computes weighted grade point average.",
        "• Status Highlighting: Passing grades (1.00 - 3.00) highlighted in clean green badges.",
        "• Deficiencies Tracking: Displays INC or Hold indicators dynamically."
    ])
    add_card(s9, Inches(8.8), Inches(1.8), Inches(3.7), Inches(5.0), "Document Requests (?page=requests)", [
        "• Request Tracking: Live table of all past and active document filings.",
        "• Price Calculator: Automated fee multiplication based on requested copies.",
        "• Status Lifecycle: Pending -> Processing -> Ready -> Released.",
        "• Self-Cancellation: Pending requests can be canceled with one click."
    ])

    # ==================== SLIDE 10: Registrar Operations ====================
    s10 = prs.slides.add_slide(blank_layout)
    create_slide_header(s10, "Module Walkthrough: Registrar Operations")
    add_card(s10, Inches(0.8), Inches(1.8), Inches(5.6), Inches(5.0), "Student Enrollment (?page=enrollment)", [
        "• Roster Directory: View students registered across academic sections and terms.",
        "• Multi-Filter Controls: Filter by School Year (2025-2026) and Semester (1st/2nd).",
        "• Course Registration Dialogue: Fast modal dialogue to link student IDs to curriculum course IDs.",
        "• Unit Constraint Verification: Validates unit loads against degree curriculum standards."
    ])
    add_card(s10, Inches(6.8), Inches(1.8), Inches(5.6), Inches(5.0), "Grades Encoding (?page=grades-encoding)", [
        "• Section-Based Encoding: Select course section to populate enrolled class roster.",
        "• Real-Time Grade Mutation: Enter numeric marks (1.00, 1.25, 1.50 ... 5.00) with instant database save.",
        "• Live Remarks Evaluation: Automatic classification into 'Passed', 'Failed', or 'Incomplete'.",
        "• Instant Recalculation: Updates student portal transcripts and GPA calculations immediately."
    ])

    # ==================== SLIDE 11: Cashier & Payments ====================
    s11 = prs.slides.add_slide(blank_layout)
    create_slide_header(s11, "Module Walkthrough: Cashier Operations & Official Receipts")
    add_card(s11, Inches(0.8), Inches(1.8), Inches(5.6), Inches(5.0), "Counter Payments Ledger (?page=payments)", [
        "• Transaction Ledger: Chronological log of all student tuition and fee settlements.",
        "• Search & Filter: Search by Student ID, student name, or Official Receipt (OR) number.",
        "• Payment Recording Modal: Enter student ID, select payment category, input amount in Philippine Pesos (₱).",
        "• Sequential OR Generation: Automatically generates unique sequential receipt numbers (e.g. OR-10023)."
    ])
    add_card(s11, Inches(6.8), Inches(1.8), Inches(5.6), Inches(5.0), "Printable Official Receipt (?page=receipts)", [
        "• Standard Institutional Format: Matches CuyoTech University official accounting receipt standards.",
        "• Itemized Financial Breakdown: Clearly states tuition, document certification, and lab fee line items.",
        "• Cashier Verification Watermark: Displays collecting cashier username and digital signature watermark.",
        "• Browser Print Optimized: Formatted specifically for standard 8.5 x 11 inch paper printing without screen headers."
    ])

    # ==================== SLIDE 12: Clearance & Administration ====================
    s12 = prs.slides.add_slide(blank_layout)
    create_slide_header(s12, "Module Walkthrough: Clearance & Administrative Governance")
    add_card(s12, Inches(0.8), Inches(1.8), Inches(5.6), Inches(5.0), "Department Clearance (?page=clearance)", [
        "• Multi-Department Evaluation: Evaluates student compliance across CCS, Library, Cashier, and Registrar.",
        "• Status Toggles: Academic chairs can toggle student status between 'Cleared', 'Pending', and 'Hold'.",
        "• Remarks & Deficiencies: Department staff can record specific reasons for administrative holds.",
        "• Graduation & Enrollment Gates: Automatically blocks credential release when active holds exist."
    ])
    add_card(s12, Inches(6.8), Inches(1.8), Inches(5.6), Inches(5.0), "Admin User Management (?page=admin-users)", [
        "• Centralized Account CRUD: Provision and deactivate student, registrar, cashier, and staff accounts.",
        "• Granular Role Assignment: Strict assignment of system roles enforcing Segregation of Duties (SoD).",
        "• Request Fulfillment Queue: Global administrative oversight across all pending institutional requests.",
        "• Auditability: Administrative changes are permanently timestamped and attributed."
    ])

    # ==================== SLIDE 13: QA Testing & Automated Execution ====================
    s13 = prs.slides.add_slide(blank_layout)
    create_slide_header(s13, "Quality Assurance: Automated Test Execution (100% Pass)")
    add_card(s13, Inches(0.8), Inches(1.8), Inches(5.6), Inches(5.0), "IEEE 829 Software Test Plan Execution", [
        "• Automated Test Suite: Developed and executed by Lead QA Balbero (scripts/run_automated_tests.php).",
        "• Total Test Cases Executed: 36 Tests Run | 36 Passed | 0 Failed | 100.0% Pass Rate.",
        "• Test Execution Date: October 13, 2026 against live XAMPP MySQL database.",
        "• Suite 1 (Database): Active connection & verification of all 8 3NF relational tables.",
        "• Suite 2 (Auth): Multi-role logins (Student, Admin, Cashier, Registrar, DeptStaff) & negative rejection."
    ])
    add_card(s13, Inches(6.8), Inches(1.8), Inches(5.6), Inches(5.0), "Test Suite Breakdown & Verification", [
        "• Suite 3 (Crypto): Verified BCrypt ($2y$10$) hashing algorithm and salt verification.",
        "• Suite 4 (Security): 5 SQL injection attack probes defeated (UNION SELECT, OR 1=1, DROP TABLE).",
        "• Suite 5 (Repositories): Verified queries across all seven (7) repository classes.",
        "• Suite 6 (Math Logic): Verified weighted GPA formula accuracy against hand calculations.",
        "• Suite 7 (XSS): Verified htmlspecialchars sanitization against malicious script payloads.",
        "• Suite 8 (RBAC): Enforced role-based permission boundaries across all five user roles."
    ])

    # ==================== SLIDE 14: Security Penetration Probes ====================
    s14 = prs.slides.add_slide(blank_layout)
    create_slide_header(s14, "Security Architecture & OWASP Vulnerability Audit")
    add_card(s14, Inches(0.8), Inches(1.8), Inches(5.6), Inches(5.0), "OWASP Top 10 Attack Probes Defeated", [
        "• SQL Injection (A03): Penetration probes injecting UNION SELECT and comment bypasses failed completely.",
        "• Defense: 100% of SQL statements parameterized via PDO prepared statements with bound parameters.",
        "• Cross-Site Scripting (A03): Injected script payloads (<script>alert()</script>) neutralized via htmlspecialchars.",
        "• Broken Access Control (A01): Route tampering (?page=admin) blocked via server-side session role checks."
    ])
    add_card(s14, Inches(6.8), Inches(1.8), Inches(5.6), Inches(5.0), "Authentication & Cryptographic Safeguards", [
        "• Cryptographic Failures (A02): Zero plaintext passwords stored. BCrypt with individual salts.",
        "• Identification Failures (A07): Session fixation prevented via session_regenerate_id() on sign-in.",
        "• Security Misconfiguration (A05): Centralized exception catching prevents leaking database credentials.",
        "• Zero High-Risk Findings: Formal QA audit verified zero open vulnerabilities prior to defense."
    ])

    # ==================== SLIDE 15: Ethical Analysis & RA 10173 ====================
    s15 = prs.slides.add_slide(blank_layout)
    create_slide_header(s15, "Ethical Governance & Data Privacy Act (RA 10173)")
    add_card(s15, Inches(0.8), Inches(1.8), Inches(5.6), Inches(5.0), "Philippine Data Privacy Act (RA 10173)", [
        "• Principle of Transparency: Clear student profile views explicitly displaying collected personal data.",
        "• Legitimate Purpose: Processing strictly bounded by educational service delivery (records, billing, clearances).",
        "• Principle of Proportionality: Minimum necessary data points collected; no intrusive biographical profiling.",
        "• Student Privacy Rights: Full 24/7 access to inspect records and self-rectify account security credentials."
    ])
    add_card(s15, Inches(6.8), Inches(1.8), Inches(5.6), Inches(5.0), "Institutional Segregation of Duties (SoD)", [
        "• Anti-Fraud Separation: Cashiers cannot encode grades; Registrars cannot accept cash collections.",
        "• Clearance Autonomy: Department chairs evaluate academic obligations independently.",
        "• Auditability: Every grade modification, clearance decision, and official receipt is permanently attributed.",
        "• Ethical Computing: Fiduciary duty of care maintained toward students' academic and financial data."
    ])

    # ==================== SLIDE 16: Zero-Dependency Portability ====================
    s16 = prs.slides.add_slide(blank_layout)
    create_slide_header(s16, "Engineering Excellence: Zero-Dependency Architecture")
    add_card(s16, Inches(0.8), Inches(1.8), Inches(5.6), Inches(5.0), "Zero-Dependency Runtime Benefits", [
        "• No Node.js / No npm: Eliminates dependency bloat, node_modules vulnerabilities, and build tool fragility.",
        "• Instant Execution: Runs on any standard PHP 8.2 runtime (XAMPP, WAMP, or cloud Linux servers).",
        "• Sub-300ms Page Loads: Pure server-side rendering delivers instantaneous response times.",
        "• PSR-4 Autoloading: Clean namespace-based class loading without heavy frameworks."
    ])
    add_card(s16, Inches(6.8), Inches(1.8), Inches(5.6), Inches(5.0), "Automated Demo Fallback Engine", [
        "• Offline Resilience: If MySQL is unavailable, system gracefully activates in-memory session demo mode.",
        "• Continuous Evaluation: Examiners and evaluators can inspect all views without database configuration hurdles.",
        "• One-Click Seeding: Status banner allows one-click database re-seeding directly from the browser.",
        "• Multi-Port Probing: Automatically detects whether MySQL runs on port 3306 or 3307."
    ])

    # ==================== SLIDE 17: Pre-Submission Review & Checklist ====================
    s17 = prs.slides.add_slide(blank_layout)
    create_slide_header(s17, "Quality Gate: Pre-Submission Final Review Checklist")
    add_card(s17, Inches(0.8), Inches(1.8), Inches(5.6), Inches(5.0), "Technical Deliverables Verification", [
        "✔ 3NF Database Normalization: 8 Relational Tables exceeding the 6-table requirement (100% Certified).",
        "✔ Zero Syntax Errors: All 44 PHP codebase files passed strict php -l linting without errors.",
        "✔ IEEE 829 Test Suite: 36 of 36 automated QA and security test cases passed (100% Success Rate).",
        "✔ 15 Wireframes Catalog: High-fidelity Figma wireframes fully captured and documented.",
        "✔ Role-Based Workflows: Complete modules for Student, Registrar, Cashier, Dept, and Admin."
    ])
    add_card(s17, Inches(6.8), Inches(1.8), Inches(5.6), Inches(5.0), "Documentation & Deliverables Package", [
        "✔ Requirements & Backlog: Formal SRS & MoSCoW Product Backlog in DOCX & PDF.",
        "✔ SDLC & Gantt Schedule: Agile/Scrum plan & 8-week project Gantt chart in DOCX & PDF.",
        "✔ System Modeling: Use Case, Sequence, Class, Activity, and DFD diagrams in DOCX & PDF.",
        "✔ Ethical Review: RA 10173 Data Privacy assessment & final audit checklist in DOCX & PDF.",
        "✔ Master Documentation: Consolidated 10-chapter project manual in DOCX & PDF.",
        "✔ Presentation Deck: Professional 18-slide PowerPoint (.pptx) deck ready for defense."
    ])

    # ==================== SLIDE 18: Conclusion & Defense Q&A ====================
    s18 = prs.slides.add_slide(blank_layout)
    bg18 = s18.shapes.add_shape(MSO_SHAPE.RECTANGLE, 0, 0, Inches(13.333), Inches(7.5))
    bg18.fill.solid()
    bg18.fill.fore_color.rgb = NAVY
    bg18.line.fill.background()

    if os.path.exists(logo_path):
        s18.shapes.add_picture(logo_path, Inches(5.8), Inches(1.2), Inches(1.7), Inches(1.7))

    t_box18 = s18.shapes.add_textbox(Inches(1.0), Inches(3.2), Inches(11.333), Inches(3.0))
    tf18 = t_box18.text_frame
    tf18.word_wrap = True

    p1 = tf18.paragraphs[0]
    p1.text = "Thank You!"
    p1.alignment = PP_ALIGN.CENTER
    p1.font.size = Pt(40)
    p1.font.bold = True
    p1.font.color.rgb = WHITE
    p1.space_after = Pt(12)

    p2 = tf18.add_paragraph()
    p2.text = "CuyoTech Student Services Information System (SSIS)"
    p2.alignment = PP_ALIGN.CENTER
    p2.font.size = Pt(20)
    p2.font.bold = True
    p2.font.color.rgb = GOLD
    p2.space_after = Pt(16)

    p3 = tf18.add_paragraph()
    p3.text = "We are now ready for the Case Study Defense & Panel Questions."
    p3.alignment = PP_ALIGN.CENTER
    p3.font.size = Pt(14)
    p3.font.color.rgb = RGBColor(226, 232, 240)

    pptx_path = os.path.join(OUT_DIR, "SSIS_Final_Project_Presentation.pptx")
    prs.save(pptx_path)
    print(f"Generated PPTX: {pptx_path}")
    return pptx_path

if __name__ == "__main__":
    generate_deck()
