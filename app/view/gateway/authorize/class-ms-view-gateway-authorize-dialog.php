<?php

/**
 * Dialog: Payment Gateway "Authorize".
 *
 * Extends MS_View for rendering methods and magic methods.
 *
 * @since 1.0.0
 * @package Membership
 * @subpackage View
 */
class MS_View_Gateway_authorize_Dialog extends MS_Dialog {

	protected $gateway_id = MS_Model_Gateway::GATEWAY_AUTHORIZE;

	/**
	 * Generate/Prepare the dialog attributes.
	 *
	 * @since 1.0
	 */
	public function prepare() {
		$view = MS_Factory::create( 'MS_View_Gateway_Authorize_Settings' );

		$data = array(
			'model' => MS_Model_Gateway::factory( $this->gateway_id ),
			'action' => 'edit',
		);

		$view->data = apply_filters( 'ms_view_gateway_settings_edit_data', $data );
		$view = apply_filters( 'ms_view_gateway_settings_edit', $view, $this->gateway_id );
		$gateway = $view->data['model'];

		// Dialog Title
		$this->title = sprintf( __( '%s settings', MS_TEXT_DOMAIN ), $gateway->name );

		// Dialog Size
		$this->height = 420;

		// Contents
		$this->content = $view->to_html();
	}

	/**
	 * Save the gateway details.
	 *
	 * @since  1.0.0
	 * @return string
	 */
	public function submit() {
		$data = $_POST;

		unset( $data['action'] );
		unset( $data['dialog'] );

		$cont = MS_Plugin::instance()->controller->controllers['gateway'];
		$res = $cont->gateway_list_do_action( 'edit', array( $this->gateway_id ), $data );

		return $res;
	}

};