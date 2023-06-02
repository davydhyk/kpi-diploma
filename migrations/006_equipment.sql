CREATE TABLE equipment (
    id BIGINT AUTO_INCREMENT,
    id_mmk BIGINT,
    name VARCHAR(255),
    name_ba VARCHAR(255),
    CONSTRAINT pk_equipment
        PRIMARY KEY (id)
);