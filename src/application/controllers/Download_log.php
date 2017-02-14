<?php

/**
 * Website Download Log Controller
 *
 * @author Murray Crane <murray.crane@ggpsystems.co.uk>
 * @copyright 2017 (c) GGP Systems Limited
 * @license http://www.gnu.org/licenses/gpl.html
 * @version 2.0
 * @todo Add a form to select the download groups
 */
class Download_log extends CI_Controller
{
	function __construct() {
		parent::__construct();
	}

	function index() {
		$this->load->database();
		$this->load->helper( array( 'form', 'url' ));
		$this->load->library( array( 'parser' ));
		$this->load->model( array( 'download_log_model' ));

		$p_group = $this->input->post( 'group', true );
		if( empty( $p_group )) {
			$p_group = $this->download_log_model->get_last_group();
		}

		$data = array(
			'intranet_title' => 'Download Monitor Log (GGP intranet)',
			'intranet_heading' => 'Download Monitor Log',
			'intranet_secondary' => date( 'd-m-Y' ),
			'intranet_user' => filter_input( INPUT_SERVER, 'INTRANET_USER' ),
			'intranet_pass' => filter_input( INPUT_SERVER, 'INTRANET_PASS' ),
			'author_name' => 'Murray Crane',
			'refresh' => '',
			'meta_description' => 'Download monitor log.',
			'keywords' => 'download monitor log',
			'remote_ip' => filter_input( INPUT_SERVER, 'REMOTE_ADDR', FILTER_VALIDATE_IP ),
			'author_mailto' => safe_mailto( 'murray.crane@ggpsystems.co.uk', 'Murray Crane',
				array( 'class' => 'link-mailto' )),
			'style' => '',
			'javascript' => '<script type="text/javascript">$(\'#legend\').click(function(){
        $(\'.form-content\').toggle();
    });</script>',
		);

		$t_table_data[ 'title' ] = '';
		$t_table_data[ 'class' ] = 'col-md-8';
		$t_table_data[ 'head' ] = array(
			0 => array( 'class' => '', 'column' => 'Date'),
			1 => array( 'class' => '', 'column' => 'Email'),
			2 => array( 'class' => '', 'column' => 'Download' ),
			);
		$t_downloads = $this->download_log_model->get_downloads( $p_group );
		$t_summary_by_user = array();
		$t_summary_by_file = array();
		foreach( $t_downloads as $t_download ) {
			$t_table_data[ 'row' ][] = array( 'column' => array(
					0 => array( 'class' => '', 'value' => $t_download[ 'date' ]),
					1 => array( 'class' => '', 'value' => $t_download[ 'user_email' ]),
					2 => array( 'class' => '', 'value' => $t_download[ 'title' ])),
			);
			if( !array_key_exists( $t_download[ 'user_email' ], $t_summary_by_user )) {
				$t_summary_by_user[ $t_download[ 'user_email' ]] = 1;
			} else {
				$t_summary_by_user[ $t_download[ 'user_email' ]] += 1;
			}
			if( !array_key_exists( $t_download[ 'title' ], $t_summary_by_file )) {
				$t_summary_by_file[ $t_download[ 'title' ]] = 1;
			} else {
				$t_summary_by_file[ $t_download[ 'title' ]] += 1;
			}
		}
		$t_table_data[ 'updated' ] = date( 'y-m-d H:i:s' );

		arsort( $t_summary_by_user );
		arsort( $t_summary_by_file );

		$t_user_summary[ 'title' ] = '<h2>Summary by user</h2>';
		$t_user_summary[ 'class' ] = 'col-md-4';
		$t_user_summary[ 'head' ] = array(
			0 => array( 'class' => '', 'column' => 'User' ),
			1 => array( 'class' => '', 'column' => 'Downloads' ),
			);
		foreach( $t_summary_by_user as $key => $value ) {
			$t_user_summary[ 'row' ][] = array( 'column' => array(
				0 => array( 'class' => '', 'value' => $key ),
				1 => array( 'class' => '', 'value' => $value )),
			);
		}
		$t_user_summary[ 'updated' ] = date( 'y-m-d H:i:s' );

		$t_file_summary[ 'title' ] = '<h2>Summary by file</h2>';
		$t_file_summary[ 'class' ] = 'col-md-4';
		$t_file_summary[ 'head' ] = array(
			0 => array( 'class' => '', 'column' => 'User' ),
			1 => array( 'class' => '', 'column' => 'Downloads' ),
		);
		foreach( $t_summary_by_file as $key => $value ) {
			$t_file_summary[ 'row' ][] = array( 'column' => array(
				0 => array( 'class' => '', 'value' => $key ),
				1 => array( 'class' => '', 'value' => $value )),
			);
		}
		$t_file_summary[ 'updated' ] = date( 'y-m-d H:i:s' );

		// Download file groups form
		$t_file_groups = $this->download_log_model->get_file_groups();
		$t_form_data[ 'variable' ] = '	<div id="legend" class="btn btn-default">Choose download</div>
		<div class="form-content row" style="display:block;">
			<form action="download_log" method="post" id="dmlog-form" class="form-horizontal" accept-charset="UTF-8">
				<div class="form-group">
					<label class="col-sm-2 control-label">Select download</label>
					<div class="col-sm-4">' . PHP_EOL;
		$t_form_data[ 'variable' ] .= form_dropdown( 'group', $t_file_groups, $p_group,
			array( 'class' => 'form-control', 'onchange' => 'this.form.submit();' ));
		$t_form_data[ 'variable' ] .= '					</div>
				</div>
			</form>
		</div>' . PHP_EOL;
		$t_form_data[ 'variable_post' ] = '';

		$this->parser->parse( 'header', $data );
		$this->parser->parse( 'row-start', array());
		$this->parser->parse( 'table', $t_table_data );
		$this->parser->parse( 'row-stop', array());
		$this->parser->parse( 'row-start', array());
		$this->parser->parse( 'table', $t_user_summary );
		$this->parser->parse( 'table', $t_file_summary );
		$this->parser->parse( 'row-stop', array());
		$this->parser->parse( 'form', $t_form_data );
		$this->parser->parse( 'footer', $data );
	}
}

/* End of file Download_log.php */
/* Location: ./application/controllers/Download_log.php */
