<?php
/**
 * Iubenda cs product service.
 *
 * @package  Iubenda
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class CS product service.
 */
class Iubenda_CS_Product_Service extends Iubenda_Abstract_Product_Service {

	/**
	 * Accepted CS Options.
	 *
	 * @var array
	 */
	private $accepted_options = array(
		'parser_engine'      => array( 'new', 'default' ),
		'configuration_type' => array( 'simplified', 'manual' ),
		'simplified'         => array(
			'position'        => array(
				'float-top-left',
				'float-top-center',
				'float-top-right',
				'float-bottom-left',
				'float-bottom-center',
				'float-bottom-right',
				'full-top',
				'full-bottom',
				'float-center',
			),
			'banner_style'    => array( 'dark', 'light' ),
			'legislation'     => array( 'gdpr', 'uspr', 'lgpd', 'all' ),
			'require_consent' => array( 'eu_only', 'worldwide' ),
		),
	);

	/**
	 * Saving Iubenda cookie law solution options
	 *
	 * @param   bool $default_options  If true insert the default options.
	 */
	public function saving_cs_options( $default_options = true ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$request_cs_option = (array) iub_array_get( $_POST, 'iubenda_cookie_law_solution', array() );
		$cs_default_keys   = array_keys( iubenda()->defaults['cs'] );
		$cs_default_keys   = array_merge( $this->get_languages_code_keys(), $cs_default_keys );
		$new_cs_option     = iub_array_only( $request_cs_option, $cs_default_keys );

		$iubenda_cookie_solution_generator = new Cookie_Solution_Generator();
		$global_options                    = iubenda()->options['global_options'];

		$codes_statues = array();
		if ( ! $default_options ) {
			// CS plugin general options.
			$new_cs_option['parse']         = isset( $new_cs_option['parse'] );
			$new_cs_option['parser_engine'] = $this->get_only_valid_values( iub_array_get( $new_cs_option, 'parser_engine' ), $this->accepted_options['parser_engine'], iubenda()->defaults['cs']['parser_engine'] );
			$new_cs_option['skip_parsing']  = isset( $new_cs_option['skip_parsing'] );
			$new_cs_option['block_gtm']     = isset( $new_cs_option['block_gtm'] );
			$new_cs_option['amp_support']   = isset( $new_cs_option['amp_support'] );
		}

		$new_cs_option['custom_scripts'] = isset( $new_cs_option['custom_scripts'] ) ? $this->prepare_custom_scripts_iframes( (array) iub_array_get( $new_cs_option, 'custom_scripts' ), 'script' ) : array();
		$new_cs_option['custom_iframes'] = isset( $new_cs_option['custom_iframes'] ) ? $this->prepare_custom_scripts_iframes( (array) iub_array_get( $new_cs_option, 'custom_iframes' ), 'iframe' ) : array();

		$new_cs_option['configuration_type'] = $this->get_only_valid_values( iub_array_get( $new_cs_option, 'configuration_type' ), $this->accepted_options['configuration_type'], iubenda()->defaults['cs']['configuration_type'] );

		if ( 'simplified' === $new_cs_option['configuration_type'] ) {
			$simplified_options      = iub_array_get( $new_cs_option, 'simplified' );
			$simplified_default_keys = array_keys( iubenda()->defaults['cs']['simplified'] );
			$simplified_options      = iub_array_only( $simplified_options, $simplified_default_keys );

			$simplified_options['position']        = $this->get_only_valid_values( iub_array_get( $simplified_options, 'position' ), $this->accepted_options['simplified']['position'], iubenda()->defaults['cs']['simplified']['position'] );
			$simplified_options['banner_style']    = $this->get_only_valid_values( iub_array_get( $simplified_options, 'banner_style' ), $this->accepted_options['simplified']['banner_style'], iubenda()->defaults['cs']['simplified']['banner_style'] );
			$simplified_options['legislation']     = $this->get_only_valid_values( iub_array_get( $simplified_options, 'legislation' ), $this->accepted_options['simplified']['legislation'], iubenda()->defaults['cs']['simplified']['legislation'] );
			$simplified_options['require_consent'] = $this->get_only_valid_values( iub_array_get( $simplified_options, 'require_consent' ), $this->accepted_options['simplified']['require_consent'], iubenda()->defaults['cs']['simplified']['require_consent'] );
			$simplified_options['tcf']             = isset( $simplified_options['tcf'] );
			$simplified_options['explicit_accept'] = isset( $simplified_options['explicit_accept'] );
			$simplified_options['explicit_reject'] = isset( $simplified_options['explicit_reject'] );

			// Check explicit accept & reject forced on if TCF is on.
			if ( true === $simplified_options['tcf'] ) {
				$simplified_options['explicit_accept'] = true;
				$simplified_options['explicit_reject'] = true;
			}

			$new_cs_option['simplified'] = $simplified_options;

			$languages = ( new Product_Helper() )->get_languages();
			// loop on iubenda->>language.
			foreach ( $languages as $lang_id => $lang_name ) {
				$privacy_policy_id = iub_array_get( $global_options, "public_ids.{$lang_id}" );
				$site_id           = iub_array_get( $global_options, 'site_id' );

				// Check if there is no public id for this language.
				if ( empty( $privacy_policy_id ) || empty( $site_id ) ) {
					continue;
				}

				// Generating CS Simplified code.
				$cs_embed_code = $iubenda_cookie_solution_generator->handle( $lang_id, $site_id, $privacy_policy_id, $simplified_options );

				$new_cs_option[ "code_{$lang_id}" ] = $this->iub_strip_slashes_deep( $cs_embed_code );
				// generate amp template file if the code is valid.
				// generate amp template file.
				if ( $cs_embed_code && (bool) iub_array_get( $new_cs_option, 'amp_support' ) ) {
					$amp_source                                     = iub_array_get( $new_cs_option, 'amp_source' );
					$amp_template                                   = iub_array_get( $new_cs_option, "amp_template.{$lang_id}" );
					$amp_options                                    = $this->handle_amp_generation_operations( $cs_embed_code, $lang_id, $amp_source, $amp_template );
					$new_cs_option['amp_template'][ $lang_id ]      = iub_array_get( $amp_options, 'amp_template' );
					$new_cs_option['amp_template_done'][ $lang_id ] = iub_array_get( $amp_options, 'amp_template_done' );
				}
			}
		} elseif ( 'manual' === $new_cs_option['configuration_type'] ) {
			foreach ( $new_cs_option as $index => $option ) {
				// check code if valid or not.
				if ( ! empty( $option ) && 0 === strpos( $index, 'code_' ) ) {
					$lang_id       = substr( $index, 5 );
					$cs_embed_code = stripslashes_deep( $option );
					// Getting data from embed code.
					$parsed_code = iubenda()->parse_configuration( $cs_embed_code );

					$new_cs_option[ "manual_{$index}" ] = $cs_embed_code;
					$codes_statues[ $lang_id ]          = true;
					// getting cookiePolicyId to save it into Iubenda global option.
					if ( ! empty( iub_array_get( $parsed_code, 'cookiePolicyId' ) ) ) {
						$global_options['public_ids'][ $lang_id ] = sanitize_key( iub_array_get( $parsed_code, 'cookiePolicyId' ) );
					}

					// getting site id to save it into Iubenda global option.
					if ( empty( iub_array_get( $global_options, 'site_id' ) ) && iub_array_get( $parsed_code, 'siteId' ) ) {
						$global_options['site_id'] = sanitize_key( iub_array_get( $parsed_code, 'siteId' ) );
					}

					// generate amp template file.
					if ( (bool) iub_array_get( $new_cs_option, 'amp_support' ) ) {
						$amp_source                                     = iub_array_get( $new_cs_option, 'amp_source' );
						$amp_template                                   = iub_array_get( $new_cs_option, "amp_template.{$lang_id}" );
						$amp_options                                    = $this->handle_amp_generation_operations( $cs_embed_code, $lang_id, $amp_source, $amp_template );
						$new_cs_option['amp_template'][ $lang_id ]      = iub_array_get( $amp_options, 'amp_template' );
						$new_cs_option['amp_template_done'][ $lang_id ] = iub_array_get( $amp_options, 'amp_template_done' );
					}
				}
			}
			// validating Embed Codes of CS contains at least one valid code.
			if ( count( array_filter( $codes_statues ) ) === 0 ) {
				wp_send_json(
					array(
						'status'       => 'error',
						'responseText' => esc_html__( '( Iubenda cookie law solution ) At least one code must be valid.', 'iubenda' ),
					)
				);
			}
		}

		if ( isset( $new_cs_option['amp_template'] ) && is_array( $new_cs_option['amp_template'] ) ) {
			// esc_url_raw for remote AMP links to languages that files are not generated.
			$new_cs_option['amp_template'] = array_map( 'esc_url_raw', $new_cs_option['amp_template'] );
		}

		// set the product configured option true.
		$new_cs_option['configured']             = 'true';
		$new_cs_option['us_legislation_handled'] = true;

		// Update only cs make it activated service.
		iubenda()->options['activated_products']['iubenda_cookie_law_solution'] = 'true';
		iubenda()->iub_update_options( 'iubenda_activated_products', iubenda()->options['activated_products'] );

		// Sanitize all options except the option with %code% because it contains a user embed script.
		$new_cs_option = $this->sanitize_options( $new_cs_option, array( 'code_', 'manual_code_', 'custom_scripts', 'custom_iframes' ) );

		// Merging new CS options with old ones.
		$new_cs_option  = $this->iub_strip_slashes_deep( $new_cs_option );
		$old_cs_options = $this->iub_strip_slashes_deep( iubenda()->options['cs'] );
		$new_cs_option  = wp_parse_args( $new_cs_option, $old_cs_options );

		// Saving and update the current instance with new CS options.
		iubenda()->options['cs'] = $new_cs_option;
		iubenda()->iub_update_options( 'iubenda_cookie_law_solution', $new_cs_option );

		// Saving and update the current instance with new global options.
		iubenda()->options['global_options'] = $global_options;
		iubenda()->iub_update_options( 'iubenda_global_options', $global_options );
	}

