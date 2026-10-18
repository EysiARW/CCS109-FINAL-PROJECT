"""
Generate Deliverable 01: Requirements Gathering & Backlog Prioritization (Bautista - Team Leader)
Outputs: DOCX & PDF in deliverables/01_Bautista_Requirements_Gathering_and_Backlog/
"""

import os
from docx.shared import Inches, Pt
from doc_builder import (
    create_base_docx, add_heading_1, add_heading_2, add_heading_3,
    add_paragraph, add_bullet, add_callout, add_table,
    build_html_wrapper, html_to_pdf
)

OUT_DIR = os.path.abspath("deliverables/01_Bautista_Requirements_Gathering_and_Backlog")
os.makedirs(OUT_DIR, exist_ok=True)

def generate_docx():
    doc = create_base_docx(
        title="Software Requirements Specification (SRS)\n& Prioritized Product Backlog",
        subtitle="Comprehensive Functional & Non-Functional Specifications with MoSCoW Prioritization",
        author="Bautista",
        role="Team Leader / Product Owner",
        deliverable_code="TASK-1A & 1B (Bautista)"
    )

    add_heading_1(doc, "1. Executive Summary & Problem Context")
    add_paragraph(doc, 
        "Prior to the implementation of the Student Services Information System (SSIS), CuyoTech University operated "
        "through disconnected manual logbooks, paper request slips, and decentralized spreadsheets across offices. "
        "Students experienced lengthy queueing times for transcript and certification requests, registrar staff faced "
        "manual grade consolidation delays, and cashier reconciliation suffered from disconnected fee tracking."
    )
    add_paragraph(doc,
        "The SSIS establishes a unified, role-based web architecture backed by a 3NF relational database (MySQL) "
        "to digitize core institutional workflows: student profile access, grade inquiry, enrollment processing, "
        "official document issuance, counter payment recording, and cross-department clearance evaluations."
    )

    add_callout(doc,
        "This requirements baseline was gathered and curated through stakeholder interviews with university registrars, "
        "cashiers, department chairs, and collegiate student councils during Sprint 0 of the CCS109 project lifecycle.",
        title="REQUIREMENTS BASELINE NOTICE"
    )

    add_heading_1(doc, "2. Stakeholder Profiles & User Personas")
    headers_persona = ["Stakeholder Role", "Target User Persona", "Primary System Needs", "Access Scope"]
    rows_persona = [
        ["Student", "Juan Dela Cruz (3rd Yr BSIT)", "View grades, verify enrolled units, file document requests, check clearance", "Self-service Student Portal"],
        ["Registrar Staff", "Maria Santiago (Registrar Officer)", "Enroll students into courses, encode & update term grades, process credentials", "Academic & Records Module"],
        ["Cashier Staff", "Rosa Ramos (University Cashier)", "Accept counter payments, issue official receipts (OR), track payment history", "Financial Operations Module"],
        ["Department Staff", "Dr. Alan Turing (CCS Department Chair)", "Review student departmental compliance, update clearance status (Hold/Cleared)", "Clearance Approval Module"],
        ["System Administrator", "Ana Reyes (Lead Administrator)", "Manage user accounts, configure system roles, oversee end-to-end request lifecycle", "SuperAdmin Control Panel"]
    ]
    add_table(doc, headers_persona, rows_persona, [Inches(1.2), Inches(1.5), Inches(2.3), Inches(1.5)])

    add_heading_1(doc, "3. Functional Requirements (FR)")
    add_paragraph(doc, "The functional requirements define the specific operational behaviors and features implemented within the SSIS:")

    add_heading_2(doc, "3.1 Module 1: Authentication & Access Control (FR-AUTH)")
    add_bullet(doc, "Allow students to authenticate using their unique Student ID (e.g. 2021-00123) or university email with password.", "FR-AUTH-01: ")
    add_bullet(doc, "Allow administrative staff to authenticate using designated staff usernames (e.g. ana.reyes, cashier01).", "FR-AUTH-02: ")
    add_bullet(doc, "Enforce strict Role-Based Access Control (RBAC) preventing privilege escalation across module routes.", "FR-AUTH-03: ")
    add_bullet(doc, "Provide secure session invalidation and automated logout safeguards upon session termination.", "FR-AUTH-04: ")

    add_heading_2(doc, "3.2 Module 2: Student Academic Portal (FR-STU)")
    add_bullet(doc, "Display comprehensive student biodata including curriculum program, year level, and residential address.", "FR-STU-01: ")
    add_bullet(doc, "Display enrolled subject schedule, section, units, professor, and classroom assignment.", "FR-STU-02: ")
    add_bullet(doc, "Provide term-by-term grade breakdown with automated, real-time semester GPA mathematical calculation.", "FR-STU-03: ")
    add_bullet(doc, "Allow self-service password update with live hash mutation in the underlying data store.", "FR-STU-04: ")

    add_heading_2(doc, "3.3 Module 3: Document Request Workflow (FR-DOC)")
    add_bullet(doc, "Allow students to request university documents (Transcript of Records, Certificate of Registration, Honorable Dismissal, Good Moral).", "FR-DOC-01: ")
    add_bullet(doc, "Calculate real-time document fees based on document type (₱50 - ₱150) multiplied by quantity of copies.", "FR-DOC-02: ")
    add_bullet(doc, "Track live request fulfillment state progression: Pending -> Processing -> Ready for Release -> Released.", "FR-DOC-03: ")
    add_bullet(doc, "Allow students to cancel pending requests prior to administrative processing.", "FR-DOC-04: ")

    add_heading_2(doc, "3.4 Module 4: Registrar Operations (FR-REG)")
    add_bullet(doc, "Provide student course enrollment capability linking StudentID to CourseID with term and school year parameters.", "FR-REG-01: ")
    add_bullet(doc, "Provide instructor grade encoding grid with real-time grade updates (1.00 - 5.00 grading scale or INC).", "FR-REG-02: ")

    add_heading_2(doc, "3.5 Module 5: Cashier & Financial Transactions (FR-CASH)")
    add_bullet(doc, "Record counter payments with student lookup, payment type selection, and automated OR number generation.", "FR-CASH-01: ")
    add_bullet(doc, "Generate printable, formatted University Official Receipts (OR) with itemized financial breakdown.", "FR-CASH-02: ")

    add_heading_2(doc, "3.6 Module 6: Department Clearance & Admin (FR-ADM)")
    add_bullet(doc, "Provide multi-department clearance evaluation grid allowing status updates between Pending, Cleared, and Hold.", "FR-ADM-01: ")
    add_bullet(doc, "Provide centralized User Account Management for creating and updating student and administrator credentials.", "FR-ADM-02: ")

    add_heading_1(doc, "4. Non-Functional Requirements (NFR)")
    headers_nfr = ["NFR ID", "Category", "Specification & Benchmark", "Validation Method"]
    rows_nfr = [
        ["NFR-PERF-01", "Performance", "Page render time under 300ms under standard local Apache / PHP 8.2 execution", "Server benchmarking & microtime probes"],
        ["NFR-SEC-01", "Security", "All stored passwords hashed using BCrypt algorithm ($2y$10$) with salts", "Automated cryptographic audit script"],
        ["NFR-SEC-02", "Security", "100% of SQL queries parameterized via PDO prepared statements against SQL injection", "Penetration probe with UNION and OR payloads"],
        ["NFR-SEC-03", "Security", "All user-supplied output sanitized via htmlspecialchars() against XSS attacks", "Script tag injection verification"],
        ["NFR-REL-01", "Reliability", "Zero-dependency pure PHP/MySQL runtime with automatic demo-mode fallback", "Offline disconnect simulation"],
        ["NFR-USAB-01", "Usability", "Responsive UI adhering to CuyoTech University branding (Navy/Gold tokens)", "Cross-viewport device inspection"]
    ]
    add_table(doc, headers_nfr, rows_nfr, [Inches(1.2), Inches(1.1), Inches(2.7), Inches(1.5)])

    add_heading_1(doc, "5. Prioritized Product Backlog (MoSCoW Framework)")
    add_paragraph(doc, 
        "The project backlog was prioritized by Team Leader Bautista using the industry-standard MoSCoW framework "
        "(Must-Have, Should-Have, Could-Have, Won't-Have) and estimated using Agile Story Points (Fibonacci scale)."
    )

    headers_backlog = ["Story ID", "User Story Description", "MoSCoW", "Pts", "Sprint", "Acceptance Criteria"]
    rows_backlog = [
        ["US-01", "As a student, I want to securely log in so I can access my academic records.", "Must-Have", "3", "Sprint 1", "Valid student ID + password grants access to portal."],
        ["US-02", "As an administrator, I want role-based routing so that staff only access their designated tools.", "Must-Have", "5", "Sprint 1", "RBAC restricts registrar, cashier, and dept routes."],
        ["US-03", "As a student, I want to see my term grades and GPA so I know my academic standing.", "Must-Have", "5", "Sprint 2", "Grades displayed per term with mathematically verified GPA."],
        ["US-04", "As a student, I want to request certificates and TOR online so I avoid manual queues.", "Must-Have", "8", "Sprint 2", "Document request form with copy selector and fee calculation."],
        ["US-05", "As a registrar, I want to encode course grades so student records stay up to date.", "Must-Have", "5", "Sprint 2", "Grades encoding table with live database persistence."],
        ["US-06", "As a cashier, I want to record student payments and issue ORs for financial compliance.", "Must-Have", "8", "Sprint 3", "Payment recording form generating unique OR numbers."],
        ["US-07", "As a dept chair, I want to clear or hold students based on departmental obligations.", "Must-Have", "5", "Sprint 3", "Clearance grid toggleable between Cleared, Hold, Pending."],
        ["US-08", "As an admin, I want to create user accounts so new students and staff can access SSIS.", "Must-Have", "5", "Sprint 4", "User management CRUD modal with password hashing."],
        ["US-09", "As a student, I want printable official receipt views so I have proof of payment.", "Should-Have", "3", "Sprint 3", "Printable OR template matching institutional receipt format."],
        ["US-10", "As a student, I want to cancel pending document requests if filed mistakenly.", "Should-Have", "3", "Sprint 2", "Cancel button active only while request is in Pending status."],
        ["US-11", "As a user, I want system demo fallback when MySQL is offline so testing is uninterrupted.", "Should-Have", "5", "Sprint 1", "Graceful fallback to memory/session demo store."],
        ["US-12", "As an admin, I want exportable grade sheets in PDF format.", "Could-Have", "5", "Future", "Deferred to Phase 2 enhancements."],
        ["US-13", "As a student, I want real-time SMS notifications upon request release.", "Won't-Have", "8", "Future", "Requires third-party SMS gateway integration; out of scope."]
    ]
    add_table(doc, headers_backlog, rows_backlog, [Inches(0.8), Inches(2.2), Inches(1.0), Inches(0.4), Inches(0.7), Inches(1.4)])

    add_heading_2(doc, "5.1 Backlog Estimation & Velocity Summary")
    add_paragraph(doc, 
        "• Total Backlog Story Points: 60 Story Points\n"
        "• Must-Have Velocity: 44 Story Points (100% completed in Sprints 1–4)\n"
        "• Should-Have Velocity: 11 Story Points (100% completed in Sprints 2–3)\n"
        "• Could-Have / Won't-Have: 5 Story Points (cataloged for future institutional roadmap)"
    )

    docx_path = os.path.join(OUT_DIR, "Requirements_Specification_and_Backlog.docx")
    doc.save(docx_path)
    print(f"Generated DOCX: {docx_path}")
    return docx_path

