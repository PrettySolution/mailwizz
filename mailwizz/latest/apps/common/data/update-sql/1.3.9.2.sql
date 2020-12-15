--
-- Update sql for MailWizz EMA from version 1.3.9.1 to 1.3.9.2
--

--
-- Alter the `campaign_option` table
--
ALTER TABLE `campaign_option` ADD `share_reports_mask_email_addresses` enum('yes','no') NOT NULL DEFAULT 'no' AFTER `share_reports_password`;

-- -----------------------------------------------------
-- Table `start_page`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `start_page` (
  `page_id` INT NOT NULL AUTO_INCREMENT,
  `application` VARCHAR(45) NOT NULL DEFAULT 'customer',
  `route` VARCHAR(255) NOT NULL,
  `icon` VARCHAR(255) NOT NULL DEFAULT 'fa-info',
  `heading` VARCHAR(255) NOT NULL,
  `content` TEXT NOT NULL,
  `date_added` DATETIME NOT NULL,
  `last_updated` DATETIME NOT NULL,
  PRIMARY KEY (`page_id`),
  UNIQUE KEY `app_route` (`application`, `route`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8;

-- -----------------------------------------------------
-- Dumping data for table `start_page`
-- -----------------------------------------------------

INSERT INTO `start_page` (`page_id`, `application`, `route`, `icon`, `heading`, `content`, `date_added`, `last_updated`) VALUES
(1, 'customer', 'campaigns/index', 'fa-envelope', 'Create your first campaign', 'Start creating your first campaign to reach your target audience.<br />\nYou can create Regular, Recurring or Autoresponder campaigns that target one<br />\nor more lists or even one or more segments of your lists and schedule them for sending at the right time', '2017-03-09 12:17:05', '2017-03-14 07:50:28'),
(2, 'customer', 'lists/index', 'glyphicon-list-alt', 'Create your first email list', 'Start creating your first email list, add subscribers to it, edit it\'s forms and pages<br />\nand create custom fields that you can later use for segmentation.', '2017-03-09 12:18:39', '2017-03-14 07:49:48'),
(3, 'customer', 'templates/index', 'glyphicon-text-width', 'Create your first email template', 'Create your first email template that you can later use in campaigns.<br />\nYou can set the base template here and edit it further in campaigns specifically for the given campaign.', '2017-03-10 07:09:26', '2017-03-14 07:49:12'),
(4, 'customer', 'delivery_servers/index', 'glyphicon-send', 'Create your first delivery server', 'Delivery servers are responsible for deliverying the emails to the subscribers.<br />\nYou have a wide range of delivery server types you can choose from. ', '2017-03-10 07:11:32', '2017-03-14 07:47:23'),
(5, 'customer', 'bounce_servers/index', 'glyphicon-filter', 'Create your first bounce server', 'Bounce servers are used to take action against the email addresses<br />\nof the subscribers that bounce back when campaigns are sent to them.', '2017-03-10 07:13:52', '2017-03-14 07:47:10'),
(6, 'customer', 'feedback_loop_servers/index', 'glyphicon-transfer', 'Create your first feedback loop server', 'Feedback loop servers will help monitoring the abuse reports that subscribers do<br />\nand take proper action when it finds such reports.', '2017-03-10 07:15:11', '2017-03-14 07:48:06'),
(7, 'customer', 'sending_domains/index', 'glyphicon-globe', 'Create your first sending domain', 'Sending domains will match the FROM address of the email campaigns and<br />\nwill add proper DKIM signatures to the email headers, thus increasing the chances for the emails to land inbox.', '2017-03-10 07:16:53', '2017-03-14 07:45:58'),
(8, 'customer', 'tracking_domains/index', 'glyphicon-globe', 'Create your first tracking domain', 'Tracking domains allow masking of the domains used in the tracking urls<br />\nfrom email campaigns with other domains that you specify here.', '2017-03-10 07:17:18', '2017-03-14 07:44:52'),
(9, 'customer', 'api_keys/index', 'glyphicon-star', 'Create your API keys', 'If you need to connect to the system from a 3rd-party app, then using the API is the best way to do it.<br />\nStart by generating a set of API keys to access the API.', '2017-03-10 07:20:19', '2017-03-14 07:43:45'),
(10, 'customer', 'campaign_groups/index', 'glyphicon-folder-close', 'Create your first campaign group', 'You might find it easier to manage your email campaigns if you group them together in groups that make more sense to you.<br />\nYou can later filter your campaigns by the groups you create here.', '2017-03-10 07:22:13', '2017-03-14 07:42:53'),
(11, 'customer', 'list_subscribers/index', 'fa-user-plus', 'Create your list first subscriber', 'You can create a new subscriber, or use the list import feature to import subscribers in bulk.', '2017-03-10 07:25:55', '2017-03-14 07:41:57'),
(12, 'customer', 'list_segments/index', 'glyphicon-cog', 'Create your list first segment', 'You can segment the list subscribers based on the custom fields defined in this list<br />\nand you can also send email campaigns to segments only instead of sending to the whole list.', '2017-03-10 07:26:49', '2017-03-14 07:41:34'),
(13, 'customer', 'campaign_tags/index', 'glyphicon-tag', 'Create your first campaign tag', 'Create custom tags to be used inside campaigns, in addition to the<br />\nregular tags available already for each campaign you create.', '2017-03-10 07:28:24', '2017-03-14 07:56:43'),
(14, 'backend', 'user_groups/index', 'glyphicon-user', 'Create the first user group', 'User groups allow additional access in the backend area of the system.<br />\nYou can decide exactly to what areas the users in the groups are allowed.', '2017-03-10 07:46:55', '2017-03-14 07:37:12'),
(15, 'backend', 'price_plans/index', 'glyphicon-credit-card', 'Create the first price plan', 'Start adding price plans to the system so that the customers can buy them.', '2017-03-10 07:50:15', '2017-03-14 07:36:37'),
(16, 'backend', 'orders/index', 'glyphicon-credit-card', 'Create the first order', 'If the system customers didn\'t buy any price plan yet, you can manually create orders in the name of the existing customers.<br />\n ', '2017-03-10 07:52:28', '2017-03-14 07:35:42'),
(17, 'backend', 'promo_codes/index', 'fa-code', 'Create the first promo code', 'Start adding promotional codes that can later be used by customers when they will purchase any of the available price plans.', '2017-03-10 07:53:17', '2017-03-14 07:33:49'),
(18, 'backend', 'taxes/index', 'fa-dollar', 'Create the first tax for orders', 'Create the tax rates that will apply for the customers of this system.', '2017-03-10 07:55:01', '2017-03-14 07:33:10'),
(19, 'backend', 'customers/index', 'glyphicon-user', 'Create the first customer', 'Create the first system customer which will be able to manage email lists, subscribers, campaigns and much more.<br />\nCustomers can be part of customer groups for easier management.', '2017-03-10 07:57:06', '2017-03-14 07:32:26'),
(20, 'backend', 'customer_groups/index', 'glyphicon-folder-close', 'Create the first customer group', 'You can create groups with various settings, permissions and quotas and assign customers to these groups.<br />\nYou can also assign customer groups with price plans.', '2017-03-10 07:58:47', '2017-03-14 07:30:32'),
(21, 'backend', 'lists/index', 'glyphicon-list-alt', 'Monitor system wide email lists', 'When lists will be created from the customers area, you\'ll see them here too for easier monitoring.', '2017-03-10 08:00:12', '2017-03-14 07:29:09'),
(22, 'backend', 'campaigns/index', 'fa-envelope', 'Monitor system wide campaigns', 'When campaigns will be created from the customers area, you\'ll see them here too for easier monitoring.', '2017-03-10 08:01:56', '2017-03-14 07:28:42'),
(23, 'backend', 'delivery_servers/index', 'glyphicon-send', 'Create the first delivery server', 'Delivery servers are responsible for deliverying the emails to the subscribers.<br />\nYou have a wide range of delivery server types you can choose from. ', '2017-03-10 08:04:09', '2017-03-14 07:27:52'),
(24, 'backend', 'bounce_servers/index', 'glyphicon-filter', 'Create the first bounce server', 'Bounce servers are used to take action against the email addresses<br />\nof the subscribers that bounce back when campaigns are sent to them.', '2017-03-10 08:05:32', '2017-03-14 07:27:09'),
(25, 'backend', 'feedback_loop_servers/index', 'glyphicon-transfer', 'Create the first feedback loop server', 'Feedback loop servers will help monitoring the abuse reports that subscribers do<br />\nand take proper action when it finds such reports.', '2017-03-10 08:06:44', '2017-03-14 07:26:23'),
(26, 'backend', 'sending_domains/index', 'glyphicon-globe', 'Create the first sending domain', 'Sending domains will match the FROM address of the email campaigns and<br />\nwill add proper DKIM signatures to the email headers, thus increasing the chances for the emails to land inbox.', '2017-03-10 08:08:16', '2017-03-14 07:25:15'),
(27, 'backend', 'tracking_domains/index', 'glyphicon-flash', 'Create the first tracking domain', 'Tracking domains allow masking of the domains used in the tracking urls<br />\nfrom email campaigns with other domains that you specify here.', '2017-03-10 08:09:24', '2017-03-14 07:24:23'),
(28, 'backend', 'email_templates_gallery/index', 'glyphicon-text-width', 'Create the email templates gallery', 'All the email templates you create here will be visible in the customers area<br />\nwhere customers can import them into their own accounts and change them as they wish.', '2017-03-10 08:11:32', '2017-03-14 07:23:13'),
(29, 'backend', 'email_blacklist/index', 'glyphicon-ban-circle', 'Manage the email blacklist', 'Start adding emails in the global email blacklist to prevent sending to them or being added in the system from email lists, registrations and so on.<br />\nThis is a global email blacklist that applies to absolutely each email from the system.', '2017-03-10 08:16:17', '2017-03-14 07:20:59'),
(30, 'backend', 'email_blacklist_monitors/index', 'glyphicon-ban-circle', 'Create the first email blacklist monitor', 'Sometimes, emails can be automatically added in the global blacklisted for false reasons and when this happens,<br />\nyou need a way to monitor the email blacklist to remove such false positives.', '2017-03-10 08:18:17', '2017-03-14 07:18:59'),
(31, 'customer', 'email_blacklist/index', 'glyphicon-ban-circle', 'Manage your email blacklist', 'Create your own email blacklist to include subscribers that will never receive emails<br />\nfrom you and that will never be added to your email lists.', '2017-03-10 09:38:03', '2017-03-14 07:39:13'),
(32, 'customer', 'price_plans/orders', 'glyphicon-credit-card', 'Create your first order', 'When you purchase a price plan you will see the order details here.', '2017-03-14 07:52:22', '2017-03-14 07:55:11');

-- --------------------------------------------------------