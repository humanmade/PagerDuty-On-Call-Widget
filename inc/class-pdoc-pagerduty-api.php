<?php

class PDOC_PagerDuty_API {

	public function __construct( $account_id, $api_token ) {

		$this->account_id = $account_id;
		$this->api_token = $api_token;
	}

	public function request( $method, $url, $args = array() ) {

		$url = 'https://' . $this->account_id . '.pagerduty.com/api/v1' . $url;

		if ( $method === 'GET' ) {
			$url = add_query_arg( $args, $url );
		}

		$request = wp_remote_request( $url, array(
			'method' => $method,
			'headers' => array(
				'Authorization' => 'Token token=' . $this->api_token
			),
			'body' => $method === 'POST' ? $args : null

		));

		if ( is_wp_error( $request ) ) {
			return $request;
		}

		$response = json_decode( wp_remote_retrieve_body( $request ) );

		return $response;
	}
}