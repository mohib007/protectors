<?php
	require_once (dirname( __FILE__ ) . '/../includes/class-customer-ledger-transactions-list-table.php');
   
	$start_date     = empty( $_GET['start_date'] ) ? date( 'Y-m-d', strtotime( erp_financial_start_date() ) ) : $_GET['start_date'];
    $end_date     = empty( $_GET['end_date'] ) ? date( 'Y-m-d', strtotime( erp_financial_end_date() ) ) : $_GET['end_date'];
    $customer_id     = empty( $_GET['people_user_id'] ) ? "" : $_GET['people_user_id'];
    $selected_sku     = empty( $_GET['product_sku'] ) ? "" : $_GET['product_sku'];

    $customer = erp_ac_get_customer( $customer_id );
    $user_id = $customer->user_id; 
    $fullname = $customer->first_name.' '.$customer->last_name; 
    $company = $customer->company;
    $OrderCount = 0;
    $email = $customer->email; 
    $phone = $customer->phone; 
    $mobile = $customer->mobile;
    $address =  $customer->street_1 . ' ' . $customer->street_2;
	
	if(!empty($customer->postal_code))
		$address .=  ', ' . $customer->postal_code;
	
	if(!empty($customer->city))
		$address .=  ', ' . $customer->city; 
	
	if(!empty($customer->state))
    $address .=  ', ' . $customer->state; 

	if(!empty($customer->country))
		$address .=  ', ' . $customer->country; 

    $contact_owner = $customer->contact_owner;if($user_id){
	$customer_orders = get_posts( array(
        'numberposts' => -1,
        'meta_key'    => '_customer_id',
        'meta_value'  => $user_id,
        'post_type'   => "sumo_pp_payments",
        'post_status' => 'close',
    ) );
	$keysStatuses = array_keys( wc_get_order_statuses() );
	$unsetStatus = array('wc-failed','wc-on-hold','wc-cancelled');
	$unsetStatus = array_intersect($keysStatuses, $unsetStatus);
	$unsetStatus = array_keys($unsetStatus);
	foreach($unsetStatus as $unsetIt){
		unset($keysStatuses[$unsetIt]);
	}
	
    $customer_otherorders = wc_get_orders( array(
        'numberposts' => -1,
		'parent_id'=> 0,
        'meta_key'    => '_customer_user',
        'meta_value'  => $user_id,
        'post_type'   => 'shop_order',
        'post_status' => $keysStatuses,
        //'exclude' => $customer_orders[0]->ID
    ) );
	if(count($customer_otherorders) > 0){
		foreach($customer_otherorders as $key => $customer_otherorder){
			$is_sumo_pp_order = get_post_meta($customer_otherorder->ID, "is_sumo_pp_order");
			if(!empty($is_sumo_pp_order)){
				unset($customer_otherorders[$key]);
			}
		}
	}
	
	if(count($customer_orders) > 0){
		//$result=array_merge($customer_orders, $customer_otherorders);
		$totalpayable;
		$paymentplan_val = array();
		$item_sku = array();
		$item_categories = array();
		$counter = 0;

		foreach ($customer_orders as $customer_order) {

			$order_id = get_post_meta($customer_order->ID, "_initial_payment_order_id");
			$total_payable_amount = get_post_meta($customer_order->ID, "_total_payable_amount");
			$down_payment = get_post_meta($customer_order->ID, "_down_payment");
			$paymentplan_val[$counter]["ID"] = explode("payments-",$customer_order->post_name)[1].'('.$order_id[0].')';
			$paymentplan_val[$counter]["order_id"] = $customer_order->ID;
			if(count($paymentplan_val) == 1){
					$paymentplan_val[$counter]["total_payable_amount"] = $total_payable_amount[0];
					$paymentplan_val[$counter]["down_payment"] = $down_payment[0];
					$totalpayable += $total_payable_amount[0];
			}
		
			if(!empty($order_id)){
			  $customer_final_orders = wc_get_order( $order_id[0] );
			  foreach ($customer_final_orders->items as $item) {				 
				$customer_final_product = wc_get_product($item->get_product_id());
				if(!empty($selected_sku)){
				  if($customer_final_product->get_sku() == $selected_sku ){
					  //var_dump($customer_final_product->ID);
						$item_sku[$customer_final_product->id] = $customer_final_product->get_sku();
						$item_categories[$customer_final_product->id] = $customer_final_product->get_categories();
						$item_name[$customer_final_product->id] = $customer_final_product->get_name();
				  } else {
					  //echo $key;
					  unset($paymentplan_val[$counter]);
					  continue;
				  }
			  }else{
					$item_sku[$customer_final_product->id] = $customer_final_product->get_sku();
					$item_categories[$customer_final_product->id] = $customer_final_product->get_categories();
					$item_name[$customer_final_product->id] = $customer_final_product->get_name();
			  }
			  }
			}
			$counter++;
		}
		$OrderCount = count($paymentplan_val);
	}

	if(count($customer_otherorders) > 0){	
		foreach ($customer_otherorders as $customer_order) {
			$Oorder_id = $customer_order->get_id();
			$order_ids[]['order_id'] = "ODR-" . $Oorder_id;

			if(!empty($Oorder_id)){
				$OItems = $customer_order->get_items();
				foreach($OItems as $item){
				$customer_order_product = wc_get_product($item->get_product_id());
					if($customer_order_product){
						if(!empty($selected_sku)){
							if($customer_order_product->get_sku() == $selected_sku ){
								$item_sku[$customer_order_product->id] = $customer_order_product->get_sku();
								$item_categories[$customer_order_product->id] = $customer_order_product->get_categories();
								$item_name[$customer_order_product->id] = $customer_order_product->get_name();								
							} else{
								  continue;
							}							  
						  }
						  else{
							$item_sku[$customer_order_product->id] = $customer_order_product->get_sku();
							$item_categories[$customer_order_product->id] = $customer_order_product->get_categories(); 
							$item_name[$customer_order_product->id] = $customer_order_product->get_name();
						  }
					}
				}
			}
			$counter++;
		}
	}
	$OrderCount += count($customer_otherorders);
	$credit = sum_user_dues($customer_id)['due'];
	$item_sku = array_unique($item_sku);
	$item_categories = array_unique($item_categories);
	$item_name = array_unique($item_name);

	$top5skus = array_slice($item_sku, 0, 5);
	$top5skus = implode($top5skus,", ");
	$purchasedItems = array_slice($item_name, 0, 5);
	$purchasedItems = implode($purchasedItems,", ");

	$top5item_categories = array_slice($item_categories, 0, 5);
	$top5_categories = implode($top5item_categories,", ");
	
	$customerdata = array(
		 'user_id' => $customer->user_id,
		'fullname' => $customer->first_name.' '.$customer->last_name, 
		'company' => $customer->company,
		'email'  => $customer->email,
		'phone'  => $customer->phone,
		'mobile'  => $customer->mobile,
		'address'  =>  $customer->street_1,
		'Categories' => $top5_categories,
		'sku' => $top5skus,
		'balance' => $paymentplan_val[0]["total_payable_amount"],
		'remaining_balance' => $credit,
	);
	
}

