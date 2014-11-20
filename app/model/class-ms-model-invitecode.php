<?php

class MS_Model_Invite_Code extends MS_Model_Custom_Post_Type {

	public static $POST_TYPE = 'ms_invite_code';
	public $post_type = 'ms_invite_code';

	protected $invite_code;
	protected $start_date;
	protected $expire_date;
	protected $membership_type = 0;
	protected $max_uses;
	protected $used = 0;
	public $message;

	public $ignore_fields = array( 'message', 'actions', 'filters', 'ignore_fields', 'post_type' );
	
	public static function get_invite_codes_count( $args = null ) {

		$defaults = array(
				'post_type' => self::$POST_TYPE,
				'post_status' => 'any',
		);

		$args = wp_parse_args( $args, $defaults );
		$query = new WP_Query( $args );

		return apply_filters( 'ms_model_invite_code_get_invite_code_count', $query->found_posts, $args );
	}
	
	public static function get_invite_codes( $args = null ) {
		$defaults = array(
				'post_type' => self::$POST_TYPE,
				'posts_per_page' => 10,
				'post_status' => 'any',
				'order' => 'DESC',
		);
		$args = wp_parse_args( $args, $defaults );

		$query = new WP_Query($args);
		$items = $query->get_posts();

		$invite_codes = array();
		foreach ( $items as $item ) {
			$invite_codes[] = MS_Factory::load( 'MS_Model_Invite_Code', $item->ID );
		}

		return apply_filters( 'ms_model_invite_code_get_invite_codes', $invite_codes, $args );
	}

	public static function load_by_invite_code( $code ) {

		$code = sanitize_text_field( $code );

		$args = array(
				'post_type' => self::$POST_TYPE,
				'posts_per_page' => 1,
				'post_status' => 'any',
				'fields' => 'ids',
				'meta_query' => array(
					array(
							'key'     => 'invite_code',
							'value'   => $code,
					),
				)
		);

		$query = new WP_Query( $args );

		$item = $query->get_posts();

		$invite_id = 0;
		if( ! empty( $item[0] ) ) {
			$invite_id = $item[0];
			$message = 'Invite Code Found in Database';
		}

		return apply_filters( 'ms_model_invite_code_load_by_invite_code', MS_Factory::load( 'MS_Model_Invite_Code', $invite_id ), $code );
	}

	public function is_valid_invite_code( $membership_id = 0 ) {

		$valid = true;
		$this->message = null;

		if ( empty($this->invite_code)) {
			$this->message = __( 'Invite Code code not found.', MS_TEXT_DOMAIN ); 
			$valid = false;
		}
		if( $this->max_uses && $this->used >= $this->max_uses ) {
			$this->message = __( 'This Invite Code has been fully used up.', MS_TEXT_DOMAIN ); 
			$valid = false;
		}
		$timestamp = MS_Helper_Period::current_time( 'timestamp');
		if( ! empty( $this->start_date ) && strtotime( $this->start_date ) > $timestamp ) {
			$this->message = __( 'This Invite Code is not valid yet.', MS_TEXT_DOMAIN );
			$valid = false;
		}
		if( ! empty( $this->expire_date ) && strtotime( $this->expire_date ) < $timestamp ) {
			$this->message = __( 'This Invite Code has expired.', MS_TEXT_DOMAIN );
			$valid = false;
		}
		if( ! empty( $this->membership_id ) && $membership_id != $this->membership_id ) {
			$this->message = __( 'This Invite Code is not valid for this membership.', MS_TEXT_DOMAIN );
			$valid = false;
		}

		return apply_filters( 'ms_invite_code_model_is_valid_invite_code', $valid, $membership_id, $this );
	}

	public function save_invite_code_application( $ms_relationship ) {
		global $blog_id;

		$membership = $ms_relationship->get_membership();

		/** @TODO Handle for network/multsite mode.*/
		$global = false;

		/** Grab the user account as we should be logged in by now */
		$user = MS_Model_Member::get_current_member();

		$transient_name = apply_filters( 'ms_model_invite_code_transient_name', "ms_invite_code_{$blog_id}_{$user->id}_{$membership->id}" );
		$transient_value = apply_filters( 'ms_model_invite_code_transient_value', array(
				'invite_id' => $this->id,
				'user_id' => $user->id,
				'membership_id'	=> $membership->id,
				'message' => $this->message,
		) );

		if ( $global && function_exists( 'get_site_transient' ) ) {
			set_site_transient( $transient_name, $transient_value, $time );
		}
		else {
			set_transient( $transient_name, $transient_value, $time );
		}
		$this->save();

		do_action( 'ms_model_invite_code_save_invite_code_application', $ms_relationship, $this );
	}

	public static function get_invite_code_application( $user_id, $membership_id ) {
		global $blog_id;

		/** @TODO Handle for network/multsite mode.*/
		$global = false;

		$transient_name = apply_filters( 'ms_model_invite_code_transient_name', "ms_invite_code_{$blog_id}_{$user_id}_{$membership_id}" );

		if ( $global && function_exists( 'get_site_transient' ) ) {
			$transient_value = get_site_transient( $transient_name );
		}
		else {
			$transient_value = get_transient( $transient_name );
		}

		$invite_code = null;
		if( ! empty ( $transient_value ) ) {
			$invite_code = MS_Factory::load( 'MS_Model_Invite_Code', $transient_value['invite_id'] );
			$invite_code->message = $transient_value['message'];
		}

		return apply_filters( 'ms_model_invite_code_get_invite_code_application', $invite_code, $user_id, $membership_id );
	}

	public static function remove_invite_code_application( $user_id, $membership_id ) {

		global $blog_id;

		/** @todo Handle for network/multsite mode.*/
		$global = false;

		$transient_name = apply_filters( 'ms_model_invite_code_transient_name', "ms_invite_code_{$blog_id}_{$user_id}_{$membership_id}" );

		if ( $global && function_exists( 'delete_site_transient' ) ) {
			delete_site_transient( $transient_name );
		}
		else {
			delete_transient( $transient_name );
		}

		do_action( 'ms_model_invite_code_remove_invite_code_application', $user_id, $membership_id );
	}

	public function __get( $property ) {
		switch( $property ) {
			case 'remaining_uses':
				if( $this->max_uses > 0 ) {
					$value = $this->max_uses - $this->used;
				}
				else {
					$value = __( 'Unlimited', MS_TEXT_DOMAIN );
				}
				break;
			default:
				$value = $this->$property;
				break;
		}

		return apply_filters( 'ms_model_invite_code__get', $value, $property, $this );

	}

	public function __set( $property, $value ) {
		if( property_exists( $this, $property ) ) {
			switch( $property ) {
				case 'invite_code':
					$value = sanitize_text_field( preg_replace("/[^a-zA-Z0-9\s]/", "", $value ) );
					$this->$property = strtoupper( $value );
					$this->name = $this->$property;
					break;
				case 'start_date':
					$this->$property = $this->validate_date( $value );
					break;
				case 'expire_date':
					$this->$property = $this->validate_date( $value );
					if( strtotime( $this->$property ) < strtotime( $this->start_date ) ) {
						$this->$property = null;
					}
					break;
				case 'membership_type':
					if( 0 == $value || MS_Model_Membership::is_valid_membership( $value ) ) {
						$this->$property = $value;
					}
					break;
				case 'max_uses':
				case 'used':
					$this->$property = absint( $value );
					break;
				default:
					$this->$property = $value;
					break;
			}
		}

		do_action( 'ms_model_invite_code__set_after', $property, $value, $this );
	}
}