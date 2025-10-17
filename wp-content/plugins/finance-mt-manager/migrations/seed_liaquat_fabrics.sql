-- Clear tables before inserting
TRUNCATE TABLE {{prefix}}invoices;
TRUNCATE TABLE {{prefix}}invoice_items;
TRUNCATE TABLE {{prefix}}cash_ledger;

INSERT INTO {{prefix}}invoices (invoice_number, customer_name, issue_date, due_date, status, subtotal, tax, total, currency, created_at)
VALUES
('INV-LF-1001', 'City Boutique', '2024-01-05', '2024-01-20', 'paid', 12000.00, 720.00, 12720.00, 'PKR', NOW()),
('INV-LF-1002', 'Fashion Lane', '2024-02-14', '2024-02-28', 'sent', 9500.00, 570.00, 10070.00, 'PKR', NOW());

INSERT INTO {{prefix}}invoice_items (invoice_id, item_name, quantity, unit_price, total)
VALUES
(1, 'Cotton Roll A', 20, 300.00, 6000.00),
(1, 'Dye Pack', 10, 600.00, 6000.00),
(2, 'Silk Bundle', 15, 400.00, 6000.00),
(2, 'Thread Set', 25, 140.00, 3500.00);

INSERT INTO {{prefix}}cash_ledger (entry_date, reference, type, amount, description, created_at)
VALUES
('2024-01-21', 'RCPT-1001', 'receipt', 12720.00, 'Invoice INV-LF-1001 paid via bank', NOW()),
('2024-02-18', 'PAY-2001', 'payment', 4200.00, 'Supplier payment for dyes', NOW()),
('2024-02-20', 'TRF-3001', 'transfer', 3000.00, 'Cash to bank transfer', NOW());
