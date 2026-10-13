const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');

const OUTPUT_DIR = path.join(__dirname, '..', 'assets', 'figma_wireframes');
if (!fs.existsSync(OUTPUT_DIR)) {
  fs.mkdirSync(OUTPUT_DIR, { recursive: true });
}

const BASE_URL = 'http://localhost/CCS109-final-project';

async function run() {
  const browser = await chromium.launch({
    executablePath: '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
    headless: true
  });

  async function captureRole(username, pagesToCapture) {
    const context = await browser.newContext({
      viewport: { width: 1440, height: 900 },
      deviceScaleFactor: 2
    });
    const page = await context.newPage();

    if (username) {
      await page.goto(`${BASE_URL}/index.php`);
      await page.waitForLoadState('networkidle');
      await page.fill('input[name="student_id"]', username);
      await page.fill('input[name="password"]', 'password123');
      await Promise.all([
        page.waitForNavigation({ waitUntil: 'networkidle' }),
        page.click('button[type="submit"]')
      ]);
    }

    for (const item of pagesToCapture) {
      console.log(`Capturing ${item.name}...`);
      if (item.url) {
        await page.goto(item.url);
        await page.waitForLoadState('networkidle');
      }
      if (item.action) {
        await item.action(page);
      }
      await page.screenshot({ path: path.join(OUTPUT_DIR, item.file), fullPage: item.fullPage !== false });
    }

    await context.close();
  }

  // 1. Sign In
  await captureRole(null, [
    {
      name: '01_Sign_In',
      url: `${BASE_URL}/index.php`,
      file: '01_Sign_In.png',
      fullPage: false
    }
  ]);

  // 2. Student Portal (Juan Dela Cruz)
  await captureRole('2021-00123', [
    { name: '02_Student_Profile', url: `${BASE_URL}/?page=profile`, file: '02_Student_Profile.png' },
    { name: '03_Student_Grades', url: `${BASE_URL}/?page=grades`, file: '03_Student_Grades.png' },
    { name: '04_Student_Subjects', url: `${BASE_URL}/?page=subjects`, file: '04_Student_Subjects.png' },
    { name: '05_Student_Requests', url: `${BASE_URL}/?page=requests`, file: '05_Student_Document_Requests.png' },
    {
      name: '06_Student_Request_Modal',
      url: `${BASE_URL}/?page=requests`,
      file: '06_Student_New_Request_Modal.png',
      fullPage: false,
      action: async (p) => {
        const btn = await p.$('button[onclick*="openModal"], button:has-text("New Request")');
        if (btn) {
          await btn.click();
          await p.waitForTimeout(400);
        }
      }
    }
  ]);

  // 3. Registrar Portal (M. Santiago)
  await captureRole('m.santiago', [
    { name: '07_Registrar_Enrollment', url: `${BASE_URL}/?page=enrollment`, file: '07_Registrar_Enrollment.png' },
    {
      name: '08_Registrar_Enroll_Modal',
      url: `${BASE_URL}/?page=enrollment`,
      file: '08_Registrar_Enroll_Modal.png',
      fullPage: false,
      action: async (p) => {
        const btn = await p.$('button[onclick*="openEnrollModal"], button:has-text("Enroll Student")');
        if (btn) {
          await btn.click();
          await p.waitForTimeout(400);
        }
      }
    },
    { name: '09_Registrar_Grades_Encoding', url: `${BASE_URL}/?page=grades-encoding`, file: '09_Registrar_Grades_Encoding.png' }
  ]);

  // 4. Cashier Portal (Cashier 01)
  await captureRole('cashier01', [
    { name: '10_Cashier_Payments', url: `${BASE_URL}/?page=payments`, file: '10_Cashier_Payments.png' },
    {
      name: '11_Cashier_Record_Payment_Modal',
      url: `${BASE_URL}/?page=payments`,
      file: '11_Cashier_Record_Payment_Modal.png',
      fullPage: false,
      action: async (p) => {
        const btn = await p.$('button[onclick*="openPaymentModal"], button:has-text("Record Payment")');
        if (btn) {
          await btn.click();
          await p.waitForTimeout(400);
        }
      }
    },
    { name: '12_Cashier_Official_Receipt', url: `${BASE_URL}/?page=receipts`, file: '12_Cashier_Official_Receipt.png' }
  ]);

  // 5. Department Portal (Dept Staff)
  await captureRole('dept.staff', [
    { name: '13_Department_Clearance', url: `${BASE_URL}/?page=clearance`, file: '13_Department_Clearance.png' }
  ]);

  // 6. Admin Portal (Ana Reyes)
  await captureRole('ana.reyes', [
    { name: '14_Admin_User_Management', url: `${BASE_URL}/?page=admin-users`, file: '14_Admin_User_Management.png' },
    { name: '15_Admin_Document_Requests', url: `${BASE_URL}/?page=admin-requests`, file: '15_Admin_Document_Requests.png' }
  ]);

  console.log('Successfully captured all high-resolution wireframe assets!');
  await browser.close();
}

run().catch(err => {
  console.error(err);
  process.exit(1);
});
