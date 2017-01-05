<?php

class Attendance extends CI_Controller {

    function __construct() {
        parent::__construct();

        $this->load->database();
        $this->load->helper( 'url' );
    }

    function index() {
        $this->load->helper( 'form' );
        $this->load->library( array( 'parser' ));
        $this->load->model( array( 'Attendance_model', 'Holiday_model' ));

        $post = $this->input->post( NULL, TRUE );
        $atts = array(
            'class' => 'link-mailto',
        );
        $data = array(
            'intranet_title' => 'GGP Systems Ltd intranet',
            'intranet_module' => 'Staff Attendance - ' . date( 'd-m-Y' ),
            'intranet_user' => $_SERVER['INTRANET_USER'],
            'intranet_pass' => $_SERVER['INTRANET_PASS'],
            'author_name' => 'Murray Crane',
            'refresh' => '',
            'meta_description' => 'Staff attendance record.',
            'keywords' => 'staff attendance',
            'remote_ip' => filter_input( INPUT_SERVER, 'REMOTE_ADDR', FILTER_VALIDATE_IP ),
        );
        $data[ 'author_mailto' ] = safe_mailto( 'murray.crane@ggpsystems.co.uk', $data[ 'author_name' ], $atts );

		// Attendance
        $data[ 'staff' ] = $this->Attendance_model->get_attendance();

		// Vacations
        $data[ 'holidays' ] = $this->Holiday_model->get_holidays_this_week();

        // Last update
		$data[ 'updated' ] = $this->Attendance_model->get_last_update();

        $this->parser->parse( 'page_head', $data );
        $this->parser->parse( 'attendance_list', $data );
        $this->parser->parse( 'page_foot', $data );
    }

    public function set( $table = 'staff' )
	{
		$this->load->library( 'grocery_CRUD' );

		if( $table == 'holidays' )
		{
			$this->grocery_crud->set_table( 'holidays' );
			$this->grocery_crud->set_subject( 'Vacations' );
		} else {
			$this->grocery_crud->set_table( 'staff' );
			$this->grocery_crud->set_subject( 'Attendance' );
			$this->grocery_crud->columns( 'name', 'work_state' );
			$this->grocery_crud->fields( 'name', 'work_state' );
			$this->grocery_crud->display_as( 'work_state', 'Attendance' );
			$this->grocery_crud->where( 'active', 1 );
			$this->grocery_crud->where( 'work_state !=', 'NaN' );
		}

        $output = $this->grocery_crud->render();
        $this->crud_output( $output );
    }

    function crud_output( $output = null )
    {
        $this->load->view( 'crud_template.php', $output );
    }
}

/* End of file intranet.php */
/* Location: application/controllers/intranet.php */
