<?php namespace App\Http\Controllers;

use App\BMC;
use App\Status;
use App\Project;
use App\Persona;
use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Auth;
use App\Notice;
use App\App;
use Illuminate\Http\Request;
class BmcController extends Controller {

	/*
	|--------------------------------------------------------------------------
	| Bmc Controller
	|--------------------------------------------------------------------------
	|
	|
	*/

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->middleware('auth');
	}

	/**
	 * Show the application dashboard to the user.
	 *
	 * @return Response
	 */
	public function index()
	{
		return view('bmc');
	}
	
	public function create($id)
	{
		$inserts = explode(",", $id);
		$project_id = $inserts[0];
		$owner = $inserts[1];
		
		return view('newBmc',['project_id' => $project_id, 'error' => false, 'owner' => $owner]);
	}
	
	public function changeStatus($id){
		$inserts = explode(",", $id);
		
		$project_id = $inserts[0];
		$bmc_id = $inserts[1];
		$bmc_status = $title = $_POST["status"];
		$view_type = $inserts[2];
		$owner = $inserts[3];
		
		$bmc = BMC::find($bmc_id);
			
		switch ($bmc_status) {
			case 'inWork':
				$bmc->status = Status::IN_WORK;
				$status = 1;
				break;
			case 'approved':
				$bmc->status = Status::APPROVED;
				$status = 2;
				break;
			case 'rejected':
				$bmc->status = Status::REJECTED;
				$status = 3;
				break;
		}

		$bmc->save();
			
		if($view_type == 1){ //redirects to project BMCs View or to BMC View
			$view = 'projects/showBMCs/'.$project_id.','.$owner;
		}else{
			$view = '../public/bmc/viewBMC/'.$bmc_id.','.$project_id.','.$status.','.$owner;
		}
		
		return redirect($view);
		
	}
	
	public function save($id){
		$inserts= explode(",", $id); 
		
		$project_id = $inserts[0];
		$bmc_id = $inserts[1];
		$bmc_status = $inserts[2];
		$view_type = $inserts[3]; //1 - showBMC.blade, 0 - viewBMC.blade
		$owner = $inserts[4];
		
		$title = $_POST["title"];
	
		if($title == ''){ 
			return view('newBmc',['project_id' => $id, 'error' => true]);
	
		}else{
			//noch pr�fen ob Titel schon in DB vorhanden ist in Kombi mit diesem Assignee
	
			if($bmc_id=='null'){
				$bmc = new BMC();
			} else {
				$bmc = BMC::find($bmc_id);
			}
	
			$bmc->title = $title;
			
			switch ($bmc_status) {
				case 'inWork':
					$bmc->status = Status::IN_WORK;
					$status = 1;
					break;
				case 'approved':
					$bmc->status = Status::APPROVED;
					$status = 2;
					break;
				case 'rejected':
					$bmc->status = Status::REJECTED;
					$status = 3;
					break;
			}
			
			$bmc->version = 1;
			$bmc->project_id = $project_id;
			$bmc->save();
			
			if($view_type == 1){ //redirects to project BMCs View or to BMC View
				$view = 'projects/showBMCs/'.$project_id.','.$owner;
			}else{
				$view = '../public/bmc/viewBMC/'.$bmc_id.','.$project_id.','.$status.','.$owner;
			}

			return redirect($view);
		}
	}
	
	public function viewBMC($id){
		$inserts= explode(",", $id);
		
		$myPersonas = $this->getMyPersonas();
		
		$bmc = BMC::find($inserts[0]);
		
		$myAssignedPersonas = $this->getAssignedPersonas($inserts[0]);
		
		$bmc_name= $bmc['title'];
		
		$bmc_id = $inserts[0];
		$project_id = $inserts[1];
		$bmc_status_id = $inserts[2];
		$owner = $inserts[3];
		
		$bmc_postIts = $this->getBMCPostIts($bmc_id);
		
		switch ($bmc_status_id) {
			case 1:
				$bmc_status = 'inWork';
				break;
			case 2:
				$bmc_status = 'approved';
				break;
			case 3:
				$bmc_status = 'rejected';
				break;
			default:
				$bmc_status = 'inWork';
				break;
		}
	
		return view('viewBMC', ['bmc_id' => $bmc_id, 'project_id' => $project_id, 'bmc_name' => $bmc_name, 'bmc_status' => $bmc_status, 'bmc_postIts' => $bmc_postIts, 'myPersonas' =>$myPersonas, 'myAssignedPersonas' => $myAssignedPersonas, 'owner' => $owner]);
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
	
	public function edit($id){
		$inserts= explode(",", $id);
		
		$bmc_id = $inserts[0];
		$owner = $inserts[1];
		
		$bmc = BMC::find($bmc_id);
		
		return view('newBmc',['bmc' =>json_decode($bmc, true) ,'project_id' => $bmc['project_id'], 'error' => false, 'owner' => $owner]);
	}
	
	public function copyBmc($id){
		$inserts = explode(",", $id);
	
		$bmc_original_id = $inserts[0];
		$project_id = $inserts[1];
		$owner = $inserts[2];
	
		$bmc_original = BMC::find($bmc_original_id);
		$bmc_original_PostIts = $this->getBMCPostIts($bmc_original_id);
		$assignedPersonas = $this->getAssignedPersonas($bmc_original_id);
		
		$bmc_copy = new BMC();
		$bmc_copy->title = $bmc_original['title'].' (Kopie)';	
		$bmc_copy->status = $bmc_original['status'];
		$bmc_copy->version = 1;
		$bmc_copy->project_id = $bmc_original['project_id'];
		$bmc_copy->save();
		
		foreach($bmc_original_PostIts as $bmc_original_PostIt){
			$sort_anz = 0;
			
			$postIt = new Notice();
			
			$postIt->title = $bmc_original_PostIt['title'];
			$postIt->content = $bmc_original_PostIt['content'];
			$postIt->status = $bmc_original_PostIt['status'];			
			$postIt->notice = $bmc_original_PostIt['notice'];
			$postIt->sort = $sort_anz+1;
			$postIt->color = '';
			$postIt->canvas_box_id = $bmc_original_PostIt['canvas_box_id'];
			$postIt->bmc_id = $bmc_copy['id'];
			
			$postIt->save();
		}		
		
		foreach($assignedPersonas as $assignedPersona){
			$bmc_copy = BMC::find($bmc_copy['id']);
			$bmc_copy->personas()->attach($assignedPersona['id']);	
		}
	
		$view = 'projects/showBMCs/'.$project_id.','.$owner;
		return redirect($view);
	}
	
	/**
	 * deletes BMC
	 * @param unknown $id
	 * @return Ambigous <\Illuminate\Routing\Redirector, \Illuminate\Http\RedirectResponse>
	 */
	public function deleteBMC($id){
		$inserts= explode(",", $id);
		
		$bmc_id = $inserts[0];
		$project_id = $inserts[1];
		$owner = $inserts[2];
		
		$bmc = BMC::find($bmc_id);
		
		//personas detachen
		$assignedPersonas = $this->getAssignedPersonas($bmc_id);
		
		foreach($assignedPersonas as $assignedPersona){
			$bmc->personas()->detach($assignedPersona['id']);
		}
				
		//post-IT's l�schen		
		$bmcPostIts = $this->getBMCPostIts($bmc_id);
		
		foreach($bmcPostIts as $bmcPostIt){
			Notice::destroy($bmcPostIt['id']);
		}		
		
		//BMC l�schen
		BMC::destroy($bmc_id);
		
		$view = 'projects/showBMCs/'.$project_id.','.$owner;
			
		return redirect($view);
	}
	
	public function savePostIt($id){
		$inserts= explode(",", $id);
		
		$canvas_box_id = $inserts[0];
		$bmc_id = $inserts[1];
		$project_id = $inserts[2];
		$bmc_status = $inserts[3];
		$post_it_id = $inserts[4];
		$owner = $inserts[5];
		
		$sort_anz = $this->getNoticeCount($canvas_box_id.$bmc_id);

		$title = $_POST["title"];
		$status = $_POST["status"];
		
		if($title == ''){
			print 'falsch';
		}else{
 			if($post_it_id=='null'){
 				$postIt = new Notice();
 			} else {
 				$postIt = Notice::find($post_it_id);
 			}
			
 			$postIt->title = $title;
 			$postIt->content = $_POST["content"];
			
 			switch ($status) {
 				case 'inWork':
 					$postIt->status = Status::IN_WORK;
 					break;
 				case 'approved':
 					$postIt->status = Status::APPROVED;
 					break;
 				case 'rejected':
 					$postIt->status = Status::REJECTED;
 					break;
 			}
			
 			$postIt->notice = $_POST["notice"];
 			$postIt->sort = $sort_anz+1;
 			$postIt->color = '';
 			$postIt->canvas_box_id = $canvas_box_id;
 			$postIt->bmc_id = $bmc_id;

 			$postIt->save();
				
 			$view = '../public/bmc/viewBMC/'.$bmc_id.','.$project_id.','.$bmc_status.','.$owner;
			
 			return redirect($view);
		}
	}
	
	public function getNoticeCount($id){
		$inserts= str_split($id);
		
		$canvas_box_id = $inserts[0];
		$bmc_id = $inserts[1];
		
		$allNotices = Notice::all();
		$canvas_Notices = null;
		
		foreach ($allNotices as $notice){
			if($notice['canvas_box_id'] == $canvas_box_id){
				if($notice['bmc_id'] == $bmc_id){
					$canvas_Notices = $canvas_Notices +1;
				}
			}
		}
		return $canvas_Notices;
	}
	
	public function deletePostIt($id){
		$inserts= explode(",", $id);
	
		$post_it_id = $inserts[0];
		$bmc_id = $inserts[1];
		$project_id = $inserts[2];
		$bmc_status = $inserts[3];
		$owner = $inserts[4];

		Notice::destroy($post_it_id);
	
		$view = '../public/bmc/viewBMC/'.$bmc_id.','.$project_id.','.$bmc_status.','.$owner;
			
 		return redirect($view);
	}
	
	public function changePostItStatus($id){
		$inserts = explode(",", $id);
	
		$project_id = $inserts[0];
		$bmc_id = $inserts[1];
		$bmc_status = $inserts[2];
		$postIt_id = $inserts[3];
		$owner = $inserts[4];
		
		$postIt_status = $_POST["postIt_status"];
	
		$postIt = Notice::find($postIt_id);
			
		switch ($postIt_status) {
			case 'inWork':
				$postIt->status = Status::IN_WORK;
				break;
			case 'approved':
				$postIt->status = Status::APPROVED;
				break;
			case 'rejected':
				$postIt->status = Status::REJECTED;
				break;
		}
	
		$postIt->save();
			
		$view = '../public/bmc/viewBMC/'.$bmc_id.','.$project_id.','.$bmc_status.','.$owner;
	
		return redirect($view);
	}
	
	public function addPersona(Request $request, $id){
		$inserts = explode(",", $id);
		$bmc_id = $inserts[0];
		$owner = $inserts[1];
		
		$bmc = BMC::find($bmc_id);
		
		if(empty($bmc)) return "ERROR!";
		
		$personaIds = $request->input('selectedPersona');
		if(!empty($personaIds)){
			// remove all existing
			$bmc->personas()->detach();
			// save new relations
			$bmc->personas()->attach($personaIds);
			$bmc->save();
		}
		
		switch ($bmc->status) {
			case 'inWork':
				$status = 1;
				break;
			case 'approved':
				$status = 2;
				break;
			case 'rejected':
				$status = 3;
				break;
		}
		
		$view = '../public/bmc/viewBMC/'.$bmc->id.','.$bmc->project->id.','.$status.','.$owner;
		
		return redirect($view);
	}
	
	public function getAssignedPersonas($id){		
		$bmc = BMC::find($id);
		return $bmc->personas()->get();
	}
	
	public function deleteAssignedPersona($id){
		$inserts = explode(",", $id);
		
		$bmc_id = $inserts[0];
		$project_id = $inserts[1];
		$bmc_status = $inserts[2];
		$persona_id = $inserts[3];
		$owner = $inserts[4];
		
		$bmc = BMC::find($bmc_id);
		$bmc->personas()->detach($persona_id);
		
		$view = '../public/bmc/viewBMC/'.$bmc->id.','.$project_id.','.$bmc_status.','.$owner;
		
		return redirect($view);
	}
}
