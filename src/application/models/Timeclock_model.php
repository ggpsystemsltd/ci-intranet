<?php
/**
 * Timeclock Model
 *
 * @author Murray Crane <murray.crane@ggpsystems.co.uk>
 * @copyright 2017 (c) GGP Systems Limited
 * @license http://www.gnu.org/licenses/gpl.html
 * @version 2.3
 */
class Timeclock_model extends CI_Model
{
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * get_data - get timeclock data for specified user over specified period
	 *
	 * @param null $p_user
	 * @param $p_period_start
	 * @param $p_period_end
	 * @return array
	 */
	public function get_data($p_user = null, $p_period_start, $p_period_end)
	{
		$t_return = array();

		if( !is_null( $p_user )) {
			$this->db->select( 'in_out, time_stamp, notes' );
			$this->db->where( 'staff_id', $p_user );
		} else {
			$this->db->select( 'CONCAT(staff.firstname, " ", staff.surname) AS name, in_out, time_stamp, notes' );
			$this->db->join( 'staff', 'touch_times.staff_id=staff.staff_id');
			$this->db->order_by( 'name, time_stamp' );
		}
		$this->db->where( 'time_stamp BETWEEN "' . $p_period_start . '" AND "' . $p_period_end . '"' );
		$query = $this->db->get( 'touch_times' );
		//var_dump($this->db->last_query()); echo "<br/>";
		if( $query->num_rows() > 0 ) {
			$r = array();
			foreach( $query->result_array() as $row ) {
				$t_class = 'success';
				if( $row[ 'in_out' ] == 'out' ) {
					$t_class = 'danger';
				}
				if( !is_null( $p_user )) {
					$r[] = array(
						'class' => $t_class,
						'in_out' => $row[ 'in_out' ],
						'time_stamp' => $row[ 'time_stamp' ],
						'note' => $row[ 'notes' ],
					);
				} else {
					$r[] = array(
						'name' => $row[ 'name' ],
						'class' => $t_class,
						'in_out' => $row[ 'in_out' ],
						'time_stamp' => $row[ 'time_stamp' ],
						'note' => $row[ 'notes' ],
					);
				}
			}
			$t_return = array( "rows" => $r );
		}

		return $t_return;
	}

	/**
	 * get_current_data - get current timeclock data from the table (most recent "punch")
	 *
	 * @return	array	Timeclock data from table
	 */
	public function get_current_data()
	{
		$return = array();
		$this->db->select( 'staff.name, touch_times.in_out, touch_times.time_stamp, touch_times.notes' );
		$this->db->join( 'staff', 'touch_times.staff_id=staff.staff_id' );
		$this->db->where( '`touch_id` IN (SELECT MAX(`touch_id`) FROM `touch_times` GROUP BY `staff_id`)' );
		$query = $this->db->get( 'touch_times' );
		//var_dump($this->db->last_query()); echo "<br/>";
		if( $query->num_rows() > 0 ) {
			foreach( $query->result_array() as $row ) {
				$t_class = 'success';
				if( $row[ 'in_out' ] == 'out' ) {
					$t_class = 'danger';
				}
				$return[] = array(
					'class' => $t_class,
					'name' => $row[ 'name' ],
					'in_out' => $row[ 'in_out' ],
					'time_stamp' => $row[ 'time_stamp' ],
					'note' => $row[ 'notes' ],
				);
			}
		}
		return $return;
	}

	public function last_updated()
	{
		$this->db->select_max( 'updated' );
		return $this->db->get( 'touch_times' )->row()->updated;
	}



	public function insert( $p_punch_data )
	{
		$this->db->insert( 'touch_times', $p_punch_data );
		return $this->db->insert_id();
	}
}