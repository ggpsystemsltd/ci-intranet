<?php

/**
 * Intranet Telephone Directory Controller
 *
 * @author Murray Crane <murray.crane@ggpsystems.co.uk>
 * @copyright 2016 (c) GGP Systems Limited
 * @license http://www.gnu.org/licenses/gpl.html
 * @version 1.3
 */
class Intranet extends CI_Controller {

	function __construct() {
		parent::__construct();

		$this->load->database();
		$this->load->helper( 'url' );
		$this->load->library( 'grocery_CRUD' );
	}

	function index() {
		$this->load->helper( 'form' );
		$this->load->library( array( 'form_validation', 'ggp_helper', 'parser' ));
		$this->load->model( array ( 'Intranet_model' ));

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
		$b_show_externals = FALSE;
		if( substr( $s_remote_ip, 0, 4 ) == "10.0" 
				|| substr( $s_remote_ip, 0, 11 ) == "192.168.254" 
				|| substr( $s_remote_ip, 0, 8 ) == "172.16.1" 
				|| in_array( $s_remote_ip, $s_ddns_ips )) {
			$b_show_externals = TRUE;
		}

		$rules = array();
		$rules[ 'order' ] = 'required';
		$this->form_validation->set_rules( $rules );

		$post = $this->input->post( NULL, TRUE );
		if( isset( $post[ 'save' ] ) and $post[ 'save' ] == "Save directory" ) {
			header( 'Content-type: text/html' );
			header( 'Content-Disposition: attachment; filename="directory.html"');
		}
		if( !isset( $post[ 'order' ])) {
			$post[ 'order' ] = 1;
		}
		switch( $post[ 'order' ] ) {
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
		$atts = array(
			'class' => 'link-mailto',
		);

		$js = 'onchange="this.form.submit();"';
		$data = array(
			'intranet_title' => 'GGP Systems Ltd intranet',
			'intranet_module' => 'Internal Telephone Directory - ' . $order,
			'intranet_user' => $_SERVER['INTRANET_USER'],
			'intranet_pass' => $_SERVER['INTRANET_PASS'],
			'author_name' => 'Murray Crane',
			'meta_description' => 'Directory of staff internal extension and external contact numbers.',
			'keywords' => 'telephone directory, internal directory, extensions',
			'refresh' => '<meta http-equiv="refresh" content="30" />',
			'remote_ip' => $s_remote_ip,
		);
		$data[ 'author_mailto' ] = safe_mailto( 'murray.crane@ggpsystems.co.uk', $data[ 'author_name' ], $atts );
		$data[ 'staff' ] = $this->Intranet_model->get_staff( $post[ 'order' ], $b_show_externals );
		$data[ 'depts' ] = $this->Intranet_model->get_departments();
		$data[ 'variable' ] = form_open( 'intranet' );
		$data[ 'variable' ] .= form_fieldset( 'Internal telephone directory order' );
		$data[ 'variable' ] .= "<br />" . validation_errors();
		$ddarray = array(
			'1' => 'firstname',
			'2' => 'surname',
			'3' => 'extension number',
//			'4' => 'department',
		);
		$data[ 'variable' ] .= form_label( 'Select ordering:', 'order' );
		$data[ 'variable' ] .= form_dropdown( 'order', $ddarray, $post[ 'order' ], $js );
		$data[ 'variable' ] .= form_submit( 'save', 'Save directory' );
		$data[ 'variable' ] .= form_fieldset_close();
		$data[ 'variable' ] .= form_close();
		$data[ 'variable_post' ] = "";
		$this->parser->parse( 'page_head', $data );
		$this->parser->parse( 'phone_list', $data );
		$this->parser->parse( 'phone_list_form', $data );
		$this->parser->parse( 'page_foot', $data );
	}

	public function crud()
	{
		$this->grocery_crud->set_table( 'staff' );
		$this->grocery_crud->set_relation( 'extn_id', 'extensions', 'name' );
		$this->grocery_crud->display_as( 'extn_id', 'Extn' );
		$this->grocery_crud->set_relation( 'dept_id', 'departments', 'name' );
		$this->grocery_crud->display_as( 'dept_id', 'Department' );
		$this->grocery_crud->set_relation( 'doorcard_id', 'doorcards', 'name', array( 'operational' => '1' ));
		$this->grocery_crud->display_as( 'doorcard_id', 'Door fob' );
		$this->grocery_crud->set_subject( 'Employee' );

		$output = $this->grocery_crud->render();
		$this->crud_output( $output );
	}

	public function telephones()
	{
		$this->grocery_crud->set_table( 'telephones' );
		$this->grocery_crud->set_relation( 'staff_id', 'staff', 'name' );
		$this->grocery_crud->display_as( 'staff_id', 'Employee' );
		$this->grocery_crud->set_subject( 'Telephone' );

		$output = $this->grocery_crud->render();
		$this->crud_output( $output );
	}

	public function fobs()
	{
		$this->grocery_crud->set_table( 'doorcards' );
		$this->grocery_crud->display_as( 'name', 'Fob number' );
		$this->grocery_crud->set_subject( 'Fob' );

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
