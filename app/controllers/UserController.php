<?php

class UserController extends BaseController{


	//Record a visit to a city. 
	public function visitCity($user){
		// City information is in POST data.
		//Only allow visits by current user.  
		if(user::isCurrentUser($user)){
			//Check for valid city
			$city = City::getCity(Input::get('city'),Input::get('state'));
			
			if($city){
				//try to add a visit
				$visit=User::visitCity($city);

				if (!is_null($visit)){
					return Response::json($visit, 201);
				} else {
					return Response::json(array('error' =>  'Conflict'), 409);
				}

			} else {
				return Response::json(array('error' =>  'No such city'), 404);
			}
		} else {
			//Trying to access someone else's content
			return Response::json(array('error' =>  'Forbidden'), 403);
		}
	}


	//List all cities visited by specified user.
	public function listVisitedCities($user){
		//Should we allow anyone to see anyone else's visits?  I am allowing it.  
		//Return an empty array for no visits.
		
		$cities=User::visitedCities($user);
		if(is_numeric($user) && count($cities)==0){
			return Response::json(array('error' =>  'No such user'), 404);
		}
		//DO NOT return 404 for user not found if given username due to security concerns.
		return Response::json($cities);

	}
}