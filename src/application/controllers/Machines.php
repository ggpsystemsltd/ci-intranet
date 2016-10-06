<?php

/**
 * Machine Booking Class
 * 
 * @author Murray Crane <murray.crane@ggpsystems.co.uk>
 * @copyright 2013 (c) GGP Systems Limited
 * @license http://www.gnu.org/licenses/gpl.html
 * @version 1.0
 */
class Machines extends CI_Controller {

	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->helper( 'url' );
		$this->load->library( array( 'grocery_CRUD' ));
	}

	function index() {
		$this->load->helper( array ( 'form', 'security' ));
		$this->load->library( array( 'form_validation', 'ggpclass', 'parser' ));

		$config = array(
			array(
				'field' => 'machines[]',
				'label' => 'Machines list',
				'rules' => 'required'
			),
			array(
				'field' => 'username',
				'label' => 'Name',
				'rules' => 'required|is_natural_no_zero'
			),
			array(
				'field' => 'reason',
				'label' => 'Note',
				'rules' => 'required|trim'
			),
			array(
				'field' => 'duration',
				'label' => 'Estimated Duration',
				'rules' => 'required|trim'
			)
		);
		$this->form_validation->set_message( 'is_natural_no_zero', 'The %s field must not be blank.' );
		$this->form_validation->set_rules( $config );
		if( $this->form_validation->run() == TRUE ) {
			$this->book( $this->input->post( NULL, TRUE ) );
		}
		$type = array(
			'desktop' => array(
				'name' => 'Desktop',
				'colour' => 'darkblue'
			),
			'laptop' => array(
				'name' => 'Laptop',
				'colour' => 'darkcyan'
			),
			'vm' => array(
				'name' => 'VM',
				'colour' => 'darkmagenta'
			),
			'software' => array(
				'name' => 'Software',
				'colour' => 'dimgrey'
			),
			'server' => array(
				'name' => 'Server',
				'colour' => 'ggpgreen'
			),
			'bds' => array(
				'name' => 'Delphi',
				'colour' => 'chocolate'
			)
		);
		$backup_period = array(
			array(
				'name' => 'Weekly',
				'colour' => 'darkgoldenrod'
			),
			array(
				'name' => 'Bi-weekly',
				'colour' => 'goldenrod'
			),
			array(
				'name' => 'Monthly',
				'colour' => 'gold'
			),
			array(
				'name' => 'Quarterly',
				'colour' => 'palegoldenrod'
			)
		);

		$this->db->select( 'machine.machine_id, machine.name, machine.description, machine.os, machine.cpu, machine.ram, machine.diskspace, machine.powered, machine.rdp_sessions, machine.type, machine.location, machine.comment, machine.ipv4_address, machine.mac_address, machine.last_backup, machine.periodicity, machine.bookable' )->from( 'machine' )->where( 'machine.deleted', 0 )->order_by( 'machine.name' );
		$order = "Machine Name Order";
		$atts = array(
			'class' => 'link-mailto',
		);
		$data = array(
			'intranet_title' => 'GGP Systems Ltd intranet',
			'intranet_module' => 'Internal Machine Directory - ' . $order,
			'intranet_user' => filter_input( INPUT_SERVER, 'INTRANET_USER' ),
			'intranet_pass' => filter_input( INPUT_SERVER, 'INTRANET_PASS' ),
			'author_name' => 'Murray Crane',
			'meta_description' => 'Directory of usable computers at GGP Systems Limited.',
			'keywords' => 'computer directory, machine directory',
			'refresh' => '<meta http-equiv="refresh" content="120" />',
			'remote_ip' => filter_input( INPUT_SERVER, 'REMOTE_ADDR', FILTER_VALIDATE_IP ),
		);
		$data[ 'author_mailto' ] = safe_mailto( 'murray.crane@ggpsystems.co.uk', $data[ 'author_name' ], $atts );
		$query = $this->db->get();
		if( $query->num_rows() > 0 ) {
			$i = 1;
			foreach( $query->result_array() as $row ) {
				$this->db->select( 'staff.firstname, staff.surname, booking.note, booking.duration, booking.start' )->from( 'booking' )->join( 'staff', 'staff.staff_id=booking.staff_id' )->like( 'booking.machine_id', '"' . $row[ 'machine_id' ] . '"' )->where( 'booking.deleted', 0 );
				$query2 = $this->db->get();
				if( $query2->num_rows() > 0 ) {
					$row2 = $query2->row_array();
					$start = explode( " ", $row2[ 'start' ] );
					$dateNow = date( "Y-m-d" );
					if( $dateNow == $start[ 0 ] ) {
						$row2[ 'start' ] = $start[ 1 ];
					}
					$row[ 'note' ] = "<span style=\"cursor:crosshair;\" onclick=\"if(confirm('Do you want to delete this booking?')) {window.location='/index.php/machines/debook/" . $row[ 'machine_id' ] . "/'};\">" . $row2[ 'note' ] . "<br/><em>" . strtolower( $row2[ 'firstname' ] ) . strtolower( substr( $row2[ 'surname' ], 0, 1 ) ) . " - " . ($row2[ 'start' ] == "0000-00-00 00:00:00" ? "" : $row2[ 'start' ] . " for ") . $row2[ 'duration' ] . "</em></span>";
				} else {
					$row[ 'note' ] = NULL;
				}
				unset( $query2 );
				unset( $row2 );
				$this->db->select( 'software.name' )->from( 'software' )->where( 'software.machine_id', $row[ 'machine_id' ] );
				$query2 = $this->db->get();
				$software = "";
				$software_array = array();
				if( $query2->num_rows() > 0 ) {
					foreach( $query2->result_array() as $row2 ) {
						$software_array[] = $row2[ 'name' ];
					}
					$software = implode( ", ", $software_array );
				}
				unset( $query2 );
				unset( $row2 );
				$configuration_array = array();
				if( $row[ 'ram' ] != "" ) {
					$configuration_array[] = $row[ 'ram' ];
				}
				if( $row[ 'cpu' ] != "" ) {
					$configuration_array[] = $row[ 'cpu' ];
				}
				if( $row[ 'diskspace' ] != "" ) {
					$configuration_array[] = $row[ 'diskspace' ];
				}
				$configuration = implode( " | ", $configuration_array );
				$description_array = explode( " | ", $row[ 'description' ] );
				$further = implode( " | ", array_slice( $description_array, 2 ) );
				$types_array = explode( ",", $row[ 'type' ] );
				$type_string = "";
				foreach( $types_array as $value ) {
					$type_string .= sprintf( '<span id="sprite" style="float: right;"><img id="%s" src="/assets/images/spritesheet.png" width="0" height="1" title="%s" alt="%s" /></span>', $type[ $value ][ 'colour' ], $type[ $value ][ 'name' ], $type[ $value ][ 'name' ] );
				}
				$data[ 'machine' ][] = array(
					'class' => $i,
					'name' => ($row[ 'powered' ] == '0' ? '<span style="color: red;">' . $row[ 'name' ] . '</span>' : $row[ 'name' ]) . "&nbsp;" . $type_string,
					'os' => $row[ 'os' ],
					'configuration' => ( $configuration != "" ? '<span style="width: 500px;">' . $configuration . '</span>' : ""),
					'description' => $description_array[ 0 ],
					'further' => ($further != "" ? '<span style="width: 500px;">' . $further . '</span>' : ""),
					'backup' => ( $row[ 'last_backup' ] != "0000-00-00" ? $row[ 'last_backup' ] . '&nbsp;' . sprintf( '[%s]', $backup_period[ $row[ 'periodicity' ] ][ 'name' ][ 0 ] ) : "" ),
					'ipv4' => ( $row[ 'ipv4_address' ] != "" ? $row[ 'ipv4_address' ] : "" ),
					'mac' => ( $row[ 'mac_address' ] != "" ? '<span style="width: 500px;">' . $row[ 'mac_address' ] . '</span>' : "" ),
					'software' => $software,
					'booking' => ( $row[ 'bookable' ] == 1 ? ( $row[ 'note' ] != NULL ? $row[ 'note' ] : form_checkbox( 'machines[]', $row[ 'machine_id' ], set_checkbox( 'machines[]', $row[ 'machine_id' ] ) )) : ""),
				);
				($i == 1 ? $i++ : $i--);
			}
		}

		$data[ 'variable_pre' ] = form_open( 'machines' );
		$data[ 'variable' ] = form_fieldset( 'Internal machine booking' );
		$data[ 'variable' ] .= "<div style='color: red;'>" . validation_errors() . "</div>\n";
		$this->db->select( 'staff.staff_id, staff.name, staff.start_date, staff.end_date' )->from( 'staff' )->order_by( 'staff.firstname' )->order_by( 'staff.surname' );
		$ddarray = array( 0 => " " );
		$query = $this->db->get();
		if( $query->num_rows() > 0 ) {
			foreach( $query->result_array() as $row ) {
				if(( $row[ 'start_date' ] == "0000-00-00"
						|| time() >= $this->ggpclass->day_start( $row[ 'start_date' ])) 
					&& ( $row[ 'end_date' ] == "0000-00-00"
						|| time() <= $this->ggpclass->day_end( $row[ 'end_date' ]))) {
					$ddarray[ $row[ 'staff_id' ] ] = $row[ 'name' ];
				}
			}
		}
		$data[ 'variable' ] .= form_label( 'Name:', 'username' );
		$data[ 'variable' ] .= form_dropdown( 'username', $ddarray, $this->input->post( 'username' ) ) . "<br />\n";
		$data[ 'variable' ] .= form_label( 'Note:', 'reason' );
		$data[ 'variable' ] .= form_input( 'reason', xss_clean(set_value( 'reason' ))) . "<br />\n";
		$data[ 'variable' ] .= form_label( 'Starting:', 'start' );
		$data[ 'variable' ] .= "<input name=\"start\" type=\"text\" id=\"start\" />\n";
		$data[ 'variable' ] .= "<a href=\"javascript:NewCal('start','ddMMyyyy', true, 24)\"><img src=\"/assets/js/cal.gif\" alt=\"\" width=\"16\" height=\"16\" /></a><br />\n";
		$data[ 'variable' ] .= form_label( 'Est. duration:', 'duration' );
		$data[ 'variable' ] .= form_input( 'duration', xss_clean(set_value( 'duration' ))) . "<br />\n";
		$data[ 'variable' ] .= form_label( "Add booking:", 'mysubmit' );
		$data[ 'variable' ] .= form_submit( 'mysubmit', ' Confirm ' );
		$data[ 'variable' ] .= form_fieldset_close();
		$data[ 'variable' ] .= form_close();
		$data[ 'variable_post' ] = "";
		$this->parser->parse( 'page_head', $data );
		$this->parser->parse( 'machine_list', $data );
		$this->parser->parse( 'phone_list_form', $data );
		$this->parser->parse( 'page_foot', $data );
	}

	/**
	 * Book one or more machine resources.
	 * 
	 * @param array $post 
	 */
	function book( $post = NULL ) {
		// Process the booking request
		if( !is_null( $post ) ) {
			$machine = $post[ 'machines' ];
			$staff_id = $post[ 'username' ];
			$note = $post[ 'reason' ];
			$duration = $post[ 'duration' ];
			if( $post[ 'duration' ] == "Do not use" ) {
				$datetime = "0000-00-00 00:00:00";
			} elseif( $post[ 'start' ] != '' ) {
				$start = explode( " ", $post[ 'start' ] );
				$date = explode( "-", $start[ 0 ] );
				$time = explode( ":", $start[ 1 ] );
				unset( $start );
				$datetime = date( "Y-m-d H:i:s", mktime( $time[ 0 ], $time[ 1 ], $time[ 2 ], $date[ 1 ], $date[ 0 ], $date[ 2 ] ) );
				unset( $date );
				unset( $time );
			} else {
				$datetime = date( "Y-m-d H:i:s", time() );
			}
			$data = array(
				'staff_id' => $staff_id,
				'note' => $note,
				'duration' => $duration,
				'start' => $datetime,
				'machine_id' => json_encode( $machine ),
			);
			$this->db->insert( 'booking', $data );
			$booking_id = $this->db->insert_id();
			$log_data = array(
				'ip_address' => filter_input(INPUT_SERVER, 'REMOTE_ADDR', FILTER_VALIDATE_IP ),
				'booking_id' => $booking_id,
				'operation' => "add"
			);
			$this->db->insert( 'booking_log', $log_data );
		}
		redirect( current_url() );
	}

	/**
	 * Unbook one or more machine resources
	 * 
	 * @param int $machine_id 
	 */
	function debook( $machine_id = NULL ) {
		// Process a booking hand back
		if( !is_null( $machine_id ) ) {
			$this->db->select( 'booking_id' );
			$this->db->where( 'deleted', 0 );
			$this->db->like( 'machine_id', '"' . $machine_id . '"' );
			$query = $this->db->get( 'booking' );

			foreach( $query->result() as $row ) {
				$booking_id = $row->booking_id;
			}
			$data = array(
				'deleted' => 1
			);
			$this->db->where( 'booking_id', $booking_id );
			$this->db->update( 'booking', $data );
			$log_data = array(
				'ip_address' => filter_input(INPUT_SERVER, 'REMOTE_ADDR', FILTER_VALIDATE_IP ),
				'booking_id' => $booking_id,
				'operation' => "delete"
			);
			$this->db->insert( 'booking_log', $log_data );
		}
		redirect( "/machines" );
	}

	public function bookings() {
		$this->grocery_crud->set_table( 'booking' );
		$this->grocery_crud->set_relation( 'staff_id', 'staff', 'name' );
		$this->grocery_crud->display_as( 'staff_id', 'Employee' );
		$this->grocery_crud->set_subject( 'Booking' );
		$this->grocery_crud->where( 'deleted', 0 );
		$this->grocery_crud->columns( 'staff_id', 'note', 'duration', 'start', 'machine_id' );
		$this->grocery_crud->callback_column( 'machine_id', array( $this, '_callback_machine_id' ) );

		$output = $this->grocery_crud->render();
		$this->crud_output( $output );
	}

	public function crud() {
		$this->grocery_crud->set_table( 'machine' );
		$this->grocery_crud->set_subject( 'Machine' );

		$output = $this->grocery_crud->render();
		$this->crud_output( $output );
	}

	function crud_output( $output = null ) {
		$this->load->view( 'crud_template.php', $output );
	}

	function _callback_machine_id( $value, $row ) {
		unset( $row );
		$machine_ids = json_decode( $value );
		$machines = '';
		foreach( $machine_ids as $machine_id ) {
			$sql = "SELECT m.name FROM machine m WHERE m.machine_id = $machine_id";
			$result = $this->db->query( $sql )->row();
			$machines .= $result->name . ', ';
		}
		return substr( $machines, 0, -2 );
	}

}

/* End of file machines.php */
/* Location: ./system/application/controllers/machines.php */
