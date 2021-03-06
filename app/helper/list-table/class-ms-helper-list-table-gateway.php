<?php
/**
 * @copyright Incsub (http://incsub.com/)
 *
 * @license http://opensource.org/licenses/GPL-2.0 GNU General Public License, version 2 (GPL-2.0)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston,
 * MA 02110-1301 USA
 *
*/

/**
 * Membership List Table to display payment gateways
 *
 * @since 1.0.0
 *
 * @return object
 */
class MS_Helper_List_Table_Gateway extends MS_Helper_List_Table {
	/**
	 * The list table id.
	 *
	 * @since 1.0.0
	 * @var int
	 * @access protected
	 */
	protected $id = 'gateway';

	/**
	 * Constructor containing list attributes.
	 *
	 * @param array $args An associative array with information about the current table
	 * @access public
	 */
	public function __construct(){
		parent::__construct(
			array(
				'singular' => 'gateway',
				'plural'   => 'gateways',
				'ajax'     => false,
			)
		);
	}

	/**
	 * Get a list of columns. The format is:
	 * 'internal-name' => 'Title'
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return array
	 */
	protected function get_columns() {
		return apply_filters(
			'membership_helper_list_table_gateway_columns',
			array(
				'name' => __( 'Gateway Name', MS_TEXT_DOMAIN ),
				'active' => __( 'Active', MS_TEXT_DOMAIN ),
			)
		);
	}

	/**
	 * Get a list of hidden columns. The format is:
	 * 'internal-name'
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return array
	 */
	protected function get_hidden_columns() {
		return apply_filters( 'ms_helper_list_table_gateway_hidden_columns', array() );
	}

	/**
	 * Get a list of sortable columns. The format is:
	 * 'internal-name' => 'orderby'
	 * or
	 * 'internal-name' => array( 'orderby', true )
	 *
	 * The second format will make the initial sorting order be descending
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return array
	 */
	protected function get_sortable_columns() {
		return apply_filters( 'ms_helper_list_table_gateway_sortable_columns', array() );
	}

	/**
	 * Prepares the list of items for displaying.
	 * @uses WP_List_Table::set_pagination_args()
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function prepare_items() {
		$this->_column_headers = array(
			$this->get_columns(),
			$this->get_hidden_columns(),
			$this->get_sortable_columns(),
		);

		$this->items = apply_filters(
			'ms_helper_list_table_gateway_items',
			MS_Model_Gateway::get_gateways()
		);

		unset( $this->items[ MS_Model_Gateway::GATEWAY_FREE ] );
	}

	/**
	 * Return contents of the column "Name"
	 *
	 * @since  1.0.0
	 * @access protected
	 *
	 * @param  MS_Model_Gateway $item A payment gateway.
	 * @return string HTML code to display in the list.
	 */
	protected function column_name( MS_Model_Gateway $item ) {
		$html = sprintf( '<div>%s %s</div>', $item->name, $item->description );
		$actions = array(
				sprintf(
					'<a href="#" data-ms-dialog="Gateway_%s_Dialog">%s</a>',
					esc_attr( $item->id ),
					__( 'Configure', MS_TEXT_DOMAIN )
				),
				sprintf(
					'<a href="?page=%s&gateway_id=%s">%s</a>',
					MS_Controller_Plugin::MENU_SLUG . '-billing',
					$item->id,
					__( 'View Transactions', MS_TEXT_DOMAIN )
				),
		);

		$actions = apply_filters(
			'gateway_helper_list_table_' . $this->id . '_column_name_actions',
			$actions,
			$item
		);

		return sprintf( '%1$s %2$s', $html, $this->row_actions( $actions ) );
	}

	/**
	 * Return contents of the column "Active"
	 *
	 * @since  1.0.0
	 * @access protected
	 *
	 * @param  MS_Model_Gateway $item A payment gateway.
	 * @return string HTML code to display in the list.
	 */
	protected function column_active( $item ) {
		$class = $item->is_configured() ? 'ms-gateway-configured' : 'ms-gateway-not-configured';

		$html = sprintf(
			'<div class="%1$s ms-active-wrapper-%2$s">',
			esc_attr( $class ),
			esc_attr( $item->id )
		);

		$toggle = array(
			'id' => 'ms-toggle-' . $item->id,
			'type' => MS_Helper_Html::INPUT_TYPE_RADIO_SLIDER,
			'value' => $item->active,
			'data_ms' => array(
				'action' => MS_Controller_Gateway::AJAX_ACTION_TOGGLE_GATEWAY,
				'gateway_id' => $item->id,
			),
		);
		$html .= MS_Helper_Html::html_element( $toggle, true );

		$html .= '<div class="ms-gateway-setup-wrapper">';
		$html .= sprintf(
			'<a class="button" href="#" data-ms-dialog="Gateway_%s_Dialog"><i class="ms-fa ms-fa-cog"></i> %s</a>',
			esc_attr( $item->id ),
			__( 'Configure', MS_TEXT_DOMAIN )
		);
		$html .= '</div></div>';

		return apply_filters( 'ms_helper_list_table_gateway_column_active', $html );
	}

	/**
	 * Get an associative array ( option_name => option_title ) with the list
	 * of bulk actions available on this table.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return array
	 */
	protected function get_bulk_actions() {
		return array();
	}

};
