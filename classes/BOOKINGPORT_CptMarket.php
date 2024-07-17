<?php

class BOOKINGPORT_CptMarket
{
    public static string $Cpt_Market = 'bookingport_market';
    public static string $Cpt_MarketOfferTime = 'bookingport_market_offer_date';
    public static string $Cpt_MarketStatus = 'bookingport_market_status';
    public static string $Cpt_MarketStatusAdminAccepted = 'Vom Admin abgesendet (48 Stunden gültig)';
    public static string $Cpt_MarketStatusCustomerAccepted = 'Vom Kunden angenommen';
    public static string $Cpt_MarketStatusExpired = 'Angebot abgelaufen (48 Stunden überschritten)';
    public static string $Cpt_MarketStatusAdminDenied = 'Vom Admin abgelehnt';
    public static string $Cpt_MarketStatusCustomerDenied = 'Vom Kunden abgelehnt';
    public static string $Cpt_MarketStatusCustomerRequested = 'Vom Kunden Angefragt';
    public static string $Cpt_MarketType = 'bookingport_market_type';
    public static string $Cpt_MarketOffer = 'Angebot';
    public static string $Cpt_MarketRequest = 'Anfrage';
    public static string $CPT_MarketPrice = 'bookingport_market_price';
    public static string $CPT_MarketComment = 'bookingport_market_comment';
    public static string $CPT_ReasonCustomerDenied = 'bookingport_market_reason_customer_denied';
    public static string $CPT_MarketPavillon = 'bookingport_market_pavillon';
    public static string $CPT_MarketPavillonConfirmation = 'bookingport_market_pavillon_confirmation';
    public static string $CPT_MarketUserID = 'bookingport_market_userID';
    public static string $CPT_MarketStands = 'bookingport_market_stands';
    public static string $CPT_MarketWidth = 'bookingport_market_stand_width';
    public static string $CPT_MarketDepth = 'bookingport_market_stand_depth';
    public static string $CPT_MarketAssociationName = 'bookingport_market_stand_association_name';
    public static string $CPT_MarketAssociationSortiment = 'bookingport_market_association_sortiment';

    public function __construct()
    {
        // Intentionally left blank.
    }

    public static function init(): void
    {
        add_action('init', [__CLASS__, 'registerCPTMarket']);
        add_action('add_meta_boxes', [__CLASS__, 'add_meta_boxes']);
    }

    public static function add_meta_boxes(): void
    {
        add_meta_box(
            'market_meta',
            __('Metadaten:', 'bookingport'),
            [__CLASS__, 'market_meta_callback'],
            self::$Cpt_Market,
            'advanced',
            'default'
        );
    }