?>
<script>
jQuery( document ).ready(function($) {
    $('#reset_filtered').attr("type","button");
	document.title = "Customer Ledger Report";		
	<?php
		if($_GET["submit_filter_pdf_export"]){
			echo 'print_erp_data()';
		}	
	?>
});
function reset_function_get(){
	<?php $resetURL = get_site_url()."/wp-admin/admin.php?page=erp-customer-ledger2"; ?>	
	window.location.replace("<?php echo $resetURL; ?>");
	return false;
}
</script>
<?php
if($_GET["submit_filter_pdf_export"]){
$filename = 'Customer-Ledger-' . $start_date . '-' . $end_date . '.csv';
?>
<script>
function print_erp_data() {
	jQuery('#print').addClass("PrintCss");
	var print = jQuery("html").html();
	jQuery('#print').removeClass("PrintCss");
    // for non-IE
   /* let mywindow = window.open('', 'PRINT', 'height=650,width=900,top=100,left=150');

 mywindow.document.write('<html><head><title><?= $filename; ?></title>');
 mywindow.document.write('</head><body >');
 mywindow.document.write(print);
 mywindow.document.write('</body></html>');

  window.document.close(); // necessary for IE >= 10*/
  
  window.focus(); // necessary for IE >= 10*/

  window.print(print);
  
  window.close();
}
</script>

<style>
@media print{
	body{
		font-family: Arial;
		font-size: 10px;
	}
	#wpwrap,body:not(.woocommerce_page_wc-reports) #wpwrap, .toast {
		display: block !important;
	}
	#wpbody {
		padding-top: 0px !important;
	}	
	.wrap, .wrap + #poststuff {
		padding: 5px !important;
	}	
	.wrap > h2:first-child	{
		padding: 9px 0px 4px 5px !important;
	}
	.customer_information {
		padding: 7px !important;
	}		
	.tablenav.bottom .tablenav-pages, .customer-ledgers2 tfoot{
		display: none;
	}
	.filter-items .actions, .tablenav .actions,
	.tablenav.top .tablenav-pages{
		display: none;
	}
	.erp-ac-trial-report-header-wrap {
		display: none;
	}
		a.clientside-back-to-top {
		display: none;
	}
	table.wp-list-table.widefat.fixed.striped.journals tfoot, #wpfooter,#betterdocs-ia{
		 display: none !important;
	}
	.wp-list-table td, .wp-list-table th, .wp-list-table.widefat td, .wp-list-table.widefat th, table.fixed td, table.fixed th {		
		padding-bottom: 5px !important;
		padding-top: 5px !important;
		font-size: 10px !important;
	}
	.widefat td, .widefat td ol, .widefat td p, .widefat td ul {
		font-size: 10px !important;
	}
	
	#the-list tr td, #the-list tr td a{
		color: #000 !important;
	}
	.customer_information tr td{
		color: #000 !important;
	}
	.customer-ledgers2 tr th{
		color: #000 !important;
	}
	.customer-ledgers2 thead {
		display: contents;
	}	
}
</style>

