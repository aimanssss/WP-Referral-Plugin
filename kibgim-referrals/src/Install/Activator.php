<?php
namespace Kibgim\Referrals\Install;

/**
 * Plugin activation tasks.
 */
class Activator {
    /**
     * Run activation tasks.
     */
    public static function activate() {
        global $wpdb;

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $charset = $wpdb->get_charset_collate();

        $tables = [
            "CREATE TABLE {$wpdb->prefix}kgr_affiliates (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                user_id BIGINT UNSIGNED NOT NULL,
                code VARCHAR(40) NOT NULL UNIQUE,
                status VARCHAR(20) NOT NULL DEFAULT 'pending',
                created_at DATETIME NOT NULL,
                updated_at DATETIME NULL,
                PRIMARY KEY (id)
            ) $charset;",
            "CREATE TABLE {$wpdb->prefix}kgr_campaigns (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                name VARCHAR(191) NOT NULL,
                slug VARCHAR(191) NOT NULL UNIQUE,
                tz VARCHAR(40) NOT NULL,
                start_date DATETIME NOT NULL,
                end_date DATETIME NOT NULL,
                rate DECIMAL(10,2) NOT NULL DEFAULT 0,
                type VARCHAR(20) NOT NULL DEFAULT 'percent',
                cookie_days INT NOT NULL DEFAULT 30,
                status VARCHAR(20) NOT NULL DEFAULT 'draft',
                created_at DATETIME NOT NULL,
                updated_at DATETIME NULL,
                PRIMARY KEY (id)
            ) $charset;",
            "CREATE TABLE {$wpdb->prefix}kgr_clicks (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                affiliate_id BIGINT UNSIGNED NOT NULL,
                campaign_id BIGINT UNSIGNED NOT NULL,
                ip_hash CHAR(64) NULL,
                ua_hash CHAR(64) NULL,
                url TEXT NULL,
                created_at DATETIME NOT NULL,
                PRIMARY KEY (id),
                KEY affiliate_id (affiliate_id)
            ) $charset;",
            "CREATE TABLE {$wpdb->prefix}kgr_referrals (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                affiliate_id BIGINT UNSIGNED NOT NULL,
                campaign_id BIGINT UNSIGNED NOT NULL,
                order_id BIGINT UNSIGNED NULL,
                amount DECIMAL(10,2) NOT NULL DEFAULT 0,
                commission DECIMAL(10,2) NOT NULL DEFAULT 0,
                status VARCHAR(20) NOT NULL DEFAULT 'pending',
                created_at DATETIME NOT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY order_affiliate (order_id, affiliate_id)
            ) $charset;",
            "CREATE TABLE {$wpdb->prefix}kgr_payouts (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                affiliate_id BIGINT UNSIGNED NOT NULL,
                amount DECIMAL(10,2) NOT NULL DEFAULT 0,
                status VARCHAR(20) NOT NULL DEFAULT 'pending',
                notes TEXT NULL,
                created_at DATETIME NOT NULL,
                paid_at DATETIME NULL,
                PRIMARY KEY (id)
            ) $charset;",
        ];

        foreach ( $tables as $sql ) {
            dbDelta( $sql );
        }
    }
}
