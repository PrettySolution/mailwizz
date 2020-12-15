--
-- Update sql for MailWizz EMA from version 1.3.8.7 to 1.3.8.8
--

--
-- Alter delivery_server table
--
ALTER TABLE `delivery_server` ADD `pause_after_send` INT(11) NOT NULL DEFAULT '0' AFTER `monthly_quota`;
ALTER TABLE `delivery_server` DROP `use_queue`;

-- -----------------------------------------------------
-- Table `list_subscriber_optin_history`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `list_subscriber_optin_history` (
  `subscriber_id` INT(11) NOT NULL,
  `optin_ip` VARCHAR(45) NULL,
  `optin_date` DATETIME NULL,
  `optin_user_agent` VARCHAR(255) NULL,
  `confirm_ip` VARCHAR(45) NULL,
  `confirm_date` DATETIME NULL,
  `confirm_user_agent` VARCHAR(255) NULL,
  PRIMARY KEY (`subscriber_id`),
  CONSTRAINT `fk_list_subscriber_optin_history_list_subscriber1`
    FOREIGN KEY (`subscriber_id`)
    REFERENCES `list_subscriber` (`subscriber_id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- -----------------------------------------------------
-- Table `campaign_filter_open_unopen`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `campaign_filter_open_unopen` (
  `campaign_id` INT(11) NOT NULL,
  `action` CHAR(6) NOT NULL DEFAULT 'open',
  `previous_campaign_id` INT(11) NOT NULL,
  KEY `fk_campaign_filter_open_unopen_campaign1_idx` (`campaign_id`),
  KEY `fk_campaign_filter_open_unopen_campaign2_idx` (`previous_campaign_id`),
  UNIQUE KEY `campaign_action_previous_campaign` (`campaign_id`, `action`, `previous_campaign_id`),
  CONSTRAINT `fk_campaign_filter_open_unopen_campaign1`
    FOREIGN KEY (`campaign_id`)
    REFERENCES `campaign` (`campaign_id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_campaign_filter_open_unopen_campaign2`
    FOREIGN KEY (`previous_campaign_id`)
    REFERENCES `campaign` (`campaign_id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
ENGINE = InnoDB DEFAULT CHARSET=utf8;

--
-- Campaign option
--
UPDATE `campaign_option` SET regular_open_unopen_campaign_id = NULL WHERE 1;
ALTER TABLE `campaign_option` 
    DROP FOREIGN KEY `fk_campaign_option_campaign3`,
    DROP KEY `fk_campaign_option_campaign3_idx`,
    DROP `regular_open_unopen_campaign_id`,
    DROP `regular_open_unopen_action`;
