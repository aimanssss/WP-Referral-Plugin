<?php
use Kibgim\Referrals\Model\Affiliate;

autoload_affiliate();

function autoload_affiliate() {}

class AffiliateTest extends \PHPUnit\Framework\TestCase {
    public function test_create_affiliate() {
        global $wpdb;
        $aff = Affiliate::create( 1 );
        $this->assertNotEmpty( $aff->code );
        $stored = Affiliate::get_by_user( 1 );
        $this->assertEquals( $aff->code, $stored->code );
    }
}
