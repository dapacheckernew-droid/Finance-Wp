INSERT INTO fmtm_invoices (invoice_number, customer_name, issue_date, due_date, status, subtotal, tax, total, currency, created_at)
VALUES
('INV-1001', 'Liaquat Fabrics', '2024-01-05', '2024-01-20', 'paid', 12000.00, 720.00, 12720.00, 'PKR', NOW()),
('INV-1002', 'Noor Textiles', '2024-02-10', '2024-02-25', 'sent', 8500.00, 510.00, 9010.00, 'PKR', NOW()),
('INV-1003', 'Bright Garments', '2024-03-15', '2024-03-30', 'draft', 4300.00, 258.00, 4558.00, 'PKR', NOW());

INSERT INTO fmtm_invoice_items (invoice_id, item_name, quantity, unit_price, total)
VALUES
(1, 'Cotton Roll A', 20, 300.00, 6000.00),
(1, 'Dye Pack', 10, 600.00, 6000.00),
(2, 'Silk Bundle', 15, 400.00, 6000.00),
(2, 'Thread Set', 25, 100.00, 2500.00),
(3, 'Sample Swatches', 10, 200.00, 2000.00),
(3, 'Packaging', 10, 230.00, 2300.00);

INSERT INTO fmtm_cash_ledger (entry_date, reference, type, amount, description, created_at)
VALUES
('2024-01-06', 'RCPT-1001', 'receipt', 12720.00, 'Payment received from Liaquat Fabrics', NOW()),
('2024-02-18', 'PAY-2001', 'payment', 2500.00, 'Utility bills', NOW()),
('2024-03-01', 'TRF-3001', 'transfer', 5000.00, 'Transfer to bank', NOW());
