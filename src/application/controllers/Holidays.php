<?php

/**
 * Staff Holidays Controller
 *
 * @author Murray Crane <murray.crane@ggpsystems.co.uk>
 * @copyright 2017 (c) GGP Systems Limited
 * @license http://www.gnu.org/licenses/gpl.html
 * @version 2.0
 */
class Holidays extends CI_Controller
{
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
		$this->load->model( array( 'Holiday_model', 'Nonce_model', 'Staff_model' ));

		$data = array(
			'intranet_title' => 'Staff Holidays (GGP intranet)',
			'intranet_heading' => 'Staff Holidays',
			'intranet_secondary' => date( 'd-m-Y' ),
			'intranet_user' => filter_input( INPUT_SERVER, 'INTRANET_USER' ),
			'intranet_pass' => filter_input( INPUT_SERVER, 'INTRANET_PASS' ),
			'author_name' => 'Murray Crane',
			'refresh' => '',
			'meta_description' => 'Staff holiday record and holiday request form.',
			'keywords' => 'staff holidays requests',
			'remote_ip' => filter_input( INPUT_SERVER, 'REMOTE_ADDR', FILTER_VALIDATE_IP ),
			'author_mailto' => safe_mailto( 'murray.crane@ggpsystems.co.uk', 'Murray Crane',
				array( 'class' => 'link-mailto' )),
			'style' => '',
			'javascript' => '<script src="' . base_url( '/assets/js/holidays.js' ) . '" type="text/javascript"></script>',
		);

		// Vacations table - array will be empty if no holidays
		$t_holidays = $this->Holiday_model->get_holidays_this_week();
		$t_holidays_data[ 'title' ] = '';
		$t_holidays_data[ 'class' ] = 'col-md-6';
		$t_holidays_data[ 'head' ] = array(
			0 => array( 'column' => 'Name'),
			1 => array( 'column' => 'Dates'),
		);
		if( empty( $t_holidays )) {
			$t_holidays_data[ 'row' ][] = array( 'class' => '', 'column' => array(
				0 => array( 'class' => '', 'value' => ''),
				1 => array( 'class' => '', 'value' => '')),
			);
		} else {
			foreach( $t_holidays as $t_holiday ) {
				$t_holidays_data[ 'row' ][] = array( 'class' => '', 'column' => array(
					0 => array( 'class' => '', 'value' => $t_holiday[ 'name' ]),
					1 => array( 'class' => 'class="' . $t_holiday[ 'class' ] . '"', 'value' => $t_holiday[ 'dates' ])),
				);
			}
		}

		// Last update
		$t_holidays_data[ 'updated' ] = $this->Holiday_model->get_last_update();

