--
-- Update sql for MailWizz EMA from version 1.3.7.5 to 1.3.7.6
--

-- 
-- Insert new field types
--
INSERT INTO `list_field_type` (`type_id`, `name`, `identifier`, `class_alias`, `description`, `date_added`, `last_updated`) VALUES (NULL, 'Country', 'country', 'customer.components.field-builder.country.FieldBuilderTypeCountry', 'Country', '2016-11-04 14:26:26', '2016-11-04 00:00:00');
INSERT INTO `list_field_type` (`type_id`, `name`, `identifier`, `class_alias`, `description`, `date_added`, `last_updated`) VALUES (NULL, 'State', 'state', 'customer.components.field-builder.state.FieldBuilderTypeState', 'State', '2016-11-04 14:26:26', '2016-11-04 00:00:00');
