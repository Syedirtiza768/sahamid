-- CSV/CRV stock allocations use the stock item's unit, which can be fractional
-- (for example 7.31 metres). INT rounded these amounts before finalization.
-- Existing whole-number allocations retain their values. Recover historical
-- fractions separately from the dispatch records; ALTER cannot recover them.
ALTER TABLE ogpcsvref MODIFY COLUMN quantity DECIMAL(18,6) DEFAULT NULL;
ALTER TABLE ogpcrvref MODIFY COLUMN quantity DECIMAL(18,6) DEFAULT NULL;
