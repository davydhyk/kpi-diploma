CREATE TABLE countries (
    id BIGINT AUTO_INCREMENT,
    id_mmk BIGINT,
    id_ba VARCHAR(255),
    name VARCHAR(255),
    shortName VARCHAR(2),
    longName VARCHAR(3),
    regionId BIGINT,
    CONSTRAINT pk_countries
       PRIMARY KEY (id),
    CONSTRAINT fk_country_world_region
        FOREIGN KEY (regionId) REFERENCES world_regions (id)
);