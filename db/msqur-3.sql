ALTER TABLE `engines` ADD `make` TINYTEXT NULL DEFAULT NULL AFTER `id`, ADD `code` TINYTEXT NULL DEFAULT NULL AFTER `make`;
ALTER TABLE `metadata` ADD `views` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `url`;
ALTER TABLE `metadata`  ADD `reingest` BOOLEAN NOT NULL DEFAULT FALSE;
