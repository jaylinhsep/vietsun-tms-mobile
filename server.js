/**
 * VIETSUN TMS MOBILE - NODE.JS SERVER FOR RENDER.COM
 * Zero external dependencies (runs on built-in Node 18+ HTTP/HTTPS).
 * Handles static PWA files + API Reverse Proxy to https://vsho.vietsunlogistics.com
 */
const http = require("http");
const https = require("https");
const fs = require("fs");
const path = require("path");
const zlib = require("zlib");
const { URL } = require("url");

const PORT = process.env.PORT || 8080;
const REMOTE_BASE = "https://vsho.vietsunlogistics.com";
const DEFAULT_SHEET_ID = "13ndQsSmBsKlHjIRQR0S0MO5BPqqL6J4CECBAYfQi1oo";
const DEFAULT_SHEET_GID = "1046738475";

// Store session cookies in memory
let sessionCookies = "";
let cachedUserString = "trucking14<<|7<<|a0b923820d<<|1<<|0.0.0.0.0.0.1.0.1.1.0.1.0.1.";

const httpsAgent = new https.Agent({
  keepAlive: true,
  rejectUnauthorized: false
});

const VENDOR_NAME_MAP = {
  NHATSON: "NHAT SON",
  NGOCSANG: "NGOC SANG",
  PHUCAN: "PHUC AN",
  TUANHUNG: "TUAN HUNG",
  VUONGPHAT: "VUONG PHAT",
  PHUCTRUONGAN: "PHUC TRUONG AN",
  GIAKHANG: "GIA KHANG"
};

function normalizeVendorName(raw) {
  const clean = (raw || "").toUpperCase().trim();
  const noSpace = clean.replace(/\s+/g, "");
  return VENDOR_NAME_MAP[noSpace] || clean;
}

function parseCsvLine(line) {
  const result = [];
  let cur = "";
  let inQuotes = false;
  for (let i = 0; i < line.length; i++) {
    const ch = line[i];
    if (ch === '"') {
      if (inQuotes && line[i + 1] === '"') {
        cur += '"';
        i++;
      } else {
        inQuotes = !inQuotes;
      }
    } else if (ch === "," && !inQuotes) {
      result.push(cur.trim());
      cur = "";
    } else {
      cur += ch;
    }
  }
  result.push(cur.trim());
  return result;
}

function mergeSetCookies(setCookieHeader) {
  if (!setCookieHeader) return;
  const cookies = Array.isArray(setCookieHeader) ? setCookieHeader : [setCookieHeader];
  const cookieMap = {};
  if (sessionCookies) {
    sessionCookies.split(";").forEach((pair) => {
      const [k, v] = pair.trim().split("=");
      if (k && v) cookieMap[k] = v;
    });
  }
  cookies.forEach((c) => {
    const firstPart = c.split(";")[0].trim();
    const eqIdx = firstPart.indexOf("=");
    if (eqIdx > 0) {
      const k = firstPart.slice(0, eqIdx).trim();
      const v = firstPart.slice(eqIdx + 1).trim();
      cookieMap[k] = v;
    }
  });
  sessionCookies = Object.entries(cookieMap)
    .map(([k, v]) => `${k}=${v}`)
    .join("; ");
}

function remoteRequest(targetUrl, options = {}, body = null, redirectsLeft = 5) {
  return new Promise((resolve, reject) => {
    const parsed = new URL(targetUrl);
    const isTmsHost = parsed.hostname.includes("vietsunlogistics.com");
    const reqOptions = {
      hostname: parsed.hostname,
      port: parsed.port || 443,
      path: parsed.pathname + parsed.search,
      method: options.method || "GET",
      agent: httpsAgent,
      headers: {
        "User-Agent": "Mozilla/5.0 (Linux; Android 14) VietsunTMSMobile/1.0",
        ...(isTmsHost && sessionCookies ? { Cookie: sessionCookies } : {}),
        ...(options.headers || {})
      }
    };

    if (body) {
      reqOptions.headers["Content-Length"] = Buffer.byteLength(body);
    }

    const req = https.request(reqOptions, (res) => {
      if (isTmsHost && res.headers["set-cookie"]) {
        mergeSetCookies(res.headers["set-cookie"]);
      }

      if ([301, 302, 303, 307, 308].includes(res.statusCode) && res.headers.location && redirectsLeft > 0) {
        const nextUrl = new URL(res.headers.location, targetUrl).toString();
        res.resume();
        return resolve(remoteRequest(nextUrl, { method: "GET" }, null, redirectsLeft - 1));
      }

      const chunks = [];
      res.on("data", (chunk) => chunks.push(chunk));
      res.on("end", () => {
        resolve({
          statusCode: res.statusCode,
          headers: res.headers,
          body: Buffer.concat(chunks)
        });
      });
    });

    req.on("error", reject);
    req.setTimeout(30000, () => {
      req.destroy(new Error("Request timeout"));
    });

    if (body) req.write(body);
    req.end();
  });
}

