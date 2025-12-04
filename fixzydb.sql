-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 04, 2025 at 04:33 PM
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
  `FullName` varchar(40) NOT NULL,
  `Email` varchar(30) NOT NULL,
  `PhoneNumber` varchar(15) NOT NULL,
  `Service` varchar(100) NOT NULL,
  `Address` varchar(80) NOT NULL,
  `Date` date NOT NULL,
  `Time` varchar(30) NOT NULL,
  `Description` text DEFAULT NULL,
  `OTP` varchar(10) DEFAULT NULL,
  `StaffID` int(11) DEFAULT NULL,
  `Assigned` tinyint(1) DEFAULT 0,
  `Status` enum('Pending','Accepted','In Progress','Completed','Cancelled') DEFAULT 'Pending',
  `Rating` int(1) DEFAULT NULL,
  `Review` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookingservice`
--

INSERT INTO `bookingservice` (`ID`, `FullName`, `Email`, `PhoneNumber`, `Service`, `Address`, `Date`, `Time`, `Description`, `OTP`, `StaffID`, `Assigned`, `Status`, `Rating`, `Review`) VALUES
(1, 'Raju Saha', 'raju@gmail.comr', '9879879879', 'Car Exterior Cleaning', '230 , Nandigram society near gajera school katargam surat', '2025-12-05', '09:10 AM', '', '3072', NULL, 0, 'Pending', NULL, NULL),
(2, 'Raju Saha', 'raju@gmail.comr', '9879879879', 'Bike Service', '230 , Nandigram society near gajera school katargam surat', '2025-12-05', '09:10 AM', '', '8337', NULL, 0, 'Pending', NULL, NULL),
(3, 'Sonu Vora', 'sonu@gmail.com', '3456776543', 'Home Tiffin Service', '1012 khodhiyar nagar katargam surat', '2025-12-05', '12:10 PM', '', '9623', 16, 1, 'Pending', NULL, NULL),
(4, 'Sonu Vora', 'sonu@gmail.com', '3456776543', 'Home Tiffin Service', '1012 khodhiyar nagar katargam surat', '2025-12-06', '12:59 PM', '', '7625', NULL, 0, 'Pending', NULL, NULL),
(5, 'Tapu Gada', 'tapu@gmail.com', '8568768768', 'Outdoor Family Photo Shoot', 'Luxury photo studio near dabholi char rasta surat', '2025-12-05', '10:15 AM', '', '2045', 14, 1, 'Completed', NULL, NULL),
(6, 'Tapu Gada', 'tapu@gmail.com', '8568768768', 'Sofa Cleaning', '2022 shivalik appartment katargam surat', '2025-12-05', '02:25 PM', '', '7097', NULL, 0, 'Pending', NULL, NULL),
(7, 'Tilak Varma', 'tilak@gmail.com', '9876987687', 'Bathroom Deep Cleaning', '2102 venus appartment near gajra school katargam surat', '2025-12-05', '02:40 PM', '', '3524', NULL, 0, 'Pending', NULL, NULL),
(8, 'Deepika Sharma', 'deepika@gmail.com', '9879871231', 'Laundry Service', '203 - vastukala apartment katargam surat', '2025-12-06', '09:30 AM', '', '3780', NULL, 0, 'Pending', NULL, NULL),
(9, 'Rajeshkumar Modi', 'rajesh@kumar.com', '3456665434', 'Daily House Cleaning', '202 - Gopin bungalow surat', '2025-12-06', '11:50 AM', '', '8869', 8, 1, 'Pending', NULL, NULL),
(10, 'Rajeshkumar Modi', 'rajesh@kumar.com', '3456665434', 'Plumbing', '203- gopin bumgalow katargam surat', '2025-12-05', '01:50 PM', '', '9828', NULL, 0, 'Pending', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `rescustomer`
--

CREATE TABLE `rescustomer` (
  `Id` int(5) NOT NULL,
  `FirstName` varchar(20) NOT NULL,
  `LastName` varchar(20) NOT NULL,
  `EMail` varchar(40) NOT NULL,
  `Phone` varchar(20) NOT NULL,
  `Password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rescustomer`
--

INSERT INTO `rescustomer` (`Id`, `FirstName`, `LastName`, `EMail`, `Phone`, `Password`) VALUES
(1, 'Rohit', 'Sharma', 'rohit@gmail.com', '9896868685', '$2y$10$p66RAA87BFOgOFxbznmNluCskK3c7IwMzOapkbntomwYyU1EuiQyK'),
(2, 'Raju', 'Saha', 'raju@gmail.comr', '9879879879', '$2y$10$X1rZocfPI3i8YFRp2apvzOPs8aK3PWIYLYMwniWCcfHb7DJnmo9PC'),
(3, 'Sonu', 'Vora', 'sonu@gmail.com', '3456776543', '$2y$10$zRoWHUaDJq6WUDwkODIFSe71J5OMh29sPLnbhWOEDiaPIgaLJEtya'),
(4, 'Tapu', 'Gada', 'tapu@gmail.com', '8568768768', '$2y$10$PYk7A0lS5jkO5ax83JMnMu3cxHFNwFOlGIXaumc.xNv42y9aYdxlO'),
(5, 'Sumeet', 'Jain', 'sumeet@gmail.com', '9999444434', '$2y$10$zA2.ilaomagzcw43jYIco.J8XgufUnXElfAFHdUP5pbf7ESuNgxvG'),
(6, 'Kano', 'Dabhi', 'kano@gmail.com', '9585857456', '$2y$10$tUEVXOCsm1tdiq.QNOXsqO4RBe9jysoJoKpa2gdWa8ZUg8OZCj7Ka'),
(7, 'Rajeshkumar', 'Modi', 'rajesh@kumar.com', '3456665434', '$2y$10$O4E6728HNpal92QgG5iM6.BvtOUY2H1n.JFu47MF3X2no3cxMOVdu'),
(8, 'Krish', 'Patel', 'krish@gmail.com', '3848576545', '$2y$10$iHgGlVjnoVIOxr33yb0VHumzMx4n0KgNZQBsTXZylNqNz5OndyjMq'),
(9, 'Deepika', 'Sharma', 'deepika@gmail.com', '9879871231', '$2y$10$ysg5avEjviK/1GngThgHfO.lbdaqjni1KcAoqVZMtD.bdkLJWM.km'),
(10, 'Priyanka', 'Chopra', 'Chopra@gmail.com', '9349349876', '$2y$10$I3P2oatDxWa1ro/If3jlC.GVToy0s3nPh9YG0.Uo3aSb/POwVewem'),
(11, 'Jeet', 'Sai', 'sai@gmail.com', '9877654545', '$2y$10$vqnBPtfxsRomY0zflc2S3u65ltRE8ntquSXF1TPNPuz5/iUOJlWrW'),
(12, 'Smriti', 'Mandhana', 'smriti@gmail.com', '1231234434', '$2y$10$w3WAQq71dEImKC4Q/xTD4eKJ8a9jvC18h9cimo56ViwylToRtQo7y'),
(13, 'Harshil', 'Dabhi', 'harshil@gmail.com', '9876543434', '$2y$10$Md8D2KMbZoIhLt.qEu9jAevtHclPMKyhRCVAeiLXXs0x6UzgwwAGW'),
(14, 'Kanjibhai', 'Kaur', 'kanjibhai@gmail.com', '8978654675', '$2y$10$oLKzyJlDM/aog0BUVggjBuFB/yx4avc1.rnHyMTOdeQnwUWh19eBy'),
(15, 'Tilak', 'Varma', 'tilak@gmail.com', '9876987687', '$2y$10$mvz4SCaAekZJ2v9Wd3Q.8OcMd3kw9ubW0YXxKiqx5lwEuoiyGWlWu');

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
(7, 'Virat', 'Kohli', 'virat@gmail.com', '2342342345', 'Cleaning Services', '2', '1764491359_person4.jpg', '$2y$10$sKYnRbWhXVzVjyLGO2kNwe39eKTsEfZmfYNBbTGCTsS6GKxkomPK6'),
(8, 'Hardik', 'Pandya', 'hardik@gmail.com', '8765764573', 'Cleaning Services', '4', '1764491346_person1.jpg', '$2y$10$jgiUaRhBs.Cp7WqNtWmQduhLK0zyjQ5Lm19S19Xk6u1GM96IHQUyG'),
(9, 'Jitesh', 'Sharma', 'jitesh@gmail.com', '2345665434', 'Plumbing Services', '2', '1764491333_person3.jpg', '$2y$10$/l4aVm0XaPwTiKmqPmgLG.YWbWBpZBYUojA9T7RnmNXK2Ss5kPtZC'),
(10, 'Shreyas', 'Iyer', 'iyer@gmail.com', '2342345676', 'Plumbing Services', '4', '1764491325_person2.jpg', '$2y$10$3NKotKEpXSYLsm9KByF3/unXRDSOfaGBWG0olovmvzrT9WbSuipu2'),
(11, 'Kenil', 'Goti', 'kenil@gmail.com', '2342345654', 'Electrical Services', '2', '1764491316_person4.jpg', '$2y$10$zJWIKqbbFyjnBEZdJJlDYODlpiuJSVseV8Ocq2Qk6C6OYR08jViNG'),
(12, 'Roshan', 'Pandya', 'roshan@gmail.com', '2343453453', 'Packing Services', '3', '1764491277_person3.jpg', '$2y$10$pP0kPrfiNrytk2IwnPx/wuL9u2eRgHUAswyaVFHXdgPbD7YSCDLdu'),
(13, 'Hit', 'Vlogger', 'vlogger@gmail.com', '2342345678', 'Packing Services', '1', '1764491111_person1.jpg', '$2y$10$Mk0WwM2MIabAfp85TGJme.9XwTmlxx7JKnMnR.yMuDzRZbyLIAh8S'),
(14, 'Yug', 'Jain', 'yug@gmail.com', '9879876854', 'Photo Shooting', '3', '1764491286_person4.jpg', '$2y$10$w.h63llsD2rVhME.0MbjC.kNhQKgiYH4Vv6X8f.N3CXZV1zRA1nu6'),
(15, 'Gopi', 'Kakadiya', 'gopi@gmail.com', '9987767454', 'Household', '2', '1764491021_person2.jpg', '$2y$10$dKQmXmgVplsgJPowdWleXeaJXGau1VrI1zsyF8SSJgEDX0NoC1Ib6'),
(16, 'Riddhi', 'Shah', 'shah@gmail.com', '9898987767', 'Tiffin Service', '5', '1764491301_person3.jpg', '$2y$10$jMpqW6XKxTU3x72ORzSYCelV7wNdoeKYL9htShmC25zvQB55Nivaq'),
(17, 'Rahul', 'Loard', 'rahul@gmail.com', '9988778877', 'Vehicle Services', '4', '1764491386_person4.jpg', '$2y$10$doYcAWwbxioWXFawpxTSm.IT590DI.AKObz8AlSbiv0dWXWEi73d2'),
(18, 'Shraddha', 'Kapoor', 'kapoor@gmail.com', '9988775643', 'Vehicle Services', '2', '1764491375_person2.jpg', '$2y$10$io/kGIAJn8vxdk0nVJN7pOKD2vn8lfnzd8fct5ODHwGhL745lSAY6');

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
(1, 'Cleaning Services', 'Bathroom Deep Cleaning', 'Removal of stains, floor scrubbing, glass & tap cleaning', 599.00, '5% OFF', 'uploads/1763152158_Aprende_los_Secretos_de_la_Limpieza__Ba__o_de_Hotel___.jpeg', '2025-11-14 18:17:37'),
(2, 'Cleaning Services', 'Sofa Cleaning', 'Deep shampoo cleaning per seat', 199.00, '', 'uploads/1763152113_A_clean_and_fresh_sofa_is_an_amazing_asset__The___.jpeg', '2025-11-14 18:19:37'),
(3, 'Plumbing Services', 'Plumbing', 'Tap fitting with basic leakage check', 249.00, '', 'uploads/1763144472_plumber.jpg', '2025-11-14 18:21:12'),
(4, 'Plumbing Services', 'Wash Basin Cleaning and installing', 'Basin surface cleaning and blockage removal or installing basin than charge is different. This charge is for installing .', 399.00, '', 'uploads/1763144669_plumber.jpg', '2025-11-14 18:24:29'),
(5, 'Electrical Services', 'Fan Installation and tube light', 'Check and repair the electric equipment like AC, refrigerator, and other equipment', 299.00, '', 'uploads/1763144796_electrician.jpg', '2025-11-14 18:26:36'),
(6, 'Electrical Services', 'Switchboard and Full House Wiring repairing', 'Socket/switch repair or replacement and full checking of home wiring and repair it.', 299.00, '5% OFF', 'uploads/1763145027_Carpenter.jpg', '2025-11-14 18:30:27'),
(7, 'Packing Services', '1 BHK Packing', 'Packing of kitchen items, clothes, electronics, fragile items, and furniture for 1 BHK home shifting', 1499.00, '10% OFF', 'uploads/1763145257_packet.jpg', '2025-11-14 18:34:17'),
(8, 'Packing Services', '2 BHK Packing', 'Full packing of household items including furniture, kitchen, bedroom, and fragile goods for 2 BHK relocation', 2499.00, '15% OFF', 'uploads/1763145301_packet.jpg', '2025-11-14 18:35:01'),
(9, 'Packing Services', '3 BHK Packing', 'Premium quality packing for large homes including bubble-wrap, fragile care, furniture dismantle+pack', 3499.00, '15% OFF', 'uploads/1763145338_packet.jpg', '2025-11-14 18:35:38'),
(10, 'Photo Shooting', 'Indoor Family Photo Shoot', 'Professional indoor family photoshoot with lighting setup and 20 edited photos', 1999.00, '10% OFF', 'uploads/1763145433_Camera__1_.jpg', '2025-11-14 18:37:13'),
(11, 'Photo Shooting', 'Outdoor Family Photo Shoot', 'Natural light shoot at a park or outdoor location with 25 edited photos', 2499.00, '15% OFF', 'uploads/1763152071_Family_Photo_Outfits_Winter_2025___2026_look_best___.jpeg', '2025-11-14 18:38:11'),
(12, 'Photo Shooting', 'Baby & Family Combo Shoot', 'Combined photoshoot with props for baby + family portraits, 20 edited photos', 2999.00, '5% OFF', 'uploads/1763145545_Camera__1_.jpg', '2025-11-14 18:39:05'),
(13, 'Household Services', 'Daily House Cleaning', 'Sweeping, mopping, dusting and basic cleaning', 250.00, '', 'uploads/1763151876_Tired_of_spending_your_weekends_cleaning__Let_us___.jpeg', '2025-11-14 18:41:03'),
(14, 'Household Services', 'Utensil Washing', 'Complete dishwashing of daily used utensils', 150.00, '', 'uploads/1763151823_Photo_housewife_cleaning_the_dishes_with___.jpeg', '2025-11-14 18:41:49'),
(15, 'Household Services', 'Laundry Service', 'Washing, drying & folding of clothes 35 per cloths', 35.00, '', 'uploads/1763151757_Running_a_spa_or_salon__Let_us_handle_your_linens___.jpeg', '2025-11-14 18:43:12'),
(16, 'Food Services', 'Home Tiffin Service', 'Healthy homemade lunch + dinner delivery . 160 per meal', 160.00, '', 'uploads/1763151718_Do_you_cut_on_meals_because_of_lack_of_time_or_of___.jpeg', '2025-11-14 18:44:27'),
(17, 'Vehicle Services', 'Bike Service', 'Oil check, chain lubrication, filter cleaning', 299.00, '', 'uploads/1763151685_Foto_homem_jogando_sabonete_l__quido_na_m___.jpeg', '2025-11-14 18:45:23'),
(18, 'Vehicle Services', 'Car Exterior Cleaning', 'Car body wash & shine', 499.00, '5% OFF', 'uploads/1763151482______Say_hello_to_the_future_of_car_wash_technology___.jpeg', '2025-11-14 18:45:53');

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
  MODIFY `ID` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `rescustomer`
--
ALTER TABLE `rescustomer`
  MODIFY `Id` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `restaff`
--
ALTER TABLE `restaff`
  MODIFY `ID` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
