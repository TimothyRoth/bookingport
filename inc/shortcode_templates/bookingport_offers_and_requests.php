<?php

$expiredOfferArgs = [
    'post_type' => BOOKINGPORT_CptMarket::$Cpt_Market,
    'posts_per_page' => -1,
    'meta_query' => [
        'post_status' => 'draft',
        'key' => BOOKINGPORT_CptMarket::$Cpt_MarketStatus,
        'value' => BOOKINGPORT_CptMarket::$Cpt_MarketStatusExpired,
        'compare' => '='
    ]
];

$expiredOfferArgs = new WP_Query($expiredOfferArgs);

