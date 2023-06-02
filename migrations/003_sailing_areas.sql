CREATE TABLE sailing_areas (
    id BIGINT NOT NULL,
    name VARCHAR(255) NOT NULL,
    hash VARCHAR(255) NOT NULL,
    CONSTRAINT pk_sailing_areas
        PRIMARY KEY (id)
);