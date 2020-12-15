--
-- Update sql for MailWizz EMA from version 1.3.7.2 to 1.3.7.3
--

--
-- Alter the `campaign_option` table
--
ALTER TABLE `campaign_option` ADD `share_reports_enabled` enum('yes','no') NOT NULL DEFAULT 'no';
ALTER TABLE `campaign_option` ADD `share_reports_password` varchar(64) NULL;

--
-- Table structure for table `email_blacklist_suggest`
--
CREATE TABLE IF NOT EXISTS `email_blacklist_suggest` (
  `email_id` INT(11) NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(150) NOT NULL,
  `ip_address` VARCHAR(15) NOT NULL,
  `user_agent` VARCHAR(255) NOT NULL,
  `date_added` DATETIME NOT NULL,
  `last_updated` DATETIME NOT NULL,
  PRIMARY KEY (`email_id`),
  UNIQUE INDEX `email` (`email` ASC))
ENGINE = InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

--
-- Table structure for table `user_message`
--
CREATE TABLE IF NOT EXISTS `user_message` (
  `message_id` INT NOT NULL AUTO_INCREMENT,
  `message_uid` CHAR(13) NOT NULL,
  `user_id` INT(11) NOT NULL,
  `title` VARCHAR(255) NULL,
  `message` TEXT NOT NULL,
  `title_translation_params` BLOB NULL,
  `message_translation_params` BLOB NULL,
  `status` CHAR(15) NOT NULL DEFAULT 'unseen',
  `date_added` DATETIME NOT NULL,
  `last_updated` DATETIME NOT NULL,
  PRIMARY KEY (`message_id`),
  INDEX `fk_user_message_user1_idx` (`user_id`),
  CONSTRAINT `fk_user_message_user1`
    FOREIGN KEY (`user_id`)
    REFERENCES `user` (`user_id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
ENGINE = InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

--
-- Alter `customer_message`
--
ALTER TABLE `customer_message` DROP `params`;
ALTER TABLE `customer_message` 
  ADD `title_translation_params` BLOB NULL AFTER `message`,
  ADD `message_translation_params` BLOB NULL AFTER `title_translation_params`;

--
-- Update `list_page_type` and `list_page`
--
UPDATE `list_page_type` SET `content` = REPLACE(content, 'box-primary', 'box-primary borderless');
UPDATE `list_page` SET `content` = REPLACE(content, 'box-primary', 'box-primary borderless');
UPDATE `list_page_type` SET `content` = REPLACE(content, '#367fa9', '#008ca9');
UPDATE `list_page` SET `content` = REPLACE(content, '#367fa9', '#008ca9');

--
-- Update `option`
--
INSERT INTO `option` (`category`, `key`, `value`, `is_serialized`, `date_added`, `last_updated`) VALUES
('system.customer_sending', 'quota_notify_email_content', 0x48656c6c6f205b46554c4c5f4e414d455d2c20c2a03c6272202f3e3c6272202f3e0a596f7572206d6178696d756d20616c6c6f7765642073656e64696e672071756f74612069732073657420746f205b51554f54415f544f54414c5d20656d61696c7320616e6420796f752063757272656e746c7920686176652073656e74205b51554f54415f55534147455d20656d61696c732c207768696368206d65616e7320796f7520686176652075736564205b51554f54415f55534147455f50455243454e545d206f6620796f757220616c6c6f7765642073656e64696e672071756f7461213c6272202f3e0a4f6e636520796f75722073656e64696e672071756f7461206973206f7665722c20796f752077696c6c206e6f742062652061626c6520746f2073656e6420616e7920656d61696c73213c6272202f3e3c6272202f3e0a506c65617365206d616b65207375726520796f752072656e657720796f75722073656e64696e672071756f74612e3c6272202f3e0a5468616e6b20796f7521, 0, '2016-10-31 17:55:19', '2016-10-31 18:43:04');


