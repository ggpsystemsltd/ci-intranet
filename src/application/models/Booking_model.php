<?php

/**
 * Machine Booking Model
 *
 * @author Murray Crane <murray.crane@ggpsystems.co.uk>
 * @copyright 2016 (c) GGP Systems Limited
 * @license http://www.gnu.org/licenses/gpl.html
 * @version 1.0
 */
class Booking_model extends CI_Model
{
	function __construct()
	{
		parent::__construct();
	}

	function get_booking( $p_machine_id )
	{
		$this->db->select( 'staff.firstname, staff.surname, booking.note, booking.duration, booking.start' )->from( 'booking' )->join( 'staff', 'staff.staff_id=booking.staff_id' )->like( 'booking.machine_id', '"' . $p_machine_id . '"' )->where( 'booking.deleted', 0 );
		$t_query = $this->db->get();
		if( $t_query->num_rows() > 0 ) {
			$t_row = $t_query->row_array();
			$t_start = explode( " ", $t_row[ 'start' ] );
			$t_now = date( "Y-m-d" );
			if( $t_now == $t_start[ 0 ] ) {
				$t_row[ 'start' ] = $t_start[ 1 ];
			}
			$t_return = "<span style=\"cursor:crosshair;\" onclick=\"if(confirm('Do you want to delete this booking?')) {window.location='/Machines/debook/" . $p_machine_id . "/'};\">" . $t_row[ 'note' ] . "<br/><em>" . strtolower( $t_row[ 'firstname' ] ) . strtolower( substr( $t_row[ 'surname' ], 0, 1 ) ) . " - " . ($t_row[ 'start' ] == "0000-00-00 00:00:00" ? "" : $t_row[ 'start' ] . " for ") . $t_row[ 'duration' ] . "</em></span>";
		} else {
			$t_return = NULL;
		}

		return $t_return;
	}

	function insert_booking( $p_booking_data )
	{
		$this->db->insert( 'booking', $p_booking_data );
		$t_booking_id = $this->db->insert_id();

		$t_log_data = array(
			'ip_address' => filter_input(INPUT_SERVER, 'REMOTE_ADDR', FILTER_VALIDATE_IP ),
			'booking_id' => $t_booking_id,
			'operation' => "add"
		);
		$this->log_booking( $t_log_data );
	}

	function update_booking( $p_machine_id )
	{
		$this->db->select( 'booking_id' )->where( 'deleted', 0 )->like( 'machine_id', '"' . $p_machine_id . '"' );
		$query = $this->db->get( 'booking' );

		foreach( $query->result() as $row ) {
			$t_booking_id = $row->booking_id;
		}
		$t_data = array(
			'deleted' => 1
		);
		$this->db->where( 'booking_id', $t_booking_id );
		$this->db->update( 'booking', $t_data );

		$t_log_data = array(
			'ip_address' => filter_input(INPUT_SERVER, 'REMOTE_ADDR', FILTER_VALIDATE_IP ),
			'booking_id' => $t_booking_id,
			'operation' => "delete"
		);
		$this->log_booking( $t_log_data );
	}

	function log_booking( $log_data )
	{
		$this->db->insert( 'booking_log', $log_data );
	}
}