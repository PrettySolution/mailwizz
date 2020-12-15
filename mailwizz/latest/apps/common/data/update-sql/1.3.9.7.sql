--
-- Update sql for MailWizz EMA from version 1.3.9.6 to 1.3.9.7
--

--
-- Alter `campaign` table
-- 
ALTER TABLE `campaign` ADD `priority` INT(11) NOT NULL DEFAULT '0' AFTER `delivery_logs_archived`;
