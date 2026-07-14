-- Database creation script para soporte_master_db
-- (Líneas CREATE DATABASE y USE eliminadas para compatibilidad con hosting compartido)


-- 1. Admins Table
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    fullname VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Insert default admin: username=admin, password=admin123
INSERT INTO admins (username, password_hash, fullname, email)
VALUES ('admin', '$2y$10$6I7IP8EKdygVRnQ2zVQ.2.cg7Mw/lxp0cREVbxLdQdWEwUXo/ICH2', 'Administrador Principal', 'admin@soportemaster.com')
ON DUPLICATE KEY UPDATE id=id;

-- 2. Brands Table
CREATE TABLE IF NOT EXISTS brands (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    logo_url VARCHAR(255) DEFAULT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Seed Brands
INSERT INTO brands (name, logo_url, description) VALUES
('HP', 'https://s2.googleusercontent.com/s2/favicons?sz=128&domain=hp.com', 'Hewlett-Packard laptops, desktops, and printer drivers.'),
('Dell', 'https://s2.googleusercontent.com/s2/favicons?sz=128&domain=dell.com', 'Dell Latitude, Inspiron, and Vostro driver support.'),
('Lenovo', 'https://s2.googleusercontent.com/s2/favicons?sz=128&domain=lenovo.com', 'ThinkPad, IdeaPad, and ThinkCentre driver packages.'),
('ASUS', 'https://s2.googleusercontent.com/s2/favicons?sz=128&domain=asus.com', 'ASUS ROG, ZenBook, and VivoBook device controllers.')
ON DUPLICATE KEY UPDATE id=id;

-- 3. Categories Table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    icon_class VARCHAR(50) DEFAULT 'fa-file',
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Seed Categories
INSERT INTO categories (name, icon_class, description) VALUES
('Audio', 'fa-volume-up', 'Controladores de sonido, Realtek, High Definition Audio, etc.'),
('Video / Gráficos', 'fa-desktop', 'Controladores de pantalla Intel HD Graphics, Nvidia GeForce, AMD Radeon.'),
('Red / LAN', 'fa-ethernet', 'Controladores de tarjeta de red cableada Realtek, Intel, Broadcom.'),
('Wi-Fi / Red Inalámbrica', 'fa-wifi', 'Controladores para tarjetas de conexión inalámbrica y Bluetooth.'),
('Chipset', 'fa-microchip', 'Controladores de placa madre, controladores de bus, Intel Management Engine, AMD SMBus.'),
('Almacenamiento', 'fa-hdd', 'Controladores SATA, AHCI, NVMe, Intel Rapid Storage Technology.')
ON DUPLICATE KEY UPDATE id=id;

-- 4. Equipment Table
CREATE TABLE IF NOT EXISTS equipment (
    id INT AUTO_INCREMENT PRIMARY KEY,
    brand_id INT NOT NULL,
    model_name VARCHAR(150) NOT NULL,
    image_url VARCHAR(255) DEFAULT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (brand_id) REFERENCES brands(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Seed Equipment
INSERT INTO equipment (brand_id, model_name, image_url, description) VALUES
(1, 'ProBook 450 G8', 'uploads/equipment/probook.png', 'Computadora portátil empresarial HP ProBook con procesadores Intel Core de 11.ª generación.'),
(2, 'Latitude 5420', 'uploads/equipment/latitude.png', 'Laptop corporativa Dell Latitude delgada y ligera.'),
(3, 'ThinkPad T14 Gen 2', 'uploads/equipment/thinkpad.png', 'Computadora portátil insignia para productividad empresarial de Lenovo.')
ON DUPLICATE KEY UPDATE id=id;

-- 5. Drivers Table
CREATE TABLE IF NOT EXISTS drivers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    equipment_id INT NOT NULL,
    category_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    version VARCHAR(50) NOT NULL,
    os VARCHAR(100) NOT NULL,
    file_size VARCHAR(50) NOT NULL,
    download_url VARCHAR(255) DEFAULT NULL,
    is_local TINYINT(1) DEFAULT 1,
    download_count INT DEFAULT 0,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (equipment_id) REFERENCES equipment(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Seed Drivers
INSERT INTO drivers (equipment_id, category_id, name, version, os, file_size, download_url, is_local) VALUES
-- HP ProBook 450 G8
(1, 1, 'Realtek High Definition Audio Driver', '6.0.9126.1', 'Windows 10 64-bit / Windows 11', '124.5 MB', 'uploads/files/hp_realtek_audio.zip', 1),
(1, 2, 'Intel UHD Graphics Driver', '30.0.100.9864', 'Windows 10 64-bit', '350.2 MB', 'https://downloadmirror.intel.com/some-example-driver-link.exe', 0),
(1, 4, 'Intel Wireless LAN Controller Driver', '22.70.0.6', 'Windows 11', '45.8 MB', 'uploads/files/hp_intel_wifi.zip', 1),
-- Dell Latitude 5420
(2, 3, 'Realtek PCIe Ethernet Controller Driver', '10.047.0121.2021', 'Windows 10 64-bit / Windows 11', '12.4 MB', 'uploads/files/dell_realtek_lan.zip', 1),
(2, 5, 'Intel Chipset Device Software', '10.1.18793.8276', 'Windows 10 / Windows 11', '4.2 MB', 'uploads/files/dell_intel_chipset.zip', 1),
-- Lenovo ThinkPad T14 Gen 2
(3, 1, 'Realtek Audio Driver', '6.0.9228.1', 'Windows 11', '98.6 MB', 'uploads/files/lenovo_realtek_audio.zip', 1),
(3, 4, 'Intel Wireless LAN Driver', '22.80.1.1', 'Windows 10 64-bit / Windows 11', '52.1 MB', 'uploads/files/lenovo_intel_wifi.zip', 1)
ON DUPLICATE KEY UPDATE id=id;
