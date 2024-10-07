<?php

namespace App\Http\Controllers;

use App\Models\CuCuttingTime;
use App\Models\CuSaw;
use App\Models\Cube;
use Illuminate\Http\Request;
use Verta;

class CuCuttingTimeController extends Controller
{
    private function newEvent($type, $details, $itemtype, $sharingLinks)
    {
        $event = new EventsController();
        $event->newEvent($type, $details, $itemtype, $sharingLinks);
        return;
    }
    public function addCuttingTimeToCube(Request $request)
    {
        $cuPremisssonCtrl = new CubePremissionController();
        if ($cuPremisssonCtrl->getUserCubePremissonByIdAndTypeNOnJson(auth('api')->user()->id, 'update') == false) {
            return response()->json(false, 401);
        }

        $factoryId = auth('api')->user()->factoryId;
        $cube = Cube::findOrFail($request->cubeId);
        if ($cube->factoryId == $factoryId) {
            $checkDuplicate = CuCuttingTime::where('factory_id', $factoryId)
                ->where('cube_id', $request->cubeId)
                ->first();
            if ($checkDuplicate == null) {
                $cuCutting = new CuCuttingTime();

                $cuCutting->cube_id = $request->cubeId;
                $cuCutting->factory_id = $factoryId;
                $cuCutting->started_at =$request->startedAt;
                $cuCutting->ended_at = $request->endedAt;
                $cuSaw = new CuSawController();

                $cuCutting->saw_id = $cuSaw->getFactoryCuSawsIdByName($request->cuSawName);
                // $cuCutting->save();
                $cuCutting->save();
                $cube->isActive = 'No';
                $cube->update();

                $this->newEvent('cutCu', 'کوپ ' . $cube->cubeNumber . ' توسط ' . auth('api')->user()->name . ' مصرف گردید.', 'cube', $cube->sharingLiks);

                return response()->json(true, 200);
            } else {
                $checkDuplicate->started_at = $request->startedAt;
                $checkDuplicate->ended_at =$request->endedAt;
                $cuSaw = new CuSawController();

                $checkDuplicate->saw_id = $cuSaw->getFactoryCuSawsIdByName($request->cuSawName);
                $checkDuplicate->update();
                $this->newEvent('cutCu', 'مصرف کوپ ' . $cube->cubeNumber . ' توسط ' . auth('api')->user()->name . ' ویرایش گردید.', 'cube', $cube->sharingLiks);

                return response()->json(true, 200);
            }
        } else {
            return response()->json(false, 401);
        }
    }
    public function removeCuttingTime($cubeId)
    {
        $cuPremisssonCtrl = new CubePremissionController();
        if ($cuPremisssonCtrl->getUserCubePremissonByIdAndTypeNOnJson(auth('api')->user()->id, 'update') == false) {
            return response()->json(false, 401);
        }
        $factoryId = auth('api')->user()->factoryId;
        $cube = Cube::findOrFail($cubeId);
        if ($cube->factoryId == $factoryId) {
            $cube->isActive = 'yes';
            $cuttingCu = CuCuttingTime::where('factory_id', $factoryId)
                ->where('cube_id', $cubeId)
                ->first();
            if ($cuttingCu->count() >= 1) {
                $cuttingCu->delete();
                $cube->isActive = 'yes';
                $this->newEvent('delCutCu', 'مصرف کوپ ' . $cube->cubeNumber . ' توسط ' . auth('api')->user()->name . ' لغو گردید.', 'cube', $cube->sharingLiks);
                $cube->update();
                return response()->json(true, 200);
            } else {
                return response()->json(false, 401);
            }
        } else {
            return response()->json(false, 401);
        }
    }
    public function updateCuttingTime(Request $request)
    {
        $cuPremisssonCtrl = new CubePremissionController();
        if ($cuPremisssonCtrl->getUserCubePremissonByIdAndTypeNOnJson(auth('api')->user()->id, 'update') == false) {
            return response()->json(false, 401);
        }
        $factoryId = auth('api')->user()->factoryId;

        $cuttingCu = CuCuttingTime::where('factory_id', $factoryId)
            ->where('cube_id', $request->cubeId)
            ->first();
        if ($cuttingCu->count() >= 1) {
            $cuttingCu->started_at = $request->startedAt;
            $cuttingCu->ended_at = $request->endedAt;
            $cuSaw = new CuSawController();

            $cuttingCu->saw_id = $cuSaw->getFactoryCuSawsIdByName($request->cuSawName);
            $cuttingCu->update();
            return response()->json(true, 200);
        } else {
            return response()->json(false, 201);
        }
    }
    public function getCuttingTimeByCubeId($cubeID)
    {
        $factoryId = auth('api')->user()->factoryId;

        return CuCuttingTime::where('factory_id', $factoryId)
            ->where('cube_id', $cubeID)
            ->first();
    }
    public function modifyCuCutting()
    {
        $cube = Cube::where('isActive',"No")->get();
        foreach ($cube as $cubeFix) {

                $cuCutting = new CuCuttingTime();
                $cuCutting->cube_id = $cubeFix->id;
                $cuCutting->factory_id = $cubeFix->factoryId;
                if($cubeFix->cuttingtime ==null) {
                    $cuCutting->started_at = now();
                    $cuCutting->ended_at = now();
                } else {
                    $cuCutting->started_at = Verta::parse($cubeFix->cuttingtime)->datetime();;
                $cuCutting->ended_at = Verta::parse($cubeFix->cuttingtime)->datetime();;

                }
                $cuCutting->saw_id = 1;
                $cuCutting->save();
        }
        return true;
    }
}
