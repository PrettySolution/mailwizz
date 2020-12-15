--
-- Update sql for MailWizz EMA from version 1.3.9.4 to 1.3.9.5
--

-- 
-- Table `campaign_random_content`
-- 
CREATE TABLE IF NOT EXISTS `campaign_random_content` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `campaign_id` INT(11) NOT NULL,
  `name` VARCHAR(50) NOT NULL,
  `content` TEXT NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_campaign_random_content_campaign1_idx` (`campaign_id`),
  UNIQUE KEY `campaign_id_name` (`campaign_id`, `name`),
  CONSTRAINT `fk_campaign_random_content_campaign1`
    FOREIGN KEY (`campaign_id`)
    REFERENCES `campaign` (`campaign_id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION
) ENGINE = InnoDB DEFAULT CHARSET=utf8;

-- 
-- Table `start_page`
-- 
ALTER TABLE `start_page` 
  CHANGE `icon` `icon` VARCHAR(255) NULL, 
  CHANGE `heading` `heading` VARCHAR(255) NULL,
  CHANGE `content` `content` TEXT NULL;

-- 
-- Table `list_url_import`
-- 
CREATE TABLE IF NOT EXISTS `list_url_import` (
  `url_id` INT NOT NULL AUTO_INCREMENT,
  `list_id` INT(11) NOT NULL,
  `url` VARCHAR(255) NOT NULL,
  `failures` TINYINT(1) NOT NULL DEFAULT 0, 
  `status` CHAR(15) NOT NULL DEFAULT 'active',
  `date_added` DATETIME NOT NULL,
  `last_updated` DATETIME NOT NULL,
  PRIMARY KEY (`url_id`),
  KEY `fk_list_url_import_list1_idx` (`list_id`),
  CONSTRAINT `fk_list_url_import_list1`
  FOREIGN KEY (`list_id`)
  REFERENCES `list` (`list_id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
ENGINE = InnoDB DEFAULT CHARSET=utf8;

-- 
-- Table `customer`
-- 
ALTER TABLE `customer` ADD `last_login` DATETIME NULL DEFAULT NULL AFTER `last_updated`;

-- 
-- Table `user`
-- 
ALTER TABLE `user` ADD `last_login` DATETIME NULL DEFAULT NULL AFTER `last_updated`;

--
-- Insert into `start_page`
--
INSERT INTO `start_page` (`page_id`, `application`, `route`, `icon`, `icon_color`, `heading`, `content`, `date_added`, `last_updated`) VALUES
(NULL, 'customer', 'account/disable', 'glyphicon-ban-circle', '', 'Disable my account', 'Once you disable your account, all your lists, segments, campaigns and subscribers will be removed from our system.<br />\nWe will keep your account disabled for a period of time and if you don\'t login anymore, we will simply remove it for good.<br />\nYou can reactivate your account at any time by simply logging into the system.<br /><br /><button class=\"btn btn-danger btn-flat\" type=\"submit\" value=\"1\"><span class=\"glyphicon glyphicon-ban-circle\"> </span>Disable account</button>', '2017-03-28 07:06:04', '2017-03-28 07:13:08'),
(NULL, 'backend', 'email_templates_categories/index', 'glyphicon-book', '', 'Create first template category', 'You can categorize the email templates so that it will be easier to group and find them.', '2017-03-30 07:13:29', '2017-03-30 07:14:32'),
(NULL, 'customer', 'templates_categories/index', 'glyphicon-book', '', 'Create your first template category', 'You can categorize the email templates so that it will be easier to group and find them.', '2017-03-30 07:15:31', '2017-03-30 07:15:31');

-- 
-- Table `customer_email_template_category`
-- 
CREATE TABLE IF NOT EXISTS `customer_email_template_category` (
  `category_id` INT NOT NULL AUTO_INCREMENT,
  `customer_id` INT(11) NULL,
  `name` VARCHAR(255) NOT NULL,
  `date_added` DATETIME NOT NULL,
  `last_updated` DATETIME NOT NULL,
  PRIMARY KEY (`category_id`),
  INDEX `fk_customer_email_template_category_customer1_idx` (`customer_id`),
  CONSTRAINT `fk_customer_email_template_category_customer1`
  FOREIGN KEY (`customer_id`)
  REFERENCES `customer` (`customer_id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION
) ENGINE = InnoDB DEFAULT CHARSET=utf8;

-- 
-- Table `customer_email_template`
-- 
ALTER TABLE `customer_email_template` ADD `category_id` INT NULL AFTER `customer_id`; 
ALTER TABLE `customer_email_template` 
  ADD KEY `fk_customer_email_template_customer_email_template_category_idx` (`category_id`),
  ADD CONSTRAINT `fk_customer_email_template_customer_email_template_category1` FOREIGN KEY (`category_id`) REFERENCES `customer_email_template_category` (`category_id`) ON DELETE SET NULL ON UPDATE NO ACTION;

-- 
-- Table `campaign_option`
-- 
ALTER TABLE `campaign_option` 
  ADD `cronjob_max_runs` INT(11) NOT NULL DEFAULT '-1' AFTER `cronjob_enabled`, 
  ADD `cronjob_runs_counter` INT(11) NOT NULL DEFAULT '0' AFTER `cronjob_max_runs`;
