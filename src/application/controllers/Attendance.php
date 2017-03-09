<?php

/**
 * Staff Attendance Controller
 *
 * @author Murray Crane <murray.crane@ggpsystems.co.uk>
 * @copyright 2017 (c) GGP Systems Limited
 * @license http://www.gnu.org/licenses/gpl.html
 * @version 2.0
 */
class Attendance extends CI_Controller {

	static $c_head = '<!DOCTYPE HTML>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<title>PHPMailer Email</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
	<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css"/>
    <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
	<style type="text/css">body {width: 640px; font-family: Roboto, sans-serif; font-size: 14px;}</style>
</head>
<body>
	<div class="container">';

	static $c_tail = '	</div> <!-- Container -->
    <!-- JavaScript at the end so the page loads faster -->
    <script src="//code.jquery.com/jquery-1.12.4.js" type="application/javascript"></script>
    <script src="//code.jquery.com/ui/1.12.1/jquery-ui.js" type="application/javascript"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
</body>
</html>';

    function __construct() {
        parent::__construct();

        $this->load->database();
        $this->load->helper( array( 'url' ));
    }

    function index() {
        $this->load->helper( array( 'form' ));
        $this->load->library( array( 'parser' ));
        $this->load->model( array( 'Attendance_model', 'Holiday_model' ));

        $attendance_classes = array(
        	'on-site' => 'success',
			'off-site' => 'active',
			'travelling' => 'active',
			'not-working' => 'danger',
			'vacation' => 'warning',
			'sick' => 'info',
		);

		$t_current_holidays = $this->Holiday_model->get_holidays();
		if( $t_current_holidays ) {
			foreach( $t_current_holidays as $value) {
				$this->Attendance_model->update( $value, 'work_state', 'vacation' );
			}
		}

        $data = array(
            'intranet_heading' => 'Staff Attendance',
			'intranet_secondary' => date( 'd-m-Y' ),
			'intranet_user' => filter_input( INPUT_SERVER, 'INTRANET_USER' ),
			'intranet_pass' => filter_input( INPUT_SERVER, 'INTRANET_PASS' ),
            'author_name' => 'Murray Crane',
            'refresh' => '',
            'meta_description' => 'Staff attendance record.',
            'keywords' => 'staff attendance',
            'remote_ip' => filter_input( INPUT_SERVER, 'REMOTE_ADDR', FILTER_VALIDATE_IP ),
			'author_mailto' => safe_mailto( 'murray.crane@ggpsystems.co.uk', 'Murray Crane' ),
			'style' => '',
			'javascript' => '',
		);

		// Attendance table
		$t_attendance_data[ 'title' ] = '';
        $t_attendance_data[ 'class' ] = 'col-md-4';
        $t_attendance_data[ 'head' ] = array(
        	0 => array( 'column' => 'Name' ),
			1 => array( 'column' => 'Attendance' ),
		);
		$t_attendance = $this->Attendance_model->get_attendance();
		foreach( $t_attendance as $t_staff ) {
			if( $t_staff[ 'class' ] == 'on-site' ) {
				$t_attendance_data[ 'row' ][] = array( 'class' => '', 'column' => array(
					0 => array( 'class' => '', 'value' => $t_staff[ 'name' ]),
					1 => array( 'class' => 'class="' . $attendance_classes[ $t_staff[ 'class' ]] . '"',
						'value' => $t_staff[ 'attendance' ])),
				);
			} else {
				$t_attendance_data[ 'row' ][] = array( 'class' => 'class="hidden-print"', 'column' => array(
					0 => array( 'class' => '', 'value' => $t_staff[ 'name' ]),
					1 => array( 'class' => 'class="' . $attendance_classes[ $t_staff[ 'class' ]] . '"',
						'value' => $t_staff[ 'attendance' ])),
				);
			}
		}

		// Vacations table - array will be empty if no holidays
		$t_display_holidays = true;
		$t_holidays = $this->Holiday_model->get_holidays_this_week();
		if( !empty( $t_holidays )) {
			$t_holidays_data[ 'class' ] = 'col-md-6 hidden-print';
			$t_holidays_data[ 'title' ] = '<h2>Current/upcoming holidays</h2>' . PHP_EOL;
			$t_holidays_data[ 'head' ] = array(
				0 => array( 'column' => 'Name'),
				1 => array( 'column' => 'Dates'),
			);
			foreach( $t_holidays as $t_holiday ) {
				$t_holidays_data[ 'row' ][] = array( 'column' => array(
					0 => array( 'class' => '', 'value' => $t_holiday[ 'name' ]),
					1 => array( 'class' => 'class="' . $t_holiday[ 'class' ] . '"', 'value' => $t_holiday[ 'dates' ])),
				);
			}
		} else {
			$t_display_holidays = false;
		}

		// Last update
		$t_attendance_data[ 'updated' ] = $this->Attendance_model->get_last_update();
		$t_holidays_data[ 'updated' ] = $this->Holiday_model->get_last_update();

        $this->parser->parse( 'header', $data );
		$this->parser->parse( 'row-start', array());
        $this->parser->parse( 'table', $t_attendance_data );
		if( $t_display_holidays ) {
			$this->parser->parse( 'table', $t_holidays_data );
		}
		$this->parser->parse( 'row-stop', array());
        $this->parser->parse( 'footer', $data );
    }

	public function set()
	{
		$this->load->library( 'grocery_CRUD' );

		$this->grocery_crud->set_table( 'staff' );
		$this->grocery_crud->set_subject( 'Attendance' );
		$this->grocery_crud->columns( 'name', 'work_state' );
		$this->grocery_crud->fields( 'name', 'work_state' );
		$this->grocery_crud->display_as( 'work_state', 'Attendance' );
		$this->grocery_crud->where( 'active', 1 );
		$this->grocery_crud->where( 'work_state !=', 'NaN' );
		$this->grocery_crud->order_by( 'firstname', 'asc' );

		$output = $this->grocery_crud->render();
		$this->crud_output( $output );
	}

	private function crud_output( $output = null )
	{
		$this->load->view( 'crud_template.php', $output );
	}
}

/* End of file Attendance.php */
/* Location: application/controllers/Attendance.php */