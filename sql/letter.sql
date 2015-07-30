-- phpMyAdmin SQL Dump
-- version 4.2.8
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jul 30, 2015 at 02:40 AM
-- Server version: 5.6.21
-- PHP Version: 5.4.35

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `myopenletter`
--

-- --------------------------------------------------------

--
-- Table structure for table `letter`
--

CREATE TABLE IF NOT EXISTS `letter` (
`id` bigint(22) unsigned NOT NULL,
  `edit_slug` varchar(1024) NOT NULL,
  `timestamp` datetime NOT NULL,
  `from` varchar(1000) NOT NULL,
  `from_slug` varchar(1000) NOT NULL,
  `to` varchar(1000) NOT NULL,
  `to_slug` varchar(1000) NOT NULL,
  `content` text NOT NULL,
  `ip` varchar(100) NOT NULL,
  `comments` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB AUTO_INCREMENT=1522 DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `letter`
--
ALTER TABLE `letter`
 ADD PRIMARY KEY (`id`), ADD KEY `edit_slug` (`edit_slug`(255));

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `letter`
--
ALTER TABLE `letter`
MODIFY `id` bigint(22) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1522;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
