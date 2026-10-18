"""
Generate Deliverable 03: System Modeling & Diagrams (Aquino - SCRUM Master)
Outputs: DOCX & PDF in deliverables/03_Aquino_System_Modeling_and_Diagrams/
"""

import os
from docx.shared import Inches, Pt
from doc_builder import (
    create_base_docx, add_heading_1, add_heading_2, add_heading_3,
    add_paragraph, add_bullet, add_callout, add_table,
    build_html_wrapper, html_to_pdf
)

OUT_DIR = os.path.abspath("deliverables/03_Aquino_System_Modeling_and_Diagrams")
os.makedirs(OUT_DIR, exist_ok=True)

def generate_docx():
    doc = create_base_docx(
        title="System Modeling & Unified Architecture Specifications",
        subtitle="UML Use Case, Sequence, Class, Activity Diagrams, and Data Flow Diagrams (DFD)",
        author="Aquino",
        role="SCRUM Master",
        deliverable_code="TASK-2C (Aquino)"
    )

    add_heading_1(doc, "1. Architectural Overview (MVC & Repository Pattern)")
    add_paragraph(doc, 
        "The CuyoTech Student Services Information System (SSIS) is engineered using a clean Model-View-Controller (MVC) "
        "and Repository architectural pattern implemented in Pure PHP 8.2 and MySQL. This architecture provides strict "
        "separation of concerns, ensuring high maintainability, testability, and adherence to the single responsibility principle."
    )
    add_bullet(doc, "Handles HTTP request routing, session validation, role authorization checks, and orchestrates model/repository operations.", "Controllers (App\\Controllers): ")
    add_bullet(doc, "Encapsulates database access using PDO prepared statements with parameter binding and automated demo fallback.", "Repositories (App\\Repositories): ")
    add_bullet(doc, "Defines strongly typed entity domain objects (Student, Course, Enrollment, Payment, DocumentRequest, Clearance, AdminUser).", "Models (App\\Models): ")
    add_bullet(doc, "Modular, secure PHP view components utilizing layout inheritance and htmlspecialchars output sanitization.", "Views (views/): ")

    add_heading_1(doc, "2. Use Case Modeling")
    add_paragraph(doc, "The SSIS provides customized interfaces for five (5) distinct primary actors across the university:")

    headers_actors = ["Actor", "Type", "Primary Responsibilities", "Core Use Cases"]
    rows_actors = [
        ["Student", "Primary / End-User", "Access academic records, request official papers, view financial balance", "UC-01 Login, UC-02 View Grades, UC-03 Request Document, UC-04 Update Password"],
        ["Registrar", "Internal Staff", "Academic curriculum enforcement, student enrollment, grade encoding", "UC-05 Enroll Student, UC-06 Encode Term Grades, UC-07 Process Credential Request"],
        ["Cashier", "Internal Staff", "Tuition & fee counter payment processing, official receipt generation", "UC-08 Record Counter Payment, UC-09 Print Official Receipt, UC-10 Search Ledger"],
        ["Dept Staff", "Academic Officer", "Collegiate clearances and departmental holds", "UC-11 Evaluate Student Clearance, UC-12 Apply/Clear Departmental Hold"],
        ["Admin", "Superuser", "System governance, account provisioning, master data management", "UC-13 Manage User Accounts, UC-14 Configure Roles, UC-15 Oversee Request Queue"]
    ]
    add_table(doc, headers_actors, rows_actors, [Inches(1.0), Inches(1.3), Inches(2.2), Inches(2.0)])

    add_heading_2(doc, "2.1 Use Case Specifications: UC-03 Document Request")
    add_bullet(doc, "Authenticated Student with active academic standing.", "Primary Actor: ")
    add_bullet(doc, "Student must be logged in; student account must not have hard institutional lockouts.", "Preconditions: ")
    add_bullet(doc, "(1) Student navigates to Document Requests page; (2) Student clicks 'Request Document'; (3) Modal prompts for Document Type (TOR, COR, etc.) and copy count; (4) System computes live fee; (5) Student submits request; (6) System inserts record into DocumentRequest table with status 'Pending'.", "Main Success Scenario: ")
    add_bullet(doc, "New request logged in 3NF database; visible to Registrar and Admin in the fulfillment queue.", "Postconditions: ")

    add_heading_1(doc, "3. Sequence Diagrams")
    add_heading_2(doc, "3.1 Sequence 1: Document Request & Fulfillment Workflow")
    add_paragraph(doc,
        "1. Student -> View: Select document type and number of copies in modal.\n"
        "2. View -> RequestController: POST ?page=requests&action=create (doc_type, copies).\n"
        "3. RequestController -> AuthService: Validate student session & StudentID.\n"
        "4. RequestController -> RequestRepository: create(studentId, docType, copies).\n"
        "5. RequestRepository -> Database: INSERT INTO DocumentRequest (StudentID, DocumentType, Copies, Fee, Status='Pending').\n"
        "6. Database --> RequestRepository: Returns generated RequestID.\n"
        "7. RequestRepository --> RequestController: Return success status.\n"
        "8. Admin/Registrar -> RequestController: POST ?page=admin-requests&action=update (requestId, newStatus='Processing'/'Released').\n"
        "9. RequestController -> RequestRepository: updateStatus(requestId, action).\n"
        "10. RequestRepository -> Database: UPDATE DocumentRequest SET Status = :status WHERE RequestID = :id."
    )

    add_heading_2(doc, "3.2 Sequence 2: Cashier Payment & Official Receipt Issuance")
    add_paragraph(doc,
        "1. Cashier -> View: Open Payment Recording Modal (?page=payments).\n"
        "2. Cashier -> View: Enter Student ID / Name, Amount (₱), Payment Type (Tuition/Document/Graduation).\n"
        "3. View -> CashierController: POST ?page=payments&action=record.\n"
        "4. CashierController -> PaymentRepository: recordPayment(studentId, amount, type, orNumber).\n"
        "5. PaymentRepository -> Database: INSERT INTO Payment (StudentID, Amount, PaymentType, ORNumber, PaymentDate, Status='Completed').\n"
        "6. Database --> PaymentRepository: Confirms commit.\n"
        "7. CashierController -> View: Redirect to Official Receipt View (?page=receipts&or=OR-XXXXX).\n"
        "8. View: Render university-formatted printable receipt with verified cashier signature watermark."
    )

    add_heading_2(doc, "3.3 Sequence 3: Registrar Grade Encoding Workflow")
    add_paragraph(doc,
        "1. Registrar -> View: Navigate to Grades Encoding (?page=grades-encoding).\n"
        "2. Registrar -> View: Select Course Section & Semester filter.\n"
        "3. View -> RegistrarController: GET course enrollment rosters.\n"
        "4. RegistrarController -> EnrollmentRepository: getAllEnrollments(schoolYear, semester).\n"
        "5. Registrar -> View: Input or update numeric grade (e.g. 1.25, 1.50, 2.00, or INC).\n"
        "6. View -> RegistrarController: POST ?page=grades-encoding&action=update_grade (enrollmentId, grade).\n"
        "7. RegistrarController -> EnrollmentRepository: updateGrade(enrollmentId, grade).\n"
        "8. EnrollmentRepository -> Database: UPDATE Enrollment SET Grade = :grade WHERE EnrollmentID = :id.\n"
        "9. Student Portal -> AcademicController: When student visits ?page=grades, CourseRepository recalculates GPA live."
    )

    add_heading_1(doc, "4. Class Diagram & Architecture")
    add_paragraph(doc,
        "The system class hierarchy is organized across three main namespaces:\n\n"
        "• App\\Config\\Database (Singleton PDO wrapper with multi-port probing)\n"
        "• App\\Models (Student, Course, Enrollment, Payment, DocumentRequest, Clearance, AdminUser, Department)\n"
        "• App\\Repositories (StudentRepository, CourseRepository, EnrollmentRepository, PaymentRepository, RequestRepository, ClearanceRepository, AdminRepository)\n"
        "• App\\Controllers (AuthController, ProfileController, AcademicController, RequestController, RegistrarController, CashierController, DepartmentController, AdminController)\n"
        "• App\\Services (AuthService, RequestService)\n"
        "• App\\Utils (View renderer, Icon SVG generator)"
    )

    add_heading_1(doc, "5. Data Flow Diagrams (DFD)")
    add_heading_2(doc, "5.1 DFD Level 0: Context Diagram")
    add_paragraph(doc,
        "At Level 0, the SSIS operates as a single centralized software entity interacting with four external entities:\n"
        "• Student: Supplies login credentials, password updates, and document requests; receives academic grades, enrolled subjects, clearance statuses, and digital receipts.\n"
        "• Registrar: Supplies enrollment records and course grades; receives student records, grade distribution reports, and document fulfillment queues.\n"
        "• Cashier: Supplies payment entries, cash amounts, and OR numbers; receives student ledger balances and transaction verification.\n"
        "• Administrator: Supplies user account creations and role permissions; receives system audit logs and global performance metrics."
    )

    add_heading_2(doc, "5.2 DFD Level 1: Functional Decomposition")
    add_paragraph(doc,
        "Decomposed into six core processes and eight 3NF data stores:\n"
        "• Process 1.0 (Authentication): Verifies credentials against D1 (Student) and D2 (AdminUser); issues authenticated session tokens.\n"
        "• Process 2.0 (Academic & Grading): Queries D3 (Course) and D4 (Enrollment); performs weighted GPA math calculations.\n"
        "• Process 3.0 (Document Requests): Manages submission, pricing, and fulfillment in D5 (DocumentRequest).\n"
        "• Process 4.0 (Financial Transactions): Records counter payments and OR generation in D6 (Payment).\n"
        "• Process 5.0 (Clearance Processing): Manages cross-department clearance status in D7 (Clearance) and D8 (Department).\n"
        "• Process 6.0 (User Administration): Provisions and manages staff and student accounts in D1 and D2."
    )

    docx_path = os.path.join(OUT_DIR, "System_Modeling_and_Diagrams.docx")
    doc.save(docx_path)
    print(f"Generated DOCX: {docx_path}")
    return docx_path

