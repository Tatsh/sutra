DROP TABLE IF EXISTS categories;
CREATE TABLE categories (
  category_id INTEGER AUTOINCREMENT PRIMARY KEY,
  name VARCHAR(128) NOT NULL,
  description TEXT DEFAULT '' NOT NULL,
  date_updated TIMESTAMP DEFAULT 0 NOT NULL,
  date_created TIMESTAMP DEFAULT 0 NOT NULL
);
CREATE UNIQUE INDEX idx_categories_id_name ON categories (category_id, name);
