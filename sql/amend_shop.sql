USE `dailytakings2`;
START TRANSACTION;

ALTER TABLE `user` ADD `failedloginattempts` TINYINT(1) NOT NULL DEFAULT '0' AFTER `suspended`;

COMMIT;