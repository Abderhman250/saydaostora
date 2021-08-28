<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\awards;
use Validator;
use Illuminate\Support\Facades\Storage;


class AwardsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //

       

    }
    

    public function  indexApi(){
        $awards  =awards ::all();
        return response()->json(["awards"=>$awards], 200);
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // 'name', 'img', 'point'

        //    $awards = awards ::all();

        return view('admin.awards.create');
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
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'img' => ['required'],
            'point' => ['required'],
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails())
            return response()->json([$validator->errors()->first()], 401);

        $data = $request->all();
        $data['point'] =  (int) $request->input('point');

        if (!empty($request->file('img'))) {

            $file = $request->file('img');
            $file_name = time() . '.' . $file->getClientOriginalExtension();
            $destinationPath = $file->storeAs('public/', $file_name);
            $data['img'] =  Storage::disk('local')->url($file_name);
            
        }else {
            $data['imag'] = null;
        }

        $awards = awards::create($data);

        return back();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}