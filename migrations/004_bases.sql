CREATE TABLE bases (
    id BIGINT AUTO_INCREMENT,
    id_mmk BIGINT,
    id_ba VARCHAR(255),
    name VARCHAR(255),
    name_ba VARCHAR(255),
    city VARCHAR(255),
    address VARCHAR(255),
    latitude DOUBLE,
    longitude DOUBLE,
    countryId BIGINT,
    CONSTRAINT pk_bases
       PRIMARY KEY (id),
    CONSTRAINT pk_bases_mmk
       UNIQUE (id_mmk),
    CONSTRAINT fk_base_country
       FOREIGN KEY (countryId) REFERENCES countries (id)
);

CREATE TABLE bases_to_sailing_areas (
    baseId BIGINT NOT NULL,
    sailingAreaId BIGINT NOT NULL,
    CONSTRAINT fk_b2s_base
        FOREIGN KEY (baseId) REFERENCES bases (id_mmk)
        ON DELETE CASCADE,
    CONSTRAINT fk_b2s_sailing_area
        FOREIGN KEY (sailingAreaId) REFERENCES sailing_areas (id)
        ON DELETE CASCADE
);