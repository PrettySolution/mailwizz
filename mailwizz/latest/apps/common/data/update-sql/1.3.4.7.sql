--
-- Update sql for MailWizz EMA from version 1.3.4.6 to 1.3.4.7
--

--
-- Alter statement for `customer_email_template`
--

ALTER TABLE `customer_email_template` CHANGE `customer_id` `customer_id` INT(11) NULL;

-- --------------------------------------------------------

--
-- Table structure for table `company_type`
--

CREATE TABLE IF NOT EXISTS `company_type` (
  `type_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `date_added` datetime NOT NULL,
  `last_updated` datetime NOT NULL,
  PRIMARY KEY (`type_id`),
  UNIQUE KEY `name_UNIQUE` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Alter statement for `customer_company`
--

ALTER TABLE `customer_company` 
    ADD `type_id` INT NULL AFTER `customer_id`, 
    ADD KEY `fk_customer_company_company_type1_idx`(`type_id`), 
    ADD CONSTRAINT `fk_customer_company_company_type1` FOREIGN KEY (`type_id`) REFERENCES `company_type` (`type_id`) ON DELETE SET NULL ON UPDATE NO ACTION;

-- --------------------------------------------------------

--
-- Alter statement for `list_company`
--   

ALTER TABLE `list_company` 
    ADD `type_id` INT NULL AFTER `list_id`, 
    ADD KEY `fk_list_company_company_type1_idx`(`type_id`), 
    ADD CONSTRAINT `fk_list_company_company_type1` FOREIGN KEY (`type_id`) REFERENCES `company_type` (`type_id`) ON DELETE SET NULL ON UPDATE NO ACTION;

-- --------------------------------------------------------

--
-- Dumping data for table `company_type`
--
    
INSERT INTO `company_type` (`type_id`, `name`, `date_added`, `last_updated`) VALUES
(NULL, 'Agriculture and Food Services', '2014-08-31 09:45:05', '2014-08-31 09:45:05'),
(NULL, 'Architecture and Construction', '2014-08-31 09:45:05', '2014-08-31 09:45:05'),
(NULL, 'Arts and Artists', '2014-08-31 09:45:05', '2014-08-31 09:45:05'),
(NULL, 'Beauty and Personal Care', '2014-08-31 09:45:05', '2014-08-31 09:45:05'),
(NULL, 'Business and Finance', '2014-08-31 09:45:05', '2014-08-31 09:45:05'),
(NULL, 'Computers and Electronics', '2014-08-31 09:45:05', '2014-08-31 09:45:05'),
(NULL, 'Construction ', '2014-08-31 09:45:05', '2014-08-31 09:45:05'),
(NULL, 'Consulting', '2014-08-31 09:45:05', '2014-08-31 09:45:05'),
(NULL, 'Creative Services/Agency', '2014-08-31 09:45:05', '2014-08-31 09:45:05'),
(NULL, 'Daily Deals/E-Coupons', '2014-08-31 09:45:05', '2014-08-31 09:45:05'),
(NULL, 'eCommerce', '2014-08-31 09:45:05', '2014-08-31 09:45:05'),
(NULL, 'Education and Training', '2014-08-31 09:45:05', '2014-08-31 09:45:05'),
(NULL, 'Entertainment and Events', '2014-08-31 09:45:05', '2014-08-31 09:45:05'),
(NULL, 'Gambling', '2014-08-31 09:45:05', '2014-08-31 09:45:05'),
(NULL, 'Games', '2014-08-31 09:45:05', '2014-08-31 09:45:05'),
(NULL, 'Government', '2014-08-31 09:45:05', '2014-08-31 09:45:05'),
(NULL, 'Health and Fitness', '2014-08-31 09:45:05', '2014-08-31 09:45:05'),
(NULL, 'Hobbies', '2014-08-31 09:45:05', '2014-08-31 09:45:05'),
(NULL, 'Home and Garden', '2014-08-31 09:45:05', '2014-08-31 09:45:05'),
(NULL, 'Insurance', '2014-08-31 09:45:05', '2014-08-31 09:45:05'),
(NULL, 'Legal', '2014-08-31 09:45:05', '2014-08-31 09:45:05'),
(NULL, 'Manufacturing', '2014-08-31 09:45:05', '2014-08-31 09:45:05'),
(NULL, 'Marketing and Advertising', '2014-08-31 09:45:05', '2014-08-31 09:45:05'),
(NULL, 'Media and Publishing', '2014-08-31 09:45:05', '2014-08-31 09:45:05'),
(NULL, 'Medical, Dental, and Healthcare', '2014-08-31 09:45:05', '2014-08-31 09:45:05'),
(NULL, 'Mobile', '2014-08-31 09:45:05', '2014-08-31 09:45:05'),
(NULL, 'Music and Musicians', '2014-08-31 09:45:05', '2014-08-31 09:45:05'),
(NULL, 'Non-Profit', '2014-08-31 09:45:05', '2014-08-31 09:45:05'),
(NULL, 'Pharmaceuticals', '2014-08-31 09:45:05', '2014-08-31 09:45:05'),
(NULL, 'Photo and Video', '2014-08-31 09:45:05', '2014-08-31 09:45:05'),
(NULL, 'Politics', '2014-08-31 09:45:05', '2014-08-31 09:45:05'),
(NULL, 'Professional Services', '2014-08-31 09:45:05', '2014-08-31 09:45:05'),
(NULL, 'Public Relations', '2014-08-31 09:45:05', '2014-08-31 09:45:05'),
(NULL, 'Real Estate', '2014-08-31 09:45:05', '2014-08-31 09:45:05'),
(NULL, 'Recruitment and Staffing', '2014-08-31 09:45:05', '2014-08-31 09:45:05'),
(NULL, 'Religion', '2014-08-31 09:45:05', '2014-08-31 09:45:05'),
(NULL, 'Restaurant and Venue', '2014-08-31 09:45:05', '2014-08-31 09:45:05'),
(NULL, 'Retail', '2014-08-31 09:45:05', '2014-08-31 09:45:05'),
(NULL, 'Social Networks and Online Communities', '2014-08-31 09:45:05', '2014-08-31 09:45:05'),
(NULL, 'Software and Web App', '2014-08-31 09:45:05', '2014-08-31 09:45:05'),
(NULL, 'Sports', '2014-08-31 09:45:05', '2014-08-31 09:45:05'),
(NULL, 'Telecommunications', '2014-08-31 09:45:05', '2014-08-31 09:45:05'),
(NULL, 'Travel and Transportation', '2014-08-31 09:45:05', '2014-08-31 09:45:05'),
(NULL, 'Vitamin supplements', '2014-08-31 09:45:05', '2014-08-31 09:45:05'),
(NULL, 'Other', '2014-08-31 09:45:05', '2014-08-31 09:45:05');

-- --------------------------------------------------------

--
-- Tag insertion into registry
--

INSERT INTO `tag_registry` (`tag_id`, `tag`, `description`, `date_added`, `last_updated`) VALUES 
(NULL, '[FORWARD_FRIEND_URL]', NULL, '2014-08-31 00:00:00', '2014-08-31 00:00:00'),
(NULL, '[CAMPAIGN_NAME]', NULL, '2014-08-31 00:00:00', '2014-08-31 00:00:00'),
(NULL, '[DIRECT_UNSUBSCRIBE_URL]', NULL, '2014-08-31 00:00:00', '2014-08-31 00:00:00');

--
-- Table structure for table `campaign_forward_friend`
--

CREATE TABLE IF NOT EXISTS `campaign_forward_friend` (
  `forward_id` INT NOT NULL AUTO_INCREMENT,
  `campaign_id` INT(11) NOT NULL,
  `subscriber_id` INT(11) NULL,
  `to_email` VARCHAR(150) NOT NULL,
  `to_name` VARCHAR(150) NOT NULL,
  `from_email` VARCHAR(150) NOT NULL,
  `from_name` VARCHAR(150) NOT NULL,
  `subject` VARCHAR(255) NOT NULL,
  `message` TEXT NOT NULL,
  `ip_address` CHAR(15) NOT NULL,
  `user_agent` VARCHAR(255) NOT NULL,
  `date_added` DATETIME NOT NULL,
  `last_updated` DATETIME NOT NULL,
  PRIMARY KEY (`forward_id`),
  KEY `fk_campaign_forward_friend_campaign1_idx` (`campaign_id` ASC),
  KEY `fk_campaign_forward_friend_list_subscriber1_idx` (`subscriber_id` ASC),
  CONSTRAINT `fk_campaign_forward_friend_campaign1`
    FOREIGN KEY (`campaign_id`)
    REFERENCES `campaign` (`campaign_id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_campaign_forward_friend_list_subscriber1`
    FOREIGN KEY (`subscriber_id`)
    REFERENCES `list_subscriber` (`subscriber_id`)
    ON DELETE SET NULL
    ON UPDATE NO ACTION)
ENGINE = InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------


--- 
--- Add avatar column to user and customer table
---

ALTER TABLE `user` ADD `avatar` VARCHAR(255) NULL AFTER `timezone`;
ALTER TABLE `customer` ADD `avatar` VARCHAR(255) NULL AFTER `timezone`;

--
-- Alter statement for `delivery_server`
--

ALTER TABLE `delivery_server` ADD `name` VARCHAR(150) NULL AFTER `type`;

-- --------------------------------------------------------

--- 
--- Alter statement for `customer`
---

ALTER TABLE `customer` 
    ADD `oauth_uid` bigint(20) NULL AFTER `confirmation_key`, 
    ADD `oauth_provider` char(10) NULL AFTER `oauth_uid`,
    ADD KEY `oauth` (`oauth_uid`, `oauth_provider`);

-- --------------------------------------------------------

--
-- Table structure for table `sending_domain`
--

CREATE TABLE IF NOT EXISTS `sending_domain` (
  `domain_id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) DEFAULT NULL,
  `name` varchar(64) NOT NULL,
  `dkim_private_key` text NOT NULL,
  `dkim_public_key` text NOT NULL,
  `locked` enum('yes','no') NOT NULL DEFAULT 'no',
  `verified` enum('yes','no') NOT NULL DEFAULT 'no',
  `date_added` datetime NOT NULL,
  `last_updated` datetime NOT NULL,
  PRIMARY KEY (`domain_id`), 
  KEY `fk_sending_domain_customer1_idx` (`customer_id`),
  KEY `name_verified_customer` (`name`, `verified`, `customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

  
--
-- Constraints for table `sending_domain`
--
ALTER TABLE `sending_domain`
    ADD CONSTRAINT `fk_sending_domain_customer1` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE ON UPDATE NO ACTION;
    
-- --------------------------------------------------------
