# NORSU OJT DTR — Deployment & Operations

This document covers production deployment, environment configuration, and operational practices (backups, maintenance).

---

## 1. Production environment checklist

Before going live, ensure the following.

### 1.1 Environment variables (`.env`)

| Variable | Production value | Notes |
|----------|------------------|--------|
| `APP_ENV` | `production` | Required for production behavior. |
| `APP_DEBUG` | `false` | **Must be false** in production to avoid exposing errors and stack traces. |
| `APP_URL` | Your live URL | e.g. `https://ojt-dtr.norsu.edu.ph`. Used for links and redirects. |
| `APP_KEY` | Generated key | Run `php artisan key:generate` once; keep this secret. |
| `APP_TIMEZONE` | `Asia/Manila` | Optional; app defaults to Asia/Manila. |
| `SESSION_DRIVER` | `database` or `file` | Use `database` if you want session persistence across restarts. |
| `SESSION_SECURE_COOKIE` | `true` | Set to `true` if your site is served over HTTPS. |
| `DB_CONNECTION` | `mysql` | Use MySQL (or your chosen DB) in production. |
| `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | Your DB details | Keep credentials secure; avoid committing `.env`. |
| `LOG_CHANNEL` | `stack` | Default; ensure `LOG_LEVEL` is `error` or `warning` in production if you prefer fewer logs. |

**Security:** Never run production with `APP_DEBUG=true`. It exposes stack traces and environment details. Use `APP_DEBUG=false` and set `APP_ENV=production`.

### 1.2 One-time setup on the server

```bash
# Install dependencies
composer install --no-dev --optimize-autoloader
npm ci
npm run vendor:copy   # Copy Bootstrap & Bootstrap Icons to public/vendor

# Environment
cp .env.example .env
php artisan key:generate

# Configure .env for production (APP_ENV=production, APP_DEBUG=false, APP_URL, DB_*, etc.)
# Then run migrations
php artisan migrate --force

# Optional: seed initial coordinator/students if needed
# php artisan db:seed --force

# Cache config and routes (recommended in production)
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 1.3 Web server and document root

- Point the document root to the **`public`** directory (e.g. `/var/www/norsu-ojt-dtr/public`).
- Ensure the server is configured to send all requests to `public/index.php` (e.g. Apache `mod_rewrite` or Nginx `try_files`).

---

## 2. Database backups

Regular backups prevent data loss. Adjust paths and schedule to your environment.

### 2.1 MySQL

**Manual backup:**
```bash
mysqldump -u YOUR_USER -p YOUR_DATABASE > backup_$(date +%Y%m%d_%H%M).sql
```

**Restore:**
```bash
mysql -u YOUR_USER -p YOUR_DATABASE < backup_20250129_120000.sql
```

**Cron (daily at 2 AM):**  
Add to crontab (`crontab -e`):
```cron
0 2 * * * mysqldump -u YOUR_USER -pYOUR_PASSWORD YOUR_DATABASE > /path/to/backups/ojt_dtr_$(date +\%Y\%m\%d).sql
```
Use a dedicated backup user with minimal privileges and store the password securely (e.g. in a script with restricted permissions).

### 2.2 SQLite

If you use SQLite (e.g. `database/database.sqlite`):

**Manual backup:**
```bash
cp database/database.sqlite database/backups/ojt_dtr_$(date +%Y%m%d).sqlite
```

**Cron (daily):**
```cron
0 2 * * * cp /path/to/norsu-ojt-dtr/database/database.sqlite /path/to/backups/ojt_dtr_$(date +\%Y\%m\%d).sqlite
```

Keep at least 7–30 days of backups and store them off-server or in a different region if possible.

---

## 3. Deploying updates

When you pull new code or deploy a new version:

