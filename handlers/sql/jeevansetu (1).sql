-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 05, 2025 at 04:14 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `jeevansetu`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `appointment_id` int(11) NOT NULL,
  `donor_id` int(11) NOT NULL,
  `bank_id` int(11) DEFAULT NULL,
  `camp_id` int(11) DEFAULT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` time DEFAULT NULL,
  `status` enum('Booked','Attended','Cancelled') NOT NULL DEFAULT 'Booked',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`appointment_id`, `donor_id`, `bank_id`, `camp_id`, `appointment_date`, `appointment_time`, `status`, `created_at`) VALUES
(1, 1, NULL, 1, '2026-02-10', '09:00:00', 'Booked', '2025-12-05 12:22:01'),
(2, 5, NULL, 5, '2026-04-12', '11:00:00', 'Booked', '2025-12-05 12:22:01'),
(3, 2, NULL, 1, '2026-02-10', '10:00:00', 'Booked', '2025-12-05 12:22:01'),
(4, 6, NULL, 5, '2026-04-12', '10:00:00', 'Booked', '2025-12-05 12:22:01'),
(5, 9, NULL, 3, '2026-03-05', '09:00:00', 'Cancelled', '2025-12-05 12:22:01'),
(6, 10, 22, NULL, '2026-01-15', '13:00:00', 'Booked', '2025-12-05 12:22:01'),
(7, 7, 23, NULL, '2026-03-20', NULL, 'Booked', '2025-12-05 12:22:01'),
(8, 4, 24, NULL, '2026-01-20', '14:00:00', 'Cancelled', '2025-12-05 12:22:01'),
(9, 8, 21, NULL, '2026-03-01', '09:30:00', 'Attended', '2025-12-05 12:22:01'),
(10, 3, NULL, 4, '2025-11-01', '10:00:00', 'Attended', '2025-12-05 12:22:01');

-- --------------------------------------------------------

--
-- Table structure for table `blood_banks`
--

CREATE TABLE `blood_banks` (
  `bank_id` int(11) NOT NULL,
  `bank_name` varchar(150) NOT NULL,
  `license_number` varchar(50) DEFAULT NULL,
  `contact_person` varchar(100) NOT NULL,
  `phone_number` varchar(15) DEFAULT NULL,
  `address_line_1` varchar(255) DEFAULT NULL,
  `city` varchar(50) NOT NULL,
  `pincode` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `blood_banks`
--

INSERT INTO `blood_banks` (`bank_id`, `bank_name`, `license_number`, `contact_person`, `phone_number`, `address_line_1`, `city`, `pincode`) VALUES
(21, 'Jeevan Central Blood Bank', 'JCB111', 'Mr. Ramesh Jain', NULL, NULL, 'Mumbai', '400001'),
(22, 'North Zone Regional Bank', 'NZRB222', 'Ms. Anjali Singh', NULL, NULL, 'Delhi', '110001'),
(23, 'South Zone Apex Bank', 'SZAB333', 'Mr. Vivek Menon', NULL, NULL, 'Bangalore', '560001'),
(24, 'West District Blood Center', 'WDBC444', 'Mrs. Sunita Devi', NULL, NULL, 'Pune', '411001');

-- --------------------------------------------------------

--
-- Table structure for table `camps`
--

CREATE TABLE `camps` (
  `camp_id` int(11) NOT NULL,
  `bank_id` int(11) NOT NULL,
  `location_name` varchar(255) NOT NULL,
  `camp_date` date NOT NULL,
  `target_units` int(11) DEFAULT 0,
  `status` enum('Scheduled','Completed','Cancelled') NOT NULL DEFAULT 'Scheduled',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `camps`
--

INSERT INTO `camps` (`camp_id`, `bank_id`, `location_name`, `camp_date`, `target_units`, `status`, `created_at`) VALUES
(1, 21, 'City College Auditorium, Mumbai', '2026-02-10', 150, 'Scheduled', '2025-12-05 10:13:28'),
(2, 21, 'Gateway Mall Community Hall', '2026-01-25', 100, 'Scheduled', '2025-12-05 10:13:28'),
(3, 24, 'Pune Tech Park Office Drive', '2026-03-05', 200, 'Cancelled', '2025-12-05 10:13:28'),
(4, 24, 'Local School Gym', '2025-11-01', 80, 'Completed', '2025-12-05 10:13:28'),
(5, 22, 'Delhi University Main Campus', '2026-04-12', 300, 'Scheduled', '2025-12-05 10:13:28');

-- --------------------------------------------------------

--
-- Table structure for table `donors`
--

CREATE TABLE `donors` (
  `donor_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `blood_group` enum('A+','A-','B+','B-','AB+','AB-','O+','O-') NOT NULL,
  `gender` enum('Male','Female','Other') NOT NULL,
  `date_of_birth` date NOT NULL,
  `phone_number` varchar(15) DEFAULT NULL,
  `address_line_1` varchar(255) DEFAULT NULL,
  `city` varchar(50) NOT NULL,
  `pincode` varchar(10) NOT NULL,
  `last_donation_date` date DEFAULT NULL,
  `is_available` tinyint(1) DEFAULT 0,
  `organ_pledge_status` enum('Pledged','Not Pledged','Registered') DEFAULT 'Not Pledged'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `donors`
