@extends('app')

@section('content')
<div class="help_info">
	<a class="help-icon" data-toggle="modal" data-target="#helpModal">
		<span class="icon-question" aria-hidden="true"></span>
	</a>
</div>
<div class="container">
	<div class=" col-md-12 col-sm-12 col-xs-12">
			<h1>Persona View</h1>
			<h4>Create, edit and delete your Personas.</h4>  
	</div>
	<div class="divider_style_1"></div>
	<div class="row" style="margin-top: 10px;">
		<div class="col-md-12">
			<div class="panel panel-default">
				<div class="panel-heading">
					<b>My Personas</b>
				</div>
				<div class="panel-body">
									
					<!-- User Projects Table -->
					
					<div class="panel panel-default">
					
					<div class="row table_head">
							<div class="col-lg-1 col-md-1 col-sm-12 col-xs-12 personaAvatar">Avatar</div>
							<div class="col-lg-2 col-md-2 col-sm-6 col-xs-6">Name</div>
							<div class="col-lg-1 col-md-1 col-sm-6 col-xs-6">Age</div>
							<div class="col-lg-1 col-md-1 col-sm-6 col-xs-6">Gender</div>
							<div class="col-lg-2 col-md-2 col-sm-6 col-xs-6">Occupation</div>
							<div class="col-lg-2 col-md-2 col-sm-12 col-xs-12 personaToolsHead">Updated at</div>
							<div class="col-lg-3 col-md-3 col-md-offset-0 col-sm-12 col-xs-12 personaToolsHead">Tools</div>
					</div>
						<?php 
							$view_type= 'persona';
							$bmc_id = 'null';
							$project_id = 'null';
							$bmc_status = 'null';
						
						foreach ($myPersonas as $myPersona){
							$created_at = explode(' ', $myPersona["created_at"]);
							$created_at_date = $created_at[0];
							$created_at_time = $created_at[1];
								
							$updated_at = explode(' ', $myPersona["updated_at"]);
							$updated_at_date = $updated_at[0];
							$updated_at_time = $created_at[1];
							
							print	'<div class="row table_body">
											<div class="col-lg-1 col-md-1 col-sm-12 col-xs-12 personaAvatar"><img class="avatarImg" src="'.$myPersona["avatarImg"].'" alt="Selfhtml" /></div>
											<div class="col-lg-2 col-md-2 col-sm-6 col-xs-6">'.$myPersona["name"].'</div>
											<div class="col-lg-1 col-md-1 col-sm-6 col-xs-6">'.$myPersona["age"].'</div>
											<div class="col-lg-1 col-md-1 col-sm-6 col-xs-6">'.$myPersona["gender"].'</div>
											<div class="col-lg-2 col-md-2 col-sm-6 col-xs-6">'.$myPersona["occupation"].'</div>
											<div class="col-lg-2 col-md-2 col-sm-12 col-xs-12 personaTools">'.date('l, d-m-Y | H:m', strtotime($myPersona["updated_at"])).'</div>
											<div class="col-lg-3 col-md-3 col-sm-12 col-xs-12 personaTools">
												<div class="row">
													<div class="col-lg-5 col-md-12 col-sm-6 col-xs-12 personaToolItems">
														<a href="persona/edit/'.$myPersona["id"].','.$view_type.','.$bmc_id.','.$project_id.','.$bmc_status.',0,Persona">
															<span class="edit-icon no_background" aria-hidden="true" data-toggle="tooltip" data-placement="bottom" title="edit"/></span>							
														</a>
														<a data-toggle="modal" data-target="#deleteModal'.$myPersona['id'].'">
															<span class="delete-icon no_background" aria-hidden="true" data-toggle="tooltip" data-placement="bottom" title="delete"/></span>
														</a>
													</div>
													<div class="col-lg-7 col-md-12 col-sm-6 col-xs-12 personaToolItems">
														<button type="button" class="btn btn-primary btn-secundar" data-toggle="modal" data-target="#myPersona'.$myPersona["id"].'">Show Persona</button>
													</div>
												</div>
											</div>
									</div>
							';
							
							$skills = explode(';',$myPersona["skills"]);
							$needs = explode(';',$myPersona["needs"]);							
							
							print '			

							<div class="modal fade" id="myPersona'.$myPersona["id"].'" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
							  <div class="modal-dialog" role="document">
							    <div class="modal-content">
							      <div class="modal-header">
							        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
							        <h4 class="modal-title" id="myModalLabel">Persona Help</h4>
							      </div>
							      <div class="modal-body">
							      	<div class="container-fluid">
							      		<div class="panel panel-default">
							      			<div class="panel-heading">My Persona</div>
							      			<div class="panel-body bmcViewBackground">
							      				<div class="row">
							      					<div class="col-md-4">
							      						<div class="col-md-12 panel panel-default"><b>'.$myPersona["name"].'</b></div>
							      						<div class="col-md-12 panel panel-default persona_view_box"><img alt="Persona Avatar" src="'.$myPersona["avatarImg"].'" height="125px"></div>
							      					</div>
							      					<div class="col-md-8">
							      						<div class="col-md-12 persona_view_box_quote">
							      							<div class="col-md-12">
							      								<b>"'.$myPersona["quote"].'"</b>
							      							</div>
							      						</div>
							      						<div class="col-md-12 panel panel-default">
							      							<div class="col-md-12"><b>Personality</b></div><br>
							      								<p>'.$myPersona["personality"].'</p>
							      						</div>
							      					</div>
							      				</div>
							      				<div class="row">
							      					<div class="col-md-4">
								      					<div class="col-md-12 panel panel-default">
								      						<div class="col-md-12 persona_view_box_content"><b>Age:</b> '.$myPersona["age"].'</div>
							      							<div class="col-md-12 persona_view_box_content"><b>Gender:</b> '.$myPersona["gender"].'</div>
								      						<div class="col-md-12 persona_view_box_content"><b>Marital Status:</b> '.$myPersona["marital_status"].'</div>
								      						<div class="col-md-12 persona_view_box_content"><b>Occupation:</b> '.$myPersona["occupation"].'</div>
								      						<div class="col-md-12 persona_view_box_content"><b>Nationality:</b> '.$myPersona["nationality"].'</div>
								      					</div>
							      					</div>
							      					<div class="col-md-4">
								      					<div class="col-md-12 panel panel-default persona_view_box_content">
								      						<div class="col-md-12"><b>Skills</b></div><br>
								      						<ul>';
																 foreach ($skills as $skill){
																 	print '<li>'.$skill.'</li>';
																 }
													 print '</ul>
								      					</div>
							      					</div>
							      					<div class="col-md-4">
								      					<div class="col-md-12 panel panel-default persona_view_box_content">
								      						<div class="col-md-12"><b>Needs</b></div><br>
								      						<ul>';
																foreach ($needs as $need){
																	print '<li>'.$need.'</li>';	
																}
													 print '</ul>
								      					</div>
							      					</div>	
							      				</div>
									      	</div>
							      		</div>
									</div>
							      </div>
							      <div class="modal-footer">
							        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
							      </div>
							    </div>
							  </div>
							</div>		  									
							';
														 
							 print ' <!-- delete Modal -->
			  					<div class="modal fade" id="deleteModal'.$myPersona["id"].'" tabindex="-1" role="dialog">
								  <div class="modal-dialog delete" role="document">
								    <div class="modal-content delete col-md-12">
								      <div class="modal-header col-md-12">
								        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
								        <h4 class="modal-title">Do you want to delete '.$myPersona["name"].'?</h4>
								      </div>
	      							  <div class="modal-body col-md-12">
							      			If you delete a Persona you will also delete any Connections of this Persona to your Models. 
	      							  </div>
								      <div class="modal-footer delete col-md-12">
							      		<div class="col-md-6"><a href="persona/delete/'.$myPersona["id"].'"><button type="button" class="btn btn-primary btn-lg">Yes</button></a></div>
						  				<div class="col-md-6"><button type="button" class="btn btn-default btn-lg" data-dismiss="modal">No</button></div>
								      </div>
								    </div>
								  </div>
								</div>
			  				';
						}
						?>   	
					</div>
					<div class="col-md-12 col-sm-12 col-xs-12">
						<br>
						<a href="persona/create/{{{$view_type}}},{{{$bmc_id}}},{{{$project_id}}},{{{$bmc_status}}},0,Persona"><button type="button" class="btn btn-primary">New Persona</button></a>
					</div>
				</div>
			</div> 
		</div>
	</div>
</div>

<!-- Modal Help-->
<div class="modal fade" id="helpModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Persona Help</h4>
      </div>
      <div class="modal-body">
      	<div class="container-fluid">		
      		<div class="panel panel-default">
	      		<div class="panel-heading">Persona Description</div>
		      	<div class="panel-body">
		      		<p>Personas are fictional character profiles, which are created to represent a certain group of customers. They help developers, entrepreneurs and start-ups to understand their target audience. With the use of Personas, you are able to see problems through the eyes of your customers. 
In addition, using Personas lets you target and design your products and services quicker, cheaper and more efficiently.</p>
		      	</div>
	      	</div>
      	</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<script type="text/javascript">
$(function() {
	var d = document.getElementById("footer");
	d.className += " personaView";
});
</script>
@endsection
