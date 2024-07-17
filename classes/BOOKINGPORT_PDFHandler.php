<?php

class BOOKINGPORT_PDFHandler
{

    public function __construct()
    {
        // Intentionally left blank.
    }

    public static function init(): void
    {
        add_action('wpo_wcpdf_after_document', [__CLASS__, 'wpo_wcpdf_show_bank_details'], 10, 3);
    }

    public static function wpo_wcpdf_show_bank_details($template_type, $order): void
    {
        $payment_method = $order->get_payment_method();
        $gateways = WC()->payment_gateways->payment_gateways();
        $instructions = $gateways['cod']->instructions;

        if ($payment_method === 'cod') {
            echo $instructions;
        }
    }
}