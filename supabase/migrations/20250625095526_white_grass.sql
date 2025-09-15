-- Complete Hospital Management System Database Schema
-- This file contains all necessary tables and columns for the entire system

DROP DATABASE IF EXISTS hospital_management_system;
CREATE DATABASE hospital_management_system;
USE hospital_management_system;

-- MySQL dump 10.13  Distrib 8.0.38, for Win64 (x86_64)
--
-- Host: localhost    Database: hospital_management_system
-- ------------------------------------------------------
-- Server version	8.0.39

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `activity_logs`
--

DROP TABLE IF EXISTS `activity_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `activity_logs` (
  `log_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `action` varchar(100) NOT NULL,
  `details` text,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`log_id`),
  KEY `idx_activity_logs_user` (`user_id`),
  KEY `idx_activity_logs_date` (`created_at`),
  CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=155 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_logs`
--

LOCK TABLES `activity_logs` WRITE;
/*!40000 ALTER TABLE `activity_logs` DISABLE KEYS */;
INSERT INTO `activity_logs` VALUES (1,1,'Login','Admin user logged in',NULL,NULL,'2025-06-26 17:10:37'),(2,2,'Login','Doctor logged in',NULL,NULL,'2025-06-26 17:10:37'),(3,7,'Book Appointment','Patient booked appointment with Dr. Smith',NULL,NULL,'2025-06-26 17:10:37'),(4,1,'Add Patient','Added new patient: Alice Brown',NULL,NULL,'2025-06-26 17:10:37'),(5,1,'Login','User logged in successfully',NULL,NULL,'2025-06-26 17:43:56'),(6,1,'Edit Appointment','Updated appointment ID: 1',NULL,NULL,'2025-06-26 17:44:15'),(7,1,'Add Appointment','Scheduled appointment for patient ID: 1 with doctor ID: 1',NULL,NULL,'2025-06-26 17:45:28'),(8,1,'Edit Appointment','Updated appointment ID: 5',NULL,NULL,'2025-06-26 17:45:43'),(9,1,'Logout','User logged out successfully',NULL,NULL,'2025-06-26 17:47:09'),(10,2,'Login','User logged in successfully',NULL,NULL,'2025-06-26 17:47:44'),(11,2,'Update Profile','Updated doctor profile',NULL,NULL,'2025-06-26 17:49:14'),(12,2,'Logout','User logged out successfully',NULL,NULL,'2025-06-26 17:49:25'),(13,5,'Login','User logged in successfully',NULL,NULL,'2025-06-26 17:50:41'),(14,5,'Logout','User logged out successfully',NULL,NULL,'2025-06-26 17:51:23'),(15,7,'Login','User logged in successfully',NULL,NULL,'2025-06-26 17:53:12'),(16,7,'Update Profile','Updated patient profile',NULL,NULL,'2025-06-26 17:54:29'),(17,2,'Login','User logged in successfully',NULL,NULL,'2025-06-26 18:16:39'),(18,2,'Logout','User logged out successfully',NULL,NULL,'2025-06-26 18:16:57'),(19,3,'Login','User logged in successfully',NULL,NULL,'2025-06-26 18:17:15'),(20,3,'Logout','User logged out successfully',NULL,NULL,'2025-06-26 18:17:37'),(21,1,'Login','User logged in successfully',NULL,NULL,'2025-06-30 09:53:55'),(22,1,'Login','User logged in successfully',NULL,NULL,'2025-06-30 20:10:27'),(23,1,'Logout','User logged out successfully',NULL,NULL,'2025-06-30 20:11:15'),(24,15,'Login','User logged in successfully',NULL,NULL,'2025-07-01 03:31:42'),(25,15,'Logout','User logged out successfully',NULL,NULL,'2025-07-01 03:31:57'),(26,1,'Login','User logged in successfully',NULL,NULL,'2025-07-01 04:12:45'),(27,1,'Add Appointment','Scheduled appointment for patient ID: 4 with doctor ID: 1',NULL,NULL,'2025-07-01 04:14:47'),(28,1,'Edit Appointment','Updated appointment ID: 6',NULL,NULL,'2025-07-01 04:15:11'),(29,1,'Edit Appointment','Updated appointment ID: 6',NULL,NULL,'2025-07-01 04:15:50'),(30,1,'Login','User logged in successfully',NULL,NULL,'2025-07-01 04:19:08'),(31,1,'Logout','User logged out successfully',NULL,NULL,'2025-07-01 04:22:19'),(32,1,'Login','User logged in successfully',NULL,NULL,'2025-07-01 12:08:06'),(33,1,'Edit Appointment','Updated appointment ID: 6',NULL,NULL,'2025-07-01 12:08:43'),(34,1,'Edit Patient','Updated patient: Alice Brown',NULL,NULL,'2025-07-01 12:12:11'),(35,1,'Edit Appointment','Updated appointment ID: 5',NULL,NULL,'2025-07-01 12:13:29'),(36,1,'Add Appointment','Scheduled appointment for patient ID: 3 with doctor ID: 4',NULL,NULL,'2025-07-01 12:16:03'),(37,1,'Login','User logged in successfully',NULL,NULL,'2025-07-01 13:25:44'),(38,1,'Edit Appointment','Updated appointment ID: 7',NULL,NULL,'2025-07-01 13:50:56'),(39,1,'Edit Appointment','Updated appointment ID: 7',NULL,NULL,'2025-07-01 13:54:18'),(40,1,'Logout','User logged out successfully',NULL,NULL,'2025-07-01 13:55:17'),(41,1,'Login','User logged in successfully',NULL,NULL,'2025-07-01 13:56:32'),(42,1,'Edit Appointment','Updated appointment ID: 7',NULL,NULL,'2025-07-01 14:36:35'),(43,1,'Edit Appointment','Updated appointment ID: 6',NULL,NULL,'2025-07-01 14:36:50'),(44,1,'Edit Appointment','Updated appointment ID: 6',NULL,NULL,'2025-07-01 14:36:58'),(45,1,'Edit Appointment','Updated appointment ID: 7',NULL,NULL,'2025-07-01 15:38:49'),(46,1,'Edit Appointment','Updated appointment ID: 7',NULL,NULL,'2025-07-01 16:09:52'),(47,1,'Edit Appointment','Updated appointment ID: 7',NULL,NULL,'2025-07-01 16:10:03'),(48,1,'Login','User logged in successfully',NULL,NULL,'2025-07-01 16:46:04'),(49,1,'Login','User logged in successfully',NULL,NULL,'2025-07-02 15:57:25'),(50,1,'Login','User logged in successfully',NULL,NULL,'2025-07-02 15:58:15'),(51,7,'Login','User logged in successfully',NULL,NULL,'2025-07-26 17:35:29'),(52,7,'Book Appointment','Booked appointment with doctor ID: 3',NULL,NULL,'2025-07-26 17:53:02'),(53,7,'Book Appointment','Booked appointment with doctor ID: 5',NULL,NULL,'2025-07-26 17:53:23'),(54,7,'Update Profile','Updated patient profile',NULL,NULL,'2025-07-26 17:58:16'),(55,7,'Logout','User logged out successfully',NULL,NULL,'2025-07-26 17:58:20'),(56,5,'Login','User logged in successfully',NULL,NULL,'2025-07-26 17:59:08'),(57,5,'Logout','User logged out successfully',NULL,NULL,'2025-07-26 18:06:20'),(58,2,'Login','User logged in successfully',NULL,NULL,'2025-07-26 18:07:03'),(59,2,'Update Schedule','Updated doctor schedule',NULL,NULL,'2025-07-26 18:08:17'),(60,2,'Update Profile','Updated doctor profile',NULL,NULL,'2025-07-26 18:10:48'),(61,2,'Logout','User logged out successfully',NULL,NULL,'2025-07-26 18:10:51'),(62,1,'Login','User logged in successfully',NULL,NULL,'2025-07-26 18:11:14'),(63,1,'Edit Appointment','Updated appointment ID: 9',NULL,NULL,'2025-07-26 18:12:01'),(64,1,'Edit Appointment','Updated appointment ID: 8',NULL,NULL,'2025-07-26 18:12:27'),(65,1,'Edit Appointment','Updated appointment ID: 8',NULL,NULL,'2025-07-26 18:12:37'),(66,1,'Add Patient','Added new patient: new patient',NULL,NULL,'2025-07-26 18:14:57'),(67,1,'Add Appointment','Scheduled appointment for patient ID: 5 with doctor ID: 6',NULL,NULL,'2025-07-26 18:15:39'),(68,1,'Edit Patient','Updated patient: new patient',NULL,NULL,'2025-07-26 18:16:17'),(69,1,'Edit Patient','Updated patient: new patient',NULL,NULL,'2025-07-26 18:16:30'),(70,1,'Add Appointment','Scheduled appointment for patient ID: 1 with doctor ID: 4',NULL,NULL,'2025-07-26 18:18:14'),(71,1,'Edit Appointment','Updated appointment ID: 9',NULL,NULL,'2025-07-26 18:18:45'),(72,1,'Add Bill','Created bill for patient ID: 1',NULL,NULL,'2025-07-26 18:19:22'),(73,1,'Add Medicine','Added new medicine: pan',NULL,NULL,'2025-07-26 18:22:12'),(74,1,'Logout','User logged out successfully',NULL,NULL,'2025-07-26 18:23:11'),(75,1,'Login','User logged in successfully',NULL,NULL,'2025-07-27 11:01:05'),(76,1,'Logout','User logged out successfully',NULL,NULL,'2025-07-27 11:04:25'),(77,1,'Login','User logged in successfully',NULL,NULL,'2025-07-27 11:04:45'),(78,7,'Login','User logged in successfully',NULL,NULL,'2025-07-27 11:05:23'),(79,1,'Login','User logged in successfully',NULL,NULL,'2025-07-27 11:11:00'),(80,7,'Login','User logged in successfully',NULL,NULL,'2025-07-27 11:13:31'),(81,1,'Login','User logged in successfully',NULL,NULL,'2025-07-27 11:18:33'),(82,1,'Login','User logged in successfully',NULL,NULL,'2025-07-27 11:20:18'),(83,7,'Login','User logged in successfully',NULL,NULL,'2025-07-27 11:20:45'),(84,7,'Logout','User logged out successfully',NULL,NULL,'2025-07-27 11:37:02'),(85,5,'Login','User logged in successfully',NULL,NULL,'2025-07-27 11:37:15'),(86,5,'Logout','User logged out successfully',NULL,NULL,'2025-07-27 11:49:22'),(87,2,'Login','User logged in successfully',NULL,NULL,'2025-07-27 11:49:54'),(88,2,'Update Schedule','Updated doctor schedule',NULL,NULL,'2025-07-27 11:51:08'),(89,5,'Login','User logged in successfully',NULL,NULL,'2025-07-27 13:16:20'),(90,5,'Logout','User logged out successfully',NULL,NULL,'2025-07-27 13:17:14'),(91,7,'Login','User logged in successfully',NULL,NULL,'2025-07-27 13:17:28'),(92,7,'Update Profile','Updated patient profile',NULL,NULL,'2025-07-27 13:33:39'),(93,7,'Logout','User logged out successfully',NULL,NULL,'2025-07-27 14:11:58'),(94,5,'Login','User logged in successfully',NULL,NULL,'2025-07-27 14:14:08'),(95,5,'Login','User logged in successfully',NULL,NULL,'2025-07-27 14:15:23'),(96,5,'Edit Appointment','Updated appointment ID: 8',NULL,NULL,'2025-07-27 14:38:41'),(97,5,'Edit Appointment','Updated appointment ID: 8',NULL,NULL,'2025-07-27 14:39:25'),(98,5,'Edit Appointment','Updated appointment ID: 10',NULL,NULL,'2025-07-27 14:45:27'),(99,5,'Edit Appointment','Updated appointment ID: 10',NULL,NULL,'2025-07-27 14:45:47'),(100,5,'Add Appointment','Scheduled appointment for patient ID: 3 with doctor ID: 1',NULL,NULL,'2025-07-27 14:48:39'),(101,5,'Edit Appointment','Updated appointment ID: 10',NULL,NULL,'2025-07-27 14:48:56'),(102,5,'Edit Patient','Updated patient: abc efg',NULL,NULL,'2025-07-27 14:50:55'),(103,5,'Logout','User logged out successfully',NULL,NULL,'2025-07-27 15:05:56'),(104,2,'Login','User logged in successfully',NULL,NULL,'2025-07-27 15:07:11'),(105,2,'Update Schedule','Updated doctor schedule',NULL,NULL,'2025-07-27 15:54:38'),(106,2,'Update Profile','Updated doctor profile',NULL,NULL,'2025-07-27 15:54:50'),(107,2,'Logout','User logged out successfully',NULL,NULL,'2025-07-27 15:58:08'),(108,5,'Login','User logged in successfully',NULL,NULL,'2025-07-27 15:58:33'),(109,5,'Logout','User logged out successfully',NULL,NULL,'2025-07-27 15:58:45'),(110,1,'Login','User logged in successfully',NULL,NULL,'2025-07-27 15:59:11'),(111,1,'Logout','User logged out successfully',NULL,NULL,'2025-07-27 16:03:39'),(112,2,'Login','User logged in successfully',NULL,NULL,'2025-07-27 16:06:54'),(113,2,'Login','User logged in successfully',NULL,NULL,'2025-07-27 16:13:52'),(114,2,'Logout','User logged out successfully',NULL,NULL,'2025-07-27 16:31:17'),(115,1,'Login','User logged in successfully',NULL,NULL,'2025-07-31 15:55:51'),(116,1,'Logout','User logged out successfully',NULL,NULL,'2025-07-31 17:43:46'),(117,5,'Login','User logged in successfully',NULL,NULL,'2025-07-31 17:44:05'),(118,5,'Logout','User logged out successfully',NULL,NULL,'2025-07-31 18:15:13'),(119,7,'Login','User logged in successfully',NULL,NULL,'2025-07-31 18:16:35'),(120,7,'Logout','User logged out successfully',NULL,NULL,'2025-07-31 18:32:26'),(121,21,'Login','User logged in successfully',NULL,NULL,'2025-07-31 19:39:56'),(122,21,'Update Profile','Updated patient profile',NULL,NULL,'2025-07-31 19:44:59'),(123,21,'Book Appointment','Booked appointment with doctor ID: 5',NULL,NULL,'2025-07-31 19:47:45'),(124,21,'Logout','User logged out successfully',NULL,NULL,'2025-07-31 19:48:11'),(125,13,'Login','User logged in successfully',NULL,NULL,'2025-07-31 19:49:18'),(126,13,'Logout','User logged out successfully',NULL,NULL,'2025-07-31 19:50:19'),(127,1,'Login','User logged in successfully',NULL,NULL,'2025-07-31 19:50:34'),(128,1,'Edit Appointment','Updated appointment ID: 13',NULL,NULL,'2025-07-31 19:51:08'),(129,1,'Logout','User logged out successfully',NULL,NULL,'2025-07-31 19:52:58'),(130,5,'Login','User logged in successfully',NULL,NULL,'2025-07-31 19:53:25'),(131,5,'Add Bill','Created bill for patient ID: 7',NULL,NULL,'2025-07-31 19:54:47'),(132,5,'Logout','User logged out successfully',NULL,NULL,'2025-07-31 19:55:01'),(133,21,'Login','User logged in successfully',NULL,NULL,'2025-07-31 19:55:18'),(134,21,'Logout','User logged out successfully',NULL,NULL,'2025-07-31 19:56:05'),(135,2,'Login','User logged in successfully',NULL,NULL,'2025-07-31 20:05:06'),(136,2,'Logout','User logged out successfully',NULL,NULL,'2025-07-31 20:05:52'),(137,5,'Login','User logged in successfully',NULL,NULL,'2025-07-31 20:06:07'),(138,5,'Logout','User logged out successfully',NULL,NULL,'2025-07-31 20:08:25'),(139,2,'Login','User logged in successfully',NULL,NULL,'2025-07-31 20:08:41'),(140,2,'Logout','User logged out successfully',NULL,NULL,'2025-07-31 21:06:59'),(141,1,'Login','User logged in successfully',NULL,NULL,'2025-08-03 15:17:55'),(142,1,'Edit Appointment','Updated appointment ID: 13',NULL,NULL,'2025-08-03 15:18:37'),(143,1,'Edit Patient','Updated patient: Kavinda Jayashan',NULL,NULL,'2025-08-03 15:19:10'),(144,1,'Edit Appointment','Updated appointment ID: 9',NULL,NULL,'2025-08-03 15:20:19'),(145,1,'Add Bill','Created bill for patient ID: 2',NULL,NULL,'2025-08-03 15:20:55'),(146,1,'Logout','User logged out successfully',NULL,NULL,'2025-08-03 15:22:49'),(147,2,'Login','User logged in successfully',NULL,NULL,'2025-08-03 15:23:03'),(148,2,'Update Profile','Updated doctor profile',NULL,NULL,'2025-08-03 15:24:47'),(149,2,'Logout','User logged out successfully',NULL,NULL,'2025-08-03 15:24:50'),(150,5,'Login','User logged in successfully',NULL,NULL,'2025-08-03 15:25:07'),(151,5,'Add Patient','Added new patient: fathima fathima',NULL,NULL,'2025-08-03 15:27:51'),(152,5,'Edit Patient','Updated patient: fathima fathima',NULL,NULL,'2025-08-03 15:28:13'),(153,5,'Add Appointment','Scheduled appointment for patient ID: 8 with doctor ID: 1',NULL,NULL,'2025-08-03 15:29:47'),(154,5,'Edit Patient','Updated patient: fathima risna',NULL,NULL,'2025-08-03 15:35:47');
/*!40000 ALTER TABLE `activity_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `appointments`
--

