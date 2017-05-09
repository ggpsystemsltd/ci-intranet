<?php

/**
 * Intranet Telephone Directory Controller
 *
 * @author Murray Crane <murray.crane@ggpsystems.co.uk>
 * @copyright 2017 (c) GGP Systems Limited
 * @license https://tldrlegal.com/license/bsd-3-clause-license-%28revised%29#fulltext
 * @version 2.1
 */
class Intranet extends CI_Controller {

	public function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->helper( 'url' );
	}

	public function index() {
		$this->load->helper( 'form' );
		$this->load->library( array( 'form_validation', 'parser' ));
		$this->load->model( array ( 'Attendance_model', 'Holiday_model', 'Intranet_model' ));

		// We have noIP FQDNs for Roger, Bexhill and Vincent Road. 
		// Do a DNS lookup and compare?
		$s_ddns_hostnames = array( 
			'ggp-bexhill.ddns.net', 
			'ggp-roger.ddns.net', 
			'ggp-vincent.ddns.net'
			);
		foreach( $s_ddns_hostnames as $s_hostname ) {
			$s_ddns_ips[] = gethostbyname( $s_hostname );
		}

		$s_remote_ip = filter_input(INPUT_SERVER, 'REMOTE_ADDR', FILTER_VALIDATE_IP );
		$b_show_externals = false;
		if( substr( $s_remote_ip, 0, 4 ) == "10.0" 
				|| substr( $s_remote_ip, 0, 11 ) == "192.168.254" 
				|| substr( $s_remote_ip, 0, 5 ) == "172.1"
				|| in_array( $s_remote_ip, $s_ddns_ips )) {
			$b_show_externals = true;
		}

		$p_save = $this->input->post( 'save', true );
		if( $p_save == "Save directory" ) {
			header( 'Content-type: text/html' );
			header( 'Content-Disposition: attachment; filename="directory.html"');
		}
		$p_order = $this->input->post( 'order', true );
		switch( $p_order ) {
			case 4:
				$order = "Department Order";
				break;
			case 3:
				$order = "Extension Number Order";
				break;
			case 2:
				$order = "Surname Order";
				break;
			case 1:
			default:
				$order = "First Name Order";
		}

		$data = array(
			'intranet_heading' => 'Internal Telephone Directory',
			'intranet_secondary' => $order,
			'intranet_user' => filter_input( INPUT_SERVER, 'INTRANET_USER' ),
			'intranet_pass' => filter_input( INPUT_SERVER, 'INTRANET_PASS' ),
			'author_name' => 'Murray Crane',
			'meta_description' => 'Directory of staff internal extension and external contact numbers.',
			'keywords' => 'telephone directory, internal directory, extensions',
			'refresh' => '<meta http-equiv="refresh" content="30" />',
			'remote_ip' => $s_remote_ip,
			'author_mailto' => safe_mailto( 'murray.crane@ggpsystems.co.uk', 'Murray Crane' ),
			'style' => '',
			'javascript' => '<script type="text/javascript">$(\'#legend\').click(function(){
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
			'intranet_active' => ' class="active"',
			'intranet_active_span' => '<span class="sr-only">(current)</span>',
			'machines_active' => '',
			'machines_active_span' => '',
			'timeclock_active' => '',
			'timeclock_active_span' => '',
			'wol_active' => '',
			'wol_active_span' => '',
		);

		// Telephone directory table
		$t_table_data[ 'title' ] = '';
		$t_table_data[ 'class' ] = 'col-md-3';
		$t_table_data[ 'head' ] = array(
			0 => array( 'class' => '', 'column' => 'Extn' ),
			1 => array( 'class' => '', 'column' => 'Name' ),
			2 => array( 'class' => ( $b_show_externals ) ? '' : 'class="hidden"', 'column' => 'External(s)' ),
		);
		$t_telephones = $this->Intranet_model->get_staff( $p_order, $b_show_externals );
		foreach( $t_telephones as $t_telephone ) {
			$t_name = $t_telephone[ 'name' ];
			$t_attendance = $this->Attendance_model->get_attendance_by_id( $t_telephone[ 'staff_id' ] );
			if( $t_attendance != "NaN" ) {
				if( $this->Holiday_model->is_on_holiday( $t_telephone[ 'staff_id' ])) {
					$t_name .= " <em>Vacation</em>";
				} else {
					$t_name .= " <em>$t_attendance</em>";
				}
			}
			$t_table_data[ 'row' ][] = array( 'class' => '', 'column' => array(
				0 => array( 'class' => '', 'value' => $t_telephone[ 'extn' ]),
				1 => array( 'class' => 'class="' . $t_telephone[ 'class' ] . '"', 'value' => $t_name ),
				2 => array( 'class' => ( $b_show_externals ) ? '' : 'class="hidden"', 'value' => $t_telephone[ 'externals' ])),
			);
		}
		$t_table_data[ 'updated' ] = date( 'Y-m-d H:i:s' );

		// Vacations table - $t_holidays will be empty if no holidays
		$t_display_holidays = true;
		$t_holidays = $this->Holiday_model->get_holidays( "1 week" );
		if( !empty( $t_holidays )) {
			$t_holidays_data[ 'class' ] = 'col-md-3 hidden-print';
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
		$t_holidays_data[ 'updated' ] = $this->Holiday_model->get_last_update();

		// Telephone directory order form
		$t_order_array = array(
			'1' => 'firstname',
			'2' => 'surname',
			'3' => 'extension number',
		);
		$t_form_data[ 'variable' ] = '	<div id="legend" class="btn btn-default">Reorder directory <span class="glyphicon glyphicon-menu-down"></span></div>
		<div class="form-content row" style="display:none;">
			<form action="intranet" method="post" id="intranet-form" class="form-horizontal" accept-charset="UTF-8">
				<div class="form-group">
					<label class="col-sm-2 control-label">Select order</label>
					<div class="col-sm-4">' . PHP_EOL;
		$t_form_data[ 'variable' ] .= form_dropdown( 'order', $t_order_array, $p_order,
			array( 'class' => 'form-control', 'onchange' => 'this.form.submit();' ));
		$t_form_data[ 'variable' ] .= '					</div>
				</div>
				<div class="form-group">
					<div class="col-sm-4 col-sm-offset-2">
						<button type="submit" name="save" value="Save directory" class="btn btn-success"><span class="glyphicon glyphicon-floppy-save"></span> Save directory</button>
					</div>
				</div>
			</form>
		</div>' . PHP_EOL;
		$t_form_data[ 'variable_post' ] = '';

		$this->parser->parse( 'header', $data );
		$this->parser->parse( 'navbar', $t_nav_data );
		$this->parser->parse( 'heading', $data );
		$this->parser->parse( 'row-start', array() );
		$this->parser->parse( 'table', $t_table_data );
		if( $t_display_holidays ) {
			$this->parser->parse( 'table', $t_holidays_data );
		}
		$this->parser->parse( 'row-stop', array() );
		$this->parser->parse( 'form', $t_form_data );
		$this->parser->parse( 'footer', $data );
	}

	public function set( $table = 'staff' )
	{
		$this->load->library( 'grocery_CRUD' );

		$this->grocery_crud->set_theme( 'bootstrap' );

		switch( $table ) {
			case 'fobs':
				$this->grocery_crud->set_table( 'doorcards' );
				$this->grocery_crud->display_as( 'name', 'Fob number' );
				$this->grocery_crud->set_subject( 'Fob' );
				break;
			case 'telephones':
				$this->grocery_crud->set_table( 'telephones' );
				$this->grocery_crud->set_relation( 'staff_id', 'staff', 'name' );
				$this->grocery_crud->display_as( 'staff_id', 'Employee' );
				$this->grocery_crud->set_subject( 'Telephone' );
				break;
			default:
				$this->grocery_crud->set_table( 'staff' );
				$this->grocery_crud->set_relation( 'extn_id', 'extensions', 'name' );
				$this->grocery_crud->display_as( 'extn_id', 'Extn' );
				$this->grocery_crud->set_relation( 'dept_id', 'departments', 'name' );
				$this->grocery_crud->display_as( 'dept_id', 'Department' );
				$this->grocery_crud->set_relation( 'doorcard_id', 'doorcards', 'name', array( 'operational' => '1' ));
				$this->grocery_crud->display_as( 'doorcard_id', 'Door fob' );
				$this->grocery_crud->set_subject( 'Employee' );
				break;
		}

		$output = $this->grocery_crud->render();
		$this->crud_output( $output );
	}

	private function crud_output( $output = null )
	{
		$this->load->view( 'crud_template.php', $output );
	}
}

/* End of file Intranet.php */
/* Location: application/controllers/Intranet.php */