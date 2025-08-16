<?php
namespace Kibgim\Referrals\Services;

use Kibgim\Referrals\Model\Affiliate;
use Kibgim\Referrals\Model\Campaign;
use Kibgim\Referrals\Services\Commission;

/**
 * Main plugin bootstrapper.
 */
class Plugin {
    /**
     * Singleton instance.
     *
     * @var Plugin
     */
    protected static $instance;

    /**
     * Get instance.
     *
     * @return Plugin
     */
    public static function instance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor.
     */
    private function __construct() {
        $this->setup();
    }

    /**
     * Setup hooks.
     */
    protected function setup() {
        add_action( 'init', [ $this, 'register_shortcodes' ] );
        add_action( 'init', [ $this, 'track_referral' ] );

        // WooCommerce integration hook.
        add_action( 'woocommerce_thankyou', [ $this, 'handle_order' ], 10, 1 );

        // REST routes.
        add_action( 'rest_api_init', [ $this, 'register_rest' ] );
    }

    /**
     * Register shortcodes.
     */
    public function register_shortcodes() {
        add_shortcode( 'kgr_affiliate_optin', [ $this, 'affiliate_optin_shortcode' ] );
        add_shortcode( 'kgr_affiliate_dashboard', [ $this, 'affiliate_dashboard_shortcode' ] );
    }

    /**
     * Opt-in shortcode.
     */
    public function affiliate_optin_shortcode() {
        if ( ! is_user_logged_in() ) {
            return '<p>' . esc_html__( 'You must be logged in to apply.', 'kibgim-referrals' ) . '</p>';
        }
        $user_id = get_current_user_id();
        $affiliate = Affiliate::get_by_user( $user_id );
        if ( $affiliate ) {
            return '<p>' . esc_html__( 'You are already an affiliate.', 'kibgim-referrals' ) . '</p>';
        }
        if ( isset( $_POST['kgr_optin_nonce'] ) && wp_verify_nonce( wp_unslash( $_POST['kgr_optin_nonce'] ), 'kgr_optin' ) ) {
            Affiliate::create( $user_id );
            return '<p>' . esc_html__( 'Request sent. Await approval.', 'kibgim-referrals' ) . '</p>';
        }
        $html  = '<form method="post">';
        $html .= wp_nonce_field( 'kgr_optin', 'kgr_optin_nonce', true, false );
        $html .= '<button type="submit">' . esc_html__( 'Become Affiliate', 'kibgim-referrals' ) . '</button>';
        $html .= '</form>';
        return $html;
    }

    /**
     * Affiliate dashboard shortcode.
     */
    public function affiliate_dashboard_shortcode() {
        if ( ! is_user_logged_in() ) {
            return '<p>' . esc_html__( 'Please log in.', 'kibgim-referrals' ) . '</p>';
        }
        $user_id = get_current_user_id();
        $aff     = Affiliate::get_by_user( $user_id );
        if ( ! $aff ) {
            return '<p>' . esc_html__( 'Not an affiliate yet.', 'kibgim-referrals' ) . '</p>';
        }
        $link = add_query_arg( 'ref', $aff->code, home_url( '/' ) );
        $earnings = $aff->get_balance();
        $html  = '<div class="kgr-dashboard">';
        $html .= '<p>' . sprintf( esc_html__( 'Your referral link: %s', 'kibgim-referrals' ), esc_url( $link ) ) . '</p>';
        $price = function_exists( 'wc_price' ) ? wc_price( $earnings ) : number_format_i18n( $earnings, 2 );
        $html .= '<p>' . sprintf( esc_html__( 'Balance: %s', 'kibgim-referrals' ), esc_html( $price ) ) . '</p>';
        $html .= '</div>';
        return $html;
    }

    /**
     * Track referral clicks via ?ref=.
     */
    public function track_referral() {
        if ( isset( $_GET['ref'] ) ) {
            $code = sanitize_text_field( wp_unslash( $_GET['ref'] ) );
            $aff  = Affiliate::get_by_code( $code );
            if ( $aff ) {
                $campaign = Campaign::current();
                if ( $campaign ) {
                    // Store cookie.
                    $days = (int) $campaign->cookie_days;
                    setcookie( 'kgr_ref', $aff->id . ':' . $campaign->id, time() + DAY_IN_SECONDS * $days, COOKIEPATH, COOKIE_DOMAIN );

                    // Log click.
                    $aff->log_click( $campaign->id );
                }
            }
        }
    }

    /**
     * Handle WooCommerce order creation.
     *
     * @param int $order_id Order ID.
     */
    public function handle_order( $order_id ) {
        if ( empty( $_COOKIE['kgr_ref'] ) ) {
            return;
        }
        list( $aff_id, $campaign_id ) = array_map( 'intval', explode( ':', $_COOKIE['kgr_ref'] ) );
        $affiliate = Affiliate::get( $aff_id );
        $campaign  = Campaign::get( $campaign_id );
        if ( ! $affiliate || ! $campaign ) {
            return;
        }
        // Commission calculation based on order subtotal.
        $commission = Commission::calculate_for_order( $affiliate, $campaign, $order_id );
        $affiliate->add_referral( $campaign_id, $order_id, $commission['amount'], $commission['commission'] );
    }

    /**
     * Register REST routes.
     */
    public function register_rest() {
        $controller = new \Kibgim\Referrals\Rest\Controller();
        $controller->register_routes();
    }
}
