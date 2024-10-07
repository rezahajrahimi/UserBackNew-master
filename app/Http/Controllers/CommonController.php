<?php

namespace App\Http\Controllers;
use App\Models\Cube;
use App\Models\Clusters;
use App\Models\Order;
use App\Models\Events;
use App\Models\ClusterPremission;
use App\Models\CubePremission;
use App\Models\OrderPremission;
use App\Models\Factory;

use Illuminate\Http\Request;

class CommonController extends Controller
{
    public function getLastStatics()
    {
        $factoryId = auth('api')->user()->factoryId;
        $userID = auth('api')->user()->id;
        $countOfCubes = Cube::where('factoryId', $factoryId)
            ->where('isActive', 'yes')
            ->count();
        $countOfClusters = $count = Clusters::where('factoryId', $factoryId)
            ->where('existence', '>', 1)
            ->count();
        $countOfOrders = Order::where('factoryId', $factoryId)
            ->where('status', 0)
            ->count();
        $lastCube = Cube::where('factoryId', $factoryId)
            ->orderBy('id', 'desc')
            ->take(10)
            ->get();
        $lastCluster = Clusters::where('factoryId', $factoryId)
            ->with('clWarehouseId')
            ->orderBy('id', 'desc')
            ->take(10)
            ->get();
        $lastOrder = Order::where('factoryId', $factoryId)
            ->where('status', 0)
            ->with('customer')
            ->orderBy('id', 'desc')
            ->take(10)
            ->get();
        $lastEvents = Events::where('factory_id', $factoryId)
            ->orderBy('id', 'desc')
            ->take(10)
            ->get();

        $clusterPremission = ClusterPremission::where('user_id', $userID)->first();
        $cubePremission = CubePremission::where('user_id', $userID)->first();
        $orderPremission = OrderPremission::where('user_id', $userID)->first();
        $cubeController = new CubeController();
        $importedMOnthCube = $cubeController->getLastMonthCube();
        $clusterController = new ClustersController();
        $importedMonthClusters = $clusterController->getlastMonthCluster();
        $data = [$countOfCubes, $countOfClusters, $countOfOrders, $lastCube, $lastCluster, $lastOrder,
         $clusterPremission, $cubePremission, $orderPremission,
          $lastEvents,
        $importedMOnthCube, $importedMonthClusters
        ];
        return $data;
    }
    public function getUserPremissionFactoryDetails()
    {
        $factoryId = auth('api')->user()->factoryId;
        $userID = auth('api')->user()->id;

        $clusterPremissionController = new ClusterPremissionController();
        $cubePremissionController = new CubePremissionController();
        $orderPremissionController = new OrderPremissionController();

        $cuPre = $cubePremissionController->getUserCubePremissonByIdNOnJson($userID);
        $clPre = $clusterPremissionController->getUserClusterPremissonByIdNOnJson($userID);
        $orPre = $orderPremissionController->getUserOrderPremissonByIdNOnJson($userID);

        $factory = Factory::where('id', $factoryId)->first();
        $data = [$factory->nameFac, $factory->logoFac, $factory->servicetype, $factory->serviceexpire, $factoryId, $cuPre, $clPre, $orPre];
        return $data;
    }
}
