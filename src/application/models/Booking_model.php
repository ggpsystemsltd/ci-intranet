<?php

/**
 * Machine Booking Model
 *
 * @author Murray Crane <murray.crane@ggpsystems.co.uk>
 * @copyright 2016 (c) GGP Systems Limited
 * @license http://www.gnu.org/licenses/gpl.html
 * @version 2.0
 */
class Booking_model extends CI_Model
{
	function __construct()
	{
		parent::__construct();
	}

	// @todo Booking deletion confirmation is "wrong" - needs to be the new type #dialog
	function get_booking( $p_machine_id )
	{
		$this->db->select( 'staff.firstname, staff.surname, booking.note, booking.duration, booking.start' );
		$this->db->join( 'staff', 'staff.staff_id=booking.staff_id' );
		$this->db->like( 'booking.machine_id', '"' . $p_machine_id . '"' );
		$this->db->where( 'booking.deleted', 0 );
		$t_query = $this->db->get( 'booking' );
		if( $t_query->num_rows() > 0 ) {
			$t_row = $t_query->row_array();
			$t_start = explode( " ", $t_row[ 'start' ] );
			$t_now = date( "Y-m-d" );
			if( $t_now == $t_start[ 0 ] ) {
				$t_row[ 'start' ] = $t_start[ 1 ];
			}
			//$t_return = '<div id="delete-dialog" title="Confirm Deletion"><input type="checkbox" name="confirm" id="confirm-request" value="confirmed">&nbsp; I confirm that I wish to delete the specified booking.</input></div>';
			$t_return = "<span style=\"cursor: url(http://cur.cursors-4u.net/others/oth-3/oth226.cur), crosshair;\" onclick=\"if(confirm('Do you want to delete this booking?')) {window.location='" . base_url( '/machines/debook/' . $p_machine_id . '/' ) . "'};\">" . $t_row[ 'note' ] . "<br/><em>" . strtolower( $t_row[ 'firstname' ] ) . strtolower( substr( $t_row[ 'surname' ], 0, 1 ) ) . " - " . ($t_row[ 'start' ] == "0000-00-00 00:00:00" ? "" : $t_row[ 'start' ] . " for ") . $t_row[ 'duration' ] . "</em></span>";
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
		$this->db->select( 'booking_id' );
		$this->db->where( 'deleted', 0 );
		$this->db->like( 'machine_id', '"' . $p_machine_id . '"' );
		$query = $this->db->get( 'booking' );
		foreach( $query->result() as $row ) {
			$t_booking_id = $row->booking_id;
		}

		$this->db->where( 'booking_id', $t_booking_id );
		$this->db->update( 'booking', array( 'deleted' => 1 ));

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

/* End of file Booking_model.php */
/* Location: application/models/Booking_model.php */