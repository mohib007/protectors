<?php
namespace IMPAKTT_Customization_Protectors\ERP\CRM\Deals;
use WeDevs\ERP\Framework\Traits\Hooker;
use IMPAKTT_Customization_Protectors\ERP\CRM\Deals\Helpers;

use \WeDevs\ORM\Eloquent\Facades\DB;



/**
 * Class responsible for admin panel functionalities
 *
 * @since 1.0.0
 */
class Admin {

    use Hooker;

    /**
     * Constructor for the class
     *
     * Sets up all the appropriate hooks and actions
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function __construct() {
        $this->includes();
        $this->hooks();
    }

    /**
     * Include the required files
     *
     * @since 1.0.0
     *
     * @return void
     */
    private function includes() {

        include_once IMPAKTT_DEALS_INCLUDES . '/class-ajax.php';
    }


    /**
     * Initializes action hooks to ERP
     *
     * @since 1.0.0
     *
     * @return void
     */
    private function hooks() {
        $this->action( 'admin_print_styles', 'admin_print_styles' );
        $this->action( 'admin_menu', 'admin_menu' );
        $this->action( 'admin_enqueue_scripts', 'admin_scripts' );
    }

    /**
     * Add inline css in wp admin panel
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function admin_print_styles() {
        ?>
            <style>
                .toplevel_page_erp-deals .dashicons-admin-generic:before {
                    font: normal normal normal 16px/1.3 FontAwesome;
                    content: "\f155";
                }
            </style>
        <?php
    }

    /**
     * Add admin panel menu item
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function admin_menu() {
       add_submenu_page( 'erp-deals', __( 'Enquiry Report 2', 'erp-deals' ), __( 'Enquiry Report 2', 'erp-deals' ), 'erp_crm_add_contact', 'erp-deals-reports2', [ $this, 'admin_view_reports2' ] );
       add_submenu_page( 'erp-deals', __( 'Receivable Report 2', 'erp-deals' ), __( 'Receivable Report 2', 'erp-deals' ), 'erp_crm_add_contact', 'erp-deals-reports4', [ $this, 'admin_view_reports4' ] );
       add_submenu_page( 'erp-accounting', __( 'Customer Ledger 2', 'erp-accounting' ), __( 'Customer Ledger 2', 'erp-accounting' ), 'erp_ac_view_reports', 'erp-customer-ledger2', [ $this, 'customer_view_ledger2' ] );
       add_submenu_page( 'erp-accounting', __( 'Vendor Ledger 2', 'erp-accounting' ), __( 'Vendor Ledger 2', 'erp-accounting' ), 'erp_ac_view_reports', 'erp-vendor-ledger2', [ $this, 'vendor_view_ledger2' ] );
    }

    /**
     * Register admin scripts
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function admin_scripts( $hook_suffix ) {
        $time_format = get_option( 'time_format', 'g:i a' );

        $menu = sanitize_title( __( 'Deals', 'erp-deals' ) );
        
    }

    /**
     * Print notices for WordPress
     *
     * @since 1.0.0
     *
     * @param string $text
     * @param string $type
     *
     * @return void
     */
    public function display_notice( $text, $type = 'updated' ) {
        printf( '<div class="%s"><p>%s</p></div>', esc_attr( $type ), $text );
    }

    /**
     * Admin notices
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function admin_notices() {
    }


    /**
     * Deals Admin Page
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function admin_view_deals() {

    }
    

    public function admin_view_reports2() {
        require_once IMPAKTT_DEALS_VIEWS . '/reports2.php';
    }
	
    public function admin_view_reports4() {
        require_once IMPAKTT_DEALS_VIEWS . '/reports4.php';
    }	

    public function customer_view_ledger2() {
        require_once IMPAKTT_DEALS_VIEWS . '/customer-ledger2.php';
    }
	
    public function vendor_view_ledger2() {
        require_once IMPAKTT_DEALS_VIEWS . '/vendor-ledger2.php';
    }	

}

new Admin();
