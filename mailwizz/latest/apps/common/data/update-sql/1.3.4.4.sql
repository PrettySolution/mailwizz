--
-- Update sql for MailWizz EMA from version 1.3.4.3 to 1.3.4.4
--

--
-- Table structure for table `currency`
--

CREATE TABLE IF NOT EXISTS `currency` (
  `currency_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `code` char(3) NOT NULL,
  `value` decimal(15,8) NOT NULL DEFAULT '0.00000000',
  `is_default` enum('yes','no') NOT NULL DEFAULT 'no',
  `status` char(15) NOT NULL DEFAULT 'active',
  `date_added` datetime NOT NULL,
  `last_updated` datetime NOT NULL,
  PRIMARY KEY (`currency_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2;

-- --------------------------------------------------------

--
-- Dumping data for table `currency`
--

INSERT INTO `currency` (`currency_id`, `name`, `code`, `value`, `is_default`, `status`, `date_added`, `last_updated`) VALUES
(1, 'US Dollar', 'USD', '1.00000000', 'yes', 'active', '2014-05-17 00:00:00', '2014-05-17 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `price_plan`
--

CREATE TABLE IF NOT EXISTS `price_plan` (
  `plan_id` int(11) NOT NULL AUTO_INCREMENT,
  `plan_uid` char(13) NOT NULL,
  `group_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `price` decimal(5,2) NOT NULL DEFAULT '0.00',
  `description` text NOT NULL,
  `recommended` enum('yes','no') NOT NULL DEFAULT 'no',
  `status` char(15) NOT NULL DEFAULT 'active',
  `date_added` datetime NOT NULL,
  `last_updated` datetime NOT NULL,
  PRIMARY KEY (`plan_id`),
  UNIQUE KEY `plan_uid_UNIQUE` (`plan_uid`),
  KEY `fk_price_plan_customer_group1_idx` (`group_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

-- --------------------------------------------------------

--
-- Table structure for table `price_plan_order`
--

CREATE TABLE IF NOT EXISTS `price_plan_order` (
  `order_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_uid` char(13) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `plan_id` int(11) NOT NULL,
  `promo_code_id` int(11) DEFAULT NULL,
  `currency_id` int(11) NOT NULL,
  `subtotal` decimal(5,2) NOT NULL DEFAULT '0.00',
  `discount` decimal(5,2) NOT NULL DEFAULT '0.00',
  `total` decimal(5,2) NOT NULL DEFAULT '0.00',
  `status` char(15) NOT NULL DEFAULT 'incomplete',
  `date_added` datetime NOT NULL,
  `last_updated` datetime NOT NULL,
  PRIMARY KEY (`order_id`),
  UNIQUE KEY `order_uid_UNIQUE` (`order_uid`),
  KEY `fk_price_plan_order_price_plan1_idx` (`plan_id`),
  KEY `fk_price_plan_order_customer1_idx` (`customer_id`),
  KEY `fk_price_plan_order_price_plan_promo_code1_idx` (`promo_code_id`),
  KEY `fk_price_plan_order_currency1_idx` (`currency_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

-- --------------------------------------------------------

--
-- Table structure for table `price_plan_order_transaction`
--

CREATE TABLE IF NOT EXISTS `price_plan_order_transaction` (
  `transaction_id` int(11) NOT NULL AUTO_INCREMENT,
  `transaction_uid` char(13) NOT NULL,
  `order_id` int(11) NOT NULL,
  `payment_gateway_name` varchar(50) NOT NULL,
  `payment_gateway_transaction_id` varchar(100) NOT NULL,
  `payment_gateway_response` text NOT NULL,
  `status` char(15) NOT NULL DEFAULT 'failed',
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`transaction_id`),
  UNIQUE KEY `transaction_uid_UNIQUE` (`transaction_uid`),
  KEY `fk_price_plan_order_transaction_price_plan_order1_idx` (`order_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

-- --------------------------------------------------------

--
-- Table structure for table `price_plan_promo_code`
--

CREATE TABLE IF NOT EXISTS `price_plan_promo_code` (
  `promo_code_id` int(11) NOT NULL AUTO_INCREMENT,
  `code` char(15) NOT NULL,
  `type` enum('percentage','fixed amount') NOT NULL DEFAULT 'fixed amount',
  `discount` decimal(5,2) NOT NULL DEFAULT '0.00',
  `total_amount` decimal(5,2) NOT NULL DEFAULT '0.00',
  `total_usage` tinyint(4) NOT NULL DEFAULT '0',
  `customer_usage` tinyint(4) NOT NULL DEFAULT '0',
  `date_start` date NOT NULL,
  `date_end` date NOT NULL,
  `status` char(15) NOT NULL DEFAULT 'active',
  `date_added` datetime NOT NULL,
  `last_updated` datetime NOT NULL,
  PRIMARY KEY (`promo_code_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

-- --------------------------------------------------------

--
-- Constraints for table `price_plan`
--
ALTER TABLE `price_plan`
  ADD CONSTRAINT `fk_price_plan_customer_group1` FOREIGN KEY (`group_id`) REFERENCES `customer_group` (`group_id`) ON DELETE CASCADE ON UPDATE NO ACTION;
  
--
-- Constraints for table `price_plan_order`
--
ALTER TABLE `price_plan_order`
  ADD CONSTRAINT `fk_price_plan_order_price_plan1` FOREIGN KEY (`plan_id`) REFERENCES `price_plan` (`plan_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_price_plan_order_customer1` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_price_plan_order_price_plan_promo_code1` FOREIGN KEY (`promo_code_id`) REFERENCES `price_plan_promo_code` (`promo_code_id`) ON DELETE SET NULL ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_price_plan_order_currency1` FOREIGN KEY (`currency_id`) REFERENCES `currency` (`currency_id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `price_plan_order_transaction`
--
ALTER TABLE `price_plan_order_transaction`
  ADD CONSTRAINT `fk_price_plan_order_transaction_price_plan_order1` FOREIGN KEY (`order_id`) REFERENCES `price_plan_order` (`order_id`) ON DELETE CASCADE ON UPDATE NO ACTION;

-- 
ALTER TABLE `campaign_option` 
    ADD `autoresponder_open_campaign_id` INT( 11 ) NULL AFTER `autoresponder_time_value`,
    ADD KEY `fk_campaign_option_campaign2_idx` (`autoresponder_open_campaign_id`),
    ADD CONSTRAINT `fk_campaign_option_campaign2` FOREIGN KEY (`autoresponder_open_campaign_id`) REFERENCES `campaign` (`campaign_id`) ON DELETE SET NULL ON UPDATE NO ACTION;
