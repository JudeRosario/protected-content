<?php


class MS_View_Invite_Codes_List extends MS_View {

	/**
	 * Create view output.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function to_html() {
		
		$invitecodes_list = new MS_Helper_List_Table_Invite_Codes();
		//$invitecodes_list->prepare_items();

		$title = __( 'Invite Codes', MS_TEXT_DOMAIN );
		$add_new_invite_codes_button = array(
				'id' => 'add_new',
				'type' => MS_Helper_Html::TYPE_HTML_LINK,
				'url' => sprintf( 'admin.php?page=%s&action=edit&invite_id=0', MS_Controller_Plugin::MENU_SLUG . '-invitecodes' ),
				'value' => __( 'Add New', MS_TEXT_DOMAIN ),
				'class' => 'button',);

		$add_new_checkbox_signup = array(
				'id' => 'signup-blocked',
				'type' => MS_Helper_Html::INPUT_TYPE_RADIO_SLIDER,
				'desc' => 'Block Sign ups for new users without Invite Codes. Existing uses will not be affected. Users can also be restricted by subscription model',
				'value' => __( 'Add New', MS_TEXT_DOMAIN ),
				'data_ms' => array(
					'action' => MS_Controller_Settings::AJAX_ACTION_TOGGLE_SETTINGS,
					'setting' => 'signup_blocked',
				)
				);
		
		$add_new_checkbox_show_at_login = array(
				'id' => 'show-at-login',
				'type' => MS_Helper_Html::INPUT_TYPE_RADIO_SLIDER,
				'desc' => 'Show form at Sign up page',
				'value' => __( 'Add New', MS_TEXT_DOMAIN ),
				'data_ms' => array(
					'action' => MS_Controller_Settings::AJAX_ACTION_TOGGLE_SETTINGS,
					'setting' => 'show_at_login',
				)
				);
		
		ob_start();
		?>
		
		<div class="wrap ms-wrap">
			<div class="invite-code-options">
				<form id="main-options" action="" method="post">
				<?php
				MS_Helper_Html::settings_box(
				array( $add_new_checkbox_signup),__( 'Block Sign-ups without Invite Codes', MS_TEXT_DOMAIN )
				);

				MS_Helper_Html::settings_box(
				array( $add_new_checkbox_show_at_login),__( 'Show form at log in page', MS_TEXT_DOMAIN )
				);

				MS_Helper_Html::settings_box(
				array( $add_new_invite_codes_button),__( 'Create new Invite Codes', MS_TEXT_DOMAIN )
				);
				?>

				</form>
			</div>
			<div  class="invite-code-tables">
				<form id="table-options" action="" method="post">
					<?php $invitecodes_list->display(); ?>
				</form>
			</div>
		</div>

		<?php
		$html = ob_get_clean();

		return apply_filters( 'ms_view_invitecodes_list_to_html', $html, $this );
	}
}