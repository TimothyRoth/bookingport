<?php
$user_id = get_current_user_id();
$option_table = get_option(BOOKINGPORT_Settings::$option_table);

$userStandsArgs = [
    'post_type' => BOOKINGPORT_CptStands::$Cpt_Stands,
    'posts_per_page' => -1,
    'meta_query' => [
        'relation' => 'AND',
        [
            'key' => BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralSellStatus,
            'value' => BOOKINGPORT_CptStands::$Cpt_Stand_Status_Sold,
            'compare' => '=',
        ],
        [
            'key' => BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralSellUserId,
            'value' => $user_id,
            'compare' => '=',
        ]
    ]
];

$userStands = new WP_Query($userStandsArgs);
$standCounter = 1;
?>

<div class="wrapper">
    <h1 class="page-headline">Ihre Standbuchungen</h1>
    <?php if ($userStands->found_posts > 0) { ?>
        <h2>Für das Jahr <?= date('Y') ?></h2>
        <div class="user-stands">
            <?php foreach ($userStands->posts ?? [] as $userStand) {
                $id = $userStand->ID;
                $street = get_post_meta($id, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeoStreetname, true);
                $housenumber = get_post_meta($id, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeoStreetNumber, true);
                $number = get_post_meta($id, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralNumber, true);
                ?>
                <div class="single-stand">
                    <div class="upper-row">
                        <p>Stand <?= $standCounter ?></p>
                    </div>
                    <div class="inner-content">
                        <div class="row selected-stand-street">
                            <img class="stand-marker-image"
                                 src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/stands/stand-marker-white.svg">
                            <p class="stand-street"><?= $street . ' ' . $housenumber ?></p>
                        </div>
                        <div class="row selected-stand-number">
                            <img class="stand-number-image"
                                 src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/stands/stand-number-white.svg">
                            <p class="stand-number stand-street">Standnummer: <?= $number ?> </p>
                        </div>
                    </div>
                </div>
                <?php
                $standCounter++;
            } ?>
            <a class="invoice-link" href="/<?= $option_table[BOOKINGPORT_Settings::$option_redirects_invoice] ?>">
                <img src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/info_white.svg">
                Weitere Details können Sie der Rechnung entnehmen
            </a>
        </div>
    <?php } else { ?>
        <div class="no-stands-container">
            <h3>Sie haben für dieses Jahr noch keinen Stand gebucht</h3>
            <a class="btn-primary" href="/<?= $option_table[BOOKINGPORT_Settings::$option_redirects_stand_booking] ?>">Jetzt
                Stand buchen</a>
        </div>
    <?php } ?>
</div>