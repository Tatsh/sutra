DROP TABLE IF EXISTS user_verifications;
CREATE TABLE user_verifications (
  verification_id INTEGER AUTOINCREMENT PRIMARY KEY,
  user_id INTEGER NOT NULL REFERENCES users(user_id) ON DELETE CASCADE ON UPDATE CASCADE,
  verification_key VARCHAR(64) NOT NULL,
  date_issued TIMESTAMP DEFAULT 0 NOT NULL,
  date_used TIMESTAMP DEFAULT 0 NOT NULL,
  timezone VARCHAR(64) DEFAULT 'America/Los_Angeles' NOT NULL
);
CREATE UNIQUE INDEX idx_uid_key ON user_verifications (user_id, verification_key);
