--
-- Update sql for MailWizz EMA from version 1.3.5.8 to 1.3.5.9
--

--
-- Table structure for table `customer_campaign_tag`
--
CREATE TABLE IF NOT EXISTS `customer_campaign_tag` (
    `tag_id` INT NOT NULL AUTO_INCREMENT,
    `tag_uid` CHAR(13) NOT NULL,
    `customer_id` INT(11) NOT NULL,
    `tag` VARCHAR(50) NOT NULL,
    `content` TEXT NOT NULL,
    `random` ENUM('yes','no') NOT NULL DEFAULT 'no',
    `date_added` DATETIME NOT NULL,
    `last_updated` DATETIME NOT NULL,
    PRIMARY KEY (`tag_id`),
    KEY `fk_customer_campaign_tag_customer1_idx` (`customer_id`),
    UNIQUE KEY `customer_campaign_tag_uid` (`tag_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Constraints for table `customer_campaign_tag`
--
ALTER TABLE `customer_campaign_tag`
    ADD CONSTRAINT `fk_customer_campaign_tag_customer1` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE ON UPDATE NO ACTION;

---
--- Add list column
---
ALTER TABLE `list` ADD `removable` ENUM('yes', 'no') NOT NULL DEFAULT 'yes' AFTER `welcome_email`;

---
--- Add blocked_reason column
---
ALTER TABLE `campaign_option` ADD `blocked_reason` VARCHAR(255) NULL DEFAULT NULL AFTER `cronjob_enabled`;

---
--- Add subscriber_exists_redirect column
---
ALTER TABLE `list` ADD `subscriber_exists_redirect` VARCHAR(255) NULL DEFAULT NULL AFTER `subscriber_404_redirect`;

---
--- Add website column
---
ALTER TABLE `list_company` ADD `website` VARCHAR(255) NULL DEFAULT NULL AFTER `name`;

---
--- Add open_tracking column
---
ALTER TABLE `campaign_option` ADD `open_tracking` ENUM('yes','no') NOT NULL DEFAULT 'yes' AFTER `campaign_id`;

--
-- Table structure for table `customer_message`
--
CREATE TABLE IF NOT EXISTS `customer_message` (
  `message_id` INT NOT NULL AUTO_INCREMENT,
  `message_uid` CHAR(13) NOT NULL,
  `customer_id` INT(11) NOT NULL,
  `title` VARCHAR(255) NULL,
  `message` TEXT NOT NULL,
  `params` TEXT NULL,
  `status` CHAR(15) NOT NULL DEFAULT 'unseen',
  `date_added` DATETIME NOT NULL,
  `last_updated` DATETIME NOT NULL,
  PRIMARY KEY (`message_id`),
  INDEX `fk_customer_message_customer1_idx` (`customer_id`),
  CONSTRAINT `fk_customer_message_customer1`
    FOREIGN KEY (`customer_id`)
    REFERENCES `customer` (`customer_id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
ENGINE = InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

---
--- INDEXES last as they might break the queries.
---
ALTER TABLE `bounce_server` ADD KEY `status` (`status`);
ALTER TABLE `list_subscriber` ADD KEY `email` (`email`);
ALTER TABLE `campaign_bounce_log` ADD KEY `proc_bt` (`processed`,`bounce_type`);
ALTER TABLE `campaign_delivery_log` ADD KEY `proc_status` (`processed`,`status`);
ALTER TABLE `campaign_delivery_log` ADD KEY `cid_status`(`campaign_id`, `status`);
ALTER TABLE `campaign_delivery_log` ADD KEY `cid_date_added`(`campaign_id`, `date_added`);
