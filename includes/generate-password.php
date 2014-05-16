<?php
/*
 * Copyright (C) 2014 Bellevue College
 *
 * This file is part of the WordPress CAS Client
 *
 * The WordPress CAS Client is free software; you can redistribute
 * it and/or modify it under the terms of the GNU General Public
 * License as published by the Free Software Foundation; either
 * version 2 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Bellevue College
 * Address: 3000 Landerholm Circle SE
 *          Room N215F
 *          Bellevue WA 98007-6484
 * Phone:   +1 425.564.4201
 */

/**
 * Modulo function that works with negative numbers
 *
 * PHP's standard modulo operator returns negative numbers as is without
 * modulus operation (ie -3 % 7 == -3 not the expected 3 % 7 == 4). This
 * function returns the expected negative module operation on negative numbers.
 *
 * @param int $num
 * @param int $mod
 * @return int
 */
function true_modulo( $num, $mod ) {
	return ( $mod + ( $num % $mod ) ) % $mod;
}

/**
 * Generate secure passwords of random length
 *
 * Generate passwords that have printable keyboard characters that are a
 * random length between the $min and $max paramters passed to the function.
 *
 * @param int $min the minimum length of the random password to generate
 * @param int $max the maximum length of the random password ro generate
 * @return string a random password that is a random length between the $min
 *                and $max paramter values
 */
function generate_password( $min, $max ) {
	$password = '';

	/*
	 * Generate cryptographically strong pseudo-random bytes as $bytes using the
	 * openssl_random_pseudo_bytes fuction. Number of bytes generated determined
	 * by $min and $max variables passed to this function.
	 */
	do {
		$bytes = openssl_random_pseudo_bytes( mt_rand( $min, $max ), $crypto_strong );
	} while ( false === $crypto_strong );

	/*
	 * Iterate through $bytes one byte at a time and modulus each byte's decimal
	 * value to be between 33 and 126 for valid keyboard characters on the ASCII
	 * table.
	 */
	foreach ( str_split( $bytes ) as $byte ) {
		$integer_value = ord( $byte );
		$integer_value = true_modulo( ( $integer_value - 33 ), 94 ) + 33;
		$password .= chr( $integer_value );
	}

	return $password;
}
