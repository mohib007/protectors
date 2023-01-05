<?php
global $wpdb;

// pipelines
$pipelinesCount = $wpdb->get_var( "SELECT COUNT(*) FROM `{$wpdb->prefix}erp_crm_deals_pipelines`" );

if ( !$pipelinesCount ) {
    $wpdb->query( 'TRUNCATE TABLE ' . $wpdb->prefix . 'erp_crm_deals_pipelines' );
    $sql = "INSERT INTO `{$wpdb->prefix}erp_crm_deals_pipelines` (`id`, `title`)
            VALUES
                (1, 'Pipeline');";

    $wpdb->query( $sql );
}

// pipelines
$stagesCount = $wpdb->get_var( "SELECT COUNT(*) FROM `{$wpdb->prefix}erp_crm_deals_pipeline_stages`" );

if ( !$stagesCount ) {
    $wpdb->query( 'TRUNCATE TABLE ' . $wpdb->prefix . 'erp_crm_deals_pipeline_stages' );
    $sql = "INSERT INTO `{$wpdb->prefix}erp_crm_deals_pipeline_stages` (`id`, `title`, `pipeline_id`, `probability`, `is_rotting_on`, `rotting_after`, `life_stage`, `order`)
            VALUES
                (1,'Lead In',1,100.00,1,1,'lead',1),
                (2,'Contact Made',1,100.00,0,0,'opportunity',2),
                (3,'Demo Scheduled',1,100.00,1,3,'0',3),
                (4,'Proposal Made',1,100.00,0,0,'0',0),
                (5,'Negotiations Started',1,100.00,0,0,'0',4);";

    $wpdb->query( $sql );
}

// activity types
$actTypesCount = $wpdb->get_var( "SELECT COUNT(*) FROM `{$wpdb->prefix}erp_crm_deals_activity_types`" );

if ( !$actTypesCount ) {
    $wpdb->query( 'TRUNCATE TABLE ' . $wpdb->prefix . 'erp_crm_deals_activity_types' );
    $sql = "INSERT INTO `{$wpdb->prefix}erp_crm_deals_activity_types` (`id`, `title`, `icon`, `order`, `deleted_at`)
            VALUES
                (1,'Call','ac-call',0,NULL),
                (2,'Meeting','ac-meeting',1,NULL),
                (3,'Task','ac-task',2,NULL),
                (4,'Deadline','deadline',3,NULL),
                (6,'Email','mail',4,NULL),
                (13,'Lunch','ac-lunch',5,NULL);";

    $wpdb->query( $sql );
}
