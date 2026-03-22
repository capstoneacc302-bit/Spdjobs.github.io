-- SPD Jobs Inc. - Recruitment & Employee Status Monitoring System
-- Database Schema
-- Created for: SPD Jobs Inc. Bataan Branch

CREATE DATABASE IF NOT EXISTS spd_jobs_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE spd_jobs_db;

-- =============================================
-- USERS TABLE (Applicants)
-- =============================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    contact_number VARCHAR(20),
    gender ENUM('Male','Female','Other'),
    date_of_birth DATE,
    address TEXT,
    profile_photo VARCHAR(255) DEFAULT NULL,
    sss_number VARCHAR(20),
    philhealth_number VARCHAR(20),
    pagibig_number VARCHAR(20),
    tin_number VARCHAR(20),
    is_verified TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =============================================
-- ADMINS TABLE (HR / Admin)
-- =============================================
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','hr','superadmin') DEFAULT 'hr',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =============================================
-- JOB CATEGORIES
-- =============================================
CREATE TABLE job_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =============================================
-- JOBS TABLE
-- =============================================
CREATE TABLE jobs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    title VARCHAR(150) NOT NULL,
    company VARCHAR(150),
    location VARCHAR(200) DEFAULT 'Hermosa Ecozone, Bataan',
    shift ENUM('Day Shift','Night Shift','Shifting','Any') DEFAULT 'Shifting',
    employment_type ENUM('Contract','Project-based','Regular') DEFAULT 'Contract',
    salary_min DECIMAL(10,2),
    salary_max DECIMAL(10,2),
    salary_display VARCHAR(100),
    slots INT DEFAULT 1,
    description TEXT,
    responsibilities TEXT,
    requirements TEXT,
    benefits TEXT,
    experience_required TINYINT(1) DEFAULT 0,
    accepts_fresh_grad TINYINT(1) DEFAULT 1,
    is_urgent TINYINT(1) DEFAULT 0,
    is_featured TINYINT(1) DEFAULT 0,
    status ENUM('open','closed','paused') DEFAULT 'open',
    deadline DATE,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES job_categories(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES admins(id) ON DELETE SET NULL
);

-- =============================================
-- APPLICATIONS TABLE
-- =============================================
CREATE TABLE applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    job_id INT NOT NULL,
    -- Personal Information
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    contact_number VARCHAR(20),
    date_of_birth DATE,
    gender ENUM('Male','Female','Other'),
    address TEXT,
    -- Education
    highest_education ENUM('Elementary','High School','Senior High School','Vocational','College','Post Graduate'),
    school_name VARCHAR(200),
    course_strand VARCHAR(150),
    year_graduated YEAR,
    -- Job preference
    preferred_shift ENUM('Day Shift','Night Shift','Any Shift') DEFAULT 'Any Shift',
    -- Application tracking
    status ENUM(
        'pending',
        'for_exam',
        'for_initial_interview',
        'for_medical',
        'for_final_interview',
        'for_orientation',
        'approved',
        'declined'
    ) DEFAULT 'pending',
    admin_notes TEXT,
    reviewed_by INT,
    reviewed_at TIMESTAMP NULL,
    -- Document uploads
    resume_path VARCHAR(255),
    id_path VARCHAR(255),
    diploma_path VARCHAR(255),
    clearance_path VARCHAR(255),
    photo_path VARCHAR(255),
    -- Source tracking
    application_source ENUM('online','walk-in') DEFAULT 'online',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES admins(id) ON DELETE SET NULL
);

-- =============================================
-- ADVERTISEMENTS TABLE
-- =============================================
CREATE TABLE advertisements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    body TEXT,
    cta_text VARCHAR(50) DEFAULT 'Apply Now',
    cta_link VARCHAR(255),
    image_path VARCHAR(255),
    is_active TINYINT(1) DEFAULT 1,
    start_date DATE,
    end_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =============================================
-- NOTIFICATIONS TABLE
-- =============================================
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    application_id INT,
    title VARCHAR(200),
    message TEXT,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE SET NULL
);

-- =============================================
-- DEFAULT DATA
-- =============================================