--

INSERT INTO `donors` (`donor_id`, `full_name`, `blood_group`, `gender`, `date_of_birth`, `phone_number`, `address_line_1`, `city`, `pincode`, `last_donation_date`, `is_available`, `organ_pledge_status`) VALUES
(1, 'Priya Sharma', 'O+', 'Female', '1995-05-10', NULL, NULL, 'Mumbai', '400001', '2025-09-01', 1, 'Pledged'),
(2, 'Rahul Verma', 'A-', 'Male', '1988-11-20', NULL, NULL, 'Delhi', '110001', '2024-12-15', 0, 'Not Pledged'),
(3, 'Anita Gupta', 'B+', 'Female', '1976-03-03', NULL, NULL, 'Bangalore', '560001', '2025-05-20', 1, 'Pledged'),
(4, 'Vijay Singh', 'AB+', 'Male', '2000-07-25', NULL, NULL, 'Chennai', '600001', '2025-10-10', 0, 'Registered'),
(5, 'Sneha Jain', 'O-', 'Female', '1999-01-01', NULL, NULL, 'Kolkata', '700001', '2025-08-05', 1, 'Pledged'),
(6, 'Aman Kumar', 'A+', 'Male', '1992-04-12', NULL, NULL, 'Pune', '411001', '2025-06-28', 0, 'Not Pledged'),
(7, 'Neha Yadav', 'B-', 'Female', '1985-09-15', NULL, NULL, 'Hyderabad', '500001', '2025-11-01', 1, 'Pledged'),
(8, 'Suresh Patel', 'O+', 'Male', '1970-12-30', NULL, NULL, 'Ahmedabad', '380001', '2025-07-19', 0, 'Not Pledged'),
(9, 'Deepa Reddy', 'AB-', 'Female', '1998-06-05', NULL, NULL, 'Jaipur', '302001', '2024-10-11', 1, 'Registered'),
(10, 'Karan Mehta', 'A+', 'Male', '1990-02-02', NULL, NULL, 'Lucknow', '226001', '2025-08-01', 0, 'Pledged'),
(11, 'Manish Rao', 'B+', 'Male', '1980-08-18', NULL, NULL, 'Bhopal', '462001', '2025-03-20', 1, 'Not Pledged'),
(12, 'Pooja Tiwari', 'O-', 'Female', '1993-10-22', NULL, NULL, 'Patna', '800001', '2025-11-15', 0, 'Pledged'),
(13, 'Vikas Dubey', 'A-', 'Male', '1978-01-28', NULL, NULL, 'Indore', '452001', '2025-07-07', 1, 'Not Pledged'),
(14, 'Ritu Malhotra', 'B-', 'Female', '1991-05-01', NULL, NULL, 'Chandigarh', '160001', '2025-04-14', 0, 'Registered'),
(15, 'Gopal Das', 'AB+', 'Male', '1965-03-01', NULL, NULL, 'Surat', '395001', '2025-09-25', 1, 'Pledged');

