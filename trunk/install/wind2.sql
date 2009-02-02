-- phpMyAdmin SQL Dump
-- version 3.1.2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Feb 01, 2009 at 04:45 PM
-- Server version: 5.0.67
-- PHP Version: 5.2.6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `wind2`
--

-- --------------------------------------------------------

--
-- Table structure for table `areas`
--

DROP TABLE IF EXISTS `areas`;
CREATE TABLE IF NOT EXISTS `areas` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `region_id` int(10) unsigned NOT NULL default '0',
  `name` varchar(40) NOT NULL default '',
  `ip_start` int(10) NOT NULL default '0',
  `ip_end` int(10) NOT NULL default '0',
  `info` text,
  PRIMARY KEY  (`id`),
  KEY `region_id` (`region_id`),
  KEY `name` (`name`),
  KEY `ip_start` (`ip_start`),
  KEY `ip_end` (`ip_end`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=125 ;

-- --------------------------------------------------------

--
-- Table structure for table `dns_nameservers`
--

DROP TABLE IF EXISTS `dns_nameservers`;
CREATE TABLE IF NOT EXISTS `dns_nameservers` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `date_in` datetime NOT NULL default '0000-00-00 00:00:00',
  `node_id` int(10) unsigned NOT NULL default '0',
  `name` enum('ns0','ns1','ns2','ns3') NOT NULL default 'ns0',
  `ip` int(10) NOT NULL default '0',
  `status` enum('waiting','active','pending','rejected','invalid') NOT NULL default 'waiting',
  `delete_req` enum('Y','N') NOT NULL default 'N',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `unique_keys` (`name`,`node_id`),
  KEY `date_in` (`date_in`),
  KEY `node_id` (`node_id`),
  KEY `ip` (`ip`),
  KEY `status` (`status`),
  KEY `delete_req` (`delete_req`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=760 ;

-- --------------------------------------------------------

--
-- Table structure for table `dns_zones`
--

DROP TABLE IF EXISTS `dns_zones`;
CREATE TABLE IF NOT EXISTS `dns_zones` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `date_in` datetime NOT NULL default '0000-00-00 00:00:00',
  `type` enum('forward','reverse') NOT NULL default 'forward',
  `name` varchar(30) NOT NULL default '',
  `node_id` int(10) unsigned default '0',
  `status` enum('waiting','active','pending','rejected','invalid') NOT NULL default 'waiting',
  `info` text,
  `delete_req` enum('Y','N') NOT NULL default 'N',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `unique_keys` (`name`,`type`),
  KEY `type` (`type`),
  KEY `date_in` (`date_in`),
  KEY `node_id` (`node_id`),
  KEY `status` (`status`),
  KEY `delete_req` (`delete_req`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1599 ;

-- --------------------------------------------------------

--
-- Table structure for table `dns_zones_nameservers`
--

DROP TABLE IF EXISTS `dns_zones_nameservers`;
CREATE TABLE IF NOT EXISTS `dns_zones_nameservers` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `zone_id` int(10) unsigned NOT NULL default '0',
  `nameserver_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `unique_keys` (`zone_id`,`nameserver_id`),
  KEY `nameserver_id` (`nameserver_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4158 ;

-- --------------------------------------------------------

--
-- Table structure for table `ip_addresses`
--

DROP TABLE IF EXISTS `ip_addresses`;
CREATE TABLE IF NOT EXISTS `ip_addresses` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `date_in` datetime NOT NULL default '0000-00-00 00:00:00',
  `hostname` varchar(50) NOT NULL default '',
  `ip` int(10) NOT NULL default '0',
  `mac` varchar(17) default NULL,
  `node_id` int(10) unsigned NOT NULL default '0',
  `type` enum('router','server','pc','wireless-bridge','voip','camera','other') NOT NULL default 'pc',
  `always_on` enum('Y','N') NOT NULL default 'N',
  `info` text,
  PRIMARY KEY  (`id`),
  KEY `ip` (`ip`),
  KEY `node_id` (`node_id`),
  KEY `hostname` (`hostname`),
  KEY `type` (`type`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2620 ;

-- --------------------------------------------------------

--
-- Table structure for table `ip_ranges`
--

DROP TABLE IF EXISTS `ip_ranges`;
CREATE TABLE IF NOT EXISTS `ip_ranges` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `date_in` datetime NOT NULL default '0000-00-00 00:00:00',
  `node_id` int(10) unsigned NOT NULL default '0',
  `ip_start` int(10) NOT NULL default '0',
  `ip_end` int(10) NOT NULL default '0',
  `status` enum('waiting','active','pending','rejected','invalid') NOT NULL default 'waiting',
  `info` text,
  `delete_req` enum('Y','N') NOT NULL default 'N',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `unique_keys` (`node_id`,`ip_start`,`ip_end`),
  KEY `date_in` (`date_in`),
  KEY `ip_start` (`ip_start`),
  KEY `ip_end` (`ip_end`),
  KEY `status` (`status`),
  KEY `delete_req` (`delete_req`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1102 ;

-- --------------------------------------------------------

--
-- Table structure for table `links`
--

DROP TABLE IF EXISTS `links`;
CREATE TABLE IF NOT EXISTS `links` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `date_in` datetime NOT NULL default '0000-00-00 00:00:00',
  `node_id` int(10) unsigned NOT NULL default '0',
  `peer_node_id` int(10) unsigned default NULL,
  `peer_ap_id` int(10) unsigned default NULL,
  `type` enum('p2p','ap','client') NOT NULL default 'p2p',
  `ssid` varchar(50) default NULL,
  `protocol` enum('IEEE 802.11b','IEEE 802.11g','IEEE 802.11a','other') default NULL,
  `channel` varchar(50) default NULL,
  `status` enum('active','inactive') NOT NULL default 'active',
  `equipment` text,
  `info` text,
  `live` enum('active','inactive') NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `type` (`type`),
  KEY `status` (`status`),
  KEY `live` (`live`),
  KEY `peer_node_id` (`peer_node_id`,`type`,`status`,`live`),
  KEY `node_id` (`node_id`,`type`,`status`,`live`),
  KEY `peer_ap_id` (`peer_ap_id`,`type`,`status`,`live`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7365 ;

-- --------------------------------------------------------

--
-- Table structure for table `live_links_temp`
--

DROP TABLE IF EXISTS `live_links_temp`;
CREATE TABLE IF NOT EXISTS `live_links_temp` (
  `id` bigint(10) NOT NULL auto_increment,
  `node1` varchar(250) NOT NULL default '0',
  `node2` varchar(250) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `node1` (`node1`,`node2`),
  KEY `node1_2` (`node1`),
  KEY `node2` (`node2`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=875 ;

-- --------------------------------------------------------

--
-- Table structure for table `live_prepends`
--

DROP TABLE IF EXISTS `live_prepends`;
CREATE TABLE IF NOT EXISTS `live_prepends` (
  `id` int(10) NOT NULL auto_increment,
  `nodeid` int(10) NOT NULL default '0',
  `parent_nodeid` varchar(255) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `nodeid` (`nodeid`,`parent_nodeid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=10 ;

-- --------------------------------------------------------

--
-- Table structure for table `newsletter`
--

DROP TABLE IF EXISTS `newsletter`;
CREATE TABLE IF NOT EXISTS `newsletter` (
  `id` int(10) NOT NULL auto_increment,
  `Nodeid` int(10) NOT NULL default '0',
  `Username` varchar(255) NOT NULL default '',
  `Email` varchar(255) NOT NULL default '',
  `Cclass` varchar(255) NOT NULL default '',
  `Nodename` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `Nodeid` (`Nodeid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=525 ;

-- --------------------------------------------------------

--
-- Table structure for table `nodes`
--

DROP TABLE IF EXISTS `nodes`;
CREATE TABLE IF NOT EXISTS `nodes` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `date_in` datetime NOT NULL default '0000-00-00 00:00:00',
  `name` varchar(50) NOT NULL default '',
  `name_ns` varchar(50) NOT NULL default '',
  `area_id` int(10) unsigned default '0',
  `latitude` float default NULL,
  `longitude` float default NULL,
  `elevation` int(10) unsigned default NULL,
  `info` text,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `unique_keys` (`name_ns`),
  KEY `date_in` (`date_in`),
  KEY `name` (`name`),
  KEY `area_id` (`area_id`),
  KEY `latitude` (`latitude`),
  KEY `longitude` (`longitude`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 PACK_KEYS=0 AUTO_INCREMENT=11528 ;

-- --------------------------------------------------------

--
-- Table structure for table `nodes_routers`
--

DROP TABLE IF EXISTS `nodes_routers`;
CREATE TABLE IF NOT EXISTS `nodes_routers` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `node_id` int(10) unsigned NOT NULL default '0',
  `ip_id` int(10) unsigned NOT NULL default '0',
  `port` int(10) unsigned NOT NULL default '0',
  `password` varchar(32) default NULL,
  `date_in` datetime NOT NULL default '0000-00-00 00:00:00',
  `status` enum('active','inactive') NOT NULL default 'active',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;

-- --------------------------------------------------------

--
-- Table structure for table `nodes_services`
--

DROP TABLE IF EXISTS `nodes_services`;
CREATE TABLE IF NOT EXISTS `nodes_services` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `date_in` datetime NOT NULL default '0000-00-00 00:00:00',
  `node_id` int(10) unsigned NOT NULL default '0',
  `service_id` int(10) unsigned NOT NULL default '0',
  `ip_id` int(10) unsigned default '0',
  `url` varchar(255) default NULL,
  `info` text,
  `status` enum('active','inactive') NOT NULL default 'active',
  `protocol` enum('tcp','udp') default NULL,
  `port` int(10) unsigned default NULL,
  PRIMARY KEY  (`id`),
  KEY `date_in` (`date_in`),
  KEY `node_id` (`node_id`),
  KEY `service_id` (`service_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=647 ;

-- --------------------------------------------------------

--
-- Table structure for table `nonexistent`
--

DROP TABLE IF EXISTS `nonexistent`;
CREATE TABLE IF NOT EXISTS `nonexistent` (
  `id` int(10) NOT NULL auto_increment,
  `Cclass` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `photos`
--

DROP TABLE IF EXISTS `photos`;
CREATE TABLE IF NOT EXISTS `photos` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `date_in` datetime NOT NULL default '0000-00-00 00:00:00',
  `node_id` int(10) unsigned NOT NULL default '0',
  `type` enum('galery','view') NOT NULL default 'galery',
  `view_point` enum('N','NE','E','SE','S','SW','W','NW','PANORAMIC') default NULL,
  `info` text,
  PRIMARY KEY  (`id`),
  KEY `date_in` (`date_in`),
  KEY `node_id` (`node_id`),
  KEY `type` (`type`),
  KEY `view_point` (`view_point`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3517 ;

-- --------------------------------------------------------

--
-- Table structure for table `regions`
--

DROP TABLE IF EXISTS `regions`;
CREATE TABLE IF NOT EXISTS `regions` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(40) NOT NULL default '',
  `ip_start` int(10) NOT NULL default '0',
  `ip_end` int(10) NOT NULL default '0',
  `info` text,
  PRIMARY KEY  (`id`),
  KEY `name` (`name`),
  KEY `ip_start` (`ip_start`),
  KEY `ip_end` (`ip_end`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

-- --------------------------------------------------------

--
-- Table structure for table `rights`
--

DROP TABLE IF EXISTS `rights`;
CREATE TABLE IF NOT EXISTS `rights` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `user_id` int(10) unsigned NOT NULL default '0',
  `type` enum('blocked','admin','hostmaster') NOT NULL default 'blocked',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `unique_keys` (`type`,`user_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=29 ;

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

DROP TABLE IF EXISTS `services`;
CREATE TABLE IF NOT EXISTS `services` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `title` varchar(255) NOT NULL default '',
  `protocol` enum('tcp','udp') default NULL,
  `port` int(10) unsigned default '0',
  PRIMARY KEY  (`id`),
  KEY `title` (`title`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=33 ;

-- --------------------------------------------------------

--
-- Table structure for table `subnets`
--

DROP TABLE IF EXISTS `subnets`;
CREATE TABLE IF NOT EXISTS `subnets` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `date_in` datetime NOT NULL default '0000-00-00 00:00:00',
  `node_id` int(10) unsigned default NULL,
  `ip_start` int(10) NOT NULL default '0',
  `ip_end` int(10) NOT NULL default '0',
  `type` enum('local','link','client') NOT NULL default 'local',
  `link_id` int(10) unsigned default NULL,
  `client_node_id` int(10) unsigned default NULL,
  PRIMARY KEY  (`id`),
  KEY `node_id` (`node_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1403 ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `date_in` datetime NOT NULL default '0000-00-00 00:00:00',
  `username` varchar(30) NOT NULL default '',
  `password` varchar(40) default NULL,
  `surname` varchar(30) default NULL,
  `name` varchar(30) default NULL,
  `phone` varchar(60) default NULL,
  `email` varchar(50) NOT NULL default '',
  `info` text,
  `last_session` datetime default NULL,
  `last_visit` datetime default NULL,
  `status` enum('activated','pending') NOT NULL default 'pending',
  `account_code` varchar(20) default NULL,
  `language` varchar(30) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `date_in` (`date_in`),
  KEY `password` (`password`),
  KEY `surname` (`surname`),
  KEY `name` (`name`),
  KEY `status` (`status`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=8967 ;

-- --------------------------------------------------------

--
-- Table structure for table `users_nodes`
--

DROP TABLE IF EXISTS `users_nodes`;
CREATE TABLE IF NOT EXISTS `users_nodes` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `user_id` int(10) unsigned NOT NULL default '0',
  `node_id` int(10) unsigned NOT NULL default '0',
  `owner` enum('Y','N') NOT NULL default 'N',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `unique_keys` (`node_id`,`user_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=24109 ;
