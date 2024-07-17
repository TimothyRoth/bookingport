<?php

class BOOKINGPORT_OrderHandler
{
    public static function set_stands_from_invalid_orders_free(): void
    {

        $twoHours = 7200;

        $orders = wc_get_orders([
            'limit' => -1,
            'status' => ['failed', 'cancelled'],
            'date_created' => '>=' . (time() - $twoHours)
        ]);

        foreach ($orders as $order) {

            if ($order->get_meta('order_items_removed')) {
                continue;
            }

            $stands = $order->get_items();

            $order->update_meta_data('order_items_removed', true);
            $order->save();

            foreach ($stands as $stand) {

                $stand_args = [
                    'post_type' => BOOKINGPORT_CptStands::$Cpt_Stands,
                    'fields' => 'ids',
                    'posts_per_page' => -1,
                    'meta_query' => [
                        'relation' => 'AND',
                        [
                            'key' => BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeneralNumber,
                            'value' => $stand->get_meta('Standnummer'),
                            'compare' => '='
                        ],
                        [
                            'key' => BOOKINGPORT_CptStands::$Cpt_Stand_Meta_GeoStreetname,
                            'value' => $stand->get_meta('Straße'),
                            'compare' => '='
                        ]
                    ]
                ];

                $post_data = new WP_Query($stand_args);
                $stand_id = $post_data->posts[0];

                BOOKINGPORT_StandStatusHandler::reset_stand_data($stand_id);

            }
        }
    }
}