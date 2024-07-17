<?php

class BOOKINGPORT_MarketHandler
{
    public function __construct()
    {
        // Intentionally left blank.
    }

    public static function init(): void
    {
        add_action('wp_ajax_deny_customer_request', [__CLASS__, 'deny_customer_request']);
        add_action('wp_ajax_nopriv_deny_customer_request', [__CLASS__, 'deny_customer_request']);

        add_action('wp_ajax_proceed_customer_request', [__CLASS__, 'proceed_customer_request']);
        add_action('wp_ajax_nopriv_proceed_customer_request', [__CLASS__, 'proceed_customer_request']);

        add_action('wp_ajax_deny_admin_offer', [__CLASS__, 'deny_admin_offer']);
        add_action('wp_ajax_nopriv_deny_admin_offer', [__CLASS__, 'deny_admin_offer']);

        add_action('wp_ajax_send_customer_request', [__CLASS__, 'send_customer_request']);
        add_action('wp_ajax_nopriv_send_customer_request', [__CLASS__, 'send_customer_request']);

        add_action('wp_ajax_delete_offer', [__CLASS__, 'delete_offer']);
        add_action('wp_ajax_nopriv_delete_offer', [__CLASS__, 'delete_offer']);

        add_action('wp_ajax_reactivate_offer', [__CLASS__, 'reactivate_offer']);
        add_action('wp_ajax_nopriv_reactivate_offer', [__CLASS__, 'reactivate_offer']);

        add_action('wp_ajax_filter_expired_offers', [__CLASS__, 'filter_expired_offers']);
        add_action('wp_ajax_nopriv_filter_expired_offers', [__CLASS__, 'filter_expired_offers']);

        add_action('wp', [__CLASS__, 'accept_customer_reservations']);
    }

    public static function accept_customer_reservations(): void
    {
        global $post;
        if (!empty($post->post_name) && $post->post_name === 'reservierungen-annehmen') {

            $_SESSION['redirect_from_reservierungen_annehmen'] = $_SERVER['REQUEST_URI'];
            add_query_arg('redirect_from_reservierungen_annehmen', urlencode($_SERVER['REQUEST_URI']), '/mein-konto');
            $request_user = get_current_user_id();

            if (!empty($_GET['request_id'])) {
                $request_id = $_GET['request_id'];
                $current_request_status = get_post_status($request_id);
                // If the GET variable is set and the user is validated to receive the offer by his ID, to prevent non-valid users from receiving the offer
                $reservation_owner = get_post_meta($request_id, BOOKINGPORT_CptMarket::$CPT_MarketUserID, true);

                if ($reservation_owner == $request_user && $current_request_status !== 'draft') {
                    update_post_meta($request_id, BOOKINGPORT_CptMarket::$Cpt_MarketStatus, BOOKINGPORT_CptMarket::$Cpt_MarketStatusCustomerAccepted);
                    wp_update_post([
                        'ID' => $request_id,
                        'post_status' => 'draft'
                    ]);

                    $offered_stands = get_post_meta($request_id, BOOKINGPORT_CptMarket::$CPT_MarketStands, true);

                    $offeredStandsAmount = count($offered_stands);

                    $offerTotal = get_post_meta($request_id, BOOKINGPORT_CptMarket::$CPT_MarketPrice, true);
                    $singleStandPrice = (float)($offerTotal / $offeredStandsAmount);

                    $offerTotalWidth = get_post_meta($request_id, BOOKINGPORT_CptMarket::$CPT_MarketWidth, true);
                    $singleStandWidth = round((float)($offerTotalWidth / $offeredStandsAmount), 2);

                    $product_id = wc_get_product_id_by_sku(BOOKINGPORT_Installation::$product_sku);

                    foreach ($offered_stands ?? [] as $cart_item) {

                        $reservedCartItem = [];
                        $reservedCartItem['articles_reserved_by_admin'][$cart_item]['price'] = $singleStandPrice;
                        $reservedCartItem['articles_reserved_by_admin'][$cart_item]['width'] = $singleStandWidth;
                        $reservedCartItem['articles_reserved_by_admin'][$cart_item]['depth'] = get_post_meta($request_id, BOOKINGPORT_CptMarket::$CPT_MarketDepth, true);
                        $reservedCartItem['articles_reserved_by_admin'][$cart_item]['pavillon'] = get_post_meta($request_id, BOOKINGPORT_CptMarket::$CPT_MarketPavillonConfirmation, true);
                        $reservedCartItem['articles_reserved_by_admin'][$cart_item]['housenumber'] = get_post_meta($cart_item, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeoStreetNumber, true);
                        $reservedCartItem['articles_reserved_by_admin'][$cart_item]['street'] = get_post_meta($cart_item, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeoStreetname, true);
                        $reservedCartItem['articles_reserved_by_admin'][$cart_item]['number'] = get_post_meta($cart_item, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralNumber, true);

                        update_post_meta($cart_item, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralSellStatusLastChange, time());
                        update_post_meta($cart_item, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralSellStatus, BOOKINGPORT_CptStands::$Cpt_Stand_Status_Reserved);

                        $_SESSION['items'][] = $cart_item;
                        WC()->cart->add_to_cart($product_id, 1, 0, [], $reservedCartItem);
                    }

                }

                wp_safe_redirect('/warenkorb');
            }
        }
    }

