<?php
namespace IMPAKTT_Customization_Protectors\ERP\CRM\Deals;

use \WeDevs\ORM\Eloquent\Facades\DB;

use IMPAKTT_Customization_Protectors\ERP\CRM\Deals\Helpers;

/**
 * Deals object
 *
 * @since 1.0.0
 */
class Deals {

    /**
     * Current user id
     *
     * @var integer
     */
    private $current_user_id = 0;

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
    public static function instance() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new self();
        }

        return $instance;
    }

    /**
     * Class constructor
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function __construct() {
        $this->current_user_id = get_current_user_id();
    }


}

/**
 * Class instance
 *
 * @since 1.0.0
 *
 * @return object
 */
function deals() {
    return Deals::instance();
}