DROP TABLE IF EXISTS `appointments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `appointments` (
  `appointment_id` int NOT NULL AUTO_INCREMENT,
  `patient_id` int NOT NULL,
  `doctor_id` int NOT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` time NOT NULL,
  `status` enum('Scheduled','Completed','Cancelled','No-Show','In-Progress') DEFAULT 'Scheduled',
  `reason` text,
  `diagnosis` text,
  `prescription` text,
  `notes` text,
  `follow_up_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `start_time` time DEFAULT NULL,
  PRIMARY KEY (`appointment_id`),
  KEY `idx_appointments_date` (`appointment_date`),
  KEY `idx_appointments_doctor` (`doctor_id`),
  KEY `idx_appointments_patient` (`patient_id`),
  KEY `appointments_ibfk_3_idx` (`start_time`),
  CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`) ON DELETE CASCADE,
  CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`doctor_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `appointments`
--

LOCK TABLES `appointments` WRITE;
/*!40000 ALTER TABLE `appointments` DISABLE KEYS */;
INSERT INTO `appointments` VALUES (1,1,1,'2025-06-26','10:00:00','Scheduled','Regular checkup','',NULL,'',NULL,'2025-06-26 17:10:37','2025-06-26 17:44:15','00:00:00'),(2,2,2,'2025-06-26','14:00:00','Scheduled','Pediatric consultation',NULL,NULL,NULL,NULL,'2025-06-26 17:10:37','2025-06-26 17:10:37','00:00:00'),(3,1,1,'2025-06-27','09:00:00','Scheduled','Follow-up visit',NULL,NULL,NULL,NULL,'2025-06-26 17:10:37','2025-06-26 17:10:37','00:00:00'),(4,3,3,'2025-06-28','11:00:00','Scheduled','General consultation',NULL,NULL,NULL,NULL,'2025-06-26 17:10:37','2025-06-26 17:10:37','00:00:00'),(5,1,1,'2025-06-27','14:20:00','Scheduled','Regular checkup','abc',NULL,'',NULL,'2025-06-26 17:45:28','2025-07-01 12:13:29','00:00:00'),(6,4,1,'2025-07-04','13:45:00','Scheduled','No reason.. he hee','aaa',NULL,'cc',NULL,'2025-07-01 04:14:47','2025-07-01 04:15:50','00:00:00'),(7,3,4,'2025-07-02','17:45:00','Cancelled','123','',NULL,'',NULL,'2025-07-01 12:16:03','2025-07-26 18:17:46','00:00:00'),(8,1,3,'2025-07-27','09:30:00','Completed','abc','',NULL,'',NULL,'2025-07-26 17:53:02','2025-07-27 14:39:25',NULL),(9,1,5,'2025-07-29','11:00:00','Completed','','',NULL,'',NULL,'2025-07-26 17:53:23','2025-08-03 15:20:19',NULL),(10,5,6,'2025-07-31','13:45:00','Scheduled','new','',NULL,'',NULL,'2025-07-26 18:15:39','2025-07-27 14:45:27',NULL),(11,1,4,'2025-07-29','16:48:00','Scheduled','',NULL,NULL,NULL,NULL,'2025-07-26 18:18:14','2025-07-26 18:18:14',NULL),(12,3,1,'2025-07-29','12:18:00','Scheduled','staff',NULL,NULL,NULL,NULL,'2025-07-27 14:48:39','2025-07-27 14:48:39',NULL),(13,7,6,'2025-08-12','01:00:00','Scheduled','I need to meet the doctor','',NULL,'',NULL,'2025-07-31 19:47:45','2025-08-03 15:18:37',NULL),(14,8,1,'2025-08-06','21:00:00','Scheduled','',NULL,NULL,NULL,NULL,'2025-08-03 15:29:47','2025-08-03 15:29:47',NULL);
/*!40000 ALTER TABLE `appointments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `billing`
--

