<?php
/*
Plugin Name: Euro Bitcoin Converter
Version: 1.1
Plugin URI: https://wordpress.org/plugins/euro-bitcoin-converter/
Author: SERGIO CASIZZONE
Author URI: https://sergiocasizzone.altervista.org/
Description: Crea un widget in cui inserendo la quantità di euro converte all'istante il valore in Bitcoin. Il prezzo si aggiorna automaticamente, non è necessario aggiornare la pagina. Shortcode: <code>[euro_converter]</code>
Text Domain: euro-bitcoin-converter
Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit;
}
if (!class_exists('EURO_BITCOIN_CONVERTER')) {

    class EURO_BITCOIN_CONVERTER_WIDGET {

        function __construct() {
            $this->euro_converter_plugin_includes();
        }

        function euro_converter_plugin_includes() {
            add_action('plugins_loaded', array($this, 'plugins_loaded_handler'));
            add_shortcode('euro_converter', 'euro_converter_init');
            //allows shortcode execution in the widget, excerpt and content
            add_filter('widget_text', 'do_shortcode');
            add_filter('the_excerpt', 'do_shortcode', 11);
            add_filter('the_content', 'do_shortcode', 11);
        }

        function plugin_url() {
            if ($this->plugin_url)
                return $this->plugin_url;
            return $this->plugin_url = plugins_url(basename(plugin_dir_path(__FILE__)), basename(__FILE__));
        }

        function plugins_loaded_handler()
        {
            load_plugin_textdomain('clappr', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/');
        }
    }
    $GLOBALS['euro_bitcoin_converter'] = new EURO_BITCOIN_CONVERTER_WIDGET();
}

function euro_converter_footer_script()
{
	echo "<!-- inizio - real_time_btc_price_bitstamp by SERGIO CASIZZONE -->";
	echo "<script src='https://code.jquery.com/jquery-1.12.4.js'></script>";
	echo "<script src='https://code.jquery.com/ui/1.12.1/jquery-ui.js'></script>";
	echo "<script type=\"text/javascript\">\n";

	echo "ebc_base = 0;\n";
	echo "ebc_quote = 0;\n";
	echo "ebc_result = 0;\n";

	echo "var ws = new WebSocket('wss://ws.bitstamp.net');"; // New websocket v. 2 for Bitstamp
	echo "var subscription = {
			'event': 'bts:subscribe',
			'data': {
				'channel': 'live_trades_btceur'
			}
	};"; // Creo l'oggetto per la sottoscrizione del canale

		echo "ws.onopen = function () {
		ws.send(JSON.stringify(subscription));
	};"; // invio la sottoscrizione

	echo "ws.onmessage = function (evt) {
		response = JSON.parse(evt.data);
		switch (response.event) {
			case 'trade': {
				$('#ebc_price').html(response.data.price);
				// Run the effect
				var options = {'color':'green'};
  				$( '#ebc_price' ).effect( 'highlight', options, 500, callback );
				ebc_quote = response.data.price;
				console.log('[Bitstamp WebSocket]:',response.data.price);
				break;
			}
			case 'bts:request_reconnect': {
				ws = new WebSocket('wss://ws.bitstamp.net');
				break;
			}
		}
	};"; // gestisco il response

	echo "// Callback function to bring a hidden box back
			function callback() {
  				setTimeout(function() {
    				$( '#ebc_price' ).removeAttr( 'style' ).hide().fadeIn();
  				}, 1000 );
	};";

	echo "function ebc_arrotonda(numero,x) {\n";
	echo "	var number = Math.round(numero*Math.pow(10,x))/Math.pow(10,x);\n";
	echo "	return Number(number.toString().substring(0,11));\n";
	echo "}\n";

	echo "function ebc_check(){\n";
	echo "	ebc_base = document.getElementById('ebc_base').value;\n";
	echo "	ebc_result = ebc_arrotonda( ebc_base / ebc_quote , 8);\n";
	echo "	$('#ebc_result').html( ebc_result );\n";
	echo "}\n";

	echo "</script>\n";
	echo "<!-- fine - real_time_btc_price_bitstamp by SERGIO CASIZZONE -->\n";
}

function euro_converter_init($atts) {
    $plugin_url = plugins_url('', __FILE__);
    $styles = '';

    add_action('wp_footer', 'euro_converter_footer_script');

    extract(shortcode_atts(array(
        'size' => '',
        'color' => ''
    ), $atts));

    if ($size || $color) {
        $styles = "style=\"font-size: $size; color: $color;\"";
    }
    $output = "<span id=\"euro_bitcoin_converter\" $styles>";
	$output .= "<style type='text/css'> @import '".$plugin_url."/css/index.css'; </style>";
	//$output .= "<div id='ebc_title'>Convertitore Euro Bitcoin</div>";
	$output .= "	<div id='ebc_container'>";
	$output .= "		<div class='ebc_left'>Bitcoin price in €</div>";
	$output .= "		<div class='ebc_right'><span id='ebc_price'><img src='".$plugin_url."/images/loading.gif' width='20' height='20' alt='loading...' /></span></div>";
	$output .= "		<br><br>";
	$output .= "		<div class='ebc_left'>Amount</div>";
	$output .= "		<div class='ebc_right'><input id='ebc_base' value='1' type='number' onchange='ebc_check();' /><i class='fa fa-eur' style='color:#3d9400;'></i></div>";
	$output .= "		<br>";
	$output .= "		<div class='ebc_left'>Bitcoin changeover</div>";
	$output .= "		<div class='ebc_right'><span id='ebc_result'></span><i class='fa fa-btc' style='color:#FF6600;'></i></div>";
	$output .= "	</div>";
	$output .= "	<div id='ebc_footer'>";
	$output .= "		<p>Made with <span class='ebc_love'>❤️</span>  by <i><b>Sergio Casizzone</b></i></p>";
	$output .= "	</div>";
    $output .= "</span>";

    return $output;
}
?>
