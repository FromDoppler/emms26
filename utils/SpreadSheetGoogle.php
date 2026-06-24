<?php
require_once 'spread/write.php';

class SpreadSheetGoogle
{
    const RANGE = 'A1:AE1';

    public static function write($idSpreadSheet, $user, $db)
    {
        date_default_timezone_set('America/Argentina/Buenos_Aires');
        $values = array(
            array(
                date('h:i:s A'),
                date('d-m-Y'),
                $user['promotions'],
                $user['privacy'],
                $user['firstname'],
                $user['email'],
                $user['source_utm'] ?? '',
                $user['medium_utm'] ?? '',
                $user['campaign_utm'] ?? '',
                $user['content_utm'] ?? '',
                $user['term_utm'] ?? '',
                $user['origin'] ?? '',
                $user['country_ip'] ?? '',
                $user['ecommerce'] ?? '',
                $user['digital_trends'] ?? '',
                $user['phone'] ?? '',
                $user['emms_ref'] ?? '',
            )
        );

        $paymentValues = self::getPaymentValues($user);
        if ($paymentValues) {
            $values[0] = array_merge($values[0], $paymentValues);
        }

        // Pass Google credentials from global constants
        global $GOOGLE_CLIENT_ID, $GOOGLE_CLIENT_SECRET;
        write_to_sheet($idSpreadSheet, self::RANGE, $values, $db, $GOOGLE_CLIENT_ID, $GOOGLE_CLIENT_SECRET);
    }
    private static function getPaymentValues($user)
    {
        if (!isset($user['payment']) || !is_array($user['payment'])) {
            return null;
        }

        return array(
            $user['payment']['price'] ?? '',
            $user['payment']['discount'] ?? '',
            $user['payment']['final_price'] ?? '',
            $user['payment']['customer_name'] ?? '',
            $user['payment']['customer_email'] ?? '',
            $user['payment']['customer_country'] ?? '',
            $user['payment']['tax_id'] ?? '',
            $user['payment']['payment_status'] ?? '',
            $user['payment']['coupon_id'] ?? '',
            $user['payment']['coupon_name'] ?? '',
            $user['payment']['event_name'] ?? '',
            $user['payment']['event_phase'] ?? '',
            $user['payment']['ticket_name'] ?? '',
            $user['payment']['ticket_price_id'] ?? '',
        );
    }
}
