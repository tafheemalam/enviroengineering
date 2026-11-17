# REST API

Base URL: `/api`. All responses are JSON:

```json
{ "success": true, "data": { ... } }
{ "success": false, "message": "Human readable error", "errors": { "field": "message" } }
```

Public GET responses are cached by the browser for 60 seconds. The API is read-only apart from the two form endpoints. Admin operations go through the session-authenticated PHP admin panel, not this API.

## Read endpoints

| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/bootstrap` | Public settings, social links, services for navigation. The PHP shell also inlines this as `window.__QES_BOOT__`. |
| GET | `/api/settings` | Public website settings only. SMTP and other private keys are never exposed. |
| GET | `/api/home` | Home sections, featured services, latest 3 posts, testimonials, home SEO |
| GET | `/api/pages/{key}` | Page and its visible sections. Keys: `home, about, services, contact, quote, insights, privacy, terms` |
| GET | `/api/services` | All published services |
| GET | `/api/services/{slug}` | One service with full description, benefits, features, SEO and 3 related services |
| GET | `/api/posts?page=1&limit=9` | Published insights, paginated (`items, total, page, per_page`) |
| GET | `/api/posts/{slug}` | One article |
| GET | `/api/testimonials` | Published testimonials. Empty if they are disabled in Settings. |
| GET | `/api/seo/{key}` | SEO meta for a page key |

### Section shape

```json
{
  "key": "why", "eyebrow": "Why Choose Us", "title": "…", "subtitle": "…",
  "paragraphs": ["…"], "image": "/uploads/…", "image_alt": "…",
  "button_text": "…", "button_link": "/services", "button2_text": "…", "button2_link": "…",
  "items": [{ "title": "…", "text": "…", "icon": "ShieldCheck" }]
}
```

### Service shape

```json
{
  "id": 1, "title": "…", "slug": "environmental-consultancy-audits",
  "short_description": "…", "image": "/uploads/services/….svg", "image_alt": "…",
  "icon": "Leaf", "page_type": "detail | contact", "is_featured": true
}
```

For a `page_type: "contact"` service, the frontend links to `/contact?service={slug}`, and the form preselects that service. The server also 301-redirects `/services/{slug}` to that URL.

## Form endpoints

### `POST /api/contact`

```json
{
  "full_name": "Jane Doe",          // required, 2–120 chars
  "company_name": "Acme Ltd",       // optional
  "email": "jane@acme.com",         // required, valid email
  "phone": "+1 555 123 4567",       // required, 7–20 digits/spaces/()+-.
  "service_id": 3,                  // optional, must be a published service
  "subject": "Inquiry about calibration",   // required, ≥ 3 chars
  "message": "…",                   // required, 10–5000 chars
  "website": "",                    // honeypot – must be empty
  "_started": 1731770000000         // ms timestamp when the form was rendered
}
```

### `POST /api/quote`

The same fields as `/contact`, except:

- `service_id` is **required**
- `subject` is optional
- three extra optional fields: `project_location`, `timeline`, `budget_range`

### Responses

| Status | Meaning |
|---|---|
| 201 | Saved. `{ success, message, reference }` |
| 403 | Request came from a disallowed origin |
| 422 | Validation failed. `errors` maps each field to a message. |
| 429 | Rate limit hit (5 per hour per IP) or form submitted too quickly |
| 500 | Server error. Details are logged, not shown. |

After a successful submission, the server stores the inquiry, emails the notification address and, if enabled, sends the visitor a confirmation. Email failures are logged and never block the visitor.

## Other server routes

| Route | Description |
|---|---|
| `/sitemap.xml` | Dynamic sitemap: static pages, detail-type services, published insights. Pages set to `noindex` are excluded. |
| `/robots.txt` | Disallows `/admin/` and `/api/` and links to the sitemap |
| `/*` | `index.php`. Serves the React app with server-rendered meta tags, JSON-LD and fallback content. Unknown URLs return a real HTTP 404. |
