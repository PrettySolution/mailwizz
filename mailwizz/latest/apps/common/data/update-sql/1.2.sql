--
-- Update sql for MailWizz EMA from version 1.1 to 1.2
--

-- 
-- Table structure for table `ip_location`
-- 

CREATE TABLE IF NOT EXISTS `ip_location` (
  `location_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `ip_address` char(15) NOT NULL,
  `country_code` char(3) NOT NULL,
  `country_name` varchar(150) NOT NULL,
  `zone_name` varchar(150) NULL,
  `city_name` varchar(150) NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`location_id`),
  UNIQUE KEY `ip_address_UNIQUE` (`ip_address`))
ENGINE = InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Alter the table `campaign_track_open`
--

ALTER TABLE `campaign_track_open` ADD `location_id` bigint(20) NULL DEFAULT NULL AFTER `subscriber_id`;
ALTER TABLE `campaign_track_open` ADD KEY `fk_campaign_track_open_ip_location1_idx` (`location_id`);
ALTER TABLE `campaign_track_open` 
    ADD CONSTRAINT `fk_campaign_track_open_ip_location1` FOREIGN KEY (`location_id`) REFERENCES `ip_location` (`location_id`) ON DELETE SET NULL ON UPDATE NO ACTION;

-- --------------------------------------------------------

--
-- Alter the table `campaign_track_url`
--

ALTER TABLE `campaign_track_url` ADD `location_id` bigint(20) NULL DEFAULT NULL AFTER `subscriber_id`;
ALTER TABLE `campaign_track_url` ADD KEY `fk_campaign_track_url_ip_location1_idx` (`location_id`);
ALTER TABLE `campaign_track_url` 
    ADD CONSTRAINT `fk_campaign_track_url_ip_location1` FOREIGN KEY (`location_id`) REFERENCES `ip_location` (`location_id`) ON DELETE SET NULL ON UPDATE NO ACTION;

-- --------------------------------------------------------

--
-- Alter the table `customer`
--

ALTER TABLE `customer` ADD `customer_uid` CHAR(13) NOT NULL AFTER `customer_id`;
ALTER TABLE `customer` ADD UNIQUE KEY `email_UNIQUE` (`email`);

-- --------------------------------------------------------

--
-- Alter the table `user`
--

ALTER TABLE `user` ADD `user_uid` CHAR(13) NOT NULL AFTER `user_id`;
ALTER TABLE `user` ADD UNIQUE KEY `email_UNIQUE` (`email`);

-- --------------------------------------------------------

--
-- Table structure for table `campaign_track_unsubscribe`
--

CREATE TABLE IF NOT EXISTS `campaign_track_unsubscribe` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `campaign_id` int(11) NOT NULL,
  `subscriber_id` int(11) NOT NULL,
  `location_id` bigint(20) DEFAULT NULL,
  `ip_address` char(15) NOT NULL,
  `user_agent` varchar(255) NOT NULL,
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_campaign_track_unsubscribe_campaign1_idx` (`campaign_id`),
  KEY `fk_campaign_track_unsubscribe_list_subscriber1_idx` (`subscriber_id`),
  KEY `fk_campaign_track_unsubscribe_ip_location1_idx` (`location_id`),
  KEY `date_added` (`date_added`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Constraints for table `campaign_track_unsubscribe`
--
ALTER TABLE `campaign_track_unsubscribe`
  ADD CONSTRAINT `fk_campaign_track_unsubscribe_campaign1` FOREIGN KEY (`campaign_id`) REFERENCES `campaign` (`campaign_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_campaign_track_unsubscribe_list_subscriber1` FOREIGN KEY (`subscriber_id`) REFERENCES `list_subscriber` (`subscriber_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_campaign_track_unsubscribe_ip_location1` FOREIGN KEY (`location_id`) REFERENCES `ip_location` (`location_id`) ON DELETE SET NULL ON UPDATE NO ACTION;

-- --------------------------------------------------------

--
-- Alter the table `list_subscriber`
--
ALTER TABLE `list_subscriber` ADD KEY `status_last_updated` (`status`, `last_updated`);

-- --------------------------------------------------------

--
-- Alter the table `campaign`
--
ALTER TABLE `campaign` ADD UNIQUE KEY `campaign_uid_UNIQUE` (`campaign_uid`);

-- --------------------------------------------------------

--
-- Alter the table `user_password_reset`
--
ALTER TABLE `user_password_reset` ADD KEY `key_status` (`reset_key`, `status`);

-- --------------------------------------------------------

--
-- Alter the table `user_password_reset`
--
ALTER TABLE `customer_password_reset` ADD KEY `key_status` (`reset_key`, `status`);

-- --------------------------------------------------------