<?php

namespace App\Http\Controllers;

use App\Models\ClusterPremission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use DB;
use App\Models\User;

class ClusterPremissionController extends Controller
{
    public function getUserClusterPremisson()
    {
        $userID = auth('api')->user()->id;
        $data = ClusterPremission::where('user_id', $userID)->first();
        return response()->json($data);
    }
    public function getUserClusterPremissonById($userID)
    {
        if (auth('api')->user()->type == 'manager') {
            $data = ClusterPremission::where('user_id', $userID)->first();
            return response()->json([$data], 200);
        } else {
            return response()->json([false], 400);
        }
    }
    public function getUserClusterPremissonByIdNOnJson($userID)
    {
        return ClusterPremission::where('user_id', $userID)

            ->select('cluster_view', 'cluster_add', 'cluster_del', 'cluster_update')
            ->first();
    }
    public function getUserClusterPremissonByIdAndTypeNOnJson($userID,$type)
    {
        $data = ClusterPremission::where('user_id', $userID)

            ->select('cluster_view', 'cluster_add', 'cluster_del', 'cluster_update')
            ->first();
             if($type == "view") {
                return $data->cluster_view;
             }
             if($type == "add") {
                return $data->cluster_add;
             }
             if($type == "del") {
                return $data->cluster_del;
             }
             if($type == "update") {
                return $data->cluster_update;
             }
    }
    public function updateUserClusterPremisson(Request $request)
    {
        if (auth('api')->user()->type == 'manager') {
            $data = ClusterPremission::where('user_id', $request->userId)->first();
            $data->cluster_view = $request->cluster_view;
            $data->cluster_add = $request->cluster_add;
            $data->cluster_del = $request->cluster_del;
            $data->cluster_update = $request->cluster_update;
            return response()->json([$data->update()]);
        } else {
            return response()->json([false]);
        }
    }
}
