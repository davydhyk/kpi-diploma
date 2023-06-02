CREATE TABLE companies (
  id BIGINT AUTO_INCREMENT,
  id_mmk BIGINT,
  id_ba VARCHAR(255),
  name VARCHAR(255) NOT NULL,
  city VARCHAR(255),
  country VARCHAR(255),
  telephone VARCHAR(255),
  telephone2 VARCHAR(255),
  mobile VARCHAR(255),
  vatCode VARCHAR(255),
  email VARCHAR(255),
  web VARCHAR(255),
  bankAccountNumber VARCHAR(255),
  termsAndConditions LONGTEXT,
  checkoutNote TEXT,
  CONSTRAINT pk_companies
    PRIMARY KEY (id)
);