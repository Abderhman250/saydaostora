<?php

namespace App\Http\Controllers;

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\UserPoints;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
// use Validator;
use Illuminate\Support\Str;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Mail\ForgetPassword;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{
    public $successStatus = 200;
    /**
     * login api
     *
     * @return \Illuminate\Http\Response
     */


    public function x()
    {


        return "x";
    }


    public function For_Get_Pasword(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email' => ['required'],
            'password'   => ['required', 'string'],
            'configPass' => ['required', 'string', 'same:password']
        ]);
        if ($validator->fails())
            return response()->json(['error' => $validator->errors()], 401);

        $User  = User::where('email', '=', $request->input('email'))->first();
        $User->update([
            'password' => bcrypt($request->input('password'))
        ]);

        return response()->json(['success' => true, 'mesg' => 'success reset password '], 202);
    }

    public function check_code_Password(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'code' => 'required|unique:password_resets',
        ]);
        $password_reset = DB::table('password_resets')->where('token', $request->code)->first();
        $end = Carbon::now();
        if($password_reset)
        {
            $t = strtotime($password_reset->created_at);
            if (strtotime('+15 minutes',$t) >= (strtotime($end))) {
                return response()->json(['success' => true, 'mesg' => 'code is match'],200);
            }
            return response()->json(['success' => true, 'mesg' => 'please resend code'],200);
        }
        else
        {
            return response()->json(['success'=>true,'mesg'=>'not found code'],200);
        }

        // if ($password_resets === null)
        //     $flag = false;

        // if ($flag  === true)
        //     $message = "mcode is math";
        // else
        //     $message = "mcode is  not math";
        // return response()->json(['success' =>  $flag, 'mesg' => $message], 200);
    }

    public function password_resets(Request $request)
    {
        $token = Str::random(6);
        $user=User::where('email',$request->email)->first();
        $templateemail=view('email',compact('user','token'));
        $validator = Validator::make(request()->all(), [
            'email' => 'required|email|exists:users',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

        DB::table('password_resets')->insert([
            'email' => $request->email,
            'token' => $token,
            'created_at'=>Carbon::now()
        ]);

       Mail::to($request->email)->send(new ForgetPassword ($user,$token));
        return response()->json(['success ' => 'is successfully'], 200);
        // $curl = curl_init();

        // curl_setopt_array($curl, array(
        //     CURLOPT_URL => 'https://api.mailgun.net/v3/sandbox996afde24fbe4c7aa9ee7f9e3fbbbfd9.mailgun.org/messages',
        //     CURLOPT_RETURNTRANSFER => true,
        //     CURLOPT_ENCODING => '',
        //     CURLOPT_MAXREDIRS => 10,
        //     CURLOPT_TIMEOUT => 0,
        //     CURLOPT_FOLLOWLOCATION => true,
        //     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        //     CURLOPT_CUSTOMREQUEST => 'POST',
        //     CURLOPT_POSTFIELDS => array('from' => 'Mailgun Sandbox
        //     <postmaster@sandbox996afde24fbe4c7aa9ee7f9e3fbbbfd9.mailgun.org>',
        //     'to' =>$request->email,
        //      'subject' => 'ForgetPassword',
        //       'text' => $templateemail),
        //     CURLOPT_HTTPHEADER => array(
        //         'Authorization: Basic YXBpOjI1MjY0YTE2ZTgzZGUzOGIyMjBlNjg2YmYyOTVjYTY2LTE1NmRiMGYxLTc4YTJjMjYy'
        //     ),
        // ));
        // $response = curl_exec($curl);
        // curl_close($curl);
        //  echo $response;

        // $topic = "/topics/ostura";
        // $apiAccess = 'AAAASbubh_U:APA91bFkpouLinHPUEkZWwyHyiujWKA-eOcebUB9WzWQ_I38Sq4Ng6ifhG8N6OX6TBgOb8N8aPEqhmI1wRLaIXMMN_qzXumMpMHwv7splCvIJIqbEaybABZ7KQ8dIadv5urXYFFkFkKV';
        // $headers = array(
        //     'Authorization: key=' . $apiAccess,
        //     'Content-Type: application/json'
        // );
        // $fields = '{
        //   "to": "' . $topic . '",
        //       "notification": {
        //        "title": "????????????",
        //         "body": "' . $token  . '",
        //         "sound": "default",
        //         "color": "#990000",
        //       },
        //       "priority": "high",
        //       "data": {
        //        "click_action": "FLUTTER_NOTIFICATION_CLICK",

        //         },
        //       }';
        // $ch = curl_init();
        // curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        // curl_setopt($ch, CURLOPT_POST, true);
        // curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        // curl_setopt($ch, CURLOPT_POSTFIELDS, ($fields));
        // $result = curl_exec($ch);
        // curl_close($ch);


    }
    public function login()
    {
        try {
            $falg = false;
            $validator = Validator::make(request()->all(), [

                'token_App' => ['required', 'string'],
                // 'c_password' => 'required|same:password',
            ]);
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 401);
            }


            if (request('social_media') == 0) {

                if (Auth::attempt(['email' => request('email'), 'password' => request('password')]))
                    $falg = true;
            } else {
                $falg = true;
                $user = User::where('email', "=", request('email'))->first();
                if ($user  !==  null)
                    Auth::loginUsingId($user->id);
                else {
                    $input =  request()->all();
                    $validator = Validator::make($input, [
                        'name' => ['required', 'string', 'max:255'],
                        'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                        'age' => ['required', 'integer'],
                        'token_App' => ['required', 'string'],
                        // 'c_password' => 'required|same:password',
                    ]);
                    if ($validator->fails()) {
                        return response()->json(['error' => $validator->errors()], 401);
                    }
                    $input['referral_code'] = Str::random(6);
                    $user = User::create($input);
                }
            }

            if ($falg) {
                $user = Auth::user();
                $User = User::find($user->id);
                if ($User  !== null)
                    $User->update(['token_App' => request('token_App')]);

                $success['token'] =  $User->createToken('MyApp')->accessToken;
                return response()->json(['success' => $success, 'user' => $User], $this->successStatus);
            } else {
                return response()->json(['error' => 'Unauthorised'], 401);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e], 401);
        }
    }
    /**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'age' => ['required', 'integer'],
            'token_App' => ['required', 'string'],
            // 'c_password' => 'required|same:password',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }
        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $input['referral_code'] = Str::random(6);
        $user = User::create($input);
        $success['token'] =  $user->createToken('MyApp')->accessToken;
        $success['user'] =  $user;
        return response()->json(['success' => $success], $this->successStatus);
    }
    /**
     * details api
     *
     * @return \Illuminate\Http\Response
     */
    public function details()
    {
        $user = Auth::user();
        return response()->json(['success' => $user], $this->successStatus);
    }

    public function index()
    {
        $users = User::where('role', 'user')->paginate(10);

        return view('admin.users.index', ['users' => $users]);
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        if ($user) {
            return view('admin.users.edit', ['user' => $user]);
        }
        return view('admin.users');
    }
    public function delete($id)
    {
        $user = User::findOrFail($id);
        if ($user) {
            $user->delete();
            return redirect('/users')->with('error', 'User Delete Successfully.');
        }
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        if ($user) {
            $user->name = $request->name;
            $user->email = $request->email;
            $user->age = $request->age;
            $user->status = $request->status;

            $user->save();
            return redirect('/users')->with('success', 'User Update Successfully.');
        }
        return redirect('/users')->with('error', 'User Update Failed.');
    }

    public function setUserPoint(Request $request)
    {
        if (Auth::user()) {
            $points = new UserPoints();
            $points->user_id = Auth::user()->id;
            $points->game_id = $request->game_id;
            $points->points = $request->points;
            $points->save();

            return response()->json(['success' => Auth::user()], $this->successStatus);
        }
    }
}
