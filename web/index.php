<?php

ini_set( 'memory_limit', '20M' );
define( 'LONG_KEY', 'long' );
define( 'CANARY_KEY', 'canary' );
define( 'CANARY_VALUE', 'canary' );

$memcache = new Memcache();
$memcache->addServer( 'memcached', 11211, true, 1, 1, 15, true );
$memcache->setCompressThreshold( 20000, 0.2 );

// Run a request with ?set_values to setup things.
if ( isset( $_GET['set_values'] ) ) {
	echo( "Setting Values\n" );

	// This doesn't have to be huge to replicate the problem - could be normal size, it just makes it quicker to cause the oom.
	$memcache->set( LONG_KEY, 'longvalue_' . bin2hex( random_bytes( 5 * 1024 ) ), 0, 2592000 );
	$memcache->set( CANARY_KEY, CANARY_VALUE, 0, 2592000 );
	exit();
}

$canary = $memcache->get( CANARY_KEY );
if ( CANARY_VALUE !== $canary ) {
	echo( "TRIGGERED\n" );
	if ( strpos( $canary, 'longvalue_' ) === 0 ) {
		echo( 'Returned LONG_KEY when CANARY_KEY was requested.' );
	} elseif ( empty( $canary ) ) {
		echo( 'Returned empty response.' );
	}

	http_response_code( 400 );
	exit();
} else {
	echo( "WORKING\n" );
}

// Trigger an OOM by reading the same big memcached value over and over
if ( ! isset( $_GET['no_oom'] ) ) {
	$garbage = array();
	while ( 1 ) {
		$garbage[] = $memcache->get( LONG_KEY );;
	}
}

echo( "FINISHED\n" );
