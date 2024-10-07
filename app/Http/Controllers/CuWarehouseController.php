<?php

namespace App\Http\Controllers;

use App\Models\CuWarehouse;
use App\Models\Cube;

use Illuminate\Http\Request;

class CuWarehouseController extends Controller
{
    public function getFactoryCuWarehouse()
    {
        $factoryId = auth('api')->user()->factoryId;
        return CuWarehouse::where('factoryId', $factoryId)->get();
    }
    public function addFactoryCuWarehouse(Request $request)
    {
        $cuPremisssonCtrl = new CubePremissionController();
        if ($cuPremisssonCtrl->getUserCubePremissonByIdAndTypeNOnJson(auth('api')->user()->id, 'update') == false) {
            return response()->json(false, 401);
        }

        $factoryId = auth('api')->user()->factoryId;
        $checkDuplicate = CuWarehouse::where('factoryId', $factoryId)
            ->where('name', $request->name)
            ->get();
        if ($checkDuplicate->count() >= 1) {
            return $this->getFactoryCuWarehouse();
        } else {
            $clWearhouse = new CuWarehouse();
            $clWearhouse->name = $request->name;
            $clWearhouse->factoryId = $factoryId;
            $clWearhouse->address =  $request->address;
            $clWearhouse->tel = $request->tel;
            $clWearhouse->save();
            return $this->getFactoryCuWarehouse();
        }
    }
    public function updateFactoryCuWarehouse(Request $request)
    {
        $cuPremisssonCtrl = new CubePremissionController();
        if ($cuPremisssonCtrl->getUserCubePremissonByIdAndTypeNOnJson(auth('api')->user()->id, 'update') == false) {
            return response()->json(false, 401);
        }

        $factoryId = auth('api')->user()->factoryId;
        $checkDuplicate = CuWarehouse::where('factoryId', $factoryId)
            ->where('name', $request->newName)
            ->get();
        if ($checkDuplicate->count() >= 1) {
            return $this->getFactoryCuWarehouse();
        } else {
            $CuWarehouse = CuWarehouse::where('factoryId', $factoryId)
                ->where('name', $request->name)
                ->first();
            if ($CuWarehouse != null) {
                $CuWarehouse->name = "$request->newName";
                $CuWarehouse->update();
            }
            return $this->getFactoryCuWarehouse();
        }
    }
    public function deleteFactoryCuWarehouse($name)
    {
        $cuPremisssonCtrl = new CubePremissionController();
        if ($cuPremisssonCtrl->getUserCubePremissonByIdAndTypeNOnJson(auth('api')->user()->id, 'update') == false) {
            return response()->json(false, 401);
        }

        $factoryId = auth('api')->user()->factoryId;
        $cuWarehouse = CuWarehouse::where('factoryId', $factoryId)
            ->where('name', $name)
            ->first();
        $cuWarehouseId = $cuWarehouse->id;
        $cube = Cube::where('factoryId', $factoryId)
            ->where('cuWarehouseId', $cuWarehouseId)
            ->get();
        if ($cube->count()>0) {
            return response()->json([false, 'Some cubes exists in this warehouse'], 401);
        } else {
            $cuWarehouse->delete();
            return $this->getFactoryCuWarehouse();
        }
    }
}
