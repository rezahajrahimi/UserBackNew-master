<?php

namespace App\Http\Controllers;

use App\Models\ClWearhouse;
use App\Models\Clusters;
use Illuminate\Http\Request;

class ClWearhouseController extends Controller
{
    public function getFactoryClWarehouse()
    {
        $factoryId = auth('api')->user()->factoryId;
        return ClWearhouse::where('factoryId', $factoryId)->get();
    }
    public function addFactoryClWarehouse(Request $request)
    {
        $clPremisssonCtrl = new ClusterPremissionController();
        if ($clPremisssonCtrl->getUserClusterPremissonByIdAndTypeNOnJson(auth('api')->user()->id, 'update') == false) {
            return response()->json(false, 401);
        }

        $factoryId = auth('api')->user()->factoryId;
        $checkDuplicate = ClWearhouse::where('factoryId', $factoryId)
            ->where('name', $request->name)
            ->get();
        if ($checkDuplicate->count() >= 1) {
            return $this->getFactoryClWarehouse();
        } else {
            $clWearhouse = new ClWearhouse();
            $clWearhouse->name = $request->name;
            $clWearhouse->factoryId = $factoryId;
            $clWearhouse->save();
            return $this->getFactoryClWarehouse();
        }
    }
    public function updateFactoryClWarehouse(Request $request)
    {
        $clPremisssonCtrl = new ClusterPremissionController();
        if ($clPremisssonCtrl->getUserClusterPremissonByIdAndTypeNOnJson(auth('api')->user()->id, 'update') == false) {
            return response()->json(false, 401);
        }

        $factoryId = auth('api')->user()->factoryId;
        $checkDuplicate = ClWearhouse::where('factoryId', $factoryId)
            ->where('name', $request->newName)
            ->get();
        if ($checkDuplicate->count() >= 1) {
            return $this->getFactoryClWarehouse();
        } else {
            $clWearhouse = ClWearhouse::where('factoryId', $factoryId)
                ->where('name', $request->name)
                ->first();
            if ($clWearhouse != null) {
                $clWearhouse->name = "$request->newName";
                $clWearhouse->update();
            }
            return $this->getFactoryClWarehouse();
        }
    }
    public function deleteFactoryClWarehouse($name)
    {
        $clPremisssonCtrl = new ClusterPremissionController();
        if ($clPremisssonCtrl->getUserClusterPremissonByIdAndTypeNOnJson(auth('api')->user()->id, 'update') == false) {
            return response()->json(false, 401);
        }

        $factoryId = auth('api')->user()->factoryId;
        $clWarehouse = ClWearhouse::where('factoryId', $factoryId)
            ->where('name', $name)
            ->first();
        $clWarehouseId = $clWarehouse->id;
        $cluster = Clusters::where('factoryId', $factoryId)
            ->where('clWearhouseId', $clWarehouseId)
            ->get();
        if ($cluster->count()>0) {
            return response()->json([false, 'Some clusters exists in this warehouse'], 401);
        } else {
            $clWarehouse->delete();
            return $this->getFactoryClWarehouse();
        }
    }
}
