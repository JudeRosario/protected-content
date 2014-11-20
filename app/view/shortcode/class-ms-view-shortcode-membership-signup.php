<?php

class MS_View_Shortcode_Membership_Signup extends MS_View {

	public function to_html() {
		$settings = MS_Factory::load( 'MS_Model_Settings' );

		ob_start();
		?>
		<?php
		if(MS_Model_Addon::is_enabled( MS_Model_Addon::ADDON_INVITE_CODES )):
		echo $this->invite_code_html(); 		
		endif;
		?>

		<div class="ms-membership-form-wrapper">
			<?php
			if ( count( $this->data['ms_relationships'] ) > 0 ) {
				foreach ( $this->data['ms_relationships'] as $membership_id => $ms_relationship ){
					$msg = $ms_relationship->get_status_description();

					$membership = MS_Factory::load(
						'MS_Model_Membership',
						$ms_relationship->membership_id
					);

					switch ( $ms_relationship->status ) {
						case MS_Model_Membership_Relationship::STATUS_CANCELED:
							$this->membership_box_html(
								$membership,
								MS_Helper_Membership::MEMBERSHIP_ACTION_RENEW,
								$msg,
								$ms_relationship
							);
							break;

						case MS_Model_Membership_Relationship::STATUS_EXPIRED:
							$this->membership_box_html(
								$membership,
								MS_Helper_Membership::MEMBERSHIP_ACTION_RENEW,
								$msg,
								$ms_relationship
							);
							break;

						case MS_Model_Membership_Relationship::STATUS_TRIAL:
						case MS_Model_Membership_Relationship::STATUS_ACTIVE:
							$this->membership_box_html(
								$membership,
								MS_Helper_Membership::MEMBERSHIP_ACTION_CANCEL,
								$msg,
								$ms_relationship
							);
							break;

						case MS_Model_Membership_Relationship::STATUS_PENDING:
							$this->membership_box_html(
								$membership,
								MS_Helper_Membership::MEMBERSHIP_ACTION_PAY,
								$msg,
								$ms_relationship
							);
							break;

						default:
							$this->membership_box_html(
								$ms_relationship,
								MS_Helper_Membership::MEMBERSHIP_ACTION_CANCEL,
								$msg,
								$ms_relationship
							);
							break;
					}
				}
			}

			if ( $this->data['member']->has_membership() && ! empty( $this->data['memberships'] ) ) {
				?>
				<legend class="ms-move-from">
					<?php
					if ( empty( $this->data['move_from_id'] ) ) {
						_e( 'Add Membership Level', MS_TEXT_DOMAIN );
					}
					else {
						_e( 'Change Membership Level', MS_TEXT_DOMAIN );
					}
					?>
				</legend>
				<?php
			}
			?>
			<div class="ms-form-price-boxes">
				<?php
				do_action( 'ms_view_shortcode_membership_signup_form_before_memberships' );

				if ( ! empty( $this->data['move_from_id'] ) ) {
					$action = MS_Helper_Membership::MEMBERSHIP_ACTION_MOVE;
				}
				else {
					$action = MS_Helper_Membership::MEMBERSHIP_ACTION_SIGNUP;
				}

				$ic_object = MS_Model_Invite_Code::load_by_invite_code($_POST['invite_code']);
				$membership_types = $ic_object->__get('membership_type');
				foreach ( $this->data['memberships'] as $membership ) {
					if($membership->__get('id') == $ic_object->__get('membership_type') 
						|| $membership->__get('parent_id') == $ic_object->__get('membership_type')
						 || intval("0") == $ic_object->__get('membership_type'))
					{
						$this->membership_box_html( $membership, $action, null, null );
					}

		}
				?>
				<?php do_action( 'ms_view_shortcode_membership_signup_form_after_memberships' ) ?>
			</div>
		</div>

		<div style="clear:both;"></div>
		<?php

		$html = ob_get_clean();

		return $html;
	}

