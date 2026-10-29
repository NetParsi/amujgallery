-- Newsletter sign-ups from the footer form (email or mobile, one row each)
CREATE TABLE IF NOT EXISTS `#__amuj_newsletter` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `contact` VARCHAR(191) NOT NULL DEFAULT '',
    `type` VARCHAR(10) NOT NULL DEFAULT 'email',
    `created` DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_contact` (`contact`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
