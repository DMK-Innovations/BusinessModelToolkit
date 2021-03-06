<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\Registrar;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Illuminate\Http\Request;
use Mail;

class AuthController extends Controller {
	
	/*
	 * |-------------------------------------------------------------------------- | Registration & Login Controller |-------------------------------------------------------------------------- | | This controller handles the registration of new users, as well as the | authentication of existing users. By default, this controller uses | a simple trait to add these behaviors. Why don't you explore it? |
	 */
	
	use AuthenticatesAndRegistersUsers;
	
	/**
	 * Create a new authentication controller instance.
	 *
	 * @param \Illuminate\Contracts\Auth\Guard $auth        	
	 * @param \Illuminate\Contracts\Auth\Registrar $registrar        	
	 * @return void
	 */
	public function __construct(Guard $auth, Registrar $registrar) {
		$this->auth = $auth;
		$this->registrar = $registrar;
		
		$this->middleware ( 'guest', [ 
				'except' => 'getLogout' 
		] );
	}
	public function postRegister(Request $request) {
		$validator = $this->registrar->validator ( $request->all () );
	
		if ($validator->fails ()) {
			$this->throwValidationException ( $request, $validator );
		}
		$token= $_POST['_token'];
		$email= $_POST['email'];
		$name= $_POST['name'];
	
		$this->auth->login ( $this->registrar->create ( $request->all () ) );
	
		$data['verification_code']  = $token;
			
		Mail::send('registering.emailsend', ['token'=>$token, 'email'=>$email], function($message) use ($email)
		{
			$message->from('support@toolkit.builders', 'support@toolkit.builders');
			$message->to($email);
			$message->subject('Complete your toolkit.builders sign up');
			
		});
		return response()->view('registering.register', [
				'name' => $name,
				'email' => $email,
				'token'=> $token,
					
				]);
	}
}
