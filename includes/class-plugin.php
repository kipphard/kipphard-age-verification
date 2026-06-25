<?php
/**
 * Plugin-Bootstrap: Hooks, Admin-UI und Gate registrieren.
 *
 * @package Kipphard\Altersverifikation
 */

namespace Kipphard\Altersverifikation;

defined( 'ABSPATH' ) || exit;

/**
 * Singleton-Einstiegspunkt.
 */
final class Plugin {

	/** @var Plugin|null */
	private static $instance = null;

	/**
	 * @return Plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Private Konstruktor (Singleton).
	 */
	private function __construct() {}

	/**
	 * Aktivierung: Standard-Einstellungen anlegen falls noch nicht vorhanden.
	 */
	public static function activate() {
		if ( false === get_option( Helpers::OPT_SETTINGS, false ) ) {
			add_option( Helpers::OPT_SETTINGS, Helpers::defaults() );
		}
	}

	/**
	 * Laufzeit-Hooks registrieren.
	 */
	public function boot() {
		load_plugin_textdomain(
			'altersverifikation',
			false,
			dirname( plugin_basename( AVF_FILE ) ) . '/languages'
		);

		( new Gate() )->hooks();

		// Pro-only: nur laden wenn die (premium) WooCommerce-Klasse im Build vorhanden ist.
		// Der freie Build (öffentliches Repo / WP.org) enthält diese Datei nicht.
		if ( class_exists( __NAMESPACE__ . '\\Woocommerce' ) ) {
			( new Woocommerce() )->hooks();
		}

		if ( is_admin() ) {
			( new Admin() )->hooks();
		}
	}
}
