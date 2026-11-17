-- =====================================================================
--  Quality Enviro & Engineering Services (QES)
--  MySQL 5.7+/MariaDB 10.3+ schema + demo/seed data
--  Charset: utf8mb4
--
--  Default admin login (CHANGE IMMEDIATELY – you will be forced to):
--     email:    admin@example.com
--     password: ChangeMe@2026
--
--  Every company detail below marked [PLACEHOLDER] is editable in the
--  admin panel and must be replaced with verified information.
-- =====================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS password_resets;
DROP TABLE IF EXISTS rate_limits;
DROP TABLE IF EXISTS contact_inquiries;
DROP TABLE IF EXISTS page_sections;
DROP TABLE IF EXISTS pages;
DROP TABLE IF EXISTS services;
DROP TABLE IF EXISTS posts;
DROP TABLE IF EXISTS testimonials;
DROP TABLE IF EXISTS media;
DROP TABLE IF EXISTS website_settings;
DROP TABLE IF EXISTS social_links;
DROP TABLE IF EXISTS seo_settings;
DROP TABLE IF EXISTS admins;

SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------
-- Admin users
-- ---------------------------------------------------------------------
CREATE TABLE admins (
  id                   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name                 VARCHAR(100)  NOT NULL,
  email                VARCHAR(190)  NOT NULL,
  password_hash        VARCHAR(255)  NOT NULL,
  role                 ENUM('super_admin','editor') NOT NULL DEFAULT 'editor',
  is_active            TINYINT(1)    NOT NULL DEFAULT 1,
  must_change_password TINYINT(1)    NOT NULL DEFAULT 0,
  last_login_at        DATETIME      NULL,
  created_at           TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at           TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_admins_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE password_resets (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  admin_id    INT UNSIGNED NOT NULL,
  token_hash  CHAR(64)     NOT NULL,
  expires_at  DATETIME     NOT NULL,
  used_at     DATETIME     NULL,
  created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_reset_token (token_hash),
  KEY idx_reset_admin (admin_id),
  CONSTRAINT fk_reset_admin FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Media library
-- ---------------------------------------------------------------------
CREATE TABLE media (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  file_path     VARCHAR(255) NOT NULL,           -- relative to /uploads
  original_name VARCHAR(255) NOT NULL,
  mime_type     VARCHAR(100) NOT NULL,
  file_size     INT UNSIGNED NOT NULL DEFAULT 0,
  width         INT UNSIGNED NULL,
  height        INT UNSIGNED NULL,
  alt_text      VARCHAR(255) NULL,
  uploaded_by   INT UNSIGNED NULL,
  created_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_media_path (file_path),
  KEY idx_media_created (created_at),
  CONSTRAINT fk_media_admin FOREIGN KEY (uploaded_by) REFERENCES admins(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Services
-- ---------------------------------------------------------------------
CREATE TABLE services (
  id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title             VARCHAR(180) NOT NULL,
  slug              VARCHAR(190) NOT NULL,
  short_description VARCHAR(500) NOT NULL,
  full_description  MEDIUMTEXT   NULL,              -- sanitised HTML
  benefits          JSON         NULL,              -- ["…","…"]
  key_features      JSON         NULL,              -- ["…","…"]
  image             VARCHAR(255) NULL,              -- relative to /uploads
  image_alt         VARCHAR(255) NULL,
  icon              VARCHAR(50)  NULL,              -- lucide icon name
  page_type         ENUM('detail','contact') NOT NULL DEFAULT 'detail',
  status            ENUM('published','draft') NOT NULL DEFAULT 'draft',
  is_featured       TINYINT(1)   NOT NULL DEFAULT 0,
  sort_order        INT          NOT NULL DEFAULT 0,
  seo_title         VARCHAR(190) NULL,
  meta_description  VARCHAR(320) NULL,
  meta_keywords     VARCHAR(320) NULL,
  og_image          VARCHAR(255) NULL,
  created_at        TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at        TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_services_slug (slug),
  KEY idx_services_status_order (status, sort_order),
  KEY idx_services_featured (is_featured)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Pages + editable sections
-- ---------------------------------------------------------------------
CREATE TABLE pages (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  page_key    VARCHAR(60)  NOT NULL,        -- home, about, contact, privacy, terms, services, insights, quote
  title       VARCHAR(190) NOT NULL,
  slug        VARCHAR(190) NOT NULL,        -- public path ('' for home)
  content     MEDIUMTEXT   NULL,            -- sanitised HTML (used by legal pages)
  status      ENUM('published','draft') NOT NULL DEFAULT 'published',
  created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_pages_key (page_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE page_sections (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  page_id      INT UNSIGNED NOT NULL,
  section_key  VARCHAR(60)  NOT NULL,
  label        VARCHAR(120) NOT NULL,          -- admin-facing name
  eyebrow      VARCHAR(190) NULL,              -- small kicker text
  title        VARCHAR(255) NULL,
  subtitle     VARCHAR(500) NULL,
  content      TEXT         NULL,              -- plain text, paragraphs split by blank line
  image        VARCHAR(255) NULL,
  image_alt    VARCHAR(255) NULL,
  button_text  VARCHAR(100) NULL,
  button_link  VARCHAR(255) NULL,
  button2_text VARCHAR(100) NULL,
  button2_link VARCHAR(255) NULL,
  items        JSON         NULL,              -- [{"title":"","text":"","icon":""}]
  is_visible   TINYINT(1)   NOT NULL DEFAULT 1,
  sort_order   INT          NOT NULL DEFAULT 0,
  created_at   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_section (page_id, section_key),
  CONSTRAINT fk_section_page FOREIGN KEY (page_id) REFERENCES pages(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Contact / quotation inquiries
-- ---------------------------------------------------------------------
CREATE TABLE contact_inquiries (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  inquiry_type    ENUM('contact','quote') NOT NULL DEFAULT 'contact',
  full_name       VARCHAR(120) NOT NULL,
  company_name    VARCHAR(160) NULL,
  email           VARCHAR(190) NOT NULL,
  phone           VARCHAR(40)  NOT NULL,
  service_id      INT UNSIGNED NULL,
  service_title   VARCHAR(180) NULL,            -- snapshot in case service is deleted
  subject         VARCHAR(200) NOT NULL,
  message         TEXT         NOT NULL,
  project_location VARCHAR(200) NULL,           -- quote only
  timeline        VARCHAR(100) NULL,            -- quote only
  budget_range    VARCHAR(100) NULL,            -- quote only
  status          ENUM('new','in_progress','responded','closed') NOT NULL DEFAULT 'new',
  is_read         TINYINT(1)   NOT NULL DEFAULT 0,
  admin_notes     TEXT         NULL,
  ip_address      VARCHAR(45)  NULL,
  user_agent      VARCHAR(255) NULL,
  created_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_inq_status (status),
  KEY idx_inq_read (is_read),
  KEY idx_inq_created (created_at),
  KEY idx_inq_service (service_id),
  CONSTRAINT fk_inq_service FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Settings, social links, SEO
-- ---------------------------------------------------------------------
CREATE TABLE website_settings (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  setting_key   VARCHAR(80)  NOT NULL,
  setting_value TEXT         NULL,
  setting_group VARCHAR(40)  NOT NULL DEFAULT 'general',
  is_public     TINYINT(1)   NOT NULL DEFAULT 1,   -- exposed through public API?
  created_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_setting_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE social_links (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  platform    VARCHAR(30)  NOT NULL,     -- facebook, linkedin, instagram, youtube, x, whatsapp
  label       VARCHAR(60)  NOT NULL,
  url         VARCHAR(255) NULL,
  is_active   TINYINT(1)   NOT NULL DEFAULT 1,
  sort_order  INT          NOT NULL DEFAULT 0,
  created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_social_platform (platform)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE seo_settings (
  id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  page_key         VARCHAR(60)  NOT NULL,
  seo_title        VARCHAR(190) NULL,
  meta_description VARCHAR(320) NULL,
  meta_keywords    VARCHAR(320) NULL,
  canonical_url    VARCHAR(255) NULL,
  og_title         VARCHAR(190) NULL,
  og_description   VARCHAR(320) NULL,
  og_image         VARCHAR(255) NULL,
  robots           VARCHAR(60)  NOT NULL DEFAULT 'index, follow',
  created_at       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_seo_page (page_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Insights (blog) + testimonials
-- ---------------------------------------------------------------------
CREATE TABLE posts (
  id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title            VARCHAR(200) NOT NULL,
  slug             VARCHAR(190) NOT NULL,
  excerpt          VARCHAR(500) NULL,
  content          MEDIUMTEXT   NULL,          -- sanitised HTML
  image            VARCHAR(255) NULL,
  image_alt        VARCHAR(255) NULL,
  author_name      VARCHAR(120) NULL,
  status           ENUM('published','draft') NOT NULL DEFAULT 'draft',
  published_at     DATETIME     NULL,
  seo_title        VARCHAR(190) NULL,
  meta_description VARCHAR(320) NULL,
  meta_keywords    VARCHAR(320) NULL,
  created_at       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_posts_slug (slug),
  KEY idx_posts_status_date (status, published_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE testimonials (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  client_name  VARCHAR(120) NOT NULL,
  client_role  VARCHAR(160) NULL,
  company      VARCHAR(160) NULL,
  content      TEXT         NOT NULL,
  rating       TINYINT UNSIGNED NOT NULL DEFAULT 5,
  status       ENUM('published','draft') NOT NULL DEFAULT 'draft',
  sort_order   INT          NOT NULL DEFAULT 0,
  created_at   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_testimonials_status (status, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Rate limiting (public forms + login)
-- ---------------------------------------------------------------------
CREATE TABLE rate_limits (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  rl_key       CHAR(64)     NOT NULL,           -- sha256(action|ip)
  hits         INT UNSIGNED NOT NULL DEFAULT 0,
  window_start DATETIME     NOT NULL,
  created_at   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_rl_key (rl_key),
  KEY idx_rl_window (window_start)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
--  SEED DATA
-- =====================================================================

INSERT INTO admins (name, email, password_hash, role, must_change_password) VALUES
('Site Administrator', 'admin@example.com', '$2y$10$rh885WAUeE4CerHRagk9v.vlH5WliiZCOspiEbKnOI/BWpEN9NNmW', 'super_admin', 1);

-- Company details below come from the client's brief (QES_Complete_Website_With_Pictures-1.html).
-- Email confirmed by the client: info@enviroengineering.pk
INSERT INTO website_settings (setting_key, setting_value, setting_group, is_public) VALUES
('company_name',      'Quality Enviro & Engineering Services', 'general', 1),
('company_short',     'QES', 'general', 1),
('tagline',           'Quality • Environment • Safety', 'general', 1),
('logo',              'brand/logo.jpg', 'general', 1),
('favicon',           'brand/logo.jpg', 'general', 1),
('company_email',     'info@enviroengineering.pk', 'contact', 1),
('notify_email',      'info@enviroengineering.pk', 'contact', 0),
('phone',             '0311-3809087', 'contact', 1),
('phone_secondary',   '0318-2820149', 'contact', 1),
('whatsapp',          '+923113809087', 'contact', 1),
('address',           'Flat No. 4, Block 35, PHA, Near Radio Colony, Landhi Industrial Area, District Malir, Karachi, Pakistan', 'contact', 1),
('service_area',      'Karachi and projects across Pakistan', 'contact', 1),
('business_hours',    '', 'contact', 1),
('map_embed_url',     'https://maps.google.com/maps?q=Landhi%20Industrial%20Area%2C%20Karachi%2C%20Pakistan&z=14&output=embed', 'contact', 1),
('site_url',          'http://localhost:8000', 'general', 1),
('timezone',          'Asia/Karachi', 'general', 0),
('copyright_text',    'Quality Enviro & Engineering Services. All rights reserved.', 'general', 1),
('show_insights',     '1', 'features', 1),
('show_testimonials', '0', 'features', 1),
('show_whatsapp',     '1', 'features', 1),
('send_confirmation', '1', 'email', 0),
('smtp_host',         '', 'email', 0),
('smtp_port',         '587', 'email', 0),
('smtp_secure',       'tls', 'email', 0),
('smtp_user',         '', 'email', 0),
('smtp_pass',         '', 'email', 0),
('smtp_from_email',   'info@enviroengineering.pk', 'email', 0),
('smtp_from_name',    'Quality Enviro & Engineering Services', 'email', 0),
('ga_measurement_id', '', 'analytics', 1),
('gsc_verification',  '', 'analytics', 0);

INSERT INTO social_links (platform, label, url, is_active, sort_order) VALUES
('facebook',  'Facebook',  '', 1, 1),
('linkedin',  'LinkedIn',  '', 1, 2),
('instagram', 'Instagram', '', 1, 3),
('youtube',   'YouTube',   '', 1, 4),
('x',         'X (Twitter)', '', 1, 5),
('whatsapp',  'WhatsApp',  'https://wa.me/923113809087', 1, 6);

-- Services (from the client brief) -------------------------------------
INSERT INTO services (title, slug, short_description, full_description, benefits, key_features, image, image_alt, icon, page_type, status, is_featured, sort_order, seo_title, meta_description, meta_keywords) VALUES
('Environmental Consultancy & Audits', 'environmental-consultancy-audits',
 'EIA, IEE, environmental audits, EMPs, regulatory compliance, environmental monitoring plans and waste management consultancy.',
 '<p>QES provides environmental consultancy and audit services that help industrial, commercial and infrastructure clients understand their environmental obligations and manage them in a practical way.</p><p>We support projects from early assessment through to ongoing compliance: preparing Environmental Impact Assessments (EIA) and Initial Environmental Examinations (IEE), conducting environmental audits, and developing Environmental Management Plans (EMPs) and environmental monitoring plans.</p><h3>How we work</h3><p>Each assignment is scoped to the project, the site and the applicable regulatory requirements. We review existing information, visit the site and deliver clear documentation with practical recommendations.</p>',
 '["Clear understanding of your environmental obligations","Documentation prepared to support regulatory review","Practical, site-specific recommendations","Ongoing support for compliance and monitoring"]',
 '["Environmental Impact Assessment (EIA)","Initial Environmental Examination (IEE)","Environmental audits","Environmental Management Plans (EMPs)","Regulatory compliance support","Environmental monitoring plans","Waste management consultancy"]',
 'photos/industrial-site.jpg', 'Industrial facility with process stacks, the type of site covered by environmental audits', 'Leaf', 'detail', 'published', 1, 1,
 'Environmental Consultancy & Audits in Karachi | EIA, IEE & EMP | QES',
 'Environmental consultancy and audits in Karachi and across Pakistan: EIA, IEE, environmental audits, EMPs, compliance support, monitoring plans and waste management consultancy.',
 'environmental consultancy Karachi, EIA consultant Pakistan, IEE report, environmental audit, environmental management plan'),

('Engineering Inspection & NDT', 'engineering-inspection-ndt',
 'Lifting equipment, boilers, pressure vessels, cranes, hoists, tanks, pipelines, third-party inspection and non-destructive testing.',
 '<p>Regular, independent inspection helps keep industrial equipment safe, reliable and fit for service. QES provides engineering inspection and non-destructive testing (NDT) for a wide range of plant and equipment.</p><p>Our inspection services cover lifting equipment, cranes and hoists, boilers and pressure vessels, storage tanks and pipelines, along with third-party inspection support for clients and projects.</p><h3>Inspection reporting</h3><p>Findings are recorded in clear inspection reports that describe the condition of the equipment, any defects observed and the recommended actions.</p>',
 '["Safer operation of critical equipment","Early detection of defects and deterioration","Independent, documented inspection records","Better-informed maintenance decisions"]',
 '["Lifting equipment inspection","Cranes and hoists","Boilers and pressure vessels","Storage tanks","Pipelines","Third-party inspection","Non-destructive testing (NDT)"]',
 'photos/engineering-workshop.jpg', 'Engineers inspecting industrial machinery', 'Cog', 'detail', 'published', 1, 2,
 'Engineering Inspection & NDT Services in Karachi | QES',
 'Engineering inspection and NDT in Karachi: lifting equipment, cranes, hoists, boilers, pressure vessels, tanks, pipelines and third-party inspection.',
 'engineering inspection Karachi, NDT services Pakistan, lifting equipment inspection, pressure vessel inspection, third-party inspection'),

('Calibration Services', 'calibration-services',
 'Industrial, medical and laboratory instrument calibration across pressure, temperature, electrical, mass, dimensional, flow and other disciplines.',
 '<p>Accurate measurement underpins product quality, process control and safety. QES provides calibration services for industrial, medical and laboratory instruments across a broad range of measurement disciplines.</p><p>Our calibration catalogue covers pressure and temperature, mass and force, electrical and electronic, flow and volume, dimensional and torque instruments, as well as medical, laboratory, gas detection and environmental monitoring equipment.</p><p>Specific ranges, methods and service availability are confirmed against each instrument and job requirement. Share your instrument list and we will confirm what we can calibrate.</p>',
 '["Confidence in your measurement results","Support for quality and maintenance programmes","Documented calibration records","One provider for many instrument types"]',
 '["Pressure & vacuum instruments","Temperature instruments","Mass, weights & balances","Force & load measurement","Electrical & electronic meters","Flow & volume instruments","Dimensional instruments","Torque & mechanical tools","Medical & biomedical devices","Laboratory instruments","Gas detection instruments","Environmental monitoring meters","Sound level & lux meters","pH & conductivity meters","Scales & weighing systems","Hydraulic test equipment"]',
 'photos/instrument-calibration.jpg', 'Technician working on an electronic instrument under a magnifier', 'Gauge', 'detail', 'published', 1, 3,
 'Calibration Services in Karachi | Industrial, Medical & Lab | QES',
 'Industrial, medical and laboratory instrument calibration in Karachi: pressure, temperature, mass, force, electrical, flow, dimensional, torque and more.',
 'calibration services Karachi, instrument calibration Pakistan, pressure gauge calibration, medical equipment calibration, laboratory calibration'),

('Water & Wastewater Treatment', 'water-wastewater-treatment',
 'Water treatment plant design, installation and commissioning, RO systems, boiler water treatment, ETP and STP solutions.',
 '<p>From plant planning to installation and commissioning, QES offers water and wastewater treatment support for industrial and commercial applications.</p><p>We work on RO and filtration plants, water softening and demineralisation (DM) plants, boiler feed-water treatment, effluent treatment plants (ETP) and sewage treatment plants (STP), including treated-water reuse, chemical dosing systems and ongoing operation and maintenance.</p>',
 '["Treatment matched to your water quality and use","Support from design through to commissioning","Protection of boilers and process equipment from scale and corrosion","Options for treated-water reuse"]',
 '["Water treatment plant design, installation & commissioning","RO & filtration plants","Water softening & DM plants","Boiler feed-water treatment","Scale & corrosion control","ETP design & commissioning","STP & treated-water reuse","Chemical dosing systems","Operation & maintenance"]',
 'photos/wind-turbines.jpg', 'Wind turbines on green hills', 'Droplets', 'detail', 'published', 1, 4,
 'Water & Wastewater Treatment, ETP & STP in Karachi | QES',
 'Water and wastewater treatment in Karachi: plant design, installation and commissioning, RO, softening and DM plants, boiler water, ETP and STP solutions.',
 'water treatment plant Karachi, RO plant Pakistan, ETP design, STP, boiler water treatment, DM plant'),

('Fire Protection Systems', 'fire-protection-systems',
 'Fire hydrant and alarm systems, firefighting installations, fire pumps, inspection, maintenance and safety support.',
 '<p>Well-designed and well-maintained fire protection systems are essential for protecting people, property and operations. QES supports clients with the installation, inspection and maintenance of fire protection systems.</p><p>Our work covers fire hydrant systems, fire alarm systems, firefighting installations and fire pumps, together with periodic inspection, maintenance and fire safety support.</p>',
 '["Systems ready to perform when needed","Planned inspection and maintenance","Support for fire safety requirements","One contact for installation and upkeep"]',
 '["Fire hydrant systems","Fire alarm systems","Firefighting installations","Fire pumps","Inspection & maintenance","Fire safety support"]',
 'services/fire-protection-systems.svg', 'Fire protection illustration with flame and fire hydrant', 'Flame', 'detail', 'published', 1, 5,
 'Fire Protection Systems in Karachi | Hydrant, Alarm & Fire Pumps | QES',
 'Fire protection systems in Karachi: fire hydrant and alarm systems, firefighting installations, fire pumps, inspection, maintenance and safety support.',
 'fire protection systems Karachi, fire hydrant system, fire alarm system Pakistan, fire pump maintenance'),

('Industrial Risk Assessment & HSE', 'industrial-risk-assessment-hse',
 'HIRA, HAZID, fire risk assessment, LPG and LOX risk assessment, process safety and occupational health & safety audits.',
 '<p>Understanding hazards is the first step to controlling them. QES provides industrial risk assessment and health, safety and environment (HSE) services to help organisations identify risks and put effective controls in place.</p><p>Our services include Hazard Identification and Risk Assessment (HIRA), hazard identification (HAZID) studies, fire risk assessment, LPG and liquid oxygen (LOX) risk assessment, process safety, and occupational health and safety audits.</p>',
 '["A clear picture of significant hazards","Prioritised, practical control measures","Safer workplaces for employees and contractors","Documented assessments for management and audits"]',
 '["HIRA (Hazard Identification & Risk Assessment)","HAZID studies","Fire risk assessment","LPG risk assessment","LOX (liquid oxygen) risk assessment","Process safety","Occupational health & safety audits"]',
 'photos/site-safety-ppe.jpg', 'Workers in hard hats and high-visibility vests on an industrial site', 'ShieldAlert', 'detail', 'published', 1, 6,
 'Industrial Risk Assessment & HSE Services in Karachi | QES',
 'Industrial risk assessment and HSE in Karachi: HIRA, HAZID, fire risk assessment, LPG and LOX risk assessment, process safety and OHS audits.',
 'risk assessment Karachi, HIRA, HAZID study, fire risk assessment Pakistan, HSE audit, process safety'),

('Boiler Water Treatment', 'boiler-water-treatment',
 'Boiler feed-water treatment, scale and corrosion control and chemical dosing to help protect boilers and steam systems.',
 NULL, NULL, NULL,
 'services/boiler-water-treatment.svg', 'Boiler water treatment illustration', 'Thermometer', 'contact', 'published', 0, 7,
 NULL, NULL, NULL);

-- Pages ---------------------------------------------------------------
INSERT INTO pages (page_key, title, slug, content, status) VALUES
('home',     'Home', '', NULL, 'published'),
('about',    'About Us', 'about', NULL, 'published'),
('services', 'Our Services', 'services', NULL, 'published'),
('contact',  'Contact Us', 'contact', NULL, 'published'),
('quote',    'Request a Quote', 'request-a-quote', NULL, 'published'),
('insights', 'Insights', 'insights', NULL, 'published'),
('privacy',  'Privacy Policy', 'privacy-policy',
 '<p><strong>[PLACEHOLDER – have this policy reviewed before publishing.]</strong></p><h2>Information we collect</h2><p>When you submit a contact or quotation form we collect the details you provide, such as your name, company, email address, phone number and message.</p><h2>How we use your information</h2><p>We use this information only to respond to your inquiry and to provide the services you request. We do not sell your personal information.</p><h2>Data retention</h2><p>Inquiry records are kept only as long as needed for business and legal purposes.</p><h2>Contact</h2><p>For privacy questions, please contact us using the details on our Contact page.</p>', 'published'),
('terms',    'Terms & Conditions', 'terms-and-conditions',
 '<p><strong>[PLACEHOLDER – have these terms reviewed before publishing.]</strong></p><h2>Use of this website</h2><p>The content of this website is provided for general information about our services. It does not constitute professional advice for any specific situation.</p><h2>Intellectual property</h2><p>All content, logos and graphics on this website belong to Quality Enviro &amp; Engineering Services unless stated otherwise.</p><h2>Limitation of liability</h2><p>We make reasonable efforts to keep information accurate but do not guarantee that it is complete or current.</p>', 'published');

-- Home sections
INSERT INTO page_sections (page_id, section_key, label, eyebrow, title, subtitle, content, image, image_alt, button_text, button_link, button2_text, button2_link, items, sort_order)
SELECT id, 'hero', 'Hero banner', 'Quality • Safety • Sustainability',
 'Engineering a Safer, Cleaner Industrial Future',
 'Environmental • Engineering • Inspection • Calibration',
 'Integrated environmental consultancy, compliance audits, engineering inspection, calibration, fire protection and water treatment solutions for industry and infrastructure.',
 'photos/engineering-drawings.jpg', 'Engineer reviewing technical drawings', 'Explore Our Services', '/services', 'Talk to Our Team', '/contact', NULL, 1 FROM pages WHERE page_key='home';

INSERT INTO page_sections (page_id, section_key, label, eyebrow, title, subtitle, content, image, image_alt, button_text, button_link, items, sort_order)
SELECT id, 'about', 'About preview', 'About QES',
 'Professional Services. Practical Industrial Solutions.',
 NULL,
 'Quality Enviro & Engineering Services (QES) supports businesses with environmental consultancy, engineering services and industrial safety solutions. We focus on helping clients manage compliance, improve operational reliability and create safer workplaces.\n\nBased in Landhi Industrial Area, Karachi, we serve clients in Karachi and on projects across Pakistan.',
 'photos/laboratory.jpg', 'Laboratory glassware used for sample testing', 'Discuss Your Requirement', '/contact',
 '[{"title":"Our Mission","text":"To help clients manage compliance, improve operational reliability and create safer workplaces.","icon":"Target"},{"title":"Our Vision","text":"To be your partner for industry, environment and safety.","icon":"Eye"}]', 2
FROM pages WHERE page_key='home';

INSERT INTO page_sections (page_id, section_key, label, eyebrow, title, subtitle, items, sort_order)
SELECT id, 'services', 'Services intro', 'What We Do', 'Our Core Services',
 'Integrated services designed for industrial facilities, commercial organizations and infrastructure projects.', NULL, 3
FROM pages WHERE page_key='home';

INSERT INTO page_sections (page_id, section_key, label, eyebrow, title, subtitle, content, image, image_alt, button_text, button_link, items, sort_order)
SELECT id, 'spotlight', 'Water & process solutions', 'Water & Process Solutions',
 'Water Treatment, ETP & Boiler Water Solutions', NULL,
 'From plant planning to installation and commissioning, QES offers water and wastewater treatment support for industrial and commercial applications.',
 'photos/instrument-calibration.jpg', 'Technician working on precision equipment', 'Explore Treatment Solutions', '/services/water-wastewater-treatment',
 '[{"title":"RO & filtration plants","text":"","icon":""},{"title":"Water softening & DM plants","text":"","icon":""},{"title":"Boiler feed-water treatment","text":"","icon":""},{"title":"Scale & corrosion control","text":"","icon":""},{"title":"ETP design & commissioning","text":"","icon":""},{"title":"STP & treated-water reuse","text":"","icon":""},{"title":"Chemical dosing systems","text":"","icon":""},{"title":"Operation & maintenance","text":"","icon":""}]', 4
FROM pages WHERE page_key='home';

INSERT INTO page_sections (page_id, section_key, label, eyebrow, title, subtitle, button_text, button_link, items, sort_order)
SELECT id, 'calibration', 'Calibration capability areas', 'Calibration Capability Areas',
 'Industrial, Medical & Laboratory Calibration',
 'A broad calibration service catalogue covering multiple measurement disciplines. Specific ranges, methods and service availability are confirmed against each instrument and job requirement.',
 'View Calibration Services', '/services/calibration-services',
 '[{"title":"Pressure & vacuum instruments","text":"","icon":""},{"title":"Temperature instruments","text":"","icon":""},{"title":"Mass, weights & balances","text":"","icon":""},{"title":"Force & load measurement","text":"","icon":""},{"title":"Electrical & electronic meters","text":"","icon":""},{"title":"Flow & volume instruments","text":"","icon":""},{"title":"Dimensional instruments","text":"","icon":""},{"title":"Torque & mechanical tools","text":"","icon":""},{"title":"Medical & biomedical devices","text":"","icon":""},{"title":"Laboratory instruments","text":"","icon":""},{"title":"Gas detection instruments","text":"","icon":""},{"title":"Environmental monitoring meters","text":"","icon":""},{"title":"Sound level & lux meters","text":"","icon":""},{"title":"pH & conductivity meters","text":"","icon":""},{"title":"Scales & weighing systems","text":"","icon":""},{"title":"Hydraulic test equipment","text":"","icon":""}]', 5
FROM pages WHERE page_key='home';

INSERT INTO page_sections (page_id, section_key, label, eyebrow, title, subtitle, items, sort_order)
SELECT id, 'why', 'Why choose us', 'Why Choose QES', 'Environmental, Engineering & Safety Expertise',
 'One team for environmental compliance, equipment reliability and workplace safety.',
 '[{"title":"Environmental Expertise","text":"Consultancy, audits and compliance support, from EIA and IEE to monitoring plans.","icon":"Leaf"},{"title":"Engineering Services","text":"Inspection, NDT, calibration and technical support for plant and equipment.","icon":"Cog"},{"title":"Safety-Focused Approach","text":"Risk assessment, process safety and fire protection built into our work.","icon":"ShieldCheck"},{"title":"Water & Process Solutions","text":"Treatment plant design, installation, commissioning and maintenance.","icon":"Droplets"},{"title":"Integrated Service","text":"Environmental, engineering and safety requirements handled by one partner.","icon":"Handshake"},{"title":"Karachi-Based Team","text":"Serving Karachi and projects across Pakistan.","icon":"Building2"}]', 6
FROM pages WHERE page_key='home';

INSERT INTO page_sections (page_id, section_key, label, eyebrow, title, subtitle, items, sort_order)
SELECT id, 'process', 'Work process', 'How We Work', 'Our Work Process',
 'A clear, structured approach from first conversation to final delivery.',
 '[{"title":"Consultation","text":"We listen to your requirements, objectives and constraints.","icon":"MessagesSquare"},{"title":"Site Assessment","text":"We review the site, existing data and applicable requirements.","icon":"Search"},{"title":"Planning","text":"We define scope, methodology, schedule and deliverables.","icon":"ClipboardList"},{"title":"Implementation","text":"We carry out the work safely and keep you informed.","icon":"Wrench"},{"title":"Quality Assurance","text":"We review results and documentation before final delivery.","icon":"BadgeCheck"}]', 7
FROM pages WHERE page_key='home';

INSERT INTO page_sections (page_id, section_key, label, eyebrow, title, subtitle, items, sort_order)
SELECT id, 'credentials', 'Professional credentials', 'Professional Credentials', 'Registered & Professionally Engaged',
 'Our professional credentials and consultancy role support our commitment to responsible engineering and environmental services.',
 '[{"title":"Pakistan Engineering Council (PEC)","text":"Company registration","icon":"Award"},{"title":"Environmental Consultant","text":"Environmental consultancy engagement","icon":"Leaf"}]', 8
FROM pages WHERE page_key='home';

INSERT INTO page_sections (page_id, section_key, label, title, subtitle, button_text, button_link, button2_text, button2_link, sort_order)
SELECT id, 'cta', 'Call to action',
 'Need Environmental, Inspection or Calibration Support?',
 'Contact QES for service details, site requirements or a quotation.',
 'Request a Quote', '/request-a-quote', 'Contact Us', '/contact', 9
FROM pages WHERE page_key='home';

INSERT INTO page_sections (page_id, section_key, label, eyebrow, title, subtitle, sort_order)
SELECT id, 'insights', 'Latest insights intro', 'Insights', 'Latest Updates & Insights',
 'News, practical guidance and updates from our team.', 10
FROM pages WHERE page_key='home';

INSERT INTO page_sections (page_id, section_key, label, eyebrow, title, subtitle, sort_order)
SELECT id, 'testimonials', 'Testimonials intro', 'Testimonials', 'What Our Clients Say', NULL, 11
FROM pages WHERE page_key='home';

INSERT INTO page_sections (page_id, section_key, label, eyebrow, title, subtitle, sort_order)
SELECT id, 'contact', 'Contact preview', 'Get In Touch', 'Let''s Discuss Your Project',
 'Contact QES for service details, site requirements or a quotation.', 12
FROM pages WHERE page_key='home';

-- About sections
INSERT INTO page_sections (page_id, section_key, label, eyebrow, title, subtitle, content, image, image_alt, sort_order)
SELECT id, 'intro', 'Page header', 'About Us', 'About Quality Enviro & Engineering Services',
 'Your partner for industry, environment and safety.', NULL, NULL, NULL, 1 FROM pages WHERE page_key='about';

INSERT INTO page_sections (page_id, section_key, label, eyebrow, title, content, image, image_alt, sort_order)
SELECT id, 'background', 'Company introduction & background', 'Who We Are', 'Professional Services. Practical Industrial Solutions.',
 'Quality Enviro & Engineering Services (QES) supports businesses with environmental consultancy, engineering services and industrial safety solutions. We focus on helping clients manage compliance, improve operational reliability and create safer workplaces.\n\nOur services cover environmental consultancy and audits, engineering inspection and NDT, calibration of industrial, medical and laboratory instruments, water and wastewater treatment, fire protection systems and industrial risk assessment.\n\nWe are based in Landhi Industrial Area, Karachi, and support clients in Karachi and on projects across Pakistan.',
 'photos/wind-turbines.jpg', 'Wind turbines on green hills', 2 FROM pages WHERE page_key='about';

INSERT INTO page_sections (page_id, section_key, label, eyebrow, title, items, sort_order)
SELECT id, 'mission_vision', 'Mission & vision', 'Purpose', 'Mission & Vision',
 '[{"title":"Our Mission","text":"To help clients manage compliance, improve operational reliability and create safer workplaces through practical environmental, engineering and safety services.","icon":"Target"},{"title":"Our Vision","text":"To be a trusted partner for industry, environment and safety, delivering quality in every assignment.","icon":"Eye"}]', 3 FROM pages WHERE page_key='about';

INSERT INTO page_sections (page_id, section_key, label, eyebrow, title, items, sort_order)
SELECT id, 'values', 'Core values', 'What Guides Us', 'Quality • Environment • Safety',
 '[{"title":"Quality","text":"Careful work that meets the agreed standard.","icon":"BadgeCheck"},{"title":"Environment","text":"Solutions that consider long-term environmental impact.","icon":"Leaf"},{"title":"Safety","text":"Protecting people, property and operations comes first.","icon":"ShieldCheck"},{"title":"Integrity","text":"Honest advice and transparent reporting.","icon":"Scale"},{"title":"Collaboration","text":"Working closely with clients and site teams.","icon":"Users"}]', 4 FROM pages WHERE page_key='about';

INSERT INTO page_sections (page_id, section_key, label, eyebrow, title, subtitle, items, sort_order)
SELECT id, 'expertise', 'Our expertise', 'Expertise', 'Our Areas of Expertise', NULL,
 '[{"title":"Environmental Consultancy & Audits","text":"EIA, IEE, audits, EMPs and compliance support.","icon":"Leaf"},{"title":"Engineering Inspection & NDT","text":"Lifting equipment, boilers, pressure vessels, tanks and pipelines.","icon":"Cog"},{"title":"Calibration","text":"Industrial, medical and laboratory instruments.","icon":"Gauge"},{"title":"Water & Wastewater Treatment","text":"RO, DM, boiler water, ETP and STP solutions.","icon":"Droplets"},{"title":"Fire Protection","text":"Hydrant, alarm and firefighting systems and fire pumps.","icon":"Flame"},{"title":"Risk Assessment & HSE","text":"HIRA, HAZID, process safety and OHS audits.","icon":"ShieldAlert"}]', 5 FROM pages WHERE page_key='about';

INSERT INTO page_sections (page_id, section_key, label, eyebrow, title, content, sort_order)
SELECT id, 'quality', 'Quality commitment', 'Quality Commitment', 'Our Commitment to Quality',
 'Quality is part of our name and central to how we work. We plan each assignment carefully, use appropriate methods and review our deliverables before they reach the client.\n\nWe aim to provide information our clients can rely on and recommendations they can act on.', 6 FROM pages WHERE page_key='about';

INSERT INTO page_sections (page_id, section_key, label, eyebrow, title, content, sort_order)
SELECT id, 'approach', 'Professional approach', 'Our Approach', 'A Professional, Structured Approach',
 'Every project starts with understanding the client''s objectives and site conditions. We then define a clear scope, carry out the work safely and keep communication open throughout.\n\nOur reports are written to be clear and practical, so decision-makers can understand the findings and next steps.', 7 FROM pages WHERE page_key='about';

INSERT INTO page_sections (page_id, section_key, label, eyebrow, title, items, sort_order)
SELECT id, 'trust', 'Credentials', 'Professional Credentials', 'Registered & Professionally Engaged',
 '[{"title":"Pakistan Engineering Council (PEC)","text":"Company registration.","icon":"Award"},{"title":"Environmental Consultant","text":"Environmental consultancy engagement.","icon":"Leaf"},{"title":"Safety-Focused","text":"Risk assessment and fire protection expertise.","icon":"ShieldCheck"},{"title":"Service Area","text":"Karachi and projects across Pakistan.","icon":"Globe"}]', 8 FROM pages WHERE page_key='about';

-- Contact / services / quote / insights headers
INSERT INTO page_sections (page_id, section_key, label, eyebrow, title, subtitle, sort_order)
SELECT id, 'intro', 'Page header', 'Get In Touch', 'Let''s Discuss Your Project',
 'Contact QES for service details, site requirements or a quotation.', 1 FROM pages WHERE page_key='contact';
INSERT INTO page_sections (page_id, section_key, label, eyebrow, title, subtitle, sort_order)
SELECT id, 'intro', 'Page header', 'Our Services', 'Environmental, Engineering & Safety Services',
 'Environmental consultancy, engineering inspection, calibration, fire protection, water treatment and industrial risk assessment for industry and infrastructure.', 1 FROM pages WHERE page_key='services';
INSERT INTO page_sections (page_id, section_key, label, eyebrow, title, subtitle, sort_order)
SELECT id, 'intro', 'Page header', 'Request a Quote', 'Request a Quotation',
 'Share a few details about your project and site requirements, and we will prepare a quotation.', 1 FROM pages WHERE page_key='quote';
INSERT INTO page_sections (page_id, section_key, label, eyebrow, title, subtitle, sort_order)
SELECT id, 'intro', 'Page header', 'Knowledge Center', 'Insights & Updates',
 'Practical guidance on environmental compliance, inspection, calibration and industrial safety.', 1 FROM pages WHERE page_key='insights';

-- Footer (stored under home page as a global section)
INSERT INTO page_sections (page_id, section_key, label, content, sort_order)
SELECT id, 'footer', 'Footer description',
 'Your partner in environmental consultancy, engineering inspection, calibration, industrial safety and water treatment solutions.', 99
FROM pages WHERE page_key='home';

-- SEO ------------------------------------------------------------------
INSERT INTO seo_settings (page_key, seo_title, meta_description, meta_keywords, og_title, og_description, robots) VALUES
('home', 'Quality Enviro & Engineering Services | Environmental & Engineering Services in Karachi',
 'Environmental consultancy, environmental audits, engineering inspection, calibration, fire protection, water treatment and industrial risk assessment in Karachi and across Pakistan.',
 'environmental consultancy Karachi, environmental audit Pakistan, engineering inspection, NDT services, calibration services Karachi, water treatment plant, fire protection systems, risk assessment',
 'Quality Enviro & Engineering Services (QES)', 'Engineering a safer, cleaner industrial future: environmental, engineering, inspection and calibration services in Karachi.', 'index, follow'),
('about', 'About QES | Quality Enviro & Engineering Services, Karachi',
 'Quality Enviro & Engineering Services (QES) supports businesses in Karachi and across Pakistan with environmental consultancy, engineering services and industrial safety solutions.',
 'environmental consultants Karachi, engineering services company Pakistan, PEC registered company', NULL, NULL, 'index, follow'),
('services', 'Our Services | Environmental, Inspection, Calibration & HSE | QES',
 'Environmental consultancy and audits, engineering inspection and NDT, calibration, water and wastewater treatment, fire protection systems and industrial risk assessment.',
 'environmental consultancy services, engineering inspection services, calibration services, water treatment, fire protection, HSE risk assessment', NULL, NULL, 'index, follow'),
('contact', 'Contact QES | Landhi Industrial Area, Karachi',
 'Contact Quality Enviro & Engineering Services in Landhi Industrial Area, Karachi. Call 0311-3809087 or 0318-2820149, or send us an inquiry online.',
 'contact environmental consultants Karachi, engineering inspection inquiry', NULL, NULL, 'index, follow'),
('quote', 'Request a Quote | Quality Enviro & Engineering Services',
 'Request a quotation for environmental consultancy, inspection, calibration, water treatment, fire protection or risk assessment services from QES, Karachi.',
 'environmental services quote, calibration quote Karachi, inspection quotation', NULL, NULL, 'index, follow'),
('insights', 'Insights & Updates | Quality Enviro & Engineering Services',
 'Articles and practical guidance on environmental compliance, inspection, calibration and industrial safety from Quality Enviro & Engineering Services.',
 'environmental compliance guidance, industrial safety articles', NULL, NULL, 'index, follow'),
('privacy', 'Privacy Policy | Quality Enviro & Engineering Services', 'Privacy policy for Quality Enviro & Engineering Services.', NULL, NULL, NULL, 'noindex, follow'),
('terms', 'Terms & Conditions | Quality Enviro & Engineering Services', 'Terms and conditions for using the Quality Enviro & Engineering Services website.', NULL, NULL, NULL, 'noindex, follow');

-- Insights (demo articles – general guidance, edit or replace) ---------
INSERT INTO posts (title, slug, excerpt, content, image, image_alt, author_name, status, published_at, seo_title, meta_description) VALUES
('Why Environmental Monitoring Matters for Industrial Sites', 'why-environmental-monitoring-matters',
 'Regular monitoring gives site managers the data they need to spot problems early, demonstrate compliance and improve performance.',
 '<p>Environmental monitoring is more than a regulatory checkbox. A well-planned programme gives site managers reliable data about emissions, discharges and ambient conditions.</p><h2>Spot issues early</h2><p>Trends in monitoring data often reveal developing problems before they become serious incidents.</p><h2>Support compliance</h2><p>Consistent records make periodic reporting simpler and more defensible.</p><h2>Improve performance</h2><p>Data helps prioritise investment in controls and process improvements where they make the most difference.</p>',
 'photos/laboratory.jpg', 'Laboratory glassware used for sample testing', 'QES Team', 'published', UTC_TIMESTAMP(),
 NULL, 'How a well-planned environmental monitoring programme helps industrial sites spot issues early, support compliance and improve performance.'),
('5 Steps to Prepare for an Environmental Audit', 'prepare-for-an-environmental-audit',
 'A little preparation makes an environmental audit smoother and more useful. Here are five practical steps.',
 '<p>Environmental audits are most valuable when the site is well prepared.</p><ol><li><strong>Gather your records</strong>: permits, monitoring data and training logs.</li><li><strong>Review previous findings</strong> and the status of corrective actions.</li><li><strong>Walk the site</strong> to check housekeeping and controls.</li><li><strong>Brief key staff</strong> on the scope and schedule.</li><li><strong>Plan follow-up</strong> so findings turn into improvements.</li></ol>',
 'photos/industrial-site.jpg', 'Industrial facility with process stacks', 'QES Team', 'published', UTC_TIMESTAMP() - INTERVAL 3 DAY,
 NULL, 'Five practical steps to prepare your site for an environmental audit and get more value from the findings.'),
('Why Regular Calibration Matters', 'why-regular-calibration-matters',
 'Instruments drift over time. Regular calibration keeps measurements trustworthy and supports quality and safety.',
 '<p>Every measuring instrument, from a pressure gauge to a laboratory balance, can drift over time through wear, environmental conditions or handling.</p><h2>Trustworthy measurements</h2><p>Calibration compares an instrument against a reference so you know how accurate its readings are.</p><h2>Quality and safety</h2><p>Accurate readings support product quality, process control and safe operation of equipment such as boilers and pressure systems.</p><h2>Plan a schedule</h2><p>Keep an instrument register, set calibration intervals based on use and criticality, and keep certificates on file for audits.</p>',
 'photos/instrument-calibration.jpg', 'Technician working on an electronic instrument', 'QES Team', 'published', UTC_TIMESTAMP() - INTERVAL 7 DAY,
 NULL, 'Why instruments drift, how regular calibration supports quality and safety, and how to plan a calibration schedule.');

-- Testimonials (demo – drafts; publish only genuine client feedback) ---
INSERT INTO testimonials (client_name, client_role, company, content, rating, status, sort_order) VALUES
('[PLACEHOLDER] Client Name', 'Operations Manager', '[Client Company]', 'Replace this with genuine client feedback before publishing.', 5, 'draft', 1),
('[PLACEHOLDER] Client Name', 'Project Director', '[Client Company]', 'Replace this with genuine client feedback before publishing.', 5, 'draft', 2);