<?php } ?>

<div id="print" class="wrap">
    <h2 align="center"><?php _e( 'Customer Ledger', 'erp' ); ?></h2>
    <div class="erp-ac-trial-report-header-wrap">
       <!--  <p class="erp-ac-report-tax-date">
        <?php
            //printf( '<i class="fa fa-calendar"></i> %1$s', erp_format_date( $end, 'F j, Y' ) );
        ?>
        </p> -->

        <?php //erp_ac_cl_report_filter_form($start_date,$end_date,$customerdata); ?>
    </div>
    <?php if(!empty($customer)) { ?>
    <div class="customer_information">
        <table class="table widefat striped vendor-list-table">
        <tbody>
            <tr>
                <td class="column-customer"><strong><?php _e( 'Customer Name: ', 'erp' ); ?></strong>
				<a target="_blank" href="<?php echo admin_url( 'admin.php?page=erp-accounting-customers&action=view&id=' . $customer_id  . '&tab=transactions' ); ?>"><?php _e( $fullname, 'erp' ); ?></a>
				</td>
                <td class="column-customer"><strong>From: </strong>
					<?php echo $start_date; ?>		
                </td>
            </tr>
            <tr>
                <td class="column-customer"><strong><?php _e( 'Customer Email: ', 'erp' ); ?></strong><?php echo $email; ?></td>
                 <td class="column-customer"><strong>To: </strong><?php echo $end_date; ?></td>            
            </tr>		
		  <tr>
			<td class="column-customer"><strong><?php _e( 'Phone: ', 'erp' ); ?> </strong> <?php echo $phone; ?></td>			
			<td class="column-customer"></td>
          </tr>	
		  <tr>
			<td class="column-customer"><strong><?php _e( 'Mobile: ', 'erp' ); ?> </strong> <?php echo $mobile; ?></td>	 		
			<td class="column-customer"></td>    			
          </tr>			   		
		<tr>		
			<td class="column-customer"><strong><?php _e( 'Customer Address: ', 'erp' ); ?></strong><?php echo $address; ?></td>
			<td class="column-customer"></td>	
		</tr>

        </tbody>
    </table>
    </div>
    <?php } ?>	
    
    <div class="wrap">

    <form method="get" class="erp-ac-list-table-form">
        <input type="hidden" name="page" value="erp-customer-ledger2">
		<input type="hidden" name="type" value="customer-ledger">
        <input type="hidden" name="action" value="view">
        <input type="hidden" name="id" value="<?php echo $ledger->id; ?>">
		
		<?php if(!empty($_POST)) {
			foreach($_POST as $key => $post){
			?>
				<input type="hidden" name="<?php echo $key;?>" value="<?php echo $post; ?>">
		
		<?php }
		}		?>
        <?php
		$list_table = new WeDevs\ERP\Accounting\Customer_Ledger_Transactions_List_Table_Updated();
        $list_table->prepare_items();
        $list_table->views();
        $list_table->display();
        ?>
    </form>
</div>

</div>
<style>
    td.col-price,
    th.col-price {
        text-align: right;
    }
	.customer_information tr td{
		color: rgba(0,0,0,.4) !important;
	}

@media print{
	.customer_information tr td{
		color: #000 !important;
	}
}
	
</style>
