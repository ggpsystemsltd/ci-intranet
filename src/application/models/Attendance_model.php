<?php

class Attendance_model extends CI_Model
{
	function __construct()
	{
		parent::__construct();
	}

	function get_last_update()
	{
		$this->db->select_max( 'updated' )->from( 'staff' )->where( 'staff.active', 1 )->where( 'staff.work_state !=', 'NaN')->order_by( 'staff.firstname' );
		return $this->db->get()->row()->updated;
	}

	function get_attendance()
	{
		$return = array();
		$this->db->select( 'staff.name, staff.work_state' )->from( 'staff' )->where( 'staff.active', 1 )->where( 'staff.work_state !=', 'NaN')->order_by( 'staff.firstname' );
		$query = $this->db->get();
		if( $query->num_rows() > 0 ) {
			foreach( $query->result_array() as $row ) {
				$return[] = array(
					'name' => $row[ 'name' ],
					'class' => preg_replace('/\s/', '-', strtolower($row[ 'work_state' ])),
					'attendance' => $row[ 'work_state' ],
				);
			}
		}
		return $return;
	}
}