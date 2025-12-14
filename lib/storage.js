const fs = require('fs').promises;
const fsSync = require('fs');
const crypto = require('crypto');

async function readJSON(filePath, defaultValue = null) {
  try {
    const data = await fs.readFile(filePath, 'utf8');
    return JSON.parse(data);
  } catch (error) {
    if (error.code === 'ENOENT') return defaultValue;
    throw error;
  }
}

async function writeJSON(filePath, data) {
  await fs.writeFile(filePath, JSON.stringify(data, null, 2), 'utf8');
}

function generateRandomTrackingCode() {
  const chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
  let code = '';
  for (let i = 0; i < 8; i++) {
    const randomByte = crypto.randomBytes(1)[0];
    code += chars[randomByte % chars.length];
  }
  return code;
}

async function nextTrackingCode(lastIdFile, trackFile) {
  let existingCodes = {};
  if (fsSync.existsSync(trackFile)) {
    try {
      existingCodes = await readJSON(trackFile, {});
    } catch (e) {}
  }
  
  let code;
  let attempts = 0;
  do {
    code = generateRandomTrackingCode();
    attempts++;
    if (attempts >= 100) {
      code = generateRandomTrackingCode() + crypto.randomBytes(1).toString('hex').toUpperCase().substring(0, 1);
      break;
    }
  } while (existingCodes[code]);
  
  return code;
}

module.exports = { readJSON, writeJSON, nextTrackingCode, generateRandomTrackingCode };