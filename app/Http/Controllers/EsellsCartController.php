<?php

namespace App\Http\Controllers;

use App\Models\EsellsCart;
use App\Models\Clusters;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EsellsCartController extends Controller
{
    public function addItemToCarts(Request $request)
    {
        $customer_id = auth('api')->user()->id;
        $da = EsellsCart::where('customer_id', $customer_id)->where('cluster_id', $request->cluster_id);
        if ($da->count() > 0) {
            $data = EsellsCart::find($da->first()->id);
            $data->count = $request->count + $data->count;
            if ($this->CheckMainClusterExistance($data->count, $request->cluster_id)) {
                if ($data->update()) {
                    $data = EsellsCart::where('customer_id', $data->customer_id)
                        ->leftjoin('clusters', 'clusters.id', '=', 'esells_carts.cluster_id')
                        ->leftjoin('cluster_esells', 'cluster_esells.cluster_id', '=', 'esells_carts.cluster_id')
                        ->select(
                            'esells_carts.*',
                            'clusters.clusterNumber',
                            'clusters.clusterNameStone',
                            'clusters.imageThumb as clusterImage',
                            'clusters.id as clusterID',
                            'clusters.sharingLinks',
                            'cluster_esells.price as clusterPrice'
                        )
                        ->get();
                    return $data;
                } else return false;
            } else return response("overflow", 200);
        } else {
            $data = new EsellsCart();
            $data->customer_id = auth('api')->user()->id;
            $data->cube_id = $request->cube_id;
            $data->cluster_id = $request->cluster_id;
            $data->count = $request->count;
            if ($data->save()) {
                $data = EsellsCart::where('customer_id', $data->customer_id)
                    ->leftjoin('clusters', 'clusters.id', '=', 'esells_carts.cluster_id')
                    ->leftjoin('cluster_esells', 'cluster_esells.cluster_id', '=', 'esells_carts.cluster_id')
                    ->select(
                        'esells_carts.*',
                        'clusters.clusterNumber',
                        'clusters.clusterNameStone',
                        'clusters.imageThumb as clusterImage',
                        'clusters.id as clusterID',
                        'clusters.sharingLinks',
                        'cluster_esells.price as clusterPrice'
                    )->get();
                return $data;
            } else return false;
        }
    }
    public function updateItemInCarts(Request $request)
    {
        $customer_id = auth('api')->user()->id;
        $da = EsellsCart::where('customer_id', $customer_id)->where('cluster_id', $request->cluster_id);
        if ($da->count() > 0) {
            $data = EsellsCart::find($da->first()->id);
            $data->count = $request->count;
            if ($this->CheckMainClusterExistance($data->count, $request->cluster_id)) {
                if ($data->update()) {
                    $data = EsellsCart::where('customer_id', $data->customer_id)
                        ->leftjoin('clusters', 'clusters.id', '=', 'esells_carts.cluster_id')
                        ->leftjoin('cluster_esells', 'cluster_esells.cluster_id', '=', 'esells_carts.cluster_id')
                        ->select(
                            'esells_carts.*',
                            'clusters.clusterNumber',
                            'clusters.clusterNameStone',
                            'clusters.imageThumb as clusterImage',
                            'clusters.id as clusterID',
                            'clusters.sharingLinks',
                            'cluster_esells.price as clusterPrice'
                        )
                        ->get();
                    return $data;
                } else return false;
            } else return response("overflow", 200);
        } return false;
    }
    public function CheckMainClusterExistance($cartNewCount, $clusterId)
    {
        $cl = Clusters::findorfail($clusterId);
        if ($cartNewCount <= $cl->existence) {
            return true;
        } else return false;
    }
    public function getUserCart()
    {
        $customer_id = auth('api')->user()->id;
        $data = EsellsCart::where('customer_id', $customer_id)
            ->leftjoin('clusters', 'clusters.id', '=', 'esells_carts.cluster_id')
            ->leftjoin('factories', 'factories.id', '=', 'clusters.factoryId')
            ->leftjoin('cluster_esells', 'cluster_esells.cluster_id', '=', 'esells_carts.cluster_id')
            ->select(
                'esells_carts.*',
                'clusters.clusterNumber',
                'clusters.clusterNameStone',
                'clusters.imageThumb as clusterImage',
                'clusters.id as clusterID',
                'clusters.ClusterTypeStones',
                'clusters.existence',
                'clusters.sharingLinks',
                'factories.nameFac',
                'cluster_esells.price as clusterPrice'
            )
            ->get();
        return $data;
    }
    public function removeItemFromCartsById($id)
    {
        $checkUserAuth = $this->checkUser($id);
        if ($checkUserAuth) {
            $data = EsellsCart::findorfail($id);
            return $data->delete();
        } else return response()->json(false, 401);
    }
    public function checkUser($cart_id)
    {
        $userId = auth('api')->user()->id;
        $data = EsellsCart::findorfail($cart_id);
        if ($data->customer_id == $userId) {
            return true;
        } else {
            return false;
        }
    }
}
