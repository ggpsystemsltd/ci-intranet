<?php

/**
 * Machine Booking Controller
 * 
 * @author Murray Crane <murray.crane@ggpsystems.co.uk>
 * @copyright 2016 (c) GGP Systems Limited
 * @license http://www.gnu.org/licenses/gpl.html
 * @version 1.3
 */
class Machines extends CI_Controller {

	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->helper( 'url' );
		$this->load->library( array( 'grocery_CRUD' ));
		$this->load->model( array( 'Booking_model', 'Machine_model' ));
	}

	function index() {
		$this->load->helper( array ( 'form', 'security' ));
		$this->load->library( array( 'form_validation', 'ggp_helper', 'parser' ));
		$this->load->model( array( 'Staff_model' ));

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

		$data[ 'machine' ] = $this->Machine_model->get_machine_list();

		$data[ 'variable_pre' ] = form_open( 'machines' );
		$data[ 'variable' ] = form_fieldset( 'Internal machine booking' );
		$data[ 'variable' ] .= "<div style='color: red;'>" . validation_errors() . "</div>\n";
		$ddarray = $this->Staff_model->get_staff_list();
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
	 * @param array $p_post
	 */
	function book($p_post = NULL ) {
		if( !is_null( $p_post ) ) {
			$t_machine = $p_post[ 'machines' ];
			$t_staff_id = $p_post[ 'username' ];
			$t_note = $p_post[ 'reason' ];
			$t_duration = $p_post[ 'duration' ];
			if( $p_post[ 'duration' ] == "Do not use" ) {
				$t_datetime = "0000-00-00 00:00:00";
			} elseif( $p_post[ 'start' ] != '' ) {
				$t_start = explode( " ", $p_post[ 'start' ] );
				$t_date = explode( "-", $t_start[ 0 ] );
				$t_time = explode( ":", $t_start[ 1 ] );
				unset( $t_start );
				$t_datetime = date( "Y-m-d H:i:s", mktime( $t_time[ 0 ], $t_time[ 1 ], $t_time[ 2 ], $t_date[ 1 ], $t_date[ 0 ], $t_date[ 2 ] ) );
				unset( $t_date );
				unset( $t_time );
			} else {
				$t_datetime = date( "Y-m-d H:i:s", time() );
			}
			$t_data = array(
				'staff_id' => $t_staff_id,
				'note' => $t_note,
				'duration' => $t_duration,
				'start' => $t_datetime,
				'machine_id' => json_encode( $t_machine ),
			);
			$this->Booking_model->insert_booking( $t_data );
		}
		redirect( current_url() );
	}

	/**
	 * Unbook one or more machine resources
	 * 
	 * @param int $p_machine_id
	 */
	function debook($p_machine_id = NULL ) {
		if( !is_null( $p_machine_id ) ) {
			$this->Booking_model->update_booking( $p_machine_id );
		}
		redirect( "/Machines" );
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
		$machine_ids = json_decode( $value );
		$machines = '';
		foreach( $machine_ids as  $machine_id ) {
			$machines .= $this->Machine_model->get_name_from_id( $machine_id ) . ', ';
		}
		return substr( $machines, 0, -2 );
	}
}

/* End of file machines.php */
/* Location: ./system/application/controllers/machines.php */
