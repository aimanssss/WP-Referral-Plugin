<?php
namespace Kibgim\Referrals\Model;

/**
 * Campaign model.
 */
class Campaign {
    public $id;
    public $name;
    public $slug;
    public $tz;
    public $start_date;
    public $end_date;
    public $rate;
    public $type;
    public $cookie_days;
    public $status;

    public static function get( $id ) {
        global $wpdb;
        $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}kgr_campaigns WHERE id = %d", $id ) );
        return $row ? self::from_row( $row ) : null;
    }

    /**
     * Return current active campaign.
     */
    public static function current() {
        global $wpdb;
        $now = current_time( 'mysql' );
        $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}kgr_campaigns WHERE status = 'active' AND start_date <= %s AND end_date >= %s ORDER BY id DESC LIMIT 1", $now, $now ) );
        return $row ? self::from_row( $row ) : null;
    }

    /**
     * Check if campaign active.
     */
    public function is_active() {
        $now = current_time( 'timestamp', true );
        $tz  = new \DateTimeZone( $this->tz );
        $start = ( new \DateTime( $this->start_date, $tz ) )->getTimestamp();
        $end   = ( new \DateTime( $this->end_date, $tz ) )->getTimestamp();
        return 'active' === $this->status && $now >= $start && $now <= $end;
    }

    protected static function from_row( $row ) {
        $obj = new self();
        foreach ( get_object_vars( $row ) as $key => $value ) {
            $obj->$key = $value;
        }
        return $obj;
    }
}
