--
-- Update sql for MailWizz EMA from version 1.3.6.1 to 1.3.6.2
--

--
-- Table price_plan
--
ALTER TABLE `price_plan` ADD `sort_order` INT(11) NOT NULL DEFAULT '0' AFTER `recommended`;
ALTER TABLE `price_plan` ADD `visible` enum('yes','no') NOT NULL DEFAULT 'yes' AFTER `recommended`;

--
-- Table delivery_server
--
ALTER TABLE `delivery_server` ADD `monthly_quota` INT(11) NOT NULL DEFAULT '0' AFTER `hourly_quota`;

--
-- Table customer_api_key
--
ALTER TABLE `customer_api_key` ADD `ip_whitelist` VARCHAR(255) DEFAULT NULL AFTER `private`;
ALTER TABLE `customer_api_key` ADD `ip_blacklist` VARCHAR(255) DEFAULT NULL AFTER `ip_whitelist`;

--
-- Table campaign_track_unsubscribe
--
ALTER TABLE `campaign_track_unsubscribe` ADD `reason` VARCHAR(255) NULL DEFAULT NULL AFTER `user_agent`;

--
-- Table list_page_type
--
UPDATE `list_page_type` SET `content` = '<div class="box box-primary">\n<div class="box-header">\n<h3 class="box-title">[LIST_NAME]</h3>\n</div>\n\n<div class="box-body">\n<div class="callout callout-info">We''re sorry to see you go, but hey, no hard feelings, hopefully we will see you back one day.<br />\nPlease fill in your email address in order to unsubscribe from the list.<br />\nYou will receive an email to confirm your unsubscription, just to make sure this is not an accident or somebody else tries to unsubscribe you.</div>\n[UNSUBSCRIBE_EMAIL_FIELD]<br />\n[UNSUBSCRIBE_REASON_FIELD]</div>\n\n<div class="box-footer">\n<div class="pull-right">[SUBMIT_BUTTON]</div>\n\n<div class="clearfix"> </div>\n</div>\n</div>\n' WHERE `type_id` = 5;

--
-- Table list
--
ALTER TABLE `list` ADD `subscriber_require_approval` ENUM('yes','no') NOT NULL DEFAULT 'no' AFTER `removable`;
ALTER TABLE `list` CHANGE `name` `name` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL, CHANGE `display_name` `display_name` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;

--
-- Table campaign_option
--
ALTER TABLE `campaign_option` ADD `giveup_counter` INT(11) NOT NULL DEFAULT '0' AFTER `blocked_reason`;

--
-- Table list_page_type
--
INSERT INTO `list_page_type` (`type_id`, `name`, `slug`, `description`, `content`, `full_html`, `meta_data`, `date_added`, `last_updated`) VALUES 
  (10, 'Subscription confirmed approval', 'subscribe-confirm-approval', 'After the user will click the confirmation link from within the email, if the list requires confirm approval, he will see this page.', '<div class="box box-primary"> <div class="box-header"> <h3 class="box-title">[LIST_NAME]</h3> </div> <div class="box-body"> <div class="callout callout-info">Congratulations, your subscription is now complete and awaiting approval.<br /> Once the approval process is done, you will get a confirmation email with further instructions.<br />Thanks.</div> </div> </div> ', 'no', 0x613a303a7b7d, '2016-04-04 21:48:48', '2016-04-04 14:54:24'),
  (11, 'Subscription confirmed approval email', 'subscribe-confirm-approval-email', 'The email the user receives after his subscription is approved.', '<!DOCTYPE html>\r\n<html><head><title>[LIST_NAME]</title><meta content="utf-8" name="charset">\r\n<style type="text/css">\r\n#outlook a{padding:0;}\r\n	body {width:100% !important; -webkit-text-size-adjust:none; margin:0; padding:0; font-family:  sans-serif; background: #f5f5f5; font-size:12px;}\r\n	img {border:0;height:auto;line-height:100%;outline:none;text-decoration:none;}\r\n	table td{border-collapse:collapse;}\r\n	a {color: #367fa9;text-decoration:none}\r\n	a:hover {color: #367fa9;text-decoration:none;}\r\n	#wrap {background:#f5f5f5; padding:10px;}\r\n	table#main-table {-webkit-border-radius:5px;-moz-border-radius:5px;border-radius:5px; border:1px solid #367fa9; overflow:hidden; background: #FFFFFF; width: 600px}\r\n	h1{padding:0; margin:0; font-family: sans-serif;font-size:25px;font-style:italic;color:#FFFFFF; font-weight:bold;}\r\n	h1 small{font-size:13px;font-weight:normal; font-family:  sans-serif; font-style:italic;}\r\n	h6{font-size:10px;color:#FFFFFF;margin:0;padding:0;font-weight:normal}\r\n	.darkbg {background: #367fa9}\r\n	input{outline:none}\r\n</style>\r\n</head><body style="width:100%;-webkit-text-size-adjust:none;margin:0;padding:0;font-family:sans-serif;background:#f5f5f5;font-size:12px">\r\n                \r\n            <div id="wrap" style="background:#f5f5f5;padding:10px">\r\n<table align="center" border="0" cellpadding="0" cellspacing="0" id="main-table" style="-webkit-border-radius:5px;-moz-border-radius:5px;border-radius:5px;border:1px solid #367fa9;overflow:hidden;background:#FFFFFF;width:600px"><tbody><tr><td class="darkbg" style="border-collapse:collapse;background:#367fa9">\r\n			<table border="0" cellpadding="0" cellspacing="20" width="100%"><tbody><tr><td style="border-collapse:collapse">\r\n						<h1 style="padding:0;margin:0;font-family:sans-serif;font-size:25px;font-style:italic;color:#FFFFFF;font-weight:bold">[LIST_NAME] <small style="font-size:13px;font-weight:normal;font-family:sans-serif;font-style:italic">[COMPANY_NAME]</small></h1>\r\n						</td>\r\n					</tr></tbody></table></td>\r\n		</tr><tr><td style="border-collapse:collapse">\r\n			<table border="0" cellpadding="20" cellspacing="0" width="100%"><tbody><tr><td style="border-collapse:collapse">&nbsp;</td>\r\n					</tr><tr><td style="border-collapse:collapse">Congratulations, <br />Your subscription into [LIST_NAME] email list is now approved.<br>\r\n						You can update your information at any time by clicking <a href="[UPDATE_PROFILE_URL]" style="color:#367fa9;text-decoration:none">here</a>.<br>\r\n						Thank you.</td>\r\n					</tr><tr><td style="border-collapse:collapse">&nbsp;</td>\r\n					</tr></tbody></table></td>\r\n		</tr><tr><td class="darkbg" style="padding:10px;border-collapse:collapse;background:#367fa9">\r\n			<h6 style="font-size:10px;color:#FFFFFF;margin:0;padding:0;font-weight:normal">&copy; [CURRENT_YEAR] [COMPANY_NAME]. All rights reserved</h6>\r\n			</td>\r\n		</tr></tbody></table></div></body></html>\r\n', 'yes', 0x613a303a7b7d, '2013-09-05 13:39:56', '2015-03-19 11:13:09');


