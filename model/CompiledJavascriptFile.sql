DROP TABLE IF EXISTS compiled_javascript_files;
CREATE TABLE compiled_javascript_files (
  file_id INTEGER AUTOINCREMENT PRIMARY KEY,
  filename VARCHAR(255) NOT NULL,
  date_created TIMESTAMP NOT NULL,
  date_completed TIMESTAMP DEFAULT 0 NOT NULL,
  completed BOOLEAN DEFAULT 0 NOT NULL
);