    public static function market_meta_callback($post): void
    {
        $price = get_post_meta($post->ID, self::$CPT_MarketPrice, true);
        $offerTime = get_post_meta($post->ID, self::$Cpt_MarketOfferTime, true);

        if ($offerTime) {
            $offerTime = date('d.m.Y H:i:s', (int)$offerTime);
        }

        $status = get_post_meta($post->ID, self::$Cpt_MarketStatus, true);
        $reason_customer_denied = get_post_meta($post->ID, self::$CPT_ReasonCustomerDenied, true);
        $type = get_post_meta($post->ID, self::$Cpt_MarketType, true);
        $comment = get_post_meta($post->ID, self::$CPT_MarketComment, true);

        // determine wether the pavillon is requested by the customer or not
        $pavillon = get_post_meta($post->ID, self::$CPT_MarketPavillon, true);
        $pavillon === 'true' ? $pavillonValue = 'Ja' : $pavillonValue = 'Nein';

        // determine wether the pavillon is allowed by the admin or not
        $pavillonConfirmation = get_post_meta($post->ID, self::$CPT_MarketPavillonConfirmation, true);
        $pavillonConfirmation === 'true' ? $pavillonConfirmationValue = 'Ja' : $pavillonConfirmationValue = 'Nein';

        $userID = get_post_meta($post->ID, self::$CPT_MarketUserID, true);
        $user = get_user_by('id', $userID);
        $user_info = $user->billing_first_name . ' ' . $user->billing_last_name . ' (' . $user->user_email . ')';

        $stands = get_post_meta($post->ID, self::$CPT_MarketStands, true);
        $width = get_post_meta($post->ID, self::$CPT_MarketWidth, true);
        $depth = get_post_meta($post->ID, self::$CPT_MarketDepth, true);
        $association_name = get_post_meta($post->ID, self::$CPT_MarketAssociationName, true);
        $association_sortiment = get_post_meta($post->ID, self::$CPT_MarketAssociationSortiment, true);

        ?>

        <div class="admin-panel">

            <?php if (!empty($type)) { ?>
                <p><label for="<?php echo self::$Cpt_MarketType; ?>">Typ:</label></br>
                    <input type="text" name="<?php echo self::$Cpt_MarketType; ?>"
                           id="<?php echo self::$Cpt_MarketType ?>" value="<?php echo $type; ?>" readonly></p>
            <?php } ?>

            <?php if (!empty($status)) { ?>
                <p><label for="<?php echo self::$Cpt_MarketStatus; ?>">Status:</label></br>
                    <input type="text" name="<?php echo self::$Cpt_MarketStatus; ?>"
                           id="<?php echo self::$Cpt_MarketStatus ?>" value="<?php echo $status; ?>" readonly></p>
            <?php } ?>

            <?php if (!empty($reason_customer_denied)) { ?>
                <p><label for="<?php echo self::$CPT_ReasonCustomerDenied ?>">Begründung des Kunden:</label></br>
                    <input type="text" name="<?php echo self::$CPT_ReasonCustomerDenied ?>"
                           id="<?php echo self::$CPT_ReasonCustomerDenied ?>"
                           value="<?php echo $reason_customer_denied; ?>" readonly></p>
            <?php } ?>

            <?php if (!empty($offerTime)) { ?>
                <p><label for="<?php echo self::$Cpt_MarketOfferTime; ?>">Zeitpunkt des Angebots vom Admin:</label></br>
                    <input type="text" name="<?php echo self::$Cpt_MarketOfferTime; ?>"
                           id="<?php echo self::$Cpt_MarketOfferTime ?>" value="<?php echo $offerTime; ?>" readonly></p>
            <?php } ?>

            <?php if (!empty($price)) { ?>
                <p><label for="<?php echo self::$CPT_MarketPrice; ?>">Preis in €:</label></br>
                    <input type="text" name="<?php echo self::$CPT_MarketPrice; ?>"
                           id="<?php echo self::$CPT_MarketPrice ?>" value="<?php echo $price; ?>" readonly></p>
            <?php } ?>

            <?php if (!empty($user_info)) { ?>
                <p><label for="<?php echo self::$CPT_MarketUserID; ?>">Kunde:</label></br>
                    <input type="text" name="<?php echo self::$CPT_MarketUserID; ?>"
                           id="<?php echo self::$CPT_MarketUserID ?>" value="<?php echo $user_info; ?>" readonly></p>
            <?php } ?>

            <p><label for="<?php echo self::$CPT_MarketPrice; ?>">Stände(ID's):</label></br>
                <input type="text" name="<?php echo self::$CPT_MarketStands ?>"
                       id="<?php echo self::$CPT_MarketStands ?>" value="<?php
                $counter = 1;
                is_array($stands) ? $stand_amount = count($stands) : $stand_amount = 1;
                if (isset($stands) && !empty($stands)) {
                    if (is_array($stands)) {
                        foreach ($stands as $stand) {
                            $stand_name = get_the_title($stand);
                            if ($counter !== $stand_amount) {
                                $stand_name .= ', ';
                            }
                            echo $stand_name;
                            $counter++;
                        }
                    } else {
                        $stand_name = get_the_title($stands);
                        echo $stand_name;
                    }
                }
                ?>"
                       readonly></p>

            <?php if (!empty($pavillonValue)) { ?>
                <p><label for="<?php echo self::$CPT_MarketPavillon; ?>">Pavillon gewünscht:</label></br>
                    <input type="text" name="<?php echo self::$CPT_MarketPavillon; ?>"
                           id="<?php echo self::$CPT_MarketPavillon ?>" value="<?php echo $pavillonValue#;
                    ?>" readonly></p>
            <?php } ?>

            <?php if (!empty($pavillonConfirmationValue)) { ?>
                <p><label for="<?php echo self::$CPT_MarketPavillonConfirmation; ?>">Pavillon genehmigt:</label></br>
                    <input type="text" name="<?php echo self::$CPT_MarketPavillonConfirmation; ?>"
                           id="<?php echo self::$CPT_MarketPavillonConfirmation ?>"
                           value="<?php echo $pavillonConfirmationValue; ?>" readonly></p>
            <?php } ?>

            <?php if (!empty($width)) { ?>
                <p><label for="<?php echo self::$CPT_MarketWidth; ?>">Breite (m) :</label></br>
                    <input type="text" name="<?php echo self::$CPT_MarketWidth; ?>"
                           id="<?php echo self::$CPT_MarketWidth ?>" value="<?php echo $width; ?>" readonly></p>
            <?php } ?>

            <?php if (!empty($depth)) { ?>
                <p><label for="<?php echo self::$CPT_MarketDepth; ?>">Tiefe (m):</label></br>
                    <input type="text" name="<?php echo self::$CPT_MarketDepth; ?>"
                           id="<?php echo self::$CPT_MarketDepth ?>" value="<?php echo $depth; ?>" readonly></p>
            <?php } ?>

            <?php if (!empty($comment)) { ?>
                <p><label for="<?php echo self::$CPT_MarketComment; ?>">Kommentar:</label></br>
                    <textarea name="<?php echo self::$CPT_MarketComment; ?>"
                              id="<?php echo self::$CPT_MarketComment ?>"
                              readonly><?php echo $comment; ?></textarea></p>
            <?php } ?>

            <?php if (!empty($association_name)) { ?>
                <p><label for="<?php echo self::$CPT_MarketAssociationName ?>">Vereinsname:</label></br>
                    <input type="text" name="<?php echo self::$CPT_MarketAssociationName; ?>"
                           id="<?php echo self::$CPT_MarketAssociationName ?>" value="<?php echo $association_name; ?>"
                           readonly></p>
            <?php } ?>

            <?php if (!empty($association_sortiment)) { ?>
                <p><label for="<?php echo self::$CPT_MarketAssociationSortiment ?>">Verinssortiment:</label></br>
                    <input type="text" name="<?php echo self::$CPT_MarketAssociationSortiment; ?>"
                           id="<?php echo self::$CPT_MarketAssociationSortiment ?>"
                           value="<?php echo $association_sortiment; ?>" readonly></p>
            <?php } ?>
        </div>

        <style>
            .admin-panel p {
                width: 300px;
            }

            .admin-panel input, textarea {
                width: 100%;
            }
        </style>

        <?php

    }

    public static function registerCPTMarket(): void
    {

        $labels = [
            'name' => _x('Inserate', 'post type general name', 'bookingport'),
            'singular_name' => _x('Inserat', 'post type singular name', 'bookingport'),
            'add_new' => _x('Hinzufügen', 'Inserat', 'bookingport'),
            'add_new_item' => __('Neues Inserat hinzufügen', 'bookingport'),
            'edit_item' => __('Inserat bearbeiten', 'bookingport'),
            'new_item' => __('Neues Inserat', 'bookingport'),
            'view_item' => __('Inserat anzeigen', 'bookingport'),
            'search_items' => __('Inserate suchen', 'bookingport'),
            'not_found' => __('Keine Inserate gefunden', 'bookingport'),
            'not_found_in_trash' => __('Keine Inserate im Papierkorb', 'bookingport'),
            'parent_item_colon' => ''
        ];

        $args = [
            'label' => __('Inserate', 'bookingport'),
            'description' => __('Inserate Beschreibung', 'bookingport'),
            'labels' => $labels,
            'hierarchical' => false,
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'menu_icon' => 'dashicons-cart',
            'menu_position' => 3,
            'show_in_admin_bar' => true,
            'show_in_nav_menus' => true,
            'can_export' => true,
            'publicly_queryable' => false,
            'has_archive' => true,
            'exclude_from_search' => true,
            'capability_type' => 'post',
            '_builtin' => false,
            'query_var' => true,
            'rewrite' => ['slug' => self::$Cpt_Market, 'with_front' => true],
            'supports' => ['title'],
            'show_in_rest' => false,
        ];

        register_post_type(self::$Cpt_Market, $args);
        flush_rewrite_rules();

    }
}