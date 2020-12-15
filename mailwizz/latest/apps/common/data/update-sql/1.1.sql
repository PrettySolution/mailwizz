--
-- Update sql for MailWizz EMA from version 1.0 to 1.1
--

--
-- Table structure for table `language`
--

CREATE TABLE IF NOT EXISTS `language` (
  `language_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `language_code` char(2) NOT NULL,
  `region_code` char(2) DEFAULT NULL,
  `is_default` enum('yes','no') NOT NULL DEFAULT 'no',
  `date_added` datetime NOT NULL,
  `last_updated` datetime NOT NULL,
  PRIMARY KEY (`language_id`),
  KEY `is_default` (`is_default`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Alter the table `customer`
--

ALTER TABLE `customer` ADD `language_id` int(11) NULL DEFAULT NULL AFTER `customer_id`;
ALTER TABLE `customer` ADD KEY `fk_customer_language1_idx` (`language_id` ASC);
ALTER TABLE `customer` 
    ADD CONSTRAINT `fk_customer_language1` FOREIGN KEY (`language_id`) REFERENCES `language` (`language_id`) ON DELETE SET NULL ON UPDATE NO ACTION;

-- --------------------------------------------------------

--
-- Alter the table `user`
--

ALTER TABLE `user` ADD `language_id` int(11) NULL DEFAULT NULL AFTER `user_id`;
ALTER TABLE `user` ADD KEY `fk_user_language1_idx` (`language_id` ASC);
ALTER TABLE `user` 
    ADD CONSTRAINT `fk_user_language1` FOREIGN KEY (`language_id`) REFERENCES `language` (`language_id`) ON DELETE SET NULL ON UPDATE NO ACTION;

-- --------------------------------------------------------