<?php

ini_set( 'memory_limit', '20M' );
define( 'LONG_KEY', 'long' );
define( 'CANARY_KEY', 'canary' );
define( 'CANARY_VALUE', 'canary' );

// NOTE: needs shared connections / persistency enabled to replicate.
$memcache = new Memcache();
$persistent = 1;
$memcache->addServer( 'memcached', 11211, true, $persistent, 1, 15, true );
$memcache->setCompressThreshold( 20000, 0.2 );

// Run a request with ?set_keys to setup things.
if ( isset( $_GET['set_keys'] ) ) {
	trigger_error( 'Setting up key caches', E_USER_NOTICE );

	// This doesn't have to be huge to replicate the problem - could be normal size, it just makes it quicker to cause the oom.
	$memcache->set( LONG_KEY, 'longvalue_' . bin2hex( random_bytes( 5 * 1024 ) ), 0, 2592000 );
	$memcache->set( CANARY_KEY, CANARY_VALUE, 0, 2592000 );
	exit();
}

$canary = $memcache->get( CANARY_KEY );
if ( CANARY_VALUE !== $canary && ! empty( $canary ) ) {
	$received_value = strpos( $canary, 'longvalue_' ) === 0 ? ' Received LONG_KEY value.' : " Received value: $canary.";
	$request_info = isset( $_GET['thread'], $_GET['iteration'] ) ? " Thread: #{$_GET['thread']}. Iteration: #{$_GET['iteration']}." : '';

	trigger_error( "Invalid value returned for canary.$received_value$request_info", E_USER_WARNING );
}

// Trigger an OOM by reading the same big memcached value over and over
if ( ! isset( $_GET['no_oom'] ) ) {
	$garbage = array();
	while ( 1 ) {
		$garbage[] = $memcache->get( LONG_KEY );;
	}
}

echo( "FINISHED\n" );
