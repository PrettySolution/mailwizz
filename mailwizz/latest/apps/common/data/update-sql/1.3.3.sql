--
-- Update sql for MailWizz EMA from version 1.3.2 to 1.3.3
--

-- --------------------------------------------------------

ALTER TABLE `list_default` ADD `from_email` VARCHAR(100) NOT NULL AFTER `from_name` ;
UPDATE `list_default` SET `from_email` = `reply_to`;

ALTER TABLE `campaign` ADD `from_email` VARCHAR(100) NOT NULL AFTER `from_name`;
UPDATE `campaign` SET `from_email` = `reply_to`;

ALTER TABLE `delivery_server` ADD `custom_from_header` ENUM('no','yes') NOT NULL DEFAULT 'no' AFTER `hourly_sent` ;

ALTER TABLE `campaign_template` ADD `plain_text` TEXT NULL DEFAULT NULL AFTER `inline_css` ;
ALTER TABLE `campaign_template` ADD `auto_plain_text` ENUM('yes','no') NOT NULL DEFAULT 'yes' AFTER `plain_text` ;

INSERT INTO `tag_registry` (`tag_id`, `tag`, `description`, `date_added`, `last_updated`) VALUES
(NULL, '[CAMPAIGN_FROM_EMAIL]', NULL, '2014-02-02 00:00:00', '2014-02-02 00:00:00'),
(NULL, '[LIST_FROM_EMAIL]', NULL, '2014-02-02 00:00:00', '2014-02-02 00:00:00');

-- --------------------------------------------------------