    /**
     * @throws JsonException
     */
    public static function deny_customer_request(): void
    {
        $html = [];
        $item_to_delete = $_POST['itemToDelete'];
        $request_to_delete = $_POST['requestToDelete'];
        BOOKINGPORT_StandStatusHandler::reset_stand_data($item_to_delete);

        update_post_meta($request_to_delete, BOOKINGPORT_CptMarket::$Cpt_MarketStatus, BOOKINGPORT_CptMarket::$Cpt_MarketStatusAdminDenied);

        wp_update_post([
            'ID' => $request_to_delete,
            'post_status' => 'draft'
        ]);

        $customer_request_args = [
            'post_type' => BOOKINGPORT_CptMarket::$Cpt_Market,
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'meta_query' => [
                [
                    'key' => BOOKINGPORT_CptMarket::$Cpt_MarketType,
                    'value' => BOOKINGPORT_CptMarket::$Cpt_MarketRequest,
                    'compare' => '=',
                ],
            ]
        ];

        $customer_requests = new WP_Query($customer_request_args);

        ob_start(); ?>
        <h3 id="current-request-counter">Aktuelle Anfragen: <?= count($customer_requests->posts) ?></h3>
        <div class="inner-content">
            <?php if ($customer_requests->found_posts > 0) {
                foreach ($customer_requests->posts ?? [] as $single_request) {

                    $request_association_name = null;
                    $request_association_sortiment = null;
                    $id = $single_request->ID;
                    $user_id = get_post_meta($id, BOOKINGPORT_CptMarket::$CPT_MarketUserID, true);
                    $request_width = get_post_meta($id, BOOKINGPORT_CptMarket::$CPT_MarketWidth, true);
                    $request_depth = get_post_meta($id, BOOKINGPORT_CptMarket::$CPT_MarketDepth, true);
                    $request_pavillon = get_post_meta($id, BOOKINGPORT_CptMarket::$CPT_MarketPavillon, true);
                    $request_comment = get_post_meta($id, BOOKINGPORT_CptMarket::$CPT_MarketComment, true);
                    $requested_stand_id = get_post_meta($id, BOOKINGPORT_CptMarket::$CPT_MarketStands, true);
                    $user = get_user_by('ID', $user_id);

                    $billing_first_name = get_user_meta($user_id, 'billing_first_name', true);
                    $billing_last_name = get_user_meta($user_id, 'billing_last_name', true);
                    $full_name = $billing_first_name . ' ' . $billing_last_name;

                    if (!empty(get_post_meta($id, BOOKINGPORT_CptMarket::$CPT_MarketAssociationName, true))) {
                        $request_association_name = get_post_meta(BOOKINGPORT_CptMarket::$CPT_MarketAssociationName, true);
                    }
                    if (!empty(get_post_meta($id, BOOKINGPORT_CptMarket::$CPT_MarketAssociationSortiment, true))) {
                        $request_association_sortiment = get_post_meta(BOOKINGPORT_CptMarket::$CPT_MarketAssociationSortiment, true);
                    }

                    ?>
                    <div class="single-request">
                        <div class="request-id-container">
                            <h3><?= get_the_title($id) ?></h3>
                        </div>
                        <div>
                            <img class="stand-marker-image"
                                 src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/customers/customer-blue.svg">
                            <p>Von: <?= $full_name ?></p>
                            <input type="hidden" name="request_user_id" value="<?= $user_id ?>"/>
                            <input type="hidden" name="request_user_name" value="<?= $full_name ?>"/>
                        </div>
                        <div>
                            <img class="stand-marker-image"
                                 src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/customers/mail-blue.svg">
                            <a href="mailto:<?= $user->user_email ?>">E-Mail: <?= $user->user_email ?></a>
                        </div>

                        <?php if (isset($request_association_name)) { ?>
                            <div>
                                <img class="stand-marker-image"
                                     src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/customers/stand-cat-blue.svg">
                                <p>Vereinsname: <?= $request_association_name ?></p>
                            </div>

                        <?php } ?>
                        <?php if (isset($request_association_sortiment)) { ?>
                            <div>
                                <img class="stand-marker-image"
                                     src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/customers/stand-cat-blue.svg">
                                <p>Sortiment: <?= $request_association_sortiment ?></p>
                            </div>
                        <?php } ?>
                        <div>
                            <img class="stand-marker-image"
                                 src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/stands/stand-marker-blue.svg">
                            <p>
                                Wunschstraße: <?= get_the_title($requested_stand_id) ?></p>
                        </div>
                        <div>
                            <img class="stand-marker-image"
                                 src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/stands/space-blue.svg">
                            <p>Angeforderte Breite: <?= $request_width ?>m</p>
                        </div>

                        <?php if (!empty($request_depth)) { ?>
                            <div>
                                <img class="stand-marker-image"
                                     src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/stands/space-blue.svg">
                                <p>Angeforderte Tiefe: <?= $request_depth ?>m</p>
                            </div>

                        <?php } ?>
                        <div>
                            <img class="stand-marker-image"
                                 src="<?= BOOKINGPORT_PLUGIN_URI ?>assets/images/icons/stands/pavillon-blue.svg">
                            <p><?php echo 'Pavillon gewünscht: ' . ($request_pavillon === "true" ? 'Ja' : 'Nein'); ?></p>
                        </div>
                        <?php if (!empty($request_comment)) { ?>
                            <label for="user_remarks"><strong>Anmerkungen des Kunden:</strong></label>
                            <textarea name="user_remarks" readonly><?= $request_comment ?></textarea>
                        <?php } ?>
                        <div class="request-button-wrapper">
                            <div class="deny-request btn-primary" data-request-id="<?= $id ?>"
                                 data-stand-id="<?= $requested_stand_id ?>">Anfrage ablehnen
                            </div>
                            <div class="edit-request btn-primary" data-request-id="<?= $id ?>"
                                 data-stand-id="<?= $requested_stand_id ?>">Anfrage bearbeiten
                            </div>
                        </div>
                    </div>
                <?php }
            } ?>
        </div>
        <?php

        $html[] = ob_get_clean();
        wp_die(json_encode($html, JSON_THROW_ON_ERROR));
    }

