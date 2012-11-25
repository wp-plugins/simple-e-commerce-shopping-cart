create table if not exists `[prefix]products` (
 `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `item_number` varchar(50) NOT NULL,
  `price` decimal(8,2) NOT NULL,
  `options_1` text NOT NULL,
  `options_2` text NOT NULL,
  `custom` varchar(50) NOT NULL DEFAULT 'none',
  `custom_desc` text NOT NULL,
  `taxable` tinyint(1) unsigned NOT NULL,
  `specific_tax` int(11) NOT NULL,
  `shipped` tinyint(1) unsigned NOT NULL,
  `single_sihipping_cost` decimal(10,0) NOT NULL,
  `multiple_sihipping_cost` decimal(10,0) NOT NULL,
  `single_sihipping_cost_international` decimal(10,0) NOT NULL,
  `multiple_sihipping_cost_international` decimal(10,0) NOT NULL,
  `weight` decimal(8,2) unsigned NOT NULL DEFAULT '0.00',
  `weight_type` text NOT NULL,
  `height` decimal(8,2) unsigned NOT NULL DEFAULT '0.00',
  `height_type` text NOT NULL,
  `width` decimal(8,2) unsigned NOT NULL DEFAULT '0.00',
  `width_type` text NOT NULL,
  `length` decimal(8,2) unsigned NOT NULL DEFAULT '0.00',
  `length_type` text NOT NULL,
  `download_path` text,
  `digital_prdoduct_url` varchar(1000) NOT NULL,
  `s3_bucket` varchar(200) NOT NULL,
  `s3_file` varchar(200) NOT NULL,
  `download_limit` tinyint(4) DEFAULT '0',
  `spreedly_subscription_id` varchar(250) NOT NULL DEFAULT '',
  `allow_cancel` tinyint(4) DEFAULT '1',
  `is_paypal_subscription` tinyint(4) DEFAULT '0',
  `max_quantity` int(10) unsigned NOT NULL DEFAULT '0',
  `gravity_form_id` int(10) unsigned NOT NULL DEFAULT '0',
  `gravity_form_qty_id` int(10) unsigned NOT NULL DEFAULT '0',
  `feature_level` varchar(255) NOT NULL,
  `setup_fee` decimal(8,2) NOT NULL,
  `billing_interval` int(10) unsigned NOT NULL,
  `billing_interval_unit` varchar(50) NOT NULL,
  `billing_cycles` int(10) unsigned NOT NULL,
  `offer_trial` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `trial_period` int(10) unsigned NOT NULL,
  `trial_period_unit` varchar(50) NOT NULL,
  `trial_price` decimal(8,2) NOT NULL,
  `trial_cycles` int(10) unsigned NOT NULL DEFAULT '0',
  `start_recurring_number` int(10) unsigned NOT NULL DEFAULT '1',
  `start_recurring_unit` varchar(50) NOT NULL,
  `price_description` varchar(255) NOT NULL,
  `is_membership_product` tinyint(1) NOT NULL DEFAULT '0',
  `lifetime_membership` tinyint(1) NOT NULL DEFAULT '0',
  `min_quantity` int(10) unsigned NOT NULL DEFAULT '0',
  `is_user_price` tinyint(1) NOT NULL DEFAULT '0',
  `min_price` decimal(8,2) NOT NULL DEFAULT '0.00',
  `max_price` decimal(8,2) NOT NULL DEFAULT '0.00',
  `variation1_name` varchar(100) NOT NULL,
  `variation1_variations` text NOT NULL,
  `variation1_prices` text NOT NULL,
  `variation1_signs` varchar(100) NOT NULL,
  `variation2_name` varchar(100) NOT NULL,
  `variation2_variations` text NOT NULL,
  `variation2_prices` text NOT NULL,
  `variation2_signs` varchar(100) NOT NULL,
  `description` varchar(1000) NOT NULL,
  `category` int(11) NOT NULL,
  `button_image_path` text NOT NULL,
  `product_image_path` text NOT NULL,
  PRIMARY KEY (`id`)
);

create table if not exists `[prefix]product_categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` text,
  PRIMARY KEY (`id`)
);

create table if not exists `[prefix]downloads` (
  `id` int(10) unsigned not null auto_increment,
  `duid` varchar(100),
  `downloaded_on` datetime null,
  `ip` varchar(50) not null,
  primary key(`id`)
);

create table if not exists `[prefix]promotions` (
 `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `type` enum('dollar','percentage') NOT NULL DEFAULT 'dollar',
  `amount` decimal(8,2) DEFAULT NULL,
  `min_order` decimal(8,2) DEFAULT NULL,
  `products` varchar(5000) NOT NULL,
  `apply_for_all_products` tinyint(1) unsigned NOT NULL,
  `redemption_limit` int(11) NOT NULL,
  `redemption_count` int(11) NOT NULL,
  `start_date` datetime NOT NULL,
  `expiry_date` datetime NOT NULL,
  `active` tinyint(1) unsigned NOT NULL,
  `description` varchar(500) NOT NULL,
  `optional_option1` tinyint(1) NOT NULL, 
  `optional_option1_condition` tinyint(1) NOT NULL, 
  `optional_option1_value` decimal(10,0) NOT NULL,
  PRIMARY KEY (`id`)
);

