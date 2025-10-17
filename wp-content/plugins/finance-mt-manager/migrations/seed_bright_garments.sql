-- Seed data for Bright Garments tenant
-- Clear tables before inserting
TRUNCATE TABLE fmtm_invoices;
TRUNCATE TABLE fmtm_invoice_items;
TRUNCATE TABLE fmtm_cash_ledger;

INSERT INTO fmtm_invoices (invoice_number, customer_name, issue_date, due_date, status, subtotal, tax, total, currency, created_at)
VALUES
('INV-BG-3001', 'Galaxy Outfits', '2024-02-01', '2024-02-16', 'paid', 9100.00, 546.00, 9646.00, 'PKR', NOW()),
('INV-BG-3002', 'Velvet Vogue', '2024-04-05', '2024-04-19', 'sent', 6800.00, 408.00, 7208.00, 'PKR', NOW());

INSERT INTO fmtm_invoice_items (invoice_id, item_name, quantity, unit_price, total)
VALUES
(1, 'Printed Lawn', 18, 350.00, 6300.00),
(1, 'Zari Lace', 40, 70.00, 2800.00),
(2, 'Premium Silk', 12, 400.00, 4800.00),
(2, 'Accessories Kit', 20, 100.00, 2000.00);

INSERT INTO fmtm_cash_ledger (entry_date, reference, type, amount, description, created_at)
VALUES
('2024-02-18', 'RCPT-3101', 'receipt', 9646.00, 'Payment from Galaxy Outfits', NOW()),
('2024-03-02', 'PAY-3102', 'payment', 1800.00, 'Rent payment', NOW()),
('2024-04-21', 'TRF-3103', 'transfer', 2200.00, 'Bank to cash transfer', NOW());
