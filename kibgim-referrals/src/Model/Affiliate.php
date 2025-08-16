<?php
namespace Kibgim\Referrals\Model;

/**
 * Affiliate model.
 */
class Affiliate {
    public $id;
    public $user_id;
    public $code;
    public $status;

    /**
     * Get affiliate by ID.
     */
    public static function get( $id ) {
        global $wpdb;
        $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}kgr_affiliates WHERE id = %d", $id ) );
        return $row ? self::from_row( $row ) : null;
    }

    /**
     * Get by user.
     */
    public static function get_by_user( $user_id ) {
        global $wpdb;
        $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}kgr_affiliates WHERE user_id = %d", $user_id ) );
        return $row ? self::from_row( $row ) : null;
    }

    /**
     * Get by code.
     */
    public static function get_by_code( $code ) {
        global $wpdb;
        $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}kgr_affiliates WHERE code = %s", $code ) );
        return $row ? self::from_row( $row ) : null;
    }

    /**
     * Create affiliate.
     */
    public static function create( $user_id ) {
        global $wpdb;
        $code = strtolower( wp_generate_password( 8, false, false ) );
        $wpdb->insert( $wpdb->prefix . 'kgr_affiliates', [
            'user_id'    => $user_id,
            'code'       => $code,
            'status'     => 'pending',
            'created_at' => current_time( 'mysql' ),
        ] );
        return self::get( $wpdb->insert_id );
    }

    /**
     * Add referral.
     */
    public function add_referral( $campaign_id, $order_id, $amount, $commission ) {
        global $wpdb;
        $wpdb->insert( $wpdb->prefix . 'kgr_referrals', [
            'affiliate_id' => $this->id,
            'campaign_id'  => $campaign_id,
            'order_id'     => $order_id,
            'amount'       => $amount,
            'commission'   => $commission,
            'status'       => 'pending',
            'created_at'   => current_time( 'mysql' ),
        ] );
    }

    /**
     * Log click.
     */
    public function log_click( $campaign_id ) {
        global $wpdb;
        $ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? wp_privacy_anonymize_ip( $_SERVER['REMOTE_ADDR'] ) : '';
        $ua  = isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : '';
        $wpdb->insert( $wpdb->prefix . 'kgr_clicks', [
            'affiliate_id' => $this->id,
            'campaign_id'  => $campaign_id,
            'ip_hash'      => wp_hash( $ip ),
            'ua_hash'      => wp_hash( $ua ),
            'url'          => esc_url_raw( $_SERVER['REQUEST_URI'] ?? '' ),
            'created_at'   => current_time( 'mysql' ),
        ] );
    }

    /**
     * Get balance.
     */
    public function get_balance() {
        global $wpdb;
        return (float) $wpdb->get_var( $wpdb->prepare( "SELECT SUM(commission) FROM {$wpdb->prefix}kgr_referrals WHERE affiliate_id = %d AND status != 'rejected'", $this->id ) );
    }

    /**
     * Create from db row.
     */
    protected static function from_row( $row ) {
        $obj = new self();
        foreach ( get_object_vars( $row ) as $key => $value ) {
            $obj->$key = $value;
        }
        return $obj;
    }
}
