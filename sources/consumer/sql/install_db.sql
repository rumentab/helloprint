USE `helloprint`;

SET @email = 'rumen.tabakov@gmail.com';

CREATE TABLE IF NOT EXISTS `users` (
  id INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(64) NOT NULL UNIQUE,
  password VARCHAR(64) NOT NULL,
  email VARCHAR(128) NOT NULL UNIQUE,
  status TINYINT(1) DEFAULT 0
) ENGINE=InnoDB;

TRUNCATE TABLE `users`;

INSERT INTO `users` (username, password, email, status) VALUES ("helloprint", "P @ ssw0rd!", @email, 1);
