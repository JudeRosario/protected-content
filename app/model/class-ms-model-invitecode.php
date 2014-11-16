<?php

class MS_Model_Invite_Code extends MS_Model_Custom_Post_Type {

	public static $POST_TYPE = 'ms_invite_code';
	public $post_type = 'ms_invite_code';

	protected $invite_id;
	protected $invite_code;
	protected $start_date;
	protected $expiry_date;
	protected $times_used;
	protected $membership_type;
	protected $max_uses;
	protected $active;
	protected $message;

public static function get_invite_codes_count( $args = null ) {

		$defaults = array(
				'post_type' => self::$POST_TYPE,
				'post_status' => 'any',
		);

		$args = wp_parse_args( $args, $defaults );
		$query = new WP_Query( $args );

		return apply_filters( 'ms_model_invite_code_get_count', $query->found_posts, $args );

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
			$invite_codes[] = MS_Factory::load( 'MS_Model_Coupon', $item->ID );
		}

	return apply_filters( 'ms_model_invite_code_get_invite_codes', $invite_codes, $args );

}
	

public static function get_by_invite_code( $invite_code ) {

		$invite_code = sanitize_text_field( $invite_code );

		$args = array(
				'post_type' => self::$POST_TYPE,
				'posts_per_page' => 1,
				'post_status' => 'any',
				'fields' => 'ids',
				'meta_query' => array(
					array(
							'key'     => 'invite_code',
							'value'   => $invite_code,
					),
				)
		);

		$query = new WP_Query( $args );
		$item = $query->get_posts();

		$invite_id = 0;
		if( ! empty( $item[0] ) ) {
			$invite_id = $item[0];
		}

	return apply_filters( 'ms_model_invite_code_get_invite_by_code', MS_Factory::load( 'MS_Model_Coupon', $invite_id ), $invite_code );

}

public function apply_invite_code( $membership_type = null ) {

		$invite_code = sanitize_text_field( $invite_code );
		$current = $get_by_invite_code($invite_code);
		$is_applied = false; 
		$message = null;
		
		if($current->isvalid())
		{
			$current->$times_used++;
			$message = 'Sucessfully applied code';
			$is_applied = true;
			if($max_uses>0 && $max_uses == this->times_used)
				deactivate_invite_code($invite_code);
		}

		else {
			$is_applied = false;
			$message = 'Invalid code';
		}

	return apply_filters('ms_model_invite_code_applied_successfully', $is_applied, $message, $membership_type, $invite_code)

}

public function activate_invite_code() {
 	$current=get_by_invite_code($invite_code)
 	$current->$active = true;
}

public function deactivate_invite_code($invite_code) {
 	$current=get_by_invite_code($invite_code)
 	$current->$active = false;
}

public function isvalid($invite_code) {
	$valid = true;
	$this->message = null;
	$args = array(
				'post_type' => self::$POST_TYPE,
				'posts_per_page' => 1,
				'post_status' => 'any',
				'fields' => 'ids',
				'meta_query' => array(
					array(
							'key'     => 'invite_code',
							'value'   => $invite_code,
					),
				)
		);
	$query = new WP_Query( $args );
		if (!$query->has_posts){
			$valid = false;
		}

		if ( empty( $this->invite_code ) || empty( $this->invite_id ) ) {
			$this->message = __( 'Invite code not found.', MS_TEXT_DOMAIN );
			$valid = false;
		}
		if( $this->max_uses && $this->times_used > $this->max_uses ) {
			$this->message = __( 'Invite Code has exceded maximum uses', MS_TEXT_DOMAIN );
			$valid = false;
		}
		$timestamp = MS_Helper_Period::current_time( 'timestamp');
		if( ! empty( $this->start_date ) && strtotime( $this->start_date ) > $timestamp ) {
			$this->message = __( 'This Coupon is not valid yet.', MS_TEXT_DOMAIN );
			$valid = false;
		}
		if( ! empty( $this->expiry_date ) && strtotime( $this->expire_date ) < $timestamp ) {
			$this->message = __( 'This Invite Code has expired.', MS_TEXT_DOMAIN );
			$valid = false;
		}
		if( $this->membership_id !=0 && $membership_type != $this->membership_type ) {
			$this->message = __( 'This Invite Code is not valid for this membership.', MS_TEXT_DOMAIN );
			$valid = false;
		}
		if(! $this->active){
			$valid = false;
		}



		return apply_filters( 'ms_coupon_model_is_valid_coupon', $valid, $membership_id, $this );
		
}

public function __get( $property ) {
	switch( $property ) {
		case 'remaining_uses':
			if( $this->max_uses > 0 ) {
				$value = $this->max_uses - $this->times_used;
			}
			else {
				$value = __( 'Unlimited', MS_TEXT_DOMAIN );
			}
			break;
		default:
			$value = $this->$property;
			break;
	}

	return apply_filters( 'ms_model_coupon__get', $value, $property, $this );

}

public function __set( $property, $value ) {
	if( property_exists( $this, $property ) ) {
		switch( $property ) {
			case 'invite_code':
				
				$this->$property = $value;
				break;
			case 'start_date':
				$this->$property = $this->validate_date( $value );
				break;
			case 'expiry_date':
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
			case 'times_used':
				$this->$property = absint( $value );
				break;
			default:
				$this->$property = $value;
				break;
		}
		}

		do_action( 'ms_model_invite_code_set_after', $property, $value, $this );
}


}



