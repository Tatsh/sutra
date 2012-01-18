DROP TABLE IF EXISTS site_variables;
CREATE TABLE site_variables (
  name VARCHAR(128) NOT NULL PRIMARY KEY,
  value_string VARCHAR(1024) NOT NULL
);
