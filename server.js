const express = require("express");
const path = require("path");
const fs = require("fs");
const session = require("express-session");
const bcrypt = require("bcryptjs");
const bodyParser = require("body-parser");
const multer = require("multer");
const storage = require("./lib/storage");

const app = express();
const PORT = process.env.PORT || 3000;

// Data paths
const DATA_DIR = path.join(__dirname, "data");
const TRACK_FILE = path.join(DATA_DIR, "tracking.json");
const ADMIN_FILE = path.join(DATA_DIR, "admin.json");
const LAST_ID = path.join(DATA_DIR, "last_id.txt");
const CONFIG = path.join(__dirname, "config.json");

// Ensure data folder exists
if (!fs.existsSync(DATA_DIR)) fs.mkdirSync(DATA_DIR);

// Load config for initial admin
let config = { initial_admin_user: "admin", initial_admin_password: "admin" };
try {
  config = JSON.parse(fs.readFileSync(CONFIG, "utf8"));
} catch (e) {}

// Bootstrap admin.json if missing
if (!fs.existsSync(ADMIN_FILE)) {
  const hash = bcrypt.hashSync(config.initial_admin_password, 10);
  const initial = { user: config.initial_admin_user, pass_hash: hash };
  fs.writeFileSync(ADMIN_FILE, JSON.stringify(initial, null, 2));
}

// Middleware
app.use(bodyParser.json());
app.use(bodyParser.urlencoded({ extended: true }));

const MemoryStore = session.MemoryStore;
app.use(
  session({
    secret: "fastreed-secret-please-change",
    resave: false,
    saveUninitialized: false,
    cookie: { secure: false, httpOnly: true, maxAge: 24 * 60 * 60 * 1000 },
    store: new MemoryStore(),
  })
);

// Serve static files from project root so existing asset paths work
app.use(express.static(path.join(__dirname)));

// Serve index.html
app.get("/", (req, res) => {
  res.sendFile(path.join(__dirname, "index.html"));
});

// Serve admin.html on /admin route
app.get("/admin", (req, res) => {
  res.sendFile(path.join(__dirname, "admin.html"));
});


// Async storage helpers
async function readAll() {
  return await storage.readJSON(TRACK_FILE, {});
}
async function writeAll(obj) {
  return await storage.writeJSON(TRACK_FILE, obj);
}
async function readAdmin() {
  return await storage.readJSON(ADMIN_FILE, null);
}
async function writeAdmin(obj) {
  return await storage.writeJSON(ADMIN_FILE, obj);
}

// Configure uploads directory for product images
const UPLOAD_DIR = path.join(__dirname, "images", "uploads");
if (!fs.existsSync(UPLOAD_DIR)) fs.mkdirSync(UPLOAD_DIR, { recursive: true });
const upload = multer({
  storage: multer.diskStorage({
    destination: (req, file, cb) => cb(null, UPLOAD_DIR),
    filename: (req, file, cb) => cb(null, Date.now() + '-' + file.originalname.replace(/\s+/g, '_')),
  }),
});

// Ping
app.get("/api/ping", (req, res) => {
  res.json({
    ok: true,
    info: {
      server_time: new Date().toISOString(),
      session_id: req.sessionID,
      session_admin: !!req.session.admin,
      node_version: process.version,
    },
  });
});

// Login
app.post("/api/login", async (req, res) => {
  try {
    const { username, password } = req.body || {};
    if (!username || !password)
      return res.status(400).json({ error: "Missing username or password" });

    const creds = await readAdmin();
    if (!creds)
      return res.status(500).json({ error: "Admin credentials missing" });

    if (username === creds.user && creds.pass_hash && bcrypt.compareSync(password, creds.pass_hash)) {
      req.session.admin = true;
      req.session.save((err) => {
        if (err) console.error('Session save error:', err);
        console.log('Login success for user:', username, 'SessionID:', req.sessionID);
        return res.json({ ok: true });
      });
      return;
    }

    return res.status(401).json({ error: "Invalid credentials" });
  } catch (err) {
    console.error("Login error:", err);
    res.status(500).json({ error: "Server error: " + (err.message || err) });
  }
});

// Logout
app.post("/api/logout", (req, res) => {
  req.session.destroy(() => res.json({ ok: true }));
});

// Admin-only routes middleware
function requireAdmin(req, res, next) {
  console.log('requireAdmin check - SessionID:', req.sessionID, 'session.admin:', !!req.session.admin, 'session:', req.session);
  if (!req.session.admin) return res.status(401).json({ error: "Unauthorized" });
  next();
}

