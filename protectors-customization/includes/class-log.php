<?php
namespace IMPAKTT_Customization_Protectors\ERP\CRM\Deals;

use WeDevs\ERP\Framework\Traits\Hooker;
use IMPAKTT_Customization_Protectors\ERP\CRM\Deals\Helpers;

/**
 * Deal audit log
 *
 * @since 1.0.0
 */
class Log {

    use Hooker;

    private $special_field_vals = [];

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
     * The class constructor
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function __construct() {
        //$this->action( 'erp_deals_delete_note', 'erp_deals_delete_note' );
    }

    /**
     * Store audit log
     *
     * @since 1.0.0
     *
     * @param array $args
     *
     * @return void
     */
    public function audit_log( $args ) {
        // the message field is required
        if ( empty( $args['message'] ) ) {
            return;
        }

        $defaults = [
            'component'     => 'CRM',
            'sub_component' => 'Deals',
            'changetype'    => 'add',
            'created_by'    => get_current_user_id(),
        ];


        $args = wp_parse_args( $args, $defaults );

        if ( !empty( $args['old_value'] ) ) {
            $args['old_value'] = base64_encode( maybe_serialize( $args['old_value'] ) );
        }

        if ( !empty( $args['new_value'] ) ) {
            $args['new_value'] = base64_encode( maybe_serialize( $args['new_value'] ) );
        }

        erp_log()->insert_log( $args );
    }

    /**
     * Log after delete a note
     *
     * @since 1.0.0
     *
     * @param object $note Eloquent Note model
     *
     * @return void
     */
    public function erp_deals_delete_note( $note ) {
        $deal = $note->deal;

        $link = Helpers::admin_url( [ 'action' => 'view-deal', 'id' => $deal->id ] );
        $title = $deal->title;
        $change_type = 'delete';
        $change_msg = __( 'Deleted note', 'erp-deals' );

        $args = [
            'data_id'       => $deal->id,
            'changetype'    => 'edit',
            'message'       => sprintf( __( '<span data-type="deal">Updated</span> <a href="%s" target="_blank">%s</a> - <span data-sub-changes="%s">%s</span>', 'erp-deals' ), $link, $title, $change_type, $change_msg ),
            'old_value'     => [ 'note' => $note->note ],
            'new_value'     => [ 'note' => null ],
        ];

        $this->audit_log( $args );
    }

}

/**
 * Class instance
 *
 * @since 1.0.0
 *
 * @return object
 */
function audit_log() {
    return Log::instance();
}

// Make an instance immediately when include this file,
// so that the hook will be activated
audit_log();
