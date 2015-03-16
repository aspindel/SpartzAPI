<?php

class CityController extends \BaseController {


	//get all cities a specified distance from the given city. 
	public function show($thestate, $thecity)	{
		// Distance comes from querystring ?radius=.
		//This should probably support using a two querystring variables, ?units and ?distance  instead of ?radius=
		//Try to accomodate some reasonable variants (WARNING: distance output is still in miles)

		
		if(count(Input::get())==1){
			//Try to accept any reasonable input
			$miles=  Input::get("radius") ;
			if (!isset($miles)){
				$miles=  Input::get("miles") ;
			}
			if (!isset($miles)){
				$miles=  Input::get("km") * 0.62;
			}
			if (!isset($miles)){
				$miles=  Input::get("nm") * 0.869;
			}

			if (is_numeric($miles)){
				//limit output by capping distance of "near" to 500 miles, which is more than generous
				//This works even in AK
				if ($miles>0 && $miles<=500){
					
					$cities=City::getNearbyCities($thestate, $thecity,$miles);
					
					if($cities ==null){
						//no such city
						return Response::json(array('error' =>  'Not Found'), 404);
					} else {
						//An empty array is returned for a valid city with nothing nearby.
						return Response::json($cities);
					
					}
				} else 	{	
					//don't bother looking, out of range
					return Response::json(array('error' =>  'Invalid Distance'), 400);
				}
			} else {
				return Response::json(array('error' =>  'Invalid Distance(must be numeric)'), 400);
			}
		} else {
			return Response::json(array('error' =>  'Invalid Request'), 400);
		}
	}



	//Get all of the cities in a state	
	public function getStateCities($abbrev){
		//Note that there is no states table.  The states are ony abbreviations in the ciies table.
		//We would like to be generous in accepting the name or ID of a state, but can't.
		if(strlen($abbrev)==2){
			
			$cities=City::getCitiesInState($abbrev);

			if(count($cities)>0){

				return Response::json($cities);
			
			} else 	{	
				return Response::make(null, 404);
			}
		} else {
			return Response::json(array('error' =>  'Bad Request'), 400);
		}
	}
/*
	public function update($thestate, $thecity){
		//syntax you will need
		$city->fill(Input::get());
		$city->save;
	}
*/
}
