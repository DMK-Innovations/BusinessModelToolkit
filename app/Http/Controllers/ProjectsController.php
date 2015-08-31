<?php


namespace App\Http\Controllers;

use App\Project;
use App\User;
use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Auth;
use App\BMC;
use App\Notice;

class ProjectsController extends Controller {
	
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
		$user_name = Auth::user ()->name;
		$getMyProjects = $this->getMyProjects ();
		$myAssignedProjects = $this->getMyAssignedProjects();
		$assignedProjectsOwners = $this->getAssignedProjectsOwner();
		
		return view ( 'projects', ['myProjects' => $getMyProjects, 'user_name' => $user_name, 'myAssignedProjects' => $myAssignedProjects, 'assignedProjectsOwners' =>$assignedProjectsOwners] );
	}
	
	/**
	 * Gets all the projects.
	 * @return Ambigous <\Illuminate\Database\Eloquent\Collection, multitype:\Illuminate\Database\Eloquent\static >
	 */
	public function getAllProjects() {
		return Project::all ();
	}
	
	/**
	 * Gets my project.
	 * @return multitype:
	 */
	public function getMyProjects() {
		$allProjects = $this->getAllProjects ();
		$projects = json_decode ( $allProjects, true );
		
		$user_id = Auth::user ()->id;
		$myProjects = array ();
		
		foreach ( $projects as $project ) {
			
			$assigne_id = $project ["assignee_id"];
			
			if ($user_id == $assigne_id) {
				array_push ( $myProjects, $project );
			}
		}
		
		return $myProjects;
	}
	
	/**
	 * Edit a project.
	 * @param unknown $id
	 * @return \Illuminate\View\View
	 */
	public function edit($id) {
		$project = Project::find ( $id );
		
		return view ( 'newProject', [ 
				'project' => json_decode ( $project, true ), 'error' => 0 
		] );
	}
	
	/**
	 * Shows all bmcs of a project.
	 * @param unknown $id
	 * @return \Illuminate\View\View
	 */
	public function showBMCs($id) {
		$inserts = explode(",", $id);
		
		$project_id = $inserts[0];
		$project_name = $this->getProjectName ( $id );
		$allProjectBMCs = $this->getAllProjectBMCs ( $id );
		
		return view ( 'showBMCs', [ 
				'bmcs' => $allProjectBMCs,
				'project_id' => $project_id,
				'project_name' => $project_name,
				'owner' => $inserts[1]
		] );
	}
	
	/**
	 * Gets the project name.
	 * @param unknown $id
	 * @return \App\Http\Controllers\Ambigous
	 */
	public function getProjectName($id) {
		$allProjects = $this->getAllProjects ();
		foreach ( $allProjects as $project ) {
			if ($project ['id'] == $id) {
				$name = $project ['title'];
				return $name;
			}
		}
	}
	
	/**
	 * Gets all the bmcs of a project.
	 * @param unknown $id
	 * @return multitype:
	 */
	public function getAllProjectBMCs($id) {
		$allBMCs = BMC::all ();
		$allProjectBMCs = array ();
		
		foreach ( $allBMCs as $bmc ) {
			if ($bmc ['project_id'] == $id) {
				array_push ( $allProjectBMCs, $bmc );
			}
		}
		
		return $allProjectBMCs;
	}
	
	/**
	 * Deletes the project.
	 * @param unknown $id
	 * @return Ambigous <\Illuminate\Routing\Redirector, \Illuminate\Http\RedirectResponse>
	 */
	public function deleteProject($id) {
		$project = Project::find ( $id );
		
		//alle bmc's des Projektes finden
		$projectBMCs = $this-> getAllProjectBMCs($project['id']);
		
		foreach($projectBMCs as $projectBMC){
			//alle personas von BMC's finden und detachen
			$bmc_personas = $projectBMC->personas()->get();
			
			foreach($bmc_personas as $bmc_persona){
				$projectBMC->personas()->detach($bmc_persona['id']);
			}
			
			//alle post-IT's finden und l�schen
			$bmcPostIts = $this->getBMCPostIts($projectBMC['id']);
			
			foreach($bmcPostIts as $bmcPostIt){
				Notice::destroy($bmcPostIt['id']);
			}
			
			//bmc l�schen
			BMC::destroy($projectBMC['id']);
		}		
		
		//Projekt l�schen		
		Project::destroy ($id);
		
		return redirect ('projects');
	}
	
	public function getBMCPostIts($bmc_id){
		$getAllPostIts = Notice::all();
		$bmcPostIts = array();
	
		$dbPostIts = json_decode($getAllPostIts, true);
	
		foreach ($dbPostIts as $dbPostIt){
			if ($dbPostIt["bmc_id"] == $bmc_id){
				array_push($bmcPostIts, $dbPostIt);
			}
		}
		return $bmcPostIts;
	}
	
	/**
	 * Saves or updated a project.
	 * @param string $id
	 * @return \Illuminate\View\View
	 */
	public function save($id = null) {
		$title = $_POST ["title"];
		
		if ($title == '') {
			return view ( 'newProject', ['error' => 1]);
		} else {
			
			$myProjects = $this->getMyProjects();
			$title_is_identical = false;
			
			foreach($myProjects as $myProject){
				if($title == $myProject["title"]){
					$title_is_identical = true;
				}
			}
			
			if($title_is_identical == false){
				if (is_null ( $id )) {
					$project = new Project ();
				} else {
					$project = Project::find ( $id );
				}
				
				$user_id = Auth::user ()->id;
				
				$project->title = $title;
				$project->assignee_id = $user_id;
				$project->save ();
				
				return redirect ( 'projects' );
			}else{
				return view ( 'newProject', ['error' => 2]);
			}
		}
	}
	
	/**
	 * Show the application dashboard to the user.
	 *
	 * @return Response
	 */
	public function create() {
		return view ( 'newProject', ['error' => 0]);
	}
	
	/**
	 * Validierung der Nutzereingaben vor der Speicheroperation.
	 * TODO Sollte man sicherlich noch einbauen!
	 */
	public function eingabeKorrekt() {
		$validator = Validator::make ( Input::all (), array (
				'title' => '$project_title' 
		) );
		
		if ($validator->fails) {
			return Redirect::action ( NewProjectController::index () );
		}
		return Redirect::action ( NewProjectController::createNewProject () );
	}
	
	public function getMyAssignedProjects(){
		$allProjects = $this->getAllProjects();
		$user_id = Auth::user()->id;
	
		$myAssignedProjects = array();
	
		foreach($allProjects as $aProject){
			$project = Project::find($aProject['id']);
			$assignedTeamMembers = $project->members()->get();
				
			foreach ($assignedTeamMembers as $assignedTeamMember){
				if($assignedTeamMember['id'] == $user_id){
					array_push($myAssignedProjects, $project);
				}
			}
		}
		return $myAssignedProjects;
	}
	
	public function getAssignedProjectsOwner(){
		$myAssignedProjects = $this->getMyAssignedProjects();
		
		$owner = array();
		
		foreach($myAssignedProjects as $myAssignedProject){

			$owner_array= User::find($myAssignedProject['assignee_id']);
			array_push($owner, $owner_array);
		}
		
		return $owner;
	}
}
