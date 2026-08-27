-- Performance indexes for shop/parchi/inward/api/listInwardBazarParchiApi.php.
-- Run once against each company database during a low-traffic window.

SET @inward_bazar_parchi_old_sql_mode = @@SESSION.sql_mode;
SET SESSION sql_mode = '';

DELIMITER //
DROP PROCEDURE IF EXISTS _inward_bazar_parchi_add_indexes //
CREATE PROCEDURE _inward_bazar_parchi_add_indexes()
BEGIN
    IF EXISTS (
        SELECT 1 FROM information_schema.tables
        WHERE table_schema = DATABASE() AND table_name = 'bazar_parchi'
    ) AND NOT EXISTS (
        SELECT 1 FROM information_schema.statistics
        WHERE table_schema = DATABASE()
          AND table_name = 'bazar_parchi'
          AND index_name = 'idx_bazar_parchi_list_type_date'
    ) THEN
        ALTER TABLE `bazar_parchi`
            ADD INDEX `idx_bazar_parchi_list_type_date`
                (`type`, `discarded`, `created_at`);
    END IF;

    IF EXISTS (
        SELECT 1 FROM information_schema.tables
        WHERE table_schema = DATABASE() AND table_name = 'bazar_parchi'
    ) AND NOT EXISTS (
        SELECT 1 FROM information_schema.statistics
        WHERE table_schema = DATABASE()
          AND table_name = 'bazar_parchi'
          AND index_name = 'idx_bazar_parchi_list_vendor_state'
    ) THEN
        ALTER TABLE `bazar_parchi`
            ADD INDEX `idx_bazar_parchi_list_vendor_state`
                (`type`, `discarded`, `svid`, `inprogress`);
    END IF;

    IF EXISTS (
        SELECT 1 FROM information_schema.tables
        WHERE table_schema = DATABASE() AND table_name = 'bpitems'
    ) AND NOT EXISTS (
        SELECT 1 FROM information_schema.statistics
        WHERE table_schema = DATABASE()
          AND table_name = 'bpitems'
          AND index_name = 'idx_bpitems_parchi_deleted'
    ) THEN
        ALTER TABLE `bpitems`
            ADD INDEX `idx_bpitems_parchi_deleted`
                (`parchino`, `deleted_by`);
    END IF;

    IF EXISTS (
        SELECT 1 FROM information_schema.tables
        WHERE table_schema = DATABASE() AND table_name = 'bpitems'
    ) AND NOT EXISTS (
        SELECT 1 FROM information_schema.statistics
        WHERE table_schema = DATABASE()
          AND table_name = 'bpitems'
          AND index_name = 'idx_bpitems_parchi_stockid'
    ) THEN
        ALTER TABLE `bpitems`
            ADD INDEX `idx_bpitems_parchi_stockid`
                (`parchino`, `stockid`);
    END IF;

    IF EXISTS (
        SELECT 1 FROM information_schema.tables
        WHERE table_schema = DATABASE() AND table_name = 'bpledger'
    ) AND NOT EXISTS (
        SELECT 1 FROM information_schema.statistics
        WHERE table_schema = DATABASE()
          AND table_name = 'bpledger'
          AND index_name = 'idx_bpledger_parchino'
    ) THEN
        ALTER TABLE `bpledger`
            ADD INDEX `idx_bpledger_parchino` (`parchino`);
    END IF;

    IF EXISTS (
        SELECT 1 FROM information_schema.tables
        WHERE table_schema = DATABASE() AND table_name = 'supptrans'
    ) AND NOT EXISTS (
        SELECT 1 FROM information_schema.statistics
        WHERE table_schema = DATABASE()
          AND table_name = 'supptrans'
          AND index_name = 'idx_supptrans_type_transno'
    ) THEN
        ALTER TABLE `supptrans`
            ADD INDEX `idx_supptrans_type_transno`
                (`type`, `transno`, `id`, `settled`);
    END IF;

    IF EXISTS (
        SELECT 1 FROM information_schema.tables
        WHERE table_schema = DATABASE() AND table_name = 'vendor_permission'
    ) AND NOT EXISTS (
        SELECT 1 FROM information_schema.statistics
        WHERE table_schema = DATABASE()
          AND table_name = 'vendor_permission'
          AND index_name = 'idx_vendor_permission_user_permission'
    ) THEN
        ALTER TABLE `vendor_permission`
            ADD INDEX `idx_vendor_permission_user_permission`
                (`userid`, `permission`);
    END IF;
END //
DELIMITER ;

CALL _inward_bazar_parchi_add_indexes();
DROP PROCEDURE IF EXISTS _inward_bazar_parchi_add_indexes;

SET SESSION sql_mode = @inward_bazar_parchi_old_sql_mode;
