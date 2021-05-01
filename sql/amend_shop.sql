USE `dailytakings2`;
START TRANSACTION;

ALTER TABLE `user` ADD `failedloginattempts` TINYINT(1) NOT NULL DEFAULT '0' AFTER `suspended`;
ALTER TABLE `user` ADD `email` VARCHAR(255) NULL AFTER `failedloginattempts`;
ALTER TABLE `user` ADD `title` VARCHAR(100) NULL AFTER `failedloginattempts`;
ALTER TABLE `user` ADD `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `email`;
UPDATE `user` SET username='nsc' WHERE id = 1;

ALTER DATABASE `dailytakings2` DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_general_ci;
ALTER TABLE `shop` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
ALTER TABLE `shopamendment` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
ALTER TABLE `takings` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
ALTER TABLE `user` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

INSERT INTO user
SET username='test', isAdmin='0', firstname='Test',surname='User', shopid=1,suspended='0', failedloginattempts='0',password='$2y$10$Annq5/qbt5w9VnaSj3qWKOElR5lj1KpjTshqKghW3v9xb5Wbbbovm';
INSERT INTO user
SET username='admin', isAdmin='1', firstname='Admin', surname='User', shopid=1,suspended='0', failedloginattempts='0',password='$2y$10$FJ8kSpWlrCbv18SIhVwK1.Thx9xzBEkVvqhjurlYk2n853KH9IW8G';

CREATE TABLE `usertoken` ( 
`iduser` INT NOT NULL ,
`primaryKey` VARCHAR(36) NOT NULL , 
`secondaryKey` VARCHAR(36) NOT NULL , 
`status` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'When 0 token is invalid', 
`issuedAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP , 
`expiresAt` DATETIME NOT NULL ) ENGINE = InnoDB COMMENT = 'Store of access/refresh token pairs';

ALTER TABLE usertoken
    ADD CONSTRAINT fk_usertoken_user_idx
    FOREIGN KEY (iduser)
    REFERENCES user(id);
    
COMMIT;