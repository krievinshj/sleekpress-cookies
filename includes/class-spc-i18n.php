<?php
/**
 * Banner-language translations.
 *
 * Independent of WordPress's locale. The plugin has a single "banner language"
 * setting (auto | en | lv | ru) that drives:
 *   - the modal's built-in strings ("Always active", "Show cookies", …),
 *   - the "Load defaults" preset values offered on the Banner & design tab,
 *   - the natural language passed to the OpenAI prompt when categorising
 *     discovered cookies.
 *
 * Add a language by adding a key to self::translations().
 *
 * @package SleekPressCookies
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SPC_I18n {

	/**
	 * Available banner languages.
	 * @return array  [ code => label, ... ]
	 */
	public static function languages() {
		return array(
			'auto' => __( 'Auto (follow WordPress site language)', 'sleekpress-cookies' ),
			'en'   => 'English',
			'lv'   => 'Latviešu',
			'ru'   => 'Русский',
		);
	}

	/**
	 * Human-friendly language name used in the AI prompt.
	 */
	public static function language_name( $code ) {
		$names = array(
			'en' => 'English',
			'lv' => 'Latvian',
			'ru' => 'Russian',
		);
		return isset( $names[ $code ] ) ? $names[ $code ] : 'English';
	}

	/**
	 * Resolve "auto" against the current WordPress locale; fall back to English
	 * if the locale isn't one we ship strings for.
	 */
	public static function resolve( $code ) {
		$code = (string) $code;
		if ( 'auto' === $code || '' === $code ) {
			$loc = strtolower( substr( (string) get_locale(), 0, 2 ) );
			if ( isset( self::translations()[ $loc ] ) ) {
				return $loc;
			}
			return 'en';
		}
		return isset( self::translations()[ $code ] ) ? $code : 'en';
	}

	/**
	 * Full translation table for the banner strings. English is the source of truth.
	 */
	public static function translations() {
		return array(
			'en' => array(
				'title'             => 'We value your privacy',
				'message'           => 'We use cookies to enhance your browsing experience, serve personalised ads or content, and analyse our traffic. By clicking "Accept", you consent to our use of cookies.',
				'btn_accept_text'   => 'Accept',
				'btn_decline_text'  => 'Decline',
				'btn_adjust_text'   => 'Adjust',
				'btn_save_text'     => 'Save my preferences',
				'privacy_link_text' => 'Privacy Policy',
				'modal_title'       => 'Customise consent preferences',
				'always_active'     => 'Always active',
				'show_cookies'      => 'Show cookies',
				'hide_cookies'      => 'Hide cookies',
			),
			'lv' => array(
				'title'             => 'Mēs cienām jūsu privātumu',
				'message'           => 'Mēs izmantojam sīkdatnes, lai uzlabotu jūsu pārlūkošanas pieredzi, rādītu personalizētas reklāmas vai saturu un analizētu mūsu trafiku. Nospiežot „Pieņemt”, jūs piekrītat mūsu sīkdatņu lietošanai.',
				'btn_accept_text'   => 'Pieņemt',
				'btn_decline_text'  => 'Noraidīt',
				'btn_adjust_text'   => 'Pielāgot',
				'btn_save_text'     => 'Saglabāt manas izvēles',
				'privacy_link_text' => 'Privātuma politika',
				'modal_title'       => 'Pielāgot piekrišanas iestatījumus',
				'always_active'     => 'Vienmēr aktīvas',
				'show_cookies'      => 'Rādīt sīkdatnes',
				'hide_cookies'      => 'Slēpt sīkdatnes',
			),
			'ru' => array(
				'title'             => 'Мы ценим вашу конфиденциальность',
				'message'           => 'Мы используем файлы cookie, чтобы улучшить ваш опыт просмотра, показывать персонализированную рекламу или контент и анализировать наш трафик. Нажимая «Принять», вы соглашаетесь с использованием файлов cookie.',
				'btn_accept_text'   => 'Принять',
				'btn_decline_text' => 'Отклонить',
				'btn_adjust_text'   => 'Настроить',
				'btn_save_text'     => 'Сохранить мои настройки',
				'privacy_link_text' => 'Политика конфиденциальности',
				'modal_title'       => 'Настройка параметров согласия',
				'always_active'     => 'Всегда активны',
				'show_cookies'      => 'Показать файлы cookie',
				'hide_cookies'      => 'Скрыть файлы cookie',
			),
		);
	}

	public static function strings( $code = null ) {
		$code = self::resolve( $code );
		$all  = self::translations();
		return isset( $all[ $code ] ) ? $all[ $code ] : $all['en'];
	}
}
