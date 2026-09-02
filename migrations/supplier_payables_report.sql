-- Register the Supplier & Payables report with the existing AP report security level.
-- Run once per company database after deploying the PHP files.
INSERT INTO scripts (script, pagesecurity, description)
VALUES ('SupplierPayablesReport.php', 2, 'Executive supplier relationship and accounts payable reporting')
ON DUPLICATE KEY UPDATE
    pagesecurity = VALUES(pagesecurity),
    description = VALUES(description);
