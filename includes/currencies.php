<?php
global $rwstripe_currency_variations;
$rwstripe_currency_variations = array( 
'EUR' => array(
    'symbol' => '&euro;',
    'position' => apply_filters("pmpro_euro_position", rwstripe_euro_position_from_locale())
    ),				
'GBP' => array(
    'symbol' => '&pound;',
    'position' => 'left'
    ),
'BRL' => array(
    'symbol' => 'R&#36;',
    'position' => 'left'
    ),
'CZK' => array(
            'decimals' => '2',
            'thousands_separator' => '&nbsp;',
            'decimal_separator' => ',',
            'symbol' => '&nbsp;Kč',
            'position' => 'right',
    ),
'DKK' => array(
    'decimals' => '2',
    'thousands_separator' => '&nbsp;',
    'decimal_separator' => ',',
    'symbol' => 'DKK&nbsp;',
    'position' => 'left',
    ),
'GHS' => array(
    'symbol' => '&#8373;',
    'position' => 'left',
    ),
'JPY' => array(
    'symbol' => '&yen;',
    'position' => 'left',
    'decimals' => 0,
    ),
'RON' => array(	
        'decimals' => '2',
        'thousands_separator' => '.',
        'decimal_separator' => ',',
        'symbol' => '&nbsp;Lei',
        'position' => 'right'
),
'RUB' => array(
    'decimals' => '2',
    'thousands_separator' => '&nbsp;',
    'decimal_separator' => ',',
    'symbol' => '&#8381;',
    'position' => 'right'
),
'SGD' => array(
    'symbol' => '&#36;',
    'position' => 'right'
    ),
'ZAR' => array(
    'symbol' => 'R ',
    'position' => 'left'
),			
'KRW' => array(
    'decimals' => 0,
    ),
'UAH' => array(
    'decimals' => 0,
    'thousands_separator' => '',
    'decimal_separator' => ',',
    'symbol' => '&#8372;',
    'position' => 'right'
    ),
'VND' => array(
    'decimals' => 0,
    ),
);

	
/**
 * Get the Euro position based on locale.
 * English uses left, others use right.
 */
function rwstripe_euro_position_from_locale($position = 'right') {
    $locale = get_locale();
    if(strpos($locale, 'en_') === 0) {
        $position = 'left';
    }
    return $position;
}

/**
 * Format a Stripe price for display.
 *
 * @since 1.0
 *
 * @param Stripe/Price $price The price to format.
 */
function rwstripe_format_price( $price ) {
    global $rwstripe_currency_variations;

    // Set up a default currency format.
	$currency_format = array(
        'decimals' => '2',
        'thousands_separator' => ',',
        'decimal_separator' => '.',
        'symbol' => '&#36;',
        'position' => 'left',
    );

    // If Stripe Price has a different currency, check if we have a format for it.
    if ( ! empty( $rwstripe_currency_variations[ strtoupper( $price->currency ) ] ) ) {
        // We have a varaition for this currency. Merge it with the default.
        $currency_format = array_merge( $currency_format, $rwstripe_currency_variations[ strtoupper( $price->currency ) ] );
    }

    $formatted = number_format(
        (float) $price->unit_amount / (float) pow( 10, $currency_format['decimals'] ),
        $currency_format['decimals'],
        $currency_format['decimal_separator'],
        $currency_format['thousands_separator']
    );

    // which side is the symbol on?
    if ( $currency_format['position'] == 'left' ) {
        $formatted = $currency_format['symbol'] . $formatted;
    } else {
        $formatted = $formatted . $currency_format['symbol'];
    }

    // Trim empty decimals off the end.
    $formatted = preg_replace( '/' . preg_quote( $currency_format['decimal_separator'], '/' ) . '0+$/', '', $formatted );

    // If this is a recurring price, append that information.
    if ( $price->recurring ) {
        $interval_count_string = '';
        if ( intval( $price->recurring->interval_count ) > 1 ) {
            $interval_count_string = $price->recurring->interval_count . ' ';
        }
        $formatted .= ' / ' . $interval_count_string . $price->recurring->interval;
    }

    return $formatted;
}
