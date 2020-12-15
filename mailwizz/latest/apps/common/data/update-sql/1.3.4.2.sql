--
-- Update sql for MailWizz EMA from version 1.3.4.1 to 1.3.4.2
--

ALTER TABLE `customer_email_template` ADD `minify` ENUM('yes','no') NOT NULL DEFAULT 'no' AFTER `inline_css`;
ALTER TABLE `campaign_option` ADD `embed_images` ENUM('yes','no') NOT NULL DEFAULT 'no' AFTER `xml_feed`;
ALTER TABLE `campaign_template` ADD `minify` ENUM('yes','no') NOT NULL DEFAULT 'no' AFTER `inline_css`;