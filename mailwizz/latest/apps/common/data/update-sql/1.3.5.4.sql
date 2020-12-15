--
-- Update sql for MailWizz EMA from version 1.3.5.3 to 1.3.5.4
--

--
-- Alter delivery_server table
--
ALTER TABLE `delivery_server` CHANGE `password` `password` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
ALTER TABLE `delivery_server` ADD `reply_to_email` VARCHAR(150) NULL DEFAULT NULL AFTER `from_name`;
ALTER TABLE `delivery_server` ADD `force_reply_to` VARCHAR(50) NOT NULL DEFAULT 'never' AFTER `force_from`;

-- --------------------------------------------------------

--
-- Alter option table
--
ALTER TABLE `option` CHANGE `category` `category` VARCHAR(150) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL, CHANGE `key` `key` VARCHAR(150) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;

-- --------------------------------------------------------

--
-- Alter customer_company table
--
ALTER TABLE `customer_company` ADD `vat_number` VARCHAR(100) NULL DEFAULT NULL AFTER `fax`;

-- --------------------------------------------------------
