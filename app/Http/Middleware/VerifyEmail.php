<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;


use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Mail\Message;
use Carbon\Carbon;

class VerifyEmail
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        if(!auth()->user()){
            return response([
                "message"=>"You must register"
            ],401);
        }
        if(!auth()->user()->email_verified_at){
            $user = auth()->user();
            $updatedAt = Carbon::parse($user->updated_at);
            $currentTime = Carbon::now();
            if($updatedAt->diffInMinutes($currentTime) >= 10){
                $token = Str::random(60);
                $user->remember_token = $token;
                $user->save();

                $email=$user->email;
                Mail::send('verifyemail',['token'=>$token],function(Message $message)use($email){
                    $message->subject("Verify Your Email");
                    $message->to($email);
                });
            } 
            
            return response([
                "message"=>"Please check your email to verify"
            ],401);
        }

        return $next($request);
    }
}
