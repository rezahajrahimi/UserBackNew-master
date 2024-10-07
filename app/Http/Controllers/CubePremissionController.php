<?php

namespace App\Http\Controllers;

use App\Models\CubePremission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use DB;
use App\Models\User;

class CubePremissionController extends Controller
{
    public function getUserCubePremisson()
    {
        $userID = auth('api')->user()->id;
        $data = CubePremission::where('user_id', $userID)->first();
        return response()->json($data);
    }
    public function getUserCubePremissonById($userID)
    {
        if (auth('api')->user()->type == 'manager') {
            $data = CubePremission::where('user_id', $userID)->first();
            return response()->json([$data], 200);
        } else {
            return response()->json([false], 400);
        }
    }
    public function getUserCubePremissonByIdAndTypeNOnJson($userID,$type)
    {
        $data = CubePremission::where('user_id', $userID)
            ->select('cube_view', 'cube_add', 'cube_del', 'cube_update', 'cube_cutting')
            ->first();
        if ($type == 'view') {
            return $data->cube_view;
        }
        if ($type == 'add') {
            return $data->cube_add;
        }
        if ($type == 'del') {
            return $data->cube_del;
        }
        if ($type == 'update') {
            return $data->cube_update;
        }
    }
    public function getUserCubePremissonByIdNOnJson($userID)
    {
        return CubePremission::where('user_id', $userID)
            ->select('cube_view', 'cube_add', 'cube_del', 'cube_update', 'cube_cutting')
            ->first();
    }
    public function updateUserCubePremisson(Request $request)
    {
        if (auth('api')->user()->type == 'manager') {
            $data = CubePremission::where('user_id', $request->userId)->first();
            $data->cube_view = $request->cube_view;
            $data->cube_add = $request->cube_add;
            $data->cube_del = $request->cube_del;
            $data->cube_update = $request->cube_update;
            $data->cube_cutting = $request->cube_cutting;
            return response()->json([$data->update()]);
        } else {
            return response()->json([false]);
        }
    }
}
