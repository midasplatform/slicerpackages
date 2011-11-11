DROP TABLE IF EXISTS slicerpackages_package;

CREATE TABLE IF NOT EXISTS slicerpackages_package (
  package_id serial PRIMARY KEY,
  item_id bigint NOT NULL,
  os character varying(256) NOT NULL,
  arch character varying(256) NOT NULL,
  revision character varying(256) NOT NULL,
  submissiontype character varying(256) NOT NULL,
  packagetype character varying(256) NOT NULL
);
