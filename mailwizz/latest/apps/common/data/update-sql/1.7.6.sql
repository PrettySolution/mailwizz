--
-- Update sql for MailWizz EMA from version 1.7.5 to 1.7.6
--

ALTER TABLE `delivery_server` CHANGE `name` `name` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
ALTER TABLE `delivery_server` CHANGE `hostname` `hostname` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
ALTER TABLE `delivery_server` CHANGE `username` `username` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;

ALTER TABLE `delivery_server` CHANGE `from_email` `from_email` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
ALTER TABLE `delivery_server` CHANGE `from_name` `from_name` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
ALTER TABLE `delivery_server` CHANGE `reply_to_email` `reply_to_email` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;

ALTER TABLE `campaign_option` ADD `giveup_count` INT(11) NOT NULL DEFAULT '0' AFTER `giveup_counter`;

DROP TABLE IF EXISTS `campaign_resend_giveup_queue`;
CREATE TABLE IF NOT EXISTS `campaign_resend_giveup_queue` (
  `campaign_id` INT(11) NOT NULL,
  `date_added` DATETIME NOT NULL,
  `last_updated` DATETIME NOT NULL,
  PRIMARY KEY (`campaign_id`),
  CONSTRAINT `fk_campaign_resend_giveups_queue_campaign1`
    FOREIGN KEY (`campaign_id`)
    REFERENCES `campaign` (`campaign_id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION
) ENGINE = InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

-- -----------------------------------------------------
-- Table structure for `campaign_share_code`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `campaign_share_code`;
CREATE TABLE IF NOT EXISTS `campaign_share_code` (
  `code_id` INT(11) NOT NULL AUTO_INCREMENT,
  `code_uid` CHAR(40) NOT NULL,
  `used` ENUM('yes', 'no') NOT NULL DEFAULT 'no',
  `date_added` DATETIME NOT NULL,
  `last_updated` DATETIME NOT NULL,
  PRIMARY KEY (`code_id`),
  UNIQUE KEY `code_uid_UNIQUE` (`code_uid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

-- --------------------------------------------------------

-- -----------------------------------------------------
-- Table structure for `campaign_share_code_to_campaign`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `campaign_share_code_to_campaign`;
CREATE TABLE IF NOT EXISTS `campaign_share_code_to_campaign` (
  `code_id` INT(11) NOT NULL,
  `campaign_id` INT(11) NOT NULL,
  PRIMARY KEY (`campaign_id`, `code_id`),
  INDEX `fk_campaign_share_code_to_campaign_code_id_idx` (`code_id`),
  INDEX `fk_campaign_share_code_to_campaign_campaign_id_idx` (`campaign_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

-- --------------------------------------------------------

--
-- Constraints for table `campaign_share_code_to_campaign`
--
ALTER TABLE `campaign_share_code_to_campaign`
  ADD CONSTRAINT `fk_campaign_share_code_to_campaign_code_id` FOREIGN KEY (`code_id`) REFERENCES `campaign_share_code` (`code_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_campaign_share_code_to_campaign_campaign_id` FOREIGN KEY (`campaign_id`) REFERENCES `campaign` (`campaign_id`) ON DELETE CASCADE ON UPDATE NO ACTION;

-- --------------------------------------------------------
