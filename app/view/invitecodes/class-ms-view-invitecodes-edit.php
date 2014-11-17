<?php

class MS_View_Invite_Codes_Edit extends MS_View {

	protected $data;

	public function to_html() {
		$fields = $this->prepare_fields();
		ob_start();
		/** Render tabbed interface. */
		?>
			<div class='ms-wrap'>
				<?php
					$text = !$this->data['invite_code']->is_valid() ? __( 'Add', MS_TEXT_DOMAIN ) : __( 'Edit', MS_TEXT_DOMAIN );
					MS_Helper_Html::settings_header( array(
						'title' => sprintf( __( ' %s Invite Code', MS_TEXT_DOMAIN ), $text ),
						'title_icon_class' => 'ms-fa ms-fa-pencil-square',
					) );
				?>
				<form action="<?php echo remove_query_arg( array( 'action', 'invite_id' ) ); ?>" method="post" class="ms-form">
					<?php MS_Helper_Html::settings_box( $fields ); ?>
				</form>
				<div class="clear"></div>
			</div>
		<?php
		$html = ob_get_clean();

		return apply_filters( 'ms_view_invite_codes_edit_to_html', $html, $this );
	}

	function prepare_fields() {
		$invite_code = $this->data['invite_code'];
		$fields = array(
			'invite_code' => array(
					'id' => 'invite_code',
					'title' => __( 'Invite code', MS_TEXT_DOMAIN ),
					'type' => MS_Helper_Html::INPUT_TYPE_TEXT,
					'value' => ($invite_code->invite_code) ? $invite_code->invite_code : $this->generate_code(),
			),
			'start_date' => array(
					'id' => 'start_date',
					'title' => __( 'Start date', MS_TEXT_DOMAIN ),
					'type' => MS_Helper_Html::INPUT_TYPE_TEXT,
					'value' => ( $invite_code->start_date ) ? $invite_code->start_date : "",
					'placeholder'   => '0 = Always Valid',
					'class' => 'ms-date',
			),
			'expire_date' => array(
					'id' => 'expire_date',
					'title' => __( 'Expiry date', MS_TEXT_DOMAIN ),
					'type' => MS_Helper_Html::INPUT_TYPE_TEXT,
					'value' => $invite_code->expire_date,
					'placeholder'   => '0 = Never Expires',
					'class' => 'ms-date',
			),
			'membership_type' => array(
					'id' => 'membership_type',
					'title' => __( 'Memberships', MS_TEXT_DOMAIN ),
					'type' => MS_Helper_Html::INPUT_TYPE_SELECT,
					'field_options' => $this->data['memberships'],
					'value' => ($invite_code->membership_type)? $invite_code->membership_type : "Any" ,
			),
			'max_uses' => array(
					'id' => 'max_uses',
					'title' => __( 'Max uses', MS_TEXT_DOMAIN ),
					'type' => MS_Helper_Html::INPUT_TYPE_TEXT,
					'placeholder'   => '0 = Unlimited uses',
					'value' => $invite_code->max_uses,
			),
			'invite_id' => array(
					'id' => 'invite_id',
					'type' => MS_Helper_Html::INPUT_TYPE_HIDDEN,
					'value' => $invite_code->id,
			),
			'_wpnonce' => array(
					'id' => '_wpnonce',
					'type' => MS_Helper_Html::INPUT_TYPE_HIDDEN,
					'value' => wp_create_nonce( $this->data['action'] ),
			),
			'action' => array(
					'id' => 'action',
					'type' => MS_Helper_Html::INPUT_TYPE_HIDDEN,
					'value' => $this->data['action'],
			),
			'separator' => array(
					'type' => MS_Helper_Html::TYPE_HTML_SEPARATOR,
			),
			'cancel' => array(
					'id' => 'cancel',
					'type' => MS_Helper_Html::TYPE_HTML_LINK,
					'title' => __( 'Cancel', MS_TEXT_DOMAIN ),
					'value' => __( 'Cancel', MS_TEXT_DOMAIN ),
					'url' => remove_query_arg( array( 'action', 'invite_id' ) ),
					'class' => 'ms-link-button button',
			),
			'submit' => array(
					'id' => 'submit',
					'type' => MS_Helper_Html::INPUT_TYPE_SUBMIT,
					'value' => __( 'Save Changes', MS_TEXT_DOMAIN ),
			),
		);

		return apply_filters( 'ms_view_invite_codes_edit_prepare_fields', $fields, $this );
	}

	function generate_code() {
		return strtoupper(uniqid());
	}
}