--
-- Table customer_email_blacklist
--
CREATE TABLE IF NOT EXISTS `customer_email_blacklist` (
  `email_id` INT NOT NULL AUTO_INCREMENT,
  `email_uid` CHAR(13) NOT NULL,
  `customer_id` INT(11) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `reason` VARCHAR(255) NULL DEFAULT NULL,
  `date_added` DATETIME NOT NULL,
  `last_updated` DATETIME NOT NULL,
  PRIMARY KEY (`email_id`),
  UNIQUE KEY `unique_id_UNIQUE` (`email_uid`),
  UNIQUE KEY `customer_id_email_UNIQUE` (`customer_id`, `email`),
  INDEX `fk_customer_email_blacklist_customer1_idx` (`customer_id` ASC),
  CONSTRAINT `fk_customer_email_blacklist_customer1`
  FOREIGN KEY (`customer_id`)
  REFERENCES `customer` (`customer_id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
  ENGINE = InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

--
-- Table list_subscriber_field_cache
--
CREATE TABLE IF NOT EXISTS `list_subscriber_field_cache` (
  `subscriber_id` INT(11) NOT NULL,
  `data` LONGBLOB NOT NULL,
  INDEX `fk_list_subscriber_field_cache_list_subscriber1_idx` (`subscriber_id` ASC),
  PRIMARY KEY (`subscriber_id`),
  CONSTRAINT `fk_list_subscriber_field_cache_list_subscriber1`
  FOREIGN KEY (`subscriber_id`)
  REFERENCES `list_subscriber` (`subscriber_id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
  ENGINE = InnoDB DEFAULT CHARSET=utf8;

--
-- Table customer_login_log
--
CREATE TABLE IF NOT EXISTS `customer_login_log` (
  `log_id` BIGINT(20) NOT NULL AUTO_INCREMENT,
  `customer_id` INT(11) NOT NULL,
  `location_id` BIGINT(20) NULL,
  `ip_address` CHAR(15) NOT NULL,
  `user_agent` VARCHAR(255) NOT NULL,
  `date_added` DATETIME NOT NULL,
  PRIMARY KEY (`log_id`),
  INDEX `fk_customer_login_log_customer1_idx` (`customer_id` ASC),
  INDEX `fk_customer_login_log_ip_location1_idx` (`location_id` ASC),
  CONSTRAINT `fk_customer_login_log_customer1`
  FOREIGN KEY (`customer_id`)
  REFERENCES `customer` (`customer_id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_customer_login_log_ip_location1`
  FOREIGN KEY (`location_id`)
  REFERENCES `ip_location` (`location_id`)
    ON DELETE SET NULL
    ON UPDATE NO ACTION)
  ENGINE = InnoDB DEFAULT CHARSET=utf8;

--
-- Table campaign_bounce_log
--
DELETE FROM `campaign_bounce_log` WHERE log_id NOT IN (SELECT * FROM (SELECT MAX(n.log_id) FROM `campaign_bounce_log` n GROUP BY n.campaign_id, n.subscriber_id) x);
ALTER TABLE `campaign_bounce_log` ADD UNIQUE KEY `cid_sid` (`campaign_id`, `subscriber_id`);