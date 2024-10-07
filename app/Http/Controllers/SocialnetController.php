<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\socialnet;
use Illuminate\Support\Facades\Auth;

class SocialnetController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function addSocial(Request $request)
    {
        $data = new socialnet();
        $user = Auth::user();
        $data->factoryId = $user->factoryId;
        $data->instagram = $request->instagram;
        $data->telegram = $request->telegram;
        $data->facebook = $request->facebook;
        $data->twitter = $request->twitter;
        $data->youtube = $request->youtube;
        $data->linkedin = $request->linkedin;
        return response()->json([$data->save()]);
    }
    public function updateSocial(Request $request)
    {
        $user = Auth::user();
        $data = socialnet::where('factoryId', $request->factoryId)->first();
        $data->instagram = $request->instagram;
        $data->telegram = $request->telegram;
        $data->facebook = $request->facebook;
        $data->twitter = $request->twitter;
        $data->youtube = $request->youtube;
        $data->linkedin = $request->linkedin;
        return $data->update();
    }
    public function getFactorySocial(Request $request)
    {
        $user = Auth::user();
        if(socialnet::where('factoryId', $user->factoryId)->count() < 1) {
            $data = new socialnet();
            $user = Auth::user();
            $data->factoryId = $user->factoryId;
            return response()->json([$data->save()]);
        } else {
            $data = socialnet::where('factoryId', $user->factoryId)->first();
            return response()->json([$data]);
        }

    }
}
