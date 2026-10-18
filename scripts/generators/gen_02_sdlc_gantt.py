"""
Generate Deliverable 02: SDLC Plan & Gantt Chart Documentation (Aquino - SCRUM Master)
Outputs: DOCX & PDF in deliverables/02_Aquino_SDLC_Plan_and_Gantt_Chart/
"""

import os
from docx.shared import Inches, Pt
from doc_builder import (
    create_base_docx, add_heading_1, add_heading_2, add_heading_3,
    add_paragraph, add_bullet, add_callout, add_table,
    build_html_wrapper, html_to_pdf
)

OUT_DIR = os.path.abspath("deliverables/02_Aquino_SDLC_Plan_and_Gantt_Chart")
os.makedirs(OUT_DIR, exist_ok=True)

def generate_docx():
    doc = create_base_docx(
        title="Agile/Scrum SDLC Plan &\nComprehensive Gantt Chart Documentation",
        subtitle="Sprint Cadences, Team Velocity, Ceremonies, WBS, and 8-Week Project Timeline",
        author="Aquino",
        role="SCRUM Master",
        deliverable_code="TASK-2A & GANTT (Aquino)"
    )

    add_heading_1(doc, "1. Software Development Life Cycle (SDLC) Methodology")
    add_paragraph(doc, 
        "For the development of the CuyoTech Student Services Information System (SSIS), the team adopted the "
        "Agile/Scrum framework. Unlike traditional Waterfall methodologies that enforce rigid sequential phases, "
        "Agile/Scrum provided iterative delivery cycles, frequent stakeholder feedback from university departments, "
        "and rapid adaptability when refining requirements across student, registrar, cashier, and administrative modules."
    )
    add_paragraph(doc,
        "The project was executed across an 8-week timeline structured into four (4) distinct 2-week Sprints, "
        "preceded by a Sprint 0 (Inception & Architecture) and concluded with a pre-defense hardening cycle."
    )

    add_heading_1(doc, "2. Scrum Roles & Responsibilities Matrix")
    headers_roles = ["Member", "Scrum Role", "Core Responsibilities", "Key Deliverables"]
    rows_roles = [
        ["Bautista", "Product Owner / Team Leader", "Defines product vision, maintains & prioritizes the Product Backlog (MoSCoW), stakeholder alignment, final review sign-off.", "SRS Document, Prioritized Backlog, Ethical Review"],
        ["Aquino", "SCRUM Master", "Facilitates Scrum ceremonies, removes technical impediments, tracks team velocity, authors SDLC & Gantt documentation, leads final defense presentation.", "SDLC Plan, Gantt Schedule, System Modeling Diagrams, PPT Deck"],
        ["Baltazar & Capoy", "Developers / UI-UX Designers", "Architects pure PHP MVC application, engineers 3NF database schema, develops UI/UX wireframes and front-end university stylesheets.", "PHP MVC Engine, MySQL 3NF Schema, 15 Wireframe Catalog, Responsive CSS"],
        ["Balbero", "QA Engineer / Security Lead", "Designs and executes software test plans (IEEE 829), conducts automated unit & integration testing, performs OWASP security audit, compiles master documentation.", "Software Test Plan, Automated Test Suite (36 Tests), Pentest Report, Master Manual"]
    ]
    add_table(doc, headers_roles, rows_roles, [Inches(1.2), Inches(1.5), Inches(2.3), Inches(1.5)])

    add_heading_1(doc, "3. Scrum Ceremonies & Quality Gates")
    add_heading_2(doc, "3.1 Sprint Cadence & Ceremonies")
    add_bullet(doc, "Held at the start of each 2-week cycle to decompose user stories into technical tasks and assign story point estimates.", "Sprint Planning: ")
    add_bullet(doc, "15-minute sync held Mondays, Wednesdays, and Fridays to assess progress, coordinate handoffs, and clear roadblocks.", "Daily Standups: ")
    add_bullet(doc, "Held on the final Friday of each sprint to demonstrate functional software increments to team leads and advisors.", "Sprint Review / Demo: ")
    add_bullet(doc, "Held following each review to identify process bottlenecks, celebrate wins, and enact actionable team improvements.", "Sprint Retrospective: ")

    add_heading_2(doc, "3.2 Quality Gate Governance: DoR & DoD")
    add_callout(doc,
        "Definition of Ready (DoR): A backlog item is ready for sprint ingestion only when it has a clear user story format "
        "('As a... I want to... So that...'), defined acceptance criteria, estimated story points, and database schema dependencies identified.\n\n"
        "Definition of Done (DoD): A feature is completed only when: (1) PHP code passes strict syntax linting without warnings; "
        "(2) 100% of database queries use PDO prepared statements; (3) UI renders responsively across desktop and mobile; "
        "(4) Unit and integration tests pass; (5) Code is reviewed and merged into the main Git branch.",
        title="SCRUM QUALITY GATES (DoR & DoD)"
    )

    add_heading_1(doc, "4. Work Breakdown Structure (WBS)")
    add_paragraph(doc, "The project scope was decomposed into five hierarchical WBS phases:")
    add_bullet(doc, "Needs assessment, stakeholder interviews, project charter formulation, and repository configuration.", "1.0 Inception & Planning: ")
    add_bullet(doc, "Relational modeling, UNF to 3NF normalization analysis, ERD design, and table DDL script authoring.", "2.0 Database Architecture: ")
    add_bullet(doc, "Developing MVC controllers, models, repositories, and views across Sprints 1 to 4.", "3.0 System Development: ")
    add_bullet(doc, "Unit testing, integration testing, SQLi penetration probing, XSS sanitization, and regression verification.", "4.0 Quality Assurance & Security: ")
    add_bullet(doc, "User manuals, developer guides, presentation deck authoring, and case study defense readiness.", "5.0 Documentation & Defense: ")

    add_heading_1(doc, "5. Gantt Chart Schedule & Project Timeline")
    add_paragraph(doc, 
        "The project followed an 8-week schedule spanning September 1 to October 24, 2026. "
        "The schedule below details the Work Breakdown Structure, start/end dates, durations, dependencies, and assigned members."
    )

    headers_gantt = ["WBS", "Task / Deliverable Name", "Start Date", "End Date", "Days", "Dependencies", "Assigned", "Status"]
    rows_gantt = [
        ["1.1", "Project Inception & Requirements Gathering", "Sep 01", "Sep 05", "5", "None", "Bautista", "Completed"],
        ["1.2", "Backlog Prioritization & MoSCoW Scoping", "Sep 06", "Sep 08", "3", "1.1", "Bautista", "Completed"],
        ["1.3", "SDLC Framework Formulation & Scrum Setup", "Sep 08", "Sep 10", "3", "1.2", "Aquino", "Completed"],
        ["2.1", "Database Modeling & 3NF Normalization", "Sep 11", "Sep 15", "5", "1.3", "Baltazar", "Completed"],
        ["2.2", "MySQL Schema Script (ssis_schema.sql)", "Sep 16", "Sep 18", "3", "2.1", "Baltazar", "Completed"],
        ["3.1", "Sprint 1: MVC Core & Auth Engine", "Sep 19", "Sep 26", "8", "2.2", "Capoy", "Completed"],
        ["3.2", "Sprint 2: Student Portal & Academic Grades", "Sep 27", "Oct 04", "8", "3.1", "Baltazar", "Completed"],
        ["3.3", "Sprint 2: Document Request System Workflow", "Oct 01", "Oct 06", "6", "3.1", "Capoy", "Completed"],
        ["3.4", "Sprint 3: Cashier Payments & Official Receipts", "Oct 05", "Oct 11", "7", "3.2", "Baltazar", "Completed"],
        ["3.5", "Sprint 3: Department Clearance Management", "Oct 07", "Oct 13", "7", "3.2", "Capoy", "Completed"],
        ["3.6", "Sprint 4: Admin Controls & User Management", "Oct 12", "Oct 16", "5", "3.4, 3.5", "Baltazar", "Completed"],
        ["4.1", "Software Test Plan (IEEE 829) Formulation", "Oct 14", "Oct 17", "4", "3.6", "Balbero", "Completed"],
        ["4.2", "Automated QA Test Execution (36 Test Cases)", "Oct 17", "Oct 19", "3", "4.1", "Balbero", "Completed"],
        ["4.3", "OWASP Security Audit & Vulnerability Testing", "Oct 19", "Oct 20", "2", "4.2", "Balbero", "Completed"],
        ["5.1", "Ethical Impact & RA 10173 DPA Analysis", "Oct 20", "Oct 21", "2", "1.1", "Bautista/Aquino", "Completed"],
        ["5.2", "System Modeling (Mermaid UML Diagrams)", "Oct 20", "Oct 22", "3", "3.6", "Aquino", "Completed"],
        ["5.3", "UI/UX Wireframes Catalog Compilation", "Oct 21", "Oct 22", "2", "3.6", "Capoy/Baltazar", "Completed"],
        ["5.4", "Final Presentation Slide Deck (.pptx)", "Oct 22", "Oct 23", "2", "5.2", "Aquino", "Completed"],
        ["5.5", "Master Documentation & Final Quality Review", "Oct 23", "Oct 24", "2", "All", "Team", "Completed"]
    ]
    add_table(doc, headers_gantt, rows_gantt, [Inches(0.5), Inches(2.2), Inches(0.7), Inches(0.7), Inches(0.4), Inches(0.8), Inches(0.9), Inches(0.8)])

    add_heading_2(doc, "5.1 Visual Gantt Chart Representation (8-Week Project Timeline)")
    add_paragraph(doc,
        "The following diagram visualizes the scheduling and concurrent execution tracks across the 8-week lifecycle:\n\n"
        "Week 1 [Sep 01 - Sep 07]: [████ Inception & Requirements (Bautista)]\n"
        "Week 2 [Sep 08 - Sep 14]: [███ SDLC Plan (Aquino)] [████ 3NF Database Modeling (Baltazar)]\n"
        "Week 3 [Sep 15 - Sep 21]: [██ DDL Schema] [████ Sprint 1: MVC Core & Auth (Capoy)]\n"
        "Week 4 [Sep 22 - Sep 28]: [██████ Sprint 2: Student Portal & Grades (Baltazar)]\n"
        "Week 5 [Sep 29 - Oct 05]: [████ Sprint 2: Document Requests] [████ Sprint 3: Payments]\n"
        "Week 6 [Oct 06 - Oct 12]: [████ Sprint 3: Clearances] [████ Sprint 4: Admin Controls]\n"
        "Week 7 [Oct 13 - Oct 19]: [████ IEEE 829 Test Suite] [████ Automated QA 36 Tests (Balbero)]\n"
        "Week 8 [Oct 20 - Oct 24]: [███ Security Audit] [███ UML Diagrams] [███ PPT Deck & Final Review]"
    )

    add_heading_2(doc, "5.2 Critical Path Analysis")
    add_paragraph(doc,
        "The Critical Path for the SSIS project comprises the sequence of dependent tasks that directly dictate "
        "the minimum project duration:\n"
        "1.1 Requirements -> 2.1 Database Modeling -> 2.2 Schema Script -> 3.1 Auth Engine -> "
        "3.2 Student Portal -> 3.4 Cashier Payments -> 3.6 Admin Controls -> 4.2 Automated Testing -> "
        "5.5 Final Review & Submission.\n\n"
        "Zero slack was permitted on this path; buffer days were strategically allocated in Week 7 and Week 8 "
        "to ensure rigorous testing and documentation without risking delivery deadlines."
    )

    docx_path = os.path.join(OUT_DIR, "SDLC_Plan_and_Gantt_Chart.docx")
    doc.save(docx_path)
    print(f"Generated DOCX: {docx_path}")
    return docx_path

