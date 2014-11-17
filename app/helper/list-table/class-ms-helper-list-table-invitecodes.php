<?php

class MS_Helper_List_Table_Invite_Codes extends MS_Helper_List_Table {

	protected $id = 'invite_code';

	public function __construct(){
		parent::__construct(
			array(
				'singular' => 'code',
				'plural'   => 'codes',
				'ajax'     => false,
			)
		);
	}

	public function get_columns() {
		return apply_filters(
			'invite_code_helper_list_table_invite_code_columns',
			array(
				'cb' => '<input type="checkbox" />',
				'invite_code' => __( 'Invite Code', MS_TEXT_DOMAIN ),
				'start_date' => __( 'Start date', MS_TEXT_DOMAIN ),
				'expire_date' => __( 'Expire date', MS_TEXT_DOMAIN ),
				'membership_type' => __( 'Subscription Type', MS_TEXT_DOMAIN ),
				'used' => __( 'Times Used', MS_TEXT_DOMAIN ),
				'remaining_uses' => __( 'Remaining uses', MS_TEXT_DOMAIN ),
			)
		);
	}

	public function get_hidden_columns() {
		return apply_filters(
			'invite_code_helper_list_table_hidden_columns',
			array()
		);
	}

	public function get_sortable_columns() {
		return apply_filters(
			'invite_code_helper_list_table_sortable_columns',
			array()
		);
	}

	public function prepare_items() {
		$this->_column_headers = array(
			$this->get_columns(),
			$this->get_hidden_columns(),
			$this->get_sortable_columns(),
		);

		$total_items = MS_Model_Invite_Code::get_invite_codes_count();
		$per_page = $this->get_items_per_page( 'invite_codes_per_page', 10 );
		$current_page = $this->get_pagenum();

		$args = array(
			'posts_per_page' => $per_page,
			'offset' => ( $current_page - 1 ) * $per_page,
		);

		$this->items = apply_filters(
			'invite_code_helper_list_table_invite_code_items',
			MS_Model_Invite_Code::get_invite_codes( $args )
		);

		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page' => $per_page,
			)
		);
	}

	public function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="invite_id[]" value="%1$s" />',
			esc_attr( $item->id )
		);
	}

	public function column_invite_code( $item ) {
		$actions = array();
		$actions['edit'] = sprintf(
			'<a href="?page=%s&action=%s&invite_id=%s">%s</a>',
			esc_attr( $_REQUEST['page'] ),
			'edit',
			esc_attr( $item->id ),
			__( 'Edit', MS_TEXT_DOMAIN )
		);
		$actions['delete'] = sprintf(
			'<span class="delete"><a href="%s">%s</a></span>',
			wp_nonce_url(
				sprintf(
					'?page=%s&invite_id=%s&action=%s',
					esc_attr( $_REQUEST['page'] ),
					esc_attr( $item->id ),
					'delete'
				),
				'delete'
			),
			__( 'Delete', MS_TEXT_DOMAIN )
		);

		printf( '%1$s %2$s', $item->invite_code, $this->row_actions( $actions) );
	}

	public function column_default( $item, $column_name ) {
		$html = '';
		switch ( $column_name ) {
			case 'membership_type':
				if ( MS_Model_Membership::is_valid_membership( $item->membership_type ) ) {
					$membership = MS_Factory::load( 'MS_Model_Membership', $item->membership_type );
					$html = $membership->name;
				}
				else {
					$html = __( 'Any', MS_TEXT_DOMAIN );
				}
				break;

			case 'start_date':
				if ( $item->start_date) {
					$html = $item->start_date;
				}
				else {
					$html = __( 'Always Valid', MS_TEXT_DOMAIN );
				}
				break;

			case 'expire_date':
				if ( $item->expire_date ) {
					$html = $item->expire_date;
				}
				else {
					$html = __( 'No expiry', MS_TEXT_DOMAIN );
				}
				break;

			default:
				$html = $item->$column_name;
				break;
		}
		return $html;
	}

	public function get_bulk_actions() {
		return apply_filters(
			'invite_code_helper_list_table_invite_code_bulk_actions',
			array(
				'delete' => __( 'Delete', MS_TEXT_DOMAIN ),
			)
		);
	}

}