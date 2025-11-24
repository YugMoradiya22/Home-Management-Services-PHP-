-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 05, 2025 at 02:46 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `fixzydb`
--

-- --------------------------------------------------------

--
-- Table structure for table `admintable`
--

CREATE TABLE `admintable` (
  `Email` varchar(20) NOT NULL,
  `Password` varchar(288) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admintable`
--

INSERT INTO `admintable` (`Email`, `Password`) VALUES
('admin@fixzy.com', 'admin123'),
('admin1@fixzy.com', 'admin456'),
('admin2@fixzy.com', 'admin789');

-- --------------------------------------------------------

--
-- Table structure for table `bookingservice`
--

CREATE TABLE `bookingservice` (
  `ID` int(5) NOT NULL,
  `FullName` varchar(40) DEFAULT NULL,
  `Email` varchar(30) NOT NULL,
  `PhoneNumber` varchar(15) DEFAULT NULL,
  `Service` varchar(20) NOT NULL,
  `Address` varchar(80) NOT NULL,
  `Date` date NOT NULL,
  `Time` varchar(30) DEFAULT NULL,
  `Description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookingservice`
--

INSERT INTO `bookingservice` (`ID`, `FullName`, `Email`, `PhoneNumber`, `Service`, `Address`, `Date`, `Time`, `Description`) VALUES
(17, 'Amit Sharma', 'amit.sharma@gmail.com', '9876000001', 'Plumbing', '12 MG Road, Delhi\', \'2025-09-01', '2025-08-29', '11:41 AM', 'Kitchen sink leaking, needs repair'),
(18, 'Neha Verma', 'neha.verma@gmail.com', '9876000002', 'Electrical', '45 Park Street, Mumbai', '2025-08-29', '01:43 PM', '45 Park Street, Mumbai'),
(19, 'Rohit Kapoor', 'rohit.kapoor@gmail.com', '9876000003', 'Painting', 'A-65 Venus appartment katargam', '2025-08-30', '03:41 PM', 'Cook required for dinner party');

-- --------------------------------------------------------

--
-- Table structure for table `rescustomer`
--

CREATE TABLE `rescustomer` (
  `Id` int(5) NOT NULL,
  `FirstName` varchar(20) NOT NULL,
  `LastName` varchar(20) NOT NULL,
  `EMail` varchar(40) NOT NULL,
  `Phone` varchar(20) DEFAULT NULL,
  `Password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rescustomer`
--

INSERT INTO `rescustomer` (`Id`, `FirstName`, `LastName`, `EMail`, `Phone`, `Password`) VALUES
(34, 'Rajubhai', 'Vora', 'raju@gmail.com', '9876789876', '$2y$10$0LoYmoTsCWrGEByZdb5cr.iALG9mGu0oAVIkcmVznY.FJOuvtj0zC'),
(35, 'Sai', 'Surdharshan', 'sai@gmail.com', '9898989898', '$2y$10$u2oamMJOy/OGPv9Osomdl.J2C7LpdBIZ1tcykOKZCiBuu3ZQ0BXuq'),
(36, 'Kanobhai', 'Shah', 'kano@gmail.com', '9999888856', '$2y$10$htDmqnVXcMEJdALMdOXnDeqgWuWX.hN/jH1old4gffvM7XuEahCyK'),
(37, 'Ankit', 'Patel', 'ankit@gmail.com', '9876543210', '$2y$10$examplehash1'),
(38, 'Sneha', 'Mehta', 'sneha@gmail.com', '9898989898', '$2y$10$9oWVb90azCwcxUbZql5WwOrqd2Je7ZMbYP2Gj9i3ZV0XTeUmSFXIW'),
(39, 'Rahul', 'Joshi', 'rahul@gmail.com', 'rahul@gmail.com', '$2y$10$J4qj17RJ3aJoeFQbv8uiH.Bq2/yK6DZVdpFWzu7QzpUE5cEvA2VZy');

-- --------------------------------------------------------

--
-- Table structure for table `restaff`
--

CREATE TABLE `restaff` (
  `ID` int(5) NOT NULL,
  `FirstName` varchar(20) NOT NULL,
  `LastName` varchar(20) NOT NULL,
  `Email` varchar(25) NOT NULL,
  `PhoneNumber` varchar(20) NOT NULL,
  `Skill` varchar(30) NOT NULL,
  `Experience` varchar(3) NOT NULL,
  `Photo` varchar(50) NOT NULL,
  `Password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `restaff`
--

INSERT INTO `restaff` (`ID`, `FirstName`, `LastName`, `Email`, `PhoneNumber`, `Skill`, `Experience`, `Photo`, `Password`) VALUES
(1, 'Amit', 'Sharma', 'amit.sharma@gmail.com', '9876000001', 'Cleaner', '4', 'uploads/1756220432_person1.jpg', 'staff1'),
(2, 'Neha', 'Verma', 'neha.verma@gmail.com', '9876000002', 'Plumber', '2', 'uploads/1756220483_person2.jpg', 'staff2'),
(3, 'Rohit', 'Kapoor', 'rohit.kapoor@gmail.com', '9876000003', 'Painter', '3', 'uploads/1756220559_person3.jpg', 'staff3'),
(4, 'Priya', 'Nair', 'priya.nair@gmail.com', '9876000004', 'AC Maintenance', '2', 'uploads/1756220634_person4.jpg', 'staff4');

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `category` varchar(100) NOT NULL,
  `service_name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `visit_charge` decimal(10,2) NOT NULL DEFAULT 0.00,
  `offers` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `category`, `service_name`, `description`, `visit_charge`, `offers`, `image`, `created_at`) VALUES
(1, 'Plumbing', 'Tap Repair', 'Repairing leaks, pipe fitting, and bathroom installations', 500.00, '10% Off', 'uploads/1756222360_plumber.jpg', '2025-08-26 15:21:14'),
(2, 'Electrician', 'Electrical Services', 'Wiring, lighting, and appliance repair', 600.00, '15% Off', 'uploads/1756221724_electrician.jpg', '2025-08-26 15:22:04'),
(3, 'Carpenter', 'Carpentry Services', 'Furniture repair, woodwork, and fittings', 550.00, '5% Off', 'uploads/1756221772_Carpenter.jpg', '2025-08-26 15:22:52'),
(4, 'House Cleaning', 'Cleaning Services', '\'Deep cleaning of home and office spaces', 650.00, '30% Off', 'uploads/1756221835_repairman.jpg', '2025-08-26 15:23:55'),
(5, 'House Cleaning', 'Cleaning Services', 'Deep cleaning of home and office spaces', 650.00, '15% Off', 'uploads/1756221907_Cleaner.jpg', '2025-08-26 15:25:07'),
(6, 'Barber', 'Home Salon & Haircut', 'Professional haircut, beard trim, and grooming at your doorstep', 100.00, '5% Off', 'uploads/1756222027_barber.jpg', '2025-08-26 15:27:07'),
(7, 'Photography', 'Home Photo Shooting', 'Professional indoor and outdoor photo shooting at your home with high-quality equipment.', 300.00, '20% Off on family package booking', 'uploads/1756222139_Camera (1).jpg', '2025-08-26 15:28:59');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookingservice`
--
ALTER TABLE `bookingservice`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `rescustomer`
--
ALTER TABLE `rescustomer`
  ADD PRIMARY KEY (`Id`);

--
-- Indexes for table `restaff`
--
ALTER TABLE `restaff`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookingservice`
--
ALTER TABLE `bookingservice`
  MODIFY `ID` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `rescustomer`
--
ALTER TABLE `rescustomer`
  MODIFY `Id` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `restaff`
--
ALTER TABLE `restaff`
  MODIFY `ID` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
