<?php
	require_once (dirname( __FILE__ ) . '/../includes/class-vendor-ledger-transactions-list-table.php');
    $start_date     = empty( $_GET['start_date'] ) ? date( 'Y-m-d', strtotime( erp_financial_start_date() ) ) : $_GET['start_date'];
    $end_date     = empty( $_GET['end_date'] ) ? date( 'Y-m-d', strtotime( erp_financial_end_date() ) ) : $_GET['end_date'];
    $vendor_id     = empty( $_GET['people_user_id'] ) ? "" : $_GET['people_user_id'];
    $selected_sku     = empty( $_GET['product_sku'] ) ? "" : $_GET['product_sku'];

    $vendor = erp_ac_get_customer( $vendor_id );
    $user_id = $vendor->user_id; 
    $fullname = $vendor->first_name.' '.$customer->last_name; 
    $company = $vendor->company;
    
    $email = $vendor->email; 
    $phone = $vendor->phone; 
    $mobile = $vendor->mobile;
    $address =  $vendor->street_1 . ' ' . $vendor->street_2;
	$credit = sum_user_dues($vendor_id, 'expense')['due'];	
	if(!empty($vendor->postal_code))
		$address .=  ', ' . $vendor->postal_code;
	
	if(!empty($vendor->city))
		$address .=  ', ' . $vendor->city; 
	
	if(!empty($vendor->state))
    $address .=  ', ' . $vendor->state; 

	if(!empty($vendor->country))
		$address .=  ', ' . $vendor->country; 

    $contact_owner = $vendor->contact_owner;
?>
<script>
jQuery( document ).ready(function($) {
    $('#reset_filtered').attr("type","button");
	document.title = "Vendor Ledger Report";	
	<?php
		if($_GET["submit_filter_pdf_export"]){
			echo 'print_erp_data()';
		}	
	?>
});
function reset_function_get(){
	<?php $resetURL = get_site_url()."/wp-admin/admin.php?page=erp-vendor-ledger2"; ?>	
	window.location.replace("<?php echo $resetURL; ?>");
	return false;
}
</script>
<?php
if($_GET["submit_filter_pdf_export"]){
$filename = 'Vendor-Ledger-' . $start_date . '-' . $end_date . '.csv';
?>
<script>
function print_erp_data() {
	var print = jQuery("html").html();
    // for non-IE
   /* let mywindow = window.open('', 'PRINT', 'height=650,width=900,top=100,left=150');

 mywindow.document.write('<html><head><title>${title}</title>');
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
	.tablenav.bottom .tablenav-pages, .vendor-ledgers tfoot{
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
	.vendor-ledgers tr th{
		color: #000 !important;
	}
	.vendor-ledgers thead {
		display: contents;
	}	
	.widefat td, .widefat th{
		padding: 5px 10px;	
	}
}
</style>

<?php } ?>

<div id="print" class="wrap">
    <h2 align="center"><?php _e( 'Vendor Ledger', 'erp' ); ?></h2>
	
    <div class="erp-ac-trial-report-header-wrap">
       <!--  <p class="erp-ac-report-tax-date">
        <?php
            //printf( '<i class="fa fa-calendar"></i> %1$s', erp_format_date( $end, 'F j, Y' ) );
        ?>
        </p> -->

        <?php //erp_ac_cl_report_filter_form($start_date,$end_date,$customerdata); ?>
    </div>
    <?php if(!empty($vendor)) { ?>
    <div class="customer_information">
        <table class="table widefat striped vendor-list-table">
        <tbody>
            <tr>
                <td class="column-customer"><strong><?php _e( 'Vendor Name: ', 'erp' ); ?></strong>
				<a target="_blank" href="<?php echo admin_url( 'admin.php?page=erp-accounting-vendors&action=view&id=' . $vendor_id  . '&tab=transactions' ); ?>"><?php _e( $fullname, 'erp' ); ?></a>
				</td>
				<td class="column-customer"><strong><?php _e( 'From: ', 'erp' ); ?></strong><?php echo $start_date; ?></td>
            </tr>

		  <tr>
				<td class="column-customer"><strong><?php _e( 'Mobile: ', 'erp' ); ?> </strong> <?php echo $mobile; ?></td>
				<td class="column-customer"><strong><?php _e( 'To: ', 'erp' ); ?> </strong> <?php echo $end_date; ?></td>
          </tr>
		  
		<tr>
			<td class="column-customer"><strong><?php _e( 'Vendor Email: ', 'erp' ); ?></strong><?php echo $email; ?></td>	
		</tr>


        </tbody>
    </table>
    </div>
    <?php } ?>	
    
    <div class="wrap">

    <form method="get" class="erp-ac-list-table-form">
        <input type="hidden" name="page" value="erp-vendor-ledger2">
		<input type="hidden" name="type" value="vendor-ledger">
        <input type="hidden" name="action" value="view">
        <input type="hidden" name="id" value="<?php echo $ledger->id; ?>">
		
        <?php
       $list_table = new WeDevs\ERP\Accounting\Vendor_Ledger_Transactions_List_Table_Updated();
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
