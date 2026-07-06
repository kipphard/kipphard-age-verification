<?php
/**
 * Plugin Name:       Kipphard Age Verification
 * Plugin URI:        https://kipphard.com/products/altersverifikation
 * Description:       Shows a GDPR-compliant full-screen age gate overlay before visitors can access content. Date-of-birth verification runs entirely in the browser — no personal data is sent to the server.
 * Version:           0.4.0
 * Requires at least: 6.4
 * Requires PHP:      7.4
 * Author:            André Kipphard
 * Author URI:        https://kipphard.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       kipphard-age-verification
 * Domain Path:       /languages
 *
 * @package Kipphard\Altersverifikation
 */

defined( 'ABSPATH' ) || exit;

define( 'KIPPHARD_AGE_VERIFICATION_VERSION', '0.4.0' );
define( 'KIPPHARD_AGE_VERIFICATION_FILE', __FILE__ );
define( 'KIPPHARD_AGE_VERIFICATION_DIR', plugin_dir_path( __FILE__ ) );
define( 'KIPPHARD_AGE_VERIFICATION_URL', plugin_dir_url( __FILE__ ) );
define( 'KIPPHARD_AGE_VERIFICATION_SLUG', 'kipphard-age-verification' );

/**
 * Minimaler PSR-4-Autoloader für den Namespace Kipphard\Altersverifikation\.
 * Kipphard\Altersverifikation\Foo_Bar -> includes/class-foo-bar.php
 */
spl_autoload_register(
	static function ( $class ) {
		$prefix = 'Kipphard\\Altersverifikation\\';
		if ( strncmp( $class, $prefix, strlen( $prefix ) ) !== 0 ) {
			return;
		}
		$relative = substr( $class, strlen( $prefix ) );
		$file     = 'class-' . strtolower( str_replace( '_', '-', $relative ) ) . '.php';
		$path     = KIPPHARD_AGE_VERIFICATION_DIR . 'includes/' . $file;
		if ( is_readable( $path ) ) {
			require_once $path;
		}
	}
);

// Shared design system (kip-ui). Injected into the build at /shared by build-zip;
// guarded so the plugin still runs unstyled if it's absent.
$kipphard_age_verification_shared_autoload = KIPPHARD_AGE_VERIFICATION_DIR . 'shared/autoload.php';
if ( is_readable( $kipphard_age_verification_shared_autoload ) ) {
	require_once $kipphard_age_verification_shared_autoload;
}

register_activation_hook( __FILE__, array( '\Kipphard\Altersverifikation\Plugin', 'activate' ) );

add_action(
	'plugins_loaded',
	static function () {
		\Kipphard\Altersverifikation\Plugin::instance()->boot();
	}
);
