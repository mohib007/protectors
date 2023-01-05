<?php
/**
Plugin Name: Customization Protectors
Plugin URI: https://impaktt.com
Description: Customization required by the Protectors in IMPAKTT
Version: 1
Author: IMPAKTT
Author URI: https://impaktt.com
Text Domain: impaktt
**/

// don't call the file directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Deals plugin main class
 *
 * @since 1.0.0
 */
class IMPAKTT_Customization_Protectors {

	/**
	 * Add-on Version
	 *
	 * @var  string
	 */
	public $version = '1.0.0';

    /**
     * Initializes the class
     *
     * Checks for an existing instance
     * and if it doesn't find one, creates it.
     *
     * @since 1.0.0
     *
     * @return object Class instance
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new self();
        }

        return $instance;
    }

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

        // on plugin register hook
        register_activation_hook( __FILE__, [ $this, 'activate' ] );

        // on plugin deactivation hook
        register_deactivation_hook( __FILE__, [ $this, 'deactivate' ] );

        // plugin not installed - notice
        add_action( 'plugins_loaded', [ $this, 'plugins_loaded' ] );

    }


    /**
     * Plugins loaded hook
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function plugins_loaded() {

		//protectors customization loaded
		$this->erp_protectors_loaded();
		
        // plugin not installed - notice
        add_action( 'admin_notices', [ $this, 'admin_notice' ] );
    }

    /**
     * Display an error message if WP ERP is not active
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function admin_notice() {
        if ( !class_exists( 'WeDevs_ERP' ) ) {
            printf(
                '%s'. __( '<strong>Error:</strong> <a href="%s">IMAPKTT ERP</a> Plugin is required to use Customization plugin.', 'impaktt-erp' ) . '%s',
                '<div class="message error"><p>',
                'https://impaktt.com',
                '</p></div>'
            );
        }
    }


    /**
     * Executes during plugin activation
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function activate() {
        if ( !class_exists( 'WeDevs_ERP' ) ) {
            require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
            deactivate_plugins( plugin_basename( __FILE__ ) );
            wp_die( __( 'You need to install IMPAKTT ERP main plugin to use this addon', 'imapktt-erp' ) );
        }

    }

    /**
     * Executes during plugin deactivation
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function deactivate() {
    }

    /**
     * Placeholder for creating tables while activating plugin
     *
     * @since 1.0.0
     *
     * @return void
     */
    private function create_table() {

        global $wpdb;

        $collate = '';

        if ( $wpdb->has_cap( 'collation' ) ) {
            if ( ! empty($wpdb->charset ) ) {
                $collate .= "DEFAULT CHARACTER SET $wpdb->charset";
            }

            if ( ! empty($wpdb->collate ) ) {
                $collate .= " COLLATE $wpdb->collate";
            }
        }

        //$table_schema = include_once dirname( __FILE__ ) . '/table-schema.php';

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		/*         foreach ( $table_schema as $table ) {

            dbDelta( $table );
        } */
    }

    /**
     * Insert default data for the plugin during installation
     *
     * @since 1.0.0
     *
     * @return void
     */
    private function insert_initial_table_data() {
       // include_once dirname( __FILE__ ) . '/table-data.php';
    }

    
    /**
     * Executes if CRM is installed
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function erp_protectors_loaded() {
        $this->define_constants();
        $this->includes();
    }

    /**
     * Define Add-on constants
     *
     * @since 1.0.0
     *
     * @return void
     */
    private function define_constants() {
        define( 'IMPAKTT_DEALS_VERSION', $this->version );
        define( 'IMPAKTT_DEALS_FILE', __FILE__ );
        define( 'IMPAKTT_DEALS_PATH', dirname( IMPAKTT_DEALS_FILE ) );
        define( 'IMPAKTT_DEALS_INCLUDES', IMPAKTT_DEALS_PATH . '/includes' );
        define( 'IMPAKTT_DEALS_URL', plugins_url( '', IMPAKTT_DEALS_FILE ) );
        define( 'IMPAKTT_DEALS_ASSETS', IMPAKTT_DEALS_URL . '/assets' );
        define( 'IMPAKTT_DEALS_VIEWS', IMPAKTT_DEALS_PATH . '/views' );
    }

    /**
     * Include required files
     *
     * @since 1.0.0
     *
     * @return void
     */
    private function includes() {

        include_once IMPAKTT_DEALS_INCLUDES . '/erp-helper.php';
        include_once IMPAKTT_DEALS_INCLUDES . '/class-helpers.php';
        include_once IMPAKTT_DEALS_INCLUDES . '/class-hooks.php';
        include_once IMPAKTT_DEALS_INCLUDES . '/class-log.php';
        include_once IMPAKTT_DEALS_INCLUDES . '/class-deals.php';

        // admin functionalities
        add_action( 'init', function () {
                include_once IMPAKTT_DEALS_INCLUDES . '/class-admin.php';
            
        if ( isset( $_GET['action'] ) && 'view-deal' === $_GET['action'] ) { // single deal page
            $this->add_admin_scripts();
        }

        });

    }
    function add_admin_scripts() {
        wp_enqueue_script( 'erp-deals-pros', plugin_dir_url(__FILE__) . 'assets/js/deal-page.js', 'jquery', WPERP_DEALS_VERSION, true );
    }
}

IMPAKTT_Customization_Protectors::init();