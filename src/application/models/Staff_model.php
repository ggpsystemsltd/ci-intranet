<?php

/**
 * Staff Model
 *
 * @author Murray Crane <murray.crane@ggpsystems.co.uk>
 * @copyright 2017 (c) GGP Systems Limited
 * @license https://tldrlegal.com/license/bsd-3-clause-license-%28revised%29#fulltext
 * @version 2.1
 */
class Staff_model extends CI_Model
{
	public function __construct()
	{
		parent::__construct();
	}

	public function get_staff_list()
	{
		$t_return = array();
		$this->db->select( 'name, firstname, surname' );
		$this->db->where( 'active', 1 );
		$this->db->where( 'work_state !=', 'NaN');
		$this->db->order_by( 'firstname' );
		$query = $this->db->get( 'staff' );
		if( $query->num_rows() > 0 ) {
			foreach( $query->result_array() as $row ) {
				$t_return[ strtolower( $row[ 'firstname' ] . '.' . $row[ 'surname' ] . '@ggpsystems.co.uk' )] =  $row[ 'name' ];
			}
		}
		return $t_return;
	}

	public function get_email_by_id( $p_id )
	{
		$this->db->select( 'firstname, surname' );
		$this->db->where( 'active', 1 );
		$this->db->where( 'work_state !=', 'NaN');
		$this->db->where( 'staff_id', $p_id );
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
			$this->db->select( 'staff_id' );
			$this->db->where( 'active', 1 );
			$this->db->where( 'work_state !=', 'NaN');
			$this->db->like( 'firstname', $t_firstname );
			$this->db->like( 'surname', $t_surname );
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
			$this->db->select( 'name' );
			$this->db->where( 'active', 1 );
			$this->db->where( 'work_state !=', 'NaN');
			$this->db->where( 'staff_id', $p_id );
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

/* End of file Staff_model.php */
/* Location: application/models/Staff_model.php */