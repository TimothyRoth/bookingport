<?php

class BOOKINGPORT_MapHandler
{
    public function __construct()
    {
        // Intentionally left blank.
    }

    public static function init(): void
    {
        add_action('wp_ajax_getGoogleMapsStandsList', [__CLASS__, 'getGoogleMapsStandsList']);
        add_action('wp_ajax_nopriv_getGoogleMapsStandsList', [__CLASS__, 'getGoogleMapsStandsList']);

        add_action('wp_ajax_getGoogleMapsStandsList_Admin', [__CLASS__, 'getGoogleMapsStandsList_Admin']);
        add_action('wp_ajax_nopriv_getGoogleMapsStandsList_Admin', [__CLASS__, 'getGoogleMapsStandsList_Admin']);

        add_action('wp_ajax_admin_map_stand_filter', [__CLASS__, 'admin_map_stand_filter']);
        add_action('wp_ajax_nopriv_admin_map_stand_filter', [__CLASS__, 'admin_map_stand_filter']);

        add_action('wp_ajax_get_map_freespace', [__CLASS__, 'get_map_freespace']);
        add_action('wp_ajax_nopriv_get_map_freespace', [__CLASS__, 'get_map_freespace']);
    }

    /**
     * @throws JsonException
     */
    public static function getGoogleMapsStandsList(): void
    {
        $filter = $_POST['filter'];
        $result = [];

        $argsStands = [
            'post_type' => BOOKINGPORT_CptStands::$Cpt_Stands,
            'posts_per_page' => -1,
            'post_status' => 'publish'
        ];

        if (isset($filter) && !empty($filter)) {

            $filter === "accessible" ? $value = 0 : $value = null;

            $argsStands['meta_query'] = [
                [
                    'key' => BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralSellStatus,
                    'value' => $value,
                    'compare' => '=',
                ],
            ];
        }

        $StandsData = new WP_Query($argsStands);

        if ($StandsData->found_posts > 0) {
            foreach ($StandsData->posts ?? [] as $p) {

                $title = get_the_title($p->ID);

                $result[] = [
                    'title' => $title,
                    'post_id' => $p->ID,
                    'lat' => get_post_meta($p->ID, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeoLatitude, true),
                    'lng' => get_post_meta($p->ID, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeoLongitude, true),
                    'stand_status' => get_post_meta($p->ID, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralSellStatus, true)
                ];
            }
        }

        wp_die(json_encode($result, JSON_THROW_ON_ERROR));
    }

    /**
     * @throws JsonException
     */
    public static function getGoogleMapsStandsList_Admin(): void
    {

        $filter = $_POST['filter'];
        $result = [];

        $argsStands = [
            'post_type' => BOOKINGPORT_CptStands::$Cpt_Stands,
            'posts_per_page' => -1,
            'post_status' => 'publish'
        ];

        if (!empty($filter) && $filter !== "all") {

            $value = match ($filter) {
                'accessible' => BOOKINGPORT_CptStands::$Cpt_Stand_Status_Free,
                'booked' => BOOKINGPORT_CptStands::$Cpt_Stand_Status_Sold,
                'admin-reserved-requested' => BOOKINGPORT_CptStands::$Cpt_Stand_Status_Reserved_By_Admin,
                'reserved' => BOOKINGPORT_CptStands::$Cpt_Stand_Status_Requested_By_Customer,
                'expired' => BOOKINGPORT_CptStands::$Cpt_Stand_Status_Admin_Offer_Expired,
                default => null
            };

            $argsStands['meta_query'] = [
                [
                    'key' => BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralSellStatus,
                    'value' => $value,
                    'compare' => '=',
                ],
            ];
        }

        $StandsData = new WP_Query($argsStands);

        if ($StandsData->found_posts > 0) {
            foreach ($StandsData->posts ?? [] as $p) {

                $title = get_the_title($p->ID);

                $result[] = [
                    'title' => $title, 'post_id' => $p->ID,
                    'lat' => get_post_meta($p->ID, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeoLatitude),
                    'lng' => get_post_meta($p->ID, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeoLongitude),
                    'stand_status' => get_post_meta($p->ID, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralSellStatus, true)
                ];
            }
        }

        wp_die(json_encode($result, JSON_THROW_ON_ERROR));
    }

