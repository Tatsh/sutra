DROP TABLE IF EXISTS user_verifications;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
  user_id INTEGER AUTOINCREMENT PRIMARY KEY,
  name VARCHAR(128) NOT NULL,
  user_password VARCHAR(255) NOT NULL,
  email_address VARCHAR(255) DEFAULT '' NOT NULL,
  verified BOOLEAN DEFAULT 0 NOT NULL,
  auth_level VARCHAR(32) DEFAULT 'user' NOT NULL,
  last_accessed TIMESTAMP DEFAULT 0 NOT NULL,
  date_created TIMESTAMP DEFAULT 0 NOT NULL,
  timezone VARCHAR(64) DEFAULT 'America/Los_Angeles' NOT NULL,
  language VARCHAR(2) DEFAULT 'en' NOT NULL,
  avatar VARCHAR(255) DEFAULT '' NOT NULL,
  deactivated TIMESTAMP DEFAULT 0 NOT NULL
);
CREATE UNIQUE INDEX idx_users_id_name ON users (user_id, name);
CREATE UNIQUE INDEX idx_users_name ON users (name);
CREATE UNIQUE INDEX idx_users_email ON users (email_address);

INSERT INTO users (name, user_password) VALUES('guest', 'nologin');
