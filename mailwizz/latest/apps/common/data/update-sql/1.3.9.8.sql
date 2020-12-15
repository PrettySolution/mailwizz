--
-- Update sql for MailWizz EMA from version 1.3.9.7 to 1.3.9.8
--

--
-- Alter `api_key` table
--
ALTER TABLE `customer_api_key` 
  ADD `name` VARCHAR(255) NULL AFTER `customer_id`, 
  ADD `description` VARCHAR(255) NULL AFTER `name`;

-- -----------------------------------------------------
-- Table `list_subscriber_optout_history`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `list_subscriber_optout_history` (
  `subscriber_id` INT(11) NOT NULL,
  `optout_ip` VARCHAR(45) NULL,
  `optout_date` DATETIME NULL,
  `optout_user_agent` VARCHAR(255) NULL,
  `confirm_ip` VARCHAR(45) NULL,
  `confirm_date` DATETIME NULL,
  `confirm_user_agent` VARCHAR(255) NULL,
  PRIMARY KEY (`subscriber_id`),
  CONSTRAINT `fk_list_subscriber_optout_history_list_subscriber1`
    FOREIGN KEY (`subscriber_id`)
    REFERENCES `list_subscriber` (`subscriber_id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;