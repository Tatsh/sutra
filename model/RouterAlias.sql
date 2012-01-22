DROP TABLE IF EXISTS router_aliases;
CREATE TABLE router_aliases (
  alias VARCHAR(255) PRIMARY KEY,
  path VARCHAR(255) NOT NULL
);
CREATE UNIQUE INDEX idx_ra_alias_path ON router_aliases (alias, path);
