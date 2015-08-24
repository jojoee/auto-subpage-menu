<?php

if ( ! function_exists( 'dd' ) ) {
	function dd( $var, $die = true ) {
		echo '<pre>';
		print_r( $var );
		echo '</pre>';

		if ( $die ) { die(); }
	}
}

if ( ! function_exists( 'da' ) && function_exists( 'dd' ) ) {
	function da( $var ) {
		dd( $var, false );
	}
}