```bash
git pull   # or your deploy method

composer install --no-dev --optimize-autoloader
npm ci
npm run vendor:copy

php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

If you use queues (e.g. for emails or jobs), restart workers after deploy:

```bash
php artisan queue:restart
# or restart your queue:work process (supervisor, systemd, etc.)
```

---

## 4. Queues (optional)

The app uses `QUEUE_CONNECTION=database` by default. If you use queues in production:

- Run one or more workers: `php artisan queue:work --tries=3`
- Run them under a process manager (e.g. Supervisor or systemd) so they restart on failure and after deploys.

---

## 5. Logs and monitoring

- Laravel logs go to `storage/logs/laravel.log` (or the channel configured in `config/logging.php`).
- Rotate logs regularly (logrotate or your host’s tool) to avoid filling disk.
- In production, set `LOG_LEVEL=error` or `warning` in `.env` if you want less verbose logging.

---

## 6. Security reminders

- Never set `APP_DEBUG=true` in production.
- Keep `APP_KEY` and DB credentials secret; do not commit `.env`.
- Use HTTPS and set `SESSION_SECURE_COOKIE=true`.
- Restrict write access to `storage` and `bootstrap/cache` to the web server user only.

---

## 7. Quick reference

| Task | Command |
|------|--------|
| Run migrations | `php artisan migrate --force` |
| Clear all caches | `php artisan optimize:clear` |
| Rebuild caches | `php artisan config:cache && php artisan route:cache && php artisan view:cache` |
| Backup DB (MySQL) | `mysqldump -u USER -p DATABASE > backup.sql` |
| Backup DB (SQLite) | `cp database/database.sqlite backup.sqlite` |

---

## 6. Testing the PWA on iOS (iPhone / iPad)

To test NORSU OJT DTR as a PWA on your iOS device, the phone must be able to open your app in **Safari**. Two options:

### Option A: Same Wi‑Fi (quick test)

Your computer runs the app; your iPhone uses the same Wi‑Fi and opens the site by your computer’s IP.

**1. Run the app so it’s reachable on the network**

On your **Windows PC** (where the project lives):

```bash
cd c:\xampp\OJT\norsu-ojt-dtr
php artisan serve --host=0.0.0.0
```

Leave this running. Note the line that says something like: `Server running on [http://0.0.0.0:8000]`.

**2. Find your PC’s IP address**

- Open **Command Prompt** or **PowerShell** and run: `ipconfig`
- Under your Wi‑Fi adapter, find **IPv4 Address** (e.g. `192.168.1.105`).

**3. On your iPhone**

- Connect the iPhone to the **same Wi‑Fi** as the PC.
- Open **Safari** (PWA install and service worker work in Safari on iOS).
- In the address bar type: `http://YOUR_PC_IP:8000`  
  Example: `http://192.168.1.105:8000`
- You should see the login page. Log in as student or coordinator and use the app as usual.

**4. Add to Home Screen (install as PWA)**

- In Safari, tap the **Share** button (square with arrow).
- Scroll and tap **“Add to Home Screen”**.
- Edit the name if you want (e.g. “OJT DTR”), then tap **Add**.
- An icon (NORSU seal) appears on your home screen. Tapping it opens the app in full‑screen like a native app.

**Note:** Over **HTTP** (e.g. `http://192.168.1.x:8000`), the **service worker** may not register on iOS (Safari requires HTTPS for that). So “Add to Home Screen” and full‑screen will work, but **offline caching** might not until you use HTTPS (Option B).

**If the iPhone says "cannot be reached":** (1) Use `php artisan serve --host=0.0.0.0`. (2) Allow port 8000 in Windows Firewall — in **PowerShell as Administrator:** `New-NetFirewallRule -DisplayName "Laravel Serve 8000" -Direction Inbound -LocalPort 8000 -Protocol TCP -Action Allow -Profile Private`. (3) PC and iPhone on same Wi-Fi; confirm IP with `ipconfig`. (4) Test `http://YOUR_IP:8000` in the PC browser first.

**Still can't open it?** Use **Option B (ngrok)** below — your iPhone only needs internet; it doesn't need to be on the same Wi‑Fi. If ngrok works, the problem is likely Windows Firewall or router "AP isolation." On the PC you can also: run `netstat -an | findstr 8000` and confirm you see `0.0.0.0:8000` (not only `127.0.0.1:8000`); or temporarily turn off Windows Firewall to test, then turn it back on and add the rule again.

---

### Option B: HTTPS (full PWA, including offline)

For the service worker (offline caching, etc.) to work on iOS, the site must be opened over **HTTPS**. Easiest way for local testing is a tunnel that gives you an HTTPS URL to your PC.

**1. Install ngrok (one-time)**

- Download from [https://ngrok.com/download](https://ngrok.com/download) or install with: `choco install ngrok` (Windows).
- Sign up at ngrok.com and run `ngrok config add-authtoken YOUR_TOKEN` once.

**2. Start your app and the tunnel**

On the PC, in **two** terminals:

**Terminal 1:**

```bash
cd c:\xampp\OJT\norsu-ojt-dtr
php artisan serve --host=127.0.0.1
```

**Terminal 2:**

```bash
ngrok http 8000
```

ngrok will print an HTTPS URL, e.g. `https://abc123.ngrok-free.app`.

**3. Set APP_URL (optional but recommended)**

In `.env` set:

```env
APP_URL=https://abc123.ngrok-free.app
```

Then run: `php artisan config:clear`. (Use your real ngrok URL from step 2.)

**4. On your iPhone**

- In **Safari**, open the **HTTPS** ngrok URL (e.g. `https://abc123.ngrok-free.app`).
- Use the app; then **Share → Add to Home Screen** to install the PWA.
- With HTTPS, the service worker can register and offline caching can work (e.g. after you’ve visited the student dashboard once).

---

### Checklist for iOS PWA test

| Step | Done |
|------|------|
| PC and iPhone on same Wi‑Fi (Option A) or ngrok HTTPS (Option B) | ☐ |
| Open site in **Safari** (not Chrome) | ☐ |
| Login works (unified login: email/student no + password + program) | ☐ |
| Add to Home Screen (Share → Add to Home Screen) | ☐ |
| Icon on home screen opens app full‑screen | ☐ |
| (Option B) After loading dashboard once, turn off Wi‑Fi and reload to test offline shell | ☐ |

