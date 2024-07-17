<?php

use League\Csv\Writer;

class BOOKINGPORT_FileExporter
{
    public function __construct()
    {
        // Intentionally left blank.
    }

    public static function init(): void
    {
        add_action('wp_ajax_getStandsStatusList_Export', [__CLASS__, 'getStandsStatusList_Export']);
        add_action('wp_ajax_nopriv_getStandsStatusList_Export', [__CLASS__, 'getStandsStatusList_Export']);
    }

    /**
     * @throws \League\Csv\Exception
     * @throws JsonException
     */
    public static function getStandsStatusList_Export(): void
    {

        $stand_ids = self::get_all_stand_ids();
        $result = [];

        $result[] = ['Standname', 'Standstatus', 'Kunde', 'Pavillon', 'Rechnungsnummer', 'Standpreis', 'Standbreite', 'Standtiefe', 'Notizen vom Kunden', 'Bezahlstatus'];

        if ($stand_ids->found_posts > 0) {
            foreach ($stand_ids->posts as $stand_id) {
                $standNumber = get_post_meta($stand_id, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralNumber, true);
                $stand_name = get_post_meta($stand_id, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeoStreetname, true) . ' ' . get_post_meta($stand_id, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeoStreetNumber, true) . ', ' . get_post_meta($stand_id, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralNumber, true);
                $order_id = get_post_meta($stand_id, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralInvoiceID, true);
                $status = BOOKINGPORT_CptStands::get_sell_status_label($stand_id);
                $customer = get_user_by('id', get_post_meta($stand_id, BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralSellUserId, true));
                $customerRole = $customer->roles[0] ?? '';
                !empty($customer) ? $customer_name = $customer->billing_first_name . ' ' . $customer->billing_last_name : $customer_name = 'Kein Kunde';
                $customer_name !== 'Kein Kunde' ? $customer_name .= ' (' . ucfirst($customerRole) . ')' : $customer_name .= '';
                $displayed_order_id = '-';

                $standMetaFromOrder = [];

                if (!empty($order_id)) {
                    $standMetaFromOrder = self::get_stand_meta_from_order((int)$order_id, (string)$standNumber);
                    $displayed_order_id = $order_id;
                    $order_id = null;
                }

                $payment_status = $standMetaFromOrder['status'] ?? '-';
                $stand_width = $standMetaFromOrder['width'] ?? '-';
                $stand_depth = $standMetaFromOrder['depth'] ?? '-';
                $stand_pavillon = $standMetaFromOrder['pavillon'] ?? '-';
                $stand_total = $standMetaFromOrder['total'] ?? '-';
                $stand_notes = $standMetaFromOrder['notes'] ?? '-';
                $result[] = [$stand_name, $status, $customer_name, $stand_pavillon, $displayed_order_id, $stand_total, $stand_width, $stand_depth, $stand_notes, $payment_status];
            }
        }

        $name = 'buchungsliste-' . date('m-d-Y', time()) . '.csv';
        $writer = Writer::createFromPath(BOOKINGPORT_PLUGIN_PATH . '/exportFiles/' . $name, 'w+');
        $writer->insertAll($result);

        wp_die(json_encode(BOOKINGPORT_PLUGIN_URI . '/exportFiles/' . $name, JSON_THROW_ON_ERROR));

    }

    private
    static function get_all_stand_ids(): WP_Query
    {
        $argsStands = [
            'post_type' => BOOKINGPORT_CptStands::$Cpt_Stands,
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'fields' => 'ids',
            'order' => 'ASC'
        ];

        return new WP_Query($argsStands);

    }

    private
    static function get_stand_meta_from_order(int $order_id, string $standNumber): array
    {
        $standMeta = [];
        $order = wc_get_order($order_id);
        $standMeta['notes'] = $order->get_customer_note();
        $status = $order->get_status();
        $orderStands = $order->get_items();

        $standMeta['status'] = match ($status) {
            'completed' => 'bezahlt',
            default => 'bezahlung ausstehend'
        };

        foreach ($orderStands as $orderStand) {
            $thisStandNumber = wc_get_order_item_meta($orderStand->get_id(), 'Standnummer', true);

            if ($thisStandNumber === $standNumber) {
                $standMeta['width'] = wc_get_order_item_meta($orderStand->get_id(), 'Standbreite', true);
                $standMeta['depth'] = wc_get_order_item_meta($orderStand->get_id(), 'Standtiefe', true);
                $standMeta['total'] = round($orderStand->get_total(), '2') . '€';
                $standMeta['pavillon'] = wc_get_order_item_meta($orderStand->get_id(), 'Pavillon', true);
            }
        }

        return $standMeta;
    }
}
