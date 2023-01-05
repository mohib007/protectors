<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.12/css/jquery.dataTables.min.css" />
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.5.3/css/buttons.dataTables.min.css">

<script src="https://cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.5.3/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.5.3/js/buttons.flash.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/1.5.3/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.5.3/js/buttons.print.min.js"></script>
<style>
    #erpRpt td {
        text-align: center;
    }
	.filtration-section{
		float: right;
		margin-bottom: 10px;
		width: 60%;		
	}
	.filtration-section .table-cell {
		float: left;
		margin-right: 2%;
	}	
	.filtration-section .table-cell select{
		width: 170px;
	}	
</style>
<div class="wrap">
	<div class="filtration-section" style="float:right;margin-bottom:10px;">
		<form class="table-cell" style="padding-top:8px;" id="form-type-selection">
			<span><strong>Assigned Enquiries?</strong></span>
			<input type="radio" id="field-type" value="yes" name="filter-field-type" checked>
			<label for="field-type">Yes</label>
			<input type="radio" id="officer-type" value="no" name="filter-field-type">
			<label for="officer-type">No</label>
		</form>
		
		<div class="table-cell" id="filter-field-cordinator">
			<label><strong>Select Field Cordinator</strong></label>
			<select name="filter-field-cordinator">
				<?php
					$field_cordinator_id = get_option('erp_accounting_invoice_collect_id');
					$custom_fields = get_option('wc_admin_custom_order_fields');
					$users = $custom_fields[$field_cordinator_id]['options'];
					foreach($users as $user){
						$userWP = get_user_by('login', $user['label']);
						if ($userWP) {
							echo '<option value="'.$user['value'].'">' . $userWP->display_name . '</option>';
						}
					}
				?>
			</select>
		</div>

		<div class="table-cell" style="display:none;" id="filter-office-cordinator">
			<label><strong>Select Office Cordinator</strong></label>
			<select name="filter-office-cordinator">
				<?php
					$users = get_users( [ 'role__in' => [ 'erp_crm_agent', 'erp_crm_manager'] ] );
					foreach($users as $user){
						echo '<option value="'.$user->data->ID.'">' . $user->data->display_name. '</option>';
					}
				?>
			</select>
		</div>			
		<input type="button" class="button-primary" id="submit-filration-data" value="submit" />
	</div>
	
   <table id="erpRpt" class="display" style="width:100%">
        <thead>
            <tr>
                <th>Sales Zone</th>
                <th>Company</th>
                <th>Concerned Att.</th>
                <th>Enquiry No.</th>
                <th>Estimate No</th>
                <th>Item Name</th>
                <th>Order Amount</th>
                <th>Shipping Address</th>
                <th>Remarks</th>
                
            </tr>
        </thead>
        
    </table>
</div>

<script>
jQuery('#form-type-selection').change(function(){
            selected_value = jQuery("input[name='filter-field-type']:checked").val();
			if (selected_value == 'yes'){
				jQuery('#filter-office-cordinator').hide();
				jQuery('#filter-field-cordinator').show();
			}
			else{
				jQuery('#filter-field-cordinator').hide();				
				jQuery('#filter-office-cordinator').show();
			}
        });
		
jQuery( document ).ready(function(){
            selected_value = jQuery("input[name='filter-field-type']:checked").val();
			if (selected_value == 'yes'){
				jQuery('#filter-office-cordinator').hide();
				jQuery('#filter-field-cordinator').show();
			}
			else{
				jQuery('#filter-field-cordinator').hide();				
				jQuery('#filter-office-cordinator').show();
			}
        });		
		
jQuery("#submit-filration-data").click(function() {
	
	var selected_value = jQuery("input[name='filter-field-type']:checked").val();
	if (selected_value == 'yes'){
		var fieldCordinator = jQuery('#filter-field-cordinator select').val();
	}
	else{
		var fieldCordinator = jQuery('#filter-office-cordinator select').val();	
	}
	
	jQuery('#erpRpt').dataTable().fnDestroy();
    jQuery('#erpRpt').DataTable({
        "processing": true,
        "serverSide": true,
        "serverMethod": 'post',
        // "displayLength": 25,
            ajax:{
                url: ajaxurl+"?action=get_reports_customized","data":{"fieldCordinator":fieldCordinator,"selected_value":selected_value}, "type":"post"},
            "dom": 'Bfrtip',
            "columns": [
             { "data": 'sales_zone' },
             { "data": 'client_company' },
             { "data": 'client_name' },
             { "data": 'deal_id' },
             { "data": 'order_no' },
             { "data": 'product_name' },
             { "data": 'order_amount' },
             { "data": 'shipping_address' },
             { "data": 'remarks' }
            ],
            scrollY:        "450px",
            scrollX:        true,
            scrollCollapse: true,
            paging:         false,
            buttons: [
                {
                    extend: 'excelHtml5',
                    text: '<span class="fa fa-file-excel-o"></span> Excel Export',
					title: 'Enquiry Report: ' + fieldCordinator,
					exportOptions: {
							modifier: {
                            search: 'applied',
                            order: 'applied'								
							//page: 'all'
							},
							format: {
								header: function ( data, columnIdx ) {
									if(columnIdx==1){
									return data;
									}
									else{
									return data;
								}
							}
						}
					}
                }
            ],
            "columnDefs": [
                   
                ],
                "order": [[ 0, 'asc' ]],
    });

    var table = jQuery('#erpRpt').DataTable();

    jQuery('#erpRpt tbody').on('click', 'tr', function () {
		var getBaseurl = window.location.origin;
		var data = table.row( this ).data();

        var win = window.open(getBaseurl+"/wp-admin/admin.php?page=erp-deals-admin-page&action=view-deal&id=" + data.deal_id, '_blank');
        if (win) {
            //Browser has allowed it to be opened
            win.focus();
        } else {
            //Browser has blocked it
            alert('Please allow popups for this website');
        }
    });
});
</script>