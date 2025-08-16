<?php
namespace Kibgim\Referrals\Rest;

use WP_REST_Controller;
use WP_REST_Server;
use Kibgim\Referrals\Model\Affiliate;
use Kibgim\Referrals\Model\Campaign;
use Kibgim\Referrals\Services\Commission;

/**
 * REST API controller.
 */
class Controller extends WP_REST_Controller {
    /**
     * Register routes.
     */
    public function register_routes() {
        register_rest_route( 'kgr/v1', '/track-click', [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => [ $this, 'track_click' ],
            'permission_callback' => '__return_true',
        ] );

        register_rest_route( 'kgr/v1', '/conversion', [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => [ $this, 'conversion' ],
            'permission_callback' => '__return_true',
        ] );

        register_rest_route( 'kgr/v1', '/me', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [ $this, 'me' ],
            'permission_callback' => function () {
                return is_user_logged_in();
            },
        ] );
    }

    public function track_click( $request ) {
        $code = sanitize_text_field( $request->get_param( 'code' ) );
        $aff  = Affiliate::get_by_code( $code );
        if ( ! $aff ) {
            return new \WP_Error( 'not_found', 'Affiliate not found', [ 'status' => 404 ] );
        }
        $campaign = Campaign::current();
        if ( ! $campaign ) {
            return new \WP_Error( 'no_campaign', 'No active campaign', [ 'status' => 400 ] );
        }
        $aff->log_click( $campaign->id );
        return [ 'success' => true ];
    }

    public function conversion( $request ) {
        $code     = sanitize_text_field( $request->get_param( 'code' ) );
        $order_id = absint( $request->get_param( 'order_id' ) );
        $aff = Affiliate::get_by_code( $code );
        $campaign = Campaign::current();
        if ( ! $aff || ! $campaign ) {
            return new \WP_Error( 'invalid', 'Invalid data', [ 'status' => 400 ] );
        }
        $commission = Commission::calculate_for_order( $aff, $campaign, $order_id );
        $aff->add_referral( $campaign->id, $order_id, $commission['amount'], $commission['commission'] );
        do_action( 'kgr_after_create_referral', $aff, $order_id );
        return [ 'success' => true ];
    }

    public function me( $request ) {
        $user_id = get_current_user_id();
        $aff     = Affiliate::get_by_user( $user_id );
        if ( ! $aff ) {
            return new \WP_Error( 'no_affiliate', 'Not an affiliate', [ 'status' => 403 ] );
        }
        return [
            'id'       => $aff->id,
            'code'     => $aff->code,
            'balance'  => $aff->get_balance(),
        ];
    }
}
