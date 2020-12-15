--
-- Update sql for MailWizz EMA from version 1.3.6.2 to 1.3.6.3
--

--
-- Table campaign_option
--
ALTER TABLE `campaign_option` ADD `max_send_count` INT(11) NOT NULL DEFAULT '0' AFTER `giveup_counter`;
ALTER TABLE `campaign_option` ADD `max_send_count_random` ENUM('yes','no') NOT NULL DEFAULT 'no' AFTER `max_send_count`;

--
-- Table delivery_server
--
UPDATE `delivery_server` SET `force_from` = 'always' WHERE (`type` LIKE '%web-api' OR `type` = 'smtp-amazon');

--
-- Table list_subscriber_list_move
--
CREATE TABLE IF NOT EXISTS `list_subscriber_list_move` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `source_subscriber_id` INT(11) NOT NULL,
  `source_list_id` INT(11) NOT NULL,
  `destination_subscriber_id` INT(11) NOT NULL,
  `destination_list_id` INT(11) NOT NULL,
  `date_added` DATETIME NOT NULL,
  `last_updated` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_list_subscriber_list_move_list_subscriber1_idx` (`source_subscriber_id`),
  INDEX `fk_list_subscriber_list_move_list1_idx` (`source_list_id`),
  INDEX `fk_list_subscriber_list_move_list_subscriber2_idx` (`destination_subscriber_id`),
  INDEX `fk_list_subscriber_list_move_list2_idx` (`destination_list_id`),
  CONSTRAINT `fk_list_subscriber_list_move_list_subscriber1`
    FOREIGN KEY (`source_subscriber_id`)
    REFERENCES `list_subscriber` (`subscriber_id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_list_subscriber_list_move_list1`
    FOREIGN KEY (`source_list_id`)
    REFERENCES `list` (`list_id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_list_subscriber_list_move_list_subscriber2`
    FOREIGN KEY (`destination_subscriber_id`)
    REFERENCES `list_subscriber` (`subscriber_id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_list_subscriber_list_move_list2`
    FOREIGN KEY (`destination_list_id`)
    REFERENCES `list` (`list_id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
ENGINE = InnoDB DEFAULT CHARSET=utf8;

--
-- Table campaign_bounce_log
--
ALTER TABLE `campaign_bounce_log` CHANGE `bounce_type` `bounce_type` ENUM('hard','soft','internal') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'hard';