--
-- Update sql for MailWizz EMA from version 1.5.2 to 1.5.3
--

-- 
-- Table structure for table `campaign_extra_tag`
-- 
CREATE TABLE IF NOT EXISTS `campaign_extra_tag` (
  `tag_id` INT NOT NULL AUTO_INCREMENT,
  `campaign_id` INT(11) NOT NULL,
  `tag` VARCHAR(50) NOT NULL,
  `content` TEXT NOT NULL,
  `date_added` DATETIME NOT NULL,
  `last_updated` DATETIME NOT NULL,
  PRIMARY KEY (`tag_id`),
  KEY `fk_campaign_specific_tag_campaign1_idx` (`campaign_id`),
  CONSTRAINT `fk_campaign_extra_tag_campaign1` 
    FOREIGN KEY (`campaign_id`) 
    REFERENCES `campaign` (`campaign_id`) 
    ON DELETE CASCADE 
    ON UPDATE NO ACTION
) ENGINE = InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

-- --------------------------------------------------------

--
-- Modify the password length for bounce / email box monitor and fbl servers 
--
ALTER TABLE `bounce_server` CHANGE `password` `password` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL; 
ALTER TABLE `email_box_monitor` CHANGE `password` `password` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;  
ALTER TABLE `feedback_loop_server` CHANGE `password` `password` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;

--
-- Modify the campaign template table 
--
ALTER TABLE `campaign_template` ADD `name` VARCHAR(255) NULL DEFAULT NULL AFTER `campaign_id`; 

--
-- Modify the customer table 
--
ALTER TABLE `customer` ADD `birth_date` DATE NULL DEFAULT NULL AFTER `status`;

