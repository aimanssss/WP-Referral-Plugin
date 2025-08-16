<?php
use Kibgim\Referrals\Model\Campaign;

class CampaignTest extends \PHPUnit\Framework\TestCase {
    public function test_campaign_active() {
        $camp = new Campaign();
        $camp->status = 'active';
        $camp->tz = 'UTC';
        $camp->start_date = date( 'Y-m-d H:i:s', time() - 3600 );
        $camp->end_date   = date( 'Y-m-d H:i:s', time() + 3600 );
        $this->assertTrue( $camp->is_active() );

        $camp->end_date   = date( 'Y-m-d H:i:s', time() - 10 );
        $this->assertFalse( $camp->is_active() );
    }
}
