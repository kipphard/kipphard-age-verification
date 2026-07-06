<?php
/**
 * Gemeinsame Hilfsmethoden: Capability, Optionen, Sanitisierung.
 *
 * @package Kipphard\Altersverifikation
 */

namespace Kipphard\Altersverifikation;

defined( 'ABSPATH' ) || exit;

/**
 * Zustandslose Hilfsmethoden, die im gesamten Plugin verwendet werden.
 */
class Helpers {

	/** Capability für alle Admin-Aktionen. */
	const CAP = 'manage_options';

	/** Option-Key für die gespeicherten Plugin-Einstellungen. */
	const OPT_SETTINGS = 'kipphard_age_verification_settings';

	/**
	 * Gibt die Standardwerte aller Einstellungen zurück.
	 *
	 * @return array<string,mixed>
	 */
	public static function defaults() {
		$base = array(
			// Allgemein.
			'min_age'        => 18,
			'mode'           => 'confirm',
			'scope'          => 'site',
			'pages'          => array(),
			// Texte.
			'heading'        => __( 'Altersverifikation', 'kipphard-age-verification' ),
			'message'        => __( 'This website contains age-restricted content. Please confirm that you have reached the required minimum age.', 'kipphard-age-verification' ),
			'confirm_label'  => __( 'Yes, I am old enough', 'kipphard-age-verification' ),
			'decline_label'  => __( 'No, I am too young', 'kipphard-age-verification' ),
			// Ablehnen.
			'decline_action'  => 'message',
			'decline_message' => __( 'You must have reached the minimum age to visit this page.', 'kipphard-age-verification' ),
			'decline_url'     => '',
			// Funktionscookie.
			'remember_days'  => 30,
			// Design.
			'overlay_color'  => '#0d0d0f',
			'accent_color'   => '#f0834e',
			'logo_url'       => '',
			'show_credit'    => true,
			// WooCommerce-Add-on (nur im Premium-Build aktiv, siehe class-woocommerce.php).
			'wc_categories'  => array(),
		);
		if ( class_exists( '\Kipphard\Shared\Appearance' ) ) {
			$base = array_merge( $base, \Kipphard\Shared\Appearance::defaults() );
		}
		return $base;
	}

	/**
	 * Liefert den gespeicherten Wert einer Einstellung, mit Fallback auf den Standardwert.
	 *
	 * @param string $key Einstellungsschlüssel.
	 * @return mixed
	 */
	public static function get( $key ) {
		$saved    = (array) get_option( self::OPT_SETTINGS, array() );
		$defaults = self::defaults();
		$merged   = array_merge( $defaults, $saved );
		return isset( $merged[ $key ] ) ? $merged[ $key ] : null;
	}

	/**
	 * Sanitisiert das Einstellungsformular strikt je Feld.
	 *
	 * @param array<string,mixed> $raw Rohe $_POST-Daten.
	 * @return array<string,mixed>
	 */
	public static function sanitize_settings( array $raw ) {
		$clean = array();

		// min_age: Ganzzahl, 0–99.
		$min_age = isset( $raw['min_age'] ) ? absint( $raw['min_age'] ) : 18;
		$clean['min_age'] = min( 99, max( 0, $min_age ) );

		// mode: nur erlaubte Werte.
		$mode = isset( $raw['mode'] ) ? sanitize_key( $raw['mode'] ) : 'confirm';
		$clean['mode'] = in_array( $mode, array( 'confirm', 'dob' ), true ) ? $mode : 'confirm';

		// scope: nur erlaubte Werte.
		$scope = isset( $raw['scope'] ) ? sanitize_key( $raw['scope'] ) : 'site';
		$clean['scope'] = in_array( $scope, array( 'site', 'pages' ), true ) ? $scope : 'site';

		// pages: entweder als Array (multiselect) oder als Rohtext (kommagetrennte IDs).
		if ( isset( $raw['pages'] ) && is_array( $raw['pages'] ) ) {
			$pages = $raw['pages'];
		} elseif ( isset( $raw['kipphard_age_verification_pages_raw'] ) && '' !== trim( $raw['kipphard_age_verification_pages_raw'] ) ) {
			$pages = explode( ',', $raw['kipphard_age_verification_pages_raw'] );
		} else {
			$pages = array();
		}
		$clean['pages'] = array_filter( array_map( 'absint', $pages ) );

		// Textfelder.
		$clean['heading']       = isset( $raw['heading'] ) ? sanitize_text_field( wp_unslash( $raw['heading'] ) ) : '';
		$clean['confirm_label'] = isset( $raw['confirm_label'] ) ? sanitize_text_field( wp_unslash( $raw['confirm_label'] ) ) : '';
		$clean['decline_label'] = isset( $raw['decline_label'] ) ? sanitize_text_field( wp_unslash( $raw['decline_label'] ) ) : '';

		// message und decline_message erlauben einfaches HTML.
		$clean['message']         = isset( $raw['message'] ) ? wp_kses_post( wp_unslash( $raw['message'] ) ) : '';
		$clean['decline_message'] = isset( $raw['decline_message'] ) ? wp_kses_post( wp_unslash( $raw['decline_message'] ) ) : '';

		// Farbfelder.
		$clean['overlay_color'] = isset( $raw['overlay_color'] ) ? sanitize_hex_color( $raw['overlay_color'] ) : '#0d0d0f';
		$clean['accent_color']  = isset( $raw['accent_color'] ) ? sanitize_hex_color( $raw['accent_color'] ) : '#f0834e';

		// decline_action.
		$decline_action = isset( $raw['decline_action'] ) ? sanitize_key( $raw['decline_action'] ) : 'message';
		$clean['decline_action'] = in_array( $decline_action, array( 'message', 'redirect' ), true ) ? $decline_action : 'message';

		// decline_url.
		$clean['decline_url'] = isset( $raw['decline_url'] ) ? esc_url_raw( wp_unslash( $raw['decline_url'] ) ) : '';

		// remember_days: 1–3650.
		$remember_days = isset( $raw['remember_days'] ) ? absint( $raw['remember_days'] ) : 30;
		$clean['remember_days'] = min( 3650, max( 1, $remember_days ) );

		// logo_url.
		$clean['logo_url'] = isset( $raw['logo_url'] ) ? esc_url_raw( wp_unslash( $raw['logo_url'] ) ) : '';

		// Boolesche Felder.
		$clean['show_credit'] = ! empty( $raw['show_credit'] );

		// WooCommerce-Add-on: Produktkategorien (nur im Premium-Build wirksam).
		$wc_cats = isset( $raw['wc_categories'] ) && is_array( $raw['wc_categories'] ) ? $raw['wc_categories'] : array();
		$clean['wc_categories'] = array_map( 'absint', $wc_cats );

		if ( class_exists( '\Kipphard\Shared\Appearance' ) ) {
			$clean = array_merge( $clean, \Kipphard\Shared\Appearance::sanitize( $raw ) );
		}

		return $clean;
	}

	/**
	 * Prüft Capability + Nonce für Admin-POST-Anfragen. Bricht bei Fehler ab.
	 *
	 * @param string $action Nonce-Aktion.
	 * @param string $field  Name des Nonce-Feldes.
	 */
	public static function guard_post( $action, $field = '_wpnonce' ) {
		if ( ! current_user_can( self::CAP ) ) {
			wp_die( esc_html__( 'Permission denied.', 'kipphard-age-verification' ), '', array( 'response' => 403 ) );
		}
		check_admin_referer( $action, $field );
	}
}
