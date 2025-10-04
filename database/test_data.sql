-- Test Data for Today's Arrivals
-- Run this SQL in your database to add sample data

-- First, ensure we have at least one branch (update existing data structure)
UPDATE branches SET whatsapp_number = phone WHERE whatsapp_number IS NULL OR whatsapp_number = '';

-- Update existing today's arrivals to have proper branch_id structure
UPDATE todays_arrivals 
SET branch_id = JSON_ARRAY(1) 
WHERE id = 1 AND (branch_id IS NULL OR JSON_LENGTH(branch_id) = 0);

-- Add more sample data
INSERT INTO todays_arrivals (title, description, arrival_date, branch_id, poster_images, product_ids, is_active, show_in_app, sort_order, created_at, updated_at) 
VALUES 
(
    'Premium Roses Collection',
    'Beautiful premium quality roses in various colors. Perfect for special occasions and gifts.',
    CURDATE(),
    JSON_ARRAY(1),
    JSON_ARRAY('arrivals/premium_roses_1.jpg', 'arrivals/premium_roses_2.jpg'),
    JSON_ARRAY(1, 2),
    1,
    1,
    1,
    NOW(),
    NOW()
),
(
    'Fresh Lily Arrangements',
    'Elegant white and pink lilies arranged beautifully. Great for weddings and formal events.',
    CURDATE(),
    JSON_ARRAY(1),
    JSON_ARRAY('arrivals/lily_arrangement_1.jpg'),
    JSON_ARRAY(2, 3),
    1,
    1,
    2,
    NOW(),
    NOW()
);

-- Verify the data was inserted/updated
SELECT 
    id,
    title,
    arrival_date,
    branch_id,
    JSON_LENGTH(branch_id) as branches_count,
    JSON_LENGTH(product_ids) as products_count,
    is_active,
    show_in_app
FROM todays_arrivals 
WHERE arrival_date = CURDATE();