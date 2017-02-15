<?php

/**
 * Intranet Model
 *
 * @author Murray Crane <murray.crane@ggpsystems.co.uk>
 * @copyright 2017 (c) GGP Systems Limited
 * @license http://www.gnu.org/licenses/gpl.html
 * @version 2.0
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
		$this->db->select( 'name' );
		$query = $this->db->get( 'departments' );
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
		$this->db->select( 'staff.staff_id, staff.name, staff.display_midname, staff.start_date, staff.end_date, 
			staff.xmpp, extensions.name AS extn,  departments.name AS dept' );
		$this->db->join( 'extensions', 'extensions.extn_id = staff.extn_id' );
		$this->db->join( 'departments', 'departments.dept_id = staff.dept_id' );
		switch( $p_order ) {
			case 4:
				$this->db->order_by( 'departments.name' );
				$this->db->order_by( 'staff.firstname' );
				$this->db->order_by( 'staff.surname' );
				$this->db->order_by( 'extensions.name' );
				break;
			case 3:
				$this->db->order_by( 'extensions.name' );
				break;
			case 2:
				$this->db->order_by( 'staff.surname' );
				$this->db->order_by( 'staff.firstname' );
				$this->db->order_by( 'extensions.name' );
				break;
			case 1:
			default:
				$this->db->order_by( 'staff.firstname' );
				$this->db->order_by( 'staff.surname' );
				$this->db->order_by( 'extensions.name' );
		}
		$query = $this->db->get( 'staff' );
		if( $query->num_rows() > 0 ) {
			foreach( $query->result_array() as $row ) {
				if(( $row[ 'start_date' ] == "0000-00-00"
						|| time() >= $this->day_start( $row[ 'start_date' ]))
					&& ( $row[ 'end_date' ] == "0000-00-00"
						|| time() <= $this->day_end( $row[ 'end_date' ]))) {
					$return[] = array(
						'class' => $this->get_presence( $row[ 'xmpp' ]),
						'extn' => $row[ 'extn' ],
						'name' => $row[ 'name' ],
						'externals' => ($p_show_externals) ? $this->get_externals($row[ 'staff_id' ] ) : "",
						'dept' => $row[ 'dept' ],
					);
				}
			}
		}

		return $return;
	}

	function get_externals( $p_staff_id )
	{
		$this->db->select( 'description, name' );
		$this->db->where( 'staff_id', $p_staff_id );
		$query = $this->db->get( 'telephones' );
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

	private function get_presence( $jid = null )
	{
		if( $jid != null ) {
			$ch = curl_init( "http://svn.ggpsystems.co.uk:5280/status/$jid/text" );
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$res = curl_exec($ch);
			curl_close($ch);
			if( $res !== false ) {
				switch( $res) {
					case "away":
					case "xa":
						return "warning";
						break;
					case "chat":
					case "online":
						return "success";
						break;
					case "dnd":
						return "danger";
						break;
					case "offline":
					default:
						return "active";
						break;
				}
			}
		}

		return false;
	}

	private function day_start($p_date)
	{
		$t_date = explode("-", $p_date);
		return mktime(0, 0, 0, $t_date[1], $t_date[2], $t_date[0]);
	}

	private function day_end($p_date)
	{
		$t_date = explode("-", $p_date);
		return mktime(23, 59, 59, $t_date[1], $t_date[2], $t_date[0]);
	}
}

/* End of file Intranet_model.php */
/* Location: application/models/Intranet_model.php */