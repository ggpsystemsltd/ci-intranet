<?php

/**
 * Staff Model
 *
 * @author Murray Crane <murray.crane@ggpsystems.co.uk>
 * @copyright 2016 (c) GGP Systems Limited
 * @license http://www.gnu.org/licenses/gpl.html
 * @version 1.0
 */
class Staff_model extends CI_Model
{
	public function __construct()
	{
		parent::__construct();
	}

	public function get_staff_list()
	{
		$return = array();
		$this->db->select( 'name, firstname, surname' )->where( 'active', 1 )->where( 'work_state !=', 'NaN')->order_by( 'firstname' );
		$query = $this->db->get( 'staff' );
		if( $query->num_rows() > 0 ) {
			foreach( $query->result_array() as $row ) {
				$return[ strtolower( $row[ 'firstname' ] . '.' . $row[ 'surname' ] . '@ggpsystems.co.uk' )] =  $row[ 'name' ];
			}
		}
		return $return;
	}

	public function get_email_by_id( $p_id )
	{
		$this->db->select( 'firstname, surname' )->where( 'active', 1 )->where( 'work_state !=', 'NaN')->where( 'staff_id', $p_id );
		$query = $this->db->get( 'staff' );
		if( $query->num_rows() > 0 ) {
			foreach( $query->result_array() as $row ) {
				return strtolower( $row[ 'firstname' ] . '.' . $row[ 'surname' ] . '@ggpsystems.co.uk' );
			}
		}
		return false;
	}

	public function get_id_by_email( $p_email='' )
	{
		if( !empty( $p_email )) {
			// explode the firstname and surname from the email address
			$t_username = explode( '@', $p_email )[0];
			$t_firstname = explode( '.', $t_username )[0];
			$t_surname = explode( '.', $t_username )[1];

			// select staff_id where firstname and surname
			$this->db->select( 'staff_id' )->where( 'active', 1 )->where( 'work_state !=', 'NaN')->like( 'firstname', $t_firstname )->like( 'surname', $t_surname );
			$query = $this->db->get( 'staff' );
			if( $query->num_rows() > 0 ) {
				foreach ( $query->result_array() as $row ) {
					return (int)$row['staff_id'];
				}
			}
		}
		return false;
	}

	public function get_name_by_id( $p_id='' )
	{
		if( !empty( $p_id )) {
			// select firstname and surname where staff_id
			$this->db->select( 'name' )->where( 'active', 1 )->where( 'work_state !=', 'NaN')->where( 'staff_id', $p_id );
			$query = $this->db->get( 'staff' );
			if( $query->num_rows() > 0 ) {
				foreach ( $query->result_array() as $row ) {
					return $row['name'];
				}
			}
		}
		return false;
	}
}