<?php
/**
 * Frontend Age Gate: Overlay-Ausgabe, Skripte und Styles.
 *
 * @package Kipphard\Altersverifikation
 */

namespace Kipphard\Altersverifikation;

defined( 'ABSPATH' ) || exit;

/**
 * Rendert den Altersverifikations-Overlay auf der Frontend-Seite.
 */
class Gate {

	/**
	 * WordPress-Hooks registrieren.
	 */
	public function hooks() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'wp_head', array( $this, 'inline_critical_css' ) );
		add_action( 'wp_footer', array( $this, 'render_overlay' ) );
	}

	/**
	 * Assets (CSS + JS) einbinden und Konfiguration per wp_localize_script übergeben.
	 */
	public function enqueue_assets() {
		if ( ! $this->should_render() ) {
			return;
		}

		// Shared kip-ui.css zuerst laden, damit die Tokens für gate.css verfügbar sind.
		$has_kip = class_exists( '\Kipphard\Shared\Appearance' );
		$deps     = array();
		if ( $has_kip && is_readable( KIPPHARD_AGE_VERIFICATION_DIR . 'shared/kip-ui.css' ) ) {
			$settings = (array) get_option( Helpers::OPT_SETTINGS, array() );
			wp_enqueue_style( 'kip-ui', KIPPHARD_AGE_VERIFICATION_URL . 'shared/kip-ui.css', array(), KIPPHARD_AGE_VERIFICATION_VERSION );
			wp_add_inline_style( 'kip-ui', \Kipphard\Shared\Appearance::css( $settings, '.kip-ui.kip-age-gate' ) );
			$deps[] = 'kip-ui';
		}

		wp_enqueue_style(
			'kipphard-age-verification-gate',
			KIPPHARD_AGE_VERIFICATION_URL . 'assets/gate.css',
			$deps,
			KIPPHARD_AGE_VERIFICATION_VERSION
		);

		wp_enqueue_script(
			'kipphard-age-verification-gate',
			KIPPHARD_AGE_VERIFICATION_URL . 'assets/gate.js',
			array(),
			KIPPHARD_AGE_VERIFICATION_VERSION,
			true
		);

		// Nur nicht-personenbezogene Anzeigekonfiguration an den Browser übertragen.
		wp_localize_script(
			'kipphard-age-verification-gate',
			'kipphardAgeVerificationData',
			array(
				'minAge'        => (int) Helpers::get( 'min_age' ),
				'mode'          => Helpers::get( 'mode' ),
				'rememberDays'  => (int) Helpers::get( 'remember_days' ),
				'declineAction' => Helpers::get( 'decline_action' ),
				'declineUrl'    => Helpers::get( 'decline_url' ),
				'cookieName'    => 'kipphard_age_verification_ok',
			)
		);
	}

	/**
	 * Minimales Inline-CSS im <head>: Overlay standardmäßig sichtbar (Fail-Safe).
	 * Verhindert Content-Flash, bevor gate.css geladen ist.
	 */
	public function inline_critical_css() {
		if ( ! $this->should_render() ) {
			return;
		}
		// Overlay per CSS sichtbar; JS entfernt es nach Cookie-Prüfung.
		echo '<style id="kipphard-age-verification-critical">#kipphard-age-verification-overlay{display:flex!important}</style>' . "\n";
	}

	/**
	 * Overlay-Markup im Footer ausgeben.
	 */
	public function render_overlay() {
		if ( ! $this->should_render() ) {
			return;
		}

		$heading         = Helpers::get( 'heading' );
		$message         = Helpers::get( 'message' );
		$confirm_label   = Helpers::get( 'confirm_label' );
		$decline_label   = Helpers::get( 'decline_label' );
		$decline_message = Helpers::get( 'decline_message' );
		$mode            = Helpers::get( 'mode' );
		$overlay_color   = Helpers::get( 'overlay_color' );
		$accent_color    = Helpers::get( 'accent_color' );
		$logo_url        = Helpers::get( 'logo_url' );
		$show_credit     = (bool) Helpers::get( 'show_credit' );
		$min_age         = (int) Helpers::get( 'min_age' );

		$overlay_color = $overlay_color ? $overlay_color : '#0d0d0f';
		$accent_color  = $accent_color ? $accent_color : '#f0834e';

		// Inline-Stil: overlay-Farbe + kip-Accenttoken aus plugin-eigenem accent_color.
		$inline_style = sprintf(
			'--kipphard-age-verification-overlay-color:%s;--kipphard-age-verification-accent-color:%s;--kip-accent:%s',
			esc_attr( $overlay_color ),
			esc_attr( $accent_color ),
			esc_attr( $accent_color )
		);

		$has_kip  = class_exists( '\Kipphard\Shared\Appearance' );
		$settings = $has_kip ? (array) get_option( Helpers::OPT_SETTINGS, array() ) : array();
		$styled   = $has_kip && \Kipphard\Shared\Appearance::is_enabled( $settings );
		$kip_atts = $styled ? \Kipphard\Shared\Appearance::data_atts( $settings ) : '';
		$wrap_cls = $styled ? 'kip-ui kip-age-gate' : '';
		?>
		<div id="kipphard-age-verification-overlay" role="dialog" aria-modal="true" aria-labelledby="kipphard-age-verification-heading"
			class="<?php echo esc_attr( $wrap_cls ); ?>"
			style="<?php echo esc_attr( $inline_style ); ?>"<?php echo $kip_atts; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pre-escaped in Appearance::data_atts(). ?>>
			<div class="kipphard-age-verification-card">

				<?php if ( $logo_url ) : ?>
					<img class="kipphard-age-verification-logo" src="<?php echo esc_url( $logo_url ); ?>"
						alt="<?php esc_attr_e( 'Logo', 'kipphard-age-verification' ); ?>">
				<?php endif; ?>

				<h1 id="kipphard-age-verification-heading" class="kipphard-age-verification-heading"><?php echo esc_html( $heading ); ?></h1>

				<div class="kipphard-age-verification-message"><?php echo wp_kses_post( $message ); ?></div>

				<?php if ( 'dob' === $mode ) : ?>
					<div class="kipphard-age-verification-dob-wrap">
						<label for="kipphard-age-verification-day"><?php esc_html_e( 'Date of birth', 'kipphard-age-verification' ); ?></label>
						<div class="kipphard-age-verification-dob-fields">
							<input type="number" id="kipphard-age-verification-day" name="kipphard_age_verification_day" placeholder="TT"
								min="1" max="31" inputmode="numeric" autocomplete="bday-day"
								aria-label="<?php esc_attr_e( 'Day', 'kipphard-age-verification' ); ?>">
							<input type="number" id="kipphard-age-verification-month" name="kipphard_age_verification_month" placeholder="MM"
								min="1" max="12" inputmode="numeric" autocomplete="bday-month"
								aria-label="<?php esc_attr_e( 'Month', 'kipphard-age-verification' ); ?>">
							<input type="number" id="kipphard-age-verification-year" name="kipphard_age_verification_year"
								placeholder="<?php echo esc_attr( gmdate( 'Y' ) ); ?>"
								min="1900" max="<?php echo esc_attr( gmdate( 'Y' ) ); ?>"
								inputmode="numeric" autocomplete="bday-year"
								aria-label="<?php esc_attr_e( 'Year', 'kipphard-age-verification' ); ?>">
						</div>
						<p id="kipphard-age-verification-dob-error" class="kipphard-age-verification-error" aria-live="polite" style="display:none;">
							<?php
							printf(
								/* translators: %d: required minimum age */
								esc_html__( 'You must be at least %d years old to visit this page.', 'kipphard-age-verification' ),
								$min_age
							);
							?>
						</p>
					</div>
				<?php endif; ?>

				<div class="kipphard-age-verification-actions">
					<button type="button" id="kipphard-age-verification-confirm" class="kipphard-age-verification-btn kipphard-age-verification-btn-confirm kip-btn kip-btn--primary">
						<?php echo esc_html( $confirm_label ); ?>
					</button>
					<button type="button" id="kipphard-age-verification-decline" class="kipphard-age-verification-btn kipphard-age-verification-btn-decline kip-btn kip-btn--ghost">
						<?php echo esc_html( $decline_label ); ?>
					</button>
				</div>

				<div id="kipphard-age-verification-decline-message" class="kipphard-age-verification-decline-message" style="display:none;" aria-live="polite">
					<?php echo wp_kses_post( $decline_message ); ?>
				</div>

				<?php if ( $show_credit ) : ?>
					<p class="kipphard-age-verification-credit">
						<a href="https://kipphard.com/products/altersverifikation" target="_blank" rel="noopener noreferrer">
							<?php esc_html_e( 'Altersverifikation by Kipphard', 'kipphard-age-verification' ); ?>
						</a>
					</p>
				<?php endif; ?>

			</div>
		</div>
		<?php
	}

	/**
	 * Ermittelt ob der Overlay auf der aktuellen Seite angezeigt werden soll.
	 *
	 * @return bool
	 */
	public function should_render() {
		// Im Adminbereich nie anzeigen.
		if ( is_admin() ) {
			return false;
		}

		// Login-Seite ausschließen.
		if ( isset( $GLOBALS['pagenow'] ) && 'wp-login.php' === $GLOBALS['pagenow'] ) {
			return false;
		}

		// Never gate admins (unless a filter opts out).
		if ( current_user_can( 'manage_options' ) && apply_filters( 'kipphard_age_verification_bypass_admin', true ) ) {
			return false;
		}

		$scope = Helpers::get( 'scope' );
		if ( 'pages' === $scope ) {
			$pages = (array) Helpers::get( 'pages' );
			if ( empty( $pages ) || ! is_page( $pages ) ) {
				$show = false;
			} else {
				$show = true;
			}
		} else {
			$show = true;
		}

		/**
		 * Erlaubt es anderen Klassen (z. B. der WooCommerce-Add-on-Klasse),
		 * die Gate-Entscheidung zu überschreiben.
		 *
		 * @param bool $show Ob der Overlay angezeigt werden soll.
		 */
		return (bool) apply_filters( 'kipphard_age_verification_should_gate', $show );
	}
}
