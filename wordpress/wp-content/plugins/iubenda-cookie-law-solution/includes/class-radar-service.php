<?php
/**
 * Iubenda radar service class.
 *
 * @package  Iubenda
 */

// exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Iubenda radar service.
 *
 * @Class Radar_Service
 */
class Radar_Service {

	/**
	 * Authorization data
	 *
	 * @var array
	 */
	private $authorization = array(
		'username' => 'devops',
		'password' => 'orIDiVvPVdHvwyjM4',
	);

	/**
	 * Service rating class.
	 *
	 * @var Service_Rating
	 */
	private $service_rating = '';

	/**
	 * Radar urls.
	 *
	 * @var array
	 */
	private $url = array(
		'match-async'    => 'https://radar.iubenda.com/api/match-async',
		'match-progress' => 'https://radar.iubenda.com/api/match-progress',
	);

	/**
	 * API configuration.
	 *
	 * @var array
	 */
	public $api_configuration = array();

	/**
	 * Update notice message.
	 *
	 * @var string
	 */
	private $update_message = "Please, make also sure the plugin is updated to the <a target='_blank' href='https://wordpress.org/plugins/iubenda-cookie-law-solution/'><u> latest version.</u></a>";

	/**
	 * Radar_Service constructor.
	 */
	public function __construct() {
		$this->service_rating    = new Service_Rating();
		$this->api_configuration = array_filter( (array) get_option( 'iubenda_radar_api_configuration', array() ) );
	}

	/**
	 * Ask radar to send request.
	 *
	 * @return bool
	 */
	public function ask_radar_to_send_request() {
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			iub_verify_ajax_request( 'iub_radar_percentage_reload_nonce', 'iub_nonce' );
		}

		if ( ! empty( $this->api_configuration ) ) {
			return $this->send_radar_progress_request();
		}

		return $this->send_radar_sync_request();
	}

	/**
	 * Force delete radar configuration.
	 *
	 * @return bool
	 */
	public function force_delete_radar_configuration() {
		return delete_option( 'iubenda_radar_api_configuration' );
	}

	/**
	 * Calculate radar percentage.
	 *
	 * @return array
	 */
	public function calculate_radar_percentage() {
		$services['pp']   = $this->service_rating->is_privacy_policy_activated();
		$services['cs']   = $this->service_rating->is_cookie_solution_activated();
		$services['cons'] = boolval( $this->service_rating->is_cookie_solution_activated() && $this->service_rating->is_cookie_solution_automatically_parse_enabled() );
		$services['tc']   = $this->service_rating->is_terms_conditions_activated();

		return array(
			'percentage' => ( count( array_filter( $services ) ) / count( $services ) ) * 100,
			'services'   => $services,
		);
	}

	/**
	 * Send radar sync request for the first time
	 *
	 * @return bool
	 */
	private function send_radar_sync_request() {
		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		$encoded_authorization = base64_encode( $this->authorization['username'] . ':' . $this->authorization['password'] );
		$website               = get_site_url();

		$data = array(
			'timeout'     => 30,
			'redirection' => 5,
			'httpversion' => '1.0',
			'headers'     => array( 'Authorization' => "Basic {$encoded_authorization}" ),
			'body'        => array(
				'url'                  => $website,
				'detectLegalDocuments' => 'true',
			),
		);

		$response      = wp_remote_get( iub_array_get( $this->url, 'match-async' ), $data );
		$response_code = wp_remote_retrieve_response_code( $response );

		// check response code.
		$this->check_response( $response, $response_code );

		$body = json_decode( iub_array_get( $response, 'body' ), true );

		$body['trial_num']  = 1;
		$body['next_trial'] = time();

		iubenda()->iub_update_options( 'iubenda_radar_api_configuration', $body );

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			wp_send_json(
				array(
					'code'   => $response_code,
					'status' => 'progress',
				)
			);
		}

		return true;
	}

	/**
	 * Send radar request to check the progress
	 *
	 * @return bool
	 */
	private function send_radar_progress_request() {
		$iubenda_radar_api_configuration = $this->api_configuration;

		if ( 'completed' === (string) iub_array_get( $iubenda_radar_api_configuration, 'status' ) ) {
			if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
				wp_send_json(
					array(
						'code'   => '200',
						'status' => 'complete',
						'data'   => $this->calculate_radar_percentage(),
					)
				);
			}

			return true;
		}

		// Check if the next trial is not now.
		$next_trial = (int) iub_array_get( $iubenda_radar_api_configuration, 'next_trial' );
		if ( $next_trial > time() ) {
			if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
				$next_request_in_sec = $next_trial - time();

				wp_send_json(
					array(
						'code'   => '200',
						'status' => 'timeout',
						'data'   => $next_request_in_sec,
					)
				);
			}

			return true;
		}

		$next_trial = time();
		$trial_num  = intval( iub_array_get( $iubenda_radar_api_configuration, 'trial_num', 1 ) ?? 1 );

		// Check if 3 trials were made in this round.
		if ( is_int( $trial_num / 3 ) ) {
			$rounds     = $trial_num / 3;
			$next_trial = time() + ( pow( 30, $rounds ) );
		}
		$trial_num++;

		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		$encoded_authorization = base64_encode( $this->authorization['username'] . ':' . $this->authorization['password'] );

		$id = iub_array_get( $iubenda_radar_api_configuration, 'id' );

		$data = array(
			'timeout'     => 30,
			'redirection' => 5,
			'httpversion' => '1.0',
			'headers'     => array( 'Authorization' => "Basic {$encoded_authorization}" ),
			'body'        => array( 'id' => $id ),
		);

		$response      = wp_remote_get( iub_array_get( $this->url, 'match-progress' ), $data );
		$response_code = wp_remote_retrieve_response_code( $response );

		// check response code.
		$this->check_response( $response, $response_code );

		$body               = json_decode( iub_array_get( $response, 'body' ), true );
		$body['trial_num']  = $trial_num;
		$body['next_trial'] = $next_trial;

		iubenda()->iub_update_options( 'iubenda_radar_api_configuration', $body );

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			wp_send_json(
				array(
					'code'   => $response_code,
					'status' => 'progress',
				)
			);
		}

		return true;
	}

	/**
	 * Check radar response
	 *
	 * @param   array|WP_Error $response       The response or WP_Error on failure.
	 * @param   int|string     $response_code  The response code as an integer. Empty string if incorrect parameter given.
	 *
	 * @return bool
	 */
	private function check_response( $response, $response_code ) {
		if ( is_wp_error( $response ) || 200 !== (int) $response_code ) {
			if ( ! is_numeric( $response_code ) ) {
				$message = $this->update_message;
			} elseif ( 408 === (int) $response_code ) {
				// 408 error code it`s mean request timeout
				$message = $this->update_message;
			} elseif ( 4 === (int) substr( $response_code, 0, 1 ) ) {
				// 4xx error codes
				$message = $this->update_message;
			} else {
				$message = 'Something went wrong: ' . $response->get_error_message();
			}

			if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
				wp_send_json(
					array(
						'code'    => $response_code,
						'status'  => 'error',
						'message' => $message,
					)
				);
			}
			return true;
		}
	}
}
