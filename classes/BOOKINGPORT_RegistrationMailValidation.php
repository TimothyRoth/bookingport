<?php

class BOOKINGPORT_RegistrationMailValidation
{
    public function __construct()
    {
        // Intentionally left blank.
    }

    public static function init(): void
    {

        add_action('init', [__CLASS__, 'email_verification']);
        add_filter('wp_authenticate_user', [__CLASS__, 'check_email_verification_before_woocommerce_login'], 10, 2);

    }

    public static function email_verification(): void
    {
        if (isset($_GET['action'], $_GET['token']) && $_GET['action'] === 'verify_email') {
            $token = sanitize_text_field($_GET['token']);
            $user_id = get_users(['meta_key' => 'verification_token', 'meta_value' => $token, 'number' => 1])[0]->ID;

            if ($user_id) {
                delete_user_meta($user_id, 'verification_token');
                wp_set_auth_cookie($user_id);

                $option_table = get_option(BOOKINGPORT_Settings::$option_table);

                wp_redirect('/' . $option_table[BOOKINGPORT_Settings::$option_redirects_email_verification_successful]);
                exit();
            }
        }
    }

    public static function check_email_verification_before_woocommerce_login($user, $password)
    {
        if (!is_wp_error($user) && $user instanceof WP_User) {
            // Check if the user's email has been verified
            $verification_token = get_user_meta($user->ID, 'verification_token', true);

            if ($verification_token) {
                // Remove standard WooCommerce login error message
                add_filter('login_errors', [__CLASS__, 'customize_login_error_for_verified_users']);

                // Create a new WooCommerce error instance
                $error_message = __('Bitte schauen Sie in Ihr E-Mail Postfach und klicken Sie den Bestätigungslink um Ihre Registrierung abzuschließen');
                wc_add_notice($error_message, 'error');
                $user = new WP_Error('email_not_verified', $error_message);
            }
        }

        // Return the user or error based on email verification status
        return $user;
    }

    public static function customize_login_error_for_verified_users($error)
    {
        // Check if the error message contains "Ungültiger Benutzername oder Passwort"
        if (str_contains($error, 'Ungültiger Benutzername oder Passwort')) {
            // Remove the standard error message
            $error = '';
        }
        return $error;
    }

}