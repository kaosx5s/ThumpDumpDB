SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `tddb_release`
--

-- --------------------------------------------------------

--
-- Table structure for table `abilitymodules`
--

CREATE TABLE IF NOT EXISTS `abilitymodules` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `itemTypeId` int(11) NOT NULL,
  `abilityId` int(11) DEFAULT NULL,
  `craftingTypeId` int(11) DEFAULT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `iconId` int(11) DEFAULT NULL,
  `itemCraftingTypeId` int(11) DEFAULT NULL,
  `level` int(11) DEFAULT NULL,
  `name` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `moduleType` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `powerLevel` int(11) DEFAULT NULL,
  `rarity` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `subTypeId` int(11) DEFAULT NULL,
  `type` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `uiCategory` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `version` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `durability` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=0 ;

--
-- Dumping data for table `abilitymodules`
--


-- --------------------------------------------------------

--
-- Table structure for table `achievements`
--

CREATE TABLE IF NOT EXISTS `achievements` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `a_id` int(11) NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `category` int(11) NOT NULL,
  `points` int(11) NOT NULL,
  `reward_name` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `reward_tooltip` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `version` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=0 ;

--
-- Dumping data for table `achievements`
--


-- --------------------------------------------------------

--
-- Table structure for table `armies`
--

CREATE TABLE IF NOT EXISTS `armies` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `armyId` int(11) NOT NULL,
  `name` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `commander` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `playstyle` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `member_count` int(11) NOT NULL,
  `personality` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `version` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `is_recruiting` tinyint(1) NOT NULL DEFAULT '0',
  `motd` varchar(1000) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=0 ;

--
-- Dumping data for table `armies`
--


-- --------------------------------------------------------

--
-- Table structure for table `armymembers`
--

