FastReed Express - Quick Local Run Instructions

1) Unzip the package to a folder on your PC.
2) Install Node.js if not installed: https://nodejs.org/
3) Open PowerShell, go to the project folder and run:
   ```powershell
   cd "C:\Users\edehd\Downloads\Fastreedexpress Whole\fastreedexpress.com (4)\fastreedexpress.com"
   npm install
   npm start
   ```
4) Open http://localhost:3000 in your browser.

Test tracking codes:
- E5704139436-FWG
- TEST12345

Admin:
- URL: http://localhost:3000/admin.html
- Credentials: check `config.json` (`initial_admin_user` / `initial_admin_password`)

IMPORTANT:
- Change the admin password after first login.
- For hosting, upload all files to a PHP-capable host and run `php php/init_db.php` there OR upload the created `data/tracking.db`.
