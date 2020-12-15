--
-- Update sql for MailWizz EMA from version 1.3.7 to 1.3.7.1
--

--
-- Alter the `delivery_server` table
--
ALTER TABLE `delivery_server` ADD `force_sender` ENUM('yes','no') NOT NULL DEFAULT 'no' AFTER `force_reply_to`;

--
-- Alter the `campaign_option` table
--
ALTER TABLE `campaign_option` ADD `preheader` varchar(255) NULL;
