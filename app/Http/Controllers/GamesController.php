<?php

namespace App\Http\Controllers;

use App\Games;
use App\GameAttribute;
use App\Questions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\UserPoints;
// use App\Models\User;
use App\User;
use Exception;
use App\gameSession;
use function PHPUnit\Framework\isEmpty;
use Validator;
use Illuminate\Support\Facades\Storage;

class GamesController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $games = Games::all();
        return view('admin.games.index', ['games' => $games]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Games  $games
     * @return \Illuminate\Http\Response
     */
    public function show(Games $games)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Games  $games
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        $game = Games::findOrFail($id);
        $gamesattribut=GameAttribute::find($id);
        return view('admin.games.edit',compact('game','gamesattribut'));
        // if ($game) {
        //     return view('admin.games.edit', ['game' => $game]);
        // }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Games  $games
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $game = Games::findOrFail($id);
        if ($game) {
            $game->name = $request->title;
            $game->status = $request->status;
            $game->save();

            return redirect('/games')->with('success', 'Game Update Successfully.');
        }
    }
    public function TotalPoint(Request  $request)
    {

        if (Auth::user()) {

            $data['UserPoints'] =  UserPoints::where("user_id", "=", Auth::user()->id)->get();

            if ($data['UserPoints']->count() == 0)
                $data['UserPoints'] = array('points' => 0);
            return response()->json([$data], 201);
        } else
            return response()->json(["unauthorize"], 401);
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Games  $games
     * @return \Illuminate\Http\Response
     */
    public function destroy(Games $games)
    {
        //
    }

    public function updateAttributes(Request $request, $id)
    {
        $game = Games::find($id);
        if (isset($game->attributes)) {
            $game->attributes->attempts = $request->attempts;
            $game->attributes->ads_count = $request->ads_count;
            $game->attributes->points_per_try = $request->points_per_try;

            $game->attributes->save();
            return redirect('/games')->with('success', 'Game Attributes Update Successfully.');
        }

        $attributes = new GameAttribute();
        $attributes->game_id = $id;
        $attributes->attempts = $request->attempts;
        $attributes->ads_count = $request->ads_count;
        $attributes->points_per_try = $request->points_per_try;

        $attributes->save();
        return redirect('/games')->with('success', 'Game Attributes Update Successfully.');
    }


    public function referralLink($code)
    {

        if (Auth::user()) {

            $User = User::where('referral_code', '=', $code)->first();

            if ($User ===  null)
                return response()->json([$User, 'msg' => 'is not match '], 401);

            if ($User->id == Auth::user()->id)
                return view("referralLink", ["Worning" => "you can\'t use code"]);



            if ($User->visit_code >= 10)
                return view("referralLink", ["Worning" => "this code is invalid"]);




            $user_points = \App\UserPoints::updateOrCreate(
                [
                    'user_id'   => $User->id,
                ],
                [
                    'game_id' => 0,
                    'points' => $User->user_points ? $User->points : 0,
                ]
            );

            $User->increment('visit_code', 1);
            $user_points->increment('points', 10);
            $user_points->save();
            $User->Residual = 10 - $User->visit_code;
            unset($User->user_points);
            return view("referralLink", ["User" => $User, "points" => $user_points->points]);
        }
    }
    public function referral(Request $request)
    {

        if (Auth::user()) {

            $rules = [
                'code' => ['required', 'string', 'max:255'],
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails())
                return response()->json([$validator->errors()->first()], 401);


            $User = User::where('referral_code', '=', $request->input('code'))->first();

            if ($User ===  null)
                return response()->json([$User, 'msg' => 'is not match '], 401);

            if ($User->id == Auth::user()->id)
                return response()->json(['msg' => 'you can\'t use code '], 401);


            if ($User->visit_code >= 10)
                return response()->json(['msg' => 'this code is invalid '], 401);



            $user_points = \App\UserPoints::updateOrCreate(
                [
                    'user_id'   => $User->id,
                ],
                [
                    'game_id' => 0,
                    'points' => $User->user_points ? $User->points : 0,
                ]
            );

            $User->increment('visit_code', 1);
            $user_points->increment('points', 10);
            $user_points->save();
            $User->Residual = 10 - $User->visit_code;
            unset($User->user_points);
            return response()->json([$User, 'link' => url('/') . '/api/auth/referral/' . $User->referral_code, "points" => $user_points->points, 'msg' => 'is exiest,  you have a new 10 points'], 201);
        }
    }
    public function  show_referral()
    {

        if (Auth::user()) {

            $DuePoints = (10 - Auth::user()->visit_code) * 10;
            $EarnedPoints =  Auth::user()->visit_code * 10;
            return response()->json([Auth::user(), 'link' => url('/') . '/api/auth/referral/' . Auth::user()->referral_code, 'DuePoints' => $DuePoints, 'EarnedPoints' => $EarnedPoints], 201);
        }
    }
    public function joinGame(Request $request)
    {
        if (Auth::user()) {

            $game = Games::findOrFail($request->game_id);


            if ($game->status) {
                $data = [];
                $id_user =  Auth::user()->id;
                $data['user'] = Auth::user();
                $gameSession  = array(
                    1 => 'join.attempts1' . $id_user,
                    2 => 'join.attempts2' . $id_user,
                    3 => 'join.attempts3' . $id_user,
                    4 => 'join.attempts4' . $id_user,
                );

                $gameSessionAdd  = array(
                    1 => 'join.adds1' . $id_user,
                    2 => 'join.adds2' . $id_user,
                    3 => 'join.adds3' . $id_user,
                    4 => 'join.adds4' . $id_user,
                );

                $gameSessionAdd_number  = array(
                    1 => 'join.add_number1' . $id_user,
                    2 => 'join.adds_number2' . $id_user,
                    3 => 'join.adds_number3' . $id_user,
                    4 => 'join.adds_number4' . $id_user,
                );


                $attempts = session()->get($gameSession[$game->id], 0);
                $adds = session()->get($gameSessionAdd[$game->id], 0);
                $data['adds'] =  $adds;
                $data['adds_max'] = $game->attributes->ads_count;


                $data['attempts'] = $attempts;
                $data['attempts_max'] = $game->attributes->attempts;
                $data['adds_number'] = session()->get($gameSessionAdd_number[$game->id], 0);

                if ($request->show_adds  == 1) {

                    $adds_number = session()->get($gameSessionAdd_number[$game->id], 0);

                    if ($adds >= $game->attributes->ads_count) {
                        $data = array("reached_upper_limit" => true);
                        return response()->json([$data, 'msg' => 'scas'], 401, array('Content-Type' => 'application/json; charset=utf-8'));
                    } else {
                        $data = array("reached_upper_limit" => false);

                        session()->put($gameSessionAdd[$game->id], $adds + 1);

                        session()->put($gameSessionAdd_number[$game->id], $adds_number + 5);
                        $adds_number = session()->get($gameSessionAdd[$game->id], 0);
                        $data['adds_number'] = $adds_number;

                        //                         return response()->json([$data, 'msg' => 'لقد  حصلت على 5 محاولات جديدة  '], 201, array('Content-Type'=>'application/json; charset=utf-8' ));
                        return response()->json([$data, 'msg' => 'I got 5 new tries '], 201);
                    }
                }




                if ($attempts >= $game->attributes->attempts) {
                    if ($adds >= $game->attributes->ads_count) {
                        $data['can_view_adds'] = false;
                        $data['can_join_game'] = false;
                        //                         return response()->json([$data, 'msg' => 'لقد تجاوزت الحد الاعلى للمحاولات .. حاول غدا '], 401, array('Content-Type'=>'application/json; charset=utf-8' ));
                        return response()->json([$data, 'msg' => 'You have exceeded the maximum number of attempts.. try tomorrow '], 201);
                    }

                    $data['can_view_adds'] = true;
                    $data['can_join_game'] = false;
                    $adds_number = session()->get($gameSessionAdd_number[$game->id], 0);

                    if ($adds_number <= 0)
                        //                         return response()->json([$data, 'msg' => 'لقد تجاوزت الحد الاعلى للمحاولات .. شاهد أعلان '], 401, array('Content-Type'=>'application/json; charset=utf-8' ));
                        return response()->json([$data, 'msg' => 'You have exceeded the maximum number of attempts.. Watch an ad  '], 201);



                    if ($game->id == 2)
                        $data['questions'] = Questions::where('status', 1)->with('answers')->get();

                    session()->put($gameSessionAdd_number[$game->id], $adds_number - 1);

                    $data['new_attempts'] =  $adds_number;

                    return response()->json([$data], 200);
                }

                session()->put($gameSession[$game->id], $attempts + 1);

                $data['can_view_adds'] = true;
                $data['can_join_game'] = true;
                if ($game->id == 2)
                    $data['questions'] = Questions::where('status', 1)->with('answers')->get();

                $data['attempts'] = $attempts;
                $data['attempts_max'] = $game->attributes->attempts;

                return response()->json([$data], 200);
            }
        }
    }

 public function showattent(Request $request)
 {
    $user =  Auth::user()->id;
    $game_id = $request->game_id;
    $GameAttribute = GameAttribute::where("game_id", $game_id)->first();
    if (!$GameAttribute){
    return response()->json(["mesg" => "id game does not exist"], 200);
    }
    $gameSession = gameSession::where('user_id',$user)->where('game_id', $game_id)->first();
    // return $gameSession;
    if(!$gameSession)
    {
        // return response()->json(["mesg" => "Not Game"], 400);
        // $data['attempts_max'] = $GameAttribute->attempts;
        //  $data['ads_max'] = $GameAttribute->ads_count;
        //  $data['points_per_try']=$GameAttribute->ads_count;
        //  $data['game_id']=$GameAttribute->ads_count;

        $data['gameSession']['attempts'] =0;
        $data['gameSession']['ads'] = 0;

        $data['gameSession']['try_ads'] =0;
        $data['gameSession']['game_id'] = $GameAttribute->game_id;
        $data['gameSession']['user_id '] =Auth::user()->id;
        $data['GameAttribute']  = $GameAttribute;
        return response()->json(['data'=>$data],200);
    }
    $game['gameAttribute']=$GameAttribute;
    $game['gameSession']=$gameSession;
return response()->json(['data'=>$game],200);
 }

    public function  joinGame2(Request  $request)
    {

        // ["attempts","ads","try_ads","user_id"]

        $User =  Auth::user();


        $game_id = $request->input('game_id');
        $GameAttribute = GameAttribute::where("game_id", '=', $game_id)->first();
        if ($GameAttribute  ===  null)
            return response()->json(["mesg" => "id game does not exist"], 400);


        $gameSession = gameSession::where('user_id', "=", $User->id)->where('game_id', '=', $game_id)->first();
        // = ["attempts","ads","try_ads","user_id","game_id"];
        if ($gameSession === null)
            $gameSession =  gameSession::create([
                'user_id'   => Auth::user()->id,
                'game_id'  => $game_id,
                'attempts' => 0
            ]);

        $try_ads = $gameSession->try_ads;

        $attempts = $gameSession->attempts;
        $data['can_view_adds'] = true;
        $data['can_adds_try'] = true;
        $data['can_join_game'] = true;
        if ($attempts < $GameAttribute->attempts) {

            $gameSession->increment('attempts', 1);
        } elseif ($try_ads > 0) {


            $data['can_join_game'] = false;
            $gameSession->decrement('try_ads', 1);
        } else {

            if ($gameSession->ads <= $GameAttribute->ads_count)
                $data['can_view_adds'] = false;
            $data['can_adds_try'] = false;
            $data['can_join_game'] = false;
        }


        $data['attempts_max'] = $GameAttribute->attempts;
        $data['ads_max'] = $GameAttribute->ads_count;
        $data['number_add_try_ads'] = $gameSession->try_ads;
        $data['game_session']  = $gameSession;


        return response()->json([$data], 200);
    }


    public function  showAds(Request  $request)
    {
        $User =  Auth::user();

        $game_id = $request->input('game_id');
        $GameAttribute = GameAttribute::where("game_id", '=', $game_id)->first();
        if ($GameAttribute  ===  null)
            return response()->json(["mesg" => "id game does not exist"], 400);


        $gameSession = gameSession::where('user_id', "=", $User->id)->where('game_id', '=', $game_id)->first();
        // = ["attempts","ads","try_ads","user_id","game_id"];
        if ($gameSession === null)
            $gameSession =  gameSession::create([
                'user_id'   => Auth::user()->id,
                'game_id'  => $game_id,
                'try_ads' => 0,
                'ads' => 0,

            ]);

        if ($gameSession->ads < $GameAttribute->ads_count) {
            $gameSession->increment('ads', 1);
            $gameSession->increment('try_ads', 5);
            return response()->json(["mesg" => "I got five new tries "], 200);
        } else
            return response()->json(["mesg" => "You have to wait for tomorrow "], 200);
    }




    public function  GameSession(Request $request)
     {
        $User = Auth::user();
        $game_id = $request->input('game_id');
        $GameAttribute = GameAttribute::where('game_id','=', $game_id);

        if ($GameAttribute  ===  null)
        return response()->json(["mesg" => "id game does not exist"], 400);

         $gameSession = gameSession::where('user_id', "=", $User->id)->where('game_id', '=', $game_id)->first();

         $data['attempts_max'] = $GameAttribute->attempts;
         $data['ads_max'] = $GameAttribute->ads_count;
         $data['number_add_try_ads'] = $gameSession->try_ads;
         $data['game_session']  = $gameSession;
         return response()->json([$data], 200);

    }


    public function adds_show(Request $request)
    {
        $data = $request->session()->all();
        return response()->json([$data, 'msg' => 'You have exceeded the maximum number of attempts.. try tomorrow  '], 401);

        if (Auth::user()) {
            $game = Games::findOrFail($request->game_id);
            $gameSessionAdd  = array(
                1 => 'join.adds1',
                2 => 'join.adds2',
                3 => 'join.adds3',
                4 => 'join.adds4',
            );

            $gameSessionAdd_number  = array(
                1 => 'join.add_number1',
                2 => 'join.adds_number2',
                3 => 'join.adds_number3',
                4 => 'join.adds_number4',
            );

            $adds = session()->get($gameSessionAdd[$game->id], 0);
            $adds_number = session()->get($gameSessionAdd[$game->id], 0);
        }
    }
    // public function ShowAdvertisement(Request $request)
    // {
    //     if (Auth::user()) {
    //         $game = Games::findOrFail($request->game_id);


    //         if ($game->status) {
    //             $data = [];

    //             $data['user'] = Auth::user();
    //             $gameSession  = array(
    //                 1 => 'join.attempts1',
    //                 2 => 'join.attempts2',
    //                 3 => 'join.attempts3',
    //                 4 => 'join.attempts4',
    //             );

    //             $gameSessionAdd  = array(
    //                 1 => 'join.adds1',
    //                 2 => 'join.adds2',
    //                 3 => 'join.adds3',
    //                 4 => 'join.adds4',
    //             );
    //             // $data['Points'] = UserPoints::where('user_id' ,"=",Auth::user()->id)->get();

    //             // if($data['Points']->isEmpty() )
    //             //   $data['Points'] = 0;
    //             // else
    //             //  $data['Points'] = $data['Points'][0]->points;

    //             $attempts = session()->get($gameSession[$game->id], 0);


    //             $data['Points'] = $attempts;

    //             $adds = session()->get($gameSessionAdd[$game->id], 0);
    //             $data['attempts_max'] = $game->attributes->attempts;

    //             if ($attempts >= $game->attributes->attempts) {
    //                 if ($adds >= $game->attributes->ads_count) {
    //                     $data['can_view_adds'] = false;
    //                     $data['can_join_game'] = false;
    //                     return response()->json([$data, 'msg' => 'لقد تجاوزت الحد الاعلى للمحاولات .. حاول غدا '], 401);
    //                 }

    //                 $data['can_view_adds'] = true;
    //                 $data['can_join_game'] = false;

    //                 session()->put('join.adds', $adds - 1);
    //                 if ($game->id == 2)
    //                     $data['questions'] = Questions::where('status', 1)->with('answers')->get();

    //                 return response()->json([$data], 200);
    //             }

    //             session()->put($gameSession[$game->id], $attempts - 1);

    //             $data['can_view_adds'] = true;
    //             $data['can_join_game'] = true;
    //             if ($game->id == 2)
    //                 $data['questions'] = Questions::where('status', 1)->with('answers')->get();
    //             return response()->json([$data], 200);
    //         }
    //     }


    //     session()->put('join.adds', $adds + 1);
    // }


    public function viewAdds(Request $request)
    {
        // sdfgdsf sdgfsdfg sdfgsdfgsd sdfgsdfgsdf sdfgsdfg
        if (Auth::user()) {
            // want parameter from mobile to insure validations (if user complete the adds )
            session()->put('join.attempts', 0);

            return response()->json(['msg' => 'لقد حصلت على محاولات اضافية'], 200);
        }
    }

    public function getAnswer(Request $request)
    {
        if (Auth::user()) {

            $game = GameAttribute::where('game_id', $request->game_id)->first();

            if ($game) {
                $game_points = $game->points_per_try;

                $is_correct = $request->is_correct;

                $user_points = \App\UserPoints::updateOrCreate(
                    [
                        'user_id'   => Auth::user()->id,
                    ],
                    [
                        'game_id' => 0,
                        'points' => Auth::user()->user_points ? Auth::user()->points : 0,
                    ]
                );

                if ($is_correct == true) {
                    $user_points->increment('points', $game_points);
                    $user_points->save();

                    return response()->json(['points' => $user_points->points, 'status' => true], 200);
                } else {

                    if ($user_points->points <=  $game_points) {
                        $user_points->points = 0;
                        $user_points->save();

                        return response()->json(['points' => $user_points->points, 'status' => true], 200);
                    } else {

                        // $user_points->decrement('points', $game_points);
                        // $user_points->save();

                        return response()->json(['points' => $user_points->points, 'status' => true], 200);
                    }
                }
            }
            return response()->json(['msg' => 'game not found'], 401);
        }
        return response()->json(['msg' => 'not auth'], 401);
    }

    public function WheelOfFortune(Request $request)
    {
        if (Auth::user()) {
            $game = GameAttribute::where('game_id', $request->game_id)->first();
            // dd($game);
            if ($game) {
                $user_points = \App\UserPoints::updateOrCreate(
                    [
                        'user_id'   => Auth::user()->id,
                    ],
                    [
                        'game_id' => 0,
                        'points' => Auth::user()->user_points ? Auth::user()->points : 0,
                    ]
                );



                $user_points->increment('points', $request->value);

                $user_points->save();

                return response()->json(['points' => $user_points->points, 'status' => true], 200);
            }

            return response()->json(['msg' => 'game not found'], 401);
        }
        return response()->json(['msg' => 'not auth'], 401);
    }

    public function slotMachine(Request $request)
    {
        if (Auth::user()) {
            $game = GameAttribute::where('game_id', $request->game_id)->first();
            if ($game) {
                $user_points = \App\UserPoints::updateOrCreate(
                    [
                        'user_id'   => Auth::user()->id,
                    ],
                    [
                        'game_id' => 0,
                        'points' => Auth::user()->user_points ? Auth::user()->points : 0,
                    ]
                );


                if ($request->is_match == true) {
                    $game_points = $game->points_per_try;
                    $user_points->increment('points', $game_points);

                    $user_points->save();

                    return response()->json(['points' => $user_points->points, 'status' => true], 200);
                }

                return response()->json(['points' => $user_points->points, 'status' => true], 200);
            }

            return response()->json(['msg' => 'game not found'], 401);
        }
        return response()->json(['msg' => 'not auth'], 401);
    }
}