DROP TABLE IF EXISTS `billing`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `billing` (
  `bill_id` int NOT NULL AUTO_INCREMENT,
  `patient_id` int NOT NULL,
  `appointment_id` int DEFAULT NULL,
  `description` text,
  `amount` decimal(10,2) NOT NULL,
  `tax_amount` decimal(10,2) DEFAULT '0.00',
  `total_amount` decimal(10,2) NOT NULL,
  `billing_date` date NOT NULL,
  `due_date` date NOT NULL,
  `status` enum('Paid','Unpaid','Partial','Overdue') DEFAULT 'Unpaid',
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_date` date DEFAULT NULL,
  `insurance_claim` tinyint(1) DEFAULT '0',
  `insurance_amount` decimal(10,2) DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`bill_id`),
  KEY `appointment_id` (`appointment_id`),
  KEY `idx_billing_patient` (`patient_id`),
  KEY `idx_billing_status` (`status`),
  CONSTRAINT `billing_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`) ON DELETE CASCADE,
  CONSTRAINT `billing_ibfk_2` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`appointment_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `billing`
--

LOCK TABLES `billing` WRITE;
/*!40000 ALTER TABLE `billing` DISABLE KEYS */;
INSERT INTO `billing` VALUES (1,1,1,'Consultation fee - Cardiology',1500.00,150.00,1650.00,'2025-06-26','2025-07-26','Partial','Cash','2025-07-02',0,0.00,'2025-06-26 17:10:37','2025-07-26 18:19:53'),(2,2,2,'Pediatric consultation',1200.00,120.00,1320.00,'2025-06-26','2025-07-26','Paid',NULL,NULL,0,0.00,'2025-06-26 17:10:37','2025-06-26 18:02:50'),(3,3,4,'General consultation',1000.00,100.00,1100.00,'2025-06-26','2025-07-26','Paid','Cash','2025-07-01',0,0.00,'2025-06-26 17:10:37','2025-07-01 16:35:27'),(4,1,NULL,'asdfg',1500.00,10.00,1510.00,'2025-07-26','2025-08-25','Unpaid','Insurance','2025-07-27',0,0.00,'2025-07-26 18:19:22','2025-07-27 14:58:46'),(5,7,NULL,'nothing',2000.00,20.00,2020.00,'2025-07-31','2025-08-30','Partial',NULL,NULL,0,0.00,'2025-07-31 19:54:47','2025-07-31 19:54:47'),(6,2,NULL,'abc',1500.00,150.00,1650.00,'2025-08-03','2025-09-02','Paid','Credit Card','2025-08-03',0,0.00,'2025-08-03 15:20:54','2025-08-03 15:21:07');
/*!40000 ALTER TABLE `billing` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `doctor_schedules`
--

DROP TABLE IF EXISTS `doctor_schedules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `doctor_schedules` (
  `schedule_id` int NOT NULL AUTO_INCREMENT,
  `doctor_id` int NOT NULL,
  `day_of_week` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `is_available` tinyint(1) DEFAULT '1',
  `max_appointments` int DEFAULT '10',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `department` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`schedule_id`),
  KEY `doctor_id` (`doctor_id`),
  CONSTRAINT `doctor_schedules_ibfk_1` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`doctor_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `doctor_schedules`
--

LOCK TABLES `doctor_schedules` WRITE;
/*!40000 ALTER TABLE `doctor_schedules` DISABLE KEYS */;
INSERT INTO `doctor_schedules` VALUES (1,1,'Monday','09:00:00','17:00:00',1,8,'2025-06-26 17:10:37',NULL),(2,1,'Tuesday','09:00:00','17:00:00',1,8,'2025-06-26 17:10:37',NULL),(3,1,'Wednesday','09:00:00','17:00:00',1,8,'2025-06-26 17:10:37',NULL),(4,1,'Thursday','09:00:00','17:00:00',1,8,'2025-06-26 17:10:37',NULL),(5,1,'Friday','09:00:00','17:00:00',1,8,'2025-06-26 17:10:37',NULL),(6,2,'Monday','08:00:00','16:00:00',1,10,'2025-06-26 17:10:37',NULL),(7,2,'Tuesday','08:00:00','16:00:00',1,10,'2025-06-26 17:10:37',NULL),(8,2,'Wednesday','08:00:00','16:00:00',1,10,'2025-06-26 17:10:37',NULL),(9,2,'Thursday','08:00:00','16:00:00',1,10,'2025-06-26 17:10:37',NULL),(10,2,'Friday','08:00:00','16:00:00',1,10,'2025-06-26 17:10:37',NULL),(11,3,'Monday','10:00:00','18:00:00',1,6,'2025-06-26 17:10:37',NULL),(12,3,'Tuesday','10:00:00','18:00:00',1,6,'2025-06-26 17:10:37',NULL),(13,3,'Wednesday','10:00:00','18:00:00',1,6,'2025-06-26 17:10:37',NULL),(14,3,'Thursday','10:00:00','18:00:00',1,6,'2025-06-26 17:10:37',NULL),(15,3,'Friday','10:00:00','18:00:00',1,6,'2025-06-26 17:10:37',NULL),(16,4,'Monday','10:00:00','18:00:00',1,10,'2025-06-30 19:30:18',NULL),(17,4,'Tuesday','10:00:00','18:00:00',1,10,'2025-06-30 19:30:18',NULL),(18,4,'Wednesday','10:00:00','18:00:00',1,10,'2025-06-30 19:30:18',NULL),(19,4,'Thursday','10:00:00','18:00:00',1,10,'2025-06-30 19:30:18',NULL),(20,4,'Friday','10:00:00','18:00:00',1,10,'2025-06-30 19:30:18',NULL),(21,5,'Monday','10:00:00','18:00:00',1,10,'2025-06-30 19:30:18',NULL),(22,5,'Tuesday','10:00:00','18:00:00',1,10,'2025-06-30 19:30:18',NULL),(23,5,'Wednesday','10:00:00','18:00:00',1,10,'2025-06-30 19:30:18',NULL),(24,5,'Thursday','10:00:00','18:00:00',1,10,'2025-06-30 19:30:18',NULL),(25,5,'Friday','10:00:00','18:00:00',1,10,'2025-06-30 19:30:18',NULL),(26,6,'Monday','10:00:00','18:00:00',1,10,'2025-06-30 19:30:18',NULL),(27,6,'Tuesday','10:00:00','18:00:00',1,10,'2025-06-30 19:30:18',NULL),(28,6,'Wednesday','10:00:00','18:00:00',1,10,'2025-06-30 19:30:18',NULL),(29,6,'Thursday','10:00:00','18:00:00',1,10,'2025-06-30 19:30:18',NULL),(30,6,'Friday','10:00:00','18:00:00',1,10,'2025-06-30 19:30:18',NULL);
/*!40000 ALTER TABLE `doctor_schedules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `doctors`
--

DROP TABLE IF EXISTS `doctors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `doctors` (
  `doctor_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `specialization` varchar(100) NOT NULL,
  `license_number` varchar(50) NOT NULL,
  `department` varchar(100) NOT NULL,
  `consultation_fee` decimal(10,2) NOT NULL,
  `bio` text,
  `qualifications` text,
  `experience_years` int DEFAULT '0',
  `schedule_start` time DEFAULT '09:00:00',
  `schedule_end` time DEFAULT '17:00:00',
  `available_days` varchar(20) DEFAULT 'Mon,Tue,Wed,Thu,Fri',
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`doctor_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `doctors_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `doctors`
--

LOCK TABLES `doctors` WRITE;
/*!40000 ALTER TABLE `doctors` DISABLE KEYS */;
INSERT INTO `doctors` VALUES (1,2,'Cardiology','MD001','Cardiology',1500.00,'Experienced cardiologist with 15 years of practice','MD from Harvard Medical School, Board Certified in Cardiology',15,'09:00:00','17:00:00','Mon,Tue,Wed,Thu,Fri','0000-00-00 00:00:00'),(2,3,'Pediatrics','MD002','Pediatrics',1200.00,'Specialist in child healthcare and development','MD from Johns Hopkins, Pediatric Residency at Children\'s Hospital',10,'09:00:00','17:00:00','Mon,Tue,Wed,Thu,Fri','0000-00-00 00:00:00'),(3,4,'Neurology','MD003','Neurology',1000.00,'Family medicine practitioner','MD from Stanford University, Family Medicine Residency',12,'09:00:00','17:00:00','Mon,Tue,Wed,Thu,Fri','0000-00-00 00:00:00'),(4,12,'Orthopedics','MD004','Orthopedics',1500.00,'Orthopedic surgeon expert in bone, joint, and musculoskeletal treatments.','MD from Harvard Medical School, Board Certified in Orthopedics',8,'10:00:00','18:00:00','Mon,Tue,Wed,Thu,Fri','2025-07-26 18:17:18'),(5,13,'General Medicine','MD005','General Medicine',1600.00,'Family medicine practitioner providing comprehensive primary healthcare.','MD from Stanford University, Family Medicine Residency',20,'10:00:00','18:00:00','Mon,Tue,Wed,Thu,Fri','0000-00-00 00:00:00'),(6,14,'Cardiology','MD006','Cardiology',1500.00,'Interventional cardiologist specializing in minimally invasive procedures.','MD from Johns Hopkins, Pediatric Residency at Children\'s Hospital',14,'10:00:00','18:00:00','Mon,Tue,Wed,Thu,Fri','0000-00-00 00:00:00'),(7,20,'General Medicine','PENDING','nuro',2000.00,NULL,NULL,0,'09:00:00','17:00:00','Mon,Tue,Wed,Thu,Fri','2025-08-03 15:19:56');
/*!40000 ALTER TABLE `doctors` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `medical_records`
--

DROP TABLE IF EXISTS `medical_records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `medical_records` (
  `record_id` int NOT NULL AUTO_INCREMENT,
  `patient_id` int NOT NULL,
  `doctor_id` int NOT NULL,
  `appointment_id` int DEFAULT NULL,
  `visit_date` date NOT NULL,
  `chief_complaint` text,
  `diagnosis` text,
  `treatment` text,
  `prescription` text,
  `notes` text,
  `vital_signs` json DEFAULT NULL,
  `follow_up_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`record_id`),
  KEY `appointment_id` (`appointment_id`),
  KEY `idx_medical_records_patient` (`patient_id`),
  KEY `idx_medical_records_doctor` (`doctor_id`),
  CONSTRAINT `medical_records_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`) ON DELETE CASCADE,
  CONSTRAINT `medical_records_ibfk_2` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`doctor_id`) ON DELETE CASCADE,
  CONSTRAINT `medical_records_ibfk_3` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`appointment_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `medical_records`
--

LOCK TABLES `medical_records` WRITE;
/*!40000 ALTER TABLE `medical_records` DISABLE KEYS */;
INSERT INTO `medical_records` VALUES (1,1,1,NULL,'2025-05-27','Chest pain and shortness of breath','Hypertension','Prescribed medication and lifestyle changes',NULL,'Patient responding well to treatment',NULL,NULL,'2025-06-26 17:10:37','2025-06-26 17:10:37'),(2,2,2,NULL,'2025-06-11','Fever and cough','Common cold','Rest and fluids',NULL,'Symptoms resolved',NULL,NULL,'2025-06-26 17:10:37','2025-06-26 17:10:37'),(3,3,3,NULL,'2025-06-19','Annual physical examination','Annual checkup','Routine examination',NULL,'All vitals normal',NULL,NULL,'2025-06-26 17:10:37','2025-06-26 17:10:37');
/*!40000 ALTER TABLE `medical_records` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `patients`
--

DROP TABLE IF EXISTS `patients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `patients` (
  `patient_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `dob` date NOT NULL,
  `gender` enum('Male','Female','Other') NOT NULL,
  `blood_type` enum('A+','A-','B+','B-','AB+','AB-','O+','O-') DEFAULT NULL,
  `height` decimal(5,2) DEFAULT NULL,
  `weight` decimal(5,2) DEFAULT NULL,
  `insurance_provider` varchar(100) DEFAULT NULL,
  `insurance_number` varchar(50) DEFAULT NULL,
  `emergency_contact_name` varchar(100) DEFAULT NULL,
  `emergency_contact_phone` varchar(20) DEFAULT NULL,
  `medical_history` text,
  `allergies` text,
  `current_medications` text,
  PRIMARY KEY (`patient_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `patients_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `patients`
--

LOCK TABLES `patients` WRITE;
/*!40000 ALTER TABLE `patients` DISABLE KEYS */;
INSERT INTO `patients` VALUES (1,7,'2000-06-15','Female','A+',165.00,61.00,NULL,NULL,'John Brown','9876543210','No significant medical history','Penicillin allergy','Multivitamin daily'),(2,8,'2002-03-22','Male','O+',175.00,75.00,NULL,NULL,'Mary Wilson','9876543211','Hypertension managed with medication','None known','None'),(3,9,'1988-12-10','Female','B+',160.00,55.00,NULL,NULL,'David Davis','9876543212','Asthma, well controlled','Shellfish allergy','Birth control pill'),(4,15,'1990-01-01','Male',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(5,16,'2025-07-17','Male','B+',123.00,50.00,NULL,NULL,'119','',NULL,NULL,NULL),(6,18,'1990-01-01','Male','AB+',150.00,50.00,NULL,NULL,'','',NULL,NULL,NULL),(7,21,'2000-07-19','Male','A-',163.00,58.00,NULL,NULL,'suwaseriya','1990','pesABCDEFG','allergies to dath madinnethi aya','rapidine,metformin'),(8,22,'2011-06-15','Female','AB+',156.00,50.00,NULL,NULL,'Mom','1245786953',NULL,NULL,NULL);
/*!40000 ALTER TABLE `patients` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pharmacy`
--

DROP TABLE IF EXISTS `pharmacy`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pharmacy` (
  `medicine_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `generic_name` varchar(100) DEFAULT NULL,
  `brand_name` varchar(100) DEFAULT NULL,
  `description` text,
  `category` varchar(50) DEFAULT NULL,
  `dosage_form` varchar(50) DEFAULT NULL,
  `strength` varchar(50) DEFAULT NULL,
  `quantity` int NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `wholesale_price` decimal(10,2) DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `batch_number` varchar(50) DEFAULT NULL,
  `supplier` varchar(100) DEFAULT NULL,
  `manufacturer` varchar(100) DEFAULT NULL,
  `storage_conditions` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`medicine_id`),
  KEY `idx_pharmacy_name` (`name`),
  KEY `idx_pharmacy_expiry` (`expiry_date`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pharmacy`
--

LOCK TABLES `pharmacy` WRITE;
/*!40000 ALTER TABLE `pharmacy` DISABLE KEYS */;
INSERT INTO `pharmacy` VALUES (1,'Paracetamol','Acetaminophen','Tylenol','Pain reliever and fever reducer','Analgesic','Tablet','500mg',500,50.00,35.00,'2025-12-31',NULL,'PharmaCorp','Johnson & Johnson',NULL,'2025-06-26 17:10:37','2025-06-26 18:07:27'),(2,'Amoxicillin','Amoxicillin','Amoxil','Antibiotic for bacterial infections','Antibiotic','Capsule','250mg',200,150.00,100.00,'2026-06-30','','MediSupply','GlaxoSmithKline','','2025-06-26 17:10:37','2025-07-31 18:14:54'),(3,'Ibuprofen','Ibuprofen','Advil','Anti-inflammatory pain reliever','NSAID','Tablet','200mg',300,80.00,55.00,'2025-10-31','','HealthMeds','Pfizer','','2025-06-26 17:10:37','2025-08-03 15:21:57'),(4,'Aspirin','Acetylsalicylic acid','Bayer','Blood thinner and pain reliever','Antiplatelet','Tablet','81mg',400,30.00,20.00,'2025-11-20',NULL,'PharmaCorp','Bayer',NULL,'2025-06-26 17:10:37','2025-06-26 18:07:27'),(5,'Metformin','Metformin HCl','Glucophage','Diabetes medication','Antidiabetic','Tablet','500mg',250,120.00,80.00,'2025-08-01','','DiabetesCare','Bristol Myers Squibb','','2025-06-26 17:10:37','2025-08-03 15:22:13');
/*!40000 ALTER TABLE `pharmacy` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `prescription_items`
--

DROP TABLE IF EXISTS `prescription_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `prescription_items` (
  `item_id` int NOT NULL AUTO_INCREMENT,
  `prescription_id` int NOT NULL,
  `medicine_id` int NOT NULL,
  `dosage` varchar(50) NOT NULL,
  `frequency` varchar(50) NOT NULL,
  `duration` varchar(50) NOT NULL,
  `quantity` int NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `instructions` text,
  PRIMARY KEY (`item_id`),
  KEY `prescription_id` (`prescription_id`),
  KEY `medicine_id` (`medicine_id`),
  CONSTRAINT `prescription_items_ibfk_1` FOREIGN KEY (`prescription_id`) REFERENCES `prescriptions` (`prescription_id`) ON DELETE CASCADE,
  CONSTRAINT `prescription_items_ibfk_2` FOREIGN KEY (`medicine_id`) REFERENCES `pharmacy` (`medicine_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `prescription_items`
--

LOCK TABLES `prescription_items` WRITE;
/*!40000 ALTER TABLE `prescription_items` DISABLE KEYS */;
INSERT INTO `prescription_items` VALUES (1,1,1,'500mg','Twice daily','7 days',14,5.00,70.00,'Take with food'),(2,1,4,'81mg','Once daily','30 days',30,3.00,90.00,'Take in the morning'),(3,2,2,'250mg','Three times daily','5 days',15,15.00,225.00,'Complete full course');
/*!40000 ALTER TABLE `prescription_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `prescriptions`
--

DROP TABLE IF EXISTS `prescriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `prescriptions` (
  `prescription_id` int NOT NULL AUTO_INCREMENT,
  `patient_id` int NOT NULL,
  `doctor_id` int NOT NULL,
  `appointment_id` int DEFAULT NULL,
  `prescription_date` date NOT NULL,
  `instructions` text,
  `status` enum('Pending','Filled','Cancelled','Partial') DEFAULT 'Pending',
  `total_amount` decimal(10,2) DEFAULT '0.00',
  `dispensed_by` int DEFAULT NULL,
  `dispensed_date` date DEFAULT NULL,
  `notes` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `department` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`prescription_id`),
  KEY `appointment_id` (`appointment_id`),
  KEY `dispensed_by` (`dispensed_by`),
  KEY `idx_prescriptions_patient` (`patient_id`),
  KEY `idx_prescriptions_doctor` (`doctor_id`),
  CONSTRAINT `prescriptions_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`) ON DELETE CASCADE,
  CONSTRAINT `prescriptions_ibfk_2` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`doctor_id`) ON DELETE CASCADE,
  CONSTRAINT `prescriptions_ibfk_3` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`appointment_id`) ON DELETE SET NULL,
  CONSTRAINT `prescriptions_ibfk_4` FOREIGN KEY (`dispensed_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `prescriptions`
--

LOCK TABLES `prescriptions` WRITE;
/*!40000 ALTER TABLE `prescriptions` DISABLE KEYS */;
INSERT INTO `prescriptions` VALUES (1,1,1,NULL,'2025-06-26','Take medication as prescribed. Follow up in 2 weeks.','Pending',250.00,NULL,NULL,NULL,'2025-06-26 17:10:37','2025-06-26 18:08:30',NULL),(2,2,2,NULL,'2025-06-21','Complete the full course of antibiotics.','Filled',150.00,NULL,NULL,NULL,'2025-06-26 17:10:37','2025-06-26 18:08:30',NULL);
/*!40000 ALTER TABLE `prescriptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system_settings`
--

DROP TABLE IF EXISTS `system_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `system_settings` (
  `setting_id` int NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text,
  `description` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`setting_id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_settings`
--

LOCK TABLES `system_settings` WRITE;
/*!40000 ALTER TABLE `system_settings` DISABLE KEYS */;
INSERT INTO `system_settings` VALUES (1,'hospital_name','MediCare Hospital','Name of the hospital','2025-06-26 17:10:37','2025-06-26 17:10:37'),(2,'hospital_address','123 Medical Center , Colombo1','Hospital address','2025-06-26 17:10:37','2025-06-26 18:11:28'),(3,'hospital_phone','+94 47-3698521','Hospital contact number','2025-06-26 17:10:37','2025-06-26 18:11:28'),(4,'appointment_duration','30','Default appointment duration in minutes','2025-06-26 17:10:37','2025-06-26 17:10:37'),(5,'max_appointments_per_day','20','Maximum appointments per doctor per day','2025-06-26 17:10:37','2025-06-26 17:10:37'),(6,'tax_rate','10','Tax rate percentage for billing','2025-06-26 17:10:37','2025-06-26 17:10:37'),(7,'currency','Rs','Currency used for billing','2025-06-26 17:10:37','2025-06-26 18:11:28');
/*!40000 ALTER TABLE `system_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `test_results`
--

DROP TABLE IF EXISTS `test_results`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `test_results` (
  `result_id` int NOT NULL AUTO_INCREMENT,
  `patient_id` int NOT NULL,
  `doctor_id` int NOT NULL,
  `appointment_id` int DEFAULT NULL,
  `test_name` varchar(100) NOT NULL,
  `test_type` varchar(50) DEFAULT NULL,
  `test_date` date NOT NULL,
  `results` text,
  `normal_range` varchar(100) DEFAULT NULL,
  `status` enum('Normal','Abnormal','Critical') DEFAULT 'Normal',
  `notes` text,
  `file_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`result_id`),
  KEY `patient_id` (`patient_id`),
  KEY `doctor_id` (`doctor_id`),
  KEY `appointment_id` (`appointment_id`),
  CONSTRAINT `test_results_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`) ON DELETE CASCADE,
  CONSTRAINT `test_results_ibfk_2` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`doctor_id`) ON DELETE CASCADE,
  CONSTRAINT `test_results_ibfk_3` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`appointment_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `test_results`
--

LOCK TABLES `test_results` WRITE;
/*!40000 ALTER TABLE `test_results` DISABLE KEYS */;
/*!40000 ALTER TABLE `test_results` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('admin','doctor','staff','patient') NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text,
  `profile_image` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_users_role` (`role`),
  KEY `idx_users_email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin','admin123','admin@hospital.com','admin','Risna','Rimsiyan','1234567890','123 Admin St',NULL,1,NULL,'2025-06-26 17:10:37','2025-07-31 19:52:53'),(2,'doctor1','doctor123','smith@hospital.com','doctor','John sm','Smith','1234567891','456 Doctor Ave',NULL,1,NULL,'2025-06-26 17:10:37','2025-08-03 15:24:47'),(3,'doctor2','doctor123','jones@hospital.com','doctor','Sarah','Jones','1234567892','789 Medical Blvd',NULL,1,NULL,'2025-06-26 17:10:37','2025-06-26 18:13:05'),(4,'doctor3','doctor123','wilson@hospital.com','doctor','Michael','Wilson','1234567893','321 Health St',NULL,1,NULL,'2025-06-26 17:10:37','2025-06-26 18:13:05'),(5,'staff1','staff123','staff@hospital.com','staff','Mike','Johnson','1234567894','321 Staff Rd',NULL,1,NULL,'2025-06-26 17:10:37','2025-06-26 17:10:37'),(6,'staff2','staff123','staff2@hospital.com','staff','Lisa','Davis','1234567895','654 Staff Ave',NULL,1,NULL,'2025-06-26 17:10:37','2025-06-26 17:10:37'),(7,'patient1','patient123','patient1@email.com','patient','Alice','Brown','1234567896','654 Patient Lane',NULL,1,NULL,'2025-06-26 17:10:37','2025-06-26 17:10:37'),(8,'patient2','patient123','patient2@email.com','patient','Bob','Wilson','1234567897','987 Health St',NULL,1,NULL,'2025-06-26 17:10:37','2025-06-26 17:10:37'),(9,'patient3','patient123','patient3@email.com','patient','Carol','Davis','1234567898','123 Wellness Rd',NULL,1,NULL,'2025-06-26 17:10:37','2025-06-26 17:10:37'),(12,'doctor4','doctor123','davis@hospital.com','doctor','Emily','Davis','1234567899','456 Doctor St',NULL,1,NULL,'2025-06-30 19:03:58','2025-07-26 18:17:18'),(13,'doctor5','doctor123','brown@hospital.com','doctor','Robert','Brown','1234567898','789 Doc ave',NULL,1,NULL,'2025-06-30 19:03:58','2025-06-30 19:03:58'),(14,'doctor6','doctor123','anderson@hospital.com','doctor','Lisa','Anderson','1234567898','654 Health Ave',NULL,1,NULL,'2025-06-30 19:03:58','2025-06-30 19:03:58'),(15,'fathi','123456','fa@gmail.com','patient','Fathi','Fathi','123657895',NULL,NULL,1,NULL,'2025-07-01 03:31:30','2025-07-01 03:31:30'),(16,'pat1','pat123','new@gmail.com','patient','new','patient','1234567898','',NULL,1,NULL,'2025-07-26 18:14:57','2025-07-26 18:14:57'),(17,'pule','pule123','pulathisi@gmail.com','doctor','kavinda','pulathisi','1234567899',NULL,NULL,1,NULL,'2025-07-26 18:24:48','2025-07-26 18:24:48'),(18,'abc','abc123','abc@gmail.com','patient','abc','efg','123456785','',NULL,1,NULL,'2025-07-26 18:26:18','2025-07-27 14:50:19'),(19,'moke','123456','moke@pule.com','doctor','moke','pule','1234567895',NULL,NULL,1,NULL,'2025-07-27 16:32:01','2025-07-27 16:32:01'),(20,'pulemoke','200109','moke@ple.com','doctor','moke','pule','1234569875',NULL,NULL,1,NULL,'2025-07-27 16:35:04','2025-08-03 15:19:56'),(21,'jayashan','jayashan','Mkp2jayashan@g.com','patient','Kavinda','Jayashan','0716756980','dxcfgvhbnjmk',NULL,1,NULL,'2025-07-31 19:39:34','2025-07-31 19:44:59'),(22,'fathima','123456','fathi456@gmail.com','patient','fathima','risna','125486935','',NULL,1,NULL,'2025-08-03 15:27:51','2025-08-03 15:35:47');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-09-16  0:32:39
