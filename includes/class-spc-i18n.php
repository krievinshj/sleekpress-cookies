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
				'categories'        => array(
					'necessary'     => array(
						'label'       => 'Necessary',
						'description' => 'Necessary cookies are required to enable the basic features of this site, such as providing secure log-in or adjusting your consent preferences. These cookies do not store any personally identifiable data.',
					),
					'functional'    => array(
						'label'       => 'Functional',
						'description' => 'Functional cookies help perform certain functionalities like sharing the content of the website on social media platforms, collecting feedback, and other third-party features.',
					),
					'analytics'     => array(
						'label'       => 'Analytics',
						'description' => 'Analytical cookies are used to understand how visitors interact with the website. These cookies help provide information on metrics such as the number of visitors, bounce rate, traffic source, etc.',
					),
					'advertisement' => array(
						'label'       => 'Advertisement',
						'description' => 'Advertisement cookies are used to provide visitors with customised advertisements based on the pages you visited previously and to analyse the effectiveness of the ad campaigns.',
					),
					'others'        => array(
						'label'       => 'Others',
						'description' => 'Other uncategorised cookies are those that are being analysed and have not been classified into a category as yet.',
					),
				),
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
				'categories'        => array(
					'necessary'     => array(
						'label'       => 'Obligātās',
						'description' => 'Obligātās sīkdatnes ir nepieciešamas, lai nodrošinātu šīs vietnes pamatfunkcijas, piemēram, drošu pieteikšanos vai jūsu piekrišanas iestatījumu pielāgošanu. Šīs sīkdatnes neuzglabā nekādus personu identificējošus datus.',
					),
					'functional'    => array(
						'label'       => 'Funkcionālās',
						'description' => 'Funkcionālās sīkdatnes palīdz veikt noteiktas funkcijas, piemēram, vietnes satura kopīgošanu sociālajos tīklos, atsauksmju vākšanu un citas trešo pušu funkcijas.',
					),
					'analytics'     => array(
						'label'       => 'Analītikas',
						'description' => 'Analītikas sīkdatnes tiek izmantotas, lai izprastu, kā apmeklētāji mijiedarbojas ar vietni. Tās sniedz informāciju par tādiem rādītājiem kā apmeklētāju skaits, atlēkšanas līmenis, trafika avots utt.',
					),
					'advertisement' => array(
						'label'       => 'Reklāmas',
						'description' => 'Reklāmas sīkdatnes tiek izmantotas, lai apmeklētājiem rādītu pielāgotas reklāmas, pamatojoties uz iepriekš apmeklētajām lapām, un lai analizētu reklāmas kampaņu efektivitāti.',
					),
					'others'        => array(
						'label'       => 'Citas',
						'description' => 'Citas nekategorizētas sīkdatnes ir tās, kuras tiek analizētas un vēl nav klasificētas nevienā kategorijā.',
					),
				),
			),
			'ru' => array(
				'title'             => 'Мы ценим вашу конфиденциальность',
				'message'           => 'Мы используем файлы cookie, чтобы улучшить ваш опыт просмотра, показывать персонализированную рекламу или контент и анализировать наш трафик. Нажимая «Принять», вы соглашаетесь с использованием файлов cookie.',
				'btn_accept_text'   => 'Принять',
				'btn_decline_text'  => 'Отклонить',
				'btn_adjust_text'   => 'Настроить',
				'btn_save_text'     => 'Сохранить мои настройки',
				'privacy_link_text' => 'Политика конфиденциальности',
				'modal_title'       => 'Настройка параметров согласия',
				'always_active'     => 'Всегда активны',
				'show_cookies'      => 'Показать файлы cookie',
				'hide_cookies'      => 'Скрыть файлы cookie',
				'categories'        => array(
					'necessary'     => array(
						'label'       => 'Обязательные',
						'description' => 'Обязательные файлы cookie необходимы для базовой функциональности сайта — например, для безопасного входа или сохранения ваших настроек согласия. Они не сохраняют персональные данные.',
					),
					'functional'    => array(
						'label'       => 'Функциональные',
						'description' => 'Функциональные файлы cookie помогают выполнять отдельные функции — например, делиться содержимым сайта в соцсетях, собирать отзывы и подключать другие сторонние возможности.',
					),
					'analytics'     => array(
						'label'       => 'Аналитические',
						'description' => 'Аналитические файлы cookie помогают понять, как посетители взаимодействуют с сайтом. Они дают информацию о таких показателях, как число посетителей, показатель отказов, источник трафика и т.д.',
					),
					'advertisement' => array(
						'label'       => 'Рекламные',
						'description' => 'Рекламные файлы cookie используются для показа посетителям персонализированной рекламы на основе ранее посещённых страниц и для оценки эффективности рекламных кампаний.',
					),
					'others'        => array(
						'label'       => 'Прочие',
						'description' => 'Прочие неклассифицированные файлы cookie — это те, что в настоящее время анализируются и пока не отнесены ни к одной категории.',
					),
				),
			),
		);
	}

	public static function strings( $code = null ) {
		$code = self::resolve( $code );
		$all  = self::translations();
		return isset( $all[ $code ] ) ? $all[ $code ] : $all['en'];
	}

	/**
	 * Translated per-category labels + descriptions for the given language.
	 *
	 * @return array<string,array{label:string,description:string}>
	 */
	public static function categories( $code = null ) {
		$bundle = self::strings( $code );
		return isset( $bundle['categories'] ) ? $bundle['categories'] : self::strings( 'en' )['categories'];
	}
}
