CREATE TABLE shipyards (
    id BIGINT AUTO_INCREMENT,
    id_mmk BIGINT,
    id_ba VARCHAR(255),
    name VARCHAR(255),
    shortName VARCHAR(255),
    CONSTRAINT pk_shipyards
        PRIMARY KEY (id)
);