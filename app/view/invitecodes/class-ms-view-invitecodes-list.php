<?php


class MS_View_Invite_Codes_List extends MS_View {

	public function to_html() {
		
		 $invitecodes_list = new MS_Helper_List_Table_Invite_Codes();
		 $invitecodes_list->prepare_items();

		$title = __( 'Invite Codes', MS_TEXT_DOMAIN );
		$add_new_invite_codes_button = array(
				'id' => 'add_new',
				'type' => MS_Helper_Html::TYPE_HTML_LINK,
				'url' => sprintf( 'admin.php?page=%s&action=edit&invite_id=0', MS_Controller_Plugin::MENU_SLUG . '-invite-codes' ),
				'value' => __( 'Add New', MS_TEXT_DOMAIN ),
				'class' => 'button',);
		
		ob_start();
		?>
		
		<div class="wrap ms-wrap">
			<div class="invite-code-options">
				<form id="main-options" action="" method="post">
				<?php
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