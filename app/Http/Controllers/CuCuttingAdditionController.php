<?php

namespace App\Http\Controllers;

use App\Models\CuCuttingAddition;
use App\Models\Cube;
use App\Models\CuCuttingAdditionItem;

use Illuminate\Http\Request;

class CuCuttingAdditionController extends Controller
{
    public function checkUserPremisson($type)
    {
        $userID = auth('api')->user()->id;
        $cubeCtrl = new CubePremissionController();
        return $cubeCtrl->getUserCubePremissonByIdAndTypeNOnJson($userID, $type);
    }
    public function getCuCuttingAdditions()
    {
        $factoryId = auth('api')->user()->factoryId;
        return CuCuttingAddition::where('factory_id', $factoryId)->get();
    }
    public function addCuCuttingAdditions(Request $request)
    {
        if ($this->checkUserPremisson('update') == false) {
            return response()->json(false, 401);
        }

        $factoryId = auth('api')->user()->factoryId;
        $checkExists = CuCuttingAddition::where('factory_id', $factoryId)
            ->where('name', $request->name)
            ->where('unit', $request->unit)
            ->first();
        if ($checkExists == null) {
            $cuCuttingAddition = new CuCuttingAddition();
            $cuCuttingAddition->name = $request->name;
            $cuCuttingAddition->unit = $request->unit;
            $cuCuttingAddition->factory_id = $factoryId;
            $cuCuttingAddition->save();
            return $this->getCuCuttingAdditions();
        } else {
            if($request->newName != null) {
                $checkExists->name = $request->newName;

            }
            if($request->newUnit != null) {
                $checkExists->unit = $request->newUnit;

            }
            $checkExists->update();
            return $this->getCuCuttingAdditions();
        }
    }
    public function deleteCuCuttingAdditions(Request $request)
    {
        if ($this->checkUserPremisson('update') == false) {
            return response()->json(false, 401);
        }

        $factoryId = auth('api')->user()->factoryId;
        $checkExists = CuCuttingAddition::where('factory_id', $factoryId)
            ->where('name', $request->name)
            ->where('unit', $request->unit)
            ->first();
        if ($checkExists != null) {
            $CuCuttingAdditionId = $checkExists->id;
            $cuCuttingAdditionItem = CuCuttingAdditionItem::where('cu_cutting_addition_id', $CuCuttingAdditionId)->get();
            if ($cuCuttingAdditionItem->count() > 0) {
                return response()->json([false, 'Some Additions Items have relation by this Item'], 401);
            } else {
                $checkExists->delete();
                return $this->getCuCuttingAdditions();
            }
        } else {
            return $this->getCuCuttingAdditions();
        }
    }
    public function returnCuItemIdByNameUnit($name,$unit) {
        $factoryId = auth('api')->user()->factoryId;
        return CuCuttingAddition::where('factory_id', $factoryId)
            ->where('name', $name)
            ->where('unit', $unit)
            ->first()->id;

    }


}