    /**
     * @throws JsonException
     */
    public static function deny_admin_offer(): void
    {
        $request_id = $_POST['requestToDelete'];
        $customer_message = $_POST['customerMessage'];
        $option_table = BOOKINGPORT_Settings::$option_table;
        $market_prefix = $option_table[BOOKINGPORT_Settings::$option_market_prefix];

        // send an e-mail to the admin, that the customer denied his offer

        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8'
        ];

        $message = "Hallo admin, <br/> Das Angebot mit der Angebotsnummer " . $market_prefix . $request_id . " wurde vom Kunden abgelehnt. <br/> <br/><b>Begründung des Kunden</b><br/>";
        $customer_message === '' ? $customer_reason = 'Keine Begründung angegeben' : $customer_reason = $customer_message;
        $message .= $customer_reason;
        $subject = 'Abgelehntes Angebot #' . $request_id;
        $additional_headers = implode("\r\n", $headers);
        $option_table = get_option(BOOKINGPORT_Settings::$option_table);
        $admin_email = $option_table[BOOKINGPORT_Settings::$option_email_booking_request];

        wp_mail($admin_email, $subject, $message, $additional_headers);


        // reset the status of the offer
        wp_update_post([
            'ID' => $request_id,
            'post_status' => 'draft'
        ]);

        update_post_meta($request_id, BOOKINGPORT_CptMarket::$Cpt_MarketStatus, BOOKINGPORT_CptMarket::$Cpt_MarketStatusCustomerDenied);
        update_post_meta($request_id, BOOKINGPORT_CptMarket::$CPT_ReasonCustomerDenied, $customer_reason);

        // reset the status of the offered stands
        $items_to_delete = get_post_meta($request_id, BOOKINGPORT_CptMarket::$CPT_MarketStands, true);
        $session_items = [];

        foreach ($items_to_delete ?? [] as $item_to_delete) {
            BOOKINGPORT_StandStatusHandler::reset_stand_data($item_to_delete);
            $session_items[] = $item_to_delete;
        }

        $_SESSION['successfully_deleted'] = $session_items;

