<?php

namespace App\Http\Controllers;

use App\Models\ClProduceAddition;
use App\Models\ClProduceAdditionItem;
use Illuminate\Http\Request;

class ClProduceAdditionController extends Controller
{
    public function checkUserPremisson($type)
    {
        $userID = auth('api')->user()->id;
        $clCtrl = new ClusterPremissionController();
        return $clCtrl->getUserClusterPremissonByIdAndTypeNOnJson($userID, $type);
    }
    public function getFactoryClProduceAdditions()
    {
        $factoryId = auth('api')->user()->factoryId;
        return ClProduceAddition::where('factory_id', $factoryId)->get();
    }
    public function addFactoryClProduceAdditions(Request $request)
    {
        if ($this->checkUserPremisson('update') == false) {
            return response()->json(false, 401);
        }

        $factoryId = auth('api')->user()->factoryId;
        $checkExists = ClProduceAddition::where('factory_id', $factoryId)
            ->where('name', $request->name)
            ->where('unit', $request->unit)
            ->first();
        if ($checkExists == null) {
            $clCuttingAddition = new ClProduceAddition();
            $clCuttingAddition->name = $request->name;
            $clCuttingAddition->unit = $request->unit;
            $clCuttingAddition->factory_id = $factoryId;
            $clCuttingAddition->save();
            return $this->getFactoryClProduceAdditions();
        } else {
            if($request->newName != null) {
                $checkExists->name = $request->newName;

            }
            if($request->newUnit  != null) {
                $checkExists->unit = $request->newUnit;

            }
            $checkExists->update();
            return $this->getFactoryClProduceAdditions();
        }
    }
    public function deleteFactoryClProduceAdditions(Request $request)
    {
        if ($this->checkUserPremisson('update') == false) {
            return response()->json(false, 401);
        }

        $factoryId = auth('api')->user()->factoryId;
        $checkExists = ClProduceAddition::where('factory_id', $factoryId)
            ->where('name', $request->name)
            ->where('unit', $request->unit)
            ->first();
        if ($checkExists != null) {
            $ClProduceAdditionId = $checkExists->id;
            $clCuttingAdditionItem = ClProduceAdditionItem::where('cl_produce_addition_id', $ClProduceAdditionId)->get();
            if ($clCuttingAdditionItem->count() > 0) {
                return response()->json([false, 'Some Additions Items have relation by this Item'], 201);
            } else {
                $checkExists->delete();
                return $this->getFactoryClProduceAdditions();
            }
        } else {
            return $this->getFactoryClProduceAdditions();
        }
    }
    public function returnClItemIdByNameUnit($name,$unit) {
        $factoryId = auth('api')->user()->factoryId;
        return ClProduceAddition::where('factory_id', $factoryId)
            ->where('name', $name)
            ->where('unit', $unit)
            ->first()->id;
    }

}