create table if not exists `[prefix]shipping_methods` (
  `id` int(10) unsigned not null auto_increment,
  `name` varchar(100) not null,
  `default_rate` decimal(8,2) not null,
  `default_bundle_rate` decimal(8,2) not null,
  `carrier` varchar(100) not null,
  `code` varchar(50) not null,
  primary key(`id`)
);

create table if not exists `[prefix]shipping_rates` (
  `id` int(10) unsigned not null auto_increment,
  `product_id` int(10) unsigned not null,
  `shipping_method_id` int(10) unsigned not null,
  `shipping_rate` decimal(8,2) not null,
  `shipping_bundle_rate` decimal(8,2) not null,
  primary key(`id`)
);

create table if not exists `[prefix]shipping_rules` (
  `id` int(10) unsigned not null auto_increment,
  `min_amount` decimal(8,2),
  `shipping_method_id` int(10) unsigned not null,
  `shipping_cost` decimal(8,2),
  primary key(`id`)
);

create table if not exists `[prefix]tax_rates` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `state` varchar(20) NOT NULL,
  `zip_low` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `zip_high` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `rate` decimal(8,3) NOT NULL,
  `tax_shipping` tinyint(1) NOT NULL DEFAULT '0',
  `is_usa_canada` tinyint(1) NOT NULL,
  `country` varchar(500) NOT NULL,
  PRIMARY KEY (`id`)
);

create table if not exists `[prefix]cart_settings` (
  `key` varchar(50) not null,
  `value` text not null,
  primary key(`key`)
);

create table if not exists `[prefix]orders` (
 `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `bill_first_name` varchar(50) NOT NULL,
  `bill_last_name` varchar(50) NOT NULL,
  `bill_address` varchar(150) NOT NULL,
  `bill_address2` varchar(150) NOT NULL,
  `bill_city` varchar(150) NOT NULL,
  `bill_state` varchar(50) NOT NULL,
  `bill_country` varchar(50) NOT NULL DEFAULT '',
  `bill_zip` varchar(150) NOT NULL,
  `ship_first_name` varchar(50) NOT NULL,
  `ship_last_name` varchar(50) NOT NULL,
  `ship_address` varchar(150) NOT NULL,
  `ship_address2` varchar(150) NOT NULL,
  `ship_city` varchar(150) NOT NULL,
  `ship_state` varchar(50) NOT NULL,
  `ship_country` varchar(50) NOT NULL DEFAULT '',
  `ship_zip` varchar(150) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `email` varchar(100) NOT NULL,
  `coupon` varchar(50) DEFAULT NULL,
  `discount_amount` decimal(8,2) NOT NULL,
  `trans_id` varchar(25) NOT NULL,
  `shipping` decimal(8,2) NOT NULL,
  `subtotal` decimal(8,2) NOT NULL,
  `tax` decimal(8,2) NOT NULL,
  `total` decimal(8,2) NOT NULL,
  `non_subscription_total` decimal(8,2) NOT NULL,
  `ordered_on` datetime DEFAULT NULL,
  `status` varchar(50) NOT NULL,
  `ip` varchar(50) NOT NULL,
  `ouid` varchar(100) NOT NULL,
  `shipping_method` varchar(50) DEFAULT NULL,
  `account_id` int(10) unsigned NOT NULL DEFAULT '0',
  `payment_method` varchar(50) DEFAULT NULL,
  `delivery_status` varchar(50) DEFAULT NULL,
  `payment_status` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
);

create table if not exists `[prefix]order_items` (
 `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned NOT NULL,
  `product_id` int(10) unsigned NOT NULL,
  `item_number` varchar(50) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `product_price` decimal(8,2) NOT NULL,
  `description` text,
  `quantity` int(10) unsigned NOT NULL,
  `duid` varchar(100) DEFAULT NULL,
  `form_entry_ids` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
);

create table if not exists `[prefix]inventory` (
  `ikey` varchar(250) NOT NULL,
  `product_id` int(10) unsigned NOT NULL,
  `track` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `quantity` int(10) unsigned NOT NULL,
  PRIMARY KEY (`ikey`)
);

create table if not exists`[prefix]sessions` (
  `id` int(10) unsigned not null auto_increment,
  `session_id` varchar(50) not null,
  `ip_address` varchar(16) default '0' not null,
  `user_agent` varchar(255) not null,
  `last_activity` datetime not null,
  `user_data` text default '' not null,
  unique key `sid` (`session_id`),
  primary key (`id`)
);

create table if not exists`[prefix]shipping_table_rate` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `total_cart_price` decimal(8,2) NOT NULL,
  `local_shipping_price` decimal(8,2) NOT NULL,
  `international_shipping_price` decimal(8,2) NOT NULL,
  PRIMARY KEY (`id`)
);

create table if not exists`[prefix]shipping_variation` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `variation` text NOT NULL,
  `additional_price` decimal(8,2) NOT NULL,
  PRIMARY KEY (`id`)
);

create table if not exists`[prefix]shipping_weight_rate` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `total_weight` decimal(8,2) NOT NULL,
  `local_shipping_price` decimal(8,2) NOT NULL,
  `international_shipping_price` decimal(8,2) NOT NULL,
  PRIMARY KEY (`id`)
);