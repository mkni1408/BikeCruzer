<?php
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Origin: *");

require 'Slim/Slim.php';
//will get the database var from here
require_once '../../config/db.php';

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();


$app->get('/getPOITest', function() use ($app, $database){
	echo "its working!!!";
});

$app->get('/getPOIs/:currentLat/:currentLng/:radius', function($currentLat, $currentLng, $radius) use ($app, $database){

	$POIArray = $database->select("POI",array( 
		// The row author_id from table post is equal the row user_id from table account
		"[>]Coordinates" => array("fk_id" => "id")
				), array(
					"POI.id",
					"POI.description",
					"POI.fk_id",
					"Coordinates.lat",
					"Coordinates.lng",
					"Coordinates.name"
				), array(
					"Coordinates.relatedto" => 0
		));


	echo json_encode($POIArray);
});

$app->get('/getPOIsWithinRadius/:currentLat/:currentLng/:radius', function($currentLat, $currentLng, $radius) use ($app, $database){


	// echo("SELECT * , ACOS( SIN( RADIANS( `lat` ) ) * SIN( RADIANS( $currentLat ) ) + 
	// 	COS( RADIANS( `lat` ) ) * COS( RADIANS( $currentLat ) ) * COS( RADIANS( `lng` ) - RADIANS( $currentLng ) ) ) * 
	// 6380 AS `distance` FROM `POI` LEFT JOIN `Coordinates` ON `POI.fk_id` = `Coordinates.id` WHERE ACOS( SIN( RADIANS( `lat` ) ) * 
	// 	SIN( RADIANS( $currentLat ) ) + COS( RADIANS( `lat` ) ) * COS( RADIANS( $currentLat )) * COS( RADIANS( `lng` ) - RADIANS( $currentLng ) ) ) * 
	// 6380 < $radius ORDER BY `distance`");

	$POIArray = $database->query("SELECT * , ACOS( SIN( RADIANS( `lat` ) ) * SIN( RADIANS( $currentLat ) ) + COS( RADIANS( `lat` ) ) * COS( RADIANS( $currentLat ) ) * COS( RADIANS( `lng` ) - RADIANS( $currentLng ) ) ) * 6380 AS `distance` FROM `POI` LEFT JOIN `Coordinates` ON POI.fk_id = Coordinates.id WHERE ACOS( SIN( RADIANS( `lat` ) ) * SIN( RADIANS( $currentLat ) ) + COS( RADIANS( `lat` ) ) * COS( RADIANS( $currentLat )) * COS( RADIANS( `lng` ) - RADIANS( $currentLng ) ) ) * 6380 < $radius ORDER BY `distance`")->fetchAll();

	//print_r($database->info());


	echo json_encode($POIArray);
});

$app->post('/addPOI', function() use ($app, $database){
	$lat = $app->request->post('lat');
	$lng = $app->request->post('lng');
	$name = $app->request->post('name');
	$description = $app->request->post('description');
	
	if(!empty($lat) && !empty($lng) && !empty($name))
	{
		
		$newCoordinate = $database->insert("Coordinates", array(
			"lat" => $lat,
			"lng" => $lng,
			"description" => $description,
			"relatedto" => "POI",
			"name" => $name
		));

		$newPOI = $database->insert("POI", array(
			"description" => $description,
			"fk_id" => $newCoordinate
		));



		//do some inserting
		echo "Coordinate inserted with coordinateID = " + $newCoordinate;
		echo " POI inserted with POI id = " + $newPOI;
	}
	else
	{
		echo "Not a valid joke";
	}
});

$app->run();

?>