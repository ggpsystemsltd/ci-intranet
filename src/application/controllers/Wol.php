<?php

/**
 * Wake-on-LAN Controller
 *
 * @author Murray Crane <murray.crane@ggpsystems.co.uk>
 * @copyright 2017 (c) GGP Systems Limited
 * @license https://tldrlegal.com/license/bsd-3-clause-license-%28revised%29#fulltext
 * @version 2.1
 */
class Wol extends CI_Controller
{
	static $c_header = '<!DOCTYPE HTML>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<title>Wake-on-LAN (GGP intranet)</title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
	<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css"/>
	<link rel="stylesheet" media="screen" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap3-dialog/1.34.7/css/bootstrap-dialog.min.css" type="text/css" />
    <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
	<style type="text/css">body {width: 640px; font-family: Roboto, sans-serif; font-size: 14px;}</style>
    <script src="https://code.jquery.com/jquery-3.1.1.min.js" integrity="sha256-hVVnYaiADRTO2PzUGmuLJr8BLUSjGIZsDYGmIJLv2b8=" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js" type="application/javascript"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap3-dialog/1.34.7/js/bootstrap-dialog.min.js" type="text/javascript"></script>
</head>
<body>
	<div class="container">';

	static $c_footer = '	</div> <!-- Container -->
</body>
</html>';

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
			'intranet_heading' => 'Wake-on-LAN',
			'intranet_secondary' => date( 'd-m-Y' ),
			'intranet_user' => $_SERVER['INTRANET_USER'],
			'intranet_pass' => $_SERVER['INTRANET_PASS'],
			'author_name' => 'Murray Crane',
			'refresh' => '',
			'meta_description' => 'Wake-on-LAN page.',
			'keywords' => 'wake-on-lan, wol, magic packet',
			'remote_ip' => filter_input( INPUT_SERVER, 'REMOTE_ADDR', FILTER_VALIDATE_IP ),
			'author_mailto' => safe_mailto( 'murray.crane@ggpsystems.co.uk', 'Murray Crane' ),
			'style' => '',
			'javascript' => '',
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
			'wol_active' => ' class="active"',
			'wol_active_span' => '<span class="sr-only">(current)</span>',
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
		$this->parser->parse( 'navbar', $t_nav_data );
		$this->parser->parse( 'heading', $data );
		$this->parser->parse( 'inject', $t_form_open );
		$this->parser->parse( 'row-start', array());
		$this->parser->parse( 'table', $t_table_data );
		$this->parser->parse( 'row-stop', array());
		$this->parser->parse( 'inject', $t_form_close );
		$this->parser->parse( 'footer', $data );
	}

	# Wake on LAN - (c) HotKey@spr.at, upgraded by Murzik
	# Modified by Allan Barizo http://www.hackernotcracker.com
	public function wake_old()
	{
		$t_broadcast_address = "10.0.0.255";
		$t_socket_number = "7";
		$t_uri = '/'. explode( '/', uri_string() )[0];

		$p_values = unserialize($this->input->post( 'wake', true ));
		$t_name = $p_values[ 0 ];
		$t_mac = strtoupper( $p_values[ 1 ]);


		$t_address_byte = explode(':', $t_mac);
		$t_hardware_address = '';
		for ($i = 0; $i < 6; $i++) $t_hardware_address .= chr(hexdec($t_address_byte[$i]));
		$t_msg = chr(255) . chr(255) . chr(255) . chr(255) . chr(255) . chr(255);
		for ($i = 1; $i <= 16; $i++) $t_msg .= $t_hardware_address;
		// send it to the broadcast address using UDP
		// SQL_BROADCAST option isn't help!!
		$t_socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
		$t_return = false;
		if ($t_socket == false) {
			echo '	<script type="text/javascript">BootstrapDialog.alert({type: BootstrapDialog.TYPE_WARNING, message: \'Error creating socket!<br>Error code is ' . socket_last_error($t_socket) . ' - ' . socket_strerror(socket_last_error($t_socket)) . '\', callback: function(result){if(result) {window.location.replace(\'' . base_url('/' . $t_uri) . '\');}}});</script>' . PHP_EOL;
		} else {
			// setting a broadcast option to socket:
			$t_option_return = socket_set_option($t_socket, SOL_SOCKET, SO_BROADCAST, TRUE);
			if ($t_option_return < 0) {
				echo '	<script type="text/javascript">BootstrapDialog.alert({type: BootstrapDialog.TYPE_WARNING, message: \'setsockopt() failed, error:  ' . socket_strerror($t_option_return) . ' ' . socket_strerror(socket_last_error($t_socket)) . '\', callback: function(result){if(result) {window.location.replace(\'' . base_url('/' . $t_uri) . '\');}}});</script>' . PHP_EOL;
			}
			if (socket_sendto($t_socket, $t_msg, strlen($t_msg), 0, $t_broadcast_address, $t_socket_number)) {
				socket_close($t_socket);
			} else {
				echo '<script type="text/javascript">BootstrapDialog.alert({type: BootstrapDialog.TYPE_WARNING, message: \'Magic packet failed\', callback: function(result){if(result){window.location.replace(\'' . base_url('/' . $t_uri) . '\');}}});</script>' . PHP_EOL;
			}
		}
	}

	//public function wol($broadcast, $mac)
	public function wake()
	{
		$this->load->helper( array(  'url' ));

		$t_uri = '/'. explode( '/', uri_string() )[0];

		$p_values = unserialize($this->input->post( 'wake', true ));
		$t_name = $p_values[ 0 ];
		$t_mac = strtoupper( $p_values[ 1 ]);
		$broadcast = "10.0.0.255";

		$hwaddr = pack('H*', preg_replace('/[^0-9a-zA-Z]/', '', $t_mac) );

		// Create Magic Packet
		$packet = '';
		for ($i = 1; $i <= 6; $i++)
		{
			$packet .= chr(255);
		}

		for ($i = 1; $i <= 16; $i++)
		{
			$packet .= $hwaddr;
		}

		$sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
		if ($sock)
		{
			$options = socket_set_option($sock, 1, 6, true);

			if ($options >=0)
			{
				$e = socket_sendto($sock, $packet, strlen($packet), 0, $broadcast, 7);
				echo $this::$c_header . PHP_EOL;
				echo '<script type="text/javascript">BootstrapDialog.alert({message: \'Magic packet sent to wake  ' . $t_name . ' [' . $t_mac . ']\', callback: function(result){if(result){window.location.replace(\'' . base_url('/' . $t_uri) . '\');}}});</script>' . PHP_EOL;
				echo $this::$c_footer;
				socket_close($sock);
			}
		}
	}
}
/* End of file Wol.php */
/* Location: application/controllers/Wol.php */