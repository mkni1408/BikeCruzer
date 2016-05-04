<?php
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Origin: *");

require 'Slim/Slim.php';
//will get the database var from here
require_once '../../config/db.php';

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();


$app->get('/getVolumeTest', function() use ($app, $database){
	echo "its working!!!";
});

$app->get('/getVolumeRoutes', function() use ($app, $database){

	$routeArray = array();

	$StartCoordinateArray = $database->select("VolumeRoutes",array( 
		// The row author_id from table post is equal the row user_id from table account
		"[>]Coordinates" => array("fk_key_cord_start" => "id")
				), array(
					"VolumeRoutes.id",
					"VolumeRoutes.description",
					"VolumeRoutes.fk_key_cord_start",
					"Coordinates.lat",
					"Coordinates.lng"
				), array(
					"Coordinates.relatedto" => 1
		));


		$EndCoordinateArray = $database->select("VolumeRoutes",array( 
		// The row author_id from table post is equal the row user_id from table account
		"[>]Coordinates" => array("fk_key_cord_end" => "id")
				), array(
					"VolumeRoutes.id",
					"VolumeRoutes.description",
					"VolumeRoutes.volume",
					"VolumeRoutes.fk_key_cord_end",
					"Coordinates.lat",
					"Coordinates.lng",
				), array(
					"Coordinates.relatedto" => 1
		));

	for ($i = 0; $i <  sizeof($StartCoordinateArray); $i++){ 

		array_push($routeArray, array(
			'id' => $StartCoordinateArray[$i]["id"], 
			'latStart' => $StartCoordinateArray[$i]['lat'],
			'lngStart' => $StartCoordinateArray[$i]['lng'],
			'latEnd' => $EndCoordinateArray[$i]['lat'],
			'lngEnd' => $EndCoordinateArray[$i]['lng'],
			'desc' => $StartCoordinateArray[$i]['description'],
			'volume' => $EndCoordinateArray[$i]['volume']
		));
	}


	echo json_encode($routeArray);
	// echo json_encode($StartCoordinateArray);
	// echo json_encode($EndCoordinateArray);
});

// $app->get('/getPOIsWithinRadius/:currentLat/:currentLng/:radius', function($currentLat, $currentLng, $radius) use ($app, $database){


// 	// echo("SELECT * , ACOS( SIN( RADIANS( `lat` ) ) * SIN( RADIANS( $currentLat ) ) + 
// 	// 	COS( RADIANS( `lat` ) ) * COS( RADIANS( $currentLat ) ) * COS( RADIANS( `lng` ) - RADIANS( $currentLng ) ) ) * 
// 	// 6380 AS `distance` FROM `POI` LEFT JOIN `Coordinates` ON `POI.fk_id` = `Coordinates.id` WHERE ACOS( SIN( RADIANS( `lat` ) ) * 
// 	// 	SIN( RADIANS( $currentLat ) ) + COS( RADIANS( `lat` ) ) * COS( RADIANS( $currentLat )) * COS( RADIANS( `lng` ) - RADIANS( $currentLng ) ) ) * 
// 	// 6380 < $radius ORDER BY `distance`");

// 	$POIArray = $database->query("SELECT * , ACOS( SIN( RADIANS( `lat` ) ) * SIN( RADIANS( $currentLat ) ) + COS( RADIANS( `lat` ) ) * COS( RADIANS( $currentLat ) ) * COS( RADIANS( `lng` ) - RADIANS( $currentLng ) ) ) * 6380 AS `distance` FROM `POI` LEFT JOIN `Coordinates` ON POI.fk_id = Coordinates.id WHERE ACOS( SIN( RADIANS( `lat` ) ) * SIN( RADIANS( $currentLat ) ) + COS( RADIANS( `lat` ) ) * COS( RADIANS( $currentLat )) * COS( RADIANS( `lng` ) - RADIANS( $currentLng ) ) ) * 6380 < $radius ORDER BY `distance`")->fetchAll();

// 	//print_r($database->info());


// 	echo json_encode($POIArray);
// });

$app->post('/addRouteVolume', function() use ($app, $database){
	//get vars
	$latStart = $app->request->post('latStart');
	$lngStart = $app->request->post('lngStart');
	$latEnd = $app->request->post('latEnd');
	$lngEnd = $app->request->post('lngEnd');
	$desc = $app->request->post('desc');
	$vol = $app->request->post('vol');

	
	if(!empty($latStart) && !empty($lngStart) && !empty($latEnd) && !empty($lngEnd) && !empty($vol)) 
	{
		
		$StartCoordId = $database->insert("Coordinates", array(
			"lat" => $latStart,
			"lng" => $lngStart,
			"description" => $desc,
			"relatedto" => 1,
			"name" => ""
		));

		$EndCoordId = $database->insert("Coordinates", array(
			"lat" => $latEnd,
			"lng" => $lngEnd,
			"description" => $desc,
			"relatedto" => 1,
			"name" => ""
		));

		$newVolId = $database->insert("VolumeRoutes", array(
			"fk_key_cord_start" => $StartCoordId,
			"fk_key_cord_end" => $EndCoordId,
			"Description" => $desc,
			"Volume" => $vol
		));



		//do some inserting
		echo "Start Coordinate inserted with coordinateStartID = " + $StartCoordId;
		echo "End Coordinate inserted with coordinateStartID = " + $EndCoordId;
		echo " Vol inserted with Vol id = " + $newVolId;
	}
	else
	{
		echo "Some post data missing, check if you posted everything";
	}
});

$app->run();

?>