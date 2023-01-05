<?php
return [

    // the primary deals table
    "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_crm_deals` (
      `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
      `title` varchar(255) NOT NULL DEFAULT '',
      `stage_id` int(2) unsigned NOT NULL,
      `contact_id` bigint(20) unsigned DEFAULT NULL,
      `company_id` bigint(20) unsigned DEFAULT NULL,
      `created_by` bigint(20) unsigned NOT NULL,
      `owner_id` bigint(20) unsigned NOT NULL,
      `value` decimal(20,2) unsigned DEFAULT NULL,
      `currency` varchar(5) NOT NULL DEFAULT '',
      `expected_close_date` datetime DEFAULT NULL,
      `won_at` datetime DEFAULT NULL,
      `lost_at` datetime DEFAULT NULL,
      `lost_reason_id` int(11) unsigned DEFAULT NULL,
      `lost_reason` varchar(255) DEFAULT NULL,
      `lost_reason_comment` varchar(255) DEFAULT NULL,
      `created_at` datetime NOT NULL,
      `updated_at` datetime NOT NULL,
      `deleted_at` datetime DEFAULT NULL,
      PRIMARY KEY (`id`)
    ) $collate;",

    // table for activities
    "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_crm_deals_activities` (
      `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
      `type` varchar(20) NOT NULL DEFAULT '',
      `title` varchar(255) NOT NULL DEFAULT '',
      `deal_id` bigint(20) unsigned NOT NULL,
      `contact_id` bigint(20) unsigned DEFAULT NULL,
      `company_id` bigint(20) unsigned DEFAULT NULL,
      `created_by` bigint(20) unsigned NOT NULL,
      `assigned_to_id` bigint(20) unsigned NOT NULL,
      `start` datetime NOT NULL,
      `end` datetime DEFAULT NULL,
      `is_start_time_set` tinyint(1) NOT NULL DEFAULT '0',
      `note` text,
      `done_at` datetime DEFAULT NULL,
      `done_by` bigint(20) unsigned DEFAULT NULL,
      `created_at` datetime NOT NULL,
      `updated_at` datetime DEFAULT NULL,
      `deleted_at` datetime DEFAULT NULL,
      PRIMARY KEY (`id`)
    ) $collate;",

    // settings - activity types
    "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_crm_deals_activity_types` (
      `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
      `title` varchar(255) NOT NULL DEFAULT '',
      `icon` varchar(255) NOT NULL DEFAULT '',
      `order` int(11) unsigned NOT NULL,
      `deleted_at` datetime DEFAULT NULL,
      PRIMARY KEY (`id`)
    ) $collate;",

    // deals -> additional agents
    "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_crm_deals_agents` (
      `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
      `deal_id` bigint(20) NOT NULL,
      `agent_id` bigint(20) NOT NULL,
      `added_by` bigint(20) NOT NULL,
      `created_at` datetime NOT NULL,
      `updated_at` datetime NOT NULL,
      PRIMARY KEY (`id`)
    ) $collate;",

    // deals -> attachments
    "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_crm_deals_attachments` (
      `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
      `deal_id` bigint(20) unsigned NOT NULL,
      `attachment_id` bigint(20) unsigned NOT NULL,
      `added_by` bigint(20) unsigned NOT NULL,
      `created_at` datetime NOT NULL,
      `updated_at` datetime NOT NULL,
      PRIMARY KEY (`id`)
    ) $collate;",

    // deals -> competitors
    "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_crm_deals_competitors` (
      `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
      `deal_id` bigint(20) unsigned NOT NULL,
      `competitor_name` varchar(255) NOT NULL DEFAULT '',
      `website` text,
      `strengths` varchar(255) DEFAULT NULL,
      `weaknesses` varchar(255) DEFAULT NULL,
      `created_by` bigint(20) unsigned NOT NULL,
      `created_at` datetime NOT NULL,
      `updated_at` datetime NOT NULL,
      PRIMARY KEY (`id`)
    ) $collate;",

    // emails
    "CREATE TABLE `{$wpdb->prefix}erp_crm_deals_emails` (
      `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
      `deal_id` bigint(20) unsigned NOT NULL,
      `cust_act_id` bigint(20) unsigned NOT NULL,
      `hash` varchar(40) DEFAULT NULL,
      `parent_id` bigint(20) DEFAULT NULL,
      PRIMARY KEY (`id`)
    ) $collate",

    // settings -> lost reasons
    "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_crm_deals_lost_reasons` (
      `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
      `reason` varchar(255) NOT NULL DEFAULT '',
      PRIMARY KEY (`id`)
    ) $collate;",

    // deals -> notes
    "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_crm_deals_notes` (
      `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT,
      `deal_id` bigint(20) unsigned NOT NULL,
      `note` text NOT NULL,
      `is_sticky` tinyint(1) DEFAULT '0',
      `created_by` bigint(20) unsigned NOT NULL,
      `created_at` datetime NOT NULL,
      `updated_at` datetime NOT NULL,
      PRIMARY KEY (`id`)
    ) $collate;",

    // deals -> participants
    "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_crm_deals_participants` (
      `id` bigint(11) unsigned NOT NULL AUTO_INCREMENT,
      `deal_id` bigint(20) NOT NULL,
      `people_id` bigint(20) NOT NULL,
      `people_type` varchar(10) NOT NULL DEFAULT '',
      `added_by` bigint(20) NOT NULL,
      `created_at` datetime NOT NULL,
      `updated_at` datetime NOT NULL,
      PRIMARY KEY (`id`)
    ) $collate;",

    // deals -> stages
    "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_crm_deals_pipeline_stages` (
      `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
      `title` varchar(255) NOT NULL DEFAULT '',
      `pipeline_id` int(11) unsigned NOT NULL,
      `probability` decimal(5,2) DEFAULT '0.00',
      `is_rotting_on` tinyint(1) unsigned DEFAULT '0',
      `rotting_after` int(3) unsigned DEFAULT '0',
      `life_stage` varchar(255) DEFAULT NULL,
      `order` int(2) unsigned NOT NULL,
      PRIMARY KEY (`id`)
    ) $collate;",

    // deals -> pipelines
    "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_crm_deals_pipelines` (
      `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
      `title` varchar(255) NOT NULL DEFAULT '',
      PRIMARY KEY (`id`)
    ) $collate;",

    // deals -> stage history
    "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}erp_crm_deals_stage_history` (
      `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
      `deal_id` bigint(20) unsigned NOT NULL,
      `stage_id` bigint(20) unsigned NOT NULL,
      `in` datetime NOT NULL,
      `out` datetime DEFAULT NULL,
      `in_amount` decimal(20,2) DEFAULT NULL,
      `expected_close_date` datetime DEFAULT NULL,
      `modified_by` bigint(20) NOT NULL,
      PRIMARY KEY (`id`)
    ) $collate;",


];
