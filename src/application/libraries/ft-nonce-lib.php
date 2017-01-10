<?php
/*
 * Name: FT-NONCE-LIB
 * Created By: Full Throttle Development, LLC (http://fullthrottledevelopment.com)
 * Created On: July 2009
 * Last Modified On: August 12, 2009
 * Last Modified By: Glenn Ansley (glenn@fullthrottledevelopment.com)
 * Version: 0.2
 */

/* 
Copyright 2009 Full Throttle Development, LLC

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

class ft_nonce {
	const FT_NONCE_UNIQUE_KEY = '';
	const FT_NONCE_DURATION = 300; // 300 makes link or form good for 5 minutes from time of generation
	const FT_NONCE_KEY = '_nonce';
	const FT_NONCE_ALGO = 'sha512';

	function __construct() {
		if ( self::FT_NONCE_UNIQUE_KEY == '' ){
			die( 'You must enter a unique key on line 2 of ft_nonce_lib.php to use this library.');
		}
	}

// This method creates a key / value pair for a url string
	function create_query_string( $action = '' , $user = '' ){
		return self::FT_NONCE_KEY."=".$this->create( $action , $user );
	}

// This method creates an nonce for a form field
	function create_form_input( $action = '' , $user='' ){
		echo "<input type='hidden' name='".self::FT_NONCE_KEY."' value='".$this->create( $action . $user )."' />";
	}

// This method creates an nonce. It should be called by one of the previous two functions.
	function create( $action = '' , $user='' ){
		return substr( $this->generate_hash( $action . $user ), -12, 10);
	}

// This method validates an nonce
	function is_valid( $nonce , $action = '' , $user='' ){
		// Nonce generated 0-12 hours ago
		if ( substr($this->generate_hash( $action . $user ), -12, 10) == $nonce ){
			return true;
		}
		return false;
	}

// This method generates the nonce timestamp
	function generate_hash( $action='' , $user='' ){
		$i = ceil( time() / ( self::FT_NONCE_DURATION / 2 ) );
		return hash( self::FT_NONCE_ALGO, $i . $action . $user . $action );
	}
}