	/**
	 * Prepare custom scripts iframes
	 *
	 * @param   array  $data  Array of custom_(scripts/iframes).
	 * @param   string $flag  Scripts/Iframes.
	 *
	 * @return array|ArrayAccess|mixed|null
	 */
	private function prepare_custom_scripts_iframes( $data, $flag ) {
		return array_combine(
			array_map( 'sanitize_text_field', (array) iub_array_get( $data, $flag, array() ) ),
			array_map( 'intval', (array) iub_array_get( $data, 'type', array() ) )
		);
	}

	/**
	 * Handling amp generation operations.
	 *
	 * @param   string $code          CS Embed code.
	 * @param   string $lang_id       Language ID.
	 * @param   string $source        Source (local - remote).
	 * @param   string $amp_template  Optional - Remote AMP template URL.
	 *
	 * @return array
	 */
	private function handle_amp_generation_operations( string $code, string $lang_id, string $source, $amp_template = null ) {
		$result = array();

		$template_done = (bool) iubenda()->AMP->generate_amp_template( $code, $lang_id );

		if ( ( 'local' === $source ) && false === $template_done ) {
			( new Quick_Generator_Service() )->add_amp_permission_error();
		}

		if ( 'remote' === $source && $amp_template ) {
			$result['amp_template'] = esc_url_raw( $amp_template );
		}
		$result['amp_template_done'] = $template_done;

		return $result;
	}
}