def generate_pdf():
    html_content = """
<h1>1. Executive Summary & Problem Context</h1>
<p>
Prior to the implementation of the Student Services Information System (SSIS), CuyoTech University operated
through disconnected manual logbooks, paper request slips, and decentralized spreadsheets across offices.
Students experienced lengthy queueing times for transcript and certification requests, registrar staff faced
manual grade consolidation delays, and cashier reconciliation suffered from disconnected fee tracking.
</p>
<p>
The SSIS establishes a unified, role-based web architecture backed by a 3NF relational database (MySQL)
to digitize core institutional workflows: student profile access, grade inquiry, enrollment processing,
official document issuance, counter payment recording, and cross-department clearance evaluations.
</p>

<div class="callout">
  <div class="callout-title">📌 REQUIREMENTS BASELINE NOTICE</div>
  This requirements baseline was gathered and curated through stakeholder interviews with university registrars,
  cashiers, department chairs, and collegiate student councils during Sprint 0 of the CCS109 project lifecycle.
</div>

<h1>2. Stakeholder Profiles & User Personas</h1>
<table class="data-table">
  <thead>
    <tr>
      <th>Stakeholder Role</th>
      <th>Target User Persona</th>
      <th>Primary System Needs</th>
      <th>Access Scope</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><strong>Student</strong></td>
      <td>Juan Dela Cruz (3rd Yr BSIT)</td>
      <td>View grades, verify enrolled units, file document requests, check clearance</td>
      <td>Self-service Student Portal</td>
    </tr>
    <tr>
      <td><strong>Registrar Staff</strong></td>
      <td>Maria Santiago (Registrar Officer)</td>
      <td>Enroll students into courses, encode & update term grades, process credentials</td>
      <td>Academic & Records Module</td>
    </tr>
    <tr>
      <td><strong>Cashier Staff</strong></td>
      <td>Rosa Ramos (University Cashier)</td>
      <td>Accept counter payments, issue official receipts (OR), track payment history</td>
      <td>Financial Operations Module</td>
    </tr>
    <tr>
      <td><strong>Department Staff</strong></td>
      <td>Dr. Alan Turing (CCS Department Chair)</td>
      <td>Review student departmental compliance, update clearance status (Hold/Cleared)</td>
      <td>Clearance Approval Module</td>
    </tr>
    <tr>
      <td><strong>System Administrator</strong></td>
      <td>Ana Reyes (Lead Administrator)</td>
      <td>Manage user accounts, configure system roles, oversee end-to-end request lifecycle</td>
      <td>SuperAdmin Control Panel</td>
    </tr>
  </tbody>
</table>

<h1>3. Functional Requirements (FR)</h1>
<h2>3.1 Module 1: Authentication & Access Control (FR-AUTH)</h2>
<ul>
  <li><strong>FR-AUTH-01:</strong> Allow students to authenticate using their unique Student ID (e.g. 2021-00123) or university email with password.</li>
  <li><strong>FR-AUTH-02:</strong> Allow administrative staff to authenticate using designated staff usernames (e.g. ana.reyes, cashier01).</li>
  <li><strong>FR-AUTH-03:</strong> Enforce strict Role-Based Access Control (RBAC) preventing privilege escalation across module routes.</li>
  <li><strong>FR-AUTH-04:</strong> Provide secure session invalidation and automated logout safeguards upon session termination.</li>
</ul>

<h2>3.2 Module 2: Student Academic Portal (FR-STU)</h2>
<ul>
  <li><strong>FR-STU-01:</strong> Display comprehensive student biodata including curriculum program, year level, and residential address.</li>
  <li><strong>FR-STU-02:</strong> Display enrolled subject schedule, section, units, professor, and classroom assignment.</li>
  <li><strong>FR-STU-03:</strong> Provide term-by-term grade breakdown with automated, real-time semester GPA mathematical calculation.</li>
  <li><strong>FR-STU-04:</strong> Allow self-service password update with live hash mutation in the underlying data store.</li>
</ul>

<h2>3.3 Module 3: Document Request Workflow (FR-DOC)</h2>
<ul>
  <li><strong>FR-DOC-01:</strong> Allow students to request university documents (Transcript of Records, Certificate of Registration, Honorable Dismissal, Good Moral).</li>
  <li><strong>FR-DOC-02:</strong> Calculate real-time document fees based on document type (₱50 - ₱150) multiplied by quantity of copies.</li>
  <li><strong>FR-DOC-03:</strong> Track live request fulfillment state progression: Pending -> Processing -> Ready for Release -> Released.</li>
  <li><strong>FR-DOC-04:</strong> Allow students to cancel pending requests prior to administrative processing.</li>
</ul>

<h2>3.4 Module 4: Registrar Operations (FR-REG)</h2>
<ul>
  <li><strong>FR-REG-01:</strong> Provide student course enrollment capability linking StudentID to CourseID with term and school year parameters.</li>
  <li><strong>FR-REG-02:</strong> Provide instructor grade encoding grid with real-time grade updates (1.00 - 5.00 grading scale or INC).</li>
</ul>

<h2>3.5 Module 5: Cashier & Financial Transactions (FR-CASH)</h2>
<ul>
  <li><strong>FR-CASH-01:</strong> Record counter payments with student lookup, payment type selection, and automated OR number generation.</li>
  <li><strong>FR-CASH-02:</strong> Generate printable, formatted University Official Receipts (OR) with itemized financial breakdown.</li>
</ul>

<h2>3.6 Module 6: Department Clearance & Admin (FR-ADM)</h2>
<ul>
  <li><strong>FR-ADM-01:</strong> Provide multi-department clearance evaluation grid allowing status updates between Pending, Cleared, and Hold.</li>
  <li><strong>FR-ADM-02:</strong> Provide centralized User Account Management for creating and updating student and administrator credentials.</li>
</ul>

<h1>4. Non-Functional Requirements (NFR)</h1>
<table class="data-table">
  <thead>
    <tr>
      <th>NFR ID</th>
      <th>Category</th>
      <th>Specification & Benchmark</th>
      <th>Validation Method</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><strong>NFR-PERF-01</strong></td>
      <td>Performance</td>
      <td>Page render time under 300ms under standard local Apache / PHP 8.2 execution</td>
      <td>Server benchmarking & microtime probes</td>
    </tr>
    <tr>
      <td><strong>NFR-SEC-01</strong></td>
      <td>Security</td>
      <td>All stored passwords hashed using BCrypt algorithm ($2y$10$) with salts</td>
      <td>Automated cryptographic audit script</td>
    </tr>
    <tr>
      <td><strong>NFR-SEC-02</strong></td>
      <td>Security</td>
      <td>100% of SQL queries parameterized via PDO prepared statements against SQL injection</td>
      <td>Penetration probe with UNION and OR payloads</td>
    </tr>
    <tr>
      <td><strong>NFR-SEC-03</strong></td>
      <td>Security</td>
      <td>All user-supplied output sanitized via htmlspecialchars() against XSS attacks</td>
      <td>Script tag injection verification</td>
    </tr>
    <tr>
      <td><strong>NFR-REL-01</strong></td>
      <td>Reliability</td>
      <td>Zero-dependency pure PHP/MySQL runtime with automatic demo-mode fallback</td>
      <td>Offline disconnect simulation</td>
    </tr>
    <tr>
      <td><strong>NFR-USAB-01</strong></td>
      <td>Usability</td>
      <td>Responsive UI adhering to CuyoTech University branding (Navy/Gold tokens)</td>
      <td>Cross-viewport device inspection</td>
    </tr>
  </tbody>
</table>

<h1>5. Prioritized Product Backlog (MoSCoW Framework)</h1>
<p>
The project backlog was prioritized by Team Leader Bautista using the industry-standard MoSCoW framework
(Must-Have, Should-Have, Could-Have, Won't-Have) and estimated using Agile Story Points (Fibonacci scale).
</p>

<table class="data-table">
  <thead>
    <tr>
      <th>Story ID</th>
      <th>User Story Description</th>
      <th>MoSCoW</th>
      <th>Pts</th>
      <th>Sprint</th>
      <th>Acceptance Criteria</th>
    </tr>
  </thead>
  <tbody>
    <tr><td>US-01</td><td>As a student, I want to securely log in so I can access my academic records.</td><td><span class="badge badge-must">Must-Have</span></td><td>3</td><td>Sprint 1</td><td>Valid student ID + password grants access.</td></tr>
    <tr><td>US-02</td><td>As an administrator, I want role-based routing so that staff only access their designated tools.</td><td><span class="badge badge-must">Must-Have</span></td><td>5</td><td>Sprint 1</td><td>RBAC restricts registrar, cashier, dept routes.</td></tr>
    <tr><td>US-03</td><td>As a student, I want to see my term grades and GPA so I know my academic standing.</td><td><span class="badge badge-must">Must-Have</span></td><td>5</td><td>Sprint 2</td><td>Grades displayed with calculated GPA.</td></tr>
    <tr><td>US-04</td><td>As a student, I want to request certificates and TOR online so I avoid manual queues.</td><td><span class="badge badge-must">Must-Have</span></td><td>8</td><td>Sprint 2</td><td>Request form with fee calculation.</td></tr>
    <tr><td>US-05</td><td>As a registrar, I want to encode course grades so student records stay up to date.</td><td><span class="badge badge-must">Must-Have</span></td><td>5</td><td>Sprint 2</td><td>Encoding table with live database persistence.</td></tr>
    <tr><td>US-06</td><td>As a cashier, I want to record student payments and issue ORs for financial compliance.</td><td><span class="badge badge-must">Must-Have</span></td><td>8</td><td>Sprint 3</td><td>Payment recording with unique OR generator.</td></tr>
    <tr><td>US-07</td><td>As a dept chair, I want to clear or hold students based on departmental obligations.</td><td><span class="badge badge-must">Must-Have</span></td><td>5</td><td>Sprint 3</td><td>Clearance grid toggleable (Cleared, Hold, Pending).</td></tr>
    <tr><td>US-08</td><td>As an admin, I want to create user accounts so new students and staff can access SSIS.</td><td><span class="badge badge-must">Must-Have</span></td><td>5</td><td>Sprint 4</td><td>User management CRUD modal with password hashing.</td></tr>
    <tr><td>US-09</td><td>As a student, I want printable official receipt views so I have proof of payment.</td><td><span class="badge badge-should">Should-Have</span></td><td>3</td><td>Sprint 3</td><td>Printable OR template matching institutional format.</td></tr>
    <tr><td>US-10</td><td>As a student, I want to cancel pending document requests if filed mistakenly.</td><td><span class="badge badge-should">Should-Have</span></td><td>3</td><td>Sprint 2</td><td>Cancel button active only while in Pending status.</td></tr>
    <tr><td>US-11</td><td>As a user, I want system demo fallback when MySQL is offline so testing is uninterrupted.</td><td><span class="badge badge-should">Should-Have</span></td><td>5</td><td>Sprint 1</td><td>Graceful fallback to memory/session demo store.</td></tr>
    <tr><td>US-12</td><td>As an admin, I want exportable grade sheets in PDF format.</td><td><span class="badge badge-could">Could-Have</span></td><td>5</td><td>Future</td><td>Deferred to Phase 2 enhancements.</td></tr>
    <tr><td>US-13</td><td>As a student, I want real-time SMS notifications upon request release.</td><td><span class="badge" style="background:#f1f5f9;color:#64748b;">Won't-Have</span></td><td>8</td><td>Future</td><td>Requires SMS gateway; out of scope.</td></tr>
  </tbody>
</table>

<h2>5.1 Backlog Estimation & Velocity Summary</h2>
<ul>
  <li><strong>Total Backlog Story Points:</strong> 60 Story Points</li>
  <li><strong>Must-Have Velocity:</strong> 44 Story Points (100% completed in Sprints 1–4)</li>
  <li><strong>Should-Have Velocity:</strong> 11 Story Points (100% completed in Sprints 2–3)</li>
  <li><strong>Could-Have / Won't-Have:</strong> 5 Story Points (cataloged for future roadmap)</li>
</ul>
"""
    html_doc = build_html_wrapper(
        title="Software Requirements Specification & Product Backlog",
        deliverable_code="TASK-1A & 1B (Bautista - Team Leader)",
        author_role="Bautista (Team Leader / Product Owner)",
        content_html=html_content
    )
    html_path = os.path.join(OUT_DIR, "Requirements_Specification_and_Backlog.html")
    pdf_path = os.path.join(OUT_DIR, "Requirements_Specification_and_Backlog.pdf")
    with open(html_path, "w", encoding="utf-8") as f:
        f.write(html_doc)
    html_to_pdf(html_path, pdf_path)
    print(f"Generated PDF: {pdf_path}")
    return pdf_path

if __name__ == "__main__":
    generate_docx()
    generate_pdf()
