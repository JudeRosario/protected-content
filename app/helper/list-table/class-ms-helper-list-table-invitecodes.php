<?php

class MS_Helper_List_Table_Invite_Codes extends MS_Helper_List_Table {

	protected $id = 'code';

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
			'membership_helper_list_table_invite_code_columns',
			array(
				'icb' => '<input type="checkbox" />',
				'invite_code' => __( 'Invite Code', MS_TEXT_DOMAIN ),
				'start_date' => __( 'Valid from', MS_TEXT_DOMAIN ),
				'expiry_date' => __( 'Valid upto', MS_TEXT_DOMAIN ),
				'membership_type' => __( 'Membership', MS_TEXT_DOMAIN ),
				'times_used' => __( 'Used', MS_TEXT_DOMAIN ),
				'max_uses' => __( 'Maximum uses', MS_TEXT_DOMAIN ),
			)
		);
	}

	public function get_hidden_columns() {
		return apply_filters(
			'membership_helper_list_table_membership_hidden_columns',
			array()
		);
	}

	public function get_sortable_columns() {
		return apply_filters(
			'membership_helper_list_table_membership_sortable_columns',
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
		$per_page = $this->get_items_per_page( 'coupon_per_page', 10 );
		$current_page = $this->get_pagenum();

		$args = array(
			'posts_per_page' => $per_page,
			'offset' => ( $current_page - 1 ) * $per_page,
		);

		$this->items = apply_filters(
			'membership_helper_list_table_coupon_items',
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
			'<input type="checkbox" name="coupon_id[]" value="%1$s" />',
			esc_attr( $item->id )
		);
	}

	public function column_code( $item ) {
		$actions = array();
		$actions['edit'] = sprintf(
			'<a href="?page=%s&action=%s&coupon_id=%s">%s</a>',
			esc_attr( $_REQUEST['page'] ),
			'edit',
			esc_attr( $item->id ),
			__( 'Edit', MS_TEXT_DOMAIN )
		);
		$actions['delete'] = sprintf(
			'<span class="delete"><a href="%s">%s</a></span>',
			wp_nonce_url(
				sprintf(
					'?page=%s&coupon_id=%s&action=%s',
					esc_attr( $_REQUEST['page'] ),
					esc_attr( $item->id ),
					'delete'
				),
				'delete'
			),
			__( 'Delete', MS_TEXT_DOMAIN )
		);

		printf( '%1$s %2$s', $item->name, $this->row_actions( $actions ) );
	}

	public function column_default( $item, $column_name ) {
		$html = '';
		switch ( $column_name ) {
			case 'imembership':
				if ( MS_Model_Membership::is_valid_membership( $item->membership_type ) ) {
					$membership = MS_Factory::load( 'MS_Model_Membership', $item->membership_type);
					$html = $membership->name;
				}
				else {
					$html = __( 'Any', MS_TEXT_DOMAIN );
				}
				break;

			case 'iexpiry_date':
				if ( $item->expiry_date ) {
					$html = $item->expiry_date;
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
			'membership_helper_list_table_membership_bulk_actions',
			array(
				'delete' => __( 'Delete', MS_TEXT_DOMAIN ),
			)
		);
	}

}