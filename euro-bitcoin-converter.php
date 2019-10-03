<?php
/*
Plugin Name: Euro Bitcoin Converter
Version: 1.0
Plugin URI: https://wordpress.org/plugins/euro-bitcoin-converter/
Author: SERGIO CASIZZONE
Author URI: https://www.sergiocasizzone.it/
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
            add_action('wp_enqueue_scripts', 'euro_converter_header_script');
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

function euro_converter_header_script() {
    if (!is_admin()) {
        $plugin_url = plugins_url('', __FILE__);
        wp_register_script('pusher-js', $plugin_url . '/js/pusher.min.js', array(), '4.3.1', false);
        wp_enqueue_script('pusher-js');
    }
}

function euro_converter_footer_script()
{
    echo "<!-- inizio - Euro Bitcoin Converter by SERGIO CASIZZONE -->";
    echo "<script type=\"text/javascript\">\n";
	echo "ebc_base = 0;\n";
	echo "ebc_quote = 0;\n";
	echo "ebc_result = 0;\n";
	echo "function ebc_arrotonda(numero,x) {\n";
	echo "	var number = Math.round(numero*Math.pow(10,x))/Math.pow(10,x);\n";
	echo "	return Number(number.toString().substring(0,11));\n";
	echo "}\n";
	echo "ebc_pusher = new Pusher('de504dc5763aeef9ff52'); // Pusher key for Bitstamp\n";
	echo "var ebc_channel = ebc_pusher.subscribe('live_trades_btceur'); // subscribe live trade data for btc-eur pair\n";
	echo "ebc_channel.bind('trade', function (data) { // callback on message receipt\n";
	echo "	$('#ebc_base').removeAttr('disabled');\n";

	echo "	ebc_base = document.getElementById('ebc_base').value;\n";
	echo "	ebc_quote = data.price;\n";
	echo "	ebc_result = ebc_arrotonda( ebc_base / ebc_quote , 8);\n";

	echo "	$('#ebc_price').html(ebc_quote);\n";
	echo "	$('#ebc_result').html( ebc_result );\n";
	//echo "	pusher.disconnect();\n"; // in this way price updates continuosly
	echo "});\n";

	echo "function ebc_check(){\n";
	echo "	ebc_base = document.getElementById('ebc_base').value;\n";
	echo "	ebc_result = ebc_arrotonda( ebc_base / ebc_quote , 8);\n";
	echo "	$('#ebc_result').html( ebc_result );\n";
	echo "}\n";

	echo "</script>\n";
    echo "<!-- fine - Euro Bitcoin Converter by SERGIO CASIZZONE -->\n";
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
	$output .= "		<div class='ebc_right'><input id='ebc_base' value='1' type='number' disabled='disabled' onchange='ebc_check();' /><i class='fa fa-eur' style='color:#3d9400;'></i></div>";
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
