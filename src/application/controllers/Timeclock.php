<?php
/**
 * Timeclock Controller
 *
 * @author Murray Crane <murray.crane@ggpsystems.co.uk>
 * @copyright 2017 (c) GGP Systems Limited
 * @license https://tldrlegal.com/license/bsd-3-clause-license-%28revised%29#fulltext
 * @version 2.2
 */
class Timeclock extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->library( 'session' );
	}

	public function index()
	{
		$this->load->helper( array( 'form', 'url' ));
		$this->load->library( array( 'parser' ));
		$this->load->model( array( 'Staff_model', 'Timeclock_model' ));

		$t_page_data = array(
			'intranet_heading' => 'Timeclock',
			'intranet_secondary' => date( 'd-m-Y' ),
			'intranet_user' => filter_input( INPUT_SERVER, 'INTRANET_USER' ),
			'intranet_pass' => filter_input( INPUT_SERVER, 'INTRANET_PASS' ),
			'author_name' => 'Murray Crane',
			'refresh' => '',
			'meta_description' => 'Remote staff timeclock.',
			'keywords' => 'staff timeclock',
			'remote_ip' => filter_input( INPUT_SERVER, 'REMOTE_ADDR', FILTER_VALIDATE_IP ),
			'author_mailto' => safe_mailto( 'murray.crane@ggpsystems.co.uk', 'Murray Crane' ),
			'style' => '',
			'javascript' => '<script type="application/javascript">$(\'#legend\').click(function(){
        $(\'.form-content\').toggle();
    });</script>',
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
			'machines_active' => '',
			'machines_active_span' => '',
			'timeclock_active' => ' class="active"',
			'timeclock_active_span' => '<span class="sr-only">(current)</span>',
			'wol_active' => '',
			'wol_active_span' => '',
		);

		// Table data
		$t_timeclock_data = $this->Timeclock_model->get_current_data();
		$t_table_data[ 'title' ] = '';
		$t_table_data[ 'class' ] = 'col-md-4';
		$t_table_data[ 'head' ] = array(
			0 => array( 'column' => 'Name'),
			1 => array( 'column' => 'In/Out'),
			2 => array( 'column' => 'Time and Date' ),
			3 => array( 'column' => 'Note' ),
		);
		if( empty( $t_timeclock_data )) {
			$t_table_data[ 'row' ][] = array( 'class' => '', 'column' => array(
				0 => array( 'class' => '', 'value' => ''),
				1 => array( 'class' => '', 'value' => ''),
				2 => array( 'class' => '', 'value' => ''),
				3 => array( 'class' => '', 'value' => '')),
			);
		} else {
			foreach ($t_timeclock_data as $t_touch) {
				$t_datetime = new DateTime( $t_touch[ 'time_stamp' ], new DateTimeZone('UTC'));
				$t_datetime->setTimezone(new DateTimeZone('Europe/London'));
				$t_table_data[ 'row' ][] = array( 'class' => '', 'column' => array(
					0 => array( 'class' => '', 'value' => $t_touch[ 'name' ]),
					1 => array( 'class' => 'class="' . $t_touch[ 'class' ] . '"', 'value' => $t_touch[ 'in_out' ]),
					2 => array( 'class' => '', 'value' => $t_datetime->format('H:i:s d/m/Y (T)')),
					3 => array( 'class' => '', 'value' => $t_touch[ 'note' ])),
				);
			}
		}
		$t_table_data[ 'updated' ] = $this->Timeclock_model->last_updated();

		// Form data
		$t_users = $t_users = $this->Staff_model->get_staff_list_id();
		$t_touch_in = false;
		$t_touch_out = true; // Assume "out" - make a function to get this from the database [in/out XOR]
		if( isset( $_SESSION[ 'user' ])) {
			$t_user = $_SESSION[ 'user' ];
		} else {
			$t_user = null;
		}
		$t_form_data[ 'variable' ] = '	<div id="legend" class="btn btn-default">Timeclock entry <span class="glyphicon glyphicon-menu-down"></span></div>
		<div class="form-content row" style="display:none;">
			<form action="' . base_url( '/timeclock/touch' ) . '" method="post" id="timeclock-form" class="form-horizontal" accept-charset="UTF-8">
				<div class="form-group">
					<label class="col-sm-2 control-label">Select name</label>
					<div class="col-sm-4">' . PHP_EOL;
		$t_form_data[ 'variable' ] .= form_dropdown( 'name', $t_users, $t_user,
			array( 'id' => 'name', 'class' => 'form-control' ));
		$t_form_data[ 'variable' ] .= '					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label">Select in/out</label>
					<div class="col-sm-4" id="touch-type">
						<label class="radio-inline">
							<input type="radio" name="touch-type" id="touch-in" value="in"';
		if( $t_touch_out ) {
			$t_form_data[ 'variable' ] .= ' checked="checked"';
		}
		$t_form_data[ 'variable' ] .= '> In
						</label>
						<label class="radio-inline">
							<input type="radio" name="touch-type" id="touch-out" value="out"';
		if( $t_touch_in ) {
			$t_form_data[ 'variable' ] .= ' checked="checked"';
		}
		$t_form_data[ 'variable' ] .= '> Out
						</label>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label">Note</label>
					<div class="col-sm-4">
						<input type="text" name="note" id="note" class="form-control" placeholder="Explanatory note">
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-4 col-sm-offset-2">
						<label class="checkbox-inline">
							<!-- It would be neat if I dual-purposed this to delete the cookie as well...-->
							<input type="checkbox" name="cookie" id="cookie"> Remember me
						</label>
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-4 col-sm-offset-2">
						<button type="submit" id="submit-btn" name="submit" class="btn btn-success"><span class="glyphicon glyphicon-ok"></span> Submit entry</button>
						<button type="reset" id="cancel-btn" name="cancel" class="btn btn-danger"><span class="glyphicon glyphicon-warning-sign"></span> Cancel</button>
					</div>
				</div>
			</form>
		</div>' . PHP_EOL;
		$t_form_data[ 'variable_post' ] = '';

		$this->parser->parse( 'header', $t_page_data );
		$this->parser->parse( 'navbar', $t_nav_data );
		$this->parser->parse( 'heading', $t_page_data );
		$this->parser->parse( 'row-start', array());
		$this->parser->parse( 'table', $t_table_data );
		$this->parser->parse( 'row-stop', array());
		$this->parser->parse( 'form', $t_form_data );
		$this->parser->parse( 'footer', $t_page_data );
	}

	public function touch()
	{
		$this->load->helper( array( 'url' ));
		$this->load->model( array( 'Timeclock_model' ));

		/* "name" String staff_id int(10)
		 * "touch-type" Radio in_out enum('in', 'out')
		 * "note" String notes varchar(255)
		 *
		 * "cookie" Checkbox unset or "on"
		 * "submit" no value
		 */
		$t_touch_data = $this->input->post( null, false );
		unset( $t_touch_data[ 'submit' ]);

		if( isset( $t_touch_data[ 'cookie' ])) {
			// Add the user to the session cookie
			$this->session->sess_expiration( 0 );
			$this->session->set_userdata( 'user', (int) $t_touch_data[ 'name' ]);
			unset( $t_touch_data[ 'cookie' ]);
		}

		// Make a db-order punch record, then unset all the redundant columns
		$t_touch_data[ 'in_out' ] = $t_touch_data[ 'touch-type' ];
		$t_touch_data[ 'time_stamp' ] = gmdate('c');
		$t_touch_data[ 'notes' ] = $t_touch_data[ 'note' ];
		$t_touch_data[ 'staff_id' ] = (int) $t_touch_data[ 'name' ];
		$t_touch_data[ 'ip_address' ] = filter_input( INPUT_SERVER, 'REMOTE_ADDR', FILTER_VALIDATE_IP );
		unset( $t_touch_data[ 'name' ]);
		unset( $t_touch_data[ 'touch-type' ]);
		unset( $t_touch_data[ 'note' ]);

		$this->Timeclock_model->insert( $t_touch_data );
		redirect( base_url( '/timeclock' ));
	}

	public function report()
	{
		$this->load->helper( array( 'form', 'url' ));
		$this->load->library( array( 'parser' ));
		$this->load->model( array( 'Staff_model', 'Timeclock_model' ));

		$t_yesterday = date( 'Y-m-d', strtotime( 'last weekday' ));

		// Default to logged in user and yesterday
		if( $this->input->post( 'name', true )) {
			$t_user = $this->input->post( 'name', true );
		} elseif( isset( $_SESSION[ 'user' ])) {
			$t_user = $_SESSION[ 'user' ];
		} else {
			$t_user = null;
		}
		if( !is_null( $t_user )) {
			$t_user_name = $this->Staff_model->get_name_by_id( $t_user );
		} else {
			$t_user_name = "All Staff";
		}
		if( $this->input->post( 'start-date', true )) {
			$t_period_start = $this->input->post( 'start-date', true ) . " 00:00:00";
		} else {
			$t_period_start = $t_yesterday . " 00:00:00";
		}
		if( $this->input->post( 'end-date', true )) {
			$t_period_end = $this->input->post( 'end-date', true ) . " 23:59:59";
		} else {
			$t_period_end = $t_yesterday . " 23:59:59";
		}

		$t_start_date = substr( $t_period_start, 0, strpos( $t_period_start, ' ' ));
		$t_end_date = substr( $t_period_end, 0, strpos( $t_period_end, ' ' ));
		if( $t_start_date == $t_end_date ){
			$t_period = $t_start_date;
		} else {
			$t_period = $t_start_date . ' to ' . $t_end_date;
		}

		$t_page_data = array(
			'intranet_heading' => 'Hours Worked',
			'intranet_secondary' => $t_user_name . ', ' . $t_period,
			'intranet_user' => filter_input( INPUT_SERVER, 'INTRANET_USER' ),
			'intranet_pass' => filter_input( INPUT_SERVER, 'INTRANET_PASS' ),
			'author_name' => 'Murray Crane',
			'refresh' => '',
			'meta_description' => 'Remote staff timeclock hours worked report.',
			'keywords' => 'staff timeclock report',
			'remote_ip' => filter_input( INPUT_SERVER, 'REMOTE_ADDR', FILTER_VALIDATE_IP ),
			'author_mailto' => safe_mailto( 'murray.crane@ggpsystems.co.uk', 'Murray Crane' ),
			'style' => '',
			'javascript' => '<script src="' . base_url( "/assets/js/datepicker.js" ). '" type="application/javascript"></script>
	<script type="application/javascript">$(\'#legend\').click(function(){
        $(\'.form-content\').toggle();
    });</script>',
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
			'machines_active' => '',
			'machines_active_span' => '',
			'timeclock_active' => ' class="active"',
			'timeclock_active_span' => '<span class="sr-only">(current)</span>',
			'wol_active' => '',
			'wol_active_span' => '',
		);

		// Form data
		$t_users = $t_users = $this->Staff_model->get_staff_list_id();
		$t_form_data[ 'variable' ] = '	<div id="legend" class="btn btn-default">Report parameters <span class="glyphicon glyphicon-menu-down"></span></div>
		<div class="form-content row" style="display:none;">
			<form action="' . base_url( '/timeclock/report' ) . '" method="post" id="timeclock-form" class="form-horizontal" accept-charset="UTF-8">
				<div class="form-group">
					<label class="col-sm-2 control-label">Select name</label>
					<div class="col-sm-4">' . PHP_EOL;
		$t_form_data[ 'variable' ] .= form_dropdown( 'name', $t_users, $t_user,
			array( 'id' => 'name', 'class' => 'form-control' ));
		$t_form_data[ 'variable' ] .= '					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label">From</label>
					<div class="col-sm-4 input-group" style="padding-left: 15px; padding-right: 15px;">
						<input type="text" id="start-date" name="start-date" class="form-control date-picker" placeholder="From date">
						<span class="input-group-addon add-on"><span class="glyphicon glyphicon-calendar"></span></span>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label">To</label>
					<div class="col-sm-4 input-group" style="padding-left: 15px; padding-right: 15px;">
						<input type="text" id="end-date" name="end-date" class="form-control date-picker" placeholder="To date">
						<span class="input-group-addon add-on"><span class="glyphicon glyphicon-calendar"></span></span>
					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-4 col-sm-offset-2">
						<button type="submit" id="submit-btn" name="submit" class="btn btn-success"><span class="glyphicon glyphicon-ok"></span> Submit parameters</button>
						<button type="reset" id="cancel-btn" name="cancel" class="btn btn-danger"><span class="glyphicon glyphicon-warning-sign"></span> Cancel</button>
					</div>
				</div>
			</form>
		</div>' . PHP_EOL;
		$t_form_data[ 'variable_post' ] = '';
		//var_dump($this->session->userdata()); echo "<br/>";

		$this->parser->parse( 'header', $t_page_data );
		$this->parser->parse( 'navbar', $t_nav_data );
		$this->parser->parse( 'heading', $t_page_data );

		// Table data
		$t_timeclock_data = $this->Timeclock_model->get_data( $t_user, $t_period_start, $t_period_end );
		//var_dump($t_timeclock_data['rows']); echo "<br/>";

		if( !empty( $t_timeclock_data )) {
			$t_table_data[ 'class' ] = 'col-md-4';
			$t_table_data[ 'title' ] = '';
			$t_table_data[ 'head' ] = array(
				0 => array( 'column' => 'In/Out' ),
				1 => array( 'column' => 'Time and Date' ),
				2 => array( 'column' => 'Note' ),
			);

			$t_row_date = "";

			// @todo Handles multiple people simultaneously, now what about multiple days?
			foreach ($t_timeclock_data[ 'rows' ] as $t_touch) {
				$t_datetime = new DateTime( $t_touch[ 'time_stamp' ], new DateTimeZone('UTC'));
				$t_datetime->setTimezone(new DateTimeZone('Europe/London'));

				// Multi-day handling
				if( empty( $t_row_date )) {
					$t_interval = new DateTime( "00:00:00" );
					$f = clone $t_interval;
				} elseif( $t_datetime->format('d/m/Y' ) != $t_row_date ) {
					$t_table_data[ 'row' ][] = array( 'class' => 'class="info"', 'column' => array(
						0 => array( 'class' => '', 'value' => '<strong>Total Worked</strong>'),
						1 => array( 'class' => '', 'value' => ''),
						2 => array( 'class' => '', 'value' => '<strong>'. $t_interval->diff( $f, true )->format("%H h %I m %S s").'</strong>'),
					));

					$this->parser->parse( 'row-start', array());
					$this->parser->parse( 'table', $t_table_data );
					$this->parser->parse( 'row-stop', array());

					$t_interval = new DateTime( "00:00:00" );
					$f = clone $t_interval;
					unset( $t_table_data[ 'row' ]);
				}

				// Multi-user handling
				if( isset( $t_touch[ 'name' ])) {
					if( empty( $t_table_data[ 'title' ] )) {
						// First time through, no user name yet
						$t_table_data[ 'title' ] = $t_touch[ 'name' ];
					} elseif( $t_touch[ 'name' ] != $t_table_data[ 'title' ] ) {
						// Change of user name
						$t_table_data[ 'row' ][] = array( 'class' => 'class="info"', 'column' => array(
							0 => array( 'class' => '', 'value' => '<strong>Total Worked</strong>'),
							1 => array( 'class' => '', 'value' => ''),
							2 => array( 'class' => '', 'value' => '<strong>'. $t_interval->diff( $f, true )->format("%H h %I m %S s").'</strong>'),
						));

						$this->parser->parse( 'row-start', array());
						$this->parser->parse( 'table', $t_table_data );
						$this->parser->parse( 'row-stop', array());

						$t_table_data[ 'title' ] = $t_touch[ 'name' ];
						$t_interval = new DateTime( "00:00:00" );
						$f = clone $t_interval;
						unset( $t_table_data[ 'row' ]);
					}
				}

				$t_row_date = $t_datetime->format( 'd/m/Y' );

				$t_table_data[ 'row' ][] = array( 'class' => 'class="' . $t_touch[ 'class' ] . '"', 'column' => array(
					0 => array( 'class' => '', 'value' => $t_touch[ 'in_out' ] ),
					1 => array( 'class' => '', 'value' => $t_datetime->format('H:i:s d/m/Y (T)' )),
					2 => array( 'class' => '', 'value' => $t_touch[ 'note' ] ),
				));
				if( $t_touch[ 'in_out' ] == 'in' ) {
					$t_in = new DateTime( $t_touch[ 'time_stamp' ]);
				} else {
					$t_out = new DateTime( $t_touch[ 'time_stamp' ]);
					$t_interval->add( $t_out->diff(  $t_in, true ));
				}
			}
			$t_table_data[ 'row' ][] = array( 'class' => 'class="info"', 'column' => array(
				0 => array( 'class' => '', 'value' => '<strong>Total Worked</strong>'),
				1 => array( 'class' => '', 'value' => ''),
				2 => array( 'class' => '', 'value' => '<strong>'. $t_interval->diff( $f, true )->format("%H h %I m %S s").'</strong>'),
			));

			$this->parser->parse( 'row-start', array());
			$this->parser->parse( 'table', $t_table_data );
			$this->parser->parse( 'row-stop', array());
		}

		$this->parser->parse( 'form', $t_form_data );
		$this->parser->parse( 'footer', $t_page_data );
	}

	public function set()
	{
		$this->load->library( 'grocery_CRUD' );

		$this->grocery_crud->set_theme( 'bootstrap' );

		$this->grocery_crud->set_table( 'touch_times' );
		$this->grocery_crud->set_subject( 'Timeclock' );
		$this->grocery_crud->fields( 'staff_id', 'in_out', 'time_stamp', 'notes', 'ip_address' );
		$this->grocery_crud->field_type( 'in_out', 'enum', array( 'in', 'out' ));
		$this->grocery_crud->set_relation( 'staff_id', 'staff', 'name', 'active = true AND work_state != "NaN"' );

		$output = $this->grocery_crud->render();
		$this->crud_output( $output );
	}

	private function crud_output( $output = null )
	{
		$this->load->view( 'crud_template.php', $output );
	}
}