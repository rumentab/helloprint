ALTER USER 'root'@'localhost' IDENTIFIED BY 'rootpass';

DROP SCHEMA IF EXISTS `helloprint`;

DROP USER IF EXISTS 'helloprint'@'localhost';

DROP USER  IF EXISTS 'helloprint'@'%';

FLUSH PRIVILEGES;

CREATE SCHEMA `helloprint` DEFAULT CHARACTER SET = 'utf8' DEFAULT COLLATE = 'utf8_general_ci';

SET NAMES 'utf8';

CREATE USER 'helloprint'@'%' IDENTIFIED BY 'helloprint';

GRANT ALL ON helloprint.* TO 'helloprint'@'%';

FLUSH PRIVILEGES;

USE `helloprint`;

SET @email = 'xxxx@xxxx.xx';

CREATE TABLE IF NOT EXISTS `users` (
  id INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(64) NOT NULL UNIQUE,
  password VARCHAR(64) NOT NULL,
  email VARCHAR(128) NOT NULL UNIQUE,
  status TINYINT(1) DEFAULT 0
) ENGINE=InnoDB;

TRUNCATE TABLE `users`;

INSERT INTO `users` (username, password, email, status) VALUES ("helloprint", "P @ ssw0rd!", @email, 1);
