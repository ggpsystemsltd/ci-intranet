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
		$query = $this->db->query( 'SELECT staff.name, holidays.start, holidays.end, holidays.approved FROM `holidays` 
	JOIN `staff` ON holidays.staff_id=staff.staff_id 
	WHERE `confirmed`=1 
	AND (`start` BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 1 week) 
	OR `end` BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 1 week) 
	OR CURDATE() BETWEEN `start` AND `end`) ORDER BY staff.firstname' );
		if( $query->num_rows() > 0 ) {
			foreach( $query->result_array() as $row ) {
				$return[] = array(
					'class' => ( $row[ 'approved' ]==1) ? 'success' : 'danger' ,
					'name' => $row[ 'name' ],
					'dates' => $row[ 'start' ] . " to " . $row[ 'end' ],
				);
			}
		}
		return $return;
	}

	function get_last_update()
	{
		$this->db->select_max( 'updated' )->from( 'holidays' );
		return $this->db->get()->row()->updated;
	}

	function get_holiday( $p_id )
	{
		$this->db->select( 'holiday_id, staff_id, start, end, holiday_type, note, confirmed, approved' )->where( 'holiday_id', $p_id );
		$query = $this->db->get( 'holidays' );
		if( $query->num_rows() > 0 ) {
			foreach( $query->result_array() as $row ) {
				return array(
					'holiday_id' => (int)$row[ 'holiday_id' ],
					'staff_id' => (int)$row[ 'staff_id' ],
					'start' => $row[ 'start' ],
					'end' => $row[ 'end' ],
					'holiday_type' => $row[ 'holiday_type' ],
					'note' => $row[ 'note' ],
					'confirmed' => (int)$row[ 'confirmed' ],
					'approved' => (int)$row[ 'approved' ]
				);
			}
		}
		return false;
	}

	function insert_holiday( $p_request )
	{
		$this->db->insert( 'holidays', $p_request );
		return $this->db->insert_id();
	}

	function update_holiday( $p_id, $p_data )
	{
		$this->db->where( 'holiday_id', $p_id );
		$this->db->update( 'holidays', $p_data );
	}

	function delete_holiday( $p_id )
	{
		$this->db->where( 'holiday_id', $p_id );
		$this->db->delete( 'holidays' );
	}
}