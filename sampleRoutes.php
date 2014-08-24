<?php

function getSampleRoute($count=1) {
	$routes = array(
	array( 'start' => 'Albert Ayler', 'end' => 'Adam Ant' ),
	array( 'start' => 'Dethklok', 'end' => 'The Muppets' ),
	array( 'start' => 'Cat Stevens', 'end' => 'Snoop Dogg' ),
	array( 'start' => 'Ministry', 'end' => 'Morrissey' ),
	array( 'start' => 'Louis Armstrong', 'end' => 'Billie Joe Armstrong' ),
	array( 'start' => 'Gorillaz', 'end' => 'The Monkees' ),
	array( 'start' => 'Jaco Pastorius', 'end' => 'Sid Vicious' ),
	array( 'start' => 'Darby Crash', 'end' => "Terence Trent D'Arby" ),
	array( 'start' => 'Blackie Lawless', 'end' => 'Lucy Lawless' ),
	array( 'start' => 'ABBA', 'end' => 'ZZ Top' ),
	array( 'start' => 'Jeff Trott', 'end' => 'Rich Trott'),
	array( 'start' => 'Rob Zombie', 'end' => 'The Zombies'),
	array( 'start' => 'Boston', 'end' => 'Chicago'),
	array( 'start' => 'Circle', 'end' => 'Squarepusher'),
	array( 'start' => 'Yes', 'end' => 'NoMeansNo'),
	array( 'start' => 'Moby', 'end' => 'Moby Grape'),
	array( 'start' => 'Tesla', 'end' => 'Coil'),
	array( 'start' => 'Daft Punk', 'end' => '"Daft Punk Is Playing At My House"'),
	array( 'start' => 'Archers Of Loaf', 'end' => 'Bread'),
	array( 'start' => 'Bow Wow', 'end' => 'Bow Wow Wow'),
	array( 'start' => 'Cracker', 'end' => 'Uncle Kracker'),
	array( 'start' => 'Camper Van Beethoven', 'end' => 'Sebastian Bach'),
	array( 'start' => 'Alice Cooper', 'end' => 'Alice Coltrane'),
	array( 'start' => 'Sonic Youth', 'end' => 'Musical Youth'),
	array( 'start' => 'Tom Jones', 'end' => 'Jesus Jones'),
	array( 'start' => 'Thelonious Monk', 'end' => 'Thelonious Monster'),
	array( 'start' => 'Europe', 'end' => 'America'),
	array( 'start' => 'Gary Numan', 'end' => 'Randy Newman'),
	array( 'start' => 'X', 'end' => 'Xavier Cugat'),
	);
	shuffle($routes);
	return array_slice($routes, 0, $count);
}

?>
