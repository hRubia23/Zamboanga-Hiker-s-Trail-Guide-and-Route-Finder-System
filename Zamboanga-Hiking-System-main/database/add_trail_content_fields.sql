USE zamboanga_hiking;
ALTER TABLE trails
    ADD COLUMN highlights TEXT NULL AFTER description,
    ADD COLUMN tips TEXT NULL AFTER highlights,
    ADD COLUMN features VARCHAR(255) NULL AFTER tips;
