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
	function __construct()
	{
		parent::__construct();
	}

	function get_staff_list()
	{
		$this->db->select( 'staff.staff_id, staff.name, staff.start_date, staff.end_date' )->from( 'staff' )->where( 'active', 1 )->not_like( 'work_state', 'NaN' )->order_by( 'staff.firstname' )->order_by( 'staff.surname' );
		$t_return = array( 0 => " " );
		$query = $this->db->get();
		if( $query->num_rows() > 0 ) {
			foreach( $query->result_array() as $row ) {
				$t_return[ $row[ 'staff_id' ] ] = $row[ 'name' ];
			}
		}

		return $t_return;
	}
}