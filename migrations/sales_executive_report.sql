-- ---------------------------------------------------------------------------
-- Sales Executive Report (v2/salesReportsExecutive.php)
--
-- Registers the page with webERP page security so the Reports hub can list it
-- (the hub hides any script without a row in `scripts`). Page security 2 is the
-- level used by the other sales reports.
--
-- Who can open it is decided by the page itself: the 'directorreports'
-- permission (holders of '*' pass too). The sidebar link and the Reports hub
-- link are shown to the same users only.
--
-- Idempotent and non-destructive; safe to run more than once. Users who are
-- already signed in may need to sign in again before the hub shows the link.
-- ---------------------------------------------------------------------------

-- The report's own cache. The shared `cache` table stores TEXT (64 KB) in latin1, which a
-- year of sales cases can outgrow; without this table the page still works but may
-- recalculate on every load. Values are compressed, base64-encoded (ASCII) JSON.
CREATE TABLE IF NOT EXISTS sales_executive_cache (
    unique_key   VARCHAR(64) NOT NULL,
    value        MEDIUMTEXT CHARACTER SET ascii NOT NULL,
    refreshed_at DATETIME NOT NULL,
    PRIMARY KEY (unique_key)
) ENGINE=InnoDB;

INSERT INTO scripts (script, pagesecurity, description)
VALUES ('salesReportsExecutive.php', 2, 'Sales Executive Report: live invoices, shop sales, PO, OC, DC, pending DC, outstanding and sales-case results')
ON DUPLICATE KEY UPDATE
    pagesecurity = VALUES(pagesecurity),
    description = VALUES(description);