// Create tracking (accept JSON or multipart/form-data with productImages)
app.post("/api/create_tracking", requireAdmin, upload.array('productImages'), async (req, res) => {
  try {
    // If products supplied as JSON body (application/json)
    let input = req.body || {};

    // If multipart, admin client sends products as JSON string in req.body.products
    if (typeof input.products === 'string') {
      try {
        input.products = JSON.parse(input.products);
      } catch (e) {
        return res.status(400).json({ error: 'Invalid products JSON' });
      }
    }

    const products = input.products;
    if (!Array.isArray(products) || products.length === 0)
      return res.status(400).json({ error: "Missing products array" });

    let sender = input.sender || { name: "", phone: "", email: "" };
    let receiver = input.receiver || { name: "", phone: "", email: "" };
    if (typeof sender === 'string') {
      try { sender = JSON.parse(sender); } catch (e) { sender = { name: "", phone: "", email: "" }; }
    }
    if (typeof receiver === 'string') {
      try { receiver = JSON.parse(receiver); } catch (e) { receiver = { name: "", phone: "", email: "" }; }
    }
    const origin = input.origin || "";
    const destination = input.destination || "";
    const expected = input.expectedDelivery || "";
    const status = input.status || "pending";

    // Attach uploaded files to products (order preserved)
    const files = req.files || [];
    for (let i = 0; i < products.length; i++) {
      if (files[i]) {
        // store relative web path so it can be served
        products[i].image = path.join('images', 'uploads', files[i].filename).replace(/\\/g, '/');
      }
    }

    const all = await readAll();
    const code = await storage.nextTrackingCode(LAST_ID, TRACK_FILE);

    const record = {
      tcode: code,
      created_at: new Date().toISOString().slice(0, 19).replace("T", " "),
      origin,
      destination,
      sender,
      receiver,
      expectedDelivery: expected,
      status,
      products,
      history: [{ when: new Date().toISOString().slice(0, 19).replace("T", " "), status, note: "Created" }],
    };

    all[code] = record;
    await writeAll(all);
    res.json({ ok: true, tcode: code, record });
  } catch (err) {
    console.error('create_tracking error:', err);
    res.status(500).json({ error: 'Server error: ' + (err.message || err) });
  }
});

// Update status
app.post("/api/update_status", requireAdmin, async (req, res) => {
  const { tcode, status, note } = req.body || {};
  if (!tcode || !status) return res.status(400).json({ error: "Missing tcode or status" });

  const all = await readAll();
  if (!all[tcode]) return res.status(404).json({ error: "Not found" });

  all[tcode].status = status;
  all[tcode].history = all[tcode].history || [];
  all[tcode].history.push({ when: new Date().toISOString().slice(0, 19).replace("T", " "), status, note: note || "" });

  await writeAll(all);
  res.json({ ok: true, record: all[tcode] });
});

// List tracking
app.get("/api/list_tracking", requireAdmin, async (req, res) => {
  const all = await readAll();
  const list = Object.keys(all).map((k) => ({
    tcode: k,
    status: all[k].status || "",
    created_at: all[k].created_at || "",
    origin: all[k].origin || "",
    destination: all[k].destination || "",
    products_count: Array.isArray(all[k].products) ? all[k].products.length : 0,
  }));
  res.json({ ok: true, list });
});

// Public track endpoint
app.get("/api/track", async (req, res) => {
  const t = req.query.tcode || req.query.track || "";
  if (!t) return res.status(400).json({ error: "Missing tcode" });

  const all = await readAll();
  const rec = all[t] || null;
  if (!rec) return res.status(404).json({ error: "Not found" });

  res.json(rec);
});

// Change credentials
app.post("/api/change_credentials", requireAdmin, async (req, res) => {
  const { new_user, new_pass } = req.body || {};
  if (!new_user || !new_pass) return res.status(400).json({ error: "Missing new_user or new_pass" });

  const hash = bcrypt.hashSync(new_pass, 10);
  const creds = { user: new_user, pass_hash: hash };
  await writeAdmin(creds);

  res.json({ ok: true });
});

// Start server
app.listen(PORT, "0.0.0.0", () => console.log(`Server listening on http://localhost:${PORT}`));

// Global error handler
app.use((err, req, res, next) => {
  console.error("Unhandled error:", err);
  res.status(500).json({ error: "Internal server error: " + (err.message || err) });
});
