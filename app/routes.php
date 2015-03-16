<?php




// Route group for API versioning
	Route::group(array('prefix' => 'v1', 'before' => 'auth.basic'), function()
	{

		//get cities in a state
		Route::get('states/{abbrev}/cities', 'CityController@getStateCities') ;

		//get cities near a specified city
		Route::get('states/{abbrev}/cities/{city}', 'CityController@show') ;


		
		//add a visit to the specified city for the authorized user
		Route::post('users/{user}/visits','UserController@visitCity');

		//get the cities the specified user has visited
		Route::get('users/{user}/visits','UserController@listVisitedCities');
	


		//return 501 Not Implemented for verbs not used but likely to be used in the future
		//Laravel will return  405 not allowed for everything else
		Route::match(array('PUT', 'POST','DELETE'), 'states/{abbrev}/cities', function()
		{
			return Response::json(array('error' =>  'Not Implemented'), 501);
		});

		Route::match(array('PUT','DELETE'), 'users/{user}/visits', function()
		{
			return Response::json(array('error' =>  'Not Implemented'), 501);
		});

	
	});

