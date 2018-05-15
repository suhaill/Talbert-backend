INSERT INTO `status` (`id`, `status_name`, `type`) VALUES (NULL, 'EditFromOrder', 'Plywood'), (NULL, 'EditFromOrder', 'Veneer'), (NULL, 'EditFromOrder', 'Door');

-- 08-05-2018
UPDATE `order_status` SET `status_id`=5 WHERE 1

-- 15-05-2018
UPDATE `status` SET `type` = 'LineItem', `is_active` = '1' WHERE `status`.`id` = 1;