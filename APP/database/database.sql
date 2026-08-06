CREATE DATABASE IF NOT EXISTS `iphone_store_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `iphone_store_db`;

DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `users`;

-- Users Table
CREATE TABLE `users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(100) NOT NULL,
  `role` VARCHAR(20) DEFAULT 'Admin',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Admin User (username: admin | password: admin123)
INSERT INTO `users` (`username`, `password`, `full_name`, `role`) VALUES
('admin', '$2y$10$wT282n00IByU/W6S4Iexvu2a5y9B4iV.UfE.z/Sj3cW5yU5aQeS/2', 'Cris Ian Laminero', 'Admin');

-- Products Table
CREATE TABLE `products` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `model` VARCHAR(100) NOT NULL,
  `condition_type` ENUM('Brand New', 'Secondhand') NOT NULL DEFAULT 'Brand New',
  `storage` VARCHAR(20) NOT NULL,
  `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `stock` INT(11) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Products
INSERT INTO `products` (`model`, `condition_type`, `storage`, `price`, `stock`) VALUES
('iPhone 15 Pro', 'Brand New', '256GB', 59990.00, 4),
('iPhone 15', 'Brand New', '128GB', 44990.00, 8),
('iPhone 14', 'Secondhand', '256GB', 34990.00, 2),
('iPhone 13', 'Brand New', '128GB', 32990.00, 10),
('iPhone 12', 'Secondhand', '128GB', 21990.00, 3),
('iPhone 11', 'Secondhand', '64GB', 14990.00, 5);

-- Orders Table
CREATE TABLE `orders` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `order_code` VARCHAR(20) NOT NULL UNIQUE,
  `customer` VARCHAR(100) NOT NULL,
  `contact` VARCHAR(20) NOT NULL,
  `product_id` INT(11) NOT NULL,
  `quantity` INT(11) NOT NULL DEFAULT 1,
  `total_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `status` ENUM('Completed', 'Pending', 'Cancelled') NOT NULL DEFAULT 'Completed',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Orders
INSERT INTO `orders` (`order_code`, `customer`, `contact`, `product_id`, `quantity`, `total_amount`, `status`) VALUES
('#ORD-0002', 'Maria Santos', '09189876543', 1, 1, 59990.00, 'Cancelled'),
('#ORD-0001', 'Juan Dela Cruz', '09171234567', 4, 1, 32990.00, 'Completed');