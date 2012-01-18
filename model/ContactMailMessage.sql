DROP TABLE IF EXISTS contact_mail_messages;
CREATE TABLE contact_mail_messages (
  message_id INTEGER AUTOINCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  email_address VARCHAR(255) NOT NULL,
  phone_number VARCHAR(32) DEFAULT '' NOT NULL,
  message TEXT NOT NULL,
  date_created TIMESTAMP NOT NULL
);
