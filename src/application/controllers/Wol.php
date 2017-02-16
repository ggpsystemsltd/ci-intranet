<?php

/**
 * Wake-on-LAN Controller
 *
 * @author Murray Crane <murray.crane@ggpsystems.co.uk>
 * @copyright 2017 (c) GGP Systems Limited
 * @license http://www.gnu.org/licenses/gpl.html
 * @version 2.0
 */
class Wol extends CI_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

	function index( )
	{
		$this->load->helper( array(  'url' ));
		$this->load->library( array( 'parser' ));
		$this->load->model( array( 'Machine_model' ));

		$data = array(
			'intranet_title' => 'Wake-on-LAN (GGP intranet)',
			'intranet_heading' => 'Wake-on-LAN',
			'intranet_secondary' => date( 'd-m-Y' ),
			'intranet_user' => $_SERVER['INTRANET_USER'],
			'intranet_pass' => $_SERVER['INTRANET_PASS'],
			'author_name' => 'Murray Crane',
			'refresh' => '',
			'meta_description' => 'Wake-on-LAN page.',
			'keywords' => 'wake-on-lan, wol, magic packet',
			'remote_ip' => filter_input( INPUT_SERVER, 'REMOTE_ADDR', FILTER_VALIDATE_IP ),
			'author_mailto' => safe_mailto( 'murray.crane@ggpsystems.co.uk', 'Murray Crane',
				array( 'class' => 'link-mailto' )),
			'style' => '',
			'javascript' => '',
		);

		$t_form_open['data'] = '			<form action="' . base_url( '/wol/wake' ) .
			'" method="post" id="wol-form" class="form-horizontal" accept-charset="utf-8">';
		$t_form_close['data'] = '			</form>';

		// Machine table
		$t_table_data[ 'title' ] = '';
		$t_table_data[ 'class' ] = 'col-md-3';
		$t_table_data[ 'head' ] = array(
			0 => array( 'column' => 'Machine' ),
			1 => array( 'column' => 'User' ),
			2 => array( 'column' => '' ),
		);
		$t_machines = $this->Machine_model->get_wol_list();
		foreach( $t_machines as $value ) {
			$t_table_data[ 'row' ][] = array( 'class' => '', 'column' => array(
				0 => array( 'class' => '', 'value' => $value[ 'm_name' ]),
				1 => array( 'class' => '', 'value' => $value[ 's_name' ]),
				2 => array( 'class' => '',
					'value' => '<button class="btn btn-default" name="wake" value="' .
						htmlspecialchars( serialize( array( $value[ 'm_name' ], $value[ 'mac_address' ] )))
						. '">Wake machine</button>')),
			);
		}
		$t_table_data[ 'updated' ] = $this->Machine_model->get_last_update();

		$this->parser->parse( 'header', $data );
		$this->parser->parse( 'inject', $t_form_open );
		$this->parser->parse( 'row-start', array());
		$this->parser->parse( 'table', $t_table_data );
		$this->parser->parse( 'row-stop', array());
		$this->parser->parse( 'inject', $t_form_close );
		$this->parser->parse( 'footer', $data );
	}

	# Wake on LAN - (c) HotKey@spr.at, upgraded by Murzik
	# Modified by Allan Barizo http://www.hackernotcracker.com
	public function wake()
	{
		$t_broadcast_address = "10.0.0.255";
		$t_socket_number = "7";

		$p_values = unserialize($this->input->post( 'wake', true ));
		$t_name = $p_values[ 0 ];
		$t_mac = strtoupper( $p_values[ 1 ]);

		echo 'Attempting to wake ' . $t_name . ' [' . $t_mac . ']'; // @todo do this with a dialog

		$t_address_byte = explode(':', $t_mac);
		$t_hardware_address = '';
		for ($i = 0; $i < 6; $i++) $t_hardware_address .= chr(hexdec($t_address_byte[$i]));
		$t_msg = chr(255) . chr(255) . chr(255) . chr(255) . chr(255) . chr(255);
		for ($i = 1; $i <= 16; $i++) $t_msg .= $t_hardware_address;
		// send it to the broadcast address using UDP
		// SQL_BROADCAST option isn't help!!
		$t_socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
		if ($t_socket == false) {
			echo 'Error creating socket!\n';
			echo "Error code is '" . socket_last_error($t_socket) . "' - " . socket_strerror(socket_last_error($t_socket)); // @todo do this with a dialog
			return FALSE;
		} else {
			// setting a broadcast option to socket:
			$t_option_return = socket_set_option($t_socket, SOL_SOCKET, SO_BROADCAST, TRUE);
			if ($t_option_return < 0) {
				// echo 'setsockopt() failed, error: ' . strerror($option_return) . "\n"; // @todo do this with a dialog
				return FALSE;
			}
			if (socket_sendto($t_socket, $t_msg, strlen($t_msg), 0, $t_broadcast_address, $t_socket_number)) {
				// echo 'Magic Packet sent successfully!'; // @todo do this with a dialog
				socket_close($t_socket);
				return TRUE;
			} else {
				// echo 'Magic packet failed!'; // @todo do this with a dialog
				return FALSE;
			}
		}
	}
}

/* End of file Wol.php */
/* Location: application/controllers/Wol.php */