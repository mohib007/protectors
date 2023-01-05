<?php
namespace WeDevs\ERP\Accounting;

if ( ! class_exists ( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}


/**
 * List table class
 */
class Customer_Ledger_Transactions_List_Table_Updated extends Transaction_List_Table {
    public $type_id = [];
    public $chart_group  = [];
    public $account_prev_balance = 0;
    public $TotalDebit = 0;
    public $TotalCredit = 0;


	
    function __construct() {

        $this->type = 'journal';
        $this->TotalDebit = 0;
        $this->TotalCredit = 0;
		
        $this->slug = ! empty( $_GET['page'] ) && ( $_GET['page'] == 'erp-customer-ledger2' ) ? 'erp-customer-ledger2' : 'erp-customer-ledger2';

        parent::__construct();

        \WP_List_Table::__construct([
            'singular' => 'customer-ledger2',
            'plural'   => 'customer-ledgers2',
            'ajax'     => false
        ]);

    }

    /**
     * Get the column names
     *
     * @return array
     */
    function get_columns() {

        $columns = array(
            'issue_date' => __( 'Date', 'erp' ),
            'form_type' => __( 'Type', 'erp' ),
            'ref'        => __( 'ODR', 'erp' ),
            'inv_title'  => __( 'PO# / Your Ref', 'erp' )
        );

			$columns['narration'] = __( 'Item Narration', 'erp' ); 
            $columns['debit']   = __( 'Debit', 'erp' );
            $columns['credit']  = __( 'Credit', 'erp' );
            $columns['balance'] = __( 'Balance', 'erp' );
            $columns['summary'] = __( 'Memo', 'erp' );


        return $columns;
    }

    /**
     * Get sortable columns
     *
     * @return array
     */
    function get_sortable_columns() {
        $sortable_columns = array();

       $sortable_columns = array(
                'issue_date' => array( 'issue_date', true ),
            );

        return $sortable_columns;
    }

    /**
     * Render the issue date column
     *
     * @since  1.1.6
     *
     * @param  object  $item
     *
     * @return string
     */
    function column_issue_date( $item ) {
        if ( empty( $item->id ) ) {
            return $item->issue_date;
        }

        if ( $this->slug == 'erp-accounting-charts' ) {
            return sprintf( '<a data-transaction_id="%d" class="erp-ac-transaction-report" href="#">%s</a>', $item->id, erp_format_date( $item->issue_date ) );
        }

        $url   = admin_url( 'admin.php?page='.$this->slug.'&action=new&journal_id=' . $item->id );

        if ( $this->slug == 'erp-accounting-journal' ) {
            $actions['edit'] = sprintf( '<a href="%1s">%2s</a>', $url, __( 'Edit', 'erp' ) );
        } else {
            $actions = [];
        }

        return sprintf( '<a target="_blank" href="%1$s">%2$s</a> %3$s', admin_url( 'admin.php?page=erp-accounting-customers&action=view&id=' . $item->id ), erp_format_date( $item->issue_date ), $this->row_actions( $actions ) );
    }

    /**
     * Render the debit column
     *
     * @since  1.1.6
     *
     * @param  object  $item
     *
     * @return string
     */
    function column_debit( $item ) {
        if ( isset( $item->is_opening ) && $item->is_opening ) {
            return '&#8212';
        }

        if ( isset( $item->is_total ) && $item->is_total ) {
			return $item->debit;
        }
		
		$this->TotalDebit = (float)$this->TotalDebit + (float)$item->debit;

        return erp_ac_get_price( $item->debit, ['symbol' => false] );
    }

    /**
     * Render the credit column
     *
     * @since  1.1.6
     *
     * @param  object  $item
     *
     * @return string
     */
    function column_credit( $item ) {
        if ( isset( $item->is_opening ) && $item->is_opening ) {
            return '&#8212';
        }
        if ( isset( $item->is_total ) && $item->is_total ) {
			return $item->credit;
        }		
		$this->TotalCredit = (float)$this->TotalCredit + (float)$item->credit;
        return erp_ac_get_price( $item->credit, ['symbol' => false] );
    }

    /**
     * Balance
     *
     * @param  array $item
     *
     * @return string
     */
    function column_balance( $item ) {
        $balance = 0;
        $balance =  ( $item->debit + $this->account_prev_balance ) - $item->credit;
        $this->account_prev_balance = $balance;

        if ( isset( $item->is_total ) && $item->is_total ) {
			return erp_ac_get_price( $balance );
        }	

        return erp_ac_get_price( $balance );
    }
	
	/**
     * Get narrration from line of items and product name
     *
     * @param  array $item
     *
     * @return string
     */
    function column_narration( $item ) {
 
        if ( isset( $item->is_total ) && $item->is_total ) {
            return erp_ac_get_price( $this->TotalCredit );
        }
 
		if(!empty($item->description)){

			$narration =  $item->description;
			if($item->product_id != 0 && $item->product_id != '0' && !empty($item->product_id)){
				$item_product_ids = explode(',',$item->product_id);
				$item_product_ids = array_unique($item_product_ids);
				$narration .= ' against product: ';
				$i = 0;	
				foreach($item_product_ids as $product_id){
					$product = wc_get_product( $product_id );
					if($product){
						$productname = $product->get_title();		
						$narration .=  ($i == 0) ? ' ' . $productname : ', ' . $productname;
					}
					else{
						$narration .=  ($i == 0) ? ' which shall not in catalog' : ', which shall not catalog';
					}
					
					$i++;
				}
			}

		}
		else{
			$narration = '—';
		}
        return $narration;
    }

    /**
     * Type changes into format as required
     *
     * @param  array $item
     *
     * @return string
     */
	function column_form_type( $item ) {
        if ( isset( $item->is_opening ) && $item->is_opening ) {
            return '&#8212';
        }
        if ( isset( $item->is_total ) && $item->is_total ) {
            return erp_ac_get_price( $this->TotalDebit);
        }	
		
		$invoice_number = erp_ac_get_invoice_number( $item->invoice_number, $item->invoice_format);
		return $invoice_number;
	}	

    /**
     * Get section for sales table list
     *
     * @since  1.1.6
     *
     * @return array
     */
    public function get_section() {
        return [];
    }

    /**
     * Filters
     *
     * @param  string  $which
     *
     * @return void
     */
    public function extra_tablenav( $which ) {
        $financial_start = date( 'Y-m-d', strtotime( erp_financial_start_date() ) );
        $financial_end   = date( 'Y-m-d', strtotime( erp_financial_end_date() ) );

			$Ttype = array("debit"=>"Debit","credit"=>"Credit");
			$TtypeM = array("sales"=>"Sales","expense"=>"Expense","journal" => "Journal");
			$TtypeF = array("invoice"=>"Invoice","payment"=>"Payment","vendor_credit"=>"Cendor Credit","payment_voucher" => "Payment Voucher");
			
			$customerargs = wp_parse_args( $customerargs );
	
	/*$args = array(
    'limit' => -1,
	);
	//global $product,$woocommerce;
	$product = new WC_Product_Factory(); 
	$products = $product->get_products( $args );

	$item_sku = array();
	
	foreach($products as $product){
		if(!empty($product->sku)){
		    $item_sku[$product->sku] = $product->slug;
		}
		
	}*/
	

        if ( 'top' == $which ) {
            echo '<div class="alignleft actions">';
		 
				
				$substr = array();
				if(isset( $transaction['user_id'] )){
					$a = erp_get_people_by('id',$transaction['user_id']);
					$substr = [ $a->id => $a->first_name . ' ' . $a->last_name ];
				}else{
					$substr = [ '' => __( '&mdash; Select &mdash;', 'erp' ) ] + erp_get_peoples_array( ['type' => 'customer', 'number' => '50' ] );
				}							


	if ( $customerargs["user_id"] ) {
		erp_html_form_input([
				'name'        => 'user_id',
				'type'        => 'hidden',
				'class'       => 'erp-ac-user_id-search',
				'value'   => isset( $customerargs["user_id"] ) && ! empty( $customerargs["user_id"] ) ? $customerargs["user_id"] : '',
				'placeholder' => __( 'Search for user_id', 'erp' ),
			]);
	
    }
	if ( $customerargs ["fullname"] ) {
		erp_html_form_input([
				'name'        => 'fullname',
				'type'        => 'hidden',
				'class'       => 'erp-ac-fullname-search',
				'value'   => isset( $customerargs["fullname"] ) && ! empty( $customerargs["fullname"] ) ? $customerargs ["fullname"] : '',
				'placeholder' => __( 'Search for fullname', 'erp' ),
			]);
	
    }
	if ( $customerargs["company"] ) {
		erp_html_form_input([
				'name'        => 'company',
				'type'        => 'hidden',
				'class'       => 'erp-ac-company-search',
				'value'   => isset( $customerargs["company"] ) && ! empty( $customerargs["company"] ) ? $customerargs["company"] : '',
				'placeholder' => __( 'Search for Company', 'erp' ),
			]);
	
    }
	if ( $customerargs["email"] ) {
		erp_html_form_input([
				'name'        => 'email',
				'type'        => 'hidden',
				'class'       => 'erp-ac-email-search',
				'value'   => isset( $customerargs["email"] ) && ! empty( $customerargs["email"] ) ? $customerargs["email"] : '',
				'placeholder' => __( 'Search for email', 'erp' ),
			]);
	
    }
	if ( $customerargs["phone"] ) {
		erp_html_form_input([
				'name'        => 'phone',
				'type'        => 'hidden',
				'class'       => 'erp-ac-phone-search',
				'value'   => isset( $customerargs["phone"] ) && ! empty( $customerargs["phone"] ) ? $customerargs["phone"] : '',
				'placeholder' => __( 'Search for phone', 'erp' ),
			]);
	
    }
	
	if ( $customerargs["mobile"] ) {
		erp_html_form_input([
				'name'        => 'mobile',
				'type'        => 'hidden',
				'class'       => 'erp-ac-mobile-search',
				'value'   => isset( $customerargs["mobile"] ) && ! empty( $customerargs["mobile"] ) ? $customerargs["mobile"] : '',
				'placeholder' => __( 'Search for mobile', 'erp' ),
			]);
	
    }
		if ( $customerargs["address"] ) {
		erp_html_form_input([
				'name'        => 'address',
				'type'        => 'hidden',
				'class'       => 'erp-ac-address-search',
				'value'   => isset( $customerargs["address"] ) && ! empty( $customerargs["address"] ) ? $customerargs["address"] : '',
				'placeholder' => __( 'Search for address', 'erp' ),
			]);
	
    }
	if ( $customerargs["Categories"] ) {
		erp_html_form_input([
				'name'        => 'Categories',
				'type'        => 'hidden',
				'class'       => 'erp-ac-Categories-search',
				'value'   => isset( $customerargs["Categories"] ) && ! empty( $customerargs["Categories"] ) ? $customerargs["Categories"] : '',
				'placeholder' => __( 'Search for Categories', 'erp' ),
			]);
	
    }
	if ( $customerargs["sku"] ) {
		erp_html_form_input([
				'name'        => 'sku',
				'type'        => 'hidden',
				'class'       => 'erp-ac-sku-search',
				'value'   => isset( $customerargs["sku"] ) && ! empty( $customerargs["sku"] ) ? $customerargs["sku"] : '',
				'placeholder' => __( 'Search for sku', 'erp' ),
			]);
	
    }
	if ( $customerargs["balance"] ) {
		erp_html_form_input([
				'name'        => 'balance',
				'type'        => 'hidden',
				'class'       => 'erp-ac-balance-search',
				'value'   => isset( $customerargs["balance"] ) && ! empty( $customerargs["balance"] ) ? $customerargs["balance"] : '',
				'placeholder' => __( 'Search for balance', 'erp' ),
			]);
	
    }
	if ( $customerargs["remaining_balance"] ) {
		erp_html_form_input([
				'name'        => 'remaining_balance',
				'type'        => 'hidden',
				'class'       => 'erp-ac-remaining_balance-search',
				'value'   => isset( $customerargs["remaining_balance"] ) && ! empty( $customerargs["remaining_balance"] ) ? $customerargs["remaining_balance"] : '',
				'placeholder' => __( 'Search for remaining_balance', 'erp' ),
			]);
	
    }



            erp_html_form_input([
                'name'        => 'start_date',
                'class'       => 'erp-date-picker-from',
                'value'       => isset( $_REQUEST['start_date'] ) && !empty( $_REQUEST['start_date'] ) ? $_REQUEST['start_date'] : $financial_start,
                'placeholder' => __( 'Start Date', 'erp' )
            ]);

            erp_html_form_input([
                'name'        => 'end_date',
                'class'       => 'erp-date-picker-to',
                'value'       => isset( $_REQUEST['end_date'] ) && !empty( $_REQUEST['end_date'] ) ? $_REQUEST['end_date'] : $financial_end,
                'placeholder' => __( 'End Date', 'erp' )
            ]);

			erp_html_form_input( array(
			
							'name'        => 'people_user_id',
							'type'        => 'select',
							'required'    => true,
							'id'          => 'erp-ac-select-user-for-assign-contact',
							'options'     => [ '' => __( '&mdash; Select &mdash;', 'erp' ) ] + erp_get_peoples_array( ['type' => 'customer', 'number' => '-1' ] ),
							'custom_attr' => [
								'data-content' => 'erp-ac-new-customer-content-pop',
								'data-type'    => 'customer'
							],
							'value' => isset( $_REQUEST['people_user_id'] ) ? $_REQUEST['people_user_id'] : ''
				) );		
								

			wp_reset_postdata();
            submit_button( __( 'Filter', 'erp' ), 'button', 'submit_filter_sales', false );
            submit_button( __( 'Export', 'erp' ), 'button', 'submit_filter_export', false );
			submit_button( __( 'Print', 'erp' ), 'button', 'submit_filter_pdf_export', false );
            echo '</div>';
        }
			
    }

    /**
     * Prepare the class items
     *
     * @return void
     */
    function prepare_items() {
		
        //$ledger_id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : false;
        $columns               = $this->get_columns();
		
        $hidden                = array( );
        $sortable              = $this->get_sortable_columns();
        $this->_column_headers = array( $columns, $hidden, $sortable );

        $per_page              = 25;
        $current_page          = $this->get_pagenum();
        $offset                = ( $current_page - 1 ) * $per_page;
        $this->page_status     = isset( $_POST['status'] ) ? sanitize_text_field( $_POST['status'] ) : '2';

        // only ncessary because we have sample data
        $args = array(
            //'type'   => $this->type,
            'offset' => $offset,
            'number' => $per_page,
        );
		if($_GET['submit_filter_pdf_export']){
			$args['number'] = '-1'; 
			$args['offset'] = '0'; 
			$args['per_page'] = '0'; 
		}
        if ( isset( $_GET['orderby'] ) && isset( $_GET['order'] ) ) {
            $args['orderby'] = $_GET['orderby'];
            $args['order']   = $_GET['order'] ;
        }

        // search params
        if ( isset( $_GET['start_date'] ) && !empty( $_GET['start_date'] ) ) {
            $args['start_date'] = $_GET['start_date'];
        }

        if ( isset( $_GET['end_date'] ) && !empty( $_GET['end_date'] ) ) {
            $args['end_date'] = $_GET['end_date'];
        }

        if ( isset( $_GET['form_type'] ) && ! empty( $_GET['form_type'] ) ) {
            if ( $_GET['form_type'] == 'deleted' ) {
                $args['status'] = $_GET['form_type'];
            } else {
                $args['form_type'] = $_GET['form_type'];
            }
        }


        if ( isset( $_GET['section'] ) ) {
            $args['status']  = str_replace('-', '_', $_GET['section'] );
        }


			if ( isset( $_GET['people_user_id'] ) && ! empty( $_GET['people_user_id'] ) ) {
				$args['people_user_id'] = $_GET['people_user_id'];
			}			
			

			if ( isset( $_GET['submit_filter_export'] ) && ! empty( $_GET['submit_filter_export'] ) ) {
				$args['submit_filter_export'] = $_GET['submit_filter_export'];
			}	
			
			if ( isset( $_GET['people_user_id'] ) && ! empty( $_GET['people_user_id'] ) ) {
            $this->ledger_id = false;

            $this->items = erp_ac_get_ledger_transactions( $args, 'invoices' );

            $total_count = $this->items['count'];
            //unset( $this->items['count'] );
            //unset( $this->items['totalcredit'] );
            
			$start_date = empty( $args['start_date'] ) ? date( 'Y-m-d', strtotime( erp_financial_start_date() ) ) : $args['start_date'];

			$closing    = erp_ac_get_opening_ledger( 'invoices', $start_date, $args['people_user_id']);
            $pagination = erp_ac_get_ledger_opening_pagination( $offset, 'invoices', $args );
			
            $closing_balance             = new \stdClass();
            $closing_balance->issue_date = sprintf( '<strong>%s</strong>',__( 'Opening Balance', 'erp' ) );
            $closing_balance->form_type  = '&#8212';
            $closing_balance->inv_title  = '&#8212';
            $closing_balance->ref        = '&#8212';
            $closing_balance->debit      = floatval( $closing->debit + $pagination['debit'] );
            $closing_balance->credit     = floatval( $closing->credit + $pagination['credit'] );
            $closing_balance->is_opening = true;
			
			$total_balance             = new \stdClass();
            $total_balance->issue_date = sprintf( '<strong>%s</strong>',__( 'Total Debit', 'erp' ) );
            $total_balance->form_type  = '&#8212';
            $total_balance->ref        = '&nbsp;';			
            $total_balance->inv_title  = sprintf( '<strong>%s</strong>',__( 'Total Credit', 'erp' ) );

            $total_balance->debit      = '&nbsp;';
            $total_balance->credit     = sprintf( '<strong>%s</strong>',__( 'Total Balance', 'erp' ) );
            $total_balance->summary     = '&#8212';
            $total_balance->is_total = true;

            array_unshift( $this->items, $closing_balance );
            array_push( $this->items, $total_balance );
/*
        } else {
            $args['type'] = $this->type;
            $this->items = $this->get_transactions( $args );
        }*/
		}
        $this->set_pagination_args( array(
            'total_items' => $total_count,
            'per_page'    => $per_page
        ) );

    }

}
