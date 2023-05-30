CREATE TABLE boats (
    id BIGINT AUTO_INCREMENT,
    id_mmk BIGINT,
    id_ba VARCHAR(255),
    slug VARCHAR(255),
    name VARCHAR(255),
    model VARCHAR(255),
    shipyardId BIGINT,
    year INT,
    kind VARCHAR(255),
    homeBaseId BIGINT,
    companyId BIGINT NOT NULL,
    draught FLOAT,
    beam FLOAT,
    length FLOAT,
    waterCapacity FLOAT,
    fuelCapacity FLOAT,
    engine VARCHAR(255),
    deposit FLOAT,
    currency VARCHAR(3),
    commissionPercentage FLOAT,
    wc INT,
    berths INT,
    cabins INT,
    transitLog FLOAT,
    mainsailArea FLOAT,
    genoaArea FLOAT,
    mainsailType VARCHAR(255),
    genoaType VARCHAR(255),
    requiredSkipperLicense bool,
    defaultCheckInDay INT,
    defaultCheckInTime VARCHAR(255),
    defaultCheckOutTime VARCHAR(255),
    hash_mmk VARCHAR(255),
    hash_ba VARCHAR(255),
    CONSTRAINT pk_boats
        PRIMARY KEY (id),
    CONSTRAINT fk_boats_shipyards
        FOREIGN KEY (shipyardId) REFERENCES shipyards (id),
    CONSTRAINT fk_boats_bases
        FOREIGN KEY (homeBaseId) REFERENCES bases (id),
    CONSTRAINT fk_boats_companies
        FOREIGN KEY (companyId) REFERENCES companies (id)
);

CREATE TABLE images (
    id BIGINT AUTO_INCREMENT NOT NULL,
    url VARCHAR(255),
    description VARCHAR(255),
    sortOrder INT,
    boatId BIGINT NOT NULL,
    CONSTRAINT pk_images
        PRIMARY KEY (id),
    CONSTRAINT fk_images_boats
        FOREIGN KEY (boatId) REFERENCES boats (id)
        ON DELETE CASCADE
);

CREATE TABLE products (
    id BIGINT AUTO_INCREMENT NOT NULL,
    name VARCHAR(255) NOT NULL,
    extras JSON,
    dateFrom DATE,
    dateTo DATE,
    price DOUBLE,
    startPrice DOUBLE,
    discountPercentage FLOAT,
    boatId BIGINT NOT NULL,
    CONSTRAINT pk_products
        PRIMARY KEY (id),
    CONSTRAINT fk_products_boats
        FOREIGN KEY (boatId) REFERENCES boats (id)
        ON DELETE CASCADE
);

CREATE TABLE boats_to_equipment(
    boatId BIGINT NOT NULL,
    equipmentId BIGINT NOT NULL,
    CONSTRAINT fk_b2e_boat
        FOREIGN KEY (boatId) REFERENCES boats (id)
        ON DELETE CASCADE,
    CONSTRAINT fk_b2e_equipment
        FOREIGN KEY (equipmentId) REFERENCES equipment (id)
        ON DELETE CASCADE
);