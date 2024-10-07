<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClusterLog;
use Illuminate\Support\Facades\Auth;

class ClusterLogController extends Controller
{
    public function ClusterLogByClId($id)
    {
        $factoryId = auth('api')->user()->factoryId;
        return ClusterLog::where('factoryId', $factoryId)->where('clusterId', $id)
        ->orderBy('created_at', 'desc')
        ->take(10)
        ->get();
    }
    public function allClusterLogByClId($id)
    {
        $factoryId = auth('api')->user()->factoryId;
        return ClusterLog::where('factoryId', $factoryId)->where('clusterId', $id)
        ->orderBy('created_at', 'desc')
        ->get();
    }
}
