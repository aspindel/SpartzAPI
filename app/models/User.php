<?php

use Illuminate\Auth\UserTrait;
use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableTrait;
use Illuminate\Auth\Reminders\RemindableInterface;

class User extends Eloquent implements UserInterface, RemindableInterface {

	use UserTrait, RemindableTrait;

	protected $table = 'users';
	public $timestamps = false;
	protected $fillable = ['first_name','last_name','password'];
	

	//Get ID from username, returns object not number
	public static function getUserID($username)
	{
		
		$user=DB::table('users')->select('id')
								->where('username', '=', $username)
								->first(); //it is unique
		
		return $user;
	}


	//Is the specified user the current authorized user? Return bool
	public static function isCurrentUser($user){
		//Be liberal in what you accept.  Allow either user ID or username.
		//WARNING:user names are assumed to be non-numeric
		//input is a string or number, not an object
		if(is_numeric($user)){
			if ($user==Auth::user()->id){
				return true;
			} else {
				return false;
			}
		} else {
			if( empty(user::getUserID($user))){
				//no such user
				return false;
			} else {
				if(user::getUserID($user)->id == Auth::user()->id){
					return true;
				} else {
					//different user
					return false;
				}
			}
			
		}

	}


	//List the cities which were visited by the specified user
	public static function visitedCities($username){
		
		//NOTE: I'm not checking for status verified here.  If the person visited Brigadoon's US equivalent and it is now
		// unverified I am going to list it here anyway.  
	
		//Be liberal in what you accept.  Take either the name or ID of the user.
		$cities=DB::table('cities')
			->join('city_user', 'cities.id', '=', 'city_user.city_id')
			->join('users','users.id','=','city_user.user_id')
			->select('cities.name', 'cities.state', 'cities.latitude', 'cities.longitude' )
			->where('user_id', '=', $username)
			->orwhere('username', '=', $username)
			->orderBy('city_user.visit_date' , 'asc')
			->get();
		
		return	$cities;
	}


	//Add a visit for the current user to the specified city.
	public static function visitCity($city){
	
		//Use a procedure to check for a duplicate and do the insert in one transaction.
		//To return the right code to the user we need the procedure output, not just boolean success/failure.
		
		$userid= Auth::user()->id;
		$db = DB::connection()->getPdo();
					$stmt = $db->prepare("CALL AddCityVisit(?,?)");
					$stmt->bindParam(1, $city->id);
					$stmt->bindParam(2,$userid);
					$stmt->execute();
					//two sets of information are returned, the first one has the information we want
					$row = $stmt->fetch(PDO::FETCH_ASSOC);
					$stmt->nextRowset();


		if ($row['id']>0){
			//Some APIs return the entire object inserted.  The ID is returned here for warm fuzzies.
			return $row;

		} else {
			//This city has already been visited, no record created
			return null;
		}
	}

}
