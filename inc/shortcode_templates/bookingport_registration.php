<?php

if (!is_user_logged_in() && isset($_POST['register'])) {
    $fullName = $_POST['first_name'] . ' ' . $_POST['last_name'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $selected_role = $_POST['role'];
    $additional_billing_address = filter_var($_POST['additional_billing_address'], FILTER_VALIDATE_BOOLEAN);

// Register the user using WooCommerce function wc_create_new_customer
    $customer_id = wc_create_new_customer($email, $username, $password);

    if (!is_wp_error($customer_id)) {
        // User registration successful
        // Set the user role based on the selected role
        $user = new WP_User($customer_id);

        $selected_role = match ($selected_role) {
            'privat' => 'privat',
            'gewerblich' => 'gewerblich',
            'verein' => 'verein',
            default => null
        };

        if ($selected_role !== null) {
            $user->set_role($selected_role);
        } else {
            $registration_error = 'Invalid role selected.';
        }

        if (!isset($registration_error)) {

            // Overall Data
            $company = $_POST['company'];
            $first_name = $_POST['first_name'];
            $last_name = $_POST['last_name'];
            $address = $_POST['address'];
            $zip = $_POST['zip'];
            $city = $_POST['city'];
            $phone = $_POST['phone'];

            // Additional Billing Data
            if ($additional_billing_address) {
                $billing_company = $_POST['billing_company'];
                $billing_first_name = $_POST['billing_first_name'];
                $billing_last_name = $_POST['billing_last_name'];
                $billing_address = $_POST['billing_address'];
                $billing_zip = $_POST['billing_zip'];
                $billing_city = $_POST['billing_city'];
                $billing_phone = $_POST['billing_phone'];
            }

            //Save User Data
            if (isset($first_name) && !empty($first_name)) {
                add_user_meta($customer_id, 'first_name', $first_name, true);
            }

            if (isset($last_name) && !empty($last_name)) {
                add_user_meta($customer_id, 'last_name', $last_name, true);
            }

            //Save Account Data
            if (isset($first_name) && !empty($first_name)) {
                add_user_meta($customer_id, 'account_first_name', $first_name, true);
            }

            if (isset($last_name) && !empty($last_name)) {
                add_user_meta($customer_id, 'account_last_name', $last_name, true);
            }

            //Save Billing Data
            if (isset($company) && !empty($company) && !$additional_billing_address) {
                add_user_meta($customer_id, 'billing_company', $company, true);
            }

            if (isset($billing_company) && !empty($billing_company)) {
                add_user_meta($customer_id, 'billing_company', $billing_company, true);
            }

            if (isset($first_name) && !empty($first_name) && !$additional_billing_address) {
                add_user_meta($customer_id, 'billing_first_name', $first_name, true);
            }

            if (isset($billing_first_name) && !empty($billing_first_name)) {
                add_user_meta($customer_id, 'billing_first_name', $billing_first_name, true);
            }

            if (isset($last_name) && !empty($last_name) && !$additional_billing_address) {
                add_user_meta($customer_id, 'billing_last_name', $last_name, true);
            }

            if (isset($billing_last_name) && !empty($billing_last_name)) {
                add_user_meta($customer_id, 'billing_last_name', $billing_last_name, true);
            }

            if (isset($address) && !empty($address) && !$additional_billing_address) {
                add_user_meta($customer_id, 'billing_address_1', $address, true);
            }

            if (isset($billing_address) && !empty($billing_address)) {
                add_user_meta($customer_id, 'billing_address_1', $billing_address, true);
            }

            if (isset($zip) && !empty($zip) && !$additional_billing_address) {
                add_user_meta($customer_id, 'billing_postcode', $zip, true);
            }

            if (isset($billing_zip) && !empty($billing_zip)) {
                add_user_meta($customer_id, 'billing_postcode', $billing_zip, true);
            }

            if (isset($city) && !empty($city) && !$additional_billing_address) {
                add_user_meta($customer_id, 'billing_city', $city, true);
            }

            if (isset($billing_city) && !empty($billing_city)) {
                add_user_meta($customer_id, 'billing_city', $billing_city, true);
            }

            if (isset($phone) && !empty($phone) && !$additional_billing_address) {
                add_user_meta($customer_id, 'billing_phone', $phone, true);
            }

            if (isset($billing_phone) && !empty($billing_phone)) {
                add_user_meta($customer_id, 'billing_phone', $billing_phone, true);
            }

            //Save Shipping Data
            if (isset($company) && !empty($company)) {
                add_user_meta($customer_id, 'shipping_company', $company, true);
            }

            if (isset($first_name) && !empty($first_name)) {
                add_user_meta($customer_id, 'shipping_first_name', $first_name, true);
            }

            if (isset($last_name) && !empty($last_name)) {
                add_user_meta($customer_id, 'shipping_last_name', $last_name, true);
            }

            if (isset($address) && !empty($address)) {
                add_user_meta($customer_id, 'shipping_address_1', $address, true);
            }

            if (isset($zip) && !empty($zip)) {
                add_user_meta($customer_id, 'shipping_postcode', $zip, true);
            }

            if (isset($city) && !empty($city)) {
                add_user_meta($customer_id, 'shipping_city', $city, true);
            }

            if (isset($phone) && !empty($phone)) {
                add_user_meta($customer_id, 'shipping_phone', $phone, true);
            }

            // Send verification email
            $verification_token = wp_generate_password(32, false);
            update_user_meta($customer_id, 'verification_token', $verification_token);

            $verification_link = add_query_arg([
                'action' => 'verify_email',
                'token' => $verification_token,
            ], site_url());

            $headers = [
                'MIME-Version: 1.0',
                'Content-type: text/html; charset=UTF-8'
            ];

            $additional_headers = implode("\r\n", $headers);

            $bookingport_settings = new BOOKINGPORT_Settings();
            $options_table = get_option($bookingport_settings::$option_table);
            $email_logo = $options_table[$bookingport_settings::$option_email_registration_successful_logo];
            $email_subject = $options_table[$bookingport_settings::$option_email_registration_successful_subject];
            $email_title = $options_table[$bookingport_settings::$option_email_registration_successful_title];
            $email_homepage_title = $options_table[$bookingport_settings::$option_email_registration_successful_registration_page];
            $email_body = $options_table[$bookingport_settings::$option_email_registration_successful_body];
            $email_footer = $options_table[$bookingport_settings::$option_email_registration_successful_footer];

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
            table, td{mso-table-lspace:0pt; mso-table-rspace:0pt;} /* Remove spacing between tables in Outlook 2007 and up */
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
                <td bgcolor="#ffffff" align="left"  style="padding: 0px 15px 0px 15px;">
                    <table border="0" cellpadding="0" cellspacing="0" width="500" class="responsive-table">
                        <tr>
                            <td align="left"  style="padding: 0px 15px 0px 15px;">
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
                                                    <td align="left" style="padding: 20px 0 0 0; font-size: 16px; line-height: 25px; font-family: Helvetica, Arial, sans-serif; color: #666666;" class="padding-copy">Sehr geehrte/r ' . $first_name . ' ' . $last_name . ' ,</td>
                                                </tr>
                                                <tr>
                                                    <td align="left" style="font-size: 25px; font-family: Helvetica, Arial, sans-serif; color: #333333; padding-top: 30px;" class="padding-copy">
                                                        Vielen Dank für Ihre Anmeldung auf ' . $email_homepage_title . '</td>
                                                </tr>
                                                <tr>
                                                    <td align="left" style="padding: 20px 0 0 0; font-size: 16px; line-height: 25px; font-family: Helvetica, Arial, sans-serif; color: #666666;" class="padding-copy">
                                                        Bitte klicken Sie auf folgenden Link um die Einrichtung Ihres Benutzerkontos auf ' . $email_homepage_title . ' abzuschließen: <a href="' . $verification_link . '">' . $verification_link . '</a>
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
                            <td align="left"  style="padding: 0px 15px 0px 15px;">
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

            wp_mail($email, $email_subject, $email_message, $additional_headers);
            $bookingport_settings = new BOOKINGPORT_Settings();
            $option_table = get_option($bookingport_settings::$option_table);
            // Redirect to the successful registration page
            $_SESSION['username'] = $fullName;
            wp_redirect('/' . $option_table[$bookingport_settings::$option_redirects_registration_successful]);
            exit();
        }
    } else {
// User registration failed
        $registration_error = $customer_id->get_error_message(); ?>
    <?php }

} ?>
<?php if (!is_user_logged_in()) { ?>
    <div class="woocommerce">

        <div class="wrapper">
            <h1><?php the_title(); ?></h1>
        </div>

        <form method="post" class="woocommerce-form woocommerce-form-register">

            <?php do_action('woocommerce_register_form_start'); ?>

            <section id="form-login-data">
                <div class="wrapper">
                    <div>
                        <h3>Zugangsdaten</h3>

                        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                            <input type="text" class="woocommerce-Input woocommerce-Input--text input-text"
                                   placeholder="Benutzername*"
                                   name="username" id="reg_company" required/>
                        </p>

                        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                            <input type="password" class="woocommerce-Input woocommerce-Input--text input-text"
                                   placeholder="Passwort*"
                                   name="password" id="reg_password" required/>
                        </p>

                        <div class="password-strength shrink">
                            <div id="password-strength-meter"></div>
                            <div class="password-strength-label"></div>
                        </div>

                        <div class="password-requirements-container">
                            <div class="icon">Bedingungen für Ihre Passwortvergabe</div>
                            <div class="password-requirements">
                                <p>Um die Sicherheit Ihres Kontos zu gewährleisten, sollte Ihr Passwort folgende
                                    Kriterien erfüllen:</p>
                                <ul>
                                    <li>Mindestens 8 Zeichen Länge</li>
                                    <li>Verwenden Sie eine Kombination aus Groß- und Kleinbuchstaben</li>
                                    <li>Integrieren Sie Zahlen und Sonderzeichen</li>
                                    <li>Vermeiden Sie aufeinanderfolgende oder sich wiederholende Zeichen</li>
                                </ul>
                                <p>Wenn Ihr Passwort diese Anforderungen erfüllt, wird der Balken grün, und Sie
                                    können
                                    die Registrierung abschließen.</p>
                            </div>
                        </div>


                    </div>
                    <div>
                        <h3>Standbuchung*</h3>
                        <div
                                class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide radio-button-wrapper">
                            <div>
                                <input type="radio" name="role" id="privat" value="privat" required>
                                <label for="privat">Privat</label>
                            </div>
                            <div>
                                <input type="radio" name="role" id="gewerblich" value="gewerblich" required>
                                <label for="gewerblich">Gewerblich</label>
                            </div>
                            <div>
                                <input type="radio" name="role" id="verein" value="verein" required>
                                <label for="verein">Verein</label>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="form-personal-data">
                <div class="wrapper">
                    <h3>Persönliche Daten</h3>
                    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                        <input type="text" class="woocommerce-Input woocommerce-Input--text input-text"
                               placeholder="Firma/Verein (optional)"
                               name="company" id="reg_company"/>
                    </p>
                    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                        <input type="email" class="woocommerce-Input woocommerce-Input--text input-text"
                               placeholder="E-Mail-Adresse*"
                               name="email" id="reg_email" required/>
                    </p>
                    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                        <input type="text" class="woocommerce-Input woocommerce-Input--text input-text"
                               placeholder="Vorname*"
                               name="first_name" id="reg_username" required/>
                    </p>
                    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                        <input type="text" class="woocommerce-Input woocommerce-Input--text input-text"
                               placeholder="Nachname*"
                               name="last_name" id="reg_last_name" required/>
                    </p>
                    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                        <input type="text" class="woocommerce-Input woocommerce-Input--text input-text"
                               placeholder="Straße + Hausnummer*"
                               name="address" id="reg_address" required/>
                    </p>
                    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                        <input type="number" class="woocommerce-Input woocommerce-Input--text input-text"
                               placeholder="PLZ*"
                               name="zip" id="reg_zip" required/>
                    </p>
                    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                        <input type="text" class="woocommerce-Input woocommerce-Input--text input-text"
                               placeholder="Ort*"
                               name="city" id="reg_city" required/>
                    </p>
                    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                        <input type="number" class="woocommerce-Input woocommerce-Input--text input-text"
                               placeholder="Telefon*"
                               name="phone" id="reg_phone" required/>
                    </p>
                </div>
            </section>

            <section id="form-alternative-billing-address">
                <div class="wrapper">
                    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                        <input type="checkbox" id="additional_billing_address" name="additional_billing_address">
                        <label for="additional_billing_address">Abweichende Rechnungsanschrift?</label>
                    </p>
                    <div class="alternative-billing-address-content">
                        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                            <input type="text" class="woocommerce-Input woocommerce-Input--text input-text"
                                   placeholder="Firma/Verein (optional)"
                                   name="billing_company" id="reg_company"/>
                        </p>
                        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                            <input type="text"
                                   class="itsRequired woocommerce-Input woocommerce-Input--text input-text"
                                   placeholder="Vorname*"
                                   name="billing_first_name" id="reg_username"/>
                        </p>
                        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                            <input type="text"
                                   class="itsRequired woocommerce-Input woocommerce-Input--text input-text"
                                   placeholder="Nachname*"
                                   name="billing_last_name" id="reg_last_name"/>
                        </p>
                        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                            <input type="text"
                                   class="itsRequired woocommerce-Input woocommerce-Input--text input-text"
                                   placeholder="Straße + Hausnummer*"
                                   name="billing_address" id="reg_address"/>
                        </p>
                        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                            <input type="number"
                                   class="itsRequired woocommerce-Input woocommerce-Input--text input-text"
                                   placeholder="PLZ*"
                                   name="billing_zip" id="reg_zip"/>
                        </p>
                        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                            <input type="text"
                                   class="itsRequired woocommerce-Input woocommerce-Input--text input-text"
                                   placeholder="Ort*"
                                   name="billing_city" id="reg_city"/>
                        </p>
                        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                            <input type="number"
                                   class="itsRequired woocommerce-Input woocommerce-Input--text input-text"
                                   placeholder="Telefon*"
                                   name="billing_phone" id="reg_phone"/>
                        </p>
                    </div>
                </div>
            </section>


            <?php do_action('woocommerce_register_form'); ?>

            <section id="form-submit">
                <div class="wrapper">
                    <p class="woocommerce-form-row form-row">
                        <input type="checkbox" id="accept_data_privacy"" class="woocommerce-Button button"
                        name="accept_data_privacy"
                        required/>
                        <label for="accept_data_privacy">Die Hinweise zum <a href="/datenschutz">Datenschutz</a>
                            habe ich gelesen und verstanden.</label>
                    </p>
                    <p class="woocommerce-form-row form-row">
                        <input type="submit" class="woocommerce-Button button btn" name="register"
                               value="Neues Konto anmelden"/>
                    </p>
                    <p class="woocommerce-form-row form-row">
                        * Pflichtfelder
                    </p>
                </div>
            </section>

            <?php do_action('woocommerce_register_form_end'); ?>

        </form>
    </div>

    <section id="error-messages">
        <div class="wrapper">

            <?php if (isset($registration_error)) { ?>
                <p class="registration-error"><?php echo strip_tags($registration_error); ?></p>
            <?php } ?>


            <?php if (isset($login_error)) { ?>
                <p class="login-error"><?php echo esc_html($login_error); ?></p>
            <?php } ?>
        </div>
    </section>

<?php } else {
    $bookingport_settings = new BOOKINGPORT_Settings();
    $option_table = get_option($bookingport_settings::$option_table);
    wp_redirect('/' . $option_table[$bookingport_settings::$option_redirects_dashboard]);
} ?>