async function loginToTms(username = "trucking14", password = "1") {
  const form = new URLSearchParams({
    username,
    password,
    login: "ĐĂNG NHẬP"
  }).toString();

  const res = await remoteRequest(
    `${REMOTE_BASE}/0login.php`,
    {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" }
    },
    form
  );

  const html = res.body.toString("utf8");
  const match = html.match(/id="userString"\s+value="([^"]+)"/);
  if (match && match[1]) {
    cachedUserString = match[1];
  }
  return cachedUserString;
}

const MIME_TYPES = {
  ".html": "text/html; charset=utf-8",
  ".json": "application/json; charset=utf-8",
  ".js": "application/javascript; charset=utf-8",
  ".css": "text/css; charset=utf-8",
  ".svg": "image/svg+xml",
  ".png": "image/png",
  ".ico": "image/x-icon"
};

function readRequestBody(req) {
  return new Promise((resolve, reject) => {
    const chunks = [];
    req.on("data", (c) => chunks.push(c));
    req.on("end", () => resolve(Buffer.concat(chunks).toString("utf8")));
    req.on("error", reject);
  });
}

const server = http.createServer(async (req, res) => {
  res.setHeader("Access-Control-Allow-Origin", "*");
  res.setHeader("Access-Control-Allow-Methods", "GET, POST, OPTIONS");
  res.setHeader("Access-Control-Allow-Headers", "Content-Type");

  if (req.method === "OPTIONS") {
    res.writeHead(200);
    return res.end();
  }

  const parsedUrl = new URL(req.url, `http://${req.headers.host || "localhost"}`);
  const pathname = parsedUrl.pathname;
  const apiParam = parsedUrl.searchParams.get("api");

  try {
    // Health check for Render.com
    if (pathname === "/healthz") {
      res.writeHead(200, { "Content-Type": "text/plain" });
      return res.end("OK");
    }

    // 1. API Login
    if ((pathname === "/api/login" || apiParam === "login") && req.method === "POST") {
      const rawBody = await readRequestBody(req);
      let creds = { username: "trucking14", password: "1" };
      try {
        creds = JSON.parse(rawBody);
      } catch (_) {}
      const uStr = await loginToTms(creds.username || "trucking14", creds.password || "1");
      res.writeHead(200, { "Content-Type": "application/json; charset=utf-8" });
      return res.end(JSON.stringify({ ok: true, username: creds.username, userString: uStr }));
    }

    // 2. API Return JSON (with Gzip support for fast mobile 5G loading)
    if (pathname === "/api/return_json" || apiParam === "return_json") {
      const ngay = parsedUrl.searchParams.get("ngay") || new Date().toISOString().slice(0, 10);
      const remoteRes = await remoteRequest(`${REMOTE_BASE}/return_json.php?ngay=${encodeURIComponent(ngay)}`);
      const text = remoteRes.body.toString("utf8").trim() || "[]";
      const acceptEncoding = req.headers["accept-encoding"] || "";
      if (acceptEncoding.includes("gzip")) {
        const compressed = zlib.gzipSync(Buffer.from(text, "utf8"));
        res.writeHead(200, {
          "Content-Type": "application/json; charset=utf-8",
          "Content-Encoding": "gzip",
          "Cache-Control": "no-store"
        });
        return res.end(compressed);
      }
      res.writeHead(200, {
        "Content-Type": "application/json; charset=utf-8",
        "Cache-Control": "no-store"
      });
      return res.end(text);
    }

    // 3. API Index CRUD Update
    if ((pathname === "/api/index-crud" || apiParam === "index_crud") && req.method === "POST") {
      if (!sessionCookies) {
        await loginToTms("trucking14", "1");
      }
      const postBody = await readRequestBody(req);
      const remoteRes = await remoteRequest(
        `${REMOTE_BASE}/index-crud.php`,
        {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8" }
        },
        postBody
      );
      res.writeHead(200, { "Content-Type": "text/html; charset=utf-8" });
      return res.end(remoteRes.body);
    }

    // 4. API Live Fleet & Drivers Sync (TMS Vietsun Fleet + Google Sheet Vendor Fleet)
    if (pathname === "/api/drivers" || apiParam === "drivers") {
      const sheetId = parsedUrl.searchParams.get("sheetId") || DEFAULT_SHEET_ID;
      const gid = parsedUrl.searchParams.get("gid") || DEFAULT_SHEET_GID;
      const vietsunList = [];
      const vendorList = [];

      const [tmsResult, sheetResult] = await Promise.allSettled([
        (async () => {
          const form = new URLSearchParams({
            userString: cachedUserString,
            loai: "26",
            query: "0",
            cv: "0",
            id: "1"
          }).toString();
          const r = await remoteRequest(
            `${REMOTE_BASE}/ajax-gvt.php`,
            {
              method: "POST",
              headers: { "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8" }
            },
            form
          );
          const html = r.body.toString("utf8");
          const rowRegex = /<tr id="tr\d+">([\s\S]*?)<\/tr>/g;
          let rowMatch;
          while ((rowMatch = rowRegex.exec(html)) !== null) {
            const inner = rowMatch[1];
            const m = inner.match(/<th[^>]*>([^<]*)<\/th>\s*<td[^>]*>([^<]*)<\/td>\s*<td[^>]*>([^<]*)<\/td>/i);
            if (m) {
              const rawPlate = m[1].trim();
              const plate = rawPlate.toUpperCase().replace(/[^A-Z0-9]/g, "");
              const name = m[2].trim();
              const phone = m[3].trim();
              if (plate && plate !== "DEPOT") {
                vietsunList.push({
                  vendor: "VIETSUN",
                  plate,
                  name,
                  phone
                });
              }
            }
          }
        })(),
        (async () => {
          const csvUrl = `https://docs.google.com/spreadsheets/d/${encodeURIComponent(sheetId)}/export?format=csv&gid=${encodeURIComponent(gid)}`;
          const r = await remoteRequest(csvUrl);
          if (r.statusCode === 200) {
            const csvText = r.body.toString("utf8");
            const lines = csvText.split(/\r?\n/);
            for (let i = 1; i < lines.length; i++) {
              const line = lines[i].trim();
              if (!line) continue;
              const cols = parseCsvLine(line);
              const vendor = normalizeVendorName(cols[0] || "");
              const plate = (cols[1] || "").toUpperCase().replace(/[^A-Z0-9]/g, "");
              const name = (cols[2] || "").trim();
              const phone = (cols[3] || "").trim();
              const cccd = (cols[4] || "").trim();
              const gplx = (cols[5] || "").trim();
              if (plate) {
                vendorList.push({
                  vendor: vendor || "VENDOR",
                  plate,
                  name,
                  phone,
                  cccd,
                  gplx
                });
              }
            }
          }
        })()
      ]);

      res.writeHead(200, {
        "Content-Type": "application/json; charset=utf-8",
        "Cache-Control": "no-store"
      });
      return res.end(
        JSON.stringify({
          ok: true,
          vietsunCount: vietsunList.length,
          vendorCount: vendorList.length,
          vietsun: vietsunList,
          vendors: vendorList,
          tmsError: tmsResult.status === "rejected" ? tmsResult.reason?.message : null,
          sheetError: sheetResult.status === "rejected" ? sheetResult.reason?.message : null,
          updatedAt: new Date().toISOString()
        })
      );
    }

    // 4. Static PWA Files
    let filePath = pathname === "/" ? "/index.html" : pathname;
    const safePath = path.normalize(filePath).replace(/^(\.\.[\/\\])+/, "");
    const absPath = path.join(__dirname, safePath);

    if (fs.existsSync(absPath) && fs.statSync(absPath).isFile()) {
      const ext = path.extname(absPath).toLowerCase();
      res.writeHead(200, { "Content-Type": MIME_TYPES[ext] || "application/octet-stream" });
      return fs.createReadStream(absPath).pipe(res);
    }

    res.writeHead(404, { "Content-Type": "text/plain; charset=utf-8" });
    res.end("404 Not Found");
  } catch (err) {
    console.error("Server error:", err);
    res.writeHead(500, { "Content-Type": "application/json; charset=utf-8" });
    res.end(JSON.stringify({ error: err.message }));
  }
});

server.listen(PORT, () => {
  console.log(`Vietsun TMS Mobile Server listening on port ${PORT}`);
  loginToTms("trucking14", "1")
    .then((u) => console.log("Logged into Vietsun TMS:", u))
    .catch((e) => console.error("Initial login warning:", e.message));
});
