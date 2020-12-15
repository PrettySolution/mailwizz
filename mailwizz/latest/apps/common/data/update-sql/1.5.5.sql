--
-- Update sql for MailWizz EMA from version 1.5.4 to 1.5.5
--

--
-- Insert into list_field_type
-- 
INSERT INTO `list_field_type` (`type_id`, `name`, `identifier`, `class_alias`, `description`, `date_added`, `last_updated`) VALUES
  (NULL, 'Checkbox', 'checkbox', 'customer.components.field-builder.checkbox.FieldBuilderTypeCheckbox', 'Checkbox', '2018-05-01 19:35:12', '2018-05-01 19:35:12'),
  (NULL, 'Consent Checkbox', 'consentcheckbox', 'customer.components.field-builder.consentcheckbox.FieldBuilderTypeConsentCheckbox', 'Consent Checkbox', '2018-05-01 19:35:12', '2018-05-01 19:35:12');

--
-- Alter list_field
-- 
ALTER TABLE `list_field` ADD `description` VARCHAR(255) NULL DEFAULT NULL AFTER `help_text`;
ALTER TABLE `list_field` ADD `meta_data` BLOB NULL DEFAULT NULL AFTER `visibility`;

--
-- Table structure for table `page`
--
CREATE TABLE IF NOT EXISTS `page` (
  `page_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `content` longtext NOT NULL,
  `status` char(15) NOT NULL DEFAULT 'active',
  `date_added` datetime NOT NULL,
  `last_updated` datetime NOT NULL,
  PRIMARY KEY (`page_id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

--
-- Alter email_blacklist_suggest
-- 
ALTER TABLE `email_blacklist_suggest` DROP KEY `email`;
ALTER TABLE `email_blacklist_suggest` ADD `confirmation_key` CHAR(40) NULL DEFAULT NULL AFTER `user_agent`;
ALTER TABLE `email_blacklist_suggest` ADD `status` CHAR(15) NOT NULL DEFAULT 'unconfirmed' AFTER `confirmation_key`;
RENAME TABLE `email_blacklist_suggest` TO `block_email_request`;

--
-- Insert into start page
-- 
INSERT INTO `start_page` (`page_id`, `application`, `route`, `icon`, `icon_color`, `heading`, `content`, `date_added`, `last_updated`) VALUES
(NULL, 'backend', 'pages/index', 'fa-file', '', 'Create your first page', 'This area allows you to create simple pages for frontend.<br />\nIt is suited for pages like \"Terms and Conditions\", \"Privacy policy\",  but also any page where you want to showcase various info.', '2018-05-09 06:27:35', '2018-05-09 06:30:03');

--
-- Insert into page
--
INSERT INTO `page` (`page_id`, `title`, `slug`, `content`, `status`, `date_added`, `last_updated`) VALUES
(NULL, 'Terms and conditions', 'terms-and-conditions', 'Content coming soon', 'active', '2018-05-09 06:38:54', '2018-05-09 06:38:54'),
(NULL, 'Privacy policy', 'privacy-policy', 'Content coming soon', 'active', '2018-05-09 06:39:09', '2018-05-09 06:39:09');
