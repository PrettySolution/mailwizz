--
-- Update sql for MailWizz EMA from version 1.3.8.6 to 1.3.8.7
--

-- 
-- Alter the list_segment table
--
ALTER TABLE `list_segment` ADD `status` char(15) NOT NULL DEFAULT 'active' AFTER `operator_match`;