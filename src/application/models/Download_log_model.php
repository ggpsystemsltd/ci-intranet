<?php

class Download_log_model extends CI_Model
{
	function __construct()
	{
		parent::__construct();
	}

	function get_downloads( $p_download_ids )
	{
		$t_return = array();
		$t_database = $this->db->database;
		$this->db->db_select( 'wpggpsystems' );
		$this->db->select( 'log.date, user.user_email, file.title' );
		$this->db->from( 'wp_ggp_download_monitor_log AS log' );
		$this->db->join( 'wp_ggp_users AS user', 'user.id=log.user_id' );
		$this->db->join( 'wp_ggp_download_monitor_files AS file', 'file.id=log.download_id' );
		$this->db->where( 'log.download_id IN (' . $p_download_ids . ')' );
		$this->db->where( 'log.ip_address NOT LIKE ("10.0.0.%")' );
		$this->db->order_by( 'log.date' );
		$t_query = $this->db->get();
		if( $t_query->num_rows() > 0 ) {
			foreach( $t_query->result_array() as $row ) {
				$t_return[] = $row;
			}
		}
		$this->db->db_select( $t_database );
		return $t_return;
	}

	function get_file_groups()
	{
		$t_return = array();
		$this->db->select( 'description, members' );
		$this->db->from( 'download_monitor_groups' );
		$this->db->order_by( 'updated DESC' );
		$t_query = $this->db->get();
		if( $t_query->num_rows() > 0 ) {
			foreach( $t_query->result_array() as $row ) {
				$t_return[$row[ 'members' ]] = $row[ 'description' ];
			}
		}
		return $t_return;
	}

	function get_last_group()
	{
		$this->db->select_max( 'group_id' );
		$this->db->select( 'members' );
		$t_query = $this->db->get( 'download_monitor_groups' );
		if( $t_query->num_rows() >0 ) {
			foreach( $t_query->result_array() as $value ) {
				return $value[ 'members' ];
			}
		}
		return false;
	}
}
