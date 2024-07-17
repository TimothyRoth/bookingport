<?php

class BOOKINGPORT_User
{
    public function __construct()
    {
        // Intentionally left blank.
    }

    public static function init(): void
    {

        add_action('init', [__CLASS__, 'add_user_roles']);
        add_filter('manage_users_columns', [__CLASS__, 'add_verification_status_column']);
        add_action('manage_users_custom_column', [__CLASS__, 'display_verification_status_column'], 10, 3);
        add_action('admin_head', [__CLASS__, 'enqueue_custom_admin_styles']);

    }

    public static function add_user_roles(): void
    {
        add_role('privat', 'Privat', [
            'read' => true,
        ]);

        add_role('gewerblich', 'Gewerblich', [
            'read' => true,
        ]);

        add_role('verein', 'Verein', [
            'read' => true,
        ]);
    }

    public static function is_user_verified($user_id): bool
    {
        $token = get_user_meta($user_id, 'verification_token', true);
        return empty($token);
    }

    public static function add_verification_status_column($columns)
    {
        $columns['verification_status'] = 'Account Status'; // Customize the column title
        return $columns;
    }

    public static function display_verification_status_column($value, $column_name, $user_id)
    {
        if ($column_name === 'verification_status') {
            if (self::is_user_verified($user_id)) {
                return '<span class="verified-user-row">Anmeldung bestätigt</span>';
            }
            return '<span class="unverified-user-row">Bestätigung Ausstehend</span>';
        }
        return $value;
    }

    public static function enqueue_custom_admin_styles(): void
    {
        global $pagenow;

        if ($pagenow === 'users.php') {
            ob_start(); ?>

            <style>
                .verified-user-row {
                    background-color: #b8e994; /* Green for verified users */
                }

                .unverified-user-row {
                    background-color: #f78fb3; /* Red for unverified users */
                }
            </style> <?php

            echo ob_get_clean();
        }
    }
}