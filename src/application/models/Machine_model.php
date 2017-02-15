<?php

/**
 * Machine Model
 *
 * @author Murray Crane <murray.crane@ggpsystems.co.uk>
 * @copyright 2017 (c) GGP Systems Limited
 * @license http://www.gnu.org/licenses/gpl.html
 * @version 2.0
 */
class Machine_model extends CI_Model
{
	function __construct()
	{
		parent::__construct();
	}

	function get_machine_list()
	{
		$type = array(
			'desktop' => array(
				'name' => 'Desktop',
				'colour' => 'darkblue'
			),
			'laptop' => array(
				'name' => 'Laptop',
				'colour' => 'darkcyan'
			),
			'vm' => array(
				'name' => 'VM',
				'colour' => 'darkmagenta'
			),
			'software' => array(
				'name' => 'Software',
				'colour' => 'dimgrey'
			),
			'server' => array(
				'name' => 'Server',
				'colour' => 'ggpgreen'
			),
			'bds' => array(
				'name' => 'Delphi',
				'colour' => 'chocolate'
			)
		);

		$backup_period = array(
			array(
				'name' => 'Weekly',
				'colour' => 'darkgoldenrod'
			),
			array(
				'name' => 'Bi-weekly',
				'colour' => 'goldenrod'
			),
			array(
				'name' => 'Monthly',
				'colour' => 'gold'
			),
			array(
				'name' => 'Quarterly',
				'colour' => 'palegoldenrod'
			)
		);

		$t_return = array();

		$this->db->select( 'machine.machine_id, machine.name, machine.description, machine.os, machine.cpu, machine.ram, 
		machine.diskspace, machine.powered, machine.rdp_sessions, machine.type, machine.location, machine.comment, 
		machine.ipv4_address, machine.mac_address, machine.last_backup, machine.periodicity, machine.bookable' );
		$this->db->where( 'machine.deleted', 0 );
		$this->db->order_by( 'machine.name' );
		$query = $this->db->get( 'machine' );
		if( $query->num_rows() > 0 ) {
			foreach( $query->result_array() as $row ) {
				$t_note = $this->Booking_model->get_booking( $row[ 'machine_id' ] );
				$t_software = $this->Machine_model->get_software_list( $row[ 'machine_id' ] );
				$configuration_array = array();
				if( $row[ 'ram' ] != "" ) {
					$configuration_array[] = $row[ 'ram' ];
				}
				if( $row[ 'cpu' ] != "" ) {
					$configuration_array[] = $row[ 'cpu' ];
				}
				if( $row[ 'diskspace' ] != "" ) {
					$configuration_array[] = $row[ 'diskspace' ];
				}
				$t_configuration = implode( " | ", $configuration_array );
				$description_array = explode( " | ", $row[ 'description' ] );
				$t_further = implode( " | ", array_slice( $description_array, 2 ) );
				$t_types = "";
				$t_types_array = explode( ",", $row[ 'type' ] );
				sort( $t_types_array );
				foreach( $t_types_array as $value ) {
					$t_types .= ( !empty( $t_types )) ? ', ' : null;
					$t_types .= $type[ $value ][ 'name' ];
				}
				if( $row[ 'location' ] == "RoadWarrior" ) {
					$t_class = 'class="success"';
				} elseif( $row[ 'powered' ] == '0' ) {
					$t_class = 'class="danger"';
				} else {
					$t_class = null;
				}
				$t_os = '';
				$t_postfix = '';
				if( !empty( $t_configuration )) {
					$t_os .= '<abbr title="' . $t_configuration . '">';
					$t_postfix = '</abbr>';
				}
				$t_os .= $row[ 'os' ] . $t_postfix;

				$t_description = '';
				$t_postfix = '';
				if( !empty( $t_further )) {
					$t_description .= '<abbr title="' . $t_further . '">';
					$t_postfix = '</abbr>';
				}
				$t_description .= $description_array[ 0 ] . $t_postfix;

				$t_ipv4 = '';
				$t_postfix = '';
				if( !empty( $row[ 'mac_address' ] )) {
					$t_ipv4 .= '<abbr title="' . $row[ 'mac_address' ] . '">';
					$t_postfix = '</abbr>';
				}
				$t_ipv4 .= $row[ 'ipv4_address' ] . $t_postfix;

				$t_return[] = array(
					'class' => $t_class,
					'backup' => ( $row[ 'last_backup' ] != "0000-00-00" ? $row[ 'last_backup' ] . '&nbsp;' . sprintf( '[%s]', $backup_period[ $row[ 'periodicity' ] ][ 'name' ][ 0 ] ) : null ),
					'name' => '<abbr title="' . $t_types . '">' . $row[ 'name' ] . '</abbr>',
					'os' => $t_os,
					'description' => $t_description,
					'ipv4' => ( !empty( $t_ipv4 )) ? $t_ipv4 : null,
					'software' => $t_software,
					'booking' => ( $row[ 'bookable' ] == 1 ? ( $t_note != NULL ? $t_note : form_checkbox( 'machines[]', $row[ 'machine_id' ], set_checkbox( 'machines[]', $row[ 'machine_id' ] ) )) : ""),
				);
			}
		}

		return $t_return;
	}

	function get_name_from_id( $machine_id )
	{
		$this->db->select( 'machine.name' );
		$this->db->where( 'machine.machine_id', $machine_id);
		return $this->db->get( 'machine' )->row()->name;
	}

	function get_software_list( $p_machine_id )
	{
		$this->db->select( 'software.name' );
		$this->db->where( 'software.machine_id', $p_machine_id );
		$t_query = $this->db->get( 'software' );
		if ( $t_query->num_rows() > 0 ) {
			foreach( $t_query->result_array() as $t_row ) {
				$t_software_array[] = $t_row[ 'name' ];
			}
			$t_software_list = implode( ", ", $t_software_array  );
		} else {
			$t_software_list = "";
		}

		return $t_software_list;
	}
}

/* End of file Machine_model.php */
/* Location: application/models/Machine_model.php */