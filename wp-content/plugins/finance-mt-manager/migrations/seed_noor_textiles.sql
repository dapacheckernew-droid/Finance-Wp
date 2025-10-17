-- Seed data for Noor Textiles tenant
-- Clear tables before inserting
TRUNCATE TABLE {{prefix}}invoices;
TRUNCATE TABLE {{prefix}}invoice_items;
TRUNCATE TABLE {{prefix}}cash_ledger;

INSERT INTO {{prefix}}invoices (invoice_number, customer_name, issue_date, due_date, status, subtotal, tax, total, currency, created_at)
VALUES
('INV-NT-2001', 'Premium Apparel', '2024-01-10', '2024-01-25', 'sent', 7800.00, 468.00, 8268.00, 'PKR', NOW()),
('INV-NT-2002', 'Stitch & Style', '2024-03-03', '2024-03-17', 'draft', 5600.00, 336.00, 5936.00, 'PKR', NOW());

INSERT INTO {{prefix}}invoice_items (invoice_id, item_name, quantity, unit_price, total)
VALUES
(1, 'Dyed Fabric Rolls', 12, 400.00, 4800.00),
(1, 'Embroidery Thread Pack', 10, 300.00, 3000.00),
(2, 'Plain Cotton', 14, 250.00, 3500.00),
(2, 'Buttons Pack', 40, 52.50, 2100.00);

INSERT INTO {{prefix}}cash_ledger (entry_date, reference, type, amount, description, created_at)
VALUES
('2024-01-26', 'PAY-2101', 'payment', 2500.00, 'Advance to supplier', NOW()),
('2024-02-05', 'RCPT-2102', 'receipt', 8268.00, 'Received from Premium Apparel', NOW()),
('2024-03-12', 'TRF-2103', 'transfer', 1500.00, 'Cash to petty cash', NOW());
