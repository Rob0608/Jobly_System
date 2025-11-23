-- Add last_login column to track user activity

USE company;

-- Add to applicants table
ALTER TABLE applicants 
ADD COLUMN last_login DATETIME DEFAULT NULL AFTER status;

-- Add to companies table
ALTER TABLE companies 
ADD COLUMN last_login DATETIME DEFAULT NULL AFTER status;

-- Add to users table (admin)
ALTER TABLE users 
ADD COLUMN last_login DATETIME DEFAULT NULL;

-- Verify columns added
SELECT TABLE_NAME, COLUMN_NAME, DATA_TYPE 
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = 'company' 
AND COLUMN_NAME = 'last_login';
