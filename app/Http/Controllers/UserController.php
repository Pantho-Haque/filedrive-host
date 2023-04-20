<?php

namespace App\Http\Controllers;



use Illuminate\Http\Request;
use App\Models\User;
use App\Models\BugFeedback;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Mail\Message;
use Carbon\Carbon;

class UserController extends Controller
{
 
    public function me(){ 
        return auth()->user();
    }

    

    public function register(Request $request){
        $request->validate([
            'name' => 'required|string|max:255',
            'profile_pic'=>'required',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:4|confirmed',
        ]);


        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'used_storage' => 0.0, // set initial used storage to 0
            'isAdmin' => $request->isAdmin ? $request->isAdmin : false,
        ]);

        // saving the profile picture 
        $profile_pic = $request->file('profile_pic')->storeAs(
            'public/backendfiles/'.$user->id  ,
            'profilepic.'. $request->file('profile_pic')->getClientOriginalExtension()
        );
        $url = asset('storage/' . str_replace('public/', '', $profile_pic));
        $user->profile_pic = $url;
        
        $token = Str::random(60);
        $user->remember_token = $token;
        $user->save();

        $email=$request->email;
        Mail::send('verifyemail',['token'=>$token],function(Message $message)use($email){
            $message->subject("Verify Your Email");
            $message->to($email);
        }); 


        return response([
            "user"=>$user,
            "message"=>"Verify your email to login",
            "success"=>true,
        ],201);
    }
    public function emailverified($token){
        $user = User::where("remember_token",$token)->first();
        if(!$user){
            return response([
                "message"=>"Invalid Request"
            ],404);
        }

        $user->email_verified_at = now();
        $user->save();

        return response([
            "message"=>"Your email has been verified. Now you can login."
        ],200);
    }

    public function logout(){
        auth()->user()->tokens()->delete();
        return response([
            "message"=>"Successfully logged out"
        ],200);
    }

    public function login(Request $request){
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where("email", $request->email)->first();
        if(!$user){
            return response([
                "message"=>"You must Register",
            ],404);
        }

        if(!$user->visibility){
            return response([
                "message"=>"You are banned from admins"
            ],401);
        }

        if(!$user || !Hash::check($request->password, $user->password)){
            return response([
                "message"=>"The provided credentials are incorrect"
            ],401);
        }


        

        $token = $user->createToken($request->email)->plainTextToken;

        return response([
            "user"=>$user,
            "message"=>"Login successful",
            "success"=>true,
            "token"=>$token
        ],200);
    }


    public function change_password(Request $request){
        $request->validate([
            'prevPass' => 'required',
            'password' => 'required|confirmed',
        ]);

        $user=auth()->user();
        if(Hash::check($request->prevPass, $user->password)){

            $user->password=Hash::make($request->password);
            $user->save(); 
            return response([
                "message"=>"Password Changed",
            ],200);
        }

        return response([
            "message"=>"Password didnt match"
        ],401);
    }

    public function updateNameEmail(Request $request)
    {
        $user= auth()->user();
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required','string','email','max:255',
                        function ($attribute, $value, $fail) use ($user) {
                            $otherUser = User::where('email', $value)
                                ->where('id', '!=', $user->id)
                                ->first();
                            if ($otherUser) {
                                $fail('The ' . $attribute . ' is already taken by another user.');
                            }
                        }]
        ]);
        
        $user->update($request->all());
        return response([
            "message"=>"Info updated"
        ],200);
    }

    /**
     * Display a listing of users.
     */
    public function index()
    {
        return User::all();
    }

    /**
     * Display a listing of admins.
     */
    public function adminindex()
    {
        return User::where('isAdmin', true)
                    ->whereNotIn('id', [auth()->user()->id])
                    ->get();
    }


    public function setresetadmin($id){
        if(!auth()->user()->isAdmin){
            return response([
                "message"=>"Unauthenticated for you"
            ],400);
        }


        $user = User::find($id);
        
        if(auth()->user()->id === $user->id){
            return response([
                "message"=>"You cant change your credentials"
            ],400);
        }

        $user->isAdmin=($user->isAdmin === 1 ? 0 : 1);
        $user->save();

        return response([
            "message"=> $user->isAdmin ? "Admin Assigned" : "Admin Removed",
            "user"=> $user
        ],200);
    }

    public function useractivation($id){
        if(!auth()->user()->isAdmin){
            return response([
                "message"=>"Unauthenticated for you"
            ],400);
        }
        $user = User::find($id);
        
        if(auth()->user()->id === $user->id){
            return response([
                "message"=>"You cant action on your account"
            ],400);
        }

        $user->visibility=($user->visibility === 1 ? 0 : 1);
        $user->save();

        // Delete all tokens associated with the user ID
        $tokens = DB::table('personal_access_tokens')
                        ->where('tokenable_id', $id)
                        ->delete();


        return response([
            "message"=> $user->visibility ? "Activation complete" : "Deactivation Complete",
            "user"=> $user
        ],200);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|unique:users',
            'password' => 'required',
        ]);
        return User::create($request->all());
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return User::find($id);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request,$id)
    {
        $user= User::find($id);
        $user->update($request->all());
        return $request;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        return User::find($id)->delete();
    }
    
    
    /**
     * find the specified resource from storage.
     */
    public function search($query='')
    {
       return User::where('email', 'like', '%'.$query.'%')
                ->orWhere('name', 'like', '%'.$query.'%')
                ->orWhere('id', $query)
                ->get();
    }

    public function graph(){
        $users = User::all();
        $data = [];
        
        foreach ($users as $user) {
            $data[] = [
                'x' => $user->id,
                'y' => $user->used_storage
            ];
        }
        
        $totalStorageUsed = $users->sum('used_storage');

        return response([
            'data' => $data,
            'totalStorageUsed' => $totalStorageUsed
        ],200);
    }

    public function resettheuser($id){
        $user = User::find($id);
        if(!$user){
            return response([
                "message"=>"User not found",
            ],400);
        }

        // delete from database
        $user->folders->each(function ($folder){
            $folder->files()->delete();

            // delete from storage
            $folderPath = 'public/backendfiles/'. $folder->user_id  . '/' . $folder->id;
            Storage::deleteDirectory($folderPath);

            $folder->delete();
        });

        

        $user->number_of_folders=0;
        $user->number_of_files=0;
        $user->used_storage=0.0;
        $user->save();

        return response([
            "message"=>"Reset successfull",
            "user"=>$user,
        ],200);

    }


    public function bugfeedback(Request $request){
        $request->validate([
            'user_id'=>'required',
            'name' => 'required',
            'email' => 'required',
            'feedback' => 'required',
        ]);

        BugFeedback::create([
            'user_id'=> $request->user_id,
            'name' =>  $request->name,
            'email' =>  $request->email,
            'feedback' =>  $request->feedback,
        ]);

        return response([
            "message"=>"Feedback reveived",
        ],201);
    }

    public function getallbugsfeedbacks(){
        return BugFeedback::all();
    }
}
