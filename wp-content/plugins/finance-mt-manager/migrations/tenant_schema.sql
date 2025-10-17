CREATE TABLE IF NOT EXISTS {{prefix}}accounts (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    code VARCHAR(20) NOT NULL,
    name VARCHAR(191) NOT NULL,
    type VARCHAR(50) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS {{prefix}}invoices (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    invoice_number VARCHAR(50) NOT NULL,
    customer_name VARCHAR(191) NOT NULL,
    issue_date DATE NOT NULL,
    due_date DATE NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'draft',
    subtotal DECIMAL(14,2) NOT NULL DEFAULT 0,
    tax DECIMAL(14,2) NOT NULL DEFAULT 0,
    total DECIMAL(14,2) NOT NULL DEFAULT 0,
    currency VARCHAR(10) NOT NULL DEFAULT 'PKR',
    created_at DATETIME NOT NULL,
    updated_at DATETIME NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS {{prefix}}invoice_items (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    invoice_id BIGINT UNSIGNED NOT NULL,
    item_name VARCHAR(191) NOT NULL,
    quantity DECIMAL(12,2) NOT NULL,
    unit_price DECIMAL(14,2) NOT NULL,
    total DECIMAL(14,2) NOT NULL,
    PRIMARY KEY (id),
    KEY invoice_id (invoice_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS {{prefix}}cash_ledger (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    entry_date DATE NOT NULL,
    reference VARCHAR(50) NULL,
    type VARCHAR(20) NOT NULL,
    amount DECIMAL(14,2) NOT NULL,
    description TEXT NULL,
    related_invoice BIGINT UNSIGNED NULL,
    created_by BIGINT UNSIGNED NULL,
    created_at DATETIME NOT NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS {{prefix}}audit_log (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    actor BIGINT UNSIGNED NULL,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(100) NOT NULL,
    entity_id BIGINT UNSIGNED NULL,
    before_state LONGTEXT NULL,
    after_state LONGTEXT NULL,
    ip_address VARCHAR(45) NULL,
    created_at DATETIME NOT NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO {{prefix}}accounts (code, name, type)
SELECT * FROM (
    SELECT '1000' AS code, 'Cash on Hand / Cash' AS name, 'asset' AS type
    UNION ALL SELECT '1100', 'Bank Account', 'asset'
    UNION ALL SELECT '2000', 'Accounts Payable', 'liability'
    UNION ALL SELECT '3000', 'Owner Equity', 'equity'
    UNION ALL SELECT '4000', 'Sales Revenue', 'income'
    UNION ALL SELECT '5000', 'Cost of Goods Sold', 'expense'
    UNION ALL SELECT '5100', 'Operating Expenses', 'expense'
) AS defaults
WHERE NOT EXISTS (SELECT 1 FROM {{prefix}}accounts);
