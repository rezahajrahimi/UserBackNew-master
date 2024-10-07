<?php

namespace App\Http\Controllers;

use App\Models\CuSaw;
use App\Models\CuCuttingTime;
use Illuminate\Http\Request;

class CuSawController extends Controller
{
    public function getFactoryCuSaws()
    {
        $factoryId = auth('api')->user()->factoryId;
        return CuSaw::where('factory_id', $factoryId)->get();
    }
    public function getFactoryCuSawsIdByName($name)
    {
        $factoryId = auth('api')->user()->factoryId;
        return CuSaw::where('factory_id', $factoryId)
        ->where('name',$name)
        ->first()->id;
    }
    public function addFactoryCuSaws(Request $request)
    {
        $cuPremisssonCtrl = new CubePremissionController();
        if ($cuPremisssonCtrl->getUserCubePremissonByIdAndTypeNOnJson(auth('api')->user()->id, 'update') == false) {
            return response()->json(false, 401);
        }

        $factoryId = auth('api')->user()->factoryId;
        $checkDuplicate = CuSaw::where('factory_id', $factoryId)
            ->where('name', $request->name)
            ->get();
        if ($checkDuplicate->count() >= 1) {
            return $this->getFactoryCuSaws();
        } else {
            $cuSaw = new CuSaw();
            $cuSaw->name = $request->name;
            $cuSaw->factory_id = $factoryId;
            $cuSaw->save();
            return $this->getFactoryCuSaws();
        }
    }
    public function updateFactoryCuSaws(Request $request)
    {
        $cuPremisssonCtrl = new CubePremissionController();
        if ($cuPremisssonCtrl->getUserCubePremissonByIdAndTypeNOnJson(auth('api')->user()->id, 'update') == false) {
            return response()->json(false, 401);
        }

        $factoryId = auth('api')->user()->factoryId;
        $checkDuplicate = CuSaw::where('factory_id', $factoryId)
            ->where('name', $request->newName)
            ->get();
        if ($checkDuplicate->count() >= 1) {
            return $this->getFactoryCuSaws();
        } else {
            $CuWarehouse = CuSaw::where('factory_id', $factoryId)
                ->where('name', $request->name)
                ->first();
            if ($CuWarehouse != null) {
                $CuWarehouse->name = "$request->newName";
                $CuWarehouse->update();
            }
            return $this->getFactoryCuSaws();
        }
    }
    public function deleteFactoryCuSaws($name)
    {
        $cuPremisssonCtrl = new CubePremissionController();
        if ($cuPremisssonCtrl->getUserCubePremissonByIdAndTypeNOnJson(auth('api')->user()->id, 'update') == false) {
            return response()->json(false, 401);
        }

        $factoryId = auth('api')->user()->factoryId;
        $cuSaw = CuSaw::where('factory_id', $factoryId)
            ->where('name', $name)
            ->first();
        $cuSawId = $cuSaw->cube;
        $cuCuttingTime = CuCuttingTime::where('factory_id', $factoryId)
            ->where('saw_id', $cuSawId)
            ->get();
        if ($cuCuttingTime->count()>0) {
            return response()->json([false, 'Some cubes cutted with this saw'], 401);
        } else {
            $cuSaw->delete();
            return $this->getFactoryCuSaws();
        }
    }
}