def generate_pdf():
    html_content = """
<h1>1. Software Development Life Cycle (SDLC) Methodology</h1>
<p>
For the development of the CuyoTech Student Services Information System (SSIS), the team adopted the
Agile/Scrum framework. Unlike traditional Waterfall methodologies that enforce rigid sequential phases,
Agile/Scrum provided iterative delivery cycles, frequent stakeholder feedback from university departments,
and rapid adaptability when refining requirements across student, registrar, cashier, and administrative modules.
</p>
<p>
The project was executed across an 8-week timeline structured into four (4) distinct 2-week Sprints,
preceded by a Sprint 0 (Inception & Architecture) and concluded with a pre-defense hardening cycle.
</p>

<h1>2. Scrum Roles & Responsibilities Matrix</h1>
<table class="data-table">
  <thead>
    <tr>
      <th>Member</th>
      <th>Scrum Role</th>
      <th>Core Responsibilities</th>
      <th>Key Deliverables</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><strong>Bautista</strong></td>
      <td>Product Owner / Team Leader</td>
      <td>Defines product vision, maintains & prioritizes Product Backlog (MoSCoW), stakeholder alignment, final review sign-off.</td>
      <td>SRS Document, Prioritized Backlog, Ethical Review</td>
    </tr>
    <tr>
      <td><strong>Aquino</strong></td>
      <td>SCRUM Master</td>
      <td>Facilitates Scrum ceremonies, removes technical impediments, tracks team velocity, authors SDLC & Gantt documentation, leads final defense presentation.</td>
      <td>SDLC Plan, Gantt Schedule, System Modeling Diagrams, PPT Deck</td>
    </tr>
    <tr>
      <td><strong>Baltazar & Capoy</strong></td>
      <td>Developers / UI-UX Designers</td>
      <td>Architects pure PHP MVC application, engineers 3NF database schema, develops UI/UX wireframes and front-end university stylesheets.</td>
      <td>PHP MVC Engine, MySQL 3NF Schema, 15 Wireframe Catalog, Responsive CSS</td>
    </tr>
    <tr>
      <td><strong>Balbero</strong></td>
      <td>QA Engineer / Security Lead</td>
      <td>Designs and executes software test plans (IEEE 829), conducts automated unit & integration testing, performs OWASP security audit, compiles master documentation.</td>
      <td>Software Test Plan, Automated Test Suite (36 Tests), Pentest Report, Master Manual</td>
    </tr>
  </tbody>
</table>

<h1>3. Scrum Ceremonies & Quality Gates</h1>
<h2>3.1 Sprint Cadence & Ceremonies</h2>
<ul>
  <li><strong>Sprint Planning:</strong> Held at the start of each 2-week cycle to decompose user stories into technical tasks and assign story point estimates.</li>
  <li><strong>Daily Standups:</strong> 15-minute sync held Mondays, Wednesdays, and Fridays to assess progress, coordinate handoffs, and clear roadblocks.</li>
  <li><strong>Sprint Review / Demo:</strong> Held on the final Friday of each sprint to demonstrate functional software increments to team leads and advisors.</li>
  <li><strong>Sprint Retrospective:</strong> Held following each review to identify process bottlenecks, celebrate wins, and enact actionable team improvements.</li>
</ul>

<h2>3.2 Quality Gate Governance: DoR & DoD</h2>
<div class="callout">
  <div class="callout-title">📌 SCRUM QUALITY GATES (DoR & DoD)</div>
  <strong>Definition of Ready (DoR):</strong> A backlog item is ready for sprint ingestion only when it has a clear user story format ('As a... I want to... So that...'), defined acceptance criteria, estimated story points, and database schema dependencies identified.<br><br>
  <strong>Definition of Done (DoD):</strong> A feature is completed only when: (1) PHP code passes strict syntax linting without warnings; (2) 100% of database queries use PDO prepared statements; (3) UI renders responsively across desktop and mobile; (4) Unit and integration tests pass; (5) Code is reviewed and merged into the main Git branch.
</div>

<h1>4. Work Breakdown Structure (WBS)</h1>
<ul>
  <li><strong>1.0 Inception & Planning:</strong> Needs assessment, stakeholder interviews, project charter formulation, and repository configuration.</li>
  <li><strong>2.0 Database Architecture:</strong> Relational modeling, UNF to 3NF normalization analysis, ERD design, and table DDL script authoring.</li>
  <li><strong>3.0 System Development:</strong> Developing MVC controllers, models, repositories, and views across Sprints 1 to 4.</li>
  <li><strong>4.0 Quality Assurance & Security:</strong> Unit testing, integration testing, SQLi penetration probing, XSS sanitization, and regression verification.</li>
  <li><strong>5.0 Documentation & Defense:</strong> User manuals, developer guides, presentation deck authoring, and case study defense readiness.</li>
</ul>

<h1>5. Gantt Chart Schedule & Project Timeline</h1>
<p>
The project followed an 8-week schedule spanning September 1 to October 24, 2026.
The schedule below details the Work Breakdown Structure, start/end dates, durations, dependencies, and assigned members.
</p>

<table class="data-table">
  <thead>
    <tr>
      <th>WBS</th>
      <th>Task / Deliverable Name</th>
      <th>Start Date</th>
      <th>End Date</th>
      <th>Days</th>
      <th>Dependencies</th>
      <th>Assigned</th>
      <th>Status</th>
    </tr>
  </thead>
  <tbody>
    <tr><td>1.1</td><td>Project Inception & Requirements Gathering</td><td>Sep 01</td><td>Sep 05</td><td>5</td><td>None</td><td>Bautista</td><td><span class="badge badge-done">Completed</span></td></tr>
    <tr><td>1.2</td><td>Backlog Prioritization & MoSCoW Scoping</td><td>Sep 06</td><td>Sep 08</td><td>3</td><td>1.1</td><td>Bautista</td><td><span class="badge badge-done">Completed</span></td></tr>
    <tr><td>1.3</td><td>SDLC Framework Formulation & Scrum Setup</td><td>Sep 08</td><td>Sep 10</td><td>3</td><td>1.2</td><td>Aquino</td><td><span class="badge badge-done">Completed</span></td></tr>
    <tr><td>2.1</td><td>Database Modeling & 3NF Normalization</td><td>Sep 11</td><td>Sep 15</td><td>5</td><td>1.3</td><td>Baltazar</td><td><span class="badge badge-done">Completed</span></td></tr>
    <tr><td>2.2</td><td>MySQL Schema Script (ssis_schema.sql)</td><td>Sep 16</td><td>Sep 18</td><td>3</td><td>2.1</td><td>Baltazar</td><td><span class="badge badge-done">Completed</span></td></tr>
    <tr><td>3.1</td><td>Sprint 1: MVC Core & Auth Engine</td><td>Sep 19</td><td>Sep 26</td><td>8</td><td>2.2</td><td>Capoy</td><td><span class="badge badge-done">Completed</span></td></tr>
    <tr><td>3.2</td><td>Sprint 2: Student Portal & Academic Grades</td><td>Sep 27</td><td>Oct 04</td><td>8</td><td>3.1</td><td>Baltazar</td><td><span class="badge badge-done">Completed</span></td></tr>
    <tr><td>3.3</td><td>Sprint 2: Document Request System Workflow</td><td>Oct 01</td><td>Oct 06</td><td>6</td><td>3.1</td><td>Capoy</td><td><span class="badge badge-done">Completed</span></td></tr>
    <tr><td>3.4</td><td>Sprint 3: Cashier Payments & Official Receipts</td><td>Oct 05</td><td>Oct 11</td><td>7</td><td>3.2</td><td>Baltazar</td><td><span class="badge badge-done">Completed</span></td></tr>
    <tr><td>3.5</td><td>Sprint 3: Department Clearance Management</td><td>Oct 07</td><td>Oct 13</td><td>7</td><td>3.2</td><td>Capoy</td><td><span class="badge badge-done">Completed</span></td></tr>
    <tr><td>3.6</td><td>Sprint 4: Admin Controls & User Management</td><td>Oct 12</td><td>Oct 16</td><td>5</td><td>3.4, 3.5</td><td>Baltazar</td><td><span class="badge badge-done">Completed</span></td></tr>
    <tr><td>4.1</td><td>Software Test Plan (IEEE 829) Formulation</td><td>Oct 14</td><td>Oct 17</td><td>4</td><td>3.6</td><td>Balbero</td><td><span class="badge badge-done">Completed</span></td></tr>
    <tr><td>4.2</td><td>Automated QA Test Execution (36 Test Cases)</td><td>Oct 17</td><td>Oct 19</td><td>3</td><td>4.1</td><td>Balbero</td><td><span class="badge badge-done">Completed</span></td></tr>
    <tr><td>4.3</td><td>OWASP Security Audit & Vulnerability Testing</td><td>Oct 19</td><td>Oct 20</td><td>2</td><td>4.2</td><td>Balbero</td><td><span class="badge badge-done">Completed</span></td></tr>
    <tr><td>5.1</td><td>Ethical Impact & RA 10173 DPA Analysis</td><td>Oct 20</td><td>Oct 21</td><td>2</td><td>1.1</td><td>Bautista/Aquino</td><td><span class="badge badge-done">Completed</span></td></tr>
    <tr><td>5.2</td><td>System Modeling (Mermaid UML Diagrams)</td><td>Oct 20</td><td>Oct 22</td><td>3</td><td>3.6</td><td>Aquino</td><td><span class="badge badge-done">Completed</span></td></tr>
    <tr><td>5.3</td><td>UI/UX Wireframes Catalog Compilation</td><td>Oct 21</td><td>Oct 22</td><td>2</td><td>3.6</td><td>Capoy/Baltazar</td><td><span class="badge badge-done">Completed</span></td></tr>
    <tr><td>5.4</td><td>Final Presentation Slide Deck (.pptx)</td><td>Oct 22</td><td>Oct 23</td><td>2</td><td>5.2</td><td>Aquino</td><td><span class="badge badge-done">Completed</span></td></tr>
    <tr><td>5.5</td><td>Master Documentation & Final Quality Review</td><td>Oct 23</td><td>Oct 24</td><td>2</td><td>All</td><td>Team</td><td><span class="badge badge-done">Completed</span></td></tr>
  </tbody>
</table>

<h2>5.1 Visual Gantt Chart Timeline (8-Week Project Schedule)</h2>
<div class="code-box">
Week 1 [Sep 01 - Sep 07]: [████ Inception & Requirements Gathering (Bautista)]
Week 2 [Sep 08 - Sep 14]: [███ SDLC Plan (Aquino)] [████ 3NF Database Modeling (Baltazar)]
Week 3 [Sep 15 - Sep 21]: [██ DDL Schema] [████ Sprint 1: MVC Core & Auth (Capoy)]
Week 4 [Sep 22 - Sep 28]: [██████ Sprint 2: Student Portal & Grades (Baltazar)]
Week 5 [Sep 29 - Oct 05]: [████ Sprint 2: Document Requests] [████ Sprint 3: Payments]
Week 6 [Oct 06 - Oct 12]: [████ Sprint 3: Clearances] [████ Sprint 4: Admin Controls]
Week 7 [Oct 13 - Oct 19]: [████ IEEE 829 Test Suite] [████ Automated QA 36 Tests (Balbero)]
Week 8 [Oct 20 - Oct 24]: [███ Security Audit] [███ UML Diagrams] [███ PPT Deck & Final Review]
</div>

<h2>5.2 Critical Path Analysis</h2>
<p>
The Critical Path for the SSIS project comprises the sequence of dependent tasks that directly dictate
the minimum project duration:
<strong>1.1 Requirements -> 2.1 Database Modeling -> 2.2 Schema Script -> 3.1 Auth Engine -> 3.2 Student Portal -> 3.4 Cashier Payments -> 3.6 Admin Controls -> 4.2 Automated Testing -> 5.5 Final Review & Submission.</strong>
Zero slack was permitted on this path; buffer days were strategically allocated in Week 7 and Week 8
to ensure rigorous testing and documentation without risking delivery deadlines.
</p>
"""
    html_doc = build_html_wrapper(
        title="Agile/Scrum SDLC Plan & Gantt Chart Documentation",
        deliverable_code="TASK-2A & GANTT (Aquino - SCRUM Master)",
        author_role="Aquino (SCRUM Master / Presentation Lead)",
        content_html=html_content
    )
    html_path = os.path.join(OUT_DIR, "SDLC_Plan_and_Gantt_Chart.html")
    pdf_path = os.path.join(OUT_DIR, "SDLC_Plan_and_Gantt_Chart.pdf")
    with open(html_path, "w", encoding="utf-8") as f:
        f.write(html_doc)
    html_to_pdf(html_path, pdf_path)
    print(f"Generated PDF: {pdf_path}")
    return pdf_path

if __name__ == "__main__":
    generate_docx()
    generate_pdf()
