<?php

$option_table = get_option(BOOKINGPORT_Settings::$option_table);
$market_prefix = $option_table[BOOKINGPORT_Settings::$option_market_prefix];

$expiredOfferArgs = [
    'post_type' => BOOKINGPORT_CptMarket::$Cpt_Market,
    'posts_per_page' => -1,
    'post_status' => 'draft',
    'meta_query' => [
        [
            'key' => BOOKINGPORT_CptMarket::$Cpt_MarketStatus,
            'value' => BOOKINGPORT_CptMarket::$Cpt_MarketStatusExpired,
            'compare' => '='
        ]
    ]
];

$expiredOffers = new WP_Query($expiredOfferArgs); ?>
<div class="wrapper">
    <h1 class="page-headline"><?php the_title() ?></h1>
    <div class="search-filter">
        <input name="filter_expired_offers" type="text" class="search-stand-filter" placeholder="Suche...">
    </div>
    <div class="expired-offers-container">
        <?php if ($expiredOffers->found_posts === 0) { ?>
            <p>Derzeit gibt es keine abgelaufenen Angebote</p>
        <?php } ?>

        <?php foreach ($expiredOffers->posts ?? [] as $expiredOffer) {

            $ID = $expiredOffer->ID;
            $customer_ID = get_post_meta($ID, BOOKINGPORT_CptMarket::$CPT_MarketUserID, true);
            $offer_customer = get_user_by('id', $customer_ID);
            $customer_name = $offer_customer->billing_first_name . ' ' . $offer_customer->billing_last_name;
            $customer_email = $offer_customer->user_email;
            $customer_phone = $offer_customer->billing_phone;
            $offer_price = get_post_meta($ID, BOOKINGPORT_CptMarket::$CPT_MarketPrice, true);
            $offer_width = get_post_meta($ID, BOOKINGPORT_CptMarket::$CPT_MarketWidth, true);
            $offer_depth = get_post_meta($ID, BOOKINGPORT_CptMarket::$CPT_MarketDepth, true);
            $offer_pavillon_confirmation = get_post_meta($ID, BOOKINGPORT_CptMarket::$CPT_MarketPavillonConfirmation, true);
            $offered_stands_array = get_post_meta($ID, BOOKINGPORT_CptMarket::$CPT_MarketStands, true);
            $stands = [];
            $standIds = [];

            foreach ($offered_stands_array ?? [] as $singleStand) {
                $stands[] = get_the_title($singleStand);
                $standIds[] = $singleStand;
            } ?>

            <div class="single-offer">

                <h3 class="single-offer-title"><?php echo $market_prefix . $ID ?></h3>

                <div class="row single-offer-customer-name">
                    <img src="<?= BOOKINGPORT_PLUGIN_URI ?>/assets/images/icons/customers/customer-blue.svg"
                    <p><?php echo $customer_name ?></p>
                </div>

                <div class="row single-offer-customer-email">
                    <img src="<?= BOOKINGPORT_PLUGIN_URI ?>/assets/images/icons/customers/mail-blue.svg">
                    <a href="mailto:<?= $customer_email ?>"><?php echo $customer_email ?></a>
                </div>

                <div class="row single-offer-customer-phone">
                    <img src="<?= BOOKINGPORT_PLUGIN_URI ?>/assets/images/icons/customers/phone-blue.svg">
                    <a href="tel:<?= $customer_phone ?>"><?php echo $customer_phone ?></a>
                </div>

                <?php $counter = 0;
                foreach ($stands ?? [] as $stand) { ?>
                    <div class="row single-offer-stand">
                        <img src="<?= BOOKINGPORT_PLUGIN_URI ?>/assets/images/icons/stand-cat-blue.svg">
                        <p class="single-offer-position" id="<?= $standIds[$counter] ?>"><?= $stand ?></p>
                    </div>
                    <?php $counter++;
                } ?>

                <div class="row single-offer-stand-dimensions">
                    <img src="<?= BOOKINGPORT_PLUGIN_URI ?>/assets/images/icons/stands/space-blue.svg">
                    <div>
                        <p>Breite: <?= $offer_width ?>m</p>
                        <?php if (!empty($offer_depth)) { ?>
                            <p>Tiefe: <?= $offer_depth ?>m</p>
                        <?php } ?>
                    </div>

                </div>

                <div class="row single-offer-pavillon">
                    <img src="<?= BOOKINGPORT_PLUGIN_URI ?>/assets/images/icons/stands/pavillon-blue.svg">
                    <p><?php echo ($offer_pavillon_confirmation == "true") ? 'Pavillon genehmigt' : 'Kein Pavillon'; ?></p>
                </div>

                <div class="seperator"></div>

                <div class="row single-offer-price">
                    <p><strong>Angebotspreis:</strong> <?= $offer_price ?> €</p>
                </div>

                <div class="button-row">
                    <div class="btn-primary btn trigger-modal open-reactivate-offer-modal" id="reactivate-offer">Angebot erneut
                        ausstellen
                    </div>
                    <div class="btn-tertiary btn trigger-modal open-delete-offer-modal" id="delete-offer">Angebot löschen</div>
                </div>

                <div class="reactivate-offer-modal modal">
                    <div class="inner-modal">
                        <h3>Sind Sie sicher, dass Sie das Angebot <?= $market_prefix . $ID ?> erneut ausstellen wollen?</h3>
                        <div class="button-row">
                            <div data-src="<?= $ID ?>" class="btn-primary btn reactivate-offer">Angebot
                                erneut ausstellen
                            </div>
                            <div class="btn-secondary btn back-to-offer close-modal">Zurück</div>
                        </div>
                    </div>
                </div>

                <div class="delete-offer-modal modal">
                    <div class="inner-modal">
                        <h3>Sind Sie sicher, dass Sie das Angebot <?= $market_prefix . $ID ?> löschen wollen?</h3>
                        <div class="button-row">
                            <div class="btn-primary btn delete-offer" data-src="<?= $ID ?>">Angebot
                                löschen
                            </div>
                            <div class="btn-secondary btn back-to-offer close-modal">Zurück</div>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
</div>

