<?php
/* if ( ! function_exists( 'erp_crm_get_life_stage' ) ):

    /**
     * @since 1.0.1
     *
     * @param $contact_id
     *
     * @return mixed|string

    function erp_crm_get_life_stage( $contact_id ) {
        $contact = new \WeDevs\ERP\CRM\Contact( $contact_id );
        if ( is_callable( array( $contact, 'get_life_stage' ) ) ) {
            return $contact->get_life_stage();
        } else {
            return erp_people_get_meta( $contact_id, 'life_stage', true );
        }
    }
endif; 

*/
