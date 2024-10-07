<?php

namespace App\Http\Controllers;
use App\Models\EsellOrders;

use App\Models\EsellOrderDetails;
use Illuminate\Http\Request;

class EsellOrderDetailsController extends Controller
{
    public function showClusterOrderEsellDetailsById($id)
    {
        $data = EsellOrderDetails::where('esell_order_details.esells_order_id',$id)
        ->whereNotNull('esell_order_details.cluster_id')
        ->leftjoin('clusters', 'clusters.id', '=', 'esell_order_details.cluster_id')
        ->leftjoin('clustersizes', 'clustersizes.id', '=', 'esell_order_details.cluster_size_id')
            ->select(
                'esell_order_details.*',
                // 'cubes.nameCube',
                // 'cubes.cubeNumber',
                // 'cubes.weight',
                // 'cubes.length',
                // 'cubes.width',
                // 'cubes.height',
                // 'cubes.imageThumb as cubeImage',
                'clusters.clusterNumber',
                'clusters.clusterNameStone',
                'clusters.imageThumb as clusterImage',
                'clusters.id as clusterID',
                'clustersizes.length',
                'clustersizes.width',
            )->get();
        if ($this->checkUser($data->first()->id)) {
            return $data;
        } else {
            return response()->json(false, 401);
        }
    }
    public function showCubeOrderEsellDetailsById($id)
    {
        $data = EsellOrderDetails::where('esell_order_details.esells_order_id',$id)
        ->whereNotNull('esell_order_details.cube_id')
        ->leftjoin('cubes', 'cubes.id', '=', 'esell_order_details.cube_id')
            ->select(
                'esell_order_details.*',
                'cubes.nameCube',
                'cubes.id as cubeId',
                'cubes.cubeNumber',
                'cubes.weight',
                'cubes.length',
                'cubes.width',
                'cubes.height',
                'cubes.imageThumb as cubeImage'
            )->get();
        if ($this->checkUser($data->first()->id)) {
            return $data;
        } else {
            return response()->json(false, 401);
        }
    }
    public function checkUser($order_id)
    {
        $userfactoryId = auth('api')->user()->factoryId;
        $data = EsellOrders::findorfail($order_id);
        if ($data->factory_id == $userfactoryId) {
            return true;
        } else {
            return false;
        }
    }
}
