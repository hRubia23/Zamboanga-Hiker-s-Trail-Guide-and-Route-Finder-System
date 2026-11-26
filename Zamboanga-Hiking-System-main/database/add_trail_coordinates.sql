USE zamboanga_hiking;
ALTER TABLE trails
    ADD COLUMN latitude DECIMAL(10,6) NULL AFTER difficulty,
    ADD COLUMN longitude DECIMAL(10,6) NULL AFTER latitude;
