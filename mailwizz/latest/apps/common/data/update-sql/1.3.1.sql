--
-- Update sql for MailWizz EMA from version 1.3 to 1.3.1
--

--
-- Alter `campaign_option` table
--

ALTER TABLE `campaign_option` ADD `plain_text_email` enum('yes', 'no') NOT NULL DEFAULT 'yes' AFTER `xml_feed`;
ALTER TABLE `campaign_option` ADD `email_stats` varchar(100) DEFAULT NULL AFTER `plain_text_email`;

-- --------------------------------------------------------

--
-- Alter `campaign` table
--

ALTER TABLE `campaign` DROP `last_open`;

-- --------------------------------------------------------

--
-- Alter `delivery_server` table
--

ALTER TABLE `delivery_server` ADD KEY `idx_gen0` (`status`, `hourly_quota`, `hourly_sent`, `probability`, `last_sent`);

-- --------------------------------------------------------