INSERT INTO `status` (`id`, `status_name`, `type`) VALUES (NULL, 'EditFromOrder', 'Plywood'), (NULL, 'EditFromOrder', 'Veneer'), (NULL, 'EditFromOrder', 'Door');

-- 08-05-2018
UPDATE `order_status` SET `status_id`=5 WHERE 1

-- 15-05-2018
UPDATE `status` SET `type` = 'LineItem', `is_active` = '1' WHERE `status`.`id` = 1;


-- 19-06-2018

INSERT INTO `size_edge_materials` (`id`, `name`, `createdAt`) VALUES 
(NULL, '1/4"', '2018-06-19 00:00:00'), 
(NULL, '1/2"', '2018-06-19 00:00:00'), 
(NULL, '3/4"', '2018-06-19 00:00:00'), 
(NULL, '1"', '2018-06-19 00:00:00'), 
(NULL, '1-1/4"', '2018-06-19 00:00:00'), 
(NULL, '1-1/2"', '2018-06-19 00:00:00'), 
(NULL, '1-3/4"', '2018-06-19 00:00:00'), 
(NULL, '2"', '2018-06-19 00:00:00'), 
(NULL, '2-1/4"', '2018-06-19 00:00:00'), 
(NULL, '2-1/2"', '2018-06-19 00:00:00'), 
(NULL, '3"', '2018-06-19 00:00:00');
