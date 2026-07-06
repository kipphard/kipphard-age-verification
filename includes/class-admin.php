<?php
/**
 * WordPress Admin-UI: Menüs, Seiten und POST-Handler.
 *
 * @package Kipphard\Altersverifikation
 */

namespace Kipphard\Altersverifikation;

defined( 'ABSPATH' ) || exit;

/**
 * Registriert Admin-Menüs und verarbeitet Formular-Einsendungen.
 */
class Admin {

	/**
	 * Alle WordPress-Hooks für den Adminbereich registrieren.
	 */
	public function hooks() {
		add_action( 'admin_menu', array( $this, 'register_menus' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'admin_post_kipphard_age_verification_save_settings', array( $this, 'handle_save_settings' ) );
	}

	/**
	 * Hauptmenüeintrag registrieren.
	 */
	public function register_menus() {
		add_menu_page(
			__( 'Altersverifikation', 'kipphard-age-verification' ),
			__( 'Altersverifikation', 'kipphard-age-verification' ),
			Helpers::CAP,
			KIPPHARD_AGE_VERIFICATION_SLUG,
			array( $this, 'render_settings' ),
			'dashicons-lock',
			81
		);
	}

	/**
	 * Admin-Assets nur auf Plugin-Seiten einbinden.
	 *
	 * @param string $hook Aktueller Admin-Seiten-Hook-Suffix.
	 */
	public function enqueue_assets( $hook ) {
		if ( 'toplevel_page_' . KIPPHARD_AGE_VERIFICATION_SLUG !== $hook ) {
			return;
		}
		wp_enqueue_style(
			'kipphard-age-verification-admin',
			KIPPHARD_AGE_VERIFICATION_URL . 'assets/admin.css',
			array(),
			KIPPHARD_AGE_VERIFICATION_VERSION
		);
		wp_enqueue_script(
			'kipphard-age-verification-admin',
			KIPPHARD_AGE_VERIFICATION_URL . 'assets/admin.js',
			array(),
			KIPPHARD_AGE_VERIFICATION_VERSION,
			true
		);
		if ( is_readable( KIPPHARD_AGE_VERIFICATION_DIR . 'shared/kip-admin.css' ) ) {
			wp_enqueue_style( 'kip-admin', KIPPHARD_AGE_VERIFICATION_URL . 'shared/kip-admin.css', array(), KIPPHARD_AGE_VERIFICATION_VERSION );
		}
	}

	// -------------------------------------------------------------------------
	// POST-Handler
	// -------------------------------------------------------------------------

	/**
	 * Verarbeitet das Einstellungsformular.
	 */
	public function handle_save_settings() {
		Helpers::guard_post( 'kipphard_age_verification_save_settings' );

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce verified in Helpers::guard_post() above.
		$clean = Helpers::sanitize_settings( $_POST );
		update_option( Helpers::OPT_SETTINGS, $clean );

		wp_safe_redirect(
			add_query_arg(
				array(
					'page'   => KIPPHARD_AGE_VERIFICATION_SLUG,
					'notice' => 'saved',
				),
				admin_url( 'admin.php' )
			)
		);
		exit;
	}

	// -------------------------------------------------------------------------
	// Seiten-Renderer
	// -------------------------------------------------------------------------

	/**
	 * Einstellungsseite ausgeben.
	 */
	public function render_settings() {
		if ( ! current_user_can( Helpers::CAP ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only display value, no state change.
		$notice      = isset( $_GET['notice'] ) ? sanitize_key( $_GET['notice'] ) : '';
		$has_woo_pro = class_exists( __NAMESPACE__ . '\\Woocommerce' );
		$s           = Helpers::defaults();
		$saved       = (array) get_option( Helpers::OPT_SETTINGS, array() );
		$s           = array_merge( $s, $saved );

		$mode_options = array(
			'confirm' => __( 'Confirmation (Yes / No)', 'kipphard-age-verification' ),
			'dob'     => __( 'Enter date of birth', 'kipphard-age-verification' ),
		);
		$scope_options = array(
			'site'  => __( 'Entire website', 'kipphard-age-verification' ),
			'pages' => __( 'Specific pages only', 'kipphard-age-verification' ),
		);
		$decline_action_options = array(
			'message'  => __( 'Show message', 'kipphard-age-verification' ),
			'redirect' => __( 'Redirect to URL', 'kipphard-age-verification' ),
		);
		?>
		<div class="wrap kipphard-age-verification-wrap kip-admin">
			<h1><?php esc_html_e( 'Altersverifikation – Settings', 'kipphard-age-verification' ); ?><span class="kip-admin__suite">Kipphard</span></h1>

			<?php if ( 'saved' === $notice ) : ?>
				<div class="notice notice-success is-dismissible">
					<p><?php esc_html_e( 'Settings saved.', 'kipphard-age-verification' ); ?></p>
				</div>
			<?php endif; ?>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="kipphard_age_verification_save_settings">
				<?php wp_nonce_field( 'kipphard_age_verification_save_settings' ); ?>

				<h2><?php esc_html_e( 'General', 'kipphard-age-verification' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row">
							<label for="kipphard-age-verification-min-age"><?php esc_html_e( 'Minimum age', 'kipphard-age-verification' ); ?></label>
						</th>
						<td>
							<input type="number" id="kipphard-age-verification-min-age" name="min_age" min="0" max="99"
								value="<?php echo esc_attr( (int) $s['min_age'] ); ?>" class="small-text">
							<p class="description"><?php esc_html_e( 'Required minimum age in years (e.g. 18).', 'kipphard-age-verification' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="kipphard-age-verification-mode"><?php esc_html_e( 'Verification mode', 'kipphard-age-verification' ); ?></label>
						</th>
						<td>
							<select id="kipphard-age-verification-mode" name="mode">
								<?php foreach ( $mode_options as $val => $label ) : ?>
									<option value="<?php echo esc_attr( $val ); ?>"
										<?php selected( $s['mode'], $val ); ?>>
										<?php echo esc_html( $label ); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="kipphard-age-verification-scope"><?php esc_html_e( 'Scope', 'kipphard-age-verification' ); ?></label>
						</th>
						<td>
							<select id="kipphard-age-verification-scope" name="scope">
								<?php foreach ( $scope_options as $val => $label ) : ?>
									<option value="<?php echo esc_attr( $val ); ?>"
										<?php selected( $s['scope'], $val ); ?>>
										<?php echo esc_html( $label ); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
					<tr id="kipphard-age-verification-pages-row">
						<th scope="row">
							<label for="kipphard-age-verification-pages"><?php esc_html_e( 'Pages (IDs)', 'kipphard-age-verification' ); ?></label>
						</th>
						<td>
							<input type="text" id="kipphard-age-verification-pages" name="kipphard_age_verification_pages_raw" class="regular-text"
								value="<?php echo esc_attr( implode( ', ', array_map( 'absint', (array) $s['pages'] ) ) ); ?>">
							<p class="description"><?php esc_html_e( 'Comma-separated page IDs when the scope is set to "Specific pages".', 'kipphard-age-verification' ); ?></p>
						</td>
					</tr>
				</table>

				<h2><?php esc_html_e( 'Text', 'kipphard-age-verification' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row">
							<label for="kipphard-age-verification-heading"><?php esc_html_e( 'Heading', 'kipphard-age-verification' ); ?></label>
						</th>
						<td>
							<input type="text" id="kipphard-age-verification-heading" name="heading" class="regular-text"
								value="<?php echo esc_attr( $s['heading'] ); ?>">
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="kipphard-age-verification-message"><?php esc_html_e( 'Message', 'kipphard-age-verification' ); ?></label>
						</th>
						<td>
							<textarea id="kipphard-age-verification-message" name="message" rows="3" class="large-text"><?php echo esc_textarea( $s['message'] ); ?></textarea>
							<p class="description"><?php esc_html_e( 'Basic HTML allowed (e.g. <strong>, <a>).', 'kipphard-age-verification' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="kipphard-age-verification-confirm-label"><?php esc_html_e( 'Confirm button', 'kipphard-age-verification' ); ?></label>
						</th>
						<td>
							<input type="text" id="kipphard-age-verification-confirm-label" name="confirm_label" class="regular-text"
								value="<?php echo esc_attr( $s['confirm_label'] ); ?>">
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="kipphard-age-verification-decline-label"><?php esc_html_e( 'Decline button', 'kipphard-age-verification' ); ?></label>
						</th>
						<td>
							<input type="text" id="kipphard-age-verification-decline-label" name="decline_label" class="regular-text"
								value="<?php echo esc_attr( $s['decline_label'] ); ?>">
						</td>
					</tr>
				</table>

				<h2><?php esc_html_e( 'Decline behaviour', 'kipphard-age-verification' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row">
							<label for="kipphard-age-verification-decline-action"><?php esc_html_e( 'Action on decline', 'kipphard-age-verification' ); ?></label>
						</th>
						<td>
							<select id="kipphard-age-verification-decline-action" name="decline_action">
								<?php foreach ( $decline_action_options as $val => $label ) : ?>
									<option value="<?php echo esc_attr( $val ); ?>"
										<?php selected( $s['decline_action'], $val ); ?>>
										<?php echo esc_html( $label ); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="kipphard-age-verification-decline-message"><?php esc_html_e( 'Decline message', 'kipphard-age-verification' ); ?></label>
						</th>
						<td>
							<textarea id="kipphard-age-verification-decline-message" name="decline_message" rows="2" class="large-text"><?php echo esc_textarea( $s['decline_message'] ); ?></textarea>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="kipphard-age-verification-decline-url"><?php esc_html_e( 'Redirect URL', 'kipphard-age-verification' ); ?></label>
						</th>
						<td>
							<input type="url" id="kipphard-age-verification-decline-url" name="decline_url" class="regular-text"
								value="<?php echo esc_attr( $s['decline_url'] ); ?>">
							<p class="description"><?php esc_html_e( 'Only relevant when "Redirect to URL" is selected.', 'kipphard-age-verification' ); ?></p>
						</td>
					</tr>
				</table>

				<h2><?php esc_html_e( 'Design', 'kipphard-age-verification' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row">
							<label for="kipphard-age-verification-overlay-color"><?php esc_html_e( 'Overlay background colour', 'kipphard-age-verification' ); ?></label>
						</th>
						<td>
							<input type="color" id="kipphard-age-verification-overlay-color" name="overlay_color"
								value="<?php echo esc_attr( $s['overlay_color'] ); ?>">
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="kipphard-age-verification-accent-color"><?php esc_html_e( 'Accent colour (buttons)', 'kipphard-age-verification' ); ?></label>
						</th>
						<td>
							<input type="color" id="kipphard-age-verification-accent-color" name="accent_color"
								value="<?php echo esc_attr( $s['accent_color'] ); ?>">
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="kipphard-age-verification-logo-url"><?php esc_html_e( 'Logo URL', 'kipphard-age-verification' ); ?></label>
						</th>
						<td>
							<input type="url" id="kipphard-age-verification-logo-url" name="logo_url" class="regular-text"
								value="<?php echo esc_attr( $s['logo_url'] ); ?>">
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="kipphard-age-verification-show-credit"><?php esc_html_e( 'Branding notice', 'kipphard-age-verification' ); ?></label>
						</th>
						<td>
							<label>
								<input type="checkbox" id="kipphard-age-verification-show-credit" name="show_credit" value="1"
									<?php checked( (bool) $s['show_credit'] ); ?>>
								<?php esc_html_e( 'Show "Altersverifikation by Kipphard" notice in overlay', 'kipphard-age-verification' ); ?>
							</label>
						</td>
					</tr>
				</table>

				<h2><?php esc_html_e( 'Cookie', 'kipphard-age-verification' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row">
							<label for="kipphard-age-verification-remember-days"><?php esc_html_e( 'Remember duration (days)', 'kipphard-age-verification' ); ?></label>
						</th>
						<td>
							<input type="number" id="kipphard-age-verification-remember-days" name="remember_days" min="1" max="3650"
								value="<?php echo esc_attr( (int) $s['remember_days'] ); ?>" class="small-text">
							<p class="description"><?php esc_html_e( 'How long the functional cookie stores the confirmation (1–3650 days).', 'kipphard-age-verification' ); ?></p>
						</td>
					</tr>
				</table>

				<?php if ( $has_woo_pro ) : ?>
					<?php $this->render_woocommerce_settings( $s ); ?>
				<?php else : ?>
					<?php $this->render_pro_teaser(); ?>
				<?php endif; ?>

				<?php if ( class_exists( '\Kipphard\Shared\Appearance' ) ) : ?>
					<h2 class="title"><?php esc_html_e( 'Appearance', 'kipphard-age-verification' ); ?></h2>
					<table class="form-table" role="presentation">
						<?php \Kipphard\Shared\Appearance::render_fields( $s ); ?>
					</table>
				<?php endif; ?>

				<?php submit_button( __( 'Save settings', 'kipphard-age-verification' ) ); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * WooCommerce-Einstellungsbereich ausgeben (nur wenn das Add-on im Build vorhanden ist).
	 *
	 * @param array<string,mixed> $s Aktuelle Einstellungen.
	 */
	private function render_woocommerce_settings( array $s ) {
		$wc_active = class_exists( 'WooCommerce' );
		?>
		<h2><?php esc_html_e( 'WooCommerce', 'kipphard-age-verification' ); ?></h2>
		<table class="form-table" role="presentation">

			<?php if ( $wc_active ) : ?>
				<tr>
					<th scope="row">
						<?php esc_html_e( 'WooCommerce product categories', 'kipphard-age-verification' ); ?>
					</th>
					<td>
						<?php
						$terms = get_terms(
							array(
								'taxonomy'   => 'product_cat',
								'hide_empty' => false,
							)
						);
						if ( is_wp_error( $terms ) || empty( $terms ) ) {
							esc_html_e( 'No product categories found.', 'kipphard-age-verification' );
						} else {
							$selected_cats = (array) $s['wc_categories'];
							foreach ( $terms as $term ) {
								if ( ! ( $term instanceof \WP_Term ) ) {
									continue;
								}
								$checked = in_array( (int) $term->term_id, array_map( 'absint', $selected_cats ), true );
								printf(
									'<label style="display:block;margin-bottom:4px;"><input type="checkbox" name="wc_categories[]" value="%d"%s> %s</label>',
									(int) $term->term_id,
									$checked ? ' checked' : '',
									esc_html( $term->name )
								);
							}
						}
						?>
						<p class="description"><?php esc_html_e( 'Show gate only for products in these categories.', 'kipphard-age-verification' ); ?></p>
					</td>
				</tr>
			<?php else : ?>
				<tr>
					<td colspan="2">
						<p class="description"><?php esc_html_e( 'Activate WooCommerce to gate specific product categories.', 'kipphard-age-verification' ); ?></p>
					</td>
				</tr>
			<?php endif; ?>
		</table>
		<?php
	}

	/**
	 * Hinweis auf das WooCommerce-Add-on für Nutzer ohne Premium-Build ausgeben.
	 */
	private function render_pro_teaser() {
		?>
		<p class="kipphard-age-verification-pro-teaser">
			<?php esc_html_e( 'Need to gate only specific WooCommerce product categories?', 'kipphard-age-verification' ); ?>
			<a href="https://kipphard.com/products/altersverifikation" target="_blank" rel="noopener noreferrer">
				<?php esc_html_e( 'Age Verification Pro', 'kipphard-age-verification' ); ?>
			</a>
		</p>
		<?php
	}
}
