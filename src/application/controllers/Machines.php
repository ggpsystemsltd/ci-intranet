<?php

/**
 * Machine Booking Controller
 * 
 * @author Murray Crane <murray.crane@ggpsystems.co.uk>
 * @copyright 2017 (c) GGP Systems Limited
 * @license https://tldrlegal.com/license/bsd-3-clause-license-%28revised%29#fulltext
 * @version 2.1
 */
class Machines extends CI_Controller {

	public function __construct() {
		parent::__construct();

		$this->load->database();
	}

	public function index() {
		$this->load->helper( array ( 'form', 'security', 'url' ));
		$this->load->library( array( 'parser' ));
		$this->load->model( array( 'Booking_model', 'Machine_model', 'Staff_model' ));

		$data = array(
			'intranet_heading' => 'Internal Machine Directory',
			'intranet_secondary' => date( 'd-m-Y' ),
			'intranet_user' => filter_input( INPUT_SERVER, 'INTRANET_USER' ),
			'intranet_pass' => filter_input( INPUT_SERVER, 'INTRANET_PASS' ),
			'author_name' => 'Murray Crane',
			'meta_description' => 'Directory of usable computers at GGP Systems Limited.',
			'keywords' => 'computer directory, machine directory',
			'refresh' => '<meta http-equiv="refresh" content="120" />',
			'remote_ip' => filter_input( INPUT_SERVER, 'REMOTE_ADDR', FILTER_VALIDATE_IP ),
			'author_mailto' => safe_mailto( 'murray.crane@ggpsystems.co.uk', 'Murray Crane' ),
			'style' => '<link rel="stylesheet" media="screen" href="' . base_url( '/assets/style/jquery-ui-timepicker-addon.css' ) . '" type="text/css" />
	<link rel="stylesheet" media="screen" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap3-dialog/1.34.7/css/bootstrap-dialog.min.css" type="text/css" />',
			'javascript' => '<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap3-dialog/1.34.7/js/bootstrap-dialog.min.js" type="text/javascript"></script>
	<script src="' . base_url( '/assets/js/jquery-ui-timepicker-addon.js' ) . '" type="text/javascript"></script>
	<script src="' . base_url( '/assets/js/machines.js' ) . '" type="text/javascript"></script>',
		);

		$t_nav_data = array(
			'base_url' => base_url(),
			'attendance_active' => '',
			'attendance_active_span' => '',
			'dllog_active' => '',
			'dllog_active_span' => '',
			'holidays_active' => '',
			'holidays_active_span' => '',
			'intranet_active' => '',
			'intranet_active_span' => '',
			'machines_active' => ' class="active"',
			'machines_active_span' => '<span class="sr-only">(current)</span>',
			'wol_active' => '',
			'wol_active_span' => '',
		);

		$t_body_data['data'] = '			<form action="' . base_url( '/machines/book' ) . '" method="post" id="machine-form" class="form-horizontal" accept-charset="utf-8">';

		// Machine table
		$t_table_data[ 'title' ] = '';
		$t_table_data[ 'class' ] = 'col-md-12';
		$t_table_data[ 'head' ] = array(
			0 => array( 'class' => '', 'column' => 'Name' ),
			1 => array( 'class' => '', 'column' => 'Last Backup' ),
			2 => array( 'class' => '', 'column' => 'Operating System' ),
			3 => array( 'class' => '', 'column' => 'Description' ),
			4 => array( 'class' => '', 'column' => 'IP Address' ),
			5 => array( 'class' => '', 'column' => 'Third Party Software' ),
			6 => array( 'class' => '', 'column' => 'Booking' ),
		);
		$t_machines = $this->Machine_model->get_machine_list();
		foreach( $t_machines as $t_machine ) {
			$t_table_data[ 'row' ][] = array( 'class' => '', 'column' => array(
				0 => array( 'class' => $t_machine[ 'class' ], 'value' => $t_machine[ 'name' ]),
				1 => array( 'class' => '', 'value' => $t_machine[ 'backup' ]),
				2 => array( 'class' => '', 'value' => $t_machine[ 'os' ]),
				3 => array( 'class' => '', 'value' => $t_machine[ 'description' ]),
				4 => array( 'class' => '', 'value' => $t_machine[ 'ipv4' ]),
				5 => array( 'class' => '', 'value' => $t_machine[ 'software' ]),
				6 => array( 'class' => '', 'value' => $t_machine[ 'booking' ]),
			),
			);
		}
		$t_table_data[ 'updated' ] = date( 'y-m-d H:i:s' );

		// Booking form
		$t_users = $this->Staff_model->get_staff_list();
		$t_form_data[ 'variable' ] = '		<div id="legend" class="btn btn-default clearfix">Book resources</div>
		<div class="form-content row" style="display: none;">
				<div class="form-group">
					<label class="col-sm-2 control-label">Select user</label>
					<div class="col-sm-4">' . PHP_EOL;
		$t_form_data[ 'variable' ] .= form_dropdown( 'user', $t_users, '', array( 'id' => 'name', 'class' => 'form-control' ));
		$t_form_data[ 'variable' ] .= '					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label">Note</label>
					<div class="col-sm-4">
						<input type="text" id="note" name="note" class="form-control" placeholder="Explanatory note">
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label">Start</label>
					<div class="col-sm-4 input-group" style="padding-left: 15px; padding-right: 15px;">
						<input type="text" id="start-date" name="start_date" class="form-control datetime-picker" placeholder="Start date">
						<span class="input-group-addon add-on"><span class="glyphicon glyphicon-calendar"></span></span>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label">Duration</label>
					<div class="col-sm-4">
						<input type="text" id="duration" name="duration" class="form-control" placeholder="Estimated duration">
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-4 col-sm-offset-2">
						<button type="button" id="submit-btn" name="save" class="btn btn-success">Submit</button>
						<button type="reset" id="cancel-btn" name="cancel" class="btn btn-danger">Cancel</button>
					</div>
				</div>
			</form>
		</div>' . PHP_EOL;
		$t_form_data[ 'variable_post' ] = '';

		$this->parser->parse( 'header', $data );
		$this->parser->parse( 'navbar', $t_nav_data );
		$this->parser->parse( 'heading', $data );
		$this->parser->parse( 'inject', $t_body_data );
		$this->parser->parse( 'row-start', array() );
		$this->parser->parse( 'table', $t_table_data );
		$this->parser->parse( 'row-stop', array() );
		$this->parser->parse( 'form', $t_form_data );
		$this->parser->parse( 'footer', $data );
	}

