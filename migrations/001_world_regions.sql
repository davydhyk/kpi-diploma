CREATE TABLE world_regions (
  id BIGINT NOT NULL,
  name VARCHAR(255) NOT NULL,
  hash VARCHAR(255) NOT NULL,
  CONSTRAINT pk_world_regions
    PRIMARY KEY (id)
);