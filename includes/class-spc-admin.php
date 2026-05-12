<?php
/**
 * Admin UI: settings, banner customisation, consent mode, cookie scanner.
 *
 * @package SleekPressCookies
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SPC_Admin {

	const SLUG = 'sleekpress-cookies';
	const CAP  = 'manage_options';

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'menu' ) );
		add_action( 'admin_post_spc_save', array( $this, 'handle_save' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'assets' ) );
		add_action( 'admin_notices', array( $this, 'notices' ) );
	}

	public function menu() {
		add_menu_page(
			__( 'SleekPress Cookies', 'sleekpress-cookies' ),
			__( 'Cookies', 'sleekpress-cookies' ),
			self::CAP,
			self::SLUG,
			array( $this, 'render' ),
			'dashicons-privacy',
			76
		);
	}

	private function tabs() {
		return array(
			'scanner'  => __( 'Cookie scanner', 'sleekpress-cookies' ),
			'cookies'  => __( 'Cookie list', 'sleekpress-cookies' ),
			'banner'   => __( 'Banner & design', 'sleekpress-cookies' ),
			'consent'  => __( 'Consent Mode', 'sleekpress-cookies' ),
			'settings' => __( 'Settings', 'sleekpress-cookies' ),
		);
	}

	private function current_tab() {
		$tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'scanner';
		return array_key_exists( $tab, $this->tabs() ) ? $tab : 'scanner';
	}

	public function assets( $hook ) {
		if ( 'toplevel_page_' . self::SLUG !== $hook ) {
			return;
		}
		wp_enqueue_style( 'spc-admin', SPC_URL . 'assets/css/spc-admin.css', array(), SPC_VERSION );
		wp_enqueue_script( 'spc-admin', SPC_URL . 'assets/js/spc-admin.js', array(), SPC_VERSION, true );
		wp_localize_script(
			'spc-admin',
			'SPCAdmin',
			array(
				'restBase'   => esc_url_raw( rest_url( SPC_Rest::NS ) ),
				'nonce'      => wp_create_nonce( 'wp_rest' ),
				'categories' => $this->category_choices(),
				'aiReady'    => SPC_AI::is_configured(),
				'i18n'       => array(
					'scanning'   => __( 'Scanning…', 'sleekpress-cookies' ),
					'noResults'  => __( 'No cookies discovered.', 'sleekpress-cookies' ),
					'aiWorking'  => __( 'Asking the AI…', 'sleekpress-cookies' ),
					'added'      => __( 'Added to cookie list.', 'sleekpress-cookies' ),
					'pickRows'   => __( 'Select at least one cookie first.', 'sleekpress-cookies' ),
					'error'      => __( 'Something went wrong.', 'sleekpress-cookies' ),
				),
			)
		);
	}

	private function category_choices() {
		$out = array();
		foreach ( SPC_Settings::categories() as $key => $cat ) {
			$out[ $key ] = $cat['label'];
		}
		return $out;
	}

	public function notices() {
		if ( empty( $_GET['page'] ) || self::SLUG !== $_GET['page'] || empty( $_GET['spc_msg'] ) ) {
			return;
		}
		$map = array(
			'saved' => __( 'Changes saved.', 'sleekpress-cookies' ),
		);
		$key = sanitize_key( wp_unslash( $_GET['spc_msg'] ) );
		if ( isset( $map[ $key ] ) ) {
			printf( '<div class="notice notice-success is-dismissible"><p>%s</p></div>', esc_html( $map[ $key ] ) );
		}
	}

	/* ---------------------------------------------------------------------
	 * Save handler
	 * ------------------------------------------------------------------- */

	public function handle_save() {
		if ( ! current_user_can( self::CAP ) ) {
			wp_die( esc_html__( 'Insufficient permissions.', 'sleekpress-cookies' ) );
		}
		check_admin_referer( 'spc_save' );

		$tab = isset( $_POST['tab'] ) ? sanitize_key( wp_unslash( $_POST['tab'] ) ) : 'settings';
		$s   = SPC_Settings::get();

		switch ( $tab ) {
			case 'banner':
				$fields_text = array( 'title', 'btn_accept_text', 'btn_decline_text', 'btn_adjust_text', 'btn_save_text', 'privacy_link_text' );
				foreach ( $fields_text as $f ) {
					if ( isset( $_POST[ $f ] ) ) {
						$s[ $f ] = sanitize_text_field( wp_unslash( $_POST[ $f ] ) );
					}
				}
				$s['message']     = isset( $_POST['message'] ) ? wp_kses_post( wp_unslash( $_POST['message'] ) ) : '';
				$s['privacy_url'] = isset( $_POST['privacy_url'] ) ? esc_url_raw( wp_unslash( $_POST['privacy_url'] ) ) : '';
				$s['position']    = in_array( ( $_POST['position'] ?? '' ), array( 'bottom-left', 'bottom-right', 'bottom-bar' ), true ) ? $_POST['position'] : 'bottom-left';
				$s['theme']       = ( 'dark' === ( $_POST['theme'] ?? '' ) ) ? 'dark' : 'light';
				foreach ( array( 'color_bg', 'color_text', 'color_primary', 'color_primary_text', 'color_secondary', 'color_secondary_text' ) as $c ) {
					if ( isset( $_POST[ $c ] ) ) {
						$s[ $c ] = sanitize_hex_color( wp_unslash( $_POST[ $c ] ) ) ?: $s[ $c ];
					}
				}
				$s['border_radius']      = max( 0, min( 40, (int) ( $_POST['border_radius'] ?? 12 ) ) );
				$s['banner_width']       = min( 60, max( 16, (float) ( $_POST['banner_width'] ?? 26.25 ) ) );
				$s['show_branding']      = empty( $_POST['show_branding'] ) ? 0 : 1;
				$s['show_revisit_badge'] = empty( $_POST['show_revisit_badge'] ) ? 0 : 1;
				break;

			case 'consent':
				$s['gcm_enabled']         = empty( $_POST['gcm_enabled'] ) ? 0 : 1;
				$s['gcm_wait_for_update'] = max( 0, min( 10000, (int) ( $_POST['gcm_wait_for_update'] ?? 500 ) ) );
				$s['gcm_url_passthrough'] = empty( $_POST['gcm_url_passthrough'] ) ? 0 : 1;
				$s['gcm_ads_redaction']   = empty( $_POST['gcm_ads_redaction'] ) ? 0 : 1;
				$def = array();
				foreach ( array( 'functional', 'analytics', 'advertisement' ) as $c ) {
					$val = $_POST['gcm_default'][ $c ] ?? 'denied';
					$def[ $c ] = ( 'granted' === $val ) ? 'granted' : 'denied';
				}
				$s['gcm_default'] = $def;
				break;

			case 'settings':
				$s['enabled']             = empty( $_POST['enabled'] ) ? 0 : 1;
				$s['hide_for_admins']     = empty( $_POST['hide_for_admins'] ) ? 0 : 1;
				$s['consent_expiry_days'] = max( 1, min( 3650, (int) ( $_POST['consent_expiry_days'] ?? 365 ) ) );
				$s['gtm_id']              = $this->sanitize_id( $_POST['gtm_id'] ?? '' );
				$s['ga4_id']              = $this->sanitize_id( $_POST['ga4_id'] ?? '' );
				$s['openai_api_key']      = isset( $_POST['openai_api_key'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['openai_api_key'] ) ) ) : '';
				$s['openai_model']        = isset( $_POST['openai_model'] ) ? sanitize_text_field( wp_unslash( $_POST['openai_model'] ) ) : 'gpt-4o-mini';
				break;

			case 'cookies':
				$this->save_cookie_table_from_post();
				$this->redirect( 'cookies' );
				return;
		}

		SPC_Settings::save( $s );
		$this->redirect( $tab );
	}

	private function sanitize_id( $raw ) {
		$raw = trim( sanitize_text_field( wp_unslash( $raw ) ) );
		return preg_replace( '/[^A-Za-z0-9\-]/', '', $raw );
	}

	private function save_cookie_table_from_post() {
		$in    = isset( $_POST['cookie'] ) && is_array( $_POST['cookie'] ) ? wp_unslash( $_POST['cookie'] ) : array();
		$valid = array_keys( SPC_Settings::categories() );
		$table = array();
		foreach ( $valid as $c ) {
			$table[ $c ] = array();
		}
		foreach ( $in as $row ) {
			if ( ! empty( $row['_delete'] ) ) {
				continue;
			}
			$name = sanitize_text_field( $row['name'] ?? '' );
			if ( '' === $name ) {
				continue;
			}
			$cat = ( isset( $row['category'] ) && in_array( $row['category'], $valid, true ) ) ? $row['category'] : 'others';
			$table[ $cat ][] = array(
				'name'        => $name,
				'provider'    => sanitize_text_field( $row['provider'] ?? '' ),
				'duration'    => sanitize_text_field( $row['duration'] ?? '' ),
				'domain'      => sanitize_text_field( $row['domain'] ?? '' ),
				'description' => sanitize_textarea_field( $row['description'] ?? '' ),
			);
		}
		SPC_Settings::save_cookies( $table );
	}

	private function redirect( $tab ) {
		wp_safe_redirect( add_query_arg( array( 'page' => self::SLUG, 'tab' => $tab, 'spc_msg' => 'saved' ), admin_url( 'admin.php' ) ) );
		exit;
	}

	/* ---------------------------------------------------------------------
	 * Rendering
	 * ------------------------------------------------------------------- */

	public function render() {
		$tab  = $this->current_tab();
		$tabs = $this->tabs();
		echo '<div class="wrap spc-wrap">';
		echo '<h1>' . esc_html__( 'SleekPress Cookies', 'sleekpress-cookies' ) . '</h1>';
		echo '<h2 class="nav-tab-wrapper">';
		foreach ( $tabs as $key => $label ) {
			printf(
				'<a href="%s" class="nav-tab %s">%s</a>',
				esc_url( add_query_arg( array( 'page' => self::SLUG, 'tab' => $key ), admin_url( 'admin.php' ) ) ),
				$key === $tab ? 'nav-tab-active' : '',
				esc_html( $label )
			);
		}
		echo '</h2>';

		echo '<div class="spc-tab-body">';
		switch ( $tab ) {
			case 'scanner':
				$this->view_scanner();
				break;
			case 'cookies':
				$this->view_cookies();
				break;
			case 'banner':
				$this->view_banner();
				break;
			case 'consent':
				$this->view_consent();
				break;
			case 'settings':
				$this->view_settings();
				break;
		}
		echo '</div></div>';
	}

	private function form_open( $tab ) {
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		echo '<input type="hidden" name="action" value="spc_save" />';
		echo '<input type="hidden" name="tab" value="' . esc_attr( $tab ) . '" />';
		wp_nonce_field( 'spc_save' );
	}

	private function field_row( $label, $html, $desc = '' ) {
		echo '<tr><th scope="row">' . esc_html( $label ) . '</th><td>' . $html; // phpcs:ignore WordPress.Security.EscapeOutput
		if ( $desc ) {
			echo '<p class="description">' . wp_kses_post( $desc ) . '</p>';
		}
		echo '</td></tr>';
	}

	private function cb( $name, $checked, $label ) {
		return sprintf(
			'<label><input type="checkbox" name="%s" value="1" %s /> %s</label>',
			esc_attr( $name ),
			checked( (bool) $checked, true, false ),
			esc_html( $label )
		);
	}

	private function txt( $name, $value, $size = 'regular' ) {
		return sprintf( '<input type="text" name="%s" value="%s" class="%s-text" />', esc_attr( $name ), esc_attr( $value ), esc_attr( $size ) );
	}

	private function color( $name, $value ) {
		return sprintf( '<input type="color" name="%s" value="%s" />', esc_attr( $name ), esc_attr( $value ) );
	}

	/* ----- Scanner tab ----- */

	private function view_scanner() {
		$last = SPC_Scanner::last_scan();
		echo '<p>' . esc_html__( 'Scan your site for third-party scripts and cookies. The scanner fetches your home page and a few recent pages, matches known services (Google Analytics, GTM, Meta Pixel, YouTube, Hotjar, …) against a built-in database, and also lists any cookies seen in real visitors\' browsers. Review the results, optionally let the AI categorise unknown ones, then add them to your cookie list.', 'sleekpress-cookies' ) . '</p>';

		echo '<p><button type="button" class="button button-primary" id="spc-scan-btn">' . esc_html__( 'Scan now', 'sleekpress-cookies' ) . '</button> ';
		if ( SPC_AI::is_configured() ) {
			echo '<button type="button" class="button" id="spc-ai-btn" disabled>' . esc_html__( 'Categorise selected with AI', 'sleekpress-cookies' ) . '</button> ';
		} else {
			echo '<span class="description">' . esc_html__( 'Add an OpenAI API key in Settings to enable AI categorisation.', 'sleekpress-cookies' ) . '</span> ';
		}
		echo '<button type="button" class="button" id="spc-add-btn" disabled>' . esc_html__( 'Add selected to cookie list', 'sleekpress-cookies' ) . '</button>';
		echo '</p>';

		if ( $last ) {
			echo '<p class="description">' . sprintf(
				/* translators: 1: date, 2: count */
				esc_html__( 'Last scan: %1$s — %2$d cookies found.', 'sleekpress-cookies' ),
				esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $last['time'] ) ),
				(int) $last['count']
			) . '</p>';
		}

		echo '<div id="spc-scan-status" class="spc-status"></div>';
		echo '<div id="spc-scan-results"></div>';
	}

	/* ----- Cookie list tab ----- */

	private function view_cookies() {
		$this->form_open( 'cookies' );
		$cats   = SPC_Settings::categories();
		$table  = SPC_Settings::cookies();
		$idx    = 0;

		echo '<p>' . esc_html__( 'These cookies are shown to visitors inside the preferences modal, grouped by category. Necessary cookies are always active.', 'sleekpress-cookies' ) . '</p>';

		foreach ( $cats as $key => $cat ) {
			echo '<h2>' . esc_html( $cat['label'] ) . ' <span class="spc-muted">(' . esc_html( $key ) . ')</span></h2>';
			echo '<table class="widefat striped spc-cookie-table"><thead><tr>';
			echo '<th>' . esc_html__( 'Cookie', 'sleekpress-cookies' ) . '</th>';
			echo '<th>' . esc_html__( 'Provider', 'sleekpress-cookies' ) . '</th>';
			echo '<th>' . esc_html__( 'Duration', 'sleekpress-cookies' ) . '</th>';
			echo '<th>' . esc_html__( 'Domain', 'sleekpress-cookies' ) . '</th>';
			echo '<th>' . esc_html__( 'Description', 'sleekpress-cookies' ) . '</th>';
			echo '<th>' . esc_html__( 'Category', 'sleekpress-cookies' ) . '</th>';
			echo '<th>' . esc_html__( 'Delete', 'sleekpress-cookies' ) . '</th>';
			echo '</tr></thead><tbody class="spc-rows" data-cat="' . esc_attr( $key ) . '">';
			foreach ( $table[ $key ] as $row ) {
				$this->cookie_row( $idx++, $row, $key );
			}
			echo '</tbody><tfoot><tr><td colspan="7"><button type="button" class="button spc-add-row" data-cat="' . esc_attr( $key ) . '">' . esc_html__( '+ Add cookie', 'sleekpress-cookies' ) . '</button></td></tr></tfoot>';
			echo '</table>';
		}

		// Template row for JS-added rows.
		echo '<script type="text/html" id="spc-row-tpl">';
		$this->cookie_row( '__INDEX__', array(), '__CAT__' );
		echo '</script>';

		echo '<p><button type="submit" class="button button-primary">' . esc_html__( 'Save cookie list', 'sleekpress-cookies' ) . '</button></p>';
		echo '</form>';
	}

	private function cookie_row( $i, $row, $cat ) {
		$row = wp_parse_args( $row, array( 'name' => '', 'provider' => '', 'duration' => '', 'domain' => '', 'description' => '' ) );
		$n   = "cookie[$i]";
		echo '<tr>';
		echo '<td><input type="text" name="' . esc_attr( $n ) . '[name]" value="' . esc_attr( $row['name'] ) . '" /></td>';
		echo '<td><input type="text" name="' . esc_attr( $n ) . '[provider]" value="' . esc_attr( $row['provider'] ) . '" /></td>';
		echo '<td><input type="text" name="' . esc_attr( $n ) . '[duration]" value="' . esc_attr( $row['duration'] ) . '" /></td>';
		echo '<td><input type="text" name="' . esc_attr( $n ) . '[domain]" value="' . esc_attr( $row['domain'] ) . '" /></td>';
		echo '<td><textarea name="' . esc_attr( $n ) . '[description]" rows="2">' . esc_textarea( $row['description'] ) . '</textarea></td>';
		echo '<td><select name="' . esc_attr( $n ) . '[category]">';
		foreach ( SPC_Settings::categories() as $ck => $c ) {
			printf( '<option value="%s" %s>%s</option>', esc_attr( $ck ), selected( $ck, $cat, false ), esc_html( $c['label'] ) );
		}
		echo '</select></td>';
		echo '<td style="text-align:center"><input type="checkbox" name="' . esc_attr( $n ) . '[_delete]" value="1" /></td>';
		echo '</tr>';
	}

	/* ----- Banner tab ----- */

	private function view_banner() {
		$s = SPC_Settings::get();
		$this->form_open( 'banner' );
		echo '<table class="form-table" role="presentation"><tbody>';

		$this->field_row( __( 'Banner title', 'sleekpress-cookies' ), $this->txt( 'title', $s['title'] ) );
		$this->field_row(
			__( 'Intro message', 'sleekpress-cookies' ),
			sprintf( '<textarea name="message" rows="4" class="large-text">%s</textarea>', esc_textarea( $s['message'] ) ),
			__( 'Basic HTML is allowed. A link to your privacy policy is added automatically.', 'sleekpress-cookies' )
		);

		$auto = SPC_Settings::privacy_url();
		$this->field_row(
			__( 'Privacy policy URL', 'sleekpress-cookies' ),
			$this->txt( 'privacy_url', $s['privacy_url'], 'large' ),
			$s['privacy_url'] ? '' : ( $auto ? sprintf( __( 'Leave empty to use the WordPress privacy policy page: %s', 'sleekpress-cookies' ), '<code>' . esc_html( $auto ) . '</code>' ) : __( 'No WordPress privacy policy page is set. Set one under Settings → Privacy, or enter a URL here.', 'sleekpress-cookies' ) )
		);
		$this->field_row( __( 'Privacy link text', 'sleekpress-cookies' ), $this->txt( 'privacy_link_text', $s['privacy_link_text'] ) );

		$this->field_row( __( '"Accept" button label', 'sleekpress-cookies' ), $this->txt( 'btn_accept_text', $s['btn_accept_text'] ) );
		$this->field_row( __( '"Decline" button label', 'sleekpress-cookies' ), $this->txt( 'btn_decline_text', $s['btn_decline_text'] ) );
		$this->field_row( __( '"Adjust" button label', 'sleekpress-cookies' ), $this->txt( 'btn_adjust_text', $s['btn_adjust_text'] ) );
		$this->field_row( __( '"Save preferences" button label', 'sleekpress-cookies' ), $this->txt( 'btn_save_text', $s['btn_save_text'] ) );

		// Position.
		$pos_html = '<select name="position">';
		foreach ( array(
			'bottom-left'  => __( 'Box — bottom left', 'sleekpress-cookies' ),
			'bottom-right' => __( 'Box — bottom right', 'sleekpress-cookies' ),
			'bottom-bar'   => __( 'Full-width bar — bottom', 'sleekpress-cookies' ),
		) as $v => $l ) {
			$pos_html .= sprintf( '<option value="%s" %s>%s</option>', esc_attr( $v ), selected( $v, $s['position'], false ), esc_html( $l ) );
		}
		$pos_html .= '</select>';
		$this->field_row( __( 'Position', 'sleekpress-cookies' ), $pos_html );

		$theme_html = '<select name="theme"><option value="light"' . selected( 'light', $s['theme'], false ) . '>' . esc_html__( 'Light', 'sleekpress-cookies' ) . '</option><option value="dark"' . selected( 'dark', $s['theme'], false ) . '>' . esc_html__( 'Dark', 'sleekpress-cookies' ) . '</option></select>';
		$this->field_row( __( 'Theme', 'sleekpress-cookies' ), $theme_html );

		$this->field_row( __( 'Background colour', 'sleekpress-cookies' ), $this->color( 'color_bg', $s['color_bg'] ) );
		$this->field_row( __( 'Text colour', 'sleekpress-cookies' ), $this->color( 'color_text', $s['color_text'] ) );
		$this->field_row( __( 'Accept button — background', 'sleekpress-cookies' ), $this->color( 'color_primary', $s['color_primary'] ) );
		$this->field_row( __( 'Accept button — text', 'sleekpress-cookies' ), $this->color( 'color_primary_text', $s['color_primary_text'] ) );
		$this->field_row( __( 'Decline / Adjust button — background', 'sleekpress-cookies' ), $this->color( 'color_secondary', $s['color_secondary'] ) );
		$this->field_row( __( 'Decline / Adjust button — text', 'sleekpress-cookies' ), $this->color( 'color_secondary_text', $s['color_secondary_text'] ) );
		$this->field_row( __( 'Corner radius (px)', 'sleekpress-cookies' ), sprintf( '<input type="number" min="0" max="40" name="border_radius" value="%d" />', (int) $s['border_radius'] ) );
		$this->field_row(
			__( 'Banner width (rem)', 'sleekpress-cookies' ),
			sprintf( '<input type="number" step="0.25" min="16" max="60" name="banner_width" value="%s" /> <span class="description">≈ %dpx</span>', esc_attr( (string) (float) $s['banner_width'] ), (int) round( ( (float) $s['banner_width'] ) * 16 ) ),
			__( 'Applies to the bottom-left / bottom-right box layouts. The full-width bar ignores it. Default 26.25rem (≈ 420px).', 'sleekpress-cookies' )
		);

		$this->field_row( __( 'Floating "cookie settings" badge', 'sleekpress-cookies' ), $this->cb( 'show_revisit_badge', $s['show_revisit_badge'], __( 'Show a small button so visitors can reopen their preferences', 'sleekpress-cookies' ) ) );
		$this->field_row( __( 'Branding', 'sleekpress-cookies' ), $this->cb( 'show_branding', $s['show_branding'], __( 'Show "Powered by SleekPress Cookies" in the banner', 'sleekpress-cookies' ) ) );

		echo '</tbody></table>';
		echo '<p><button type="submit" class="button button-primary">' . esc_html__( 'Save changes', 'sleekpress-cookies' ) . '</button></p>';
		echo '</form>';

		echo '<p class="description">' . esc_html__( 'Tip: use the [sleekpress_cookie_settings] shortcode (or a link with class "spc-open-prefs") anywhere to let visitors reopen the preferences modal.', 'sleekpress-cookies' ) . '</p>';
	}

	/* ----- Consent Mode tab ----- */

	private function view_consent() {
		$s = SPC_Settings::get();
		$this->form_open( 'consent' );
		echo '<p>' . esc_html__( 'Google Consent Mode v2 lets Google tags (GA4, Google Ads) adjust their behaviour based on the visitor\'s consent. Before consent is given, tags receive a "denied" signal and send only cookieless pings (for modelling). When the visitor accepts, an "update" is sent and full measurement resumes.', 'sleekpress-cookies' ) . '</p>';

		echo '<table class="form-table" role="presentation"><tbody>';
		$this->field_row( __( 'Enable Google Consent Mode v2', 'sleekpress-cookies' ), $this->cb( 'gcm_enabled', $s['gcm_enabled'], __( 'Push consent default/update signals to the dataLayer', 'sleekpress-cookies' ) ), __( 'Works whether your tags are managed in Google Tag Manager or hard-coded. The plugin pushes the gtag() consent calls before any tag loads.', 'sleekpress-cookies' ) );

		// Default state table.
		$cats = SPC_Settings::categories();
		$rows = '<table class="widefat striped" style="max-width:560px"><thead><tr><th>' . esc_html__( 'Category', 'sleekpress-cookies' ) . '</th><th>' . esc_html__( 'Consent Mode signals', 'sleekpress-cookies' ) . '</th><th>' . esc_html__( 'Default before consent', 'sleekpress-cookies' ) . '</th></tr></thead><tbody>';
		foreach ( $cats as $key => $cat ) {
			$signals = $cat['gcm'] ? implode( ', ', $cat['gcm'] ) : '—';
			if ( ! empty( $cat['locked'] ) ) {
				$rows .= sprintf( '<tr><td><strong>%s</strong></td><td><code>%s</code></td><td>%s</td></tr>', esc_html( $cat['label'] ), esc_html( $signals ), esc_html__( 'Granted (always)', 'sleekpress-cookies' ) );
			} elseif ( 'others' === $key ) {
				$rows .= sprintf( '<tr><td><strong>%s</strong></td><td>%s</td><td>%s</td></tr>', esc_html( $cat['label'] ), esc_html( $signals ), esc_html__( 'Not signalled to Google', 'sleekpress-cookies' ) );
			} else {
				$cur = $s['gcm_default'][ $key ] ?? 'denied';
				$sel = sprintf(
					'<select name="gcm_default[%1$s]"><option value="denied" %2$s>%3$s</option><option value="granted" %4$s>%5$s</option></select>',
					esc_attr( $key ),
					selected( 'denied', $cur, false ),
					esc_html__( 'Denied', 'sleekpress-cookies' ),
					selected( 'granted', $cur, false ),
					esc_html__( 'Granted', 'sleekpress-cookies' )
				);
				$rows .= sprintf( '<tr><td><strong>%s</strong></td><td><code>%s</code></td><td>%s</td></tr>', esc_html( $cat['label'] ), esc_html( $signals ), $sel );
			}
		}
		$rows .= '</tbody></table>';
		$this->field_row( __( 'Default consent state', 'sleekpress-cookies' ), $rows, __( 'GDPR-style setups should keep everything "Denied". Leaving "Granted" disables consent gating for that category.', 'sleekpress-cookies' ) );

		$this->field_row( __( 'Wait for update (ms)', 'sleekpress-cookies' ), sprintf( '<input type="number" min="0" max="10000" name="gcm_wait_for_update" value="%d" />', (int) $s['gcm_wait_for_update'] ), __( 'How long Google tags should wait for a consent update before firing. 500 ms is typical.', 'sleekpress-cookies' ) );
		$this->field_row( __( 'URL passthrough', 'sleekpress-cookies' ), $this->cb( 'gcm_url_passthrough', $s['gcm_url_passthrough'], __( 'Pass ad-click & session info through URLs while consent is denied', 'sleekpress-cookies' ) ) );
		$this->field_row( __( 'Ads data redaction', 'sleekpress-cookies' ), $this->cb( 'gcm_ads_redaction', $s['gcm_ads_redaction'], __( 'Redact ad identifiers when ad_storage is denied', 'sleekpress-cookies' ) ) );

		echo '</tbody></table>';
		echo '<p><button type="submit" class="button button-primary">' . esc_html__( 'Save changes', 'sleekpress-cookies' ) . '</button></p>';
		echo '</form>';
	}

	/* ----- Settings tab ----- */

	private function view_settings() {
		$s = SPC_Settings::get();
		$this->form_open( 'settings' );
		echo '<table class="form-table" role="presentation"><tbody>';

		$this->field_row( __( 'Enable consent banner', 'sleekpress-cookies' ), $this->cb( 'enabled', $s['enabled'], __( 'Show the cookie banner on the front end', 'sleekpress-cookies' ) ) );
		$this->field_row( __( 'Hide banner for administrators', 'sleekpress-cookies' ), $this->cb( 'hide_for_admins', $s['hide_for_admins'], __( 'Don\'t show the banner to logged-in admins (handy while testing tags)', 'sleekpress-cookies' ) ) );
		$this->field_row( __( 'Re-ask for consent after (days)', 'sleekpress-cookies' ), sprintf( '<input type="number" min="1" max="3650" name="consent_expiry_days" value="%d" />', (int) $s['consent_expiry_days'] ) );

		echo '<tr><th colspan="2"><h2 style="margin:1em 0 0">' . esc_html__( 'Tags', 'sleekpress-cookies' ) . '</h2></th></tr>';
		$this->field_row(
			__( 'Google Tag Manager ID', 'sleekpress-cookies' ),
			$this->txt( 'gtm_id', $s['gtm_id'] ),
			__( 'Optional. If you enter <code>GTM-XXXXXXX</code> the plugin will output the GTM container snippet for you (after the Consent Mode defaults). Leave empty if you already added GTM yourself — the consent signals still work.', 'sleekpress-cookies' )
		);
		$this->field_row(
			__( 'GA4 Measurement ID', 'sleekpress-cookies' ),
			$this->txt( 'ga4_id', $s['ga4_id'] ),
			__( 'Optional. Used only if no GTM ID is set above; the plugin will load gtag.js with this <code>G-XXXXXXXXXX</code> ID.', 'sleekpress-cookies' )
		);

		echo '<tr><th colspan="2"><h2 style="margin:1em 0 0">' . esc_html__( 'AI categorisation (OpenAI)', 'sleekpress-cookies' ) . '</h2></th></tr>';
		$this->field_row(
			__( 'OpenAI API key', 'sleekpress-cookies' ),
			sprintf( '<input type="password" name="openai_api_key" value="%s" class="regular-text" autocomplete="new-password" />', esc_attr( $s['openai_api_key'] ) ),
			__( 'Used on the Cookie scanner tab to auto-categorise and describe discovered cookies. The key is stored in your WordPress database.', 'sleekpress-cookies' )
		);
		$this->field_row( __( 'Model', 'sleekpress-cookies' ), $this->txt( 'openai_model', $s['openai_model'] ), __( 'e.g. <code>gpt-4o-mini</code> (cheap, good enough) or <code>gpt-4o</code>.', 'sleekpress-cookies' ) );

		echo '</tbody></table>';
		echo '<p><button type="submit" class="button button-primary">' . esc_html__( 'Save changes', 'sleekpress-cookies' ) . '</button></p>';
		echo '</form>';
	}
}