	/**
	 * Book one or more machine resources.
	 */
	public function book() {
		$this->load->helper( array ( 'url' ));
		$this->load->model( array( 'Booking_model', 'Staff_model' ));

		$t_booking_data = array(
			'staff_id' => $this->Staff_model->get_id_by_email( $this->input->post( 'user', true )),
			'note' => $this->input->post( 'note', true ),
			'duration' => $this->input->post( 'duration', true ),
			'start' => ( $this->input->post( 'duration', true ) != "Do not use" ) ?
				$this->input->post( 'start_date', true ) : '0000-00-00 00:00:00',
			'machine_id' => json_encode( $this->input->post( 'machines', true )),
		);
		$this->Booking_model->insert_booking( $t_booking_data );
		redirect( base_url( '/'. explode( '/', uri_string() )[0] ));
	}

	/**
	 * Unbook one or more machine resources
	 * 
	 * @param int $p_machine_id
	 */
	public function debook($p_machine_id = NULL ) {
		$this->load->helper( array ( 'url' ));
		$this->load->model( array( 'Booking_model' ));

		if( !is_null( $p_machine_id ) ) {
			$this->Booking_model->update_booking( $p_machine_id );
		}
		redirect( base_url( '/'. explode( '/', uri_string() )[0] ));
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

	public function set() {
		$this->load->library( array( 'grocery_CRUD' ));

		$this->grocery_crud->set_table( 'machine' );
		$this->grocery_crud->set_subject( 'Machine' );

		$output = $this->grocery_crud->render();
		$this->crud_output( $output );
	}

	private function crud_output( $output = null ) {
		$this->load->view( 'crud_template.php', $output );
	}

	private function _callback_machine_id( $value, $row ) {
		$machine_ids = json_decode( $value );
		$machines = '';
		foreach( $machine_ids as  $machine_id ) {
			$machines .= $this->Machine_model->get_name_from_id( $machine_id ) . ', ';
		}
		return substr( $machines, 0, -2 );
	}
}

/* End of file Machines.php */
/* Location: ./application/controllers/Machines.php */