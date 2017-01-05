<?php

/**
 * Intranet Model
 *
 * @author Murray Crane <murray.crane@ggpsystems.co.uk>
 * @copyright 2016 (c) GGP Systems Limited
 * @license http://www.gnu.org/licenses/gpl.html
 * @version 1.0
 */
class Intranet_model extends CI_Model
{
	function __construct()
	{
		parent::__construct();
	}

	function get_departments()
	{
		$return = array();
		$this->db->select( 'name' )->from( 'departments' );
		$query = $this->db->get();
		if( $query->num_rows() > 0 ) {
			foreach( $query->result_array() as $row ) {
				$return[] = array( 'dept' => $row[ 'name' ] );
			}
		}
		return $return;
	}

	function get_staff( $p_order = 1, $p_show_externals = FALSE )
	{
		$return = array();
		$this->db->select( 'staff.staff_id, staff.name, staff.display_midname, staff.start_date, staff.end_date, staff.xmpp, extensions.name AS extn,  departments.name AS dept' )->from( 'staff' )->join( 'extensions', 'extensions.extn_id = staff.extn_id' )->join( 'departments', 'departments.dept_id = staff.dept_id' );
		switch( $p_order ) {
			case 4:
				$this->db->order_by( 'departments.name' )->order_by( 'staff.firstname' )->order_by( 'staff.surname' )->order_by( 'extensions.name' );
				break;
			case 3:
				$this->db->order_by( 'extensions.name' );
				break;
			case 2:
				$this->db->order_by( 'staff.surname' )->order_by( 'staff.firstname' )->order_by( 'extensions.name' );
				break;
			case 1:
			default:
				$this->db->order_by( 'staff.firstname' )->order_by( 'staff.surname' )->order_by( 'extensions.name' );
		}
		$query = $this->db->get();
		if( $query->num_rows() > 0 ) {
			$i = 1;
			foreach( $query->result_array() as $row ) {
				if(( $row[ 'start_date' ] == "0000-00-00"
						|| time() >= $this->ggp_helper->day_start( $row[ 'start_date' ]))
					&& ( $row[ 'end_date' ] == "0000-00-00"
						|| time() <= $this->ggp_helper->day_end( $row[ 'end_date' ]))) {
					$return[] = array(
						'class' => $i,
						'extn' => $row[ 'extn' ],
						'name' => $row[ 'name' ],
						'externals' => ($p_show_externals) ? $this->get_externals($row[ 'staff_id' ] ) : "",
						'dept' => $row[ 'dept' ],
						'presence' => $this->get_presence( $row[ 'xmpp' ]),
					);
					($i == 1 ? $i++ : $i--);
				}
			}
		}

		return $return;
	}

	function get_externals( $p_staff_id )
	{
		$this->db->select( 'description, name' )->from( 'telephones' )->where( 'staff_id', $p_staff_id );
		$query = $this->db->get();
		$t_externals = "";
		if( $query->num_rows() > 0 ) {
			foreach( $query->result_array() as $row ) {
				if( !empty( $t_externals ) ) {
					$t_externals .= "<br />";
				}
				$t_externals .= $row[ 'description' ] . ": " . $row[ 'name' ];
			}
		}

		return $t_externals;
	}

	function get_presence( $jid = null )
	{
		$status_id = "dimgrey";

		if( ENVIRONMENT == 'production' and $jid != null ) {
			$ch = curl_init( "http://svn.ggpsystems.co.uk:5280/status/$jid/text" );
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$res = curl_exec($ch);
			curl_close($ch);
			if( $res !== false ) {
				switch( $res) {
					case "away":
					case "xa":
						$status_id = "darkgoldenrod";
						break;
					case "chat":
						$status_id = "ggpgreen";
						break;
					case "dnd":
						$status_id = "darkred";
						break;
					case "offline":
						$status_id = "dimgrey";
						break;
					case "online":
						$status_id = "ggpgreen";
						break;
				}
			}
		}

		return $status_id;
	}
}
