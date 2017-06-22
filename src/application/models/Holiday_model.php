<?php

/**
 * Holiday Model
 *
 * @author Murray Crane <murray.crane@ggpsystems.co.uk>
 * @copyright 2017 (c) GGP Systems Limited
 * @license https://tldrlegal.com/license/bsd-3-clause-license-%28revised%29#fulltext
 * @version 2.1
 */
class Holiday_model  extends CI_Model
{
	function __construct()
	{
		parent::__construct();
	}

	function get_holidays_this_week()
	{
		$return = array();
		$query = $this->db->query( 'SELECT holidays.holiday_id, staff.name, holidays.start, holidays.end, holidays.approved FROM `holidays` 
	JOIN `staff` ON holidays.staff_id=staff.staff_id 
	WHERE `confirmed`=1 
	AND (`start` BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 1 week) 
	OR `end` BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 1 week) 
	OR CURDATE() BETWEEN `start` AND `end`)
	ORDER BY holidays.start, staff.firstname' );
		if( $query->num_rows() > 0 ) {
			foreach( $query->result_array() as $row ) {
				$return[] = array(
					'class' => ( $row[ 'approved' ]==1) ? 'success' : 'danger' ,
					'id' => $row[ 'holiday_id' ],
					'name' => $row[ 'name' ],
					'dates' => $row[ 'start' ] . " to " . $row[ 'end' ],
				);
			}
		}
		return $return;
	}

	/**
	 * get_holidays - get the holidays for the current month
	 *
	 * @param $p_interval string The interval of the search
	 * @return array|bool Holiday record(s), false if none
	 */
	public function get_holidays( $p_interval = "1 week" )
	{
		$return = array();
		$t_now = new DateTime();
		$this->db->select( 'holidays.holiday_id, staff.name, holidays.start, holidays.end, holidays.confirmed, holidays.approved' );
		$this->db->join( 'staff', 'holidays.staff_id=staff.staff_id');
		$this->db->where( '`start` BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL ' . $p_interval . ')' );
		$this->db->or_where( '`end` BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL ' . $p_interval . ')' );
		$this->db->or_where( 'NOW() BETWEEN `start` AND `end`' );
		$this->db->order_by( 'holidays.start');
		$query = $this->db->get( 'holidays' );
		if( $query->num_rows() > 0 ) {
			foreach( $query->result_array() as $row ) {
				$t_class = 'success';
				if( $row[ 'confirmed' ] == 0 ) {
					$t_class = 'danger';
				} elseif( $row[ 'approved' ] == 0 ) {
					$t_class = 'warning';
				}
				$t_start = new DateTime( $row[ 'start' ]);
				$t_end = new DateTime( $row[ 'end' ]);
				if( $t_now->getTimestamp() > $t_start->getTimestamp() &&
					$t_now->getTimestamp() < $t_end->getTimestamp() &&
					$t_class == 'success' ) {
					$t_name = '<strong>' . $row[ 'name' ] . '</strong>';
					$t_dates = '<strong>' . $row[ 'start' ] . " to " . $row[ 'end' ] . '</strong>';
				} else {
					$t_name = $row[ 'name' ];
					$t_dates = $row[ 'start' ] . " to " . $row[ 'end' ];
				}
				$return[] = array(
					'class' => $t_class,
					'id' => $row[ 'holiday_id' ],
					'name' => $t_name,
					'dates' => $t_dates,
				);
			}
		} else {
			return false;
		}
		return $return;
	}

	public function is_on_holiday( $p_staff_id )
	{
		$this->db->select( 'holidays.start, holidays.end' );
		$this->db->where( 'holidays.staff_id = ' . $p_staff_id );
		$this->db->where( 'holidays.confirmed = 1' );
		$this->db->where( 'holidays.approved = 1' );
		$this->db->where( 'NOW() BETWEEN `start` AND `end`' );
		$query = $this->db->get( 'holidays' );
		if( $query->num_rows() > 0 ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * get_staff_holidays - get the staff_ids of all staff currently on holiday
	 *
	 * @return array|bool Staff_ID values of staff on holiday, false if none
	 */
	public function get_staff_holidays()
	{
		$return = array();
		$this->db->select( 'staff_id' );
		$this->db->where( 'confirmed', true );
		$this->db->where( 'approved', true );
		$this->db->where( 'start <= CURDATE()' );
		$this->db->where( 'end >= CURDATE()' );
		$query = $this->db->get( 'holidays' );
		if( $query->num_rows() > 0 ) {
			foreach( $query->result_array() as $row ) {
				$return[] = (int)$row[ 'staff_id' ];
			}
		} else {
			return false;
		}
		return $return;
	}

	function get_last_update()
	{
		$this->db->select_max( 'updated' );
		return $this->db->get( 'holidays' )->row()->updated;
	}

	function get_holiday( $p_id )
	{
		$this->db->select( 'holiday_id, staff_id, start, end, holiday_type, note, confirmed, approved' );
		$this->db->where( 'holiday_id', $p_id );
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

	public function ISO_to_UK( $p_date )
	{
		$t_date = new DateTime( $p_date );
		return $t_date->format('d-m-Y');
	}
}

/* End of file Holiday_model.php */
/* Location: application/models/Holiday_model.php */