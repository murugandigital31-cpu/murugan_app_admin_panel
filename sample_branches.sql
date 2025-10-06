-- Sample data for todays_arrival_branches table
-- Import this into your MySQL database

INSERT INTO `todays_arrival_branches` (`name`, `location`, `whatsapp_number`, `contact_person`, `address`, `is_active`, `created_at`, `updated_at`) VALUES
('Main Branch - Al Qusais', 'Al Qusais', '+971501234567', 'Ahmed', 'Al Qusais Industrial Area, Dubai, UAE', 1, NOW(), NOW()),
('Sharjah Branch', 'Sharjah', '+971507654321', 'Mohammed', 'Sharjah Industrial Area, Sharjah, UAE', 1, NOW(), NOW()),
('Abu Dhabi Branch', 'Abu Dhabi', '+971509876543', 'Hassan', 'Abu Dhabi Industrial Area, Abu Dhabi, UAE', 1, NOW(), NOW()),
('Ajman Branch', 'Ajman', '+971505432109', 'Omar', 'Ajman Industrial Area, Ajman, UAE', 1, NOW(), NOW());