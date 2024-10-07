<?php

namespace App\Http\Controllers;

use App\Models\ClFinalStats;
use App\Models\Clusters;
use App\Models\Cube;
use Illuminate\Http\Request;

class ClFinalStatsController extends Controller
{
    public function setFinalExistence($clusterId, $existence)
    {
        $clFinal = ClFinalStats::firstOrNew(['cluster_id' => $clusterId]);
        $factoryId = auth('api')->user()->factoryId;

        $clFinal->final_existence = $existence;
        $clFinal->factory_id = $factoryId;
        $clFinal->save();
    }
    public function bindCubeToFinalExistenceCluster($clusterId, $cubeId)
    {
        $factoryId = auth('api')->user()->factoryId;
        $clFinal = ClFinalStats::where('cluster_id', $clusterId)
            ->where('factory_id', $factoryId)
            ->first();
        if ($clFinal != null) {
            $clFinal->cube_id = $cubeId;
            $clFinal->save();
        }
        return $clFinal;
    }
    public function setFinalStatsCluster(Request $request)
    {
        $factoryId = auth('api')->user()->factoryId;

        $clFinal = ClFinalStats::where('cluster_id', $request->clusterId)
            ->where('factory_id', $factoryId)
            ->first();
        if ($clFinal != null) {
            if ($request->description != null) {
                $clFinal->description = $request->description;
            }
            if ($request->cubeId != null) {
                $clFinal->cube_id = $request->cubeId;
            }
            $clFinal->update();
            $cluster = Clusters::findOrFail($request->clusterId);
            $cluster->finished_price = $request->finishedPrice;
            $cluster->finished_price_unit = $request->finishedPriceUnit;

            $cluster->update();
            return response()->json(true, 200);
        } else {
            return response()->json(false, 401);
        }
    }
    public function getFinalStatsByClusterId($clusterId)
    {
        $factoryId = auth('api')->user()->factoryId;

        $clFinal = ClFinalStats::where('cluster_id', $clusterId)
            ->where('factory_id', $factoryId)
            ->with(['cluster:id,clusterNumber,clusterNameStone,existence as current_existence,finished_price,finished_price_unit', 'cube:id,nameCube,cubeNumber,weight,length,width,height,bought_price'])
            ->first();
        $clAdditionCont = new ClProduceAdditionItemController();
        $clAdditions = $clAdditionCont->getClProduceAdditionItemList($clusterId);

        if ($clFinal->cube_id != null) {
            $cuAdditionCont = new CuCuttingAdditionItemController();

            $cuAdditions = $cuAdditionCont->getCuAdditionItemList($clFinal->cube_id);
            $cuCutCont = new CuCuttingTimeController();
            $cuCut = $cuCutCont->getCuttingTimeByCubeId($clFinal->cube_id);
            return response()->json([$clFinal, $clAdditions, $cuAdditions, $cuCut], 200);
        } else {
            $cube = Cube::where('factoryId', $factoryId)
                ->where('cubeNumber', $clFinal['cluster']['clusterNumber'])
                ->first();
            if ($cube != null) {
                $this->bindCubeToFinalExistenceCluster($clusterId, $cube->id);
                $clFinal = ClFinalStats::where('cluster_id', $clusterId)
                    ->where('factory_id', $factoryId)
                    ->with(['cluster:id,clusterNumber,clusterNameStone,existence as current_existence,finished_price,finished_price_unit', 'cube:id,nameCube,cubeNumber,weight,length,width,height,bought_price'])
                    ->first();

                $cuAdditionCont = new CuCuttingAdditionItemController();

                $cuAdditions = $cuAdditionCont->getCuAdditionItemList($cube->id);
                $cuCutCont = new CuCuttingTimeController();
                $cuCut = $cuCutCont->getCuttingTimeByCubeId($cube->id);
                return response()->json([$clFinal, $clAdditions, $cuAdditions, $cuCut], 200);
            }
            return response()->json([$clFinal, $clAdditions, null, null], 200);
        }
        // saw name
    }
    public function modifyClusterStat(){
        $cluster = Clusters::all();
        foreach ($cluster as $clFix) {
            $clFinal = new ClFinalStats();
            $clFinal->cluster_id = $clFix->id;
            $clFinal->final_existence =  $clFix->existence;
            $clFinal->factory_id = $clFix->factoryId;
            $clFinal->save();
        }
        return true;
    }
}
