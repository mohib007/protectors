<?php
namespace IMPAKTT_Customization_Protectors\ERP\CRM\Deals;

use IMPAKTT_Customization_Protectors\ERP\CRM\Deals\Deals;
use IMPAKTT_Customization_Protectors\ERP\CRM\Deals\Helpers;
use WeDevs\ERP\Framework\Traits\Ajax;
use WeDevs\ERP\Framework\Traits\Hooker;

/**
 * Ajax action hooks
 *
 * @since 1.0.0
 */
class IMPAKTT_Customization_Protectors_Ajax {

    use Hooker;
    use Ajax;

    /**
     * The class constructor
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function __construct() {
        $this->action( 'wp_ajax_get_reports_customized', 'get_reports_customized' );
        $this->action( 'wp_ajax_estimateDealDetail', 'get_estimate_deal_detail' );
        $this->action( 'wp_ajax_get_receivables_modified', 'get_receivables_modified' );
    }

    public function get_estimate_deal_detail()
    {
        global $wpdb, $woocommerce;
        $order_id = $_POST['orderId'];
        $contactId = $_POST['contact_id'];
        if($order_id != 0)
            $order = wc_get_order( $order_id );

        $response = array();
        if($order_id != 0){
            $response['fitting_address'] = $order->get_formatted_shipping_address();
            if(empty($response['fitting_address'])){
                $response['fitting_address'] = $order->get_formatted_billing_address();
                if(empty($response['fitting_address'])){
                    $response['fitting_address'] = "No details found.";
                }
            }

            $response['fitting_address'] = str_replace("<br/>",",",$response['fitting_address']);

            if (is_array($order->get_items())){
                // Loop through order items
                foreach ( $order->get_items() as $item ) {
                    $products_names[]  = $item->get_name(); // Store in an array
                }
                $response['products'] = implode(',', $products_names);
            }else{
                $response['products'] = "No items found.";
            }
            $field_id = '_wc_acof_' . get_option('erp_accounting_invoice_collect_id');
            $response['field_cordinator'] = get_post_meta($order_id,$field_id,true);
            if(empty($response['field_cordinator'])){
                $response['field_cordinator'] = "No details found.";
            }
        }

        $customer_user_id = erp_get_people( $contactId );
        $customer_user_id = $customer_user_id->user_id;

        $response['salesZone'] = get_user_meta($customer_user_id, 'sales_zone', true);
        $response['branch'] = get_user_meta($customer_user_id, 'branch', true);

        echo json_encode($response);
        exit;
    }

    public function get_reports_customized(){
		global $wpdb;

        $columnIndex = $_POST['order'][0]['column']; // Column index
        $columnName = $_POST['columns'][$columnIndex]['data']; // Column name
        $columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
        $searchValue = $_POST['search']['value']; // Search value
		$field_coordinator = $_POST['fieldCordinator']; 
        ## Search
        $searchQuery = " ";
        if ($searchValue != '') {
            $searchQuery .= " AND (ep.`display_name` like '%" . $searchValue . "%' or 
		        um.`meta_value` like '%" . $searchValue . "%' ) ";
        }

        $user = wp_get_current_user();


        $allowed_roles = array('editor', 'administrator', 'author', 'erp_crm_manager', 'pos_manager','factoryinventorymanager');
        $other_roles = array('salemanager');
        $sql2 = "";
        if (array_intersect($allowed_roles, $user->roles)) {
            $sql2 .= " ";
        } elseif (array_intersect($other_roles, $user->roles)) {
            $user_id = get_current_user_id();
            $user_city = get_user_meta($user_id, 'sales_zone', true);
            if (!empty($user_city)) {
                $sql2 .= " AND um.`meta_value` = '" . $user_city . "'";
            }
        } else {
            $user_id = get_current_user_id();
            $sql2 .= " AND cd.`owner_id` = '" . $user_id . "'";
        }

        $sql = "SELECT ep.`display_name` AS office_coordinator,
				cd.`owner_id` AS owner_id,
				cd.created_by as created_by,
				cd.expected_close_date,
				um.`meta_value` AS sales_zone,
				wep.`company` AS client_name,
				weps.`first_name` AS first_name,
				weps.`last_name` AS last_name,
				weps.`user_id` AS client_personal_user_id,
				wep.`user_id` AS client_user_id,
				cd.`company_id` AS company_id,
				cd.`contact_id` AS contact_id,
				cd.created_at AS deal_date,
				cd.`id` AS deal_id,
				cd.`title` AS deal_title,";
				
				if($_POST['selected_value'] == 'yes'){		
					$sql .= "pm.meta_value AS order_no,";
				}

				$sql .= "ps.`title` AS deal_status,
				'' AS remarks

				FROM `{$wpdb->prefix}erp_crm_deals` AS cd 

				LEFT JOIN `{$wpdb->prefix}users` AS ep ON ep.`id` = cd.`owner_id` 



				LEFT JOIN `{$wpdb->prefix}erp_crm_deals_participants` AS ecp ON ecp.`people_id` = cd.`contact_id` 

				LEFT JOIN `{$wpdb->prefix}erp_crm_deals_pipeline_stages` AS ps ON ps.`id` = cd.`stage_id` 
				    

				LEFT JOIN `{$wpdb->prefix}erp_peoples` AS wep ON wep.`id`= cd.`company_id` 

				LEFT JOIN `{$wpdb->prefix}erp_peoples` AS weps ON weps.`id`= cd.`contact_id` 
			
				LEFT JOIN `{$wpdb->prefix}usermeta` AS um ON um.`user_id` = ep.`id` AND um.`meta_key` = 'sales_zone'";
				
				if($_POST['selected_value'] == 'yes'){				
					$field_coordinator_id = '_wc_acof_'. get_option('erp_accounting_invoice_collect_id');
					$sql .= "LEFT JOIN `{$wpdb->prefix}postmeta` AS pm ON pm.`post_id` = cd.`id` AND pm.`meta_key` = '_erp_deal_order' 
							LEFT JOIN `{$wpdb->prefix}posts` AS wp ON wp.`ID` = pm.`meta_value` 
							INNER JOIN `{$wpdb->prefix}postmeta` AS cordinators ON cordinators.post_id = pm.meta_value AND cordinators.meta_key = '{$field_coordinator_id}' AND cordinators.meta_value = '{$field_coordinator}'";

					$sql .= " WHERE cd.`deleted_at` IS NULL AND cd.`won_at` IS NULL AND cd.`lost_at` IS NULL AND (wp.post_status != 'wc-cancelled' OR wp.ID IS NULL) ";					
				}else{
					$sql .= " WHERE cd.`owner_id` = '{$field_coordinator}' AND cd.`deleted_at` IS NULL AND cd.`won_at` IS NULL AND cd.`lost_at` IS NULL ";					
				}
				
				
				/*
				Commented query
								wp.`post_status` AS estimate_status,
								wp.`post_modified` AS estimate_updated,
								GROUP_CONCAT(DISTINCT compete.competitor_name separator ',') AS competitor,
								GROUP_CONCAT(DISTINCT deal_note.note separator ',') AS deals_notes,



								LEFT JOIN `{$wpdb->prefix}erp_crm_deals_notes` AS deal_note ON deal_note.`deal_id` = cd.`id` 				    

				*/


