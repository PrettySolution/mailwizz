--
-- Update sql for MailWizz EMA from version 1.3.9.2 to 1.3.9.3
--

--
-- Alter `start_page` table
--
ALTER TABLE `start_page` ADD `icon_color` CHAR(6) NULL DEFAULT NULL AFTER `icon`;

--
-- Update some of the start pages
--
UPDATE `start_page` SET `content` = 'If the system customers didn\'t buy any price plan yet,<br />you can manually create orders in the name of the existing customers.<br />\n' WHERE `application` = 'backend' AND `route` = 'orders/index'; 
UPDATE `start_page` SET `content` = 'Start adding promotional codes that can later be used by<br />customers when they will purchase any of the available price plans.' WHERE `application` = 'backend' AND `route` = 'promo_codes/index'; 
UPDATE `start_page` SET `content` = 'Start creating your first campaign to reach your target audience.<br />You can create Regular, Recurring or Autoresponder campaigns that target one<br />or more lists or even one or more segments of your lists and schedule them for sending at the right time.' WHERE `application` = 'customer' AND `route` = 'campaigns/index';


--
-- Insert new pages
-- 
INSERT INTO `start_page` (`page_id`, `application`, `route`, `icon`, `heading`, `content`, `date_added`, `last_updated`) VALUES
(NULL, 'customer', 'dashboard/index', 'glyphicon-dashboard', 'Welcome', 'You will see more info on this page after you start using the system and<br />\ncreate your first email list and send your first email campaign.<br /><br /><a class=\"btn btn-primary btn-flat\" href=\"[CUSTOMER_BASE_URL]lists/create\"><span class=\"glyphicon glyphicon-list-alt\"><!-- --></span> Create your first email list</a>   <a class=\"btn btn-primary btn-flat\" href=\"[CUSTOMER_BASE_URL]campaigns/create\"><span class=\"glyphicon glyphicon-envelope\"><!-- --></span> Create your first email campaign</a>', '2017-03-15 08:28:00', '2017-03-15 09:21:20'),
(NULL, 'backend', 'dashboard/index', 'glyphicon-dashboard', 'Welcome', 'The dashboard will be populated with more info once<br />\nyou and/or your customers start using the system and add content to it.', '2017-03-15 09:42:04', '2017-03-15 09:47:25');


--
-- Alter `campaign` table
--
ALTER TABLE `campaign` ADD `subject_encoded` varbinary(768) NULL DEFAULT NULL AFTER `subject`;