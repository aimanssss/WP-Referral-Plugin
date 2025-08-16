<?php
use Kibgim\Referrals\Services\Commission;
use Kibgim\Referrals\Model\Campaign;
use Kibgim\Referrals\Model\Affiliate;

class CommissionTest extends \PHPUnit\Framework\TestCase {
    public function test_calculate_percent_commission() {
        $affiliate = new Affiliate();
        $campaign = new Campaign();
        $campaign->rate = 10;
        $campaign->type = 'percent';
        $order = new class {
            public function get_subtotal() { return 100; }
        };
        function wc_get_order( $id ) { return new class {
            public function get_subtotal() { return 100; }
        }; }
        $result = Commission::calculate_for_order( $affiliate, $campaign, 1 );
        $this->assertEquals( 10, $result['commission'] );
    }
}
