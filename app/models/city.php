<?php
use Illuminate\Http\Response;


class City extends Eloquent {

	protected $table = 'cities';
	public $timestamps = false;
	// This is a reference table and nothing should be changed via the API
	// with the possible exception of status
	protected $hidden = array('id', 'name', 'state','latitude','longitude');


	//Get attributes of a city given name and state.
	public static function getCity($city,$state) {
		
		$thiscity=DB::table('cities')->select('id','name','latitude', 'longitude')
									->where('status', '=', 'verified')
									->where('state', '=', $state)
									->where('name', '=', $city)
									->first(); //unique name/state combination
		return 	$thiscity;
	}


	//Get a list of cities a given distance from the specified city.
	public static function getNearbyCities($state,$city,$miles){
			//NOTE: MySQL spatial functions talk about boundary rectangles, meaning they are not suitable 
			//      for long distances where the curvature of the earth must be considered.
			//TODO: Correct the latitude/longitude range calculations
			//		in case the latitude range includes a pole and/or the longitude range includes the prime meridian.
			//		This won't happen in the US, but the app may be expanded.
		

		//First find the city
		$thiscity=	City::getCity($city,$state);
								
		if ($thiscity==NULL) 	{ return $thiscity;} //We can't find the specified city much less anything nearby, return null.


		$latitude=$thiscity->latitude;
		$longitude=$thiscity->longitude;
		$correctedlongitude=$longitude+180; //longitude is -180 to +180, but we need 0-360 for the formula
		
		//These SHOULD be constants...
		$MILES_PER_DEGREE_LAT=69;
		$RADIUS_OF_EARTH=3959;

		$milesPerDegreeLong=$MILES_PER_DEGREE_LAT * cos(deg2rad($latitude));
		
		//Get a square of latitude/longitude ranges +/- $miles from the city.
		//We don't have to check distance for every city in the db, only those that MIGHT be in range.
		$fromlat= $latitude- ($miles/$MILES_PER_DEGREE_LAT) ;
		$tolat= $latitude + ($miles/$MILES_PER_DEGREE_LAT) ;
		$fromlong= $longitude- ($miles/$milesPerDegreeLong) ;
		$tolong= $longitude + ($miles/$milesPerDegreeLong) ;

		
		//Don't worry about injection here.  Everything came from the db except $miles, which is a sanitized number
		//The order by clause is nice to have.  The performance hit SHOULD be minimal, but it is a calculated field.   
		//Distance>0 excludes the requested city. 
		$cities= DB::select( DB::raw("SELECT id,name,state,latitude, longitude, floor(acos( cos(radians( :lat ))* cos(radians( latitude )) "
								. "* cos(radians( :corlong) - radians( 180 +longitude )) + sin(radians( :lat2 ))  * sin(radians( latitude ))"
								. " )  * $RADIUS_OF_EARTH)  AS  Distance from cities where status='verified' and "
								. " latitude between :fromlat and :tolat and longitude between :fromlong and :tolong "
								. " having distance > 0 and distance <= :miles order by distance ")
					, array('lat' => $latitude , 'corlong' => $correctedlongitude,  'lat2' => $latitude , 'fromlat' => $fromlat, 'tolat' => $tolat,
					 'fromlong' => $fromlong, 'tolong' => $tolong,'miles'=>$miles,
					));

		return $cities;
	
	}


	//Get all verified cities in the specified state
	public static function getCitiesInState($state){
		
		$cities=DB::table('cities')->select('name','latitude', 'longitude')
										->where('status', '=', 'verified')
										->where('state', '=', $state)
										->orderby('name')->get(); 
		return $cities;

	}
}
