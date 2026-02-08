-- Create database (optional; you can also do this in phpMyAdmin)
CREATE DATABASE IF NOT EXISTS crop_yield_dss
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;

USE crop_yield_dss;

-- ---------------------------
-- 1) Users
-- ---------------------------
CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  full_name VARCHAR(120) NOT NULL,
  username VARCHAR(50) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin','user') NOT NULL DEFAULT 'user',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_username (username)
) ENGINE=InnoDB;

-- ---------------------------
-- 2) Predictions
-- ---------------------------
CREATE TABLE IF NOT EXISTS predictions (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

  user_id INT UNSIGNED NULL,

 
  crop_type VARCHAR(50) NOT NULL,          
  district VARCHAR(80) NOT NULL,        
  season VARCHAR(30) NOT NULL, 

 
  farm_size_acres DECIMAL(8,2) NOT NULL,   
  soil_type ENUM('sandy','clay','loam','silt','other') NOT NULL DEFAULT 'other',
  rainfall_mm DECIMAL(8,2) NOT NULL,       
  avg_temp_c DECIMAL(5,2) NOT NULL,        
  fertilizer_kg DECIMAL(10,2) NOT NULL DEFAULT 0,
  irrigation ENUM('yes','no') NOT NULL DEFAULT 'no',
  seed_type ENUM('local','improved','hybrid','other') NOT NULL DEFAULT 'other',


  predicted_yield_tons DECIMAL(10,2) NOT NULL,      
  predicted_yield_tpa DECIMAL(10,2) NOT NULL,      
  risk_level ENUM('low','medium','high') NOT NULL DEFAULT 'medium',
  recommendations TEXT NULL,

  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (id),

  KEY idx_predictions_created_at (created_at),
  KEY idx_predictions_crop (crop_type),
  KEY idx_predictions_district (district),
  KEY idx_predictions_season (season),
  KEY idx_predictions_user (user_id),

  CONSTRAINT fk_predictions_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE SET NULL
    ON UPDATE CASCADE
) ENGINE=InnoDB;
