<?php

/**
 * Attendance Model
 *
 * @author Murray Crane <murray.crane@ggpsystems.co.uk>
 * @copyright 2017 (c) GGP Systems Limited
 * @license https://tldrlegal.com/license/bsd-3-clause-license-%28revised%29#fulltext
 * @version 2.1
 */
class Attendance_model extends CI_Model
{
	function __construct()
	{
		parent::__construct();
	}

	function get_last_update()
	{
		$this->db->select_max( 'updated' );
		$this->db->where( 'active', 1 );
		$this->db->where( 'work_state !=', 'NaN');
		$this->db->order_by( 'firstname' );
		return $this->db->get( 'staff' )->row()->updated;
	}

	function get_attendance()
	{
		$t_return = array();
		$this->db->select( 'staff_id, name, work_state' );
		$this->db->where( 'active', 1 );
		$this->db->where( 'work_state !=', 'NaN');
		$this->db->order_by( 'firstname' );
		$query = $this->db->get( 'staff' );
		if( $query->num_rows() > 0 ) {
			foreach( $query->result_array() as $row ) {
				$t_return[] = array(
					'id' => $row[ 'staff_id' ],
					'name' => $row[ 'name' ],
					'class' => preg_replace('/\s/', '-', strtolower($row[ 'work_state' ])),
					'attendance' => $row[ 'work_state' ],
				);
			}
		}
		return $t_return;
	}

	public function get_attendance_by_id( $p_staff_id )
	{
		$t_return = false;
		$this->db->select( 'work_state' );
		$this->db->where( 'staff_id', $p_staff_id );
		$this->db->order_by( 'firstname' );
		$query = $this->db->get( 'staff' );
		if( $query->num_rows() > 0 ) {
			foreach( $query->result_array() as $row ) {
				$t_return = $row[ 'work_state' ];
			}
		}
		return $t_return;
	}

	/**
	 * update - update a given staff record field with a given value
	 *
	 * @param $p_staff_id staff_id to update
	 * @param $p_field field to update
	 * @param $p_value value to update field to
	 */
	public function update( $p_staff_id, $p_field, $p_value )
	{
		$t_data = array( $p_field => $p_value );

		$this->db->where( 'staff_id', $p_staff_id );
		$this->db->update( 'staff', $t_data );
	}
}

/* End of file Attendance_model.php */
/* Location: application/models/Attendance_model.php */