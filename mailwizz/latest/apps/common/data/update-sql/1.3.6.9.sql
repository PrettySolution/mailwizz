--
-- Update sql for MailWizz EMA from version 1.3.6.8 to 1.3.6.9
--

--
-- Table structure for table `email_blacklist_monitor`
--
CREATE TABLE IF NOT EXISTS `email_blacklist_monitor` (
  `monitor_id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `email_condition` CHAR(15) NULL,
  `email` VARCHAR(255) NULL,
  `reason_condition` CHAR(15) NULL,
  `reason` VARCHAR(255) NULL,
  `condition_operator` ENUM('and', 'or') NOT NULL DEFAULT 'and',
  `notifications_to` VARCHAR(255) NULL,
  `status` CHAR(15) NOT NULL DEFAULT 'active',
  `date_added` DATETIME NOT NULL,
  `last_updated` DATETIME NOT NULL,
  PRIMARY KEY (`monitor_id`))
ENGINE = InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;