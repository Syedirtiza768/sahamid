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

INSERT INTO scripts (script, pagesecurity, description)
VALUES ('salesReportsExecutive.php', 2, 'Sales Executive Report: live invoices, shop sales, PO, OC, DC, pending DC, outstanding and sales-case results')
ON DUPLICATE KEY UPDATE
    pagesecurity = VALUES(pagesecurity),
    description = VALUES(description);
