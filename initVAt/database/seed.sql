
CREATE DATABASE IF NOT EXISTS vat_db;
USE vat_db;

CREATE TABLE IF NOT EXISTS vat_numbers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  original_value VARCHAR(255),
  final_value VARCHAR(255),
  status ENUM('valid','corrected','invalid') DEFAULT 'invalid',
  correction_or_error TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE uploads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(255),
    status ENUM('processing','done','error') DEFAULT 'processing',
    total_rows INT DEFAULT 0,
    processed_rows INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
