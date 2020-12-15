--
-- Update sql for MailWizz EMA from version 1.6.1 to 1.6.2
--

DROP TABLE IF EXISTS `common_email_template`;
CREATE TABLE IF NOT EXISTS `common_email_template` (
  `template_id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `slug` VARCHAR(255) NOT NULL,
  `subject` VARCHAR(255) NOT NULL,
  `content` LONGTEXT NOT NULL,
  `removable` enum('yes','no') NOT NULL DEFAULT 'no',
  `date_added` DATETIME NOT NULL,
  `last_updated` DATETIME NOT NULL,
  PRIMARY KEY (`template_id`),
  UNIQUE KEY `slug_UNIQUE` (`slug`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

DROP TABLE IF EXISTS `common_email_template_tag`;
CREATE TABLE IF NOT EXISTS `common_email_template_tag` (
  `tag_id` INT NOT NULL AUTO_INCREMENT,
  `template_id` INT NOT NULL,
  `tag` VARCHAR(100) NOT NULL,
  `description` VARCHAR(255) NULL,
  `date_added` DATETIME NOT NULL,
  `last_updated` DATETIME NOT NULL,
  PRIMARY KEY (`tag_id`),
  KEY `fk_common_email_template_tag_common_email_template1_idx` (`template_id`),
  CONSTRAINT `fk_common_email_template_tag_common_email_template1` FOREIGN KEY (`template_id`) REFERENCES `common_email_template` (`template_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE = InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

DROP TABLE IF EXISTS `price_plan_customer_group_display`;
CREATE TABLE IF NOT EXISTS `price_plan_customer_group_display` (
  `plan_id` INT(11) NOT NULL,
  `group_id` INT(11) NOT NULL,
  PRIMARY KEY (`plan_id`, `group_id`),
  INDEX `fk_price_plan_customer_group_display_group1_idx` (`group_id`),
  INDEX `fk_price_plan_customer_group_display_plan1_idx` (`plan_id`),
  CONSTRAINT `fk_price_plan_customer_group_display_plan1` FOREIGN KEY (`plan_id`) REFERENCES `price_plan` (`plan_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `fk_price_plan_customer_group_display_group1` FOREIGN KEY (`group_id`) REFERENCES `customer_group` (`group_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8;