<?php

class Attendance extends CI_Controller {

    function __construct() {
        parent::__construct();

        $this->load->database();
        $this->load->helper( 'url' );
    }

    function index() {
        $this->load->helper( 'form' );
        $this->load->library( array( 'calendar', 'form_validation', 'ggpclass', 'parser' ));

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

	// Attendance table
        $this->db->select( 'staff.name, staff.work_state' )->from( 'staff' )->where( 'staff.active', 1 )->where( 'staff.work_state !=', 'NaN')->order_by( 'staff.firstname' );
        $query = $this->db->get();
        if( $query->num_rows() > 0 ) {
            $i = 1;
            foreach( $query->result_array() as $row ) {
                $data[ 'staff' ][] = array(
                    'class' => $i,
                    'name' => $row[ 'name' ],
                    'attclass' => preg_replace('/\s/', '', strtolower($row[ 'work_state' ])),
                    'attendance' => $row[ 'work_state' ],
                );
                ($i == 1 ? $i++ : $i--);
            }
        }

	// Vacations table
        $query = $this->db->query( 'SELECT staff.name, holidays.start, holidays.end FROM `holidays` 
	JOIN `staff` ON holidays.staff_id=staff.staff_id 
	WHERE `approved`=1 
	AND `start` BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 1 week) 
	OR `end` BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 1 week) 
	OR CURDATE() BETWEEN `start` AND `end` ORDER BY staff.firstname' );
        if( $query->num_rows() > 0 ) {
            $i = 1;
            foreach( $query->result_array() as $row ) {
                $data[ 'holidays' ][] = array(
                    'class' => $i,
                    'name' => $row[ 'name' ],
                    'dates' => $row[ 'start' ] . " to " . $row[ 'end' ],
                );
                ($i == 1 ? $i++ : $i--);
            }
        }

        // Last update
        $this->db->select_max( 'updated' )->from( 'staff' )->where( 'staff.active', 1 )->where( 'staff.work_state !=', 'NaN')->order_by( 'staff.firstname' );
        $query = $this->db->get();
        if( $query->num_rows() > 0 ) {
            foreach( $query->result_array() as $row ) {
                $data = array_merge( $data, array(
                    'updated' => $row[ 'updated' ],
                ));
            }
        }

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
