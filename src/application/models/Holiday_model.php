<?php
class Holiday_model  extends CI_Model
{
	function __construct()
	{
		parent::__construct();
	}

	function get_holidays_this_week()
	{
		$return = array();
		$query = $this->db->query( 'SELECT staff.name, holidays.start, holidays.end FROM `holidays` 
	JOIN `staff` ON holidays.staff_id=staff.staff_id 
	WHERE `approved`=1 
	AND `start` BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 1 week) 
	OR `end` BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 1 week) 
	OR CURDATE() BETWEEN `start` AND `end` ORDER BY staff.firstname' );
		if( $query->num_rows() > 0 ) {
			$i = 1;
			foreach( $query->result_array() as $row ) {
				$return[] = array(
					'class' => $i,
					'name' => $row[ 'name' ],
					'dates' => $row[ 'start' ] . " to " . $row[ 'end' ],
				);
				($i == 1 ? $i++ : $i--);
			}
		}
		return $return;
	}

	function get_last_update()
	{
		$this->db->select_max( 'updated' )->from( 'staff' )->where( 'staff.active', 1 )->where( 'staff.work_state !=', 'NaN')->order_by( 'staff.firstname' );
		$query = $this->db->get();
		if( $query->num_rows() > 0 ) {
			foreach( $query->result_array() as $row ) {
				return array(
					'updated' => $row[ 'updated' ],
				);
			}
		}
	}
}