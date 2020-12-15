--
-- Update sql for MailWizz EMA from version 1.3.4.7 to 1.3.4.8
--

--
-- Alter statement for `customer_email_template`
--

ALTER TABLE `customer_email_template` CHANGE `customer_id` `customer_id` INT(11) NULL;

-- --------------------------------------------------------

--
-- Alter statement for `sending_domain`
--

ALTER TABLE `sending_domain` ADD `signing_enabled` ENUM('yes','no') NOT NULL DEFAULT 'yes' AFTER `verified`;

-- --------------------------------------------------------

--
-- Alter statement for `campaign_delivery_log`
--

ALTER TABLE `campaign_delivery_log` ADD `email_message_id` VARCHAR(255) NULL AFTER `max_retries`, ADD KEY `email_message_id` (`email_message_id`);

-- --------------------------------------------------------
