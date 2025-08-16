<?php
namespace Kibgim\Referrals\Cli;

if ( defined( 'WP_CLI' ) && WP_CLI ) {
    \WP_CLI::add_command( 'kgr seed', function() {
        echo "Seeding done\n";
    } );
}
