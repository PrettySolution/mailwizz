--
-- Update sql for MailWizz EMA from version 1.3.9.0 to 1.3.9.1
--

-- -----------------------------------------------------
-- Table `console_command`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `console_command` (
  `command_id` int(11) NOT NULL AUTO_INCREMENT,
  `command` varchar(255) NOT NULL,
  `date_added` datetime NOT NULL,
  `last_updated` datetime NOT NULL,
  PRIMARY KEY (`command_id`),
  UNIQUE KEY `command_UNIQUE` (`command`))
ENGINE = InnoDB DEFAULT CHARSET=utf8;


-- -----------------------------------------------------
-- Table `console_command_history`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `console_command_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `command_id` int(11) NOT NULL,
  `action` varchar(255) NOT NULL DEFAULT 'index',
  `params` varchar(255) DEFAULT NULL,
  `start_time` decimal(14,4) NOT NULL DEFAULT '0.0000',
  `end_time` decimal(14,4) NOT NULL DEFAULT '0.0000',
  `start_memory` int(11) NOT NULL DEFAULT '0',
  `end_memory` int(11) NOT NULL DEFAULT '0',
  `status` char(10) NOT NULL DEFAULT 'success',
  `date_added` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_console_command_history_console_command1_idx` (`command_id`),
  CONSTRAINT `fk_console_command_history_console_command1`
    FOREIGN KEY (`command_id`)
    REFERENCES `console_command` (`command_id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
ENGINE = InnoDB DEFAULT CHARSET=utf8;