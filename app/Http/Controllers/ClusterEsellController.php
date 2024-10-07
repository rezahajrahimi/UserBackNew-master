<?php

namespace App\Http\Controllers;

use App\Models\ClusterEsell;
use Illuminate\Http\Request;
use App\Models\Clusters;
use App\Models\Cube;
use App\Models\Factory;
use App\Models\User;
use DB;
use Illuminate\Support\Facades\Auth;
use Verta;
use Carbon\Carbon;
use PHPUnit\Util\Json;

class ClusterEsellController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }
    private function newEvent($type,$details,$itemtype,$sharingLinks) {
        $event = new EventsController();
        $event->newEvent($type,$details,$itemtype,$sharingLinks);
        return;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\ClusterEsell  $clusterEsell
     * @return \Illuminate\Http\Response
     */
    public function showClusterEsellsList($page)
    {
        $factoryId = auth('api')->user()->factoryId;
        $checkPremission = Factory::findOrFail($factoryId);
        if ($checkPremission->cluster_esells == 'yes') {
            return Clusters::where('factoryId', $factoryId)
                ->where('existence', '>=', 1.0)
                ->where('clusters.show_in_esells', '=', 'yes')
                ->leftjoin('cluster_esells', 'cluster_esells.cluster_id', '=', 'clusters.id')
                ->orderBy('clusters.created_at', 'asc')
                ->select('clusters.id', 'clusters.imageThumb', 'clusters.clusterNumber', 'clusters.clusterNameStone', 'clusters.existence', 'clusters.count', 'clusters.show_in_esells', 'cluster_esells.price', 'clusters.warehouse', 'clusters.createddatein')
                ->paginate(200, ['*'], 'page', $page);
        } else {
            return response()->json(false, 401);
        }
    }
    public function clusterEsellssearch(Request $request)
    {
        $clusterNameStone = $request->clusterNameStone;
        $clusterNumber = $request->clusterNameStone;
        $factoryId = auth('api')->user()->factoryId;
        $data = Clusters::where('factoryId', $factoryId)
            ->where('existence', '>=', 1.0)
            ->where('clusters.show_in_esells', '=', 'yes')
            ->leftjoin('cluster_esells', 'cluster_esells.cluster_id', '=', 'clusters.id')
            ->orderBy('clusterNumber', 'desc')
            ->when($clusterNameStone, function ($q) use ($clusterNameStone) {
                return $q->where('clusterNameStone', 'like', '%' . $clusterNameStone . '%');
            })
            ->select('clusters.id', 'clusters.imageThumb', 'clusters.clusterNumber', 'clusters.clusterNameStone', 'clusters.existence', 'clusters.count', 'clusters.show_in_esells', 'cluster_esells.price', 'clusters.warehouse', 'clusters.createddatein')
            ->get();
        $data2 = Clusters::where('factoryId', $factoryId)
            ->where('existence', '>=', 1.0)
            ->where('clusters.show_in_esells', '=', 'yes')
            ->leftjoin('cluster_esells', 'cluster_esells.cluster_id', '=', 'clusters.id')
            ->orderBy('clusterNumber', 'desc')
            ->when($clusterNumber, function ($q) use ($clusterNumber) {
                return $q->where('clusterNumber', 'like', '%' . $clusterNumber . '%');
            })
            ->select('clusters.id', 'clusters.imageThumb', 'clusters.clusterNumber', 'clusters.clusterNameStone', 'clusters.existence', 'clusters.count', 'clusters.show_in_esells', 'cluster_esells.price', 'clusters.warehouse', 'clusters.createddatein')
            ->get();
        $data = $data->merge($data2);
        return response()->json([$data]);
    }
    public function showClusterEsellsListByFacName($facName)
    {
        $fac = Factory::where('nameFac', $facName)->first();
        $facID = $fac->id;
        return Clusters::leftjoin('factories', 'factories.id', '=', 'clusters.factoryId')
            ->leftjoin('cluster_esells', 'cluster_esells.cluster_id', '=', 'clusters.id')
            ->leftjoin('esells_settings', 'esells_settings.factory_id', '=', 'factories.id')
            ->where('clusters.factoryId', $facID)
            ->where('esells_settings.show_clusters', true)
            ->where('clusters.show_in_esells', '=', 'yes')
            ->where('clusters.existence', '>=', 1)
            ->select('clusters.factoryId', 'clusters.sharingLinks', 'clusters.imageThumb', 'cluster_esells.price', 'clusters.ClusterTypeStones', 'cluster_esells.alias_title', 'clusters.clusterNumber', 'clusters.clusterNameStone')
            ->orderBy('clusters.created_at', 'desc')
            ->get();
    }
    public function showClusterEsellById($id)
    {
        $data = ClusterEsell::firstOrCreate(['cluster_id' => $id]);
        if ($this->checkUser($data->cluster_id)) {
            return $data;
        } else {
            return response()->json(false, 401);
        }
    }
    public function editClusterEsellById(Request $request)
    {
        if ($this->checkUser($request->cluster_id)) {
            $data = ClusterEsell::findOrFail($request->id);
            $data->show_price = $request->show_price;
            if ($request->price) {
                $data->price = $request->price;
            } else {
                $data->price = 0;
            }
            $data->alias_title = $request->alias_title;
            $data->tiny_text = $request->tiny_text;
            $data->description = $request->description;
            $cluster = Clusters::findorfail($request->cluster_id);
            $cluster->show_in_esells = $request->status;
            $this->newEvent("editCluEsell","دسته ".$cluster->clusterNumber." توسط ".auth('api')->user()->name." ویرایش شد.","cluster",$cluster->sharingLinks);

            $cluster->update();

            return response()->json([$data->update()]);
        } else {
            return response()->json(false, 401);
        }
    }
    public function changeClusterEsellsStatus(Request $request)
    {
        $clPremisssonCtrl = new ClusterPremissionController();
        if ($clPremisssonCtrl->getUserClusterPremissonByIdAndTypeNOnJson(auth('api')->user()->id, 'update') == false) {
            return response()->json(false, 401);
        }

        if ($this->checkUser($request->cluster_id)) {
            $cluster = Clusters::findorfail($request->cluster_id);
            $cluster->show_in_esells = $request->status;
            $this->newEvent("editCluEsell","وضعیت نمایش دسته ".$cluster->clusterNumber." توسط ".auth('api')->user()->name." ویرایش شد.","cluster",$cluster->sharingLinks);

            return response()->json([$cluster->update()]);
        } else {
            return response()->json(false, 401);
        }
    }
    public function checkUser($cluster_id)
    {
        $userfactoryId = auth('api')->user()->factoryId;
        $data = Clusters::findorfail($cluster_id);
        if ($data->factoryId == $userfactoryId) {
            return true;
        } else {
            return false;
        }
    }
    //Exhibition
    public function getExhibitionClustersListByFactoryId($factoryId,$page)
    {
        $followersController = new FollowersController();
        // $followersController->getFollowCheckingByFactoryId($factoryId);
        $esellsSettingsController = new EsellsSettingsController();
        $ispublic = $esellsSettingsController->isPublic($factoryId);

        if ($followersController->getFollowCheckingByFactoryId($factoryId) == true || $ispublic == true) {
            return Clusters::where('factoryId', $factoryId)
                ->where('existence', '>=', 1.0)
                ->orderBy('created_at', 'desc')
                ->paginate(18, ['*'], 'page', $page);

        } else {
            return response()->json(false, 401);
        }
    }
    public function getCluWithImgClSizeFacNameImageById($id)
    {

        $clImageController = new ClusterImageController();
        $clSizeController = new ClustersizeController();
        $clController = new ClustersController();
        $facController = new FactoryController();
        $clData = $clController->showClusterByIdEsells($id);
        $clImage = $clImageController->showClusterImageByClusterIdEsells($id);
        $clSize = $clSizeController->showAllClusterSizeByIdEsells($id);
        $facInfo = $facController->getSellerInfoById($clData->factoryId);
        $data = [$clData, $clImage, $clSize,$facInfo ];
        return response()->json($data);
    }
    public function getCluWithImgClSizeFacNameImageByshareId($id)
    {

        $clImageController = new ClusterImageController();
        $clSizeController = new ClustersizeController();
        $clController = new ClustersController();
        $facController = new FactoryController();
        $clData = $clController->showClusterByIdEsells($id);
        $clImage = $clImageController->showClusterImageByClusterIdEsells($clData->id);
        $clSize = $clSizeController->showAllClusterSizeByIdEsells($clData->id);
        $facInfo = $facController->getSellerInfoById($clData->factoryId);
        $data = [$clData, $clImage, $clSize,$facInfo ];
        return response()->json($data);
    }
}
