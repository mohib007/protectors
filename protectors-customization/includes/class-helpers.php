<?php
namespace IMPAKTT_Customization_Protectors\ERP\CRM\Deals;

/**
 * Helpers class
 *
 * Class contains miscellaneous helper methods
 *
 * @since 1.0.0
 */
class Helpers {

    /**
     * Build admin url
     *
     * @since 1.0.0
     *
     * @param array  $queries
     * @param string $page
     * @param string $base
     *
     * @return string WP Admin url
     */
    public static function admin_url( $queries = [], $page = 'erp-deals', $base = 'admin.php' ) {
        $queries = [ 'page' => $page ] + $queries;

        $query_string = http_build_query( $queries );

        return admin_url( $base . '?' . $query_string );
    }

}
