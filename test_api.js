const fs = require('fs');
const fetch = global.fetch || require('node-fetch');
const CONFIG = './config.json';

async function run(){
  const cfg = JSON.parse(fs.readFileSync(CONFIG,'utf8'));
  const base = 'http://localhost:3000';
  console.log('Login as', cfg.initial_admin_user);
  const loginResp = await fetch(base + '/api/login', { method:'POST', headers:{ 'Content-Type':'application/json' }, body: JSON.stringify({ username: cfg.initial_admin_user, password: cfg.initial_admin_password }) });
  const setCookie = loginResp.headers.get('set-cookie');
  const j = await loginResp.json();
  console.log('Login response:', j);
  if (!j.ok) throw new Error('Login failed');
  const cookie = setCookie ? setCookie.split(';')[0] : '';

  // Create tracking
  const createPayload = {
    products: [ { name: 'Test Item', qty: '1', weight: '0.5kg' } ],
    sender: { name: 'Alice', phone: '123', email: 'a@example.com' },
    receiver: { name: 'Bob', phone: '456', email: 'b@example.com' },
    origin: 'City A', destination: 'City B', expectedDelivery: '2025-12-20'
  };
  const createResp = await fetch(base + '/api/create_tracking', { method:'POST', headers:{ 'Content-Type':'application/json', 'Cookie': cookie }, body: JSON.stringify(createPayload) });
  const created = await createResp.json();
  console.log('Create response:', created);
  if (!created.ok) throw new Error('Create failed');
  const tcode = created.tcode;

  // List
  const listResp = await fetch(base + '/api/list_tracking', { method:'GET', headers:{ 'Cookie': cookie } });
  const list = await listResp.json();
  console.log('List length:', list.list.length);

  // Update status
  const upd = await fetch(base + '/api/update_status', { method:'POST', headers:{ 'Content-Type':'application/json', 'Cookie': cookie }, body: JSON.stringify({ tcode, status: 'in transit', note: 'Departed facility' }) });
  console.log('Update status response:', await upd.json());

  // Public track
  const trackResp = await fetch(base + '/api/track?tcode=' + encodeURIComponent(tcode));
  console.log('Public track:', await trackResp.json());
  console.log('All tests passed');
}

run().catch(e=>{ console.error('Test failed:', e && e.message ? e.message : e); process.exit(1); });