    /**
     * @throws JsonException
     */
    public static function admin_map_stand_filter(): void
    {
        $search = $_POST['search'];
        $dropdown = $_POST['dropdown'];
        $amount = $_POST['amount'];
        $itemID = $_POST['itemID'];
        $html = [];
        $geo = [];

        current_user_can('administrator') ? $is_admin = true : $is_admin = false;

        $args = [
            'post_type' => BOOKINGPORT_CptStands::$Cpt_Stands,
            'post_status' => 'publish',
            'posts_per_page' => $amount,
        ];

        if ($dropdown !== 'all') {

            $value = match ($dropdown) {
                'accessible' => BOOKINGPORT_CptStands::$Cpt_Stand_Status_Free,
                'booked' => BOOKINGPORT_CptStands::$Cpt_Stand_Status_Sold,
                'admin-reserved-requested' => BOOKINGPORT_CptStands::$Cpt_Stand_Status_Reserved_By_Admin,
                'reserved' => BOOKINGPORT_CptStands::$Cpt_Stand_Status_Requested_By_Customer,
                'expired' => BOOKINGPORT_CptStands::$Cpt_Stand_Status_Admin_Offer_Expired,
                default => null
            };

            $args['meta_query'] = [
                [
                    'key' => BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralSellStatus,
                    'value' => $value,
                    'compare' => '=',
                ],
            ];
        }

        if (isset($search) && !empty($search)) {
            $args['meta_query'] = [
                'relation' => 'OR',
                [
                    'key' => BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralSellUserName,
                    'value' => $search,
                    'compare' => 'LIKE',
                ],
                [
                    'key' => BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralNumber,
                    'value' => $search,
                    'compare' => 'LIKE',
                ],
            ];

            // check if the meta query search for either user name or number is returns any results
            // if it doesnt we will perform the 's' query to search through the titles
            $check_if_meta_query_search_is_empty = new WP_Query($args);

            if (empty($check_if_meta_query_search_is_empty->posts)) {
                $args['meta_query'] = null;
                $args['s'] = $search;
            }
        }

        if (isset($itemID) && !empty($itemID)) {
            $args['p'] = $itemID;
        }

        $filteredStands = new WP_Query($args);
        $stand_geo_data = null;

        if ($filteredStands->found_posts > 0) {
            $option_table = get_option(BOOKINGPORT_Settings::$option_table);
            $invoice_page_link = get_home_url() . '/' . $option_table[BOOKINGPORT_Settings::$option_redirects_invoice];
            foreach ($filteredStands->posts ?? [] as $p) {
                $id = $p->ID;
                $has_pavillon = get_post_meta($id, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralPavillon, true);
                $street = get_post_meta($id, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeoStreetname, true);
                $housenumber = get_post_meta($id, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeoStreetNumber, true);
                $current_user_id = get_post_meta($id, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralSellUserId, true);
                $user = get_user_by('ID', $current_user_id);
                $userRole = null;

                if (is_object($user) && isset($user->roles[0])) {
                    $userRole = $user->roles[0];
                }

                $billing_first_name = get_user_meta($current_user_id, 'billing_first_name', true);
                $billing_last_name = get_user_meta($current_user_id, 'billing_last_name', true);
                $user_name = $billing_first_name . ' ' . $billing_last_name;

                $status = match (get_post_meta($id, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralSellStatus, true)) {
                    BOOKINGPORT_CptStands::$Cpt_Stand_Status_Reserved => 'In Reservierung',
                    BOOKINGPORT_CptStands::$Cpt_Stand_Status_Sold => 'Gebucht',
                    BOOKINGPORT_CptStands::$Cpt_Stand_Status_Reserved_By_Admin => 'Vom Admin Reserviert',
                    BOOKINGPORT_CptStands::$Cpt_Stand_Status_Requested_By_Customer => 'Vom Kunden angefragt',
                    BOOKINGPORT_CptStands::$Cpt_Stand_Status_Admin_Offer_Expired => 'Aus abgelaufenem Angebot',
                    default => null,
                };

                $invoice_id = get_post_meta($id, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralInvoiceID, true);

                if (!empty($invoice_id)) {
                    $status .= " <form class='redirect-to-customer-invoices invoice-id' method='POST' action='$invoice_page_link'><input type='hidden' name='invoice_info' value='{$invoice_id}' /><input type='submit' value='(Zur Rechnung)' /></form> ";
                }

                if (!current_user_can('administrator')) {
                    $size = '3m/1 Tapeziertisch';
                }

                $number = get_post_meta($id, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralNumber, true);

                // Here I grab the first from the loop as a reference for the map center, since the first result is the most accurate

                if (empty($stand_geo_data)) {
                    $stand_geo_data = [
                        'lat' => get_post_meta($id, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeoLatitude, true),
                        'lng' => get_post_meta($id, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeoLongitude, true)
                    ];
                }

                ob_start();

                $stand_class = match (get_post_meta($id, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralSellStatus, true)) {
                    BOOKINGPORT_CptStands::$Cpt_Stand_Status_Reserved => 'reserved',
                    BOOKINGPORT_CptStands::$Cpt_Stand_Status_Sold => 'booked',
                    BOOKINGPORT_CptStands::$Cpt_Stand_Status_Reserved_By_Admin => 'admin-reserved-requested',
                    BOOKINGPORT_CptStands::$Cpt_Stand_Status_Requested_By_Customer => 'customer-requested',
                    BOOKINGPORT_CptStands::$Cpt_Stand_Status_Admin_Offer_Expired => 'expired',
                    default => null
                }

                ?>
                <div class="single-result">
                    <div
                            class="inner-content <?= $stand_class ?>">
                        <div class="row selected-stand-street">
                            <img class="stand-marker-image"
                                 src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/stands/stand-marker-blue.svg">
                            <p class="stand-street"><?= $street . ' ' . $housenumber ?></p>
                        </div>
                        <div class="row selected-stand-number">
                            <img class="stand-number-image"
                                 src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/stands/stand-number-blue.svg">
                            <p class="stand-number stand-street">Standnummer: <?= $number ?> </p>
                        </div>
                        <?php if (isset($size)) { ?>
                            <div class="row selected-stand-space">
                                <img class="stand-space-image"
                                     src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/stands/space-blue.svg">
                                <p class="stand-space"><?= $size ?></p>
                            </div>
                        <?php } ?>
                        <?php if ($has_pavillon) { ?>
                            <div class="row selected-stand-pavillon">
                                <img class="stand-pavillon-image"
                                     src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/stands/pavillon-blue.svg">
                                <p class="stand-pavillon">Pavillon möglich</p>
                            </div>
                        <?php } ?>

                        <?php if (!$has_pavillon) { ?>
                            <div class="row selected-stand-pavillon">
                                <img class="stand-pavillon-image"
                                     src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/stands/pavillon-blue.svg">
                                <p class="stand-pavillon">Kein Pavillon möglich</p>
                            </div>
                        <?php } ?>

                        <?php if ($is_admin && get_post_meta($id, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralSellStatus, true) !== BOOKINGPORT_CptStands::$Cpt_Stand_Status_Free) { ?>

                            <div class="row selected-stand-number">
                                <img class="stand-number-image"
                                     src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/customers/stand-cat-blue.svg">
                                <p><?= $status ?></p>
                            </div>
                            <div class="row selected-stand-number">
                                <img class="stand-number-image"
                                     src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/customers/customer-blue.svg">
                                <p><?php echo $user_name . ' (' . ucfirst($userRole) . ')'; ?></p>
                            </div>

                            <div class="row selected-stand-number">
                                <img class="stand-number-image"
                                     src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/customers/mail-blue.svg">
                                <a href="mailto<?= $user->user_email ?>"><?= $user->user_email ?></a>
                            </div>

                            <div class="row selected-stand-number">
                                <img class="stand-number-image"
                                     src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/customers/phone-blue.svg">
                                <a href="tel:<?= $user->billing_phone ?>"><?= $user->billing_phone ?></a>
                            </div>

                            <div class="change-customer">
                                <a class="customer-change-button"
                                   href="<?= get_home_url() ?>/wp-admin/user-edit.php?user_id=<?= $current_user_id ?>"
                                   id="change_customer">
                                    Zur Kundenverwaltung
                                </a>
                            </div>

                            <form class="redirect-to-customer-invoices" method="POST"
                                  action="<?= $invoice_page_link ?>">
                                <input type="hidden" name="invoice_info" value="<?= $current_user_id ?>">
                                <input type="submit" value="Alle Rechnungen des Kunden"/>
                            </form>

                            <div class="btn-tertiary trigger-reset-stand-modal">Stand zurücksetzen</div>

                            <div class="reset-stand-modal modal">
                                <div class="inner-modal">
                                    <h3>Sind Sie sicher, dass sie den Stand <?= $street . ' ' . $housenumber ?> mit der
                                        Standnummer <?= $number ?> zurücksetzen möchten?</h3>
                                    <div class="button-row">
                                        <div class="btn-primary btn reset-stand" id="<?= $id ?>">Stand zurücksetzen
                                        </div>
                                        <div class="btn-secondary btn back-to-offer close-modal">Zurück</div>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
                <?php
                $html[] = ob_get_clean();
            }
            if ($filteredStands->found_posts > $amount) {
                $html[] = '<div class="btn-secondary show-more-stands">Mehr anzeigen</div>';
            }
        } else {
            $html[] = "Leider konnten wir keine Stände finden, die Ihrer Suchanfrage entsprechen.";
        }

        $response = [
            'html' => $html,
            'geo' => $stand_geo_data,
        ];

        wp_die(json_encode($response, JSON_THROW_ON_ERROR));
    }

    /**
     * @throws JsonException
     */
    public static function get_map_freespace(): void
    {

        $response = [];

        $args = [
            'post_type' => BOOKINGPORT_CptFreespace::$Cpt_Freespace,
            'posts_per_page' => -1
        ];

        $freespaces = new WP_Query($args);

        if ($freespaces->found_posts > 0) {
            foreach ($freespaces->posts ?? [] as $freespace) {

                $latitude = get_post_meta($freespace->ID, BOOKINGPORT_CptFreespace::$Cpt_Freespace_Lat, true);
                $longitude = get_post_meta($freespace->ID, BOOKINGPORT_CptFreespace::$Cpt_Freespace_Lng, true);
                $infoText = get_post_field('post_content', $freespace->ID);

                $mapObject = [
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'infoText' => $infoText
                ];

                $response[] = $mapObject;
            }
        }

        wp_die(json_encode($response, JSON_THROW_ON_ERROR));

    }

}