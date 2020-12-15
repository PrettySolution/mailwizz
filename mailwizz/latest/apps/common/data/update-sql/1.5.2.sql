--
-- Update sql for MailWizz EMA from version 1.5.1 to 1.5.2
--

ALTER TABLE `ip_location` 
  ADD `timezone` VARCHAR(100) NULL DEFAULT NULL AFTER `longitude`, 
  ADD `timezone_offset` INT(11) NULL DEFAULT NULL AFTER `timezone`;

ALTER TABLE `campaign_option` 
  ADD `timewarp_enabled` ENUM('no','yes') NOT NULL DEFAULT 'no' AFTER `preheader`, 
  ADD `timewarp_hour` INT(2) NOT NULL DEFAULT '0' AFTER `timewarp_enabled`, 
  ADD `timewarp_minute` INT(2) NOT NULL DEFAULT '0' AFTER `timewarp_hour`;

ALTER TABLE `campaign_abuse_report` CHANGE `ip_address` `ip_address` VARCHAR(45) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
ALTER TABLE `campaign_forward_friend` CHANGE `ip_address` `ip_address` VARCHAR(45) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
ALTER TABLE `ip_location` CHANGE `ip_address` `ip_address` VARCHAR(45) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
ALTER TABLE `campaign_track_open` CHANGE `ip_address` `ip_address` VARCHAR(45) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
ALTER TABLE `campaign_track_unsubscribe` CHANGE `ip_address` `ip_address` VARCHAR(45) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
ALTER TABLE `campaign_track_url` CHANGE `ip_address` `ip_address` VARCHAR(45) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
ALTER TABLE `customer_password_reset` CHANGE `ip_address` `ip_address` VARCHAR(45) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
ALTER TABLE `email_blacklist_suggest` CHANGE `ip_address` `ip_address` VARCHAR(45) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
ALTER TABLE `user_password_reset` CHANGE `ip_address` `ip_address` VARCHAR(45) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
ALTER TABLE `customer_login_log` CHANGE `ip_address` `ip_address` VARCHAR(45) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
ALTER TABLE `list_subscriber` CHANGE `ip_address` `ip_address` VARCHAR(45) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;

ALTER TABLE `campaign_option` ADD `email_stats_sent` TINYINT(1) NOT NULL DEFAULT '0' AFTER `email_stats`;
UPDATE `campaign_option` SET `email_stats_sent` = 1 WHERE LENGTH(`email_stats`) > 0;