INSERT INTO admins (full_name, email, password, role) VALUES
('Maria Santos', 'admin@spdjobs.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'superadmin'),
('Jose Reyes', 'hr@spdjobs.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'hr');
-- Default password for both: password

INSERT INTO job_categories (name, description) VALUES
('Production / Factory', 'Assembly line, machine operation, and factory floor work'),
('Quality Control', 'QC and QA inspection and standards enforcement'),
('Warehouse / Logistics', 'Inventory, stock management, and warehouse operations'),
('Technical / Skilled', 'Maintenance, engineering, and technical positions');

INSERT INTO jobs (category_id, title, company, shift, employment_type, salary_min, salary_max, salary_display, slots, description, responsibilities, requirements, benefits, accepts_fresh_grad, is_urgent, is_featured, status) VALUES
(1, 'Production Worker', 'Electronics Manufacturing Co. – Hermosa Ecozone', 'Shifting', 'Contract', 570.00, 570.00, '₱570/day (minimum wage)', 15,
'Assembly line worker responsible for manufacturing electronic components inside the Hermosa Ecozone industrial complex.',
'• Assembly line operations\n• Wiring and product assembly\n• Repetitive factory operations\n• Reporting defects to QC team',
'• Resume / Biodata with 2x2 photo\n• 1–2 valid government IDs\n• High School or SHS Diploma\n• Barangay Clearance\n• 2x2 ID photos (white background)',
'• SSS, PhilHealth, Pag-IBIG\n• 13th month pay\n• Overtime pay\n• Basic training provided', 1, 1, 1, 'open'),

(1, 'Production Helper', 'Industrial Manufacturing Corp. – Hermosa Ecozone', 'Shifting', 'Contract', 570.00, 570.00, '₱570/day', 10,
'Support role assisting production workers on the factory floor.',
'• Assist production workers\n• Material handling\n• Cleaning and organizing workstation\n• Basic assembly tasks',
'• Resume / Biodata\n• Valid government ID\n• High School Diploma',
'• SSS, PhilHealth, Pag-IBIG\n• 13th month pay\n• Overtime pay', 1, 0, 0, 'open'),

(1, 'Assembler', 'Cable Manufacturing Inc. – Hermosa Ecozone', 'Shifting', 'Contract', 570.00, 590.00, '₱570–₱590/day', 8,
'Assembles cable and wire components following production standards.',
'• Manual cable and wire assembly\n• Following assembly diagrams\n• Quality checking assembled parts',
'• Resume / Biodata with 2x2 photo\n• Valid government ID\n• HS/SHS Diploma',
'• SSS, PhilHealth, Pag-IBIG\n• 13th month pay\n• Overtime pay', 1, 0, 0, 'open'),

(1, 'Machine Operator', 'Industrial Manufacturing Corp. – Hermosa Ecozone', 'Shifting', 'Contract', 600.00, 650.00, '₱600–₱650/day', 5,
'Operates industrial machines in the manufacturing floor with safety compliance.',
'• Operating assigned machines\n• Monitoring output quality\n• Basic machine maintenance\n• Safety compliance',
'• Resume / Biodata\n• Valid government ID\n• HS/SHS Diploma\n• Experience preferred but not required',
'• SSS, PhilHealth, Pag-IBIG\n• 13th month pay\n• Overtime pay', 1, 1, 0, 'open'),

(1, 'Packaging Staff', 'Cable Manufacturing Inc. – Hermosa Ecozone', 'Shifting', 'Contract', 570.00, 570.00, '₱570/day', 12,
'Responsible for packaging finished products according to company standards.',
'• Packaging finished goods\n• Labeling products\n• Counting and recording output\n• Maintaining clean work area',
'• Resume / Biodata\n• Valid government ID\n• HS/SHS Diploma',
'• SSS, PhilHealth, Pag-IBIG\n• 13th month pay', 1, 0, 0, 'open'),

(2, 'Quality Control Inspector', 'Electronics Manufacturing Co. – Hermosa Ecozone', 'Day Shift', 'Contract', 580.00, 620.00, '₱580–₱620/day', 5,
'Inspects products for defects and ensures production meets quality standards.',
'• Checking product quality\n• Inspecting for defects\n• Ensuring production standards\n• Documenting inspection results',
'• Resume / Biodata with 2x2 photo\n• Valid government ID\n• HS/SHS Diploma\n• Attention to detail',
'• SSS, PhilHealth, Pag-IBIG\n• 13th month pay\n• Overtime pay', 1, 0, 1, 'open'),

(2, 'Quality Assurance Staff', 'Industrial Manufacturing Corp. – Hermosa Ecozone', 'Day Shift', 'Contract', 580.00, 620.00, '₱580–₱620/day', 3,
'Supports QA processes in the manufacturing line.',
'• Monitoring production processes\n• Documenting quality metrics\n• Reporting non-conformances',
'• Resume / Biodata\n• Valid government ID\n• HS/SHS or College Diploma',
'• SSS, PhilHealth, Pag-IBIG\n• 13th month pay', 1, 0, 0, 'open'),

(3, 'Warehouse Staff', 'Hermosa Ecozone – Logistics Division', 'Day Shift', 'Contract', 570.00, 570.00, '₱570/day', 8,
'Manages warehouse operations including receiving, storing, and dispatching goods.',
'• Organizing goods in warehouse\n• Inventory monitoring\n• Loading/unloading tasks\n• Record keeping',
'• Resume / Biodata\n• Valid government ID\n• HS/SHS Diploma',
'• SSS, PhilHealth, Pag-IBIG\n• 13th month pay\n• Overtime pay', 1, 0, 1, 'open'),

(3, 'Inventory Clerk', 'Hermosa Ecozone – Logistics Division', 'Day Shift', 'Contract', 570.00, 590.00, '₱570–₱590/day', 4,
'Handles inventory records and stock monitoring in the warehouse.',
'• Maintaining inventory records\n• Conducting stock counts\n• Coordinating with production team\n• Data entry',
'• Resume / Biodata\n• Valid government ID\n• HS/SHS Diploma\n• Basic computer knowledge preferred',
'• SSS, PhilHealth, Pag-IBIG\n• 13th month pay', 1, 0, 0, 'open'),

(4, 'Maintenance Technician', 'Electronics Manufacturing Co. – Hermosa Ecozone', 'Day Shift', 'Contract', 700.00, 800.00, '₱700–₱800/day', 2,
'Responsible for maintaining and repairing industrial equipment and machinery.',
'• Preventive maintenance of machines\n• Troubleshooting equipment faults\n• Electrical and mechanical repairs\n• Maintenance documentation',
'• Resume / Biodata\n• Valid government ID\n• Technical/Vocational or College Diploma\n• Experience in industrial maintenance required',
'• SSS, PhilHealth, Pag-IBIG\n• 13th month pay\n• Overtime pay', 0, 0, 0, 'open');

INSERT INTO advertisements (title, body, cta_text, is_active) VALUES
('URGENT HIRING: Production Workers Needed', 'Electronics manufacturing company is urgently hiring production workers for day and night shifts at Hermosa Ecozone. Fresh graduates welcome. Apply before slots are filled!', 'Apply Now', 1),
('No SSS/PhilHealth Yet? No Problem!', 'SPD Jobs Inc. can assist you in processing your government numbers after you get hired. Walk in today!', 'Learn More', 1);
