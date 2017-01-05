<?php

/**
 * Machine Model
 *
 * @author Murray Crane <murray.crane@ggpsystems.co.uk>
 * @copyright 2016 (c) GGP Systems Limited
 * @license http://www.gnu.org/licenses/gpl.html
 * @version 1.0
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

		$this->db->select( 'machine.machine_id, machine.name, machine.description, machine.os, machine.cpu, machine.ram, machine.diskspace, machine.powered, machine.rdp_sessions, machine.type, machine.location, machine.comment, machine.ipv4_address, machine.mac_address, machine.last_backup, machine.periodicity, machine.bookable' )->from( 'machine' )->where( 'machine.deleted', 0 )->order_by( 'machine.name' );
		$query = $this->db->get();
		if( $query->num_rows() > 0 ) {
			$i = 1;
			foreach( $query->result_array() as $row ) {
				$t_note = $this->Booking_model->get_booking( $row[ 'machine_id' ] );

				$software = $this->Machine_model->get_software_list( $row[ 'machine_id' ] );
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
				$configuration = implode( " | ", $configuration_array );
				$description_array = explode( " | ", $row[ 'description' ] );
				$further = implode( " | ", array_slice( $description_array, 2 ) );
				$types_array = explode( ",", $row[ 'type' ] );
				$type_string = "";
				foreach( $types_array as $value ) {
					$type_string .= sprintf( '<span id="sprite" style="float: right;"><img id="%s" src="/assets/images/spritesheet.png" width="0" height="1" title="%s" alt="%s" /></span>', $type[ $value ][ 'colour' ], $type[ $value ][ 'name' ], $type[ $value ][ 'name' ] );
				}
				if( $row[ 'location' ] == "RoadWarrior" ) {
					$t_name = '<span style="color: green;"><strong>' . $row[ 'name' ] .'</strong></span>';
				} elseif( $row[ 'powered' ] == '0' ) {
					$t_name = '<span style="color: red;"><em>' . $row[ 'name' ] .'</em></span>';
				} else {
					$t_name = $t_name = $row[ 'name' ];
				}
				$t_return[] = array(
					'class' => $i,
					'name' => ($row[ 'powered' ] == '0' ? '<span style="color: red;">' . $row[ 'name' ] . '</span>' : $row[ 'name' ]) . "&nbsp;" . $type_string,
					'os' => $row[ 'os' ],
					'configuration' => ( $configuration != "" ? '<span style="width: 500px;">' . $configuration . '</span>' : ""),
					'description' => $description_array[ 0 ],
					'further' => ($further != "" ? '<span style="width: 500px;">' . $further . '</span>' : ""),
					'backup' => ( $row[ 'last_backup' ] != "0000-00-00" ? $row[ 'last_backup' ] . '&nbsp;' . sprintf( '[%s]', $backup_period[ $row[ 'periodicity' ] ][ 'name' ][ 0 ] ) : "" ),
					'ipv4' => ( $row[ 'ipv4_address' ] != "" ? $row[ 'ipv4_address' ] : "" ),
					'mac' => ( $row[ 'mac_address' ] != "" ? '<span style="width: 500px;">' . $row[ 'mac_address' ] . '</span>' : "" ),
					'software' => $software,
					'booking' => ( $row[ 'bookable' ] == 1 ? ( $t_note != NULL ? $t_note : form_checkbox( 'machines[]', $row[ 'machine_id' ], set_checkbox( 'machines[]', $row[ 'machine_id' ] ) )) : ""),
				);
				($i == 1 ? $i++ : $i--);
			}
		}

		return $t_return;
	}

	function get_name_from_id( $machine_id )
	{
		$this->db->select( 'machine.name' )->from( 'machine' )->where( 'machine.machine_id', $machine_id);
		return $this->db->get()->row()->name;
	}

	function get_software_list( $p_machine_id )
	{
		$this->db->select( 'software.name' )->from( 'software' )->where( 'software.machine_id', $p_machine_id );
		$t_query = $this->db->get();
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