CREATE TABLE IF NOT EXISTS `armymembers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `db_id` bigint(20) NOT NULL,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `rank` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `army_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `armymembers_army_id_foreign` (`army_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=0 ;

--
-- Dumping data for table `armymembers`
--


-- --------------------------------------------------------

--
-- Table structure for table `backpacks`
--

CREATE TABLE IF NOT EXISTS `backpacks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `itemTypeId` int(11) NOT NULL,
  `craftingTypeId` int(11) DEFAULT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `iconId` int(11) DEFAULT NULL,
  `itemCraftingTypeId` int(11) DEFAULT NULL,
  `level` int(11) DEFAULT NULL,
  `name` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `rarity` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `subTypeId` int(11) DEFAULT NULL,
  `type` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `version` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `durability` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=0 ;

--
-- Dumping data for table `backpacks`
--


-- --------------------------------------------------------

--
-- Table structure for table `basics`
--

CREATE TABLE IF NOT EXISTS `basics` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `itemTypeId` int(11) NOT NULL,
  `craftingTypeId` int(11) DEFAULT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `iconId` int(11) DEFAULT NULL,
  `itemCraftingTypeId` int(11) DEFAULT NULL,
  `level` int(11) DEFAULT NULL,
  `name` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `rarity` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `subTypeId` int(11) DEFAULT NULL,
  `type` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `version` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=0 ;

--
-- Dumping data for table `basics`
--


-- --------------------------------------------------------

--
-- Table structure for table `blueprints`
--

CREATE TABLE IF NOT EXISTS `blueprints` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `itemTypeId` int(11) NOT NULL,
  `craftingTypeId` int(11) DEFAULT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `itemCraftingTypeId` int(11) DEFAULT NULL,
  `level` int(11) DEFAULT NULL,
  `name` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `rarity` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `subTypeId` int(11) DEFAULT NULL,
  `type` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `version` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=0 ;

--
-- Dumping data for table `blueprints`
--


-- --------------------------------------------------------

--
-- Table structure for table `certifications`
--

CREATE TABLE IF NOT EXISTS `certifications` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `web_icon` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `xp` int(11) NOT NULL,
  `description` varchar(500) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=0 ;

--
-- Dumping data for table `certifications`
--


-- --------------------------------------------------------

--
-- Table structure for table `certificationyields`
--

CREATE TABLE IF NOT EXISTS `certificationyields` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `itemTypeId` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `certification_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `certificationyields_certification_id_foreign` (`certification_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=0 ;

--
-- Dumping data for table `certificationyields`
--


-- --------------------------------------------------------

--
-- Table structure for table `characterinfos`
--

CREATE TABLE IF NOT EXISTS `characterinfos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `db_id` bigint(20) NOT NULL,
  `entry` blob NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=0 ;

--
-- Dumping data for table `characterinfos`
--


-- --------------------------------------------------------

--
-- Table structure for table `chassis`
--

CREATE TABLE IF NOT EXISTS `chassis` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `itemTypeId` int(11) NOT NULL,
  `craftingTypeId` int(11) DEFAULT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `iconId` int(11) DEFAULT NULL,
  `itemCraftingTypeId` int(11) DEFAULT NULL,
  `level` int(11) DEFAULT NULL,
  `name` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `progression_item_id` int(11) DEFAULT NULL,
  `progression_resource_id` int(11) DEFAULT NULL,
  `rarity` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `subTypeId` int(11) DEFAULT NULL,
  `type` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `version` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=0 ;

--
-- Dumping data for table `chassis`
--


-- --------------------------------------------------------

--
-- Table structure for table `consumables`
--

CREATE TABLE IF NOT EXISTS `consumables` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `itemTypeId` int(11) NOT NULL,
  `abilityId` int(11) DEFAULT NULL,
  `craftingTypeId` int(11) DEFAULT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `iconId` int(11) DEFAULT NULL,
  `itemCraftingTypeId` int(11) DEFAULT NULL,
  `level` int(11) DEFAULT NULL,
  `name` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `powerLevel` int(11) DEFAULT NULL,
  `rarity` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `subTypeId` int(11) DEFAULT NULL,
  `type` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `version` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=0 ;

--
-- Dumping data for table `consumables`
--


-- --------------------------------------------------------

--
-- Table structure for table `craftingcomponents`
--

CREATE TABLE IF NOT EXISTS `craftingcomponents` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `itemTypeId` int(11) NOT NULL,
  `craftingTypeId` int(11) DEFAULT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `itemCraftingTypeId` int(11) DEFAULT NULL,
  `level` int(11) DEFAULT NULL,
  `name` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `rarity` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `subTypeId` int(11) DEFAULT NULL,
  `type` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `version` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=0 ;

--
-- Dumping data for table `craftingcomponents`
--


-- --------------------------------------------------------

--
-- Table structure for table `craftingstations`
--

CREATE TABLE IF NOT EXISTS `craftingstations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `itemTypeId` int(11) NOT NULL,
  `craftingTypeId` int(11) DEFAULT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `itemCraftingTypeId` int(11) DEFAULT NULL,
  `level` int(11) DEFAULT NULL,
  `name` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `rarity` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `subTypeId` int(11) DEFAULT NULL,
  `type` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `version` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=0 ;

--
-- Dumping data for table `craftingstations`
--


-- --------------------------------------------------------

--
-- Table structure for table `craftingsubcomponents`
--

CREATE TABLE IF NOT EXISTS `craftingsubcomponents` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `itemTypeId` int(11) NOT NULL,
  `craftingTypeId` int(11) DEFAULT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `itemCraftingTypeId` int(11) DEFAULT NULL,
  `level` int(11) DEFAULT NULL,
  `name` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `rarity` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `subTypeId` int(11) DEFAULT NULL,
  `type` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `version` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=0 ;

--
-- Dumping data for table `craftingsubcomponents`
--


-- --------------------------------------------------------

--
-- Table structure for table `firemodules`
--

CREATE TABLE IF NOT EXISTS `firemodules` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `itemTypeId` int(11) NOT NULL,
  `craftingTypeId` int(11) DEFAULT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `itemCraftingTypeId` int(11) DEFAULT NULL,
  `level` int(11) DEFAULT NULL,
  `name` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `rarity` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `subTypeId` int(11) DEFAULT NULL,
  `type` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `version` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=0 ;

--
-- Dumping data for table `firemodules`
--


-- --------------------------------------------------------

--
-- Table structure for table `framemodules`
--

CREATE TABLE IF NOT EXISTS `framemodules` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `itemTypeId` int(11) NOT NULL,
  `abilityId` int(11) DEFAULT NULL,
  `craftingTypeId` int(11) DEFAULT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `iconId` int(11) DEFAULT NULL,
  `itemCraftingTypeId` int(11) DEFAULT NULL,
  `level` int(11) DEFAULT NULL,
  `name` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `moduleType` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `powerLevel` int(11) DEFAULT NULL,
  `rarity` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `subTypeId` int(11) DEFAULT NULL,
  `type` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `uiCategory` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `version` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `durability` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=0 ;

--
-- Dumping data for table `framemodules`
--


-- --------------------------------------------------------

--
-- Table structure for table `hattributes`
--

CREATE TABLE IF NOT EXISTS `hattributes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `itemTypeId` int(11) NOT NULL,
  `format` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `inverse` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `value` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `stat_id` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `display_name` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `version` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `abilitymodule_id` int(10) unsigned DEFAULT NULL,
  `chassis_id` int(10) unsigned DEFAULT NULL,
  `craftingcomponent_id` int(10) unsigned DEFAULT NULL,
  `framemodule_id` int(10) unsigned DEFAULT NULL,
  `weapon_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `hattributes_abilitymodule_id_foreign` (`abilitymodule_id`),
  KEY `hattributes_chassis_id_foreign` (`chassis_id`),
  KEY `hattributes_craftingcomponent_id_foreign` (`craftingcomponent_id`),
  KEY `hattributes_framemodule_id_foreign` (`framemodule_id`),
  KEY `hattributes_weapon_id_foreign` (`weapon_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=0 ;

--
-- Dumping data for table `hattributes`
--


-- --------------------------------------------------------

--
-- Table structure for table `hbaseconstraints`
--

CREATE TABLE IF NOT EXISTS `hbaseconstraints` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `itemTypeId` int(11) NOT NULL,
  `cpu` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `mass` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `power` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `version` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `abilitymodule_id` int(10) unsigned DEFAULT NULL,
  `backpack_id` int(10) unsigned DEFAULT NULL,
  `chassis_id` int(10) unsigned DEFAULT NULL,
  `consumable_id` int(10) unsigned DEFAULT NULL,
  `framemodule_id` int(10) unsigned DEFAULT NULL,
  `powerup_id` int(10) unsigned DEFAULT NULL,
  `weapon_id` int(10) unsigned DEFAULT NULL,
  `weaponmodule_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `hbaseconstraints_abilitymodule_id_foreign` (`abilitymodule_id`),
  KEY `hbaseconstraints_backpack_id_foreign` (`backpack_id`),
  KEY `hbaseconstraints_chassis_id_foreign` (`chassis_id`),
  KEY `hbaseconstraints_consumable_id_foreign` (`consumable_id`),
  KEY `hbaseconstraints_framemodule_id_foreign` (`framemodule_id`),
  KEY `hbaseconstraints_powerup_id_foreign` (`powerup_id`),
  KEY `hbaseconstraints_weapon_id_foreign` (`weapon_id`),
  KEY `hbaseconstraints_weaponmodule_id_foreign` (`weaponmodule_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=0 ;

--
-- Dumping data for table `hbaseconstraints`
--


-- --------------------------------------------------------

--
-- Table structure for table `hcertifications`
--

CREATE TABLE IF NOT EXISTS `hcertifications` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `itemTypeId` int(11) NOT NULL,
  `certificationId` int(11) NOT NULL,
  `version` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `abilitymodule_id` int(10) unsigned DEFAULT NULL,
  `backpack_id` int(10) unsigned DEFAULT NULL,
  `basic_id` int(10) unsigned DEFAULT NULL,
  `blueprint_id` int(10) unsigned DEFAULT NULL,
  `chassis_id` int(10) unsigned DEFAULT NULL,
  `consumable_id` int(10) unsigned DEFAULT NULL,
  `craftingcomponent_id` int(10) unsigned DEFAULT NULL,
  `craftingstation_id` int(10) unsigned DEFAULT NULL,
  `craftingsubcomponent_id` int(10) unsigned DEFAULT NULL,
  `firemodule_id` int(10) unsigned DEFAULT NULL,
  `framemodule_id` int(10) unsigned DEFAULT NULL,
  `palettemodule_id` int(10) unsigned DEFAULT NULL,
  `powerup_id` int(10) unsigned DEFAULT NULL,
  `resourceitem_id` int(10) unsigned DEFAULT NULL,
  `scopemodule_id` int(10) unsigned DEFAULT NULL,
  `weapon_id` int(10) unsigned DEFAULT NULL,
  `weaponmodule_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `hcertifications_abilitymodule_id_foreign` (`abilitymodule_id`),
  KEY `hcertifications_backpack_id_foreign` (`backpack_id`),
  KEY `hcertifications_basic_id_foreign` (`basic_id`),
  KEY `hcertifications_blueprint_id_foreign` (`blueprint_id`),
  KEY `hcertifications_chassis_id_foreign` (`chassis_id`),
  KEY `hcertifications_consumable_id_foreign` (`consumable_id`),
  KEY `hcertifications_craftingcomponent_id_foreign` (`craftingcomponent_id`),
  KEY `hcertifications_craftingstation_id_foreign` (`craftingstation_id`),
  KEY `hcertifications_craftingsubcomponent_id_foreign` (`craftingsubcomponent_id`),
  KEY `hcertifications_firemodule_id_foreign` (`firemodule_id`),
  KEY `hcertifications_framemodule_id_foreign` (`framemodule_id`),
  KEY `hcertifications_palettemodule_id_foreign` (`palettemodule_id`),
  KEY `hcertifications_powerup_id_foreign` (`powerup_id`),
  KEY `hcertifications_resourceitem_id_foreign` (`resourceitem_id`),
  KEY `hcertifications_scopemodule_id_foreign` (`scopemodule_id`),
  KEY `hcertifications_weapon_id_foreign` (`weapon_id`),
  KEY `hcertifications_weaponmodule_id_foreign` (`weaponmodule_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=0 ;

--
-- Dumping data for table `hcertifications`
--


-- --------------------------------------------------------

--
-- Table structure for table `hclasses`
--

CREATE TABLE IF NOT EXISTS `hclasses` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `itemTypeId` int(11) NOT NULL,
  `name` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `version` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `abilitymodule_id` int(10) unsigned DEFAULT NULL,
  `backpack_id` int(10) unsigned DEFAULT NULL,
  `basic_id` int(10) unsigned DEFAULT NULL,
  `chassis_id` int(10) unsigned DEFAULT NULL,
  `consumable_id` int(10) unsigned DEFAULT NULL,
  `firemodule_id` int(10) unsigned DEFAULT NULL,
  `framemodule_id` int(10) unsigned DEFAULT NULL,
  `weapon_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `hclasses_abilitymodule_id_foreign` (`abilitymodule_id`),
  KEY `hclasses_backpack_id_foreign` (`backpack_id`),
  KEY `hclasses_basic_id_foreign` (`basic_id`),
  KEY `hclasses_chassis_id_foreign` (`chassis_id`),
  KEY `hclasses_consumable_id_foreign` (`consumable_id`),
  KEY `hclasses_firemodule_id_foreign` (`firemodule_id`),
  KEY `hclasses_framemodule_id_foreign` (`framemodule_id`),
  KEY `hclasses_weapon_id_foreign` (`weapon_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=0 ;

--
-- Dumping data for table `hclasses`
--


-- --------------------------------------------------------

--
-- Table structure for table `hconstraints`
--

CREATE TABLE IF NOT EXISTS `hconstraints` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `itemTypeId` int(11) NOT NULL,
  `cpu` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `mass` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `power` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `version` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `abilitymodule_id` int(10) unsigned DEFAULT NULL,
  `backpack_id` int(10) unsigned DEFAULT NULL,
  `basic_id` int(10) unsigned DEFAULT NULL,
  `blueprint_id` int(10) unsigned DEFAULT NULL,
  `chassis_id` int(10) unsigned DEFAULT NULL,
  `consumable_id` int(10) unsigned DEFAULT NULL,
  `craftingcomponent_id` int(10) unsigned DEFAULT NULL,
  `craftingstation_id` int(10) unsigned DEFAULT NULL,
  `craftingsubcomponent_id` int(10) unsigned DEFAULT NULL,
  `firemodule_id` int(10) unsigned DEFAULT NULL,
  `framemodule_id` int(10) unsigned DEFAULT NULL,
  `palettemodule_id` int(10) unsigned DEFAULT NULL,
  `powerup_id` int(10) unsigned DEFAULT NULL,
  `resourceitem_id` int(10) unsigned DEFAULT NULL,
  `scopemodule_id` int(10) unsigned DEFAULT NULL,
  `weapon_id` int(10) unsigned DEFAULT NULL,
  `weaponmodule_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `hconstraints_abilitymodule_id_foreign` (`abilitymodule_id`),
  KEY `hconstraints_backpack_id_foreign` (`backpack_id`),
  KEY `hconstraints_basic_id_foreign` (`basic_id`),
  KEY `hconstraints_blueprint_id_foreign` (`blueprint_id`),
  KEY `hconstraints_chassis_id_foreign` (`chassis_id`),
  KEY `hconstraints_consumable_id_foreign` (`consumable_id`),
  KEY `hconstraints_craftingcomponent_id_foreign` (`craftingcomponent_id`),
  KEY `hconstraints_craftingstation_id_foreign` (`craftingstation_id`),
  KEY `hconstraints_craftingsubcomponent_id_foreign` (`craftingsubcomponent_id`),
  KEY `hconstraints_firemodule_id_foreign` (`firemodule_id`),
  KEY `hconstraints_framemodule_id_foreign` (`framemodule_id`),
  KEY `hconstraints_palettemodule_id_foreign` (`palettemodule_id`),
  KEY `hconstraints_powerup_id_foreign` (`powerup_id`),
  KEY `hconstraints_resourceitem_id_foreign` (`resourceitem_id`),
  KEY `hconstraints_scopemodule_id_foreign` (`scopemodule_id`),
  KEY `hconstraints_weapon_id_foreign` (`weapon_id`),
  KEY `hconstraints_weaponmodule_id_foreign` (`weaponmodule_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=0 ;

--
-- Dumping data for table `hconstraints`
--


-- --------------------------------------------------------

--
-- Table structure for table `hflags`
--

CREATE TABLE IF NOT EXISTS `hflags` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `itemTypeId` int(11) NOT NULL,
  `name` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `value` int(11) NOT NULL,
  `version` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `abilitymodule_id` int(10) unsigned DEFAULT NULL,
  `backpack_id` int(10) unsigned DEFAULT NULL,
  `basic_id` int(10) unsigned DEFAULT NULL,
  `blueprint_id` int(10) unsigned DEFAULT NULL,
  `chassis_id` int(10) unsigned DEFAULT NULL,
  `consumable_id` int(10) unsigned DEFAULT NULL,
  `craftingcomponent_id` int(10) unsigned DEFAULT NULL,
  `craftingstation_id` int(10) unsigned DEFAULT NULL,
  `craftingsubcomponent_id` int(10) unsigned DEFAULT NULL,
  `firemodule_id` int(10) unsigned DEFAULT NULL,
  `framemodule_id` int(10) unsigned DEFAULT NULL,
  `palettemodule_id` int(10) unsigned DEFAULT NULL,
  `powerup_id` int(10) unsigned DEFAULT NULL,
  `resourceitem_id` int(10) unsigned DEFAULT NULL,
  `scopemodule_id` int(10) unsigned DEFAULT NULL,
  `weapon_id` int(10) unsigned DEFAULT NULL,
  `weaponmodule_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `hflags_abilitymodule_id_foreign` (`abilitymodule_id`),
  KEY `hflags_backpack_id_foreign` (`backpack_id`),
  KEY `hflags_basic_id_foreign` (`basic_id`),
  KEY `hflags_blueprint_id_foreign` (`blueprint_id`),
  KEY `hflags_chassis_id_foreign` (`chassis_id`),
  KEY `hflags_consumable_id_foreign` (`consumable_id`),
  KEY `hflags_craftingcomponent_id_foreign` (`craftingcomponent_id`),
  KEY `hflags_craftingstation_id_foreign` (`craftingstation_id`),
  KEY `hflags_craftingsubcomponent_id_foreign` (`craftingsubcomponent_id`),
  KEY `hflags_firemodule_id_foreign` (`firemodule_id`),
  KEY `hflags_framemodule_id_foreign` (`framemodule_id`),
  KEY `hflags_palettemodule_id_foreign` (`palettemodule_id`),
  KEY `hflags_powerup_id_foreign` (`powerup_id`),
  KEY `hflags_resourceitem_id_foreign` (`resourceitem_id`),
  KEY `hflags_scopemodule_id_foreign` (`scopemodule_id`),
  KEY `hflags_weapon_id_foreign` (`weapon_id`),
  KEY `hflags_weaponmodule_id_foreign` (`weaponmodule_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=0 ;

--
-- Dumping data for table `hflags`
--


-- --------------------------------------------------------

--
-- Table structure for table `hstats`
--

CREATE TABLE IF NOT EXISTS `hstats` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `itemTypeId` int(11) NOT NULL,
  `ammoPerBurst` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `clipSize` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `damagePerRound` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `damagePerSecond` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `healthPerRound` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `maxAmmo` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `range` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `reloadTime` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `roundsPerBurst` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `roundsPerMinute` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `splashRadius` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `spread` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `version` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `weapon_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `hstats_weapon_id_foreign` (`weapon_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=0 ;

--
-- Dumping data for table `hstats`
--


-- --------------------------------------------------------

--
-- Table structure for table `htiers`
--

CREATE TABLE IF NOT EXISTS `htiers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `itemTypeId` int(11) NOT NULL,
  `level` int(11) NOT NULL,
  `description` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `version` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `abilitymodule_id` int(10) unsigned DEFAULT NULL,
  `blueprint_id` int(10) unsigned DEFAULT NULL,
  `chassis_id` int(10) unsigned DEFAULT NULL,
  `craftingcomponent_id` int(10) unsigned DEFAULT NULL,
  `craftingsubcomponent_id` int(10) unsigned DEFAULT NULL,
  `framemodule_id` int(10) unsigned DEFAULT NULL,
  `weapon_id` int(10) unsigned DEFAULT NULL,
  `basic_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `htiers_abilitymodule_id_foreign` (`abilitymodule_id`),
  KEY `htiers_blueprint_id_foreign` (`blueprint_id`),
  KEY `htiers_chassis_id_foreign` (`chassis_id`),
  KEY `htiers_craftingcomponent_id_foreign` (`craftingcomponent_id`),
  KEY `htiers_craftingsubcomponent_id_foreign` (`craftingsubcomponent_id`),
  KEY `htiers_framemodule_id_foreign` (`framemodule_id`),
  KEY `htiers_weapon_id_foreign` (`weapon_id`),
  KEY `htiers_basic_id_foreign` (`basic_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=0 ;

--
-- Dumping data for table `htiers`
--


-- --------------------------------------------------------

--
-- Table structure for table `hvisuals`
--

CREATE TABLE IF NOT EXISTS `hvisuals` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `itemTypeId` int(11) NOT NULL,
  `zone` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `light` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `dark` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `version` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `chassis_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `hvisuals_chassis_id_foreign` (`chassis_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=0 ;

--
-- Dumping data for table `hvisuals`
--


-- --------------------------------------------------------

--
-- Table structure for table `hwebicons`
--

CREATE TABLE IF NOT EXISTS `hwebicons` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `itemTypeId` int(11) NOT NULL,
  `web_icon` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `asset_path` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `version` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `abilitymodule_id` int(10) unsigned DEFAULT NULL,
  `backpack_id` int(10) unsigned DEFAULT NULL,
  `basic_id` int(10) unsigned DEFAULT NULL,
  `blueprint_id` int(10) unsigned DEFAULT NULL,
  `chassis_id` int(10) unsigned DEFAULT NULL,
  `consumable_id` int(10) unsigned DEFAULT NULL,
  `craftingcomponent_id` int(10) unsigned DEFAULT NULL,
  `craftingstation_id` int(10) unsigned DEFAULT NULL,
  `craftingsubcomponent_id` int(10) unsigned DEFAULT NULL,
  `firemodule_id` int(10) unsigned DEFAULT NULL,
  `framemodule_id` int(10) unsigned DEFAULT NULL,
  `palettemodule_id` int(10) unsigned DEFAULT NULL,
  `powerup_id` int(10) unsigned DEFAULT NULL,
  `resourceitem_id` int(10) unsigned DEFAULT NULL,
  `scopemodule_id` int(10) unsigned DEFAULT NULL,
  `weapon_id` int(10) unsigned DEFAULT NULL,
  `weaponmodule_id` int(10) unsigned DEFAULT NULL,
  `achievement_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `hwebicons_abilitymodule_id_foreign` (`abilitymodule_id`),
  KEY `hwebicons_backpack_id_foreign` (`backpack_id`),
  KEY `hwebicons_basic_id_foreign` (`basic_id`),
  KEY `hwebicons_blueprint_id_foreign` (`blueprint_id`),
  KEY `hwebicons_chassis_id_foreign` (`chassis_id`),
  KEY `hwebicons_consumable_id_foreign` (`consumable_id`),
  KEY `hwebicons_craftingcomponent_id_foreign` (`craftingcomponent_id`),
  KEY `hwebicons_craftingstation_id_foreign` (`craftingstation_id`),
  KEY `hwebicons_craftingsubcomponent_id_foreign` (`craftingsubcomponent_id`),
  KEY `hwebicons_firemodule_id_foreign` (`firemodule_id`),
  KEY `hwebicons_framemodule_id_foreign` (`framemodule_id`),
  KEY `hwebicons_palettemodule_id_foreign` (`palettemodule_id`),
  KEY `hwebicons_powerup_id_foreign` (`powerup_id`),
  KEY `hwebicons_resourceitem_id_foreign` (`resourceitem_id`),
  KEY `hwebicons_scopemodule_id_foreign` (`scopemodule_id`),
  KEY `hwebicons_weapon_id_foreign` (`weapon_id`),
  KEY `hwebicons_weaponmodule_id_foreign` (`weaponmodule_id`),
  KEY `hwebicons_achievement_id_foreign` (`achievement_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=0 ;

--
-- Dumping data for table `hwebicons`
--


-- --------------------------------------------------------

--
-- Table structure for table `inventories`
--

CREATE TABLE IF NOT EXISTS `inventories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `db_id` bigint(20) NOT NULL,
  `inventory` blob NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `inventories_db_id_index` (`db_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=0 ;

--
-- Dumping data for table `inventories`
--


-- --------------------------------------------------------

--
-- Table structure for table `loadouts`
--

CREATE TABLE IF NOT EXISTS `loadouts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `db_id` bigint(20) NOT NULL,
  `entry` mediumblob NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `db_id` (`db_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=0 ;

--
-- Dumping data for table `loadouts`
--


-- --------------------------------------------------------

--
-- Table structure for table `locations`
--

CREATE TABLE IF NOT EXISTS `locations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `db_id` bigint(20) NOT NULL,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `current_archetype` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `instanceId` bigint(20) NOT NULL,
  `spotter_db_id` bigint(20) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `coord_x` decimal(8,6) NOT NULL,
  `coord_y` decimal(8,6) NOT NULL,
  `coord_z` decimal(8,6) NOT NULL,
  `chunkX` int(11) NOT NULL,
  `chunkY` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=0 ;

--
-- Dumping data for table `locations`
--


-- --------------------------------------------------------

--
-- Table structure for table `marketcategories`
--

CREATE TABLE IF NOT EXISTS `marketcategories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `0` int(11) DEFAULT NULL,
  `1` int(11) DEFAULT NULL,
  `2` int(11) DEFAULT NULL,
  `3` int(11) DEFAULT NULL,
  `4` int(11) DEFAULT NULL,
  `5` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `marketlisting_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `marketcategories_marketlisting_id_foreign` (`marketlisting_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=0 ;

--
-- Dumping data for table `marketcategories`
--


-- --------------------------------------------------------

--
-- Table structure for table `marketlistings`
--

CREATE TABLE IF NOT EXISTS `marketlistings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `character_guid` bigint(20) NOT NULL,
  `item_guid` varchar(50) CHARACTER SET latin1 NOT NULL,
  `ff_id` int(10) unsigned NOT NULL,
  `item_sdb_id` int(10) unsigned NOT NULL,
  `expires_at` datetime NOT NULL,
  `icon` varchar(200) CHARACTER SET latin1 NOT NULL,
  `price_per_unit` decimal(10,3) NOT NULL,
  `price_cy` int(11) NOT NULL,
  `purchased` int(11) DEFAULT NULL,
  `rarity` varchar(200) CHARACTER SET latin1 NOT NULL,
  `quantity` int(11) NOT NULL,
  `title` varchar(200) CHARACTER SET latin1 NOT NULL,
  `category` varchar(50) CHARACTER SET latin1 NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `marketlistings_ff_id_unique` (`ff_id`),
  KEY `marketlistings_category_index` (`category`),
  KEY `marketlistings_expires_at_index` (`expires_at`),
  KEY `marketlistings_active_index` (`active`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=0 ;

--
-- Dumping data for table `marketlistings`
--


-- --------------------------------------------------------

--
-- Table structure for table `marketstatabilitymodules`
--

CREATE TABLE IF NOT EXISTS `marketstatabilitymodules` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `stats` text CHARACTER SET latin1 NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `marketlisting_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `marketstatabilitymodules_marketlisting_id_foreign` (`marketlisting_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=0 ;

--
-- Dumping data for table `marketstatabilitymodules`
--


-- --------------------------------------------------------

--
-- Table structure for table `marketstatcraftingcomponents`
--

CREATE TABLE IF NOT EXISTS `marketstatcraftingcomponents` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `mass` decimal(10,6) DEFAULT NULL,
  `power` decimal(10,6) DEFAULT NULL,
  `cpu` decimal(10,6) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `marketlisting_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `marketstatcraftingcomponents_marketlisting_id_foreign` (`marketlisting_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=0 ;

--
-- Dumping data for table `marketstatcraftingcomponents`
--


-- --------------------------------------------------------

--
-- Table structure for table `marketstatjumpjets`
--

CREATE TABLE IF NOT EXISTS `marketstatjumpjets` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `mass` decimal(11,6) NOT NULL,
  `repair_pool` int(10) unsigned NOT NULL,
  `power` int(11) NOT NULL,
  `cpu` int(11) NOT NULL,
  `air_sprint` int(11) NOT NULL,
  `energy` decimal(11,6) NOT NULL,
  `jet_energy_recharge` decimal(11,6) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `marketlisting_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `marketstatjumpjets_marketlisting_id_foreign` (`marketlisting_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=0 ;

--
-- Dumping data for table `marketstatjumpjets`
--


-- --------------------------------------------------------

--
-- Table structure for table `marketstatplatings`
--

CREATE TABLE IF NOT EXISTS `marketstatplatings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `mass` decimal(11,6) NOT NULL,
  `health` int(10) unsigned NOT NULL,
  `power` int(11) NOT NULL,
  `cpu` int(11) NOT NULL,
  `health_regen` decimal(11,6) NOT NULL,
  `repair_pool` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `marketlisting_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `marketstatplatings_marketlisting_id_foreign` (`marketlisting_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=0 ;

--
-- Dumping data for table `marketstatplatings`
--


-- --------------------------------------------------------

--
-- Table structure for table `marketstatresources`
--

CREATE TABLE IF NOT EXISTS `marketstatresources` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `1` int(10) unsigned NOT NULL,
  `2` int(10) unsigned NOT NULL,
  `3` int(10) unsigned NOT NULL,
  `4` int(10) unsigned NOT NULL,
  `5` int(10) unsigned NOT NULL,
  `quality` int(10) unsigned NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `marketlisting_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `marketstatresources_marketlisting_id_foreign` (`marketlisting_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=0 ;

--
-- Dumping data for table `marketstatresources`
--


-- --------------------------------------------------------

--
-- Table structure for table `marketstatservos`
--

CREATE TABLE IF NOT EXISTS `marketstatservos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `mass` decimal(11,6) NOT NULL,
  `repair_pool` int(11) NOT NULL,
  `power` int(11) NOT NULL,
  `run_speed` decimal(11,6) NOT NULL,
  `sprint_energy_cost` int(11) NOT NULL,
  `cpu` int(11) NOT NULL,
  `jump_height` decimal(11,6) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `marketlisting_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `marketstatservos_marketlisting_id_foreign` (`marketlisting_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=0 ;

--
-- Dumping data for table `marketstatservos`
--


-- --------------------------------------------------------

--
-- Table structure for table `marketstatweapons`
--

CREATE TABLE IF NOT EXISTS `marketstatweapons` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `power` decimal(11,6) NOT NULL,
  `dps` decimal(11,6) NOT NULL,
  `range` int(11) NOT NULL,
  `max_ammo` int(11) NOT NULL,
  `rateof_fire` int(11) NOT NULL,
  `mass` int(11) NOT NULL,
  `repair_pool` int(11) NOT NULL,
  `reload_speed` decimal(11,6) NOT NULL,
  `cpu` int(11) NOT NULL,
  `clip_size` int(11) NOT NULL,
  `weapon_splash_radius` decimal(11,6) DEFAULT NULL,
  `damage_per_round` int(11) DEFAULT NULL,
  `weapon_spread` decimal(11,6) DEFAULT NULL,
  `charge_up_duration` decimal(11,6) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `marketlisting_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `marketstatweapons_marketlisting_id_foreign` (`marketlisting_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=0 ;

--
-- Dumping data for table `marketstatweapons`
--


-- --------------------------------------------------------

--
-- Table structure for table `palettemodules`
--

CREATE TABLE IF NOT EXISTS `palettemodules` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `itemTypeId` int(11) NOT NULL,
  `craftingTypeId` int(11) DEFAULT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `itemCraftingTypeId` int(11) DEFAULT NULL,
  `level` int(11) DEFAULT NULL,
  `name` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `rarity` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `subTypeId` int(11) DEFAULT NULL,
  `type` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `version` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=0 ;

--
-- Dumping data for table `palettemodules`
--


-- --------------------------------------------------------

--
-- Table structure for table `players`
--

CREATE TABLE IF NOT EXISTS `players` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `instanceId` bigint(20) DEFAULT NULL,
  `db_id` bigint(20) NOT NULL,
  `abilities` blob,
  `current_archetype` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  `armyId` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `e_id` bigint(20) DEFAULT NULL,
  `armyTag` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `status` int(11) NOT NULL DEFAULT '0',
  `addon_user` tinyint(1) NOT NULL DEFAULT '0',
  `region` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `db_id` (`db_id`),
  KEY `addon_user` (`addon_user`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=0 ;

--
-- Dumping data for table `players`
--


-- --------------------------------------------------------

--
-- Table structure for table `pointofinterests`
--

CREATE TABLE IF NOT EXISTS `pointofinterests` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `point_type` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `coord_x` int(11) NOT NULL,
  `coord_y` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=0 ;

--
-- Dumping data for table `pointofinterests`
--


-- --------------------------------------------------------

--
-- Table structure for table `powerups`
--

CREATE TABLE IF NOT EXISTS `powerups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `itemTypeId` int(11) NOT NULL,
  `craftingTypeId` int(11) DEFAULT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `iconId` int(11) DEFAULT NULL,
  `itemCraftingTypeId` int(11) DEFAULT NULL,
  `level` int(11) DEFAULT NULL,
  `name` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `rarity` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `subTypeId` int(11) DEFAULT NULL,
  `type` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `version` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=0 ;

--
-- Dumping data for table `powerups`
--


-- --------------------------------------------------------

--
-- Table structure for table `printers`
--

CREATE TABLE IF NOT EXISTS `printers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `db_id` bigint(20) NOT NULL,
  `ready_at` int(11) NOT NULL,
  `started_at` int(11) NOT NULL,
  `blueprint_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;

--
-- Dumping data for table `printers`
--


-- --------------------------------------------------------

--
-- Table structure for table `progresses`
--

CREATE TABLE IF NOT EXISTS `progresses` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `db_id` bigint(20) NOT NULL,
  `entry` mediumblob NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `progresses_db_id_index` (`db_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=0 ;

--
-- Dumping data for table `progresses`
--


-- --------------------------------------------------------

--
-- Table structure for table `progressionunlocks`
--

CREATE TABLE IF NOT EXISTS `progressionunlocks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cost_items` int(11) NOT NULL,
  `cost_crystite` int(11) NOT NULL,
  `cost_xp` int(11) NOT NULL,
  `cost_resources` int(11) NOT NULL,
  `max_speed_boost_mass` varchar(5) COLLATE utf8_unicode_ci NOT NULL,
  `cert_cpu` int(11) NOT NULL,
  `cert_mass` int(11) NOT NULL,
  `cert_power` int(11) NOT NULL,
  `bonus_energy_cpu` int(11) NOT NULL,
  `add_cpu` int(11) NOT NULL,
  `add_mass` int(11) NOT NULL,
  `add_power` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=0 ;

--
-- Dumping data for table `progressionunlocks`
--


-- --------------------------------------------------------

--
-- Table structure for table `pveevents`
--

CREATE TABLE IF NOT EXISTS `pveevents` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `db_id` bigint(20) NOT NULL,
  `ares_missions_0` int(10) unsigned NOT NULL DEFAULT '0',
  `ares_missions_1` int(10) unsigned NOT NULL DEFAULT '0',
  `crashed_lgvs` int(10) unsigned NOT NULL DEFAULT '0',
  `crashed_thumpers` int(10) unsigned NOT NULL DEFAULT '0',
  `holmgang_tech_completed` int(10) unsigned NOT NULL DEFAULT '0',
  `lgv_races` int(10) unsigned NOT NULL DEFAULT '0',
  `lgv_fastest_time_sunken_copa` decimal(10,6) unsigned NOT NULL DEFAULT '999.000000',
  `lgv_fastest_time_thump_copa` decimal(10,6) unsigned NOT NULL DEFAULT '999.000000',
  `lgv_fastest_time_copa_trans` decimal(10,6) unsigned NOT NULL DEFAULT '999.000000',
  `lgv_fastest_time_copa_thump` decimal(10,6) unsigned NOT NULL DEFAULT '999.000000',
  `lgv_fastest_time_trans_sunken` decimal(10,6) unsigned NOT NULL DEFAULT '999.000000',
  `outposts_defended` int(10) unsigned NOT NULL DEFAULT '0',
  `strike_teams_0` int(10) unsigned NOT NULL DEFAULT '0',
  `strike_teams_1` int(10) unsigned NOT NULL DEFAULT '0',
  `strike_teams_2` int(10) unsigned NOT NULL DEFAULT '0',
  `strike_teams_3` int(10) unsigned NOT NULL DEFAULT '0',
  `sunken_harbor_invasions_completed` int(10) unsigned NOT NULL DEFAULT '0',
  `thump_dump_invasions_completed` int(10) unsigned NOT NULL DEFAULT '0',
  `tornados_3` int(10) unsigned NOT NULL DEFAULT '0',
  `tornados_4` int(10) unsigned NOT NULL DEFAULT '0',
  `warbringers_3` int(10) unsigned NOT NULL DEFAULT '0',
  `warbringers_4` int(10) unsigned NOT NULL DEFAULT '0',
  `watchtowers_defended` int(10) unsigned NOT NULL DEFAULT '0',
  `watchtowers_retaken` int(10) unsigned NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `raider_squads_defeated` int(10) unsigned NOT NULL DEFAULT '0',
  `chosen_death_squads` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=0 ;

--
-- Dumping data for table `pveevents`
--


-- --------------------------------------------------------

--
-- Table structure for table `pvekills`
--

CREATE TABLE IF NOT EXISTS `pvekills` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `db_id` bigint(20) NOT NULL,
  `t1` blob,
  `t2` blob,
  `t3` blob,
  `t4` blob,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=0 ;

--
-- Dumping data for table `pvekills`
--


-- --------------------------------------------------------

--
-- Table structure for table `pvestats`
--

CREATE TABLE IF NOT EXISTS `pvestats` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `db_id` bigint(20) NOT NULL,
  `accuracy` int(11) NOT NULL,
  `damage_done` int(10) unsigned NOT NULL DEFAULT '0',
  `damage_taken` int(10) unsigned NOT NULL DEFAULT '0',
  `deaths` int(10) unsigned NOT NULL DEFAULT '0',
  `drowned` int(10) unsigned NOT NULL DEFAULT '0',
  `headshots` int(10) unsigned NOT NULL DEFAULT '0',
  `healed` int(10) unsigned NOT NULL DEFAULT '0',
  `incapacitated` int(10) unsigned NOT NULL DEFAULT '0',
  `primary_reloads` int(10) unsigned NOT NULL DEFAULT '0',
  `primary_weapon_shots_fired` int(10) unsigned NOT NULL DEFAULT '0',
  `revived` int(10) unsigned NOT NULL DEFAULT '0',
  `revives` int(10) unsigned NOT NULL DEFAULT '0',
  `scanhammer_kills` int(10) unsigned NOT NULL DEFAULT '0',
  `secondary_reloads` int(10) unsigned NOT NULL DEFAULT '0',
  `secondary_weapon_shots_fired` int(10) unsigned NOT NULL DEFAULT '0',
  `suicides` int(10) unsigned NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=0 ;

--
-- Dumping data for table `pvestats`
--


-- --------------------------------------------------------

--
-- Table structure for table `pvpratingsharvesters`
--

CREATE TABLE IF NOT EXISTS `pvpratingsharvesters` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `guid` bigint(20) NOT NULL,
  `games_played` int(10) unsigned NOT NULL,
  `damage_healed` int(10) unsigned NOT NULL,
  `net_damage` int(10) unsigned NOT NULL,
  `win_loss_ratio` int(10) unsigned NOT NULL,
  `kills` int(10) unsigned NOT NULL,
  `kda_ratio` decimal(5,2) NOT NULL,
  `character_name` varchar(50) NOT NULL,
  `rating` int(10) unsigned NOT NULL,
  `total_pages` int(10) unsigned NOT NULL,
  `elo_leaderboard_id` int(10) unsigned NOT NULL,
  `losses` int(10) unsigned NOT NULL,
  `deaths` int(10) unsigned NOT NULL,
  `wins` int(10) unsigned NOT NULL,
  `revives` int(10) unsigned NOT NULL,
  `ff_id` int(10) unsigned NOT NULL,
  `damage_dealt` int(10) unsigned NOT NULL,
  `assists` int(10) unsigned NOT NULL,
  `ff_created_at` datetime NOT NULL,
  `ff_updated_at` datetime NOT NULL,
  `passes` int(10) unsigned NOT NULL,
  `projectile_hits` int(10) unsigned NOT NULL,
  `executes` int(10) unsigned NOT NULL,
  `damage_taken` int(10) unsigned NOT NULL,
  `points_scored` int(10) unsigned NOT NULL,
  `projectile_misses` int(10) unsigned NOT NULL,
  `rank` int(10) unsigned NOT NULL,
  `added_on` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;

--
-- Dumping data for table `pvpratingsharvesters`
--


-- --------------------------------------------------------

--
-- Table structure for table `pvpratingsjetballs`
--

CREATE TABLE IF NOT EXISTS `pvpratingsjetballs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `guid` bigint(20) NOT NULL,
  `games_played` int(10) unsigned NOT NULL,
  `damage_healed` int(10) unsigned NOT NULL,
  `net_damage` int(10) unsigned NOT NULL,
  `win_loss_ratio` int(10) unsigned NOT NULL,
  `kills` int(10) unsigned NOT NULL,
  `kda_ratio` decimal(5,2) NOT NULL,
  `character_name` varchar(50) NOT NULL,
  `rating` int(10) unsigned NOT NULL,
  `total_pages` int(10) unsigned NOT NULL,
  `elo_leaderboard_id` int(10) unsigned NOT NULL,
  `losses` int(10) unsigned NOT NULL,
  `deaths` int(10) unsigned NOT NULL,
  `wins` int(10) unsigned NOT NULL,
  `revives` int(10) unsigned NOT NULL,
  `ff_id` int(10) unsigned NOT NULL,
  `damage_dealt` int(10) unsigned NOT NULL,
  `assists` int(10) unsigned NOT NULL,
  `ff_created_at` datetime NOT NULL,
  `ff_updated_at` datetime NOT NULL,
  `passes` int(10) unsigned NOT NULL,
  `projectile_hits` int(10) unsigned NOT NULL,
  `executes` int(10) unsigned NOT NULL,
  `damage_taken` int(10) unsigned NOT NULL,
  `points_scored` int(10) unsigned NOT NULL,
  `projectile_misses` int(10) unsigned NOT NULL,
  `rank` int(10) unsigned NOT NULL,
  `added_on` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;

--
-- Dumping data for table `pvpratingsjetballs`
--


-- --------------------------------------------------------

--
-- Table structure for table `pvpratingssabotages`
--

CREATE TABLE IF NOT EXISTS `pvpratingssabotages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `guid` bigint(20) NOT NULL,
  `games_played` int(10) unsigned NOT NULL,
  `damage_healed` int(10) unsigned NOT NULL,
  `net_damage` int(10) unsigned NOT NULL,
  `win_loss_ratio` int(10) unsigned NOT NULL,
  `kills` int(10) unsigned NOT NULL,
  `kda_ratio` decimal(5,2) NOT NULL,
  `character_name` varchar(50) NOT NULL,
  `rating` int(10) unsigned NOT NULL,
  `total_pages` int(10) unsigned NOT NULL,
  `elo_leaderboard_id` int(10) unsigned NOT NULL,
  `losses` int(10) unsigned NOT NULL,
  `deaths` int(10) unsigned NOT NULL,
  `wins` int(10) unsigned NOT NULL,
  `revives` int(10) unsigned NOT NULL,
  `ff_id` int(10) unsigned NOT NULL,
  `damage_dealt` int(10) unsigned NOT NULL,
  `assists` int(10) unsigned NOT NULL,
  `ff_created_at` datetime NOT NULL,
  `ff_updated_at` datetime NOT NULL,
  `passes` int(10) unsigned NOT NULL,
  `projectile_hits` int(10) unsigned NOT NULL,
  `executes` int(10) unsigned NOT NULL,
  `damage_taken` int(10) unsigned NOT NULL,
  `points_scored` int(10) unsigned NOT NULL,
  `projectile_misses` int(10) unsigned NOT NULL,
  `rank` int(10) unsigned NOT NULL,
  `added_on` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;

--
-- Dumping data for table `pvpratingssabotages`
--


-- --------------------------------------------------------

--
-- Table structure for table `pvpratingstdms`
--

CREATE TABLE IF NOT EXISTS `pvpratingstdms` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `guid` bigint(20) NOT NULL,
  `games_played` int(10) unsigned NOT NULL,
  `damage_healed` int(10) unsigned NOT NULL,
  `net_damage` int(10) unsigned NOT NULL,
  `win_loss_ratio` int(10) unsigned NOT NULL,
  `kills` int(10) unsigned NOT NULL,
  `kda_ratio` decimal(5,2) NOT NULL,
  `character_name` varchar(50) NOT NULL,
  `rating` int(10) unsigned NOT NULL,
  `total_pages` int(10) unsigned NOT NULL,
  `elo_leaderboard_id` int(10) unsigned NOT NULL,
  `losses` int(10) unsigned NOT NULL,
  `deaths` int(10) unsigned NOT NULL,
  `wins` int(10) unsigned NOT NULL,
  `revives` int(10) unsigned NOT NULL,
  `ff_id` int(10) unsigned NOT NULL,
  `damage_dealt` int(10) unsigned NOT NULL,
  `assists` int(10) unsigned NOT NULL,
  `ff_created_at` datetime NOT NULL,
  `ff_updated_at` datetime NOT NULL,
  `passes` int(10) unsigned NOT NULL,
  `projectile_hits` int(10) unsigned NOT NULL,
  `executes` int(10) unsigned NOT NULL,
  `damage_taken` int(10) unsigned NOT NULL,
  `points_scored` int(10) unsigned NOT NULL,
  `projectile_misses` int(10) unsigned NOT NULL,
  `rank` int(10) unsigned NOT NULL,
  `added_on` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;

--
-- Dumping data for table `pvpratingstdms`
--


-- --------------------------------------------------------

--
-- Table structure for table `recipecertoutputs`
--

CREATE TABLE IF NOT EXISTS `recipecertoutputs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cert_id` int(11) NOT NULL,
  `recipe_id` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `recipecertoutputs_recipe_id_foreign` (`recipe_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;

--
-- Dumping data for table `recipecertoutputs`
--


-- --------------------------------------------------------

--
-- Table structure for table `recipeinputs`
--

CREATE TABLE IF NOT EXISTS `recipeinputs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `material_type` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `required` tinyint(1) NOT NULL,
  `item_type` int(11) NOT NULL,
  `output_index` int(11) NOT NULL,
  `resource_type` int(11) NOT NULL,
  `unlimited` tinyint(1) NOT NULL,
  `stat_name` varchar(50) DEFAULT NULL,
  `attribute_name` varchar(50) DEFAULT NULL,
  `recipe_id` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `recipeinputs_recipe_id_foreign` (`recipe_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;

--
-- Dumping data for table `recipeinputs`
--


-- --------------------------------------------------------

--
-- Table structure for table `recipeoutputs`
--

CREATE TABLE IF NOT EXISTS `recipeoutputs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `itemtypeid` int(11) NOT NULL,
  `subtypeid` int(11) NOT NULL,
  `recipe_id` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `recipeoutputs_recipe_id_foreign` (`recipe_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;

--
-- Dumping data for table `recipeoutputs`
--


-- --------------------------------------------------------

--
-- Table structure for table `reciperequiredcerts`
--

CREATE TABLE IF NOT EXISTS `reciperequiredcerts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cert_id` int(11) NOT NULL,
  `recipe_id` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `reciperequiredcerts_recipe_id_foreign` (`recipe_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;

--
-- Dumping data for table `reciperequiredcerts`
--


-- --------------------------------------------------------

--
-- Table structure for table `recipes`
--

CREATE TABLE IF NOT EXISTS `recipes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `itemtypeid` int(11) NOT NULL,
  `description` varchar(300) NOT NULL,
  `name` varchar(50) NOT NULL,
  `requires_all_certs` tinyint(1) NOT NULL,
  `item_subtype` int(11) NOT NULL,
  `research_type` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;

--
-- Dumping data for table `recipes`
--


-- --------------------------------------------------------

--
-- Table structure for table `resourceitems`
--

CREATE TABLE IF NOT EXISTS `resourceitems` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `itemTypeId` int(11) NOT NULL,
  `craftingTypeId` int(11) DEFAULT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `itemCraftingTypeId` int(11) DEFAULT NULL,
  `level` int(11) DEFAULT NULL,
  `name` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `rarity` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `resource_color` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `subTypeId` int(11) DEFAULT NULL,
  `type` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `version` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=0 ;

--
-- Dumping data for table `resourceitems`
--


-- --------------------------------------------------------

--
-- Table structure for table `resourcetypes`
--

CREATE TABLE IF NOT EXISTS `resourcetypes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type_id` int(11) NOT NULL,
  `name` varchar(55) NOT NULL,
  `parent_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;

--
-- Dumping data for table `resourcetypes`
--


-- --------------------------------------------------------

--
-- Table structure for table `scopemodules`
--

CREATE TABLE IF NOT EXISTS `scopemodules` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `itemTypeId` int(11) NOT NULL,
  `craftingTypeId` int(11) DEFAULT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `itemCraftingTypeId` int(11) DEFAULT NULL,
  `level` int(11) DEFAULT NULL,
  `name` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `rarity` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `subTypeId` int(11) DEFAULT NULL,
  `type` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `version` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=0 ;

--
-- Dumping data for table `scopemodules`
--


-- --------------------------------------------------------

--
-- Table structure for table `searchitems`
--

CREATE TABLE IF NOT EXISTS `searchitems` (
  `id` int(11) NOT NULL,
  `itemTypeId` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `description` text,
  `source` varchar(30) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  FULLTEXT KEY `name` (`name`),
  FULLTEXT KEY `description` (`description`),
  FULLTEXT KEY `name_2` (`name`,`description`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `searchitems`
--


-- --------------------------------------------------------

--
-- Table structure for table `weaponmodules`
--

CREATE TABLE IF NOT EXISTS `weaponmodules` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `itemTypeId` int(11) NOT NULL,
  `abilityId` int(11) DEFAULT NULL,
  `craftingTypeId` int(11) DEFAULT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `itemCraftingTypeId` int(11) DEFAULT NULL,
  `level` int(11) DEFAULT NULL,
  `moduleType` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `name` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `powerLevel` int(11) DEFAULT NULL,
  `rarity` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `subTypeId` int(11) DEFAULT NULL,
  `type` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `uiCategory` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `version` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=0 ;

--
-- Dumping data for table `weaponmodules`
--


-- --------------------------------------------------------

--
-- Table structure for table `weapons`
--

CREATE TABLE IF NOT EXISTS `weapons` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `itemTypeId` int(11) NOT NULL,
  `craftingTypeId` int(11) DEFAULT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `iconId` int(11) DEFAULT NULL,
  `itemCraftingTypeId` int(11) DEFAULT NULL,
  `level` int(11) DEFAULT NULL,
  `name` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `rarity` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `slotIdx` int(11) DEFAULT NULL,
  `subTypeId` int(11) DEFAULT NULL,
  `type` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `weaponType` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `version` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `durability` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=0 ;

--
-- Dumping data for table `weapons`
--


-- --------------------------------------------------------

--
-- Table structure for table `websiteprefs`
--

CREATE TABLE IF NOT EXISTS `websiteprefs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `db_id` bigint(20) NOT NULL,
  `show_loadout` tinyint(1) NOT NULL DEFAULT '1',
  `show_progress` tinyint(1) NOT NULL DEFAULT '1',
  `show_inventory` tinyint(1) NOT NULL DEFAULT '1',
  `show_unlocks` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `show_pve_kills` tinyint(1) NOT NULL DEFAULT '1',
  `show_pve_stats` tinyint(1) NOT NULL DEFAULT '1',
  `show_pve_events` tinyint(1) NOT NULL DEFAULT '1',
  `show_location` tinyint(1) NOT NULL DEFAULT '1',
  `show_workbench` tinyint(1) NOT NULL DEFAULT '0',
  `show_craftables` tinyint(1) NOT NULL DEFAULT '0',
  `show_market_listings` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `db_id` (`db_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=0 ;

--
-- Dumping data for table `websiteprefs`
--


--
-- Constraints for dumped tables
--

--
-- Constraints for table `armymembers`
--
ALTER TABLE `armymembers`
  ADD CONSTRAINT `armymembers_army_id_foreign` FOREIGN KEY (`army_id`) REFERENCES `armies` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `certificationyields`
--
ALTER TABLE `certificationyields`
  ADD CONSTRAINT `certificationyields_certification_id_foreign` FOREIGN KEY (`certification_id`) REFERENCES `certifications` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `hattributes`
--
ALTER TABLE `hattributes`
  ADD CONSTRAINT `hattributes_abilitymodule_id_foreign` FOREIGN KEY (`abilitymodule_id`) REFERENCES `abilitymodules` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `hattributes_chassis_id_foreign` FOREIGN KEY (`chassis_id`) REFERENCES `chassis` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `hattributes_craftingcomponent_id_foreign` FOREIGN KEY (`craftingcomponent_id`) REFERENCES `craftingcomponents` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `hattributes_framemodule_id_foreign` FOREIGN KEY (`framemodule_id`) REFERENCES `framemodules` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `hattributes_weapon_id_foreign` FOREIGN KEY (`weapon_id`) REFERENCES `weapons` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `hbaseconstraints`
--
ALTER TABLE `hbaseconstraints`
  ADD CONSTRAINT `hbaseconstraints_abilitymodule_id_foreign` FOREIGN KEY (`abilitymodule_id`) REFERENCES `abilitymodules` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `hbaseconstraints_backpack_id_foreign` FOREIGN KEY (`backpack_id`) REFERENCES `backpacks` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `hbaseconstraints_chassis_id_foreign` FOREIGN KEY (`chassis_id`) REFERENCES `chassis` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `hbaseconstraints_consumable_id_foreign` FOREIGN KEY (`consumable_id`) REFERENCES `consumables` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `hbaseconstraints_framemodule_id_foreign` FOREIGN KEY (`framemodule_id`) REFERENCES `framemodules` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `hbaseconstraints_powerup_id_foreign` FOREIGN KEY (`powerup_id`) REFERENCES `powerups` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `hbaseconstraints_weaponmodule_id_foreign` FOREIGN KEY (`weaponmodule_id`) REFERENCES `weaponmodules` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `hbaseconstraints_weapon_id_foreign` FOREIGN KEY (`weapon_id`) REFERENCES `weapons` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `hcertifications`
--
ALTER TABLE `hcertifications`
  ADD CONSTRAINT `hcertifications_abilitymodule_id_foreign` FOREIGN KEY (`abilitymodule_id`) REFERENCES `abilitymodules` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `hcertifications_backpack_id_foreign` FOREIGN KEY (`backpack_id`) REFERENCES `backpacks` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `hcertifications_basic_id_foreign` FOREIGN KEY (`basic_id`) REFERENCES `basics` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `hcertifications_blueprint_id_foreign` FOREIGN KEY (`blueprint_id`) REFERENCES `blueprints` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `hcertifications_chassis_id_foreign` FOREIGN KEY (`chassis_id`) REFERENCES `chassis` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `hcertifications_consumable_id_foreign` FOREIGN KEY (`consumable_id`) REFERENCES `consumables` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `hcertifications_craftingcomponent_id_foreign` FOREIGN KEY (`craftingcomponent_id`) REFERENCES `craftingcomponents` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `hcertifications_craftingstation_id_foreign` FOREIGN KEY (`craftingstation_id`) REFERENCES `craftingstations` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `hcertifications_craftingsubcomponent_id_foreign` FOREIGN KEY (`craftingsubcomponent_id`) REFERENCES `craftingsubcomponents` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `hcertifications_firemodule_id_foreign` FOREIGN KEY (`firemodule_id`) REFERENCES `firemodules` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `hcertifications_framemodule_id_foreign` FOREIGN KEY (`framemodule_id`) REFERENCES `framemodules` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `hcertifications_palettemodule_id_foreign` FOREIGN KEY (`palettemodule_id`) REFERENCES `palettemodules` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `hcertifications_powerup_id_foreign` FOREIGN KEY (`powerup_id`) REFERENCES `powerups` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `hcertifications_resourceitem_id_foreign` FOREIGN KEY (`resourceitem_id`) REFERENCES `resourceitems` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `hcertifications_scopemodule_id_foreign` FOREIGN KEY (`scopemodule_id`) REFERENCES `scopemodules` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `hcertifications_weaponmodule_id_foreign` FOREIGN KEY (`weaponmodule_id`) REFERENCES `weaponmodules` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `hcertifications_weapon_id_foreign` FOREIGN KEY (`weapon_id`) REFERENCES `weapons` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `hclasses`
--
ALTER TABLE `hclasses`
  ADD CONSTRAINT `hclasses_abilitymodule_id_foreign` FOREIGN KEY (`abilitymodule_id`) REFERENCES `abilitymodules` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `hclasses_backpack_id_foreign` FOREIGN KEY (`backpack_id`) REFERENCES `backpacks` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `hclasses_basic_id_foreign` FOREIGN KEY (`basic_id`) REFERENCES `basics` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `hclasses_chassis_id_foreign` FOREIGN KEY (`chassis_id`) REFERENCES `chassis` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `hclasses_consumable_id_foreign` FOREIGN KEY (`consumable_id`) REFERENCES `consumables` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `hclasses_firemodule_id_foreign` FOREIGN KEY (`firemodule_id`) REFERENCES `firemodules` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `hclasses_framemodule_id_foreign` FOREIGN KEY (`framemodule_id`) REFERENCES `framemodules` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `hclasses_weapon_id_foreign` FOREIGN KEY (`weapon_id`) REFERENCES `weapons` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `hconstraints`
--
ALTER TABLE `hconstraints`
  ADD CONSTRAINT `hconstraints_abilitymodule_id_foreign` FOREIGN KEY (`abilitymodule_id`) REFERENCES `abilitymodules` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `hconstraints_backpack_id_foreign` FOREIGN KEY (`backpack_id`) REFERENCES `backpacks` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `hconstraints_basic_id_foreign` FOREIGN KEY (`basic_id`) REFERENCES `basics` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `hconstraints_blueprint_id_foreign` FOREIGN KEY (`blueprint_id`) REFERENCES `blueprints` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `hconstraints_chassis_id_foreign` FOREIGN KEY (`chassis_id`) REFERENCES `chassis` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `hconstraints_consumable_id_foreign` FOREIGN KEY (`consumable_id`) REFERENCES `consumables` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `hconstraints_craftingcomponent_id_foreign` FOREIGN KEY (`craftingcomponent_id`) REFERENCES `craftingcomponents` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `hconstraints_craftingstation_id_foreign` FOREIGN KEY (`craftingstation_id`) REFERENCES `craftingstations` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `hconstraints_craftingsubcomponent_id_foreign` FOREIGN KEY (`craftingsubcomponent_id`) REFERENCES `craftingsubcomponents` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `hconstraints_firemodule_id_foreign` FOREIGN KEY (`firemodule_id`) REFERENCES `firemodules` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `hconstraints_framemodule_id_foreign` FOREIGN KEY (`framemodule_id`) REFERENCES `framemodules` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `hconstraints_palettemodule_id_foreign` FOREIGN KEY (`palettemodule_id`) REFERENCES `palettemodules` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `hconstraints_powerup_id_foreign` FOREIGN KEY (`powerup_id`) REFERENCES `powerups` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `hconstraints_resourceitem_id_foreign` FOREIGN KEY (`resourceitem_id`) REFERENCES `resourceitems` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `hconstraints_scopemodule_id_foreign` FOREIGN KEY (`scopemodule_id`) REFERENCES `scopemodules` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `hconstraints_weaponmodule_id_foreign` FOREIGN KEY (`weaponmodule_id`) REFERENCES `weaponmodules` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `hconstraints_weapon_id_foreign` FOREIGN KEY (`weapon_id`) REFERENCES `weapons` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `hflags`
--
ALTER TABLE `hflags`
  ADD CONSTRAINT `hflags_abilitymodule_id_foreign` FOREIGN KEY (`abilitymodule_id`) REFERENCES `abilitymodules` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `hflags_backpack_id_foreign` FOREIGN KEY (`backpack_id`) REFERENCES `backpacks` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `hflags_basic_id_foreign` FOREIGN KEY (`basic_id`) REFERENCES `basics` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `hflags_blueprint_id_foreign` FOREIGN KEY (`blueprint_id`) REFERENCES `blueprints` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `hflags_chassis_id_foreign` FOREIGN KEY (`chassis_id`) REFERENCES `chassis` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `hflags_consumable_id_foreign` FOREIGN KEY (`consumable_id`) REFERENCES `consumables` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `hflags_craftingcomponent_id_foreign` FOREIGN KEY (`craftingcomponent_id`) REFERENCES `craftingcomponents` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `hflags_craftingstation_id_foreign` FOREIGN KEY (`craftingstation_id`) REFERENCES `craftingstations` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `hflags_craftingsubcomponent_id_foreign` FOREIGN KEY (`craftingsubcomponent_id`) REFERENCES `craftingsubcomponents` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `hflags_firemodule_id_foreign` FOREIGN KEY (`firemodule_id`) REFERENCES `firemodules` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `hflags_framemodule_id_foreign` FOREIGN KEY (`framemodule_id`) REFERENCES `framemodules` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `hflags_palettemodule_id_foreign` FOREIGN KEY (`palettemodule_id`) REFERENCES `palettemodules` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `hflags_powerup_id_foreign` FOREIGN KEY (`powerup_id`) REFERENCES `powerups` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `hflags_resourceitem_id_foreign` FOREIGN KEY (`resourceitem_id`) REFERENCES `resourceitems` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `hflags_scopemodule_id_foreign` FOREIGN KEY (`scopemodule_id`) REFERENCES `scopemodules` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `hflags_weaponmodule_id_foreign` FOREIGN KEY (`weaponmodule_id`) REFERENCES `weaponmodules` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `hflags_weapon_id_foreign` FOREIGN KEY (`weapon_id`) REFERENCES `weapons` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `hstats`
--
ALTER TABLE `hstats`
  ADD CONSTRAINT `hstats_weapon_id_foreign` FOREIGN KEY (`weapon_id`) REFERENCES `weapons` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `htiers`
--
ALTER TABLE `htiers`
  ADD CONSTRAINT `htiers_abilitymodule_id_foreign` FOREIGN KEY (`abilitymodule_id`) REFERENCES `abilitymodules` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `htiers_basic_id_foreign` FOREIGN KEY (`basic_id`) REFERENCES `basics` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `htiers_blueprint_id_foreign` FOREIGN KEY (`blueprint_id`) REFERENCES `blueprints` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `htiers_chassis_id_foreign` FOREIGN KEY (`chassis_id`) REFERENCES `chassis` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `htiers_craftingcomponent_id_foreign` FOREIGN KEY (`craftingcomponent_id`) REFERENCES `craftingcomponents` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `htiers_craftingsubcomponent_id_foreign` FOREIGN KEY (`craftingsubcomponent_id`) REFERENCES `craftingsubcomponents` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `htiers_framemodule_id_foreign` FOREIGN KEY (`framemodule_id`) REFERENCES `framemodules` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `htiers_weapon_id_foreign` FOREIGN KEY (`weapon_id`) REFERENCES `weapons` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `hvisuals`
--
ALTER TABLE `hvisuals`
  ADD CONSTRAINT `hvisuals_chassis_id_foreign` FOREIGN KEY (`chassis_id`) REFERENCES `chassis` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `hwebicons`
--
ALTER TABLE `hwebicons`
  ADD CONSTRAINT `hwebicons_abilitymodule_id_foreign` FOREIGN KEY (`abilitymodule_id`) REFERENCES `abilitymodules` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `hwebicons_achievement_id_foreign` FOREIGN KEY (`achievement_id`) REFERENCES `achievements` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `hwebicons_backpack_id_foreign` FOREIGN KEY (`backpack_id`) REFERENCES `backpacks` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `hwebicons_basic_id_foreign` FOREIGN KEY (`basic_id`) REFERENCES `basics` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `hwebicons_blueprint_id_foreign` FOREIGN KEY (`blueprint_id`) REFERENCES `blueprints` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `hwebicons_chassis_id_foreign` FOREIGN KEY (`chassis_id`) REFERENCES `chassis` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `hwebicons_consumable_id_foreign` FOREIGN KEY (`consumable_id`) REFERENCES `consumables` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `hwebicons_craftingcomponent_id_foreign` FOREIGN KEY (`craftingcomponent_id`) REFERENCES `craftingcomponents` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `hwebicons_craftingstation_id_foreign` FOREIGN KEY (`craftingstation_id`) REFERENCES `craftingstations` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `hwebicons_craftingsubcomponent_id_foreign` FOREIGN KEY (`craftingsubcomponent_id`) REFERENCES `craftingsubcomponents` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `hwebicons_firemodule_id_foreign` FOREIGN KEY (`firemodule_id`) REFERENCES `firemodules` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `hwebicons_framemodule_id_foreign` FOREIGN KEY (`framemodule_id`) REFERENCES `framemodules` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `hwebicons_palettemodule_id_foreign` FOREIGN KEY (`palettemodule_id`) REFERENCES `palettemodules` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `hwebicons_powerup_id_foreign` FOREIGN KEY (`powerup_id`) REFERENCES `powerups` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `hwebicons_resourceitem_id_foreign` FOREIGN KEY (`resourceitem_id`) REFERENCES `resourceitems` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `hwebicons_scopemodule_id_foreign` FOREIGN KEY (`scopemodule_id`) REFERENCES `scopemodules` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `hwebicons_weaponmodule_id_foreign` FOREIGN KEY (`weaponmodule_id`) REFERENCES `weaponmodules` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `hwebicons_weapon_id_foreign` FOREIGN KEY (`weapon_id`) REFERENCES `weapons` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `marketcategories`
--
ALTER TABLE `marketcategories`
  ADD CONSTRAINT `marketcategories_marketlisting_id_foreign` FOREIGN KEY (`marketlisting_id`) REFERENCES `marketlistings` (`ff_id`) ON UPDATE CASCADE;

--
-- Constraints for table `marketstatabilitymodules`
--
ALTER TABLE `marketstatabilitymodules`
  ADD CONSTRAINT `marketstatabilitymodules_marketlisting_id_foreign` FOREIGN KEY (`marketlisting_id`) REFERENCES `marketlistings` (`ff_id`) ON UPDATE CASCADE;

--
-- Constraints for table `marketstatcraftingcomponents`
--
ALTER TABLE `marketstatcraftingcomponents`
  ADD CONSTRAINT `marketstatcraftingcomponents_marketlisting_id_foreign` FOREIGN KEY (`marketlisting_id`) REFERENCES `marketlistings` (`ff_id`) ON UPDATE CASCADE;

--
-- Constraints for table `marketstatjumpjets`
--
ALTER TABLE `marketstatjumpjets`
  ADD CONSTRAINT `marketstatjumpjets_marketlisting_id_foreign` FOREIGN KEY (`marketlisting_id`) REFERENCES `marketlistings` (`ff_id`) ON UPDATE CASCADE;

--
-- Constraints for table `marketstatplatings`
--
ALTER TABLE `marketstatplatings`
  ADD CONSTRAINT `marketstatplatings_marketlisting_id_foreign` FOREIGN KEY (`marketlisting_id`) REFERENCES `marketlistings` (`ff_id`) ON UPDATE CASCADE;

--
-- Constraints for table `marketstatresources`
--
ALTER TABLE `marketstatresources`
  ADD CONSTRAINT `marketstatresources_marketlisting_id_foreign` FOREIGN KEY (`marketlisting_id`) REFERENCES `marketlistings` (`ff_id`) ON UPDATE CASCADE;

--
-- Constraints for table `marketstatservos`
--
ALTER TABLE `marketstatservos`
  ADD CONSTRAINT `marketstatservos_marketlisting_id_foreign` FOREIGN KEY (`marketlisting_id`) REFERENCES `marketlistings` (`ff_id`) ON UPDATE CASCADE;

--
-- Constraints for table `marketstatweapons`
--
ALTER TABLE `marketstatweapons`
  ADD CONSTRAINT `marketstatweapons_marketlisting_id_foreign` FOREIGN KEY (`marketlisting_id`) REFERENCES `marketlistings` (`ff_id`) ON UPDATE CASCADE;

--
-- Constraints for table `recipecertoutputs`
--
ALTER TABLE `recipecertoutputs`
  ADD CONSTRAINT `recipecertoutputs_recipe_id_foreign` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `recipeinputs`
--
ALTER TABLE `recipeinputs`
  ADD CONSTRAINT `recipeinputs_recipe_id_foreign` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `recipeoutputs`
--
ALTER TABLE `recipeoutputs`
  ADD CONSTRAINT `recipeoutputs_recipe_id_foreign` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `reciperequiredcerts`
--
ALTER TABLE `reciperequiredcerts`
  ADD CONSTRAINT `reciperequiredcerts_recipe_id_foreign` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`) ON UPDATE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