		// Form data
		$t_users = $this->Staff_model->get_staff_list();
		$t_form_data[ 'variable' ] = '		<div id="legend" class="btn btn-default clearfix">Request a holiday</div>
		<div class="form-content row" style="display: none;">
			<form action="' . base_url( '/holidays/request' ) . '" method="post" id="holiday-form" class="form-horizontal" accept-charset="utf-8">
				<div class="form-group">
					<label class="col-sm-2 control-label">Select user</label>
					<div class="col-sm-4">' .PHP_EOL;
		$t_form_data[ 'variable' ] .= form_dropdown( 'user', $t_users, '', array( 'id' => 'name', 'class' => 'form-control' ));
		$t_form_data[ 'variable' ] .= '					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label">Start</label>
					<div class="col-sm-4 input-group" style="padding-left: 15px; padding-right: 15px;">
						<input type="text" id="start-date" name="start_date" class="form-control date-picker" placeholder="Start date">
						<span class="input-group-addon add-on"><span class="glyphicon glyphicon-calendar"></span></span>
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-4 col-sm-offset-2 ">
						<label class="radio-inline">
							<input type="radio" name="start_type" id="start-am" value="am"> Half day AM
						</label>
						<label class="radio-inline">
							<input type="radio" name="start_type" id="start-pm" value="pm"> Half day PM
						</label>
						<label class="radio-inline">
							<input type="radio" name="start_type" id="start-full" value="full"> Full day
						</label>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label">End</label>
					<div class="col-sm-4 input-group" style="padding-left: 15px; padding-right: 15px;">
						<input type="text" id="end-date" name="end_date" class="form-control date-picker" placeholder="End date">
						<span class="input-group-addon add-on"><span class="glyphicon glyphicon-calendar"></span></span>
					</div>
					<span id="end-help-block" class="col-sm-5 col-sm-offset-2 help-block" style="display: none;">Change the end date only if you are requesting a multi-day holiday.</span>
				</div>
				<div class="form-group">
					<div class="col-sm-4 col-sm-offset-2 " id="end-type">
						<label class="radio-inline">
							<input type="radio" name="end_type" id="end-am" value="am"> Half day AM
						</label>
						<label class="radio-inline">
							<input type="radio" name="end_type" id="end-full" value="full"> Full day
						</label>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label">Note</label>
					<div class="col-sm-4">
						<input type="text" id="note" name="note" class="form-control" placeholder="Explanatory note">
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-4 col-sm-offset-2">
						<button type="button" id="submit-btn" name="save" class="btn btn-default">Submit</button>
						<button type="reset" id="cancel-btn" name="cancel" class="btn btn-danger">Cancel</button>
					</div>
				</div>
			</form>
			<div id="dialog" title="Confirm Request"><input type="checkbox" name="confirm" id="confirm-request" value="confirmed">&nbsp; I confirm that I am the user selected and that I wish to request the specified holiday.</input></div>
		</div>' . PHP_EOL;
		$t_form_data[ 'variable_post' ] = '';

