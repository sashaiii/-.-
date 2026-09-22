-- database.sql
CREATE DATABASE IF NOT EXISTS hotel_booking;
USE hotel_booking;

-- Таблица пользователей
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('guest', 'admin') DEFAULT 'guest',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Таблица категорий номеров
CREATE TABLE room_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL
);

-- Таблица номеров
CREATE TABLE rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    room_number VARCHAR(10) NOT NULL UNIQUE,
    image VARCHAR(255),
    FOREIGN KEY (category_id) REFERENCES room_categories(id) ON DELETE CASCADE
);

-- Таблица бронирований
CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    room_id INT,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL,
    check_in DATE NOT NULL,
    check_out DATE NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE
);

-- Начальные данные
-- Пароль для всех: "password123"
INSERT INTO users (username, password_hash, role) VALUES 
('admin', '$2y$10$YourHashHere...', 'admin'),
('guest', '$2y$10$YourHashHere...', 'guest');

-- Категории
INSERT INTO room_categories (name, description, price) VALUES 
('Стандарт', 'Уютный номер с двуспальной кроватью.', 3500.00),
('Студия', 'Номер с кухонным уголком и холодильником.', 5500.00),
('Люкс', 'Двухкомнатный номер с кондиционером и мини-баром.', 11000.00);

-- Номера (примеры)
INSERT INTO rooms (category_id, room_number, image) VALUES 
(1, '101', 'img/standart.png'),
(1, '102', 'img/standart.png'),
(2, '201', 'img/studio.jpg'),
(3, '301', 'img/lux.png');
