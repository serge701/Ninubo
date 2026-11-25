-- Catalog from "Ninubo 360 — Propuesta" (Nov 2026): Starter / Business / Premium packages, add-on services
-- and monthly Website Care plans. Adds price types (fixed, "from", range), an "Add-on" category and an internal
-- production cost (partner price, admin-only) per item.
-- The UPDATEs only touch the untouched starter rows from migration 04 (same name, price 0.00).

ALTER TABLE packages
  MODIFY category ENUM('website','addon','hosting','maintenance','software','data','marketing','training','other') NOT NULL DEFAULT 'website',
  ADD COLUMN price_type ENUM('fixed','from','range') NOT NULL DEFAULT 'fixed' AFTER price,
  ADD COLUMN price_max DECIMAL(10,2) NULL AFTER price_type,
  ADD COLUMN internal_cost DECIMAL(10,2) NULL AFTER price_max;

UPDATE packages SET
  name = 'Starter Website (Landing Page)', price = 697.00, brief_profile = 'landing', sort_order = 10,
  description = 'Professional one-page website for businesses that need a professional online presence. Custom design, fully responsive for desktop, tablet and mobile. Up to 5 sections, contact form, social media integration, Google Maps, WhatsApp button, domain and hosting setup, SSL/HTTPS, basic SEO and initial Google Analytics setup. Includes 1 round of revisions and site launch. Estimated delivery: 5–7 business days after all materials are received.'
WHERE id = 1 AND name = 'Landing Page' AND price = 0.00;

UPDATE packages SET
  name = 'Business Website', price = 1397.00, brief_profile = 'business', sort_order = 20,
  description = 'Our most popular package. Multi-page website with up to 7 pages and a fully custom, responsive design: Home, About Us, Services, Gallery or Portfolio and Contact. Contact form, Google Maps, social media integration, WhatsApp, domain and hosting setup, SSL/HTTPS, basic SEO, bilingual English/Spanish version, Google Analytics setup, basic Google Business Profile integration and basic speed optimization. Includes up to 2 rounds of revisions and site launch. Estimated delivery: 5–7 business days after all materials are received.'
WHERE id = 2 AND name = 'Business Website' AND price = 0.00;

UPDATE packages SET
  name = 'Online Store (E-commerce)', price = 2500.00, price_type = 'from', brief_profile = 'ecommerce', sort_order = 40,
  description = 'E-commerce website with product catalog, shopping cart, online payments and order notifications. Final price depends on the number of products and features and is quoted per project.'
WHERE id = 3 AND name = 'Online Store' AND price = 0.00;

UPDATE packages SET
  name = 'Website Care', category = 'maintenance', price = 59.00, billing = 'monthly', brief_profile = NULL, sort_order = 100,
  description = 'Monthly hosting and maintenance plan: hosting, SSL certificate, backups, basic monitoring, technical updates, basic support and up to 30 minutes of changes per month.'
WHERE id = 4 AND name = 'Website Hosting' AND price = 0.00;

UPDATE packages SET
  name = 'Website Care Pro', category = 'maintenance', price = 99.00, billing = 'monthly', brief_profile = NULL, sort_order = 110,
  description = 'Everything in Website Care, plus up to 1 hour of changes per month, content updates, basic optimization and priority support.'
WHERE id = 5 AND name = 'Website Maintenance' AND price = 0.00;

INSERT INTO packages (name, category, description, price, price_type, price_max, internal_cost, billing, tax_type, brief_profile, sort_order) VALUES
('Premium Website', 'website', 'For businesses that want the most complete digital presence and to compete strongly on Google. Premium website with up to 12 pages, fully custom responsive design, animations and visual effects, blog/news, gallery or portfolio, advanced forms, basic external integrations, WhatsApp, Google Maps, social media, domain and hosting setup, SSL/HTTPS, technical and on-page SEO, speed optimization, Google Analytics, Google Business Profile integration, basic local SEO setup and bilingual English/Spanish version. Includes up to 3 rounds of revisions, launch and final configuration. Estimated delivery: 10–15 business days after all materials are received.', 2697.00, 'fixed', NULL, NULL, 'one_time', 'data_processing', 'premium', 30),
('Website Growth', 'maintenance', 'Hosting and maintenance with up to 2 hours of changes per month, content updates, ongoing basic SEO, optimization, monthly report and priority support.', 199.00, 'fixed', NULL, NULL, 'monthly', 'data_processing', NULL, 120),
('Additional Page', 'addon', 'One additional page beyond the pages included in the package.', 100.00, 'fixed', NULL, NULL, 'one_time', 'data_processing', NULL, 200),
('Additional Revision Round', 'addon', 'One additional consolidated round of changes beyond the rounds included in the package.', 75.00, 'fixed', NULL, NULL, 'one_time', 'data_processing', NULL, 210),
('Bilingual Translation / Adaptation', 'addon', 'English/Spanish translation and adaptation of the website content.', 200.00, 'range', 400.00, NULL, 'one_time', 'data_processing', NULL, 220),
('Professional Copywriting', 'addon', 'Professional writing of the website content.', 150.00, 'range', 400.00, NULL, 'one_time', 'data_processing', NULL, 230),
('Advanced Local SEO', 'marketing', 'Advanced local SEO setup for the client''s service area.', 300.00, 'range', 600.00, NULL, 'one_time', 'data_processing', NULL, 240),
('Blog / Article System', 'addon', 'Blog or news section with an article management system.', 200.00, 'range', 400.00, NULL, 'one_time', 'data_processing', NULL, 250),
('Special Integration', 'addon', 'Integration with a third-party system or service. Quoted per project.', 150.00, 'from', NULL, NULL, 'one_time', 'data_processing', NULL, 260),
('Additional Landing Page', 'addon', 'An additional landing page (e.g. for a campaign or a specific service).', 300.00, 'range', 500.00, NULL, 'one_time', 'data_processing', 'landing', 270),
('Website Redesign', 'website', 'Redesign of an existing website. Quoted per project.', 700.00, 'from', NULL, NULL, 'one_time', 'data_processing', 'business', 280);
