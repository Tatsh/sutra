DROP TABLE IF EXISTS reset_password_requests;
CREATE TABLE reset_password_requests (
  request_id INTEGER AUTOINCREMENT PRIMARY KEY,
  user_id INTEGER NOT NULL REFERENCES users(user_id) ON DELETE CASCADE ON UPDATE CASCADE,
  request_key VARCHAR(64) NOT NULL,
  created_time TIMESTAMP NOT NULL,
  used BOOLEAN DEFAULT 0 NOT NULL
);
CREATE UNIQUE INDEX idx_rpr_key_uid ON reset_password_requests (request_key, user_id);
