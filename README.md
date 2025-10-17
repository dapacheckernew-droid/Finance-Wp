# Finance MT Manager

Finance MT Manager is a WordPress plugin that turns a WordPress installation into a multi-tenant financial management workspace for small fabric businesses. The plugin provisions isolated MySQL databases per tenant, manages tenant-bound WordPress users, and delivers invoicing with PDF export plus a cash ledger in both the WP Admin UI and REST API.

## Architecture Summary

- **WordPress Plugin Only**: Delivered as a drop-in plugin that lives under `wp-content/plugins/finance-mt-manager`.
- **PHP 8.1+ / MySQL**: Uses native WordPress `wpdb` APIs to provision per-tenant MySQL databases and operate within them.
- **Per-Tenant Database Isolation**: Each tenant gets its own database (`fmtm_{slug}`) with dedicated tables for accounts, invoices, invoice items, cash ledger, and audit logs.
- **Role-Based Access Control**: Defines custom roles (`fmtm_owner`, `fmtm_accountant`, `fmtm_staff`, `fmtm_viewer`) mapped to WordPress capabilities.
- **REST API + PDF**: Exposes REST endpoints under `/wp-json/fmtm/v1/...` and leverages DOMPDF for PDF exports.
- **Responsive Admin Screens**: Custom admin pages for tenant creation, invoice management, and cash ledger entries with Roman Urdu labels.
- **Auditing & Logging**: Writes key actions to a per-tenant audit log table.

## Milestone Implementation Plan

1. **Bootstrap Plugin**
   - Create plugin folder structure under `wp-content/plugins/finance-mt-manager`.
   - Add `finance-mt-manager.php` to register autoloader, hooks, and text domain.
   - Implement activator to provision control tables and WordPress roles.

2. **Tenant Provisioning**
   - Add `FMTM_Tenant_Manager` with admin-post handler to create tenant DBs and seed schema via `migrations/tenant_schema.sql`.
   - Store tenant metadata in `{prefix}fmtm_tenants` and link owner user accounts via `{prefix}fmtm_user_tenants`.

3. **Admin UI**
   - Register custom admin menus and screens (dashboard, invoices, cash ledger).
   - Implement controllers for invoices and cash ledger to process form submissions, enforce capabilities, and write audit entries.

4. **REST & PDF**
   - Register REST routes for listing/creating invoices and generating PDFs.
   - Implement `FMTM_View_Renderer` and `FMTM_Pdf_Service` (DOMPDF based) plus HTML templates.

5. **Assets & Localization**
   - Add CSS/JS for dynamic invoice line items.
   - Provide Roman Urdu translations inline and prepare textdomain usage.

6. **Testing & Tooling**
   - Ship `composer.json` with DOMPDF dependency.
   - Provide PHPUnit scaffold under `tests/` referencing WordPress test suite.

7. **Deployment Support**
   - Provide Docker Compose example for WordPress + MySQL + phpMyAdmin.
   - Document installation, tenant seeding, and API usage.

## Plugin Structure

```
wp-content/
  plugins/
    finance-mt-manager/
      assets/
      includes/
      migrations/
      tests/
      views/
      composer.json
      finance-mt-manager.php
      phpunit.xml
```

## Installation (Development)

1. **Clone repo into WordPress**
   ```bash
   git clone https://github.com/your-org/finance-wp.git
   cd finance-wp
   cp config/wp-config.sample.php config/wp-config.php
   ```
2. **Install PHP dependencies**
   ```bash
   cd wp-content/plugins/finance-mt-manager
   composer install
   ```
3. **Activate Plugin**
   - Log into WordPress Admin → Plugins → Activate **Finance MT Manager**.
   - After activation, refresh the admin screen (or log out/in once) so the new capabilities load; a **Finance MT** menu will appear in the left sidebar for Administrators and Editors.

4. **Create Tenants**
   - Navigate to **Finance MT → Dashboard**.
   - Fill in company name, optional slug, admin email.
   - Submit to provision a dedicated database and owner credentials.

5. **Assign Users**
   - Edit generated owner account, share generated password (stored in user meta `fmtm_generated_password`).
   - Additional users can be assigned to the tenant by updating their `fmtm_default_tenant` user meta and role.
   - If an existing Administrator or Editor should access a tenant, set their role accordingly (Owner/Accountant/Staff/Viewer) and save to persist the default tenant meta.

## REST API Examples

List invoices for tenant `liaquat-fabrics`:
```bash
curl -u admin:password \
  https://example.com/wp-json/fmtm/v1/tenants/liaquat-fabrics/invoices
```

Create an invoice:
```bash
curl -X POST -u admin:password \
  https://example.com/wp-json/fmtm/v1/tenants/liaquat-fabrics/invoices \
  -H 'Content-Type: application/json' \
  -d '{
    "invoice_number": "INV-2001",
    "customer_name": "Star Garments",
    "issue_date": "2024-04-01",
    "due_date": "2024-04-15",
    "items": [
      {"name": "Cotton Bale", "quantity": 5, "unit_price": 2500}
    ],
    "tax": 500
  }'
```

Download PDF:
```bash
curl -o invoice.pdf \
  https://example.com/wp-json/fmtm/v1/tenants/liaquat-fabrics/invoices/1/pdf
```

## SQL Schema & Seed

- Base schema: `migrations/tenant_schema.sql`
- Generic sample data: `migrations/sample_seed.sql`
- Tenant-specific demo packs:
  - `migrations/seed_liaquat_fabrics.sql`
  - `migrations/seed_noor_textiles.sql`
  - `migrations/seed_bright_garments.sql`

Apply to a tenant database:
```bash
mysql -u root -p fmtm_liaquat_fabrics < migrations/sample_seed.sql
```

## Docker Compose Example

See [`docker-compose.yml`](docker-compose.yml) for a local environment with WordPress, MySQL, and phpMyAdmin.

## Testing

1. Install WordPress PHPUnit test suite following [official instructions](https://make.wordpress.org/core/handbook/testing/automated-testing/phpunit/).
2. Set `WP_TESTS_DIR` to the path of the WordPress test library.
3. Run tests:
   ```bash
   cd wp-content/plugins/finance-mt-manager
   vendor/bin/phpunit
   ```

## Deployment Checklist

1. Provision LEMP stack (PHP 8.1+, MySQL 8) and install WordPress.
2. Upload plugin directory to `wp-content/plugins/` and run `composer install`.
3. Ensure MySQL user has privileges to `CREATE DATABASE`.
4. Activate plugin and create tenants.
5. Configure SSL (Let’s Encrypt/Cloudflare) and enforce HTTPS.
6. Set cron to run WordPress tasks and schedule DB backups per tenant DB (`mysqldump fmtm_{slug}`).
7. Harden WordPress (disable file edits, enforce strong passwords, enable 2FA plugin, set up Fail2ban or Cloudflare rules).

## Operations Runbook (Summary)

- **Tenant Onboarding**: Admin creates tenant in dashboard → share generated credentials → update DNS/subdirectory routing if using subdomains.
- **Invoice Workflow**: Staff create invoices via admin UI; PDFs accessible via REST. Payments recorded via cash ledger entries.
- **Backups**: Nightly `mysqldump` for each tenant DB plus WordPress DB. Store offsite.
- **Restore**: Create target DB → import dump → update tenant mapping table with DB credentials.
- **Monitoring**: Track WordPress uptime, database storage, audit logs.

## Licensing & Pricing Suggestion

- Release plugin under GPLv2 (WordPress requirement).
- Offer hosted SaaS service with support at ~$49/month per tenant; optional premium add-ons licensed separately.

