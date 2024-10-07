<?php

namespace App\Http\Controllers;

use App\Models\ClProduceAdditionItem;
use App\Models\ClProduceAddition;

use Illuminate\Http\Request;

class ClProduceAdditionItemController extends Controller
{
    public function checkUserPremisson($type)
    {
        $userID = auth('api')->user()->id;
        $clCtrl = new ClusterPremissionController();
        return $clCtrl->getUserClusterPremissonByIdAndTypeNOnJson($userID, $type);
    }
    public function getClProduceAdditionItemList($clusterId)
    {
        $factoryId = auth('api')->user()->factoryId;
        return ClProduceAdditionItem::where('factory_id', $factoryId)
            ->where('cluster_id', $clusterId)
            ->with(['clProduceAddition'])
            ->get();
    }
    public function addClProduceAdditionItem(Request $request)
    {
        if ($this->checkUserPremisson('update') == false) {
            return response()->json(false, 401);
        }
        $factoryId = auth('api')->user()->factoryId;
        $clProduceAdditionController = new ClProduceAdditionController();
        $clAdditionID = $clProduceAdditionController->returnClItemIdByNameUnit($request->itemName, $request->itemUnit);
        $clProduceAdditionItem = new ClProduceAdditionItem();
        $clProduceAdditionItem->cluster_id = $request->clusterId;
        $clProduceAdditionItem->cl_produce_addition_id = $clAdditionID;
        $clProduceAdditionItem->quantity = $request->quantity;
        $clProduceAdditionItem->factory_id = $factoryId;
        $clProduceAdditionItem->save();
        return $this->getClProduceAdditionItemList($request->clusterId);
    }
    public function updateClProduceAdditionItem(Request $request)
    {
        if ($this->checkUserPremisson('update') == false) {
            return response()->json(false, 401);
        }
        $factoryId = auth('api')->user()->factoryId;
        $clProduceAdditionController = new ClProduceAdditionController();
        $clAdditionID = $clProduceAdditionController->returnClItemIdByNameUnit($request->itemName, $request->itemUnit);

        $clProduceAdditionItem = ClProduceAdditionItem::where('cluster_id', $request->clusterId)
            ->where('cl_produce_addition_id', $clAdditionID)
            ->where('factory_id', $factoryId)
            ->first();
        if ($clProduceAdditionItem != null) {
            $clProduceAdditionItem->quantity = $request->quantity;
            $clProduceAdditionItem->save();
            return $this->getClProduceAdditionItemList($request->clusterId);
        } else {
            return $this->getClProduceAdditionItemList($request->clusterId);
        }
    }
    public function deleteClProduceAdditionItem(Request $request)
    {
        if ($this->checkUserPremisson('update') == false) {
            return response()->json(false, 401);
        }
        $factoryId = auth('api')->user()->factoryId;
        $clProduceAdditionController = new ClProduceAdditionController();
        $clAdditionID = $clProduceAdditionController->returnClItemIdByNameUnit($request->itemName, $request->itemUnit);

        $clProduceAdditionItem = ClProduceAdditionItem::where('cluster_id', $request->clusterId)
            ->where('cl_produce_addition_id', $clAdditionID)
            ->where('factory_id', $factoryId)
            ->first();
        if ($clProduceAdditionItem != null) {
            $clProduceAdditionItem->delete();
            return $this->getClProduceAdditionItemList($request->clusterId);
        } else {
            return $this->getClProduceAdditionItemList($request->clusterId);
        }
    }
}