-- --------------------------------------------------------

--
-- Table structure for table `hospitals`
--

CREATE TABLE `hospitals` (
  `hospital_id` int(11) NOT NULL,
  `hospital_name` varchar(150) NOT NULL,
  `license_number` varchar(50) DEFAULT NULL,
  `contact_person` varchar(100) NOT NULL,
  `phone_number` varchar(15) DEFAULT NULL,
  `address_line_1` varchar(255) DEFAULT NULL,
  `city` varchar(50) NOT NULL,
  `pincode` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hospitals`
--

INSERT INTO `hospitals` (`hospital_id`, `hospital_name`, `license_number`, `contact_person`, `phone_number`, `address_line_1`, `city`, `pincode`) VALUES
(16, 'City General Hospital', 'CGH7890', 'Dr. Alok Nath', NULL, NULL, 'Mumbai', '400001'),
(17, 'Apollo Multi-Specialty', 'AMS1020', 'Ms. Leena Dsouza', NULL, NULL, 'Delhi', '110002'),
(18, 'Cardio Care Institute', 'CCI3040', 'Dr. Riya Sen', NULL, NULL, 'Bangalore', '560002'),
(19, 'St. Jude Children’s', 'SJC5060', 'Mr. David Raj', NULL, NULL, 'Chennai', '600003'),
(20, 'Trauma Emergency Unit', 'TEU7080', 'Dr. Kiran Bedi', NULL, NULL, 'Kolkata', '700004');

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `inventory_id` int(11) NOT NULL,
  `bank_id` int(11) NOT NULL,
  `blood_group` enum('A+','A-','B+','B-','AB+','AB-','O+','O-') NOT NULL,
  `component_type` enum('Whole Blood','Plasma','Platelets','Red Cells') NOT NULL,
  `units_available` int(11) NOT NULL DEFAULT 0,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`inventory_id`, `bank_id`, `blood_group`, `component_type`, `units_available`, `last_updated`) VALUES
(1, 21, 'O+', 'Whole Blood', 45, '2025-12-05 09:46:25'),
(2, 21, 'O-', 'Whole Blood', 8, '2025-12-05 09:46:25'),
(3, 22, 'A+', 'Whole Blood', 30, '2025-12-05 09:46:25'),
(4, 22, 'A-', 'Plasma', 15, '2025-12-05 09:46:25'),
(5, 23, 'B+', 'Whole Blood', 5, '2025-12-05 09:46:25'),
(6, 23, 'B-', 'Platelets', 12, '2025-12-05 09:46:25'),
(7, 24, 'AB+', 'Whole Blood', 25, '2025-12-05 09:46:25'),
(8, 24, 'AB-', 'Red Cells', 6, '2025-12-05 09:46:25'),
(9, 21, 'O+', 'Plasma', 20, '2025-12-05 09:46:25'),
(10, 22, 'B+', 'Plasma', 18, '2025-12-05 09:46:25'),
(11, 23, 'O-', 'Red Cells', 11, '2025-12-05 09:46:25'),
(12, 24, 'A+', 'Platelets', 7, '2025-12-05 09:46:25'),
(13, 21, 'A+', 'Whole Blood', 35, '2025-12-05 09:46:25'),
(14, 22, 'B-', 'Whole Blood', 28, '2025-12-05 09:46:25'),
(15, 23, 'AB+', 'Red Cells', 19, '2025-12-05 09:46:25'),
(16, 24, 'O-', 'Plasma', 4, '2025-12-05 09:46:25');

-- --------------------------------------------------------

--
-- Table structure for table `organ_recipients`
--

CREATE TABLE `organ_recipients` (
  `recipient_id` int(11) NOT NULL,
  `hospital_id` int(11) NOT NULL,
  `recipient_name` varchar(100) NOT NULL,
  `required_organ` enum('Kidney','Liver','Heart','Lung','Pancreas','Intestine') NOT NULL,
  `blood_group` enum('A+','A-','B+','B-','AB+','AB-','O+','O-') NOT NULL,
  `urgency_level` enum('Critical','Urgent','Routine') NOT NULL,
  `tissue_type_hla` varchar(50) DEFAULT NULL,
  `waitlist_date` date NOT NULL,
  `status` enum('Active','Matched','Transplanted','Removed') DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `organ_recipients`
--

INSERT INTO `organ_recipients` (`recipient_id`, `hospital_id`, `recipient_name`, `required_organ`, `blood_group`, `urgency_level`, `tissue_type_hla`, `waitlist_date`, `status`) VALUES
(1, 17, 'Rakesh Tiwari', 'Kidney', 'O+', 'Critical', 'A1, B8', '2025-01-05', 'Active'),
(2, 16, 'Farah Khan', 'Liver', 'B-', 'Urgent', 'B7, DR4', '2025-03-12', 'Active'),
(3, 18, 'Amitabh Sahoo', 'Heart', 'A+', 'Critical', 'A2, DR1', '2025-10-20', 'Active'),
(4, 19, 'Leela Prasad', 'Kidney', 'A-', 'Routine', 'B44, DR7', '2024-06-01', 'Active'),
(5, 17, 'Zoya Ali', 'Lung', 'O-', 'Urgent', 'A3, B15', '2025-08-08', 'Active'),
(6, 16, 'Rohan Mehra', 'Kidney', 'AB+', 'Routine', 'A2, B7', '2024-09-19', 'Active'),
(7, 18, 'Tara Devi', 'Liver', 'O+', 'Urgent', 'B8, DR1', '2025-11-11', 'Active'),
(8, 19, 'Sandeep Kaur', 'Heart', 'B+', 'Critical', 'A1, B5', '2025-12-01', 'Active'),
(9, 17, 'Naveen Singh', 'Pancreas', 'A+', 'Routine', 'B40, DR4', '2025-02-28', 'Active'),
(10, 16, 'Meena Varma', 'Kidney', 'O-', 'Critical', 'B7, DR7', '2025-10-05', 'Active'),
(11, 18, 'Deepak Jain', 'Liver', 'AB-', 'Urgent', 'A2, B44', '2025-07-14', 'Active'),
(12, 19, 'Hina Malik', 'Lung', 'O+', 'Routine', 'B8, B15', '2024-11-20', 'Active'),
(13, 20, 'Vijay Mallya', 'Kidney', 'A+', 'Urgent', 'A1, DR1', '2025-09-09', 'Active'),
(14, 20, 'Preeti Das', 'Heart', 'B-', 'Critical', 'B7, B5', '2025-11-25', 'Active'),
(15, 20, 'Kunal Joshi', 'Liver', 'O-', 'Routine', 'B40, DR7', '2024-10-01', 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `requests`
--

CREATE TABLE `requests` (
  `request_id` int(11) NOT NULL,
  `hospital_id` int(11) NOT NULL,
  `requested_group` enum('A+','A-','B+','B-','AB+','AB-','O+','O-') NOT NULL,
  `units_needed` int(11) NOT NULL,
  `urgency_level` enum('Critical','Urgent','Routine') NOT NULL,
  `request_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('Pending','In Progress','Fulfilled','Cancelled') NOT NULL DEFAULT 'Pending',
  `fulfilled_by_bank_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `requests`
--

INSERT INTO `requests` (`request_id`, `hospital_id`, `requested_group`, `units_needed`, `urgency_level`, `request_time`, `status`, `fulfilled_by_bank_id`) VALUES
(1, 16, 'O-', 5, 'Critical', '2025-12-05 09:46:25', 'Pending', NULL),
(2, 17, 'A+', 10, 'Urgent', '2025-12-05 09:46:25', 'Pending', NULL),
(3, 18, 'B+', 2, 'Routine', '2025-12-05 09:46:25', 'Pending', NULL),
(4, 19, 'AB-', 7, 'Critical', '2025-12-05 09:46:25', 'Fulfilled', NULL),
(5, 20, 'O+', 3, 'Urgent', '2025-12-05 09:46:25', 'Pending', NULL),
(6, 16, 'A-', 4, 'Routine', '2025-12-05 09:46:25', 'Pending', NULL),
(7, 17, 'O-', 12, 'Critical', '2025-12-05 09:46:25', 'Pending', NULL),
(8, 18, 'AB+', 6, 'Urgent', '2025-12-05 09:46:25', 'In Progress', NULL),
(9, 19, 'B-', 1, 'Routine', '2025-12-05 09:46:25', 'Fulfilled', NULL),
(10, 20, 'A+', 8, 'Urgent', '2025-12-05 09:46:25', 'Pending', NULL),
(11, 16, 'B+', 5, 'Critical', '2025-12-05 09:46:25', 'Pending', NULL),
(12, 17, 'O+', 15, 'Routine', '2025-12-05 09:46:25', 'Pending', NULL),
(13, 18, 'O-', 9, 'Critical', '2025-12-05 09:46:25', 'In Progress', NULL),
(14, 19, 'A+', 2, 'Urgent', '2025-12-05 09:46:25', 'Pending', NULL),
(15, 20, 'AB+', 4, 'Routine', '2025-12-05 09:46:25', 'Fulfilled', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(50) NOT NULL,
  `user_type` enum('donor','hospital','blood_bank','admin') NOT NULL,
  `status` enum('active','inactive','suspended') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `email`, `password_hash`, `user_type`, `status`, `created_at`) VALUES
(1, 'priya.sharma@donor.com', 'password123', 'donor', 'active', '2025-12-05 08:20:10'),
(2, 'rahul.verma@donor.com', 'password123', 'donor', 'active', '2025-12-05 08:20:10'),
(3, 'anita.gupta@donor.com', 'password123', 'donor', 'active', '2025-12-05 08:20:10'),
(4, 'vijay.singh@donor.com', 'password123', 'donor', 'active', '2025-12-05 08:20:10'),
(5, 'sneha.jain@donor.com', 'password123', 'donor', 'active', '2025-12-05 08:20:10'),
(6, 'aman.kumar@donor.com', 'password123', 'donor', 'active', '2025-12-05 08:20:10'),
(7, 'neha.yadav@donor.com', 'password123', 'donor', 'active', '2025-12-05 08:20:10'),
(8, 'suresh.patel@donor.com', 'password123', 'donor', 'active', '2025-12-05 08:20:10'),
(9, 'deepa.reddy@donor.com', 'password123', 'donor', 'active', '2025-12-05 08:20:10'),
(10, 'karan.mehta@donor.com', 'password123', 'donor', 'active', '2025-12-05 08:20:10'),
(11, 'manish.rao@donor.com', 'password123', 'donor', 'active', '2025-12-05 08:20:10'),
(12, 'pooja.tiwari@donor.com', 'password123', 'donor', 'active', '2025-12-05 08:20:10'),
(13, 'vikas.dubey@donor.com', 'password123', 'donor', 'active', '2025-12-05 08:20:10'),
(14, 'ritu.malhotra@donor.com', 'password123', 'donor', 'active', '2025-12-05 08:20:10'),
(15, 'gopal.das@donor.com', 'password123', 'donor', 'active', '2025-12-05 08:20:10'),
(16, 'city_general@hospital.com', 'password123', 'hospital', 'active', '2025-12-05 08:20:10'),
(17, 'apollo_admin@hospital.com', 'password123', 'hospital', 'active', '2025-12-05 08:20:10'),
(18, 'cardio_care@hospital.com', 'password123', 'hospital', 'active', '2025-12-05 08:20:10'),
(19, 'st_judes@hospital.com', 'password123', 'hospital', 'active', '2025-12-05 08:20:10'),
(20, 'trauma_unit@hospital.com', 'password123', 'hospital', 'active', '2025-12-05 08:20:10'),
(21, 'central_bank@bloodbank.com', 'password123', 'blood_bank', 'active', '2025-12-05 08:20:10'),
(22, 'north_zone@bloodbank.com', 'password123', 'blood_bank', 'active', '2025-12-05 08:20:10'),
(23, 'south_zone@bloodbank.com', 'password123', 'blood_bank', 'active', '2025-12-05 08:20:10'),
(24, 'west_district@bloodbank.com', 'password123', 'blood_bank', 'active', '2025-12-05 08:20:10'),
(25, 'admin@jeevansetu.gov', 'password123', 'admin', 'active', '2025-12-05 08:20:10');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`appointment_id`),
  ADD KEY `donor_id` (`donor_id`),
  ADD KEY `bank_id` (`bank_id`),
  ADD KEY `camp_id` (`camp_id`);

--
-- Indexes for table `blood_banks`
--
ALTER TABLE `blood_banks`
  ADD PRIMARY KEY (`bank_id`),
  ADD UNIQUE KEY `license_number` (`license_number`),
  ADD UNIQUE KEY `phone_number` (`phone_number`);

--
-- Indexes for table `camps`
--
ALTER TABLE `camps`
  ADD PRIMARY KEY (`camp_id`),
  ADD KEY `bank_id` (`bank_id`);

--
-- Indexes for table `donors`
--
ALTER TABLE `donors`
  ADD PRIMARY KEY (`donor_id`),
  ADD UNIQUE KEY `phone_number` (`phone_number`);

--
-- Indexes for table `hospitals`
--
ALTER TABLE `hospitals`
  ADD PRIMARY KEY (`hospital_id`),
  ADD UNIQUE KEY `license_number` (`license_number`),
  ADD UNIQUE KEY `phone_number` (`phone_number`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`inventory_id`),
  ADD UNIQUE KEY `bank_group_component` (`bank_id`,`blood_group`,`component_type`);

--
-- Indexes for table `organ_recipients`
--
ALTER TABLE `organ_recipients`
  ADD PRIMARY KEY (`recipient_id`),
  ADD KEY `hospital_id` (`hospital_id`);

--
-- Indexes for table `requests`
--
ALTER TABLE `requests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `hospital_id` (`hospital_id`),
  ADD KEY `fulfilled_by_bank_id` (`fulfilled_by_bank_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `appointment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `camps`
--
ALTER TABLE `camps`
  MODIFY `camp_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `inventory_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `organ_recipients`
--
ALTER TABLE `organ_recipients`
  MODIFY `recipient_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `requests`
--
ALTER TABLE `requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`donor_id`) REFERENCES `donors` (`donor_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`bank_id`) REFERENCES `blood_banks` (`bank_id`),
  ADD CONSTRAINT `appointments_ibfk_3` FOREIGN KEY (`camp_id`) REFERENCES `camps` (`camp_id`);

--
-- Constraints for table `blood_banks`
--
ALTER TABLE `blood_banks`
  ADD CONSTRAINT `blood_banks_ibfk_1` FOREIGN KEY (`bank_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `camps`
--
ALTER TABLE `camps`
  ADD CONSTRAINT `camps_ibfk_1` FOREIGN KEY (`bank_id`) REFERENCES `blood_banks` (`bank_id`) ON DELETE CASCADE;

--
-- Constraints for table `donors`
--
ALTER TABLE `donors`
  ADD CONSTRAINT `donors_ibfk_1` FOREIGN KEY (`donor_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `hospitals`
--
ALTER TABLE `hospitals`
  ADD CONSTRAINT `hospitals_ibfk_1` FOREIGN KEY (`hospital_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `inventory`
--
ALTER TABLE `inventory`
  ADD CONSTRAINT `inventory_ibfk_1` FOREIGN KEY (`bank_id`) REFERENCES `blood_banks` (`bank_id`) ON DELETE CASCADE;

--
-- Constraints for table `organ_recipients`
--
ALTER TABLE `organ_recipients`
  ADD CONSTRAINT `organ_recipients_ibfk_1` FOREIGN KEY (`hospital_id`) REFERENCES `hospitals` (`hospital_id`);

--
-- Constraints for table `requests`
--
ALTER TABLE `requests`
  ADD CONSTRAINT `requests_ibfk_1` FOREIGN KEY (`hospital_id`) REFERENCES `hospitals` (`hospital_id`),
  ADD CONSTRAINT `requests_ibfk_2` FOREIGN KEY (`fulfilled_by_bank_id`) REFERENCES `blood_banks` (`bank_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
