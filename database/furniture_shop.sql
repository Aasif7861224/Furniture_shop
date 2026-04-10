CREATE DATABASE IF NOT EXISTS furniture_shop CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE furniture_shop;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS cart_items;
DROP TABLE IF EXISTS cart;
DROP TABLE IF EXISTS product_images;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS contact_messages;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(150) NOT NULL,
    password VARCHAR(100) NOT NULL,
    role ENUM('admin', 'user') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_users_email (email),
    KEY idx_users_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE contact_messages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(150) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('new','in_progress','resolved') DEFAULT 'new',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_contact_status (status),
    KEY idx_contact_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_categories_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE products (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id INT UNSIGNED NOT NULL,
    name VARCHAR(180) NOT NULL,
    description TEXT NOT NULL,
    price DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    cover_image VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_products_category_id (category_id),
    CONSTRAINT fk_products_category
        FOREIGN KEY (category_id) REFERENCES categories(id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE product_images (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id INT UNSIGNED NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    KEY idx_product_images_product_id (product_id),
    CONSTRAINT fk_product_images_product
        FOREIGN KEY (product_id) REFERENCES products(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE cart (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_cart_user_id (user_id),
    CONSTRAINT fk_cart_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE cart_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cart_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    quantity INT UNSIGNED NOT NULL DEFAULT 1,
    UNIQUE KEY uk_cart_product (cart_id, product_id),
    KEY idx_cart_items_product_id (product_id),
    CONSTRAINT fk_cart_items_cart
        FOREIGN KEY (cart_id) REFERENCES cart(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_cart_items_product
        FOREIGN KEY (product_id) REFERENCES products(id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE orders (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    payment_method ENUM('COD', 'UPI', 'CARD') NOT NULL,
    status ENUM('pending', 'paid', 'shipped', 'delivered') NOT NULL DEFAULT 'pending',
    customer_name VARCHAR(120) NOT NULL,
    customer_phone VARCHAR(30) NOT NULL,
    shipping_address TEXT NOT NULL,
    shipping_city VARCHAR(100) NOT NULL,
    shipping_state VARCHAR(100) NOT NULL,
    shipping_postal_code VARCHAR(20) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_orders_user_id (user_id),
    KEY idx_orders_status (status),
    CONSTRAINT fk_orders_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE order_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    quantity INT UNSIGNED NOT NULL DEFAULT 1,
    price DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    KEY idx_order_items_order_id (order_id),
    KEY idx_order_items_product_id (product_id),
    CONSTRAINT fk_order_items_order
        FOREIGN KEY (order_id) REFERENCES orders(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_order_items_product
        FOREIGN KEY (product_id) REFERENCES products(id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO users (id, name, email, password, role, created_at) VALUES
(1, 'Store Admin', 'admin@furnitureshop.com', 'admin123', 'admin', NOW()),
(2, 'Demo User', 'user@furnitureshop.com', 'user123', 'user', NOW());

INSERT INTO categories (id, name, created_at) VALUES
(1, 'Living Room', NOW()),
(2, 'Bedroom', NOW()),
(3, 'Dining', NOW()),
(4, 'Office', NOW());

INSERT INTO products (id, category_id, name, description, price, cover_image, created_at) VALUES
(1, 1, 'Nordic Cloud Sofa', 'A soft modern sofa with deep cushioning, sleek wooden legs, and a calm neutral palette for stylish living rooms.', 48999.00, 'uploads/products/placeholder-sofa.svg', NOW()),
(2, 1, 'Terra Accent Chair', 'A compact lounge chair with textured upholstery and a supportive backrest designed for cozy reading corners.', 18999.00, 'uploads/products/placeholder-chair.svg', NOW()),
(3, 2, 'Oak Haven Bed', 'A warm oak-finish platform bed with a tall headboard that creates a clean and restful bedroom centerpiece.', 52999.00, 'uploads/products/placeholder-bed.svg', NOW()),
(4, 3, 'Verona Dining Set', 'A contemporary dining table with balanced proportions and matching seating for memorable family meals.', 45999.00, 'uploads/products/placeholder-dining.svg', NOW()),
(5, 4, 'Studio Work Desk', 'A streamlined work desk with storage-friendly dimensions that fits modern home office layouts.', 23999.00, 'uploads/products/placeholder-desk.svg', NOW()),
(6, 1, 'Sierra Storage Cabinet', 'A vertical storage cabinet with rich wooden tones, clean doors, and versatile room placement.', 27999.00, 'uploads/products/placeholder-storage.svg', NOW());

INSERT INTO product_images (product_id, image_path) VALUES
(1, 'uploads/products/placeholder-detail-lifestyle.svg'),
(1, 'uploads/products/placeholder-detail-fabric.svg'),
(2, 'uploads/products/placeholder-detail-lifestyle.svg'),
(2, 'uploads/products/placeholder-detail-wood.svg'),
(3, 'uploads/products/placeholder-detail-lifestyle.svg'),
(3, 'uploads/products/placeholder-detail-wood.svg'),
(4, 'uploads/products/placeholder-detail-lifestyle.svg'),
(4, 'uploads/products/placeholder-detail-wood.svg'),
(5, 'uploads/products/placeholder-detail-wood.svg'),
(5, 'uploads/products/placeholder-detail-fabric.svg'),
(6, 'uploads/products/placeholder-detail-lifestyle.svg'),
(6, 'uploads/products/placeholder-detail-wood.svg');
