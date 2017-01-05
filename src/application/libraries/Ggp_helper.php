<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/* 
 * Helper functions for the GGP CodeIgniter intranet
 */

class Ggp_helper
{
	public function day_start($p_date)
	{
		$t_date = explode("-", $p_date);
		return mktime(0, 0, 0, $t_date[1], $t_date[2], $t_date[0]);
	}

	public function day_end($p_date)
	{
		$t_date = explode("-", $p_date);
		return mktime(23, 59, 59, $t_date[1], $t_date[2], $t_date[0]);
	}
}
/* End of file Ggp_helper.php */
/* Location: application/libraries/Ggp_helper.php */

