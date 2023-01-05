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
<div class="wrap">
	<div class="filtration-section" style="float:right;margin-bottom:10px;">
		<label>Select Field Cordinator</label>
		<select name="filter-field-cordinator" id="filter-field-cordinator">
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
		<input type="button" class="button-primary" id="submit-filration-data" value="submit" />
	</div>

    <table id="erpRpt" class="display" style="width:100%">
        <thead>
        <tr>
            <th>Invoice Date</th>
            <th>Estimate No.</th>
            <th>Sales Zone</th>
            <th>Billing Company Name</th>			
            <th>Contact Person Name</th>
            <th>Invoice Amount</th>
            <th>Balance Amount</th>
            <th>Remarks</th>
        </tr>
        </thead>

    </table>
</div>


<script>
    jQuery("#submit-filration-data").click(function() {
		var fieldCordinator = jQuery('#filter-field-cordinator').val();
		jQuery('#erpRpt').dataTable().fnDestroy();
        jQuery('#erpRpt').DataTable({
            "processing": true,
            "serverSide": true,
            "serverMethod": 'post',
            "iDisplayStart ": 0,
            "iDisplayLength": 4,
            "displayLength":  5,

            ajax:{url:ajaxurl+"?action=get_receivables_modified","data":{"fieldCordinator":fieldCordinator}, "type":"post"},
            "dom": 'Bfrtip',
            "columns": [
                { "data": 'inv_date' },
                { "data": 'estimate_no' },
                { "data": 'sales_zone' },
                { "data": 'client_name' },				
                { "data": 'contact_person' },
                { "data": 'invoice_amount' },
                { "data": 'balance_amount' },
                { "data": 'remarks' }
            ],
            scrollY:        "300px",
            scrollX:        true,
            scrollCollapse: true,
            paging:         false,
            buttons: [
                {
                    extend: 'excelHtml5',
                    text: '<span class="fa fa-file-excel-o"></span> Excel Export',
					title: 'Receivable Report: ' + fieldCordinator,
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
            "order": [[ 1, 'asc' ]],
        });
    });
</script>