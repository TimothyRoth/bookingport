<?php

class BOOKINGPORT_Cron
{
    public static BOOKINGPORT_StandStatusHandler $standStatusHandler;
    public static BOOKINGPORT_OrderHandler $orderHandler;

    public function __construct()
    {
        self::$standStatusHandler = new BOOKINGPORT_StandStatusHandler();
        self::$orderHandler = new BOOKINGPORT_OrderHandler();
    }

    public static function init(): void
    {

        date_default_timezone_set("Europe/Berlin");
        setlocale(LC_TIME, 'de_DE', 'de_DE.UTF-8');

        add_filter('cron_schedules', [__CLASS__, 'stand_validate_timestamps']);
        add_filter('cron_schedules', [__CLASS__, 'free_order_items']);

        if (!wp_next_scheduled('stand_validate_timestamps')) {
            wp_schedule_event(time(), 'every_three_minutes', 'stand_validate_timestamps');
        }

        if (!wp_next_scheduled('free_order_items')) {
            wp_schedule_event(time(), 'every_hour', 'free_order_items');
        }

        add_action('stand_validate_timestamps', [self::$standStatusHandler, 'handle_customer_reserved_stand_status_server_side'], 1);
        add_action('stand_validate_timestamps', [self::$standStatusHandler, 'handle_admin_reserved_stand_status_server_side'], 2);
        add_action('free_order_items', [self::$orderHandler, 'set_stands_from_invalid_orders_free'], 1);
    }

    public static function stand_validate_timestamps($schedules): array
    {
        $schedules['every_three_minutes'] = [
            'interval' => 120,
            'display' => __('Every 2 Minutes', 'wp_bookingport')
        ];
        return $schedules;
    }

    public static function free_order_items($schedules): array
    {
        $oneHour = 3600;
        $schedules['every_hour'] = [
            'interval' => $oneHour,
            'display' => __('Every Hour', 'wp_bookingport')
        ];
        return $schedules;
    }
}