	/**
	 * Output the HTML content of a single membership box.
	 * This includes the membership name, description, price and the action
	 * button (Sign-up, Cancel, etc.)
	 *
	 * @since  1.0.0
	 * @param  MS_Model_Membership $membership
	 * @param  string $action
	 * @param  string $msg
	 * @param  MS_Model_Relationship $ms_relationship
	 */
	private function membership_box_html( $membership, $action, $msg = null, $ms_relationship = null ) {
		$fields = $this->prepare_fields( $membership->id, $action );
		$settings = MS_Factory::load( 'MS_Model_Settings' );

		if ( 0 == $membership->price ) {
			$price = __( 'Free', MS_TEXT_DOMAIN );
		} else {
			$price = sprintf(
				'%s %s',
				$settings->currency,
				number_format( $membership->price, 2 )
			);
		}
		$price = apply_filters( 'ms_membership_price', $price, $membership );

		?>
		<form class="ms-membership-form" method="post">
			<?php
			wp_nonce_field( $fields['action']['value'] );

			foreach ( $fields as $field ) {
				MS_Helper_Html::html_element( $field );
			}
			?>
			<div id="ms-membership-wrapper-<?php echo esc_attr( $membership->id ); ?>"
				class="ms-membership-details-wrapper">
				<div class="ms-top-bar">
					<h4><span class="ms-title"><?php echo esc_html( $membership->name ); ?></span></h4>
				</div>
				<div class="ms-price-details">
					<div class="ms-description"><?php echo $membership->description; ?></div>
					<div class="ms-price"><?php echo esc_html( $price ); ?></div>

					<?php if ( $msg ) : ?>
						<div class="ms-bottom-msg"><?php echo $msg; ?></div>
					<?php endif; ?>
				</div>

				<div class="ms-bottom-bar">
					<?php
					$class = apply_filters(
						'ms_view_shortcode_membership_signup_form_button_class',
						'ms-signup-button ' . esc_attr( $action )
					);

					$button = array(
						'id' => 'submit',
						'type' => MS_Helper_Html::INPUT_TYPE_SUBMIT,
						'value' => esc_html( $this->data[ "{$action}_text" ] ),
						'class' => $class,
					);

					if ( MS_Helper_Membership::MEMBERSHIP_ACTION_CANCEL === $action ) {
						$button = apply_filters(
							'ms_view_shortcode_membership_signup_cancel_button',
							$button,
							$ms_relationship,
							$this
						);
					}
					MS_Helper_Html::html_element( $button );
					?>
				</div>
			</div>
		</form>
		<?php
	}

	/**
	 * Return an array with input field definitions used on the
	 * membership-registration page.
	 *
	 * @since  1.0.0
	 *
	 * @param  int $membership_id
	 * @param  string $action
	 * @return array Field definitions
	 */

	private function prepare_fields( $membership_id, $action ) {
		$fields = array(
			'membership_id' => array(
				'id' => 'membership_id',
				'type' => MS_Helper_Html::INPUT_TYPE_HIDDEN,
				'value' => $membership_id,
			),
			'action' => array(
				'id' => 'action',
				'type' => MS_Helper_Html::INPUT_TYPE_HIDDEN,
				'value' => $this->data['action'],
			),
			'step' => array(
				'id' => 'step',
				'type' => MS_Helper_Html::INPUT_TYPE_HIDDEN,
				'value' => $this->data['step'],
			),
		);

		if ( ! empty( $this->data['move_from_id'] ) ) {
			$fields['move_from_id'] = array(
				'id' => 'move_from_id',
				'type' => MS_Helper_Html::INPUT_TYPE_HIDDEN,
				'value' => $this->data['move_from_id'],
			);
		}
		if ( ! empty( $this->data['invite_code'] ) ) {
			$fields['invite_code'] = array(
				'id' => 'move_from_id',
				'type' => MS_Helper_Html::INPUT_TYPE_HIDDEN,
				'value' => $this->data['invite_code'],
			);
		}

		if ( MS_Helper_Membership::MEMBERSHIP_ACTION_CANCEL == $action ) {
			$fields['action']['value'] = $action;
			unset( $fields['step'] );
		}

		return $fields;
	}

	private function invite_code_html() {
		$message = "";
		$valid = false;
	if(!MS_Model_Addon::is_enabled( MS_Model_Addon::ADDON_INVITE_CODES )):
		return;
	
	// Valid Invite Code Posted
	elseif(isset($_POST['invite_code']) && MS_Model_Invite_Code::load_by_invite_code($_POST['invite_code'])->is_valid_invite_code()):
		$invite_code = $_POST['invite_code'];
		$message = "Invite Code Valid . . . Please Choose a membership type from this list";
		$class = 'ms-alert-success';
		$valid = true; 
		$this->data['invite_code'] = $invite_code;
		$this->data['membership_types'] = &$membership_types;
		
	// Invalid Invite Code Posted 
	elseif(isset($_POST['invite_code']) && !MS_Model_Invite_Code::load_by_invite_code($_POST['invite_code'])->is_valid_invite_code()):
		$message = "Invalid Invite Code . . . Please try again ";
		$class = 'ms-alert-error';
		
	// Get user inputs
	
	else:
		$message = "Have an Invite Code ?";
	endif;
			$fields = array(
				'invite_code' => array(
					'id' => 'invite_code',
					'type' => MS_Helper_Html::INPUT_TYPE_TEXT,
					'value' => $invite_code,
				),
				'apply_invite_code' => array(
					'id' => 'apply_invite_code',
					'type' => MS_Helper_Html::INPUT_TYPE_SUBMIT,
					'value' => __( 'Apply Invite Code', MS_TEXT_DOMAIN ),
				),
			);


		ob_start();
		?>

		<div class="invite-form ms-membership-form-wrapper">
		<form 
		name="invite_code_form"
		id="invite_code_form"
		action="/register" 
		method="post"
		class="ms-login-form input">
		<legend>Sign up using an Invite Code</legend>
		<div>
					<?php if ( $message ) : ?>
						<p class="ms-alert-box <?php echo esc_attr( $class ); ?>" ><?php
							echo $message;
						?></p>
					<?php endif; ?>
		</div>
		<?
		foreach ( $fields as $field ){
			MS_Helper_Html::html_element( $field );
		}
		?>
		</form>
		</div>
		<?php
 	return ob_get_clean();
	}
}