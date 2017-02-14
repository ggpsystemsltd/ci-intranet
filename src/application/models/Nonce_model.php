<?php

class Nonce_model extends CI_Model
{
	function __construct()
	{
		parent::__construct();
	}

	function get_attendance()
	{
		$return = array();
		$this->db->select( 'staff.name, staff.work_state' )->from( 'staff' )->where( 'staff.active', 1 )->where( 'staff.work_state !=', 'NaN')->order_by( 'staff.firstname' );
		$query = $this->db->get();
		if( $query->num_rows() > 0 ) {
			$i = 1;
			foreach( $query->result_array() as $row ) {
				$return[] = array(
					'class' => $i,
					'name' => $row[ 'name' ],
					'attclass' => preg_replace('/\s/', '', strtolower($row[ 'work_state' ])),
					'attendance' => $row[ 'work_state' ],
				);
				($i == 1 ? $i++ : $i--);
			}
		}

		return $return;
	}

	public function get_crc32( $p_id = '', $p_data = '', $p_storeNonce = false )
	{
		$t_cnonce = hash( 'crc32b', $this->make_random_string());
		if( $p_storeNonce ) {
			$this->store_crc32( $p_id, hash( 'crc32b', $t_cnonce . $p_data ));
		}

		return $t_cnonce;
	}

	public function verify_crc32( $p_id = '', $p_cnonce = '', $p_data = '' )
	{
		$t_hash = $this->select_crc32( $p_id ); // Get the hash out of the db, immediately deleting it from the DB
		$t_testHash = hash( 'crc32b', $p_cnonce . $p_data );

		return $t_testHash == $t_hash;
	}

	private function store_crc32( $p_id, $p_nonce )
	{
		// Store the id and nonce in the db
		$this->db->where( 'holiday_id', $p_id );
		$this->db->update( 'holidays', array( 'nonce' => $p_nonce ));

		return true;
	}

	private function select_crc32( $p_id )
	{
		$t_return = false;

		// Select the nonce, THEN DELETE IT FROM THE REQUEST!!!
		$this->db->select( 'nonce' )->where( 'holiday_id', $p_id );
		$query = $this->db->get( 'holidays' );
		if( $query->num_rows() > 0 ) {
			foreach ( $query->result_array() as $row ) {
				$t_return = $row[ 'nonce' ];
			}
		}
		//$this->db->where( 'holiday_id', $p_id );
		//$this->db->update( 'holidays', array( 'nonce' => '' );

		return $t_return;
	}

	private function make_random_string($p_bits = 256)
	{
		$t_bytes = ceil($p_bits / 8);
		$t_return = '';
		for ($i = 0; $i < $t_bytes; $i++) {
			$t_return .= chr(mt_rand(0, 255));
		}
		return $t_return;
	}
}