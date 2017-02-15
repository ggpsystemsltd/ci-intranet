<?php

/**
 * Attendance Model
 *
 * @author Murray Crane <murray.crane@ggpsystems.co.uk>
 * @copyright 2017 (c) GGP Systems Limited
 * @license http://www.gnu.org/licenses/gpl.html
 * @version 2.0
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
		$this->db->select( 'name, work_state' );
		$this->db->where( 'active', 1 );
		$this->db->where( 'work_state !=', 'NaN');
		$this->db->order_by( 'firstname' );
		$query = $this->db->get( 'staff' );
		if( $query->num_rows() > 0 ) {
			foreach( $query->result_array() as $row ) {
				$t_return[] = array(
					'name' => $row[ 'name' ],
					'class' => preg_replace('/\s/', '-', strtolower($row[ 'work_state' ])),
					'attendance' => $row[ 'work_state' ],
				);
			}
		}
		return $t_return;
	}
}

/* End of file Attendance_model.php */
/* Location: application/models/Attendance_model.php */