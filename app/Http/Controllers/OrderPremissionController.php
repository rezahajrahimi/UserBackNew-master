<?php

namespace App\Http\Controllers;

use App\Models\OrderPremission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use DB;
use App\Models\User;

class OrderPremissionController extends Controller
{
    public function getUserOrderPremisson()
    {
        $userID = auth('api')->user()->id;
        $data = OrderPremission::where('user_id', $userID)->first();
        return response()->json($data);
    }
    public function getUserOrderPremissonById($userID)
    {
        if (auth('api')->user()->type == 'manager') {
            $data = OrderPremission::where('user_id', $userID)->first();
            return response()->json([$data], 200);
        } else {
            return response()->json([false], 400);
        }
    }
    public function getUserOrderPremissonByIdNOnJson($userID)
    {
        return OrderPremission::where('user_id', $userID)
        ->select('order_view','order_add','order_del','order_update')
        ->first();
    }
    public function getUserOrderPremissonByIdAndTypeNOnJson($userID,$type)
    {
        $data = OrderPremission::where('user_id', $userID)
        ->select('order_view','order_add','order_del','order_update')
        ->first();
        if ($type == 'view') {
            return $data->order_view;
        }
        if ($type == 'add') {
            return $data->order_add;
        }
        if ($type == 'del') {
            return $data->order_del;
        }
        if ($type == 'update') {
            return $data->order_update;
        }
    }
    public function updateUserOrderPremisson(Request $request)
    {
        if (auth('api')->user()->type == 'manager') {
            // $userId = $request->userId;
            $data = OrderPremission::where('user_id', $request->userId)->first();
            $data->order_view = $request->order_view;
            $data->order_add = $request->order_add;
            $data->order_del = $request->order_del;
            $data->order_update = $request->order_update;
            return response()->json([$data->update()]);
        } else {
            return response()->json([false]);
        }
    }
}