		$this->parser->parse( 'header', $data );
		$this->parser->parse( 'row-start', array() );
		$this->parser->parse( 'table', $t_holidays_data );
		$this->parser->parse( 'row-stop', array() );
		$this->parser->parse( 'form', $t_form_data );
		$this->parser->parse( 'footer', $data );
	}

	public function request()
	{
		$this->load->model( array( 'Staff_model' ));

		// Possible post values: user (email address), start_date/end_date (date), start_type/end_type (am/pm/full), note (text)
		$t_request = array();
		$t_request[ 'email' ] = strtolower( $this->input->post( 'user', true ));
		$t_request[ 'staff_id' ] = $this->Staff_model->get_id_by_email( $this->input->post( 'user', true ));
		$t_request[ 'start' ] = $this->input->post( 'start_date', true );
		$t_request[ 'end' ] = $this->input->post( 'end_date', true );
		$t_start_type = $this->input->post( 'start_type', true );
		$t_end_type = $this->input->post( 'end_type', true );
		if( is_null( $t_request[ 'end' ])) {
			$t_request[ 'end' ] = $t_request[ 'start' ];
		}
		if( $t_request[ 'start' ] == $t_request['end'] ) {
			if( !is_null( $t_start_type )) {
				switch ( $t_start_type ) {
					case 'am':
						$t_request['holiday_type'] = "Half Day (AM)";
						$t_end_type = 'am';
						break;
					case 'pm':
						$t_request['holiday_type'] = "Half Day (PM)";
						$t_end_type = 'full';
						break;
					case 'full':
						$t_request['holiday_type'] = "Single Day";
						$t_end_type = 'full';
						break;
				}
			}
		} else {
			$t_request['holiday_type'] = "Multiple Days";
		}
		if( !is_null( $t_start_type )) {
			switch( $t_start_type ) {
				case 'am':
				case 'full':
					$t_request[ 'start' ] .= " 09:00:00";
					break;
				case 'pm':
					$t_request[ 'start' ] .= " 13:00:00";
					break;
			}
		}
		if( !is_null( $t_end_type )) {
			switch( $t_end_type ) {
				case 'am':
					$t_request[ 'end' ] .= " 13:00:00";
					break;
				case 'full':
					$t_request[ 'end' ] .= " 17:30:00";
					break;
			}
		}
		$t_request[ 'note' ] = $this->input->post( 'note', true );
		if( is_null( $t_request[ 'note' ])) {
			$t_request[ 'note' ] = '';
		}

		// Do stuff with the POSTed values. Send a confirmation email to the requesting user.
		$this->send_confirmation_email( $t_request );
		$t_uri = '/'. explode( '/', uri_string() )[0];
		redirect( $t_uri );
	}

	public function confirm_holiday_request()
	{
		$this->load->model( array( 'Holiday_model', 'Nonce_model', 'Staff_model' ));

		$t_action = $this->uri->segment( 3, 0 );
		$t_request_id = (int)explode( '-', $this->uri->segment( 4, 0 ))[0];
		$t_cnonce = explode( '-', $this->uri->segment( 4,0 ))[1];

		if( $t_action != "cancel" ) {
			$t_request = $this->Holiday_model->get_holiday( $t_request_id );
			$t_holiday_data = array(
				'email' => $this->Staff_model->get_email_by_id( $t_request[ 'staff_id' ]),
				'staff_id' => $t_request[ 'staff_id' ],
				'start' => $t_request[ 'start' ],
				'end' => $t_request[ 'end' ],
				'holiday_type' => $t_request[ 'holiday_type' ],
				'note' => $t_request[ 'note' ]);
			if( !$this->Nonce_model->verify_crc32( $t_request_id, $t_cnonce, serialize( $t_holiday_data ))) {
				echo Holidays::$c_head;
				echo '<h1>Request unverified</h1><p>The holiday request could not be verified. Please start again. Close this tab/window if you don\'t need it.</p>';
				echo Holidays::$c_tail;
				die();
			}
		} else {
			$this->Holiday_model->delete_holiday( $t_request_id );
			echo Holidays::$c_head;
			echo '<h1>Request deleted</h1><p>The holiday request has been deleted. Close this tab/window if you don\'t need it.</p>';
			echo Holidays::$c_tail;
			die();
		}

		// Request confirmed and verified. UPDATE the holiday record.
		$t_request[ 'confirmed' ] = 1;
		$this->Holiday_model->update_holiday( $t_request_id, array( 'confirmed' => 1 ));

		// Send approval email.
		$this->send_approval_email( $t_request );
		echo Holidays::$c_head;
		echo '<h1>Request confirmed</h1><p>An email has been sent to <strong>Prim Maxwell</strong> with your holiday request. Close this tab/window if you don\'t need it.</p>';
		echo Holidays::$c_tail;
		die();
	}

	public function approve_holiday_request()
	{
		$this->load->model( array( 'Holiday_model', 'Nonce_model' ));

		$t_action = $this->uri->segment( 3, 0 );
		$t_holiday_id = (int)explode( '-', $this->uri->segment( 4, 0 ))[0];
		$t_cnonce = explode( '-', $this->uri->segment( 4,0 ))[1];
		$t_holiday = $this->Holiday_model->get_holiday( $t_holiday_id );

		// Verify nonce (we do nothing if it's not good...)
		if( $this->Nonce_model->verify_crc32( $t_holiday_id, $t_cnonce, serialize( $t_holiday ))) {
			if ($t_action == "approve") {
				// Approve
				// Update the holiday
				$this->Holiday_model->update_holiday( $t_holiday_id, array( 'approved' => 1 ));
				// Send an email to the requester
				$this->send_approved_email( $t_holiday );
				echo Holidays::$c_head;
				echo '<h1>Request approved</h1><p>The holiday has been approved. Close this tab/window if you don\'t need it.</p>';
				echo Holidays::$c_tail;
				die();
			} elseif ($t_action == "deny") {
				// Deny - Possible known post values: note
				$p_note = $this->input->post( 'note', TRUE );

				if( empty( $p_note )) {
					// Collect a reason - wrap in an "if", we get a POST value back from the encapsulated form
					echo Holidays::$c_head;
					echo '<div id="dialog" title="Deny request">' . PHP_EOL;
					echo '<form  action="' . base_url('/' . $this->uri->uri_string() ) . '" method="POST" id="deny-form">' . PHP_EOL;
					echo '<input type="checkbox" name="confirm" id="confirm-request" value="confirmed">&nbsp; I am denying the requested holiday.</input><br/><br/>' . PHP_EOL;
					echo '<label for="deny-note">Note &nbsp;</label><input type="text" name="note" id="deny-note"/>' . PHP_EOL;
					echo '</form>' . PHP_EOL;
					echo '</div>' . PHP_EOL;
					echo '<script>
	$( function() {
		// confirmation dialog
		$( "#dialog" ).dialog({
			buttons: [
				{
					text: "Deny",
					icons: {
						primary: "ui-icon-alert"
					},
					click: function() {
						if ($("#confirm-request").is(":checked")) {
							$(this).dialog("close");
							$("#deny-form").submit(); //submit the encapsulated form
						} else {
							$("#dialog").effect("shake");
						}
					}
				}
			],
			modal: true
		});
	});
</script>' .PHP_EOL;
					echo Holidays::$c_tail;
				} elseif( $p_note[ 'confirm' ] === 'confirmed' ) {
					$t_note = '';
					if( !empty( $p_note[ 'note' ])) {
						$t_note = $p_note[ 'note' ];
					}
					// Delete the holiday
					//$this->Holiday_model->delete_holiday( $t_holiday_id );
					// Send an email to the requester
					$this->send_denied_email( $t_holiday, $t_note );
					echo Holidays::$c_head;
					echo '<h1>Request denied</h1><p>The holiday has been denied. Close this tab/window if you don\'t need it.</p>';
					echo Holidays::$c_tail;
					die();
				}
			} else {
				// Log naughtiness
				echo "Unrecognised action!";
				die();
			}
		} else {
			// Log naughtiness
			echo "Nonce did not verify!";
			die();
		}
	}

	private function send_confirmation_email( $p_holiday )
	{
		$this->load->model( array( 'Holiday_model', 'Nonce_model' ));

		if( !empty( $p_holiday )) {
			$t_holiday_data = array( 'staff_id' => $p_holiday[ 'staff_id' ],
				'start' => $p_holiday[ 'start' ],
				'end' => $p_holiday[ 'end' ],
				'holiday_type' => $p_holiday[ 'holiday_type' ],
				'note' => $p_holiday[ 'note' ],
				'confirmed' => 0,
				'approved' => 0,
				'nonce' => '');
			$t_request_id = $this->Holiday_model->insert_holiday( $t_holiday_data );

			$t_serialized = serialize( $p_holiday );
			$t_nonce = $this->Nonce_model->get_crc32( $t_request_id, $t_serialized, true );
			$t_controller_url = site_url() . explode( '/', uri_string() )[0] . '/confirm_holiday_request';
			$t_confirm_url = $t_controller_url . '/confirm/' . $t_request_id . '-' . $t_nonce;
			$t_cancel_url = $t_controller_url . '/cancel/' . $t_request_id . '-' . $t_nonce;

			// Create the email configuration
			$t_email_config[ 'to' ] = $p_holiday[ 'email' ];
			$t_email_config[ 'subject' ] = 'Please confirm your holiday request';
			$t_email_config[ 'message' ] = Holidays::$c_head . PHP_EOL . '<div class="row"><p>The following holiday request has been made in your name: <strong>'. $p_holiday[ 'holiday_type' ] . '</strong> on <strong>';
			switch( $p_holiday[ 'holiday_type' ] ) {
				case "Multiple Days":
					$t_email_config[ 'message' ] .= explode( ' ', $p_holiday[ 'start' ] )[0] . ' to ' . explode( ' ', $p_holiday[ 'end' ] )[0];
					break;
				default:
					$t_email_config[ 'message' ] .= explode( ' ', $p_holiday[ 'start' ] )[0];
					break;
			}
			$t_email_config[ 'message' ] .= '</strong>.</p>' . PHP_EOL . '<p>To confirm the request, please click on the following link:</p>
<a href="' . $t_confirm_url . '">' . $t_confirm_url . '</a>
<p>If the link doesn\'t work, copy it into a web browser manually.</p>
<p>If you didn\'t make this request, please click on the following link:</p>
<a href="' . $t_cancel_url . '">' . $t_cancel_url . '</a>
<p>If the link doesn\'t work, copy it into a web browser manually.</p></div>' . PHP_EOL . Holidays::$c_tail;

			// Send the email
			$this->send_email( $t_email_config );
		}
	}

	private function send_approval_email( $p_holiday_request )
	{
		$this->load->model( array( 'Nonce_model', 'Staff_model' ));

		if( !empty( $p_holiday_request )) {
			$t_controller_url = site_url() . explode( '/', uri_string() )[0] . '/approve_holiday_request';

			$t_serialized = serialize( $p_holiday_request );
			$t_nonce = $this->Nonce_model->get_crc32( $p_holiday_request [ 'holiday_id' ], $t_serialized, true );

			$t_approve_url = $t_controller_url . '/approve/' . $p_holiday_request[ 'holiday_id' ] . '-' . $t_nonce;
			$t_deny_url = $t_controller_url . '/deny/' . $p_holiday_request[ 'holiday_id' ] . '-' . $t_nonce;

			$t_user = $this->Staff_model->get_name_by_id( $p_holiday_request[ 'staff_id' ]);
			switch( $p_holiday_request[ 'holiday_type' ] ) {
				case "Multiple Days":
					$t_date = explode( ' ', $p_holiday_request[ 'start' ] )[0] . " to " . explode( ' ', $p_holiday_request[ 'end' ] )[0];
					break;
				default:
					$t_date = explode( ' ', $p_holiday_request[ 'start' ] )[0];
					break;
			}

			// Create the email configuration
			$t_email_config[ 'to' ] = 'holidays@ggpsystems.co.uk';
			$t_email_config[ 'subject' ] = 'Holiday request';
			$t_email_config[ 'message' ] = Holidays::$c_head . PHP_EOL . '<div class="row"><p><strong>' . $t_user . '</strong> has requested a <strong>' . $p_holiday_request[ 'holiday_type' ] . '</strong> holiday on <strong>' . $t_date . '</strong>.</p>';
			if( !empty( $p_holiday_request[ 'note' ] )) {
				$t_email_config[ 'message' ] .= '<p>They included the following note with the request: <em>"' . $p_holiday_request[ 'note' ] . '"</em>.</p>';
			}
			$t_email_config[ 'message' ] .= '<p>To approve the request, please click on the following link:</p>
<a href="' . $t_approve_url . '">' . $t_approve_url . '</a>
<p>If the link doesn\'t work, copy it into a web browser manually.</p>
<p>To deny the request, please click on the following link:</p>
<a href="' . $t_deny_url . '">' . $t_deny_url . '</a>
<p>If the link doesn\'t work, copy it into a web browser manually.</p></div>' . PHP_EOL . Holidays::$c_tail;

			// Send the email
			$this->send_email( $t_email_config );
		}
	}

	private function send_approved_email( $p_holiday )
	{
		$this->load->model( array( 'Staff_model' ));

		switch( $p_holiday[ 'holiday_type' ] ) {
			case "Multiple Days":
				$t_date = explode( ' ', $p_holiday[ 'start' ] )[0] . " to " . explode( ' ', $p_holiday[ 'end' ] )[0];
				break;
			default:
				$t_date = explode( ' ', $p_holiday[ 'start' ] )[0];
				break;
		}

		// Create the email configuration
		$t_user = $this->Staff_model->get_name_by_id( $p_holiday[ 'staff_id' ]);
		$t_email_config[ 'to' ] = $this->Staff_model->get_email_by_id( $p_holiday[ 'staff_id' ]);
		$t_email_config[ 'cc' ]  = array( 'holidays@ggpsystems.co.uk' );
		$t_email_config[ 'subject' ] = 'Holiday request approved';
		$t_email_config[ 'message' ] = Holidays::$c_head . PHP_EOL . '<div class="row"><p><strong>' . $t_user . '</strong>\'s requested <strong>' . $p_holiday[ 'holiday_type' ] . '</strong> holiday on <strong>' . $t_date . '</strong> has been approved.</p></div>' . PHP_EOL . Holidays::$c_tail;

		// Send the email
		$this->send_email( $t_email_config );
	}

	private function send_denied_email( $p_holiday, $p_note )
	{
		$this->load->model( array( 'Staff_model' ));

		switch( $p_holiday[ 'holiday_type' ] ) {
			case "Multiple Days":
				$t_date = explode( ' ', $p_holiday[ 'start' ] )[0] . " to " . explode( ' ', $p_holiday[ 'end' ] )[0];
				break;
			default:
				$t_date = explode( ' ', $p_holiday[ 'start' ] )[0];
				break;
		}

		// Create the email configuration
		$t_user = $this->Staff_model->get_name_by_id( $p_holiday[ 'staff_id' ]);
		$t_email_config[ 'to' ] = $this->Staff_model->get_email_by_id( $p_holiday[ 'staff_id' ]);
		$t_email_config[ 'cc' ]  = array( 'holidays@ggpsystems.co.uk' );
		$t_email_config[ 'subject' ] = 'Holiday request denied';
		$t_email_config[ 'message' ] = Holidays::$c_head . PHP_EOL . '<div class="row"><p><strong>' . $t_user . '</strong>\'s requested <strong>' . $p_holiday[ 'holiday_type' ] . '</strong> holiday on <strong>' . $t_date . '</strong> has been denied.</p>';
		if( !empty( $p_note )) {
			$t_email_config[ 'message' ] .= '<p>The following note was included: <em>"' . $p_note . '"</em>.</p></div>';
		};
		$t_email_config[ 'message' ] .= PHP_EOL . Holidays::$c_tail;

		// Send the email
		$this->send_email( $t_email_config );
	}

	private function send_email( $p_email_config )
	{
		require 'application/libraries/PHPMailer/PHPMailerAutoload.php';
		$this->config->load('email');

		$t_mail = new PHPMailer;
		$t_mail->isSMTP();
		// Comment the following if you don't want (blocking) debug output
		if( ENVIRONMENT == 'development' ) {
			$t_mail->SMTPDebug = 2;
			$t_mail->Debugoutput = 'html';
		}
		$t_mail->Host = $this->config->item( 'smtp_host' );
		$t_mail->Port = $this->config->item( 'smtp_port' );
		$t_mail->SMTPAuth = false;
		$t_mail->SMTPAutoTLS = false;
		$t_mail->Priority = 1; // Highest priority
		$t_mail->isHTML(true);
		$t_mail->setFrom( 'donotreply@ggpsystems.co.uk', 'Holiday Booking Page' );
		$t_mail->addReplyTo( 'donotreply@ggpsystems.co.uk', 'Holiday Booking Page' );
		$t_mail->addAddress( $p_email_config[ 'to' ] );
		if( isset( $p_email_config[ 'cc' ] ) && !empty( $p_email_config[ 'cc' ])) {
			foreach( $p_email_config[ 'cc' ] as $t_cc_address ) {
				$t_mail->addCC( $t_cc_address );
			}
		}
		$t_mail->Subject = $p_email_config[ 'subject' ];
		$t_mail->msgHTML( $p_email_config[ 'message' ] );
		$t_mail->AltBody = $t_mail->html2text(  $p_email_config[ 'message' ] );

		if( !$t_mail->send()) {
			echo "Mailer Error: " . $t_mail->ErrorInfo;
		}
	}

	public function set()
	{
		$this->load->library( 'grocery_CRUD' );

		$this->grocery_crud->set_table( 'holidays' );
		$this->grocery_crud->set_subject( 'Vacations' );
		$this->grocery_crud->fields( 'staff_id', 'start', 'end', 'holiday_type', 'note', 'confirmed', 'approved' );
		$this->grocery_crud->field_type( 'holiday_type', 'enum', array( 'Half Day (AM)', 'Half Day (PM)', 'Single Day', 'Multiple Days' ));
		$this->grocery_crud->set_relation( 'staff_id', 'staff', 'name' );

		$output = $this->grocery_crud->render();
		$this->crud_output( $output );
	}

	private function crud_output( $output = null )
	{
		$this->load->view( 'crud_template.php', $output );
	}
}

/* End of file Holidays.php */
/* Location: application/controllers/Holidays.php */