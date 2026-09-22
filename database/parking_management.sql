CREATE DATABASE IF NOT EXISTS vehicle_parking_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE vehicle_parking_management;

SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS audit_logs, notifications, receipts, payments, parking_records, bookings, parking_rates, parking_slots, vehicles, contact_messages, system_settings, users;
SET FOREIGN_KEY_CHECKS=1;

CREATE TABLE users (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 full_name VARCHAR(120) NOT NULL,
 email VARCHAR(190) NOT NULL UNIQUE,
 phone VARCHAR(30) NULL,
 password_hash VARCHAR(255) NOT NULL,
 role ENUM('customer','staff','admin') NOT NULL DEFAULT 'customer',
 status ENUM('active','disabled') NOT NULL DEFAULT 'active',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 INDEX idx_users_role_status(role,status)
) ENGINE=InnoDB;

CREATE TABLE vehicles (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 customer_id INT UNSIGNED NOT NULL,
 vehicle_number VARCHAR(50) NOT NULL,
 vehicle_type ENUM('Motorcycle','Scooter','Car','Van','Bus','Truck','Other') NOT NULL,
 brand VARCHAR(80) NULL,
 model VARCHAR(80) NULL,
 color VARCHAR(40) NULL,
 status ENUM('active','inactive') NOT NULL DEFAULT 'active',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 UNIQUE KEY uq_customer_vehicle(customer_id,vehicle_number),
 INDEX idx_vehicle_number(vehicle_number),
 CONSTRAINT fk_vehicle_customer FOREIGN KEY(customer_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE parking_slots (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 slot_number VARCHAR(30) NOT NULL UNIQUE,
 vehicle_type ENUM('Motorcycle','Scooter','Car','Van','Bus','Truck','Other') NOT NULL,
 zone VARCHAR(50) NOT NULL DEFAULT 'A',
 status ENUM('available','reserved','occupied','maintenance','inactive') NOT NULL DEFAULT 'available',
 description VARCHAR(255) NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 INDEX idx_slot_status(status)
) ENGINE=InnoDB;

CREATE TABLE parking_rates (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 vehicle_type ENUM('Motorcycle','Scooter','Car','Van','Bus','Truck','Other') NOT NULL,
 first_hour_rate DECIMAL(10,2) NOT NULL DEFAULT 0,
 additional_hour_rate DECIMAL(10,2) NOT NULL DEFAULT 0,
 is_active TINYINT(1) NOT NULL DEFAULT 1,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_rate_type_active(vehicle_type,is_active)
) ENGINE=InnoDB;

CREATE TABLE bookings (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 booking_number VARCHAR(50) NOT NULL UNIQUE,
 customer_id INT UNSIGNED NOT NULL,
 vehicle_id INT UNSIGNED NOT NULL,
 slot_id INT UNSIGNED NOT NULL,
 booking_date DATE NOT NULL,
 expected_entry_time TIME NOT NULL,
 expected_exit_time TIME NOT NULL,
 status ENUM('pending','confirmed','active','completed','cancelled','expired') NOT NULL DEFAULT 'pending',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 INDEX idx_booking_slot_date(slot_id,booking_date,status),
 INDEX idx_booking_customer(customer_id,status),
 CONSTRAINT fk_booking_customer FOREIGN KEY(customer_id) REFERENCES users(id) ON DELETE RESTRICT,
 CONSTRAINT fk_booking_vehicle FOREIGN KEY(vehicle_id) REFERENCES vehicles(id) ON DELETE RESTRICT,
 CONSTRAINT fk_booking_slot FOREIGN KEY(slot_id) REFERENCES parking_slots(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE parking_records (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 parking_number VARCHAR(50) NOT NULL UNIQUE,
 booking_id INT UNSIGNED NULL,
 customer_id INT UNSIGNED NOT NULL,
 vehicle_id INT UNSIGNED NOT NULL,
 slot_id INT UNSIGNED NOT NULL,
 entry_time DATETIME NOT NULL,
 exit_time DATETIME NULL,
 parking_duration_minutes INT UNSIGNED NULL,
 calculated_fee DECIMAL(10,2) NOT NULL DEFAULT 0,
 status ENUM('active','completed','cancelled') NOT NULL DEFAULT 'active',
 staff_id INT UNSIGNED NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 INDEX idx_parking_active(status),
 INDEX idx_parking_customer(customer_id),
 CONSTRAINT fk_parking_booking FOREIGN KEY(booking_id) REFERENCES bookings(id) ON DELETE SET NULL,
 CONSTRAINT fk_parking_customer FOREIGN KEY(customer_id) REFERENCES users(id) ON DELETE RESTRICT,
 CONSTRAINT fk_parking_vehicle FOREIGN KEY(vehicle_id) REFERENCES vehicles(id) ON DELETE RESTRICT,
 CONSTRAINT fk_parking_slot FOREIGN KEY(slot_id) REFERENCES parking_slots(id) ON DELETE RESTRICT,
 CONSTRAINT fk_parking_staff FOREIGN KEY(staff_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE payments (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 parking_record_id INT UNSIGNED NOT NULL,
 customer_id INT UNSIGNED NOT NULL,
 amount DECIMAL(10,2) NOT NULL,
 payment_method ENUM('cash','online') NOT NULL,
 payment_status ENUM('paid','pending') NOT NULL DEFAULT 'paid',
 transaction_reference VARCHAR(120) NULL,
 paid_at DATETIME NULL,
 recorded_by INT UNSIGNED NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY uq_payment_parking(parking_record_id),
 CONSTRAINT fk_payment_parking FOREIGN KEY(parking_record_id) REFERENCES parking_records(id) ON DELETE RESTRICT,
 CONSTRAINT fk_payment_customer FOREIGN KEY(customer_id) REFERENCES users(id) ON DELETE RESTRICT,
 CONSTRAINT fk_payment_staff FOREIGN KEY(recorded_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE receipts (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 receipt_number VARCHAR(50) NOT NULL UNIQUE,
 parking_record_id INT UNSIGNED NOT NULL,
 payment_id INT UNSIGNED NOT NULL,
 customer_id INT UNSIGNED NOT NULL,
 issued_at DATETIME NOT NULL,
 UNIQUE KEY uq_receipt_payment(payment_id),
 CONSTRAINT fk_receipt_parking FOREIGN KEY(parking_record_id) REFERENCES parking_records(id) ON DELETE RESTRICT,
 CONSTRAINT fk_receipt_payment FOREIGN KEY(payment_id) REFERENCES payments(id) ON DELETE RESTRICT,
 CONSTRAINT fk_receipt_customer FOREIGN KEY(customer_id) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE notifications (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id INT UNSIGNED NOT NULL,
 title VARCHAR(160) NOT NULL,
 message TEXT NOT NULL,
 is_read TINYINT(1) NOT NULL DEFAULT 0,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_notification_user FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE audit_logs (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id INT UNSIGNED NULL,
 action VARCHAR(80) NOT NULL,
 module VARCHAR(80) NOT NULL,
 record_id INT UNSIGNED NULL,
 description VARCHAR(255) NULL,
 ip_address VARCHAR(45) NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_audit_created(created_at),
 CONSTRAINT fk_audit_user FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE contact_messages (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(120) NOT NULL,
 email VARCHAR(190) NOT NULL,
 message TEXT NOT NULL,
 status ENUM('new','read','replied') NOT NULL DEFAULT 'new',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE system_settings (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 setting_key VARCHAR(80) NOT NULL UNIQUE,
 setting_value TEXT NULL,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO parking_slots(slot_number,vehicle_type,zone,status) VALUES
('A01','Car','A','available'),('A02','Car','A','available'),('A03','Car','A','available'),('A04','Car','A','available'),
('B01','Motorcycle','B','available'),('B02','Motorcycle','B','available'),('B03','Motorcycle','B','available'),('B04','Motorcycle','B','available'),
('C01','Van','C','available'),('C02','Van','C','available');

INSERT INTO parking_rates(vehicle_type,first_hour_rate,additional_hour_rate,is_active) VALUES
('Motorcycle',20,10,1),('Scooter',20,10,1),('Car',50,30,1),('Van',80,40,1),('Bus',120,60,1),('Truck',120,60,1),('Other',50,30,1);

INSERT INTO system_settings(setting_key,setting_value) VALUES
('parking_name','SmartPark Vehicle Parking'),
('address','Kathmandu, Nepal'),
('phone','01-0000000'),
('email','parking@example.com'),
('operating_hours','6:00 AM - 10:00 PM');

-- Demo admin/staff credentials:
-- Password hashes below correspond to: Admin@123 / Staff@123
INSERT INTO users(full_name,email,phone,password_hash,role,status) VALUES
('System Administrator','admin@parking.local','9800000000','$2y$12$60lrlZkNWU5KtoZRDgLyheVcs2n5.2yczQorxqDhNRpi/sgFaLTR2','admin','active'),
('Parking Staff','staff@parking.local','9800000001','$2y$12$ILrTkIUFdyO1N4HaKN4HL.tnapvkUg5mrV9uwgIFdkzQlM6yv/XQ.','staff','active');
