-- SQL script to create the admin_blood_request table
CREATE TABLE IF NOT EXISTS admin_blood_request (
    request_id INT AUTO_INCREMENT PRIMARY KEY,
    hospital_name VARCHAR(255) NOT NULL,
    blood_component ENUM('Whole Blood', 'Packed RBC', 'Plasma', 'Platelets') NOT NULL,
    blood_group ENUM('A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-') NOT NULL,
    amount_ml INT NOT NULL,
    number_of_bags INT GENERATED ALWAYS AS (CEIL(amount_ml / 450)) STORED,
    request_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
); 