        wp_die(json_encode($_SESSION['successfully_deleted'], JSON_THROW_ON_ERROR));
    }

    /**
     * @throws JsonException
     */
    public static function proceed_customer_request(): void
    {

        $item_IDS = $_SESSION['items'];
        $proceed_data = $_POST['proceedMeta'];
        $_SESSION['items'] = [];
        $request_id = $proceed_data['id'];
        $request_price = $proceed_data['price'];
        $request_width = $proceed_data['width'];
        $request_depth = $proceed_data['depth'];
        $request_customer_id = $proceed_data['customer_id'];
        $request_customer_name = $proceed_data['customer_name'];
        $request_pavillon_confirmation = $proceed_data['pavillon_confirmation'];

        $user_data = get_userdata($request_customer_id);
        $user_email = $user_data->user_email;
        $user_full_name = $user_data->billing_first_name . ' ' . $user_data->billing_last_name;
        $option_table = get_option(BOOKINGPORT_Settings::$option_table);
        $email_subject = $option_table[BOOKINGPORT_Settings::$option_email_request_successfully_processed_subject];
        $email_title = $option_table[BOOKINGPORT_Settings::$option_email_request_successfully_processed_title];
        $email_logo = $option_table[BOOKINGPORT_Settings::$option_email_request_successfully_processed_logo];
        $email_body = $option_table[BOOKINGPORT_Settings::$option_email_request_successfully_processed_body];
        $email_footer = $option_table[BOOKINGPORT_Settings::$option_email_request_successfully_processed_footer];
        $market_prefix = $option_table[BOOKINGPORT_Settings::$option_market_prefix];
        $redirect_link = '/' . $option_table[BOOKINGPORT_Settings::$option_redirects_stand_booking];
        $post_title = BOOKINGPORT_CptMarket::$Cpt_MarketOffer . ' ' . $market_prefix . $request_id . ', ' . $user_full_name;

        // Reset the requested stand first, because the stand initially requested by the customer might not be part of the offer.
        // Sending an offer should always affect the requested stand in some way. Either to an offer or back to free
        if (!empty($request_id)) {
            BOOKINGPORT_StandStatusHandler::reset_stand_data(get_post_meta($request_id, BOOKINGPORT_CptMarket::$CPT_MarketStands, true));
        }

        // update the post meta of each stand individually

        foreach ($item_IDS ?? [] as $item_ID) {

            update_post_meta($item_ID, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralSellStatus, BOOKINGPORT_CptStands::$Cpt_Stand_Status_Reserved_By_Admin);
            update_post_meta($item_ID, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralSellUserId, $request_customer_id);
            update_post_meta($item_ID, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralSellUserName, $user_full_name);
            update_post_meta($item_ID, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralSellStatusLastChange, time());
        }

        if (!empty($request_id)) {  // IF the Offer is based on an existing request do this
            wp_update_post([
                'ID' => $request_id,
                'post_title' => $post_title
            ]);

            update_post_meta($request_id, BOOKINGPORT_CptMarket::$Cpt_MarketType, BOOKINGPORT_CptMarket::$Cpt_MarketOffer);

        } else { // IF the admin is creating an offer that is not based on an existing request
            $request_id = wp_insert_post([
                'post_title' => '',
                'post_status' => 'publish',
                'post_type' => BOOKINGPORT_CptMarket::$Cpt_Market
            ]);

            $post_title = BOOKINGPORT_CptMarket::$Cpt_MarketOffer . ' ' . $market_prefix . $request_id . ', ' . $user_full_name;
            wp_update_post([
                'ID' => $request_id,
                'post_title' => $post_title
            ]);

            update_post_meta($request_id, BOOKINGPORT_CptMarket::$Cpt_MarketType, BOOKINGPORT_CptMarket::$Cpt_MarketOffer);

            if (!empty($request_customer_id)) {
                update_post_meta($request_id, BOOKINGPORT_CptMarket::$CPT_MarketUserID, $request_customer_id);
            }
        }

        if ($request_pavillon_confirmation == 'true') {
            update_post_meta($request_id, BOOKINGPORT_CptMarket::$CPT_MarketPavillonConfirmation, $request_pavillon_confirmation);
        }

        if (!empty($request_price)) {
            update_post_meta($request_id, BOOKINGPORT_CptMarket::$CPT_MarketPrice, $request_price);
        }
        if (!empty($request_width)) {
            update_post_meta($request_id, BOOKINGPORT_CptMarket::$CPT_MarketWidth, $request_width);
        }
        if (!empty($request_depth)) {
            update_post_meta($request_id, BOOKINGPORT_CptMarket::$CPT_MarketDepth, $request_depth);
        }
        if (!empty($item_IDS)) {
            update_post_meta($request_id, BOOKINGPORT_CptMarket::$CPT_MarketStands, $item_IDS);
        }

        update_post_meta($request_id, BOOKINGPORT_CptMarket::$Cpt_MarketStatus, BOOKINGPORT_CptMarket::$Cpt_MarketStatusAdminAccepted);
        update_post_meta($request_id, BOOKINGPORT_CptMarket::$Cpt_MarketOfferTime, time());

        $_SESSION['proceed_customer_data'] = [
            'customer_id' => $request_customer_id,
            'customer_name' => $request_customer_name,
            'request' => $market_prefix . $request_id
        ];

        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
        ];

        $additional_headers = implode("\r\n", $headers);

        $email_message = '<!DOCTYPE html>
            <html lang="de">
            <head>
            <title>' . $email_title . '</title>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width">
            <style>
                /* CLIENT-SPECIFIC STYLES */
                #outlook a{padding:0;} /* Force Outlook to provide a "view in browser" message */
                .ReadMsgBody{width:100%;} .ExternalClass{width:100%;} /* Force Hotmail to display emails at full width */
                .ExternalClass, .ExternalClass p, .ExternalClass span, .ExternalClass font, .ExternalClass td, .ExternalClass div {line-height: 100%;} /* Force Hotmail to display normal line spacing */
                body, table, td, a{-webkit-text-size-adjust:100%; -ms-text-size-adjust:100%;} /* Prevent WebKit and Windows mobile changing default text sizes */
                table, td{mso-table-lspace:0; mso-table-rspace:0;} /* Remove spacing between tables in Outlook 2007 and up */
                img{-ms-interpolation-mode:bicubic;} /* Allow smoother rendering of resized image in Internet Explorer */
            
                /* RESET STYLES */
                body{margin:0; padding:0;}
                img{border:0; height:auto; line-height:100%; outline:none; text-decoration:none;}
                table{border-collapse:collapse !important;}
                body{height:100% !important; margin:0; padding:0; width:100% !important;}
            
                /* iOS BLUE LINKS */
                .appleBody a {color:#68440a; text-decoration: none;}
                .appleFooter a {color:#999999; text-decoration: none;}
            
                /* MOBILE STYLES */
                @media screen and (max-width: 525px) {
            
                    /* ALLOWS FOR FLUID TABLES */
                    table[class="wrapper"]{
                      width:100% !important;
                    }
            
                    /* ADJUSTS LAYOUT OF LOGO IMAGE */
                    td[class="logo"]{
                      text-align: left;
                      padding: 20px 0 20px 0 !important;
                    }
            
                    td[class="logo"] img{
                      margin:0 auto!important;
                    }
            
                    /* USE THESE CLASSES TO HIDE CONTENT ON MOBILE */
                    td[class="mobile-hide"]{
                      display:none;}
            
                    img[class="mobile-hide"]{
                      display: none !important;
                    }
            
                    img[class="img-max"]{
                      max-width: 100% !important;
                      height:auto !important;
                    }
            
                    /* FULL-WIDTH TABLES */
                    table[class="responsive-table"]{
                      width:100%!important;
                    }
            
                    /* UTILITY CLASSES FOR ADJUSTING PADDING ON MOBILE */
                    td[class="padding"]{
                      padding: 10px 5% 10px 5% !important;
                    }
            
                    td[class="padding-copy"]{
                      padding: 10px 5% 10px 5% !important;
                      text-align: left;
                    }
            
                    td[class="padding-meta"]{
                      padding: 10px 5% 10px 5% !important;
                      text-align: left;
                    }
            
                    td[class="no-pad"]{
                      padding: 20px 0 20px 0 !important;
                    }
            
                    td[class="no-padding"]{
                      padding: 0 !important;
                    }
            
                    td[class="section-padding"]{
                      padding: 50px 15px 50px 15px !important;
                    }
            
                    td[class="section-padding-bottom-image"]{
                      padding: 50px 15px 0 15px !important;
                    }
            
                    /* ADJUST BUTTONS ON MOBILE */
                    td[class="mobile-wrapper"]{
                        padding: 15px 5% 15px 5% !important;
                    }
            
                    table[class="mobile-button-container"]{
                        margin:0 auto;
                        width:100% !important;
                    }
            
                    a[class="mobile-button"]{
                        width:80% !important;
                        padding: 15px !important;
                        border: 0 !important;
                        font-size: 16px !important;
                    }
            
                }
            </style>
            </head>
            <body style="margin: 0; padding: 0;">
            
            <!-- HEADER -->
            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <td bgcolor="#ffffff" align="left"  style="padding: 0 15px 0 15px;">
                        <table border="0" cellpadding="0" cellspacing="0" width="500" class="responsive-table">
                            <tr>
                                <td align="left"  style="padding: 0 15px 0 15px;">
                                    <a href="#" target="_blank"><img alt="' . $email_title . '" src="' . $email_logo . '" width="200" height="99" style="display: block; font-family: Helvetica, Arial, sans-serif; color: #666666; font-size: 16px;" border="0" class="img-max"></a>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            
            <!-- ONE COLUMN SECTION -->
            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <td bgcolor="#ffffff" align="left" style="padding: 70px 15px 70px 15px;" class="section-padding">
                        <table border="0" cellpadding="0" cellspacing="0" width="500" class="responsive-table">
                            <tr>
                                <td>
                                    <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                        <tr>
                                            <td>
                                                <!-- COPY -->
                                                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                                    <tr>
                                                        <td align="left" style="padding: 20px 0 0 0; font-size: 16px; line-height: 25px; font-family: Helvetica, Arial, sans-serif; color: #666666;" class="padding-copy">Sehr geehrte/r ' . $request_customer_name . ' ,</td>
                                                    </tr>
                                                    <tr>
                                                        <td align="left" style="padding: 20px 0 0 0; font-size: 16px; line-height: 25px; font-family: Helvetica, Arial, sans-serif; color: #666666;" class="padding-copy">
                                                            Wir haben Ihre Anfrage ' . $market_prefix . $request_id . ' bearbeitet. Unser Angebot können Sie unter <a href="' . get_home_url() . '/' . $option_table[BOOKINGPORT_Settings::$option_redirects_customer_requests] . ' /">' . get_home_url() . '/' . $option_table[BOOKINGPORT_Settings::$option_redirects_customer_requests] . '</a> annehmen oder ablehnen. <br />
                                                            Bitte bedenken Sie, dass Sie das unser Angebot nur für <strong>48 Stunden</strong> ab erhalt dieser E-Mail gültig ist. Danach werden die Stände wieder für andere Nutzer freigegeben.
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td align="left" style="padding: 20px 0 0 0; font-size: 16px; line-height: 25px; font-family: Helvetica, Arial, sans-serif; color: #666666;" class="padding-copy">
                                                            Über folgenden Link gelangen Sie direkt in den Bezahlungsprozess: <br><br>
                                                             <a href="' . get_home_url() . '/reservierungen-annehmen?request_id=' . $request_id . '">Standauswahl übernehmen und Buchung im Fettmarkt Portal abschließen(Link zum Checkout)</a>
                                                        </td>
                                                    </tr>
                                                    
                                                    <tr>
                                                        <td align="left" style="padding: 20px 0 0 0; font-size: 16px; line-height: 25px; font-family: Helvetica, Arial, sans-serif; color: #666666;" class="padding-copy">
                                                            ' . $email_body . '
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>        
            
            <!-- FOOTER -->
            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <td bgcolor="#ffffff" align="left" style="font-size: 12px; line-height: 18px; font-family: Helvetica, Arial, sans-serif; color:#666666;">
                        <table border="0" cellpadding="0" cellspacing="0" width="500" class="responsive-table">
                            <tr>
                                <td align="left"  style="padding: 0 15px 0 15px;">
                                    <span class="appleFooter" style="color:#666666;">Impressum:</span><br><span class="original-only" style="font-family: Arial, sans-serif; font-size: 12px; color: #444444;">
                                    ' . $email_footer . '
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            </body>
            </html>';

        wp_mail($user_email, $email_subject, $email_message, $additional_headers);

        wp_die(json_encode($redirect_link, JSON_THROW_ON_ERROR));
    }

    /**
     * @throws JsonException
     */
    public static function send_customer_request(): void
    {
        $request_user_id = get_current_user_id();
        $user_data = get_userdata($request_user_id);
        $user_name = $user_data->billing_first_name . ' ' . $user_data->billing_last_name;
        $request_stand_id = $_POST['stand']['id'];
        $request_stand_pavillon = $_POST['stand']['pavillon'];
        $request_stand_width = $_POST['stand']['width'];
        $request_stand_depth = $_POST['stand']['depth'];
        $request_association_name = $_POST['stand']['association_name'];
        $request_association_sortiment = $_POST['stand']['association_sortiment'];
        $request_comment = $_POST['stand']['remarks'];
        $option_table = get_option(BOOKINGPORT_Settings::$option_table);
        $redirect = '/' . $option_table[BOOKINGPORT_Settings::$option_redirects_booking_request_send];
        $market_prefix = $option_table[BOOKINGPORT_Settings::$option_market_prefix];

        $request_args = [
            'post_title' => '',
            'post_status' => 'publish',
            'post_type' => BOOKINGPORT_CptMarket::$Cpt_Market,
        ];

        $request_id = wp_insert_post($request_args);
        $request_title = BOOKINGPORT_CptMarket::$Cpt_MarketRequest . ' ' . $market_prefix . $request_id . ', ' . $user_name;

        wp_update_post([
            'ID' => $request_id,
            'post_title' => $request_title
        ]);

        update_post_meta($request_id, BOOKINGPORT_CptMarket::$CPT_MarketPavillon, $request_stand_pavillon);
        update_post_meta($request_id, BOOKINGPORT_CptMarket::$Cpt_MarketType, BOOKINGPORT_CptMarket::$Cpt_MarketRequest);
        update_post_meta($request_id, BOOKINGPORT_CptMarket::$CPT_MarketComment, $request_comment);
        update_post_meta($request_id, BOOKINGPORT_CptMarket::$CPT_MarketUserID, $request_user_id);
        update_post_meta($request_id, BOOKINGPORT_CptMarket::$CPT_MarketStands, $request_stand_id);
        update_post_meta($request_id, BOOKINGPORT_CptMarket::$CPT_MarketWidth, $request_stand_width);
        update_post_meta($request_id, BOOKINGPORT_CptMarket::$CPT_MarketDepth, $request_stand_depth);
        update_post_meta($request_id, BOOKINGPORT_CptMarket::$CPT_MarketAssociationName, $request_association_name);
        update_post_meta($request_id, BOOKINGPORT_CptMarket::$CPT_MarketAssociationSortiment, $request_association_sortiment);
        update_post_meta($request_id, BOOKINGPORT_CptMarket::$Cpt_MarketStatus, BOOKINGPORT_CptMarket::$Cpt_MarketStatusCustomerRequested);

        $user_email = $user_data->user_email;
        $user_role = null;
        if (is_object($user_data) && isset($user_data->roles[0])) {
            $user_role = $user_data->roles[0];
        }
        $admin_email = $option_table[BOOKINGPORT_Settings::$option_email_booking_request];
        $email_subject = $option_table[BOOKINGPORT_Settings::$option_email_proceed_customer_request_subject] . $market_prefix . $request_id . '/' . ucfirst($user_role);
        $email_title = $option_table[BOOKINGPORT_Settings::$option_email_proceed_customer_request_title];
        $email_logo = $option_table[BOOKINGPORT_Settings::$option_email_proceed_customer_request_logo];
        $email_body = $option_table[BOOKINGPORT_Settings::$option_email_proceed_customer_request_body];
        $email_footer = $option_table[BOOKINGPORT_Settings::$option_email_proceed_customer_request_footer];

        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8'
        ];


        $additional_headers = implode("\r\n", $headers);

        $email_message = '<!DOCTYPE html>
        <html lang="de">
        <head>
        <title>' . $email_title . '</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width">
        <style type="text/css">
            /* CLIENT-SPECIFIC STYLES */
            #outlook a{padding:0;} /* Force Outlook to provide a "view in browser" message */
            .ReadMsgBody{width:100%;} .ExternalClass{width:100%;} /* Force Hotmail to display emails at full width */
            .ExternalClass, .ExternalClass p, .ExternalClass span, .ExternalClass font, .ExternalClass td, .ExternalClass div {line-height: 100%;} /* Force Hotmail to display normal line spacing */
            body, table, td, a{-webkit-text-size-adjust:100%; -ms-text-size-adjust:100%;} /* Prevent WebKit and Windows mobile changing default text sizes */
            table, td{mso-table-lspace:0; mso-table-rspace:0;} /* Remove spacing between tables in Outlook 2007 and up */
            img{-ms-interpolation-mode:bicubic;} /* Allow smoother rendering of resized image in Internet Explorer */
        
            /* RESET STYLES */
            body{margin:0; padding:0;}
            img{border:0; height:auto; line-height:100%; outline:none; text-decoration:none;}
            table{border-collapse:collapse !important;}
            body{height:100% !important; margin:0; padding:0; width:100% !important;}
        
            /* iOS BLUE LINKS */
            .appleBody a {color:#68440a; text-decoration: none;}
            .appleFooter a {color:#999999; text-decoration: none;}
        
            /* MOBILE STYLES */
            @media screen and (max-width: 525px) {
        
                /* ALLOWS FOR FLUID TABLES */
                table[class="wrapper"]{
                  width:100% !important;
                }
        
                /* ADJUSTS LAYOUT OF LOGO IMAGE */
                td[class="logo"]{
                  text-align: left;
                  padding: 20px 0 20px 0 !important;
                }
        
                td[class="logo"] img{
                  margin:0 auto!important;
                }
        
                /* USE THESE CLASSES TO HIDE CONTENT ON MOBILE */
                td[class="mobile-hide"]{
                  display:none;}
        
                img[class="mobile-hide"]{
                  display: none !important;
                }
        
                img[class="img-max"]{
                  max-width: 100% !important;
                  height:auto !important;
                }
        
                /* FULL-WIDTH TABLES */
                table[class="responsive-table"]{
                  width:100%!important;
                }
        
                /* UTILITY CLASSES FOR ADJUSTING PADDING ON MOBILE */
                td[class="padding"]{
                  padding: 10px 5% 10px 5% !important;
                }
        
                td[class="padding-copy"]{
                  padding: 10px 5% 10px 5% !important;
                  text-align: left;
                }
        
                td[class="padding-meta"]{
                  padding: 10px 5% 10px 5% !important;
                  text-align: left;
                }
        
                td[class="no-pad"]{
                  padding: 20px 0 20px 0 !important;
                }
        
                td[class="no-padding"]{
                  padding: 0 !important;
                }
        
                td[class="section-padding"]{
                  padding: 50px 15px 50px 15px !important;
                }
        
                td[class="section-padding-bottom-image"]{
                  padding: 50px 15px 0 15px !important;
                }
        
                /* ADJUST BUTTONS ON MOBILE */
                td[class="mobile-wrapper"]{
                    padding: 15px 5% 15px 5% !important;
                }
        
                table[class="mobile-button-container"]{
                    margin:0 auto;
                    width:100% !important;
                }
        
                a[class="mobile-button"]{
                    width:80% !important;
                    padding: 15px !important;
                    border: 0 !important;
                    font-size: 16px !important;
                }
        
            }
        </style>
        </head>
        <body style="margin: 0; padding: 0;">
        
        <!-- HEADER -->
        <table border="0" cellpadding="0" cellspacing="0" width="100%">
            <tr>
                <td bgcolor="#ffffff" align="left"  style="padding: 0 15px 0 15px;">
                    <table border="0" cellpadding="0" cellspacing="0" width="500" class="responsive-table">
                        <tr>
                            <td align="left"  style="padding: 0 15px 0 15px;">
                                <a href="#" target="_blank"><img alt="FETTMARKT.com" src="' . $email_logo . '" width="200" height="99" style="display: block; font-family: Helvetica, Arial, sans-serif; color: #666666; font-size: 16px;" border="0" class="img-max"></a>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        
        <!-- ONE COLUMN SECTION -->
        <table border="0" cellpadding="0" cellspacing="0" width="100%">
            <tr>
                <td bgcolor="#ffffff" align="left" style="padding: 70px 15px 70px 15px;" class="section-padding">
                    <table border="0" cellpadding="0" cellspacing="0" width="500" class="responsive-table">
                        <tr>
                            <td>
                                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td>
                                            <!-- COPY -->
                                            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                                <tr>
                                                    <td align="left" style="padding: 20px 0 0 0; font-size: 16px; line-height: 25px; font-family: Helvetica, Arial, sans-serif; color: #666666;" class="padding-copy">Sehr geehrte/r ' . $user_name . ' ,</td>
                                                </tr>
                                                <tr>
                                                    <td align="left" style="font-size: 25px; font-family: Helvetica, Arial, sans-serif; color: #333333; padding-top: 30px;" class="padding-copy">
                                                        Vielen Dank für Ihre Buchungsanfrage über fettmarkt.com</td>
                                                </tr>
                                                <tr>
                                                    <td align="left" style="padding: 20px 0 0 0; font-size: 16px; line-height: 25px; font-family: Helvetica, Arial, sans-serif; color: #666666;" class="padding-copy">
                                                       ' . $email_body . '
                                                    </td>
                                                </tr>                                                           
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>        
        
        <!-- FOOTER -->
        <table border="0" cellpadding="0" cellspacing="0" width="100%">
            <tr>
                <td bgcolor="#ffffff" align="left" style="font-size: 12px; line-height: 18px; font-family: Helvetica, Arial, sans-serif; color:#666666;">
                    <table border="0" cellpadding="0" cellspacing="0" width="500" class="responsive-table">
                        <tr>
                            <td align="left"  style="padding: 0 15px 0 15px;">
                                <span class="appleFooter" style="color:#666666;">Impressum:</span><br><span class="original-only" style="font-family: Arial, sans-serif; font-size: 12px; color: #444444;">
                                            ' . $email_footer . '
                                            </span>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        </body>
        </html>';

        wp_mail($user_email, $email_subject, $email_message, $additional_headers);

        $email_message = "Hallo admin, <br/> Sie haben eine neue Anfrage " . $market_prefix . $request_id . " von " . $user_name . " (" . $user_email . ") für eine Standbuchung erhalten.
        <br/><br/>
        <strong><u>Anforderungsdetails</u></strong>
        <br/><br/>";

        $email_message .= "<strong>Wunschadresse:</strong> " . get_the_title($_POST['stand']['id']) . "<br/>";

        if (!empty($request_association_name)) {
            $email_message .= "<strong>Vereinsname:</strong> " . $request_association_name . "<br/>";
        }

        if (!empty($request_association_sortiment)) {
            $email_message .= "<strong>Sortiment:</strong> " . $request_association_sortiment . "<br/>";
        }

        if (!empty($request_stand_width)) {
            $email_message .= "<strong>Breite:</strong> " . $request_stand_width . "m<br/>";
        }

        if (!empty($request_stand_depth)) {
            $email_message .= "<strong>Tiefe:</strong> " . $request_stand_depth . "m<br/>";
        }

        if (!empty($request_stand_pavillon)) {
            $request_stand_pavillon == "true" ? $email_message .= "<strong>Pavillon:</strong> Ja<br/>" : $email_message .= "<strong>Pavillon:</strong> Nein<br/>";
        }

        if (!empty($request_comment)) {
            $email_message .= "<strong>Kommentar:</strong> " . $request_comment . "<br/>";
        }

        $email_message .= "<br/><a href='" . get_home_url() . "/" . $option_table[BOOKINGPORT_Settings::$option_redirects_stand_booking] . "'>Zur Bearbeitung</a>";
        $subject = 'Neue Anfrage ' . $market_prefix . $request_id . '/' . ucfirst($user_role);

        $additional_headers = implode("\r\n", $headers);

        wp_mail($admin_email, $subject, $email_message, $additional_headers);

        update_post_meta($request_stand_id, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralSellStatus, BOOKINGPORT_CptStands::$Cpt_Stand_Status_Requested_By_Customer);
        update_post_meta($request_stand_id, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralSellUserId, $request_user_id);
        update_post_meta($request_stand_id, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralSellUserName, $user_name);
        update_post_meta($request_stand_id, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralSellStatusLastChange, time());

        wp_die(json_encode($redirect, JSON_THROW_ON_ERROR));
    }

    /**
     * @throws JsonException
     */
    public static function delete_offer(): void
    {
        $response = 200;
        $requestID = $_POST['offerToDelete'];

        $request_stands = get_post_meta($requestID, BOOKINGPORT_CptMarket::$CPT_MarketStands, true);

        foreach ($request_stands as $request_stand) {
            BOOKINGPORT_StandStatusHandler::reset_stand_data($request_stand);
        }

        wp_delete_post($requestID, true);
        wp_die(json_encode($response, JSON_THROW_ON_ERROR));
    }

    /**
     * @throws JsonException
     */
    public static function reactivate_offer(): void
    {
        $response = 200;
        $requestID = $_POST['offerToReactivate'];

        $request_stands = get_post_meta($requestID, BOOKINGPORT_CptMarket::$CPT_MarketStands, true);

        foreach ($request_stands as $request_stand) {
            update_post_meta($request_stand, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralSellStatus, BOOKINGPORT_CptStands::$Cpt_Stand_Status_Reserved_By_Admin);
        }

        update_post_meta($requestID, BOOKINGPORT_CptMarket::$Cpt_MarketStatus, BOOKINGPORT_CptMarket::$Cpt_MarketStatusAdminAccepted);
        update_post_meta($requestID, BOOKINGPORT_CptMarket::$Cpt_MarketOfferTime, time());

        $post_data = [
            'ID' => $requestID,
            'post_status' => 'publish'
        ];

        wp_update_post($post_data);

        wp_die(json_encode($response, JSON_THROW_ON_ERROR));
    }

    /**
     * @throws JsonException
     */
    public static function filter_expired_offers(): void
    {

        $option_table = get_option(BOOKINGPORT_Settings::$option_table);
        $market_prefix = $option_table[BOOKINGPORT_Settings::$option_market_prefix];
        $searchValue = $_POST['searchValue'];

        $expiredOfferArgs = [
            'post_type' => BOOKINGPORT_CptMarket::$Cpt_Market,
            'posts_per_page' => -1,
            'post_status' => 'draft',
            's' => $searchValue,
            'meta_query' => [
                [
                    'key' => BOOKINGPORT_CptMarket::$Cpt_MarketStatus,
                    'value' => BOOKINGPORT_CptMarket::$Cpt_MarketStatusExpired,
                    'compare' => '='
                ]
            ]
        ];

        $expiredOffers = new WP_Query($expiredOfferArgs);

        ob_start();
        if ($expiredOffers->found_posts === 0) { ?>
            <p>Ihre Suche ergab keine Treffer</p>
        <?php }

        foreach ($expiredOffers->posts ?? [] as $expiredOffer) {

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
                    <div class="btn-primary btn trigger-modal open-reactivate-offer-modal" id="reactivate-offer">Angebot
                        erneut
                        ausstellen
                    </div>
                    <div class="btn-tertiary btn trigger-modal open-delete-offer-modal" id="delete-offer">Angebot
                        löschen
                    </div>
                </div>

                <div class="reactivate-offer-modal modal">
                    <div class="inner-modal">
                        <h3>Sind Sie sicher, dass Sie das Angebot <?= $market_prefix . $ID ?> erneut ausstellen
                            wollen?</h3>
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
        <?php }

        $result = ob_get_clean();

        wp_die(json_encode($result, JSON_THROW_ON_ERROR));
    }

}