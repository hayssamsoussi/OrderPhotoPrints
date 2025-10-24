-- Database schema for OrderPhotoPrints Platform

CREATE DATABASE IF NOT EXISTS photo_orders CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE photo_orders;

-- Admin users table
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Clients table (unique URLs)
CREATE TABLE IF NOT EXISTS clients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    unique_code VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_unique_code (unique_code)
);

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    total_photos INT DEFAULT 0,
    total_cost DECIMAL(10, 2) DEFAULT 0.00,
    status ENUM('received', 'printing', 'shipping', 'done') DEFAULT 'received',
    deposit_paid DECIMAL(10, 2) DEFAULT 0.00,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    INDEX idx_status (status)
);

-- Photos table
CREATE TABLE IF NOT EXISTS photos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    filename VARCHAR(255) NOT NULL,
    original_filename VARCHAR(255) NOT NULL,
    filepath VARCHAR(500) NOT NULL,
    quantity INT DEFAULT 1,
    price_per_photo DECIMAL(10, 2) DEFAULT 0.00,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    INDEX idx_order_id (order_id)
);

-- Products table (photo albums, box packaging, etc.)
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    type ENUM('album', 'box', 'frame', 'other') DEFAULT 'other',
    image_url VARCHAR(500),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Order products table (products added to specific orders)
CREATE TABLE IF NOT EXISTS order_products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT DEFAULT 1,
    price DECIMAL(10, 2) NOT NULL,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_order_id (order_id)
);

-- Photo options table (frame, woodboard, bigger size, etc.)
CREATE TABLE IF NOT EXISTS photo_options (
    id INT AUTO_INCREMENT PRIMARY KEY,
    photo_id INT NOT NULL,
    has_frame BOOLEAN DEFAULT FALSE,
    has_woodboard BOOLEAN DEFAULT FALSE,
    bigger_size BOOLEAN DEFAULT FALSE,
    frame_color VARCHAR(50),
    woodboard_type VARCHAR(50),
    size_multiplier DECIMAL(3, 2) DEFAULT 1.00,
    extra_cost DECIMAL(10, 2) DEFAULT 0.00,
    FOREIGN KEY (photo_id) REFERENCES photos(id) ON DELETE CASCADE,
    INDEX idx_photo_id (photo_id)
);

-- Insert default admin (password: admin123)
INSERT INTO admins (username, password, email) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@example.com');

-- Insert default products
INSERT INTO products (name, description, price, type) VALUES 
('Photo Album - Small', 'Small photo album (4x6 photos)', 15.00, 'album'),
('Photo Album - Large', 'Large photo album (5x7 photos)', 25.00, 'album'),
('Gift Box Packaging', 'Elegant gift box for photos', 10.00, 'box'),
('Premium Frame', 'Professional frame for photo', 20.00, 'frame'),
('Wooden Display Board', 'Wooden board for photo display', 30.00, 'other');