def generate_pdf():
    html_content = """
<h1>1. Architectural Overview (MVC & Repository Pattern)</h1>
<p>
The CuyoTech Student Services Information System (SSIS) is engineered using a clean Model-View-Controller (MVC)
and Repository architectural pattern implemented in Pure PHP 8.2 and MySQL. This architecture provides strict
separation of concerns, ensuring high maintainability, testability, and adherence to the single responsibility principle.
</p>
<ul>
  <li><strong>Controllers (App\\Controllers):</strong> Handles HTTP request routing, session validation, role authorization checks, and orchestrates model/repository operations.</li>
  <li><strong>Repositories (App\\Repositories):</strong> Encapsulates database access using PDO prepared statements with parameter binding and automated demo fallback.</li>
  <li><strong>Models (App\\Models):</strong> Defines strongly typed entity domain objects (Student, Course, Enrollment, Payment, DocumentRequest, Clearance, AdminUser).</li>
  <li><strong>Views (views/):</strong> Modular, secure PHP view components utilizing layout inheritance and htmlspecialchars output sanitization.</li>
</ul>

<h1>2. Use Case Modeling</h1>
<p>The SSIS provides customized interfaces for five (5) distinct primary actors across the university:</p>

<table class="data-table">
  <thead>
    <tr>
      <th>Actor</th>
      <th>Type</th>
      <th>Primary Responsibilities</th>
      <th>Core Use Cases</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><strong>Student</strong></td>
      <td>Primary / End-User</td>
      <td>Access academic records, request official papers, view financial balance</td>
      <td>UC-01 Login, UC-02 View Grades, UC-03 Request Document, UC-04 Update Password</td>
    </tr>
    <tr>
      <td><strong>Registrar</strong></td>
      <td>Internal Staff</td>
      <td>Academic curriculum enforcement, student enrollment, grade encoding</td>
      <td>UC-05 Enroll Student, UC-06 Encode Term Grades, UC-07 Process Credential Request</td>
    </tr>
    <tr>
      <td><strong>Cashier</strong></td>
      <td>Internal Staff</td>
      <td>Tuition & fee counter payment processing, official receipt generation</td>
      <td>UC-08 Record Counter Payment, UC-09 Print Official Receipt, UC-10 Search Ledger</td>
    </tr>
    <tr>
      <td><strong>Dept Staff</strong></td>
      <td>Academic Officer</td>
      <td>Collegiate clearances and departmental holds</td>
      <td>UC-11 Evaluate Student Clearance, UC-12 Apply/Clear Departmental Hold</td>
    </tr>
    <tr>
      <td><strong>Admin</strong></td>
      <td>Superuser</td>
      <td>System governance, account provisioning, master data management</td>
      <td>UC-13 Manage User Accounts, UC-14 Configure Roles, UC-15 Oversee Request Queue</td>
    </tr>
  </tbody>
</table>

<h2>2.1 Use Case Specifications: UC-03 Document Request</h2>
<ul>
  <li><strong>Primary Actor:</strong> Authenticated Student with active academic standing.</li>
  <li><strong>Preconditions:</strong> Student must be logged in; student account must not have hard institutional lockouts.</li>
  <li><strong>Main Success Scenario:</strong> (1) Student navigates to Document Requests page; (2) Student clicks 'Request Document'; (3) Modal prompts for Document Type (TOR, COR, etc.) and copy count; (4) System computes live fee; (5) Student submits request; (6) System inserts record into DocumentRequest table with status 'Pending'.</li>
  <li><strong>Postconditions:</strong> New request logged in 3NF database; visible to Registrar and Admin in the fulfillment queue.</li>
</ul>

<h1>3. Sequence Diagrams</h1>
<h2>3.1 Sequence 1: Document Request & Fulfillment Workflow</h2>
<div class="code-box">
[Student] -> (View): Select document type & number of copies
(View) -> [RequestController]: POST ?page=requests&action=create
[RequestController] -> [AuthService]: Validate student session & StudentID
[RequestController] -> [RequestRepository]: create(studentId, docType, copies)
[RequestRepository] -> [MySQL Database]: INSERT INTO DocumentRequest (..., Status='Pending')
[MySQL Database] --> [RequestRepository]: Returns generated RequestID
[RequestRepository] --> [RequestController]: Return success confirmation
[Staff/Admin] -> [RequestController]: POST ?page=admin-requests&action=update (requestId, 'Released')
[RequestController] -> [RequestRepository]: updateStatus(requestId, action)
[RequestRepository] -> [MySQL Database]: UPDATE DocumentRequest SET Status = :status
</div>

<h2>3.2 Sequence 2: Cashier Payment & Official Receipt Issuance</h2>
<div class="code-box">
[Cashier] -> (View): Open Payment Recording Modal (?page=payments)
(View) -> [CashierController]: POST ?page=payments&action=record (studentId, amount, type)
[CashierController] -> [PaymentRepository]: recordPayment(studentId, amount, type, orNumber)
[PaymentRepository] -> [MySQL Database]: INSERT INTO Payment (StudentID, Amount, ORNumber, Status='Completed')
[MySQL Database] --> [PaymentRepository]: Confirms commit
[CashierController] -> (View): Redirect to Official Receipt View (?page=receipts&or=OR-XXXXX)
(View): Render university-formatted printable receipt with cashier signature watermark
</div>

<h2>3.3 Sequence 3: Registrar Grade Encoding Workflow</h2>
<div class="code-box">
[Registrar] -> (View): Open Grades Encoding Grid (?page=grades-encoding)
(View) -> [RegistrarController]: GET roster enrollments
[RegistrarController] -> [EnrollmentRepository]: getAllEnrollments(schoolYear, semester)
[Registrar] -> (View): Input numeric grade (e.g. 1.25, 1.50, INC)
(View) -> [RegistrarController]: POST ?page=grades-encoding&action=update_grade
[RegistrarController] -> [EnrollmentRepository]: updateGrade(enrollmentId, grade)
[EnrollmentRepository] -> [MySQL Database]: UPDATE Enrollment SET Grade = :grade
[Student Portal] -> [AcademicController]: When student visits ?page=grades, GPA recalculated live
</div>

<h1>4. Class Diagram & Architecture</h1>
<p>The system class hierarchy is organized across three main namespaces:</p>
<ul>
  <li><strong>App\\Config\\Database:</strong> Singleton PDO wrapper with multi-port probing.</li>
  <li><strong>App\\Models:</strong> Student, Course, Enrollment, Payment, DocumentRequest, Clearance, AdminUser, Department.</li>
  <li><strong>App\\Repositories:</strong> StudentRepository, CourseRepository, EnrollmentRepository, PaymentRepository, RequestRepository, ClearanceRepository, AdminRepository.</li>
  <li><strong>App\\Controllers:</strong> AuthController, ProfileController, AcademicController, RequestController, RegistrarController, CashierController, DepartmentController, AdminController.</li>
  <li><strong>App\\Services:</strong> AuthService, RequestService.</li>
  <li><strong>App\\Utils:</strong> View renderer, Icon SVG generator.</li>
</ul>

<h1>5. Data Flow Diagrams (DFD)</h1>
<h2>5.1 DFD Level 0: Context Diagram</h2>
<p>
At Level 0, the SSIS operates as a single centralized software entity interacting with four external entities:
Student, Registrar, Cashier, and Administrator exchanging credentials, academic grades, official requests, and payment transactions.
</p>

<h2>5.2 DFD Level 1: Functional Decomposition</h2>
<p>
Decomposed into six core processes and eight 3NF data stores:
</p>
<ul>
  <li><strong>Process 1.0 (Authentication):</strong> Verifies credentials against D1 (Student) and D2 (AdminUser); issues authenticated session tokens.</li>
  <li><strong>Process 2.0 (Academic & Grading):</strong> Queries D3 (Course) and D4 (Enrollment); performs weighted GPA math calculations.</li>
  <li><strong>Process 3.0 (Document Requests):</strong> Manages submission, pricing, and fulfillment in D5 (DocumentRequest).</li>
  <li><strong>Process 4.0 (Financial Transactions):</strong> Records counter payments and OR generation in D6 (Payment).</li>
  <li><strong>Process 5.0 (Clearance Processing):</strong> Manages cross-department clearance status in D7 (Clearance) and D8 (Department).</li>
  <li><strong>Process 6.0 (User Administration):</strong> Provisions and manages staff and student accounts in D1 and D2.</li>
</ul>
"""
    html_doc = build_html_wrapper(
        title="System Modeling & Unified Architecture Specifications",
        deliverable_code="TASK-2C (Aquino - SCRUM Master)",
        author_role="Aquino (SCRUM Master / Presentation Lead)",
        content_html=html_content
    )
    html_path = os.path.join(OUT_DIR, "System_Modeling_and_Diagrams.html")
    pdf_path = os.path.join(OUT_DIR, "System_Modeling_and_Diagrams.pdf")
    with open(html_path, "w", encoding="utf-8") as f:
        f.write(html_doc)
    html_to_pdf(html_path, pdf_path)
    print(f"Generated PDF: {pdf_path}")
    return pdf_path

if __name__ == "__main__":
    generate_docx()
    generate_pdf()
