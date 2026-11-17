# Installation & Deployment

## Requirements

- PHP **8.2+** with these extensions: `pdo_mysql`, `mbstring`, `fileinfo`, `dom`, `openssl`
- MySQL 5.7+ or MariaDB 10.3+
- Apache with `mod_rewrite` (Nginx config below)
- Node.js 18+ to build the frontend. The production server doesn't need Node.

## 1. Create the database

```sql
CREATE DATABASE qes_website CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'qes_user'@'localhost' IDENTIFIED BY 'a-strong-password';
GRANT SELECT, INSERT, UPDATE, DELETE ON qes_website.* TO 'qes_user'@'localhost';
```

Import `database/schema.sql` with phpMyAdmin or `mysql -u root qes_website < database/schema.sql`.

> ⚠️ `schema.sql` **drops and recreates** all tables. Only run it against an empty or disposable database.

## 2. Configure

Copy `.env.example` to `backend/.env` and set:

| Key | Purpose |
|---|---|
| `APP_ENV` / `APP_DEBUG` | `production` / `false` on live servers |
| `APP_URL` | Public URL, e.g. `https://www.your-domain.com`. Used for canonical URLs, the sitemap and OG tags. |
| `APP_KEY` | Random string: `php -r "echo bin2hex(random_bytes(32));"` |
| `DB_*` | Database credentials |
| `CORS_ORIGINS` | Needed only for the Vite dev server |

`.env` is blocked from web access by `.htaccess`. Keep it out of version control.

## 3. Build the frontend

```bash
cd frontend
npm install
npm run build        # writes to ../backend/build
```

## 4. Upload to the host

The **contents of `backend/`** become your web root (e.g. `public_html/`):

```
public_html/
├── .htaccess          rewrites, security headers, caching
├── .env               your configuration
├── index.php          SEO-aware React shell
├── sitemap.php        → /sitemap.xml
├── robots.php         → /robots.txt
├── api/               REST API
├── admin/             admin dashboard (/admin)
├── build/             compiled React app
├── config/ includes/ middleware/   (blocked from the web)
└── uploads/           images (PHP execution disabled)
```

You don't need to upload `router.php`; it's only for PHP's built-in dev server. Make `uploads/` writable by PHP (`chmod 755`, owned by the web-server user).

### Nginx (if not using Apache)

```nginx
root /var/www/qes/backend;
index index.php;

location ~ ^/(config|includes|middleware|admin/partials)/ { deny all; }
location ~ /\.env { deny all; }
location ~* ^/uploads/.*\.(php|phtml|phar)$ { deny all; }

location = /sitemap.xml { rewrite ^ /sitemap.php last; }
location = /robots.txt  { rewrite ^ /robots.php last; }
location /api/ { rewrite ^/api/?(.*)$ /api/index.php?route=$1 last; }
location /admin/ { try_files $uri $uri/ =404; }
location / { try_files $uri /index.php$is_args$args; }

location ~ \.php$ {
    include fastcgi_params;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    fastcgi_pass unix:/run/php/php8.2-fpm.sock;
}
location /build/assets/ { expires 1y; add_header Cache-Control "public, immutable"; }
```

## 5. Enable HTTPS

Install an SSL certificate (e.g. Let's Encrypt). Then uncomment the HTTPS redirect at the top of `backend/.htaccess`. Admin session cookies are marked `Secure` automatically over HTTPS.

## 6. Post-launch checklist

1. Sign in at `/admin` with `admin@example.com` / `ChangeMe@2026` and set a new password.
2. **Profile:** change the admin email to a real address so password resets reach you.
3. **Settings:** company phone, email, address, WhatsApp, website URL, timezone and logo.
4. **Settings → Google Maps:** in Google Maps, choose *Share → Embed a map* and paste the code.
5. **Settings → SMTP:** enter your mail server details and click *Send test email*.
6. **Social Links:** add your profile URLs.
7. **Page Content → About → Company introduction:** replace the `[PLACEHOLDER]` paragraph with verified facts.
8. **Services:** upload real photos, then review descriptions and SEO fields.
9. **Pages → Privacy Policy / Terms:** have both reviewed, then publish.
10. **Testimonials:** add only genuine feedback, then enable them in Settings.
11. **Settings → Analytics:** add your GA4 ID and the Search Console verification. Then submit `https://your-domain/sitemap.xml` in Search Console.
12. Work through the remaining items in **Dashboard → Setup Checklist**.

## Updating content vs. code

- Content (text, images, services, SEO) is edited in `/admin` and needs no rebuild.
- Changes to React code need `npm run build`, then uploading the new `backend/build/` folder.

## Backups

Back up the database (e.g. a daily `mysqldump`) and the `uploads/` folder.
