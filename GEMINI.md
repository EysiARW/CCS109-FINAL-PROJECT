# CCS109 Project Guidelines & Invariants

## 1. Authentication & Route Invariants
- **Login Input Name**: The identifier input field is `name="student_id"`, not `name="username"`.
- **Default Password**: All seeded accounts use `password123`.
- **Verified Seeded Accounts**:
  - `2021-00123` -> Student Portal (`?page=profile`, `?page=grades`, `?page=subjects`, `?page=requests`)
  - `m.santiago` -> Registrar Module (`?page=enrollment`, `?page=grades-encoding`)
  - `cashier01` -> Cashier Module (`?page=payments`, `?page=receipts`)
  - `dept.staff` -> Department Module (`?page=clearance`)
  - `ana.reyes` -> Admin Module (`?page=admin-users`, `?page=admin-requests`)
- **Route Whitelist**: Pages are strictly whitelisted per role in `index.php`. Unauthorized pages fall back to role landing pages.

## 2. Wireframe & Figma Export Workflows
- When exporting website screens to Figma, avoid paywalled plugins with import caps (e.g., `html.to.design`).
- Recommended free workflows:
  1. **Builder.io HTML to Figma Plugin**: 100% free and unlimited for direct vector layer imports.
  2. **Automated Headless Capture**: Run `scripts/capture_wireframes.js` with local Playwright to output high-resolution Retina screenshots in `assets/figma_wireframes/` for instant drag-and-drop into Figma.
