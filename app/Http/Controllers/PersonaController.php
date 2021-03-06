<?php


namespace App\Http\Controllers;

use App\Persona;
use App\Project;
use App\User;
use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Auth;
use App\BMC;
use App\Http\Controllers\ProjectsController;
use Illuminate\Support\Facades\Input;

class PersonaController extends Controller {
	
	/*
	 * |--------------------------------------------------------------------------
	 * | Persona Controller
	 * |--------------------------------------------------------------------------
	 * |
	 * |
	 */
	
	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct() {
		$this->middleware ( 'auth' );
	}
	
	/**
	 * Show the application dashboard to the user.
	 *
	 * @return Response
	 */
	public function index() {
		$getMyPersonas = $this->getMyPersonas();
		$my_project=new ProjectsController();
		$getMyProjects =$my_project->getMyProjects();
		$path = $this->getPath();
		
		return view ( 'persona', ['myPersonas' => $getMyPersonas,'myProjects' => $getMyProjects,'path' => $path]);
	}
	
	public function getAllPersonas() {
		return Persona::all ();
	}
	
	public function getMyPersonas(){
		$allPersonas = $this->getAllPersonas();
		$personas = json_decode($allPersonas, true);
	
		$user_id = Auth::user()->id;
		$myPersonas = array();
	
		foreach ($personas as $persona){
				
			$assigne_id = $persona["assignee_id"];
				
			if($user_id == $assigne_id){
				$temp = json_encode($persona);
				array_push($myPersonas, $persona);
			}
		}
	
		return $myPersonas;
	}
	
	public function edit($id){
		$inserts = explode(",", $id);
		
		$persona_id = $inserts[0];
		$view_type = $inserts[1];
		$bmc_id = $inserts[2];
		$project_id = $inserts[3];
		$bmc_status = $inserts[4];
		$owner = $inserts[5];
		$view_type_main = $inserts[6];
		$error = false;
		
		$persona = Persona::find($id);
		$path = $this->getPath();
		
		return view('newPersona', ['view_type' => $view_type, 'bmc_id' => $bmc_id, 'project_id' => $project_id, 'bmc_status' =>$bmc_status, 'persona' => $persona, 'owner' => $owner, 'view_type_main' => $view_type_main, 'error' => $error, 'path' => $path]);
	}
	
	public function deletePersona($id){
		$bmc_persona_connections = $this->getPersonaConnection($id);	
		
		foreach($bmc_persona_connections as $bmc_persona_connection){
			$bmc = BMC::find($bmc_persona_connection['pivot']['bmc_id']);
			$bmc->personas()->detach($id);
		}
		
		Persona::destroy($id);

		return redirect('persona');
	}
	
	public function getPersonaConnection($id){
		
		$bmcs = BMC::all();
		$user_id = Auth::user()->id;
		
		$myBMCs = array();
		$bmc_connections = array();
		$bmc_persona_connection = array();
		
		foreach ($bmcs as $bmc){ //finden aller bmc des Users
			
			$project = Project::find($bmc['project_id']);
			
			if($project['assignee_id'] == $user_id){
				array_push($myBMCs, $bmc);
			}
		}
		
		foreach($myBMCs as $myBMC){
			$bmc = BMC::find($myBMC['id']);
			
			$persona_connection = $bmc->personas()->get();
			
			if(!empty($persona_connection)){
				
				foreach ($persona_connection as $connection){
					array_push($bmc_connections, $connection);
				}
			}
		}
		
		foreach($bmc_connections as $bmc_connection){
			if($bmc_connection['pivot']['persona_id'] == $id){
				array_push($bmc_persona_connection, $bmc_connection);
			}
		}
		
		return $bmc_persona_connection;
	}
	
	public function save($id){
		
		$inserts = explode(",", $id);
		
		$persona_id = $inserts[0];
		$view_type = $inserts[1];
		$bmc_id = $inserts[2];
		$project_id = $inserts[3];
		$bmc_status = $inserts[4];	
		$owner = $inserts[5];
		$view_type_main = $inserts[6];
		$path = $this->getPath();
		
		$persona_name = Input::get('name');
		
		$validator = $this->eingabeKorrekt($persona_name);
		
		if(!$validator){
			return view ( 'newPersona', [
				'view_type' => $view_type,
				'bmc_id' => $bmc_id,
				'project_id' => $project_id,
				'bmc_status' =>$bmc_status,
				'owner' => $owner,
				'view_type_main' => $view_type_main,
				'error' => true,
				'path' => $path
			]);
		}else{
			//noch pr�fen ob Titel schon in DB vorhanden ist in Kombi mit diesem Assignee
	
			if($persona_id == 'null'){
				$persona = new Persona();
			} else {
				$persona = Persona::find($id);
			}
					
			$persona->name = Input::get('name');
			$persona->assignee_id = Auth::user()->id;
			
			if (empty(Input::get('avatarImg'))){
				$persona->avatarImg = 'img/male_persona_default_bg.png';
			}else{
				$persona->avatarImg = Input::get('avatarImg');
			}
			$persona->age = Input::get('age');
			$persona->gender = Input::get('gender');
			$persona->occupation = Input::get('occupation');
			$persona->nationality = Input::get('nationality');
			$persona->marital_status = Input::get('marital_status');
			$persona->quote = Input::get('quote');
			$persona->personality = Input::get('personality');
			$persona->skills = Input::get('skills');
			$persona->needs = Input::get('needs');
			$persona->save();
	
			if($view_type == 'persona'){
				return redirect('persona');
			}else {
				$view = '../public/bmc/viewBMC/'.$bmc_id.','.$project_id.','.$bmc_status.','.$owner.','.$view_type_main;
					
				return redirect($view);
			}
		}
	}
	
	/**
	 * Show the application dashboard to the user.
	 *
	 * @return Response
	 */
	public function create($id)
	{
		$inserts = explode(",", $id);
		
		$view_type = $inserts[0];
		$bmc_id = $inserts[1];
		$project_id = $inserts[2];
		$bmc_status = $inserts[3];
		$owner = $inserts[4];
		$view_type_main = $inserts[5];
		$path = $this->getPath();
		(isset($inserts [6])? $error = $inserts [6]: $error = false);
		
		return view('newPersona', ['view_type' => $view_type, 'bmc_id' => $bmc_id, 'project_id' => $project_id, 'bmc_status' =>$bmc_status, 'owner' => $owner, 'view_type_main' => $view_type_main, 'error' => $error, 'path' => $path]);
	}
	
	/**
	 * Validierung der Nutzereingaben vor der Speicheroperation.
	 */
	public function eingabeKorrekt($title){
		if(empty($title)){
			return false;
		}else{
			if(is_string($title)){
				return true;
			}else{
				return false;
			}
		}
	}
}