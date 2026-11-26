USE zamboanga_hiking;
ALTER TABLE trails
    ADD COLUMN distance_km DECIMAL(6,2) NULL AFTER longitude,
    ADD COLUMN duration_min INT NULL AFTER distance_km,
    ADD COLUMN elevation_m INT NULL AFTER duration_min;
