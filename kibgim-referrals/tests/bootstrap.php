<?php
require_once __DIR__ . '/../kibgim-referrals.php';

// Basic stubs for WP functions used in tests.
function wp_generate_password( $length = 12, $special = true, $extra = true ) {
    return substr( md5( uniqid( '', true ) ), 0, $length );
}
function current_time( $type, $gmt = 0 ) {
    if ( 'mysql' === $type ) {
        return date( 'Y-m-d H:i:s', $gmt ? time() : time() );
    }
    return time();
}
function wp_hash( $data ) {
    return hash( 'sha256', $data );
}
function wp_privacy_anonymize_ip( $ip ) { return $ip; }
function esc_url_raw( $url ) { return $url; }
function esc_html__( $text, $domain = null ) { return $text; }
function sanitize_text_field( $text ) { return $text; }
function add_action() {}
function add_shortcode() {}
function register_rest_route() {}
function apply_filters( $tag, $value ) { return $value; }
function is_user_logged_in() { return false; }
function get_current_user_id() { return 0; }
function home_url( $path = '/' ) { return 'http://example.com' . $path; }
function add_query_arg( $key, $value, $url ) { return $url . '?' . $key . '=' . $value; }
function wp_nonce_field( $action, $name, $referer, $echo ) { return '<input type="hidden" name="' . $name . '" value="nonce" />'; }
function wp_verify_nonce( $nonce, $action ) { return true; }
function wp_unslash( $value ) { return $value; }
function number_format_i18n( $number, $decimals ) { return number_format( $number, $decimals ); }

// Simple $wpdb stub.
class WPDB_Stub {
    public $prefix = 'wp_';
    public $insert_id = 0;
    private $tables = [];

    public function insert( $table, $data ) {
        $this->insert_id++;
        $data['id'] = $this->insert_id;
        $this->tables[ $table ][ $this->insert_id ] = (object) $data;
    }
    public function get_row( $query ) {
        preg_match( '/FROM\s+(\w+)/', $query, $matches );
        $table = $matches[1] ?? '';
        if ( empty( $this->tables[ $table ] ) ) {
            return null;
        }
        preg_match( '/WHERE\s+(\w+)\s*=\s*([\'\"]?)([^\'\"\s]+)\2/', $query, $matches );
        $col = $matches[1] ?? 'id';
        $val = trim( $matches[3] ?? '' );
        foreach ( $this->tables[ $table ] as $row ) {
            if ( (string) $row->$col === $val ) {
                return $row;
            }
        }
        return null;
    }
    public function get_var( $query ) {
        preg_match( '/SUM\((\w+)\).*WHERE\s+(\w+)\s*=\s*(\d+)/', $query, $m );
        $sum_field = $m[1];
        $where_col = $m[2];
        $id        = $m[3];
        preg_match( '/FROM\s+(\w+)/', $query, $tbl );
        $table = $tbl[1];
        $sum = 0;
        if ( isset( $this->tables[ $table ] ) ) {
            foreach ( $this->tables[ $table ] as $row ) {
                if ( $row->$where_col == $id ) {
                    $sum += $row->$sum_field;
                }
            }
        }
        return $sum;
    }
    public function prepare( $query, ...$args ) {
        foreach ( $args as &$a ) {
            if ( is_numeric( $a ) ) {
                $a = (int) $a;
            }
        }
        return vsprintf( str_replace( [ '%d', '%s' ], [ '%u', '%s' ], $query ), $args );
    }
}

global $wpdb;
$wpdb = new WPDB_Stub();
