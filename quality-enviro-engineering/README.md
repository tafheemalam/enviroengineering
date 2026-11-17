# Quality Enviro & Engineering Services – Website

Business website for **Quality Enviro & Engineering Services (QES)**. It has three parts:

| Part | Tech | Location |
|---|---|---|
| Public website | React 18 + Vite, React Router, Bootstrap 5 grid, Lucide icons | `frontend/` |
| REST API + SEO shell | Core PHP 8.2+, PDO/MySQL | `backend/api/`, `backend/index.php` |
| Admin dashboard | PHP + Bootstrap 5 | `backend/admin/` → `/admin` |
| Database | MySQL 5.7+ / MariaDB 10.3+ | `database/schema.sql` |

## Features

- **Pages:** Home, About, Services, service detail pages, Contact (with Google Map), Request a Quote, Insights (blog), Privacy Policy, Terms, and a custom 404.
- **Two kinds of service:** each service either opens its own **detail page** or **redirects to Contact Us** with that service already selected in the form. The admin picks which one.
- **Admin-managed content:** every heading, paragraph, list item, image and button on the site comes from the database.
- **Inquiries:** each submission is validated, protected against spam (honeypot, timing check, per-IP rate limit), saved to the database, emailed to the company and confirmed to the visitor. The admin can search, filter, change status, add notes, and export to CSV.
- **SEO:** for every route, PHP renders the title, description, canonical URL, robots, Open Graph and Twitter tags, JSON-LD (Organization, LocalBusiness, Service, Article, Breadcrumb) and crawlable fallback content **on the server**. Search engines get full metadata without running JavaScript. The XML sitemap and robots.txt are generated dynamically.
- **Extras:** floating WhatsApp button, testimonials, Google Analytics 4 and Search Console fields, media library, rich-text editor, roles (super admin / editor).
- **Security:** PDO prepared statements, output escaping, an HTML allow-list sanitiser, CSRF tokens, `password_hash`, session regeneration and timeout, login rate limiting, a forced change of the default password, password reset by single-use hashed token, upload checks (extension + MIME + real image), and PHP blocked from running in `/uploads`.

## Quick start (local)

```bash
# 1. Database
mysql -u root -e "CREATE DATABASE qes_website CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
mysql -u root qes_website < database/schema.sql

# 2. Backend config
cp .env.example backend/.env       # then edit DB_* and APP_URL=http://localhost:8000

# 3. Build the frontend (outputs to backend/build)
cd frontend && npm install && npm run build && cd ..

# 4. Run
cd backend && php -S localhost:8000 router.php
```

Open http://localhost:8000 for the website and http://localhost:8000/admin for the admin panel.

**Default admin:** `admin@example.com` / `ChangeMe@2026`. You will be made to change the password the first time you sign in.

For frontend development with hot reload, keep the PHP server running and run `npm run dev` in `frontend/`. Then open http://localhost:5173 (it proxies `/api` and `/uploads` to PHP).

## Documentation

- [documentation/installation.md](documentation/installation.md): installation, deployment and post-launch checklist
- [documentation/api-documentation.md](documentation/api-documentation.md): REST API reference

## Placeholder content

Company details that couldn't be verified are seeded as clearly marked placeholders. They include phone `+00 000 000 0000`, `info@example.com`, `[PLACEHOLDER]` address and company background, draft testimonials, and placeholder legal text. The admin **Dashboard → Setup Checklist** lists everything still to replace. The service images are branded SVG illustrations; replace them with real project photos (WebP recommended) in **Services → Edit**.
