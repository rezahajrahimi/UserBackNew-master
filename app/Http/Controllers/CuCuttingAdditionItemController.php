<?php

namespace App\Http\Controllers;

use App\Models\CuCuttingAdditionItem;
use Illuminate\Http\Request;

class CuCuttingAdditionItemController extends Controller
{
    public function checkUserPremisson($type)
    {
        $userID = auth('api')->user()->id;
        $cubeCtrl = new CubePremissionController();
        return $cubeCtrl->getUserCubePremissonByIdAndTypeNOnJson($userID, $type);
    }
    public function getCuAdditionItemList($cubeId)
    {
        $factoryId = auth('api')->user()->factoryId;
        return CuCuttingAdditionItem::where('factory_id', $factoryId)
            ->where('cube_id', $cubeId)
            ->with(['cuCuttingAddition'])
            ->get();
    }
    public function addCuAdditionItem(Request $request)
    {
        if ($this->checkUserPremisson('update') == false) {
            return response()->json(false, 401);
        }
        $factoryId = auth('api')->user()->factoryId;
        $cuCuttingAdditionController = new CuCuttingAdditionController();
        $cuAdditionID = $cuCuttingAdditionController->returnCuItemIdByNameUnit($request->additionName,$request->additionUnit);
        $cuCuttingAdditionItem = new CuCuttingAdditionItem();
        $cuCuttingAdditionItem->cube_id = $request->cubeId;
        $cuCuttingAdditionItem->cu_cutting_addition_id = $cuAdditionID;
        $cuCuttingAdditionItem->quantity = $request->quantity;
        $cuCuttingAdditionItem->factory_id = $factoryId;
        $cuCuttingAdditionItem->save();
        return $this->getCuAdditionItemList($request->cubeId);
    }
    public function updateCuAdditionItem(Request $request)
    {
        if ($this->checkUserPremisson('update') == false) {
            return response()->json(false, 401);
        }
        $factoryId = auth('api')->user()->factoryId;
        $cuCuttingAdditionController = new CuCuttingAdditionController();

        $cuAdditionID = $cuCuttingAdditionController->returnCuItemIdByNameUnit($request->additionName,$request->additionUnit);

        $cuCuttingAdditionItem = CuCuttingAdditionItem::where('cube_id', $request->cubeId)
            ->where('cu_cutting_addition_id', $cuAdditionID)
            ->where('factory_id', $factoryId)
            ->first();
        if ($cuCuttingAdditionItem != null) {
            $cuCuttingAdditionItem->quantity = $request->quantity;
            $cuCuttingAdditionItem->save();
            return $this->getCuAdditionItemList($request->cubeId);
        } else {
            return $this->getCuAdditionItemList($request->cubeId);
        }
    }
    public function deleteCuAdditionItem(Request $request)
    {
        if ($this->checkUserPremisson('update') == false) {
            return response()->json(false, 401);
        }
        $factoryId = auth('api')->user()->factoryId;
        $cuCuttingAdditionController = new CuCuttingAdditionController();

        $cuAdditionID = $cuCuttingAdditionController->returnCuItemIdByNameUnit($request->additionName,$request->additionUnit);

        $cuCuttingAdditionItem = CuCuttingAdditionItem::where('cube_id', $request->cubeId)
            ->where('cu_cutting_addition_id', $cuAdditionID)
            ->where('factory_id', $factoryId)
            ->first();
        if ($cuCuttingAdditionItem != null) {
            $cuCuttingAdditionItem->delete();
            return $this->getCuAdditionItemList($request->cubeId);
        } else {
            return $this->getCuAdditionItemList($request->cubeId);
        }
    }
}
