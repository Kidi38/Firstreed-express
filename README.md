FastReed Express - Ready-to-Host Package

Overview
- This package contains a static front-end site plus a small Node.js backend (Express) using JSON storage for shipment tracking.
- Admin UI to create/update/delete tracking records is available at `/admin.html` (web UI). Server-side admin API routes are under `/api` (Node).
- User tracking page: `user_track.html` (customer-facing) uses the Node API `/api/track` to fetch tracking records.

Quick start (Local testing using Node)
1. Ensure Node.js is installed (node -v, npm -v).
2. From the project root run:
   ```powershell
   cd "C:\Users\edehd\Downloads\Fastreedexpress Whole\fastreedexpress.com (4)\fastreedexpress.com"
   npm install
   npm start
   ```
3. Open in browser:
   - Site: http://localhost:3000/index.html (main site)
   - Customer tracking: http://localhost:3000/user_track.html
   - Admin UI: http://localhost:3000/admin.html (login then use the UI to create trackings). The backend API is available under `/api`.

Admin credentials (temporary)
- Username: see `config.json` (`initial_admin_user`)
- Password: see `config.json` (`initial_admin_password`)
Note: On first login the server will create `data/admin.json` with a hashed password; change username/password from the Admin UI immediately after login.

Security notes
- Change the admin password in `php/config.php` after first login.
- For production, put `data/tracking.db` outside the web root or protect the `data/` folder with server rules.
- Enable HTTPS (SSL) on your host — most hosts provide Let's Encrypt.

Hosting and domain (short checklist)
1. Buy a domain (Namecheap, GoDaddy, Google Domains).
2. Buy or prepare hosting that supports PHP (shared cPanel hosting is fine).
3. Upload files to `public_html` (or host's web root) via FTP/SFTP or File Manager.
4. Run `php php/init_db.php` once on the host or upload the pre-initialized `data/tracking.db` file.
5. Set folder permissions so webserver can write `data/tracking.db` (e.g., 755/775 depending on host).
6. Configure SSL, point domain DNS (A record) to your hosting server IP.

If you want me to deploy this to a specific host (cPanel/FTP, DigitalOcean, Render, etc.), tell me the provider and I will generate step-by-step commands.

Files of interest
- `user_track.html` — public customer tracking page (modern, JS inline)
- `admin.html` — admin web interface (login + create/list/update)
- `server.js` — Node Express server exposing `/api/*` endpoints (login, create_tracking, update_status, list_tracking, track, etc.)
- `lib/storage.js` — safe JSON read/write helpers used by `server.js`
- `config.json` — initial admin username/password used to bootstrap `data/admin.json` on first run
- `data/tracking.json` — main data storage (JSON file)

The system uses JSON storage by default and will operate without a database. `server.js` uses `data/tracking.json` as the primary store. For production you may replace storage with a proper DB.

Contact
- I created this package for quick deployment and demo. Change the admin password and verify `data/tracking.db` permissions before handing to your client.
