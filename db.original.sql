-- MySQL dump 10.13  Distrib 5.7.16, for Linux (x86_64)
--
-- Host: localhost    Database: hxcmsdb
-- ------------------------------------------------------
-- Server version	5.7.16-0ubuntu0.16.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `file`
--

DROP TABLE IF EXISTS `file`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `file` (
  `file_id` int(25) unsigned NOT NULL AUTO_INCREMENT,
  `format` varchar(10) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `last_modified` datetime DEFAULT NULL,
  `processed` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`file_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1283 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `file_adjacency_list`
--

DROP TABLE IF EXISTS `file_adjacency_list`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `file_adjacency_list` (
  `directory_id` int(25) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `parent_id` int(25) DEFAULT NULL,
  `file_id` int(25) DEFAULT NULL,
  PRIMARY KEY (`directory_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1304 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `file_adjacency_list`
--
--
-- Table structure for table `file_tag`
--

DROP TABLE IF EXISTS `file_tag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `file_tag` (
  `tag_id` int(25) unsigned NOT NULL AUTO_INCREMENT,
  `file_id` int(25) DEFAULT NULL,
  `tag` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`tag_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5927 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `file_tag`
--
--
-- Table structure for table `file_version`
--

DROP TABLE IF EXISTS `file_version`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `file_version` (
  `version_id` int(25) unsigned NOT NULL AUTO_INCREMENT,
  `file_id` int(25) DEFAULT NULL,
  `filename` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `processed` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`version_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5056 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `file_version`
--
--
-- Table structure for table `page_permissions`
--

DROP TABLE IF EXISTS `page_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `page_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `page_id` int(11) NOT NULL,
  `state` enum('disabled','active','protected') CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=30 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `page_permissions`
--

LOCK TABLES `page_permissions` WRITE;
/*!40000 ALTER TABLE `page_permissions` DISABLE KEYS */;
INSERT INTO `page_permissions` VALUES (1,1,'active'),(2,2,'protected'),(3,3,'protected'),(4,4,'active'),(5,5,'protected'),(6,6,'protected'),(7,7,'protected'),(8,8,'protected'),(29,11,'protected'),(28,10,'protected'),(27,9,'protected'),(26,10,'protected');
/*!40000 ALTER TABLE `page_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pages`
--

DROP TABLE IF EXISTS `pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `meta_description` varchar(1000) DEFAULT NULL,
  `link` longtext CHARACTER SET utf8,
  `change_freq` enum('always','hourly','daily','weekly','monthly','yearly','never') NOT NULL DEFAULT 'weekly',
  `priority` decimal(2,1) DEFAULT '0.5',
  `standalone` tinyint(4) DEFAULT NULL COMMENT 'header/footer disabled (1) enabled (null)',
  `signin_required` tinyint(1) DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pages`
--

LOCK TABLES `pages` WRITE;
/*!40000 ALTER TABLE `pages` DISABLE KEYS */;
INSERT INTO `pages` VALUES (1,0,'Home','Home','home.html','weekly',1.0,0,0,'2016-10-25 22:34:56'),(2,3,'Thumbnail Info','Thumbnail Info','photo-gallery/thumbnail-info.html','weekly',0.5,1,0,'2016-11-04 15:50:30'),(3,1,'Browse','Browse photos','photo-gallery/browse.html','weekly',0.5,0,0,'2016-11-02 19:38:31'),(4,1,'Contact','Contact us','contact.html','weekly',0.5,NULL,NULL,'2016-11-04 15:19:43'),(5,1,'Page Settings','Page Settings','admin/pages/settings.html','weekly',0.1,0,NULL,'2016-11-01 00:24:48'),(6,1,'Process Images','Process images','photo-gallery/process-files.html','weekly',0.0,0,0,'2016-11-02 20:12:02'),(7,3,'Search','Search for photo','photo-gallery/search.html','weekly',0.5,0,NULL,'2016-11-04 15:50:30'),(8,3,'Tags','Tags Search','photo-gallery/tags.html','weekly',0.5,0,0,'2016-11-04 15:50:30'),(9,3,'Directory','Directory Search','photo-gallery/directory.html','weekly',0.5,0,0,'2016-11-04 15:50:30'),(10,2,'Download File','Download File','photo-gallery/download.html','never',0.0,1,0,'2016-11-04 15:50:30'),(11,2,'Edit','Edit File','photo-gallery/edit.html','never',0.0,0,0,'2016-11-16 17:43:54');
/*!40000 ALTER TABLE `pages` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2016-11-22 16:34:55
