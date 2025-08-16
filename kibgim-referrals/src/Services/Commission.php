<?php
namespace Kibgim\Referrals\Services;

use Kibgim\Referrals\Model\Affiliate;
use Kibgim\Referrals\Model\Campaign;

/**
 * Commission calculation.
 */
class Commission {
    /**
     * Calculate commission for WooCommerce order.
     *
     * @param Affiliate $affiliate Affiliate.
     * @param Campaign  $campaign  Campaign.
     * @param int       $order_id  Order ID.
     *
     * @return array { amount, commission }
     */
    public static function calculate_for_order( Affiliate $affiliate, Campaign $campaign, $order_id ) {
        if ( ! function_exists( 'wc_get_order' ) ) {
            return [ 'amount' => 0, 'commission' => 0 ];
        }
        $order = wc_get_order( $order_id );
        if ( ! $order ) {
            return [ 'amount' => 0, 'commission' => 0 ];
        }
        $amount = (float) $order->get_subtotal();
        $commission = 'percent' === $campaign->type ? ( $amount * ( (float) $campaign->rate / 100 ) ) : (float) $campaign->rate;
        $commission = apply_filters( 'kgr_calculate_commission', $commission, $affiliate, $campaign, $order );
        return [ 'amount' => $amount, 'commission' => $commission ];
    }
}
