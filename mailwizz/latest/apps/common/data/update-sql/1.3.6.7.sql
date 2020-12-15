--
-- Update sql for MailWizz EMA from version 1.3.6.6 to 1.3.6.7
--

--
-- Alter the delivery_server table
--
ALTER TABLE `delivery_server` ADD `max_connection_messages` INT(11) NOT NULL DEFAULT '1' AFTER `must_confirm_delivery`;