        $role_array = array("erp_crm_manager", "administrator", "author", "editor");
        $other_roles = array('salemanager');

        $sql3 = '';
        if (!empty(array_intersect($user->roles, $role_array))) {
            $sql3 .= '';
        } elseif (!empty(array_intersect($user->roles, $other_roles))) {
            $user_id = get_current_user_id();
            $user_city = get_user_meta($user_id, 'sales_zone', true);
            if (!empty($user_city)) {
                $sql3 .= " AND um.`meta_value` = '" . $user_city . "'";
            } else {
                $sql3 .= '';
            }
        } else {
            $sql3 .= ' AND cd.`owner_id`=' . get_current_user_id() . ' ';
        }
        
        $sql .= $sql2;
        $sql .= $sql3;
        $sql .= $searchQuery;

        //$sql .= " order by " . $columnName . " " . $columnSortOrder; 
        //$sql .= "GROUP BY deal_id ";
        $sql .= "Order by deal_id DESC";

        $res = $wpdb->get_results($sql, ARRAY_A); 

		foreach($res as $key => $value){
			if($value["order_no"] && !empty($value["order_no"]) && !is_null($value["order_no"])){
				if (strpos($value["order_no"], ',') !== false) {
    				$tempRes = explode(",", $value["order_no"]);
    				unset($res[$key]);
    				foreach($tempRes as $estimate){
    						$value["order_no"] = $estimate;
    						$uRes[] = $value;
    				}
				}else{
				    $uRes[] = $value;
				}
			}else{
				$uRes[] = $value;
			}
		}

		$res = $uRes;

		$args = array_filter($res, function($v) { return !empty($v['order_no']); });

		$estimates_id = implode(',', array_map(function ($entry) {
								return $entry['order_no'];
						}, $args));

		if(isset($estimates_id) && !empty($estimates_id)){
				$sql = "SELECT wp.`order_id` AS order_no,";
				
				$sql .= "GROUP_CONCAT(DISTINCT wp.order_item_name) AS product_name,
						
						order_tot.`meta_value` AS order_amount,
						
						shipa.meta_value AS shipping_address,
						
						pagi1.`meta_value` AS billing_company
						
						FROM `{$wpdb->prefix}woocommerce_order_items` AS wp
										
						LEFT JOIN `{$wpdb->prefix}postmeta` AS pagi1 ON pagi1.`post_id` = wp.`order_id` AND pagi1.`meta_key` = '_billing_company' 
						
						LEFT JOIN `{$wpdb->prefix}postmeta` AS shipa ON shipa.`post_id` = wp.`order_id` AND shipa.`meta_key` = '_shipping_address_1' 
									
						LEFT JOIN `{$wpdb->prefix}postmeta` AS order_tot ON order_tot.`post_id` = wp.`order_id` AND order_tot.`meta_key` = '_order_total'";

		/*
		comment query

						GROUP_CONCAT(notes.comment_content) AS order_notes	
		*/
		//		$sql .= " AND wp.`post_status` <> 'wc-cancelled'";
				$sql .= " WHERE wp.`order_id` IN (" . $estimates_id . ")";
				$sql .= " GROUP BY order_no";
				$sql .= " order by order_no DESC"; 

				$res_array = $wpdb->get_results($sql, ARRAY_A);
		}
		
		foreach($args as $key => $value){
			foreach($res_array as $value2){
				if($value['order_no'] === $value2['order_no']){
					foreach($value2 as $key2 => $allValues){
						$args[$key][$key2] = $allValues;
					}
					continue;
				}    				
			}
		}		

		foreach($res as $countKey => $value){
			if(array_key_exists($countKey,$args)){
				$res[$countKey] = $args[$countKey];
			}
		}

        $data = array();

        foreach ($res as $key => $row) {


            if (empty($row['shipping_address']) || trim($row['shipping_address']) == " " || trim($row['shipping_address']) == "" || $row['shipping_address'] == null) {
                $billingAddressGet = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}postmeta` WHERE meta_key like '_billing_%' AND `post_id` =  {$row['order_no']}", ARRAY_A);
                $keyArray = array("_billing_address_2", "_billing_city", "_billing_state", "_billing_country");
                foreach ($billingAddressGet  as $billingAddressRow){
                    if($billingAddressRow['meta_key'] == '_billing_address_1'){
                        $row['shipping_address'] = $billingAddressRow['meta_value'];
                    }
                    elseif(in_array($billingAddressRow['meta_key'], $keyArray)){
                        $row['shipping_address'] .= ',' . $billingAddressRow['meta_value'];
                    }
                }

            }else{
                $row['shipping_address'] = $row['shipping_address'];

                $keyArray = array("_shipping_address_2", "_shipping_city", "_shipping_state", "_shipping_country");
                $ShipAddressGet = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}postmeta` WHERE meta_key like '_shipping_%' AND `post_id` =  {$row['order_no']}", ARRAY_A);
                foreach ($ShipAddressGet  as $ShippingAddressRow){
                    if(in_array($ShippingAddressRow['meta_key'], $keyArray)){
                        $row['shipping_address'] .= ',' . $ShippingAddressRow['meta_value'];
                    }
                }
            }

          /*
            //Notes or Comments
            $Notes = $wpdb->get_row("SELECT GROUP_CONCAT(comment_content) AS order_notes FROM `{$wpdb->prefix}comments` WHERE comment_post_ID =  {$row['order_no']} AND comment_author != 'WooCommerce' AND comment_type = 'order_note' ", ARRAY_A);

            if(!empty($Notes))
                $row['order_notes'] = $Notes['order_notes'];

            if(!empty($row['deals_notes']))
                $row['deals_notes'] =  trim(strip_tags($row['deals_notes']));
            */
            
            if (empty($row['shipping_address']) || trim($row['shipping_address']) == "" || $row['shipping_address'] == null) {
                $order_numbers = $row['order_no'];
                $order_numbers = str_replace(", ", ",", $order_numbers);

                $temp_orders = explode(",", $order_numbers);
                $tempShipping = "";
                foreach ($temp_orders as $key => $temp_order) {
                    $shipping_name = get_post_meta($temp_order, '_shipping_address_1', true);
                    if (!empty($shipping_name)) {
                        $tempShipping .= $tempShipping . ", " . $shipping_name;
                    }
                }
                if (!empty($tempShipping)) {
                    //  $tempShipping = substr($tempShipping, 0, -1);
                    $tempShipping = substr($tempShipping, 1);
                }
            }

            if ($row['order_amount'] == "0.00") {
                $order_numbers = $row['order_no'];
                $order_numbers = str_replace(", ", ",", $order_numbers);

                $temp_orders = explode(",", $order_numbers);
                $tempAmount = 0.00;
                foreach ($temp_orders as $key => $temp_order) {
                    $order_amount = get_post_meta($temp_order, '_order_total', true);
                    $tempAmount = $tempAmount + $order_amount;
                }
            }

            $client_name = (string)$row['first_name'].' '.$row['last_name'];
            if ( strlen($row['client_name']) != 0 || $row['client_name'] != NULL || $row['client_name'] != " "){
                $client_name = (string)$row['client_name'];
            }

            if ( !empty($row['client_user_id'] && $row['client_user_id'] != 0 && $row['client_user_id'] != null) ){
                $id_for_sale_zone = $row['client_user_id'];
            } else {
                $id_for_sale_zone = $row['client_personal_user_id'];
            }
            
            $sfirstname = get_post_meta( $row['order_no'], '_shipping_first_name', true );
            $slastname = get_post_meta( $row['order_no'], '_shipping_last_name', true );
            $bfirstname = get_post_meta( $row['order_no'], '_billing_first_name', true );
            $blastname = get_post_meta( $row['order_no'], '_billing_last_name', true );

            $data[] = array(
                "office_coordinator"    => $row['office_coordinator'],
                "sales_zone"            => (get_user_meta($id_for_sale_zone, 'sales_zone', true) != false)? get_user_meta($id_for_sale_zone, 'sales_zone', true) : '-',
                "company_name"          => $row['billing_company'],
                "billing_name"          => (strlen($bfirstname) != 0 || strlen($blastname) != 0) ?$bfirstname . ' ' . $blastname : $row['first_name'] . ' ' . $row['last_name'],
                "client_company"        => strlen($row['client_name']) != 0 ? $row['client_name'] . ' (#' . $row['company_id'] . ')' : '',
                "client_name"           => (strlen($row['first_name']) != 0 || strlen($row['last_name']) != 0) ? $row['first_name'] . ' ' . $row['last_name'] . ' (#' . $row['contact_id'] . ')' : '(#' . $row['contact_id'] . ')',
                //"client_name"           => strlen($row['client_name']) != 0?$row['client_name']:$row['first_name'].' '.$row['last_name'],
                //"deal_date"             => $row['deal_date'],
                //"expected_close_date"   => $row['expected_close_date'],
                //"order_date"            => $row['deal_date'],
                "deal_id"               => $row['deal_id'],
                "order_no"              => $row['order_no'],
                //"estimate_updated"      => $row['estimate_updated'],
                //"deal_title"            => $row['deal_title'],
                "product_name"          => $row['product_name'],
                "order_amount"          => (empty($row['order_amount']) || $row['order_amount'] == "0.00") ? $tempAmount : $row['order_amount'],
                //"deal_status"           => $row['deal_status'],
                "shipping_address"      => (empty($row['shipping_address']) || $row['shipping_address'] == null) ? $tempShipping : $row['shipping_address'],
                "remarks"               => $row['remarks'],
                //"competitor_name"       => $row['competitor'],              
                //"order_notes"       	=> $row['order_notes'],              
                //"deals_notes"       	=> $row['deals_notes'],
                //"estimate_status"       => wc_get_order_status_name($row['estimate_status']),
            );
        }

        $response = array(
            "draw"                  => count($res),//intval($draw),
            "iTotalRecords"         => count($res),
            "iTotalDisplayRecords"  => count($res),
            //"iDisplayLength"      => $totalRecordwithFilter,
            "aaData"                => $data
        );

        echo json_encode($response);
        exit;
    }

    /**
     * Recieveable Report Modefied
     *
     * @July 2nd 2021
     * Ticket Request 39157
     * @return void
     */
    public function get_receivables_modified()
    {
        global $wpdb;
        $res = '';

        $draw = $_POST['draw'];
        $row = $_POST['start'];
        $rowperpage = $_POST['length']; // Rows display per page
        $columnIndex = $_POST['order'][0]['column']; // Column index

        $columnName = $_POST['columns'][$columnIndex]['data']; // Column name
        $columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
        if(!isset($columnName) || empty($columnName))
            $columnName = 'estimate_no';

        if(!isset($columnSortOrder) || empty($columnSortOrder))
            $columnSortOrder = 'desc';

        $searchValue = $_POST['search']['value']; // Search value

        ## Search
        $searchQuery = "";
        if ($searchValue != '') {
            $searchQuery = " AND (ep.`display_name` like '%" . $searchValue . "%' or 
                        um.`meta_value` like '%" . $searchValue . "%' ) ";
        }

        $user = wp_get_current_user();
        $field_coordinator = $_POST['fieldCordinator']; 
		$field_coordinator_id = '_wc_acof_'. get_option('erp_accounting_invoice_collect_id');

        $sql = "SELECT 
                trans.order_id AS estimate_no,
                trans.`created_at` AS inv_date,
                trans.`invoice_number`,
                trans.`invoice_format`,                
                trans.`id` AS `invoice_id`,                
                trans.`user_id` AS contact_code,      
                trans.`created_by` AS created_by,
                trans.`total` AS invoice_amount,
                trans.`due` AS balance_amount,
			    trans.status,
                trans.`summary` AS inv_memo,
                ' ' AS remarks";

        $sql .= " FROM 
                    `{$wpdb->prefix}erp_ac_transactions` AS trans
				INNER JOIN `{$wpdb->prefix}postmeta` ON post_id = trans.order_id AND meta_key = '{$field_coordinator_id}' AND meta_value = '{$field_coordinator}'";
					
					
        $where = " WHERE trans.due > 0 AND trans.status IN ('partial','awaiting_payment') AND trans.form_type = 'invoice'";
        $allowed_roles = array('administrator', 'erp_ac_manager', 'erp_crm_manager','accountant');
        $other_roles = array('salemanager','sales_manager');

        $OtherTransRows = "SELECT 
        trans.order_id AS estimate_no,
        trans.`created_at` AS inv_date,
        trans.`invoice_number`,
        trans.`invoice_format`,        
        trans.`id` AS `invoice_id`,                
        trans.`user_id` AS contact_code,      
        trans.`created_by` AS created_by,
        trans.`total` AS invoice_amount,
        trans.`due` AS balance_amount,
	    trans.status,
        trans.`summary` AS inv_memo,
        ' ' AS remarks
        FROM `{$wpdb->prefix}erp_ac_transactions` AS trans
		INNER JOIN `{$wpdb->prefix}postmeta` ON post_id = trans.order_id AND meta_key = '{$field_coordinator_id}' AND meta_value = '{$field_coordinator}'";

        if (array_intersect($allowed_roles, $user->roles)) {
            $where .= " ";
            $OtherTransRows .= (" WHERE trans.due > 0 AND trans.status IN ('partial','awaiting_payment') AND trans.form_type = 'invoice'");
        } elseif (array_intersect($other_roles, $user->roles)) {
            $user_id = get_current_user_id();
            $user_city = get_user_meta($user_id, 'sales_zone', true);

            if (!empty($user_city)) {
                $sql .= " INNER JOIN `{$wpdb->prefix}usermeta` AS um 
                ON um.`user_id` =  trans.`created_by` AND um.`meta_key` = 'sales_zone' 
                AND um.`meta_value` = '" . $user_city . "'";

                $OtherTransRows = (" INNER JOIN `{$wpdb->prefix}usermeta` AS um 
                ON um.`user_id` =  trans.`created_by` AND um.`meta_key` = 'sales_zone' 
                AND um.`meta_value` = '" . $user_city . "'
                WHERE trans.due > 0 AND trans.status IN ('partial','awaiting_payment') AND trans.form_type = 'invoice'");
            }
        } else {
            $user_id = get_current_user_id();
            $where .= " AND trans.`created_by` = '" . $user_id . "'";
            $OtherTransRows = (" WHERE trans.due > 0 AND trans.status IN ('partial','awaiting_payment') AND trans.form_type = 'invoice' 
            AND trans.`created_by` = '" . $user_id . "'");
        }

        $sql .= $where;
        //$sql .= " GROUP BY ertr.id";
        $sql .= " order by " . $columnName . " " . $columnSortOrder;

        //$totalResults = ($wpdb->get_var("SELECT COUNT(*) FROM `{$wpdb->prefix}erp_ac_transactions` AS ertr" . $where));

        $res = $wpdb->get_results($sql, ARRAY_A);
        $transIds = implode(",",array_column($res, 'invoice_id'));

        if(!empty($transIds)){
            $OtherTransRows .= " AND trans.id NOT IN ({$transIds})";
        }
        $OtherTransRows .= " order by " . $columnName . " " . $columnSortOrder;
        $OtherTransRows = $wpdb->get_results($OtherTransRows, ARRAY_A);
        $res = array_merge($res,$OtherTransRows);

        $data = array();

        foreach ($res as $key => $row) {

            $row['field_coordinator'] = '';
            $row['office_coordinator'] = '';
            $row['client_name'] = '';
            $company = '';
            $fullname = '';

            if(!empty($row['estimate_no'])){

                $order = wc_get_order( $row['estimate_no'] );

                if($order){
                    $row['office_coordinator'] = $order->get_meta('wc_pos_served_by_name');

                    if(empty($row['office_coordinator'])){
                        $row['office_coordinator'] = $order->get_user()->data->display_name;
                    }
                    $fullname = $order->get_formatted_billing_full_name();

                    if(empty(trim($fullname))){
                        $fullname = $order->get_formatted_shipping_full_name();
                    }
					/*
						SWITCHED OFF DUE TO FILRATION
						if($field_coordinator ){
							$row['field_coordinator'] = $order->get_meta('_wc_acof_'. $field_coordinator);
						}
					*/
					//now use for contact person
                    $att_name = $order->get_billing_company();
                }

            }


                $deal_company = erp_get_people( $row['contact_code'] );
                $row['client_name'] = $deal_company->company;

                if(empty(trim($row['client_name']))){
                    $row['client_name'] = $deal_company->first_name . ' ' . $deal_company->last_name;
                }


            $invoice_number = erp_ac_get_invoice_number( $row['invoice_number'], $row['invoice_format'] );

            if(!empty($row['contact_code'])) {
                $invoice_account = erp_get_people( $row['contact_code'] );
                $invoice_account_name = $invoice_account->first_name . ' ' . $invoice_account->last_name;

                $id_for_sale_zone = $invoice_account->user_id;
                $sale_zone = get_user_meta($id_for_sale_zone, 'sales_zone', true);
            }else{
                $sale_zone = '';
                $invoice_account_name = '';
            }

            $data[] = array(
                "office_coordinator"    => $row['office_coordinator'],
                "field_coordinator"     => $row['field_coordinator'],
                "inv_date"             	=> $row['inv_date'],
                "estimate_no"           => $row['estimate_no'],
                "sales_zone"            => strlen($sale_zone) != 0 ? $sale_zone : '-',
                "contact_person"          => strlen($att_name) != 0 ? $att_name : $fullname,
                "billing_name"          => strlen($fullname) != 0 ? $fullname:"",
                "client_name"           => strlen($invoice_account_name) != 0 ? $invoice_account_name . "(" . $row['contact_code'] . ")":"",
                "invoice_amount"        => $row['invoice_amount'],
                "balance_amount"        => $row['balance_amount'],
                "inv_memo"              => $row['inv_memo'],
                "remarks"               => $row['remarks']
            );
        }

        $response = array(
            "draw"                  => count($res), //intval($draw)$totalResults,
            "iTotalRecords"         => count($res),
            "iTotalDisplayRecords"  => count($res),
            "iDisplayLength"      => count($res),
            "aaData"                => $data
        );

        echo json_encode($response);
        exit;
    }



}
new IMPAKTT_Customization_Protectors_Ajax();
