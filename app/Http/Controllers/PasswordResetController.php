<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;


use App\Models\PasswordReset;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Mail\Message;
use Carbon\Carbon;

class PasswordResetController extends Controller
{
    public function send_reset_password_email(Request $request){
        $request->validate([
                "email"=>"required|email",
        ]); 
        $email = $request->email;

        $user=User::where("email",$email)->first();
        if(!$user){
            return response([
                "message"=>"Email Doesnt exist",
                "status"=>"Failed"
            ],404);
        }

        $token = Str::random(60);

        Mail::send('resetpass',['token'=>$token],function(Message $message)use($email){
            $message->subject("Reset Your Email");
            $message->to($email);
        }); 

        PasswordReset::create([
            "email"=>$email,
            "token"=>$token,
            'created_at'=>Carbon::now()
        ]);   

        return response([
            "message"=>"Check yout email to reset your password",
            "status"=>"success"
        ],200);
    }


    public function resetingPassword(Request $request){
        $request->validate([
                "token"=>"required",
                "password"=>"required|confirmed",
        ]); 

        $token=$request->token;
        $passreset=PasswordReset::where('token',$token)->first();

         if(!$passreset){
            return response([
                "message"=>"Token is Invalid or Expired",
                "status"=>"Failed"
            ],404);
        }


        $user=User::where("email", $passreset->email)->first();
        $user->password= Hash::make($request->password);
        $user->save();

        return response([
            "message"=>"Password Reset Success",
            "status"=>"success"
        ],200);
    } 
}
 