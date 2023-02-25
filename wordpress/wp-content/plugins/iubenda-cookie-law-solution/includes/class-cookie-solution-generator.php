<?php
/**
 * Iubenda cookie solution generator class.
 *
 * @package  Iubenda
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Cookie_Solution_Generator
 */
class Cookie_Solution_Generator {

	/**
	 * Generate CS code
	 *
	 * @param   string      $language          language.
	 * @param   string|null $site_id           site_id.
	 * @param   string      $cookie_policy_id  cookie_policy_id.
	 * @param   array       $args              args.
	 *
	 * @return string
	 */
	public function handle( string $language, $site_id, $cookie_policy_id, $args = array() ) {
		// Return if there is no public ID or site ID.
		if ( ! $cookie_policy_id || ! $site_id ) {
			return null;
		}

		// Handle if the website is single language.
		if ( 'default' === $language ) {
			$language = ! empty( iubenda()->lang_current ) ? iubenda()->lang_current : iubenda()->lang_default;
		}

		// Special handling if the language is pt-pt.
		if ( 'pt-pt' === strtolower( $language ) ) {
			$language = 'pt';
		}

		// Special handling if the language has a country to replace the country code with capital letters.
		if ( strpos( $language, '-' ) ) {
			$language    = explode( '-', $language );
			$language[1] = strtoupper( $language[1] );
			$language    = implode( '-', $language );
		}

		// No need to sanitize because we just build the embed code.
		$before_configuration = '
            <script type="text/javascript">
            var _iub = _iub || [];
            _iub.csConfiguration =';
		$after_configuration  = '</script>';

		$cs_configuration                                 = array(
			'floatingPreferencesButtonDisplay' => 'bottom-right',
			'lang'                             => $language,
			'siteId'                           => $site_id,
			'cookiePolicyId'                   => $cookie_policy_id,
			'whitelabel'                       => false,
			'invalidateConsentWithoutLog'      => true,
		);
		$cs_configuration['banner']['closeButtonDisplay'] = false;

		$legislation = (string) iub_array_get( $args, 'legislation' );

		// If legislation is GDPR or LGPD or All.
		if ( 'gdpr' === $legislation || 'lgpd' === $legislation || 'all' === $legislation ) {
			$cs_configuration['perPurposeConsent']            = true;
			$cs_configuration['banner']['listPurposes']       = true;
			$cs_configuration['banner']['explicitWithdrawal'] = true;

			$explicit_reject = iub_array_get( $args, 'explicit_reject' );
			if ( $explicit_reject || true === (bool) ( iub_array_get( $args, 'tcf' ) ) ) {
				$cs_configuration['banner']['rejectButtonDisplay'] = true;
			}

			$explicit_accept = iub_array_get( $args, 'explicit_accept' );
			if ( $explicit_accept || true === (bool) ( iub_array_get( $args, 'tcf' ) ) ) {
				$cs_configuration['banner']['acceptButtonDisplay']    = true;
				$cs_configuration['banner']['customizeButtonDisplay'] = true;
			}

			// If Require Consent is EU Only.
			if ( (string) iub_array_get( $args, 'require_consent' ) === 'eu_only' ) {
				$cs_configuration['countryDetection']    = true;
				$cs_configuration['gdprAppliesGlobally'] = false;
			}
		}

		// If legislation is USPR or All.
		if ( 'uspr' === $legislation || 'all' === $legislation ) {
			$cs_configuration['enableUspr'] = true;

			// If Require Consent is Worldwide.
			if ( (string) iub_array_get( $args, 'require_consent' ) === 'worldwide' ) {
				$cs_configuration['usprApplies'] = true;
			}

			// No need to sanitize because we just build the embed code.
			// phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript
			$after_configuration .= '<script type="text/javascript" src="//cdn.iubenda.com/cs/ccpa/stub.js"></script>';
		}

		// If legislation is LGDP or All.
		if ( 'lgpd' === $legislation || 'all' === $legislation ) {
			$cs_configuration['enableLgpd'] = true;
		}

		// If legislation is USPR or LGPD.
		if ( 'uspr' === $legislation || 'lgpd' === $legislation ) {
			// If Require Consent is Worldwide.
			if ( (string) iub_array_get( $args, 'require_consent' ) === 'worldwide' ) {
				$cs_configuration['enableGdpr'] = false;
			}
		}

		// If legislation is USPR.
		if ( 'uspr' === $legislation ) {
			// If Require Consent is EU Only.
			if ( (string) iub_array_get( $args, 'require_consent' ) === 'eu_only' ) {
				$cs_configuration['countryDetection']    = true;
				$cs_configuration['gdprAppliesGlobally'] = false;
			}
		}

		// conditions on TCF is enabled.
		if ( ( 'gdpr' === $legislation || 'all' === $legislation ) && true === boolval( iub_array_get( $args, 'tcf' ) ) ) {
			$cs_configuration['enableTcf']                    = true;
			$cs_configuration['banner']['closeButtonRejects'] = true;
			$cs_configuration['tcfPurposes']['1']             = true;
			$cs_configuration['tcfPurposes']['2']             = esc_attr( 'consent_only' );
			$cs_configuration['tcfPurposes']['3']             = esc_attr( 'consent_only' );
			$cs_configuration['tcfPurposes']['4']             = esc_attr( 'consent_only' );
			$cs_configuration['tcfPurposes']['5']             = esc_attr( 'consent_only' );
			$cs_configuration['tcfPurposes']['6']             = esc_attr( 'consent_only' );
			$cs_configuration['tcfPurposes']['7']             = esc_attr( 'consent_only' );
			$cs_configuration['tcfPurposes']['8']             = esc_attr( 'consent_only' );
			$cs_configuration['tcfPurposes']['9']             = esc_attr( 'consent_only' );
			$cs_configuration['tcfPurposes']['10']            = esc_attr( 'consent_only' );

			// No need to sanitize because we just build the embed code.
			// phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript
			$after_configuration .= '<script type="text/javascript" src="//cdn.iubenda.com/cs/tcf/stub-v2.js"></script><script type="text/javascript" src="//cdn.iubenda.com/cs/tcf/safe-tcf-v2.js"></script>
            ';
		}
		$cs_configuration['banner']['position'] = str_replace( 'full-', '', iub_array_get( $args, 'position' ) );

		$banner_style = (string) iub_array_get( $args, 'banner_style' );
		if ( 'light' === $banner_style ) {
			$cs_configuration['banner']['style']                       = 'light';
			$cs_configuration['banner']['textColor']                   = '#000000';
			$cs_configuration['banner']['backgroundColor']             = '#FFFFFF';
			$cs_configuration['banner']['customizeButtonCaptionColor'] = '#4D4D4D';
			$cs_configuration['banner']['customizeButtonColor']        = '#DADADA';
		} else {
			$cs_configuration['banner']['style'] = 'dark';
		}

		$background_overlay = iub_array_get( $args, 'background_overlay' );
		if ( $background_overlay ) {
			$cs_configuration['banner']['backgroundOverlay'] = true;
		}

		// No need to sanitize because we just build the embed code.
		// phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript
		$after_configuration .= '<script type="text/javascript" src="//cdn.iubenda.com/cs/iubenda_cs.js" charset="UTF-8" async></script>';

		return $before_configuration . wp_json_encode( $cs_configuration ) . '; ' . $after_configuration;
	}
}
