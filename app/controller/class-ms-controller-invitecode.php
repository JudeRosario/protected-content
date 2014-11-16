<?php
class MS_Controller_Invite_Code extends MS_Controller {

	public function __construct() {
		parent::__construct();

		$hook = 'protect-content_page_protected-content-invite-codes';
		$this->add_action( 'load-' . $hook, 'admin_coupon_manager' );

		$this->add_action( 'admin_print_scripts-' . $hook, 'enqueue_scripts' );
		$this->add_action( 'admin_print_styles-' . $hook, 'enqueue_styles' );
	}

public function admin_invite_manager() {

		/**
		 * Save coupon add/edit
		 */
		$isset = array( 'submit', 'membership_id' );
		if ( $this->validate_required( $isset, 'POST', false ) && $this->verify_nonce() && $this->is_admin_user() ) {
			$msg = $this->save_coupon( $_POST );
			wp_safe_redirect( add_query_arg( array( 'msg' => $msg ), remove_query_arg( array( 'invite_code') ) ) ) ;
			exit;
		}
		/**
		 * Execute table single action.
		 */
		elseif( $this->validate_required( array( 'invite_code', 'action' ), 'GET' ) && $this->verify_nonce( $_GET['action'], 'GET' ) && $this->is_admin_user() ) {
			$msg = $this->coupon_do_action( $_GET['action'], array( $_GET['invite_code'] ) );
			wp_safe_redirect( add_query_arg( array( 'msg' => $msg ), remove_query_arg( array( 'invite_code', 'action', '_wpnonce' ) ) ) );
			exit;
		}
		/**
		 * Execute bulk actions.
		 */
		elseif( $this->validate_required( array( 'invite_code' ) ) && $this->verify_nonce( 'bulk-coupons' ) && $this->is_admin_user() ) {
			$action = $_POST['action'] != -1 ? $_POST['action'] : $_POST['action2'];
			$msg = $this->coupon_do_action( $action, $_POST['invite_code'] );
			wp_safe_redirect( add_query_arg( array( 'msg' => $msg ) ) );
			exit;
		}
	}

public function invite_code_do_action( $action, $invite_codes ) {
		if( ! $this->is_admin_user() ) {
			return;
		}

		if( is_array( $invite_codes ) ) {
			foreach( $invite_codes as $invite_code ) {
				switch( $action ) {
					case 'delete':
						$invite_code = MS_Factory::load( 'MS_Model_Coupon', $coupon_id );
						$invite_code->delete();
						break;
				}
			}
		}
	}

private function save_invite_code( $fields ) {

	$invite_code = null;
	$msg = false;

	if( $this->is_admin_user() ) {
		if( is_array( $fields ) ) {
			$invite_code = ( $fields['invite_id'] ) ? $fields['invite_id'] : 0;
			$coupon = MS_Factory::load( 'MS_Model_Invite_Code', $invite_id );

			foreach( $fields as $field => $value ) {
				$invite_code->$field = $value;
			}
			$invite_code->save();
			$msg = true;
		}
	}

	return apply_filters( 'ms_model_invite_code_save_invite_code', $msg, $fields, $coupon, $this );
}

public function enqueue_styles() {
		if ( 'edit' == @$_GET['action'] ) {
			wp_enqueue_style( 'jquery-ui' );
		}

		do_action( 'ms_controller_invite_codes_enqueue_styles', $this );
	}

public function enqueue_scripts() {
		if ( 'edit' == @$_GET['action'] ) {
			wp_enqueue_script( 'jquery-ui' );
			wp_enqueue_script( 'jquery-validate' );
			wp_enqueue_script( 'ms-view-coupon-edit' );
		}

		do_action( 'ms_controller_invite_codes_enqueue_scripts', $this );
	}

}
