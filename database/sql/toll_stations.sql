-- ============================================================
-- Toll Station Management Module — Oracle DDL
-- Run this script in Oracle SQL Developer or SQL*Plus
-- ============================================================

-- Create sequence for station_id auto-increment
CREATE SEQUENCE station_id_seq
    START WITH 1
    INCREMENT BY 1
    NOCACHE
    NOCYCLE;

-- Create the toll_stations table
CREATE TABLE toll_stations (
    station_id    NUMBER          DEFAULT station_id_seq.NEXTVAL PRIMARY KEY,
    station_name  VARCHAR2(100)   NOT NULL,
    district      VARCHAR2(100)   NOT NULL,
    highway       VARCHAR2(150)   NOT NULL,
    lane_count    NUMBER          NOT NULL,
    station_type  VARCHAR2(20)    NOT NULL,
    opening_date  DATE            NOT NULL,
    status        VARCHAR2(20)    DEFAULT 'ACTIVE' NOT NULL,
    created_by    NUMBER,
    created_at    DATE            DEFAULT SYSDATE,
    updated_at    DATE,

    -- UNIQUE constraint: no duplicate station names
    CONSTRAINT uq_station_name   UNIQUE (station_name),

    -- CHECK: lane count must be positive
    CONSTRAINT chk_lane_count    CHECK (lane_count > 0),

    -- CHECK: only allowed station types
    CONSTRAINT chk_station_type  CHECK (station_type IN ('Bridge', 'Highway', 'Expressway')),

    -- CHECK: only allowed status values
    CONSTRAINT chk_station_status CHECK (status IN ('ACTIVE', 'INACTIVE', 'UNDER_MAINTENANCE')),

    -- FOREIGN KEY: created_by references users table
    CONSTRAINT fk_station_creator FOREIGN KEY (created_by) REFERENCES users(user_id)
);

-- ============================================================
-- Sample Data — 8 Realistic Bangladeshi Toll Stations
-- ============================================================

INSERT INTO toll_stations (station_name, district, highway, lane_count, station_type, opening_date, status, created_by)
VALUES ('Padma Bridge Toll Plaza',    'Shariatpur', 'N8 - Dhaka-Khulna Highway',   12, 'Bridge',     TO_DATE('2022-06-25','YYYY-MM-DD'), 'ACTIVE', 4);

INSERT INTO toll_stations (station_name, district, highway, lane_count, station_type, opening_date, status, created_by)
VALUES ('Bangabandhu Bridge West',    'Sirajganj',  'N4 - Dhaka-Bogura Highway',    8, 'Bridge',     TO_DATE('1998-06-23','YYYY-MM-DD'), 'ACTIVE', 4);

INSERT INTO toll_stations (station_name, district, highway, lane_count, station_type, opening_date, status, created_by)
VALUES ('Dhaka Elevated Expressway',  'Dhaka',      'Dhaka Elevated Expressway',   10, 'Expressway', TO_DATE('2023-09-02','YYYY-MM-DD'), 'ACTIVE', 4);

INSERT INTO toll_stations (station_name, district, highway, lane_count, station_type, opening_date, status, created_by)
VALUES ('Meghna Bridge Plaza',        'Comilla',    'N1 - Dhaka-Chittagong Highway', 6, 'Bridge',    TO_DATE('2016-11-01','YYYY-MM-DD'), 'ACTIVE', 4);

INSERT INTO toll_stations (station_name, district, highway, lane_count, station_type, opening_date, status, created_by)
VALUES ('Kanchpur Bridge Toll',       'Narayanganj','N1 - Dhaka-Chittagong Highway', 6, 'Bridge',    TO_DATE('2008-03-15','YYYY-MM-DD'), 'UNDER_MAINTENANCE', 4);

INSERT INTO toll_stations (station_name, district, highway, lane_count, station_type, opening_date, status, created_by)
VALUES ('Sylhet Bypass Highway Gate', 'Sylhet',     'N2 - Dhaka-Sylhet Highway',    4, 'Highway',   TO_DATE('2019-07-10','YYYY-MM-DD'), 'ACTIVE', 4);

INSERT INTO toll_stations (station_name, district, highway, lane_count, station_type, opening_date, status, created_by)
VALUES ('Rajshahi Bypass Gate',       'Rajshahi',   'N6 - Rajshahi Highway',         4, 'Highway',   TO_DATE('2015-01-20','YYYY-MM-DD'), 'INACTIVE', 4);

INSERT INTO toll_stations (station_name, district, highway, lane_count, station_type, opening_date, status, created_by)
VALUES ('Chittagong Port Access Gate','Chittagong',  'N1 - Dhaka-Chittagong Highway', 8, 'Highway',   TO_DATE('2010-05-05','YYYY-MM-DD'), 'ACTIVE', 4);

COMMIT;
