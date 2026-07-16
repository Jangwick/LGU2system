# Auto-Deploy to CyberPanel via SSH

When you push code to `main` or `master`, GitHub Actions automatically deploys your `LLRMSystem` folder to `llrm.spvalenzuela.com` using SSH + rsync.

---

## How It Works (Simple Version)

```
You commit & push → GitHub Actions runs → SSH into your server → rsync copies files → Live site updated
```

No manual uploading. No FTP. Just `git push` and your hosted site updates automatically.

---

## Setup: 3 Steps

### Step 1 — Enable SSH in CyberPanel

You already see this in CyberPanel:

> **SSH Access**
> Set up SSH access for llrm.spvalenzuela.com.
> SSH user for llrm.spvalenzuela.com is **llrms9456**

1. Click **Set up SSH access** in CyberPanel.
2. Set a password (or use the one shown).
3. SSH is now enabled for your site.

### Step 2 — Add Secrets to GitHub

Go to your GitHub repo:
**https://github.com/Jangwick/LGU2system → Settings → Secrets and variables → Actions → New repository secret**

Add these **2 required** secrets:

| Secret Name | Value | Notes |
|---|---|---|
| `DEPLOY_PASSWORD` | Your SSH password from CyberPanel | The password for user `llrms9456` |
| `DEPLOY_PATH` | `/home/llrms9456/public_html` | This is where CyberPanel serves your website files |

That's it. These are the only two you **must** add.

These **3 optional** secrets already have defaults but you can override them:

| Secret Name | Default Value | Notes |
|---|---|---|
| `DEPLOY_HOST` | `llrm.spvalenzuela.com` | Your domain |
| `DEPLOY_USER` | `llrms9456` | CyberPanel SSH username |
| `DEPLOY_PORT` | `22` | SSH port (change if different) |

### Step 3 — Commit and Push

```bash
git add .github/workflows/deploy.yml DEPLOYMENT.md
git commit -m "Add auto-deploy to CyberPanel"
git push origin main
```

The first deploy will run automatically. Go to your GitHub repo → **Actions** tab to watch it happen.

---

## What Gets Deployed

The workflow copies the `LLRMSystem/` folder to your server's `public_html`.

**Included:** All PHP files, views, models, controllers, config, `.htaccess`, `storage/.htaccess`, `composer.json`

**Excluded (not overwritten on server):**
- `modules/core/config/config.local.php` — your server's database credentials and local settings
- `storage/backups`, `storage/documents`, `storage/keys`, `storage/profiles`, `storage/temp`, `storage/versions` — uploaded files on the server
- `node_modules`, `vendor`, `tests` — dev-only files

This means your server's database config and uploaded files are never wiped when you deploy.

---

## How to Trigger a Deploy

**Automatic** — every time you push to `main` or `master`:

```bash
git add .
git commit -m "your changes"
git push origin main
```

**Manual** — go to GitHub → **Actions** tab → **Deploy to CyberPanel** → **Run workflow** button.

---

## Checking Deploy Status

1. Go to **https://github.com/Jangwick/LGU2system/actions**
2. Click the latest run.
3. Green checkmark = deployed successfully.
4. Red X = something failed. Click it to see the error log.

---

## Troubleshooting

| Problem | Fix |
|---|---|
| `DEPLOY_PASSWORD is required` | You forgot to add the `DEPLOY_PASSWORD` secret in GitHub |
| `Permission denied` or `Auth failed` | Wrong password in the secret, or SSH not enabled in CyberPanel |
| Site shows old content | Check if `DEPLOY_PATH` is correct — try `/home/llrms9456/public_html` |
| `rsync: connection unexpectedly closed` | Server might be blocking SSH — check CyberPanel firewall settings |
| Deploy succeeds but site is broken | SSH into server and check file permissions: `chmod -R 755 /home/llrms9456/public_html` |

---

## First-Time Server Setup Checklist

After your first successful deploy, SSH into the server and do these once:

```bash
# SSH in (use your CyberPanel password)
ssh llrms9456@llrm.spvalenzuela.com

# Go to your site directory
cd /home/llrms9456/public_html

# Make sure storage folders are writable
chmod -R 775 storage/

# Copy the example config and edit it with your LIVE database credentials
cp modules/core/config/config.example.php modules/core/config/config.local.php
nano modules/core/config/config.local.php
# Set your real DB host, DB name, DB user, DB password for the live server

# If composer is available on the server, install dependencies
composer install --no-dev --optimize-autoloader
```

Your local `config.local.php` (with XAMPP credentials) is never deployed — the server keeps its own copy.
