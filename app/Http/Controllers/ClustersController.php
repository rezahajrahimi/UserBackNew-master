<?php

namespace App\Http\Controllers;

use App\Models\ClusterImage;
use App\Models\ClusterLog;
use App\Models\Clusters;
use App\Models\ClusterEsell;
use App\Models\ClWearhouse;
use App\Models\ClWearhouseAxel;
use App\Models\Factory;
use App\Models\Clustersize;
use App\Models\User;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\ImageManagerStatic as Image;
use Storage;
use Verta;
use File;
use Carbon\Carbon;
use GuzzleHttp\RetryMiddleware;

class ClustersController extends Controller
{
    private function newEvent($type, $details, $itemtype, $sharingLinks)
    {
        $event = new EventsController();
        $event->newEvent($type, $details, $itemtype, $sharingLinks);
        return;
    }
    public function getLastCluster()
    {
        $factoryId = auth('api')->user()->factoryId;
        $cluster = Clusters::where('factoryId', $factoryId)
            ->orderBy('created_at', 'desc')
            ->first();
        return response()->json($cluster);
    }
    public function getLastClusters($count)
    {
        $factoryId = auth('api')->user()->factoryId;
        $cluster = Clusters::where('factoryId', $factoryId)
            ->orderBy('id', 'desc')
            ->take($count)
            ->get();
        return response()->json($cluster);
    }
    public function getGroupedCluster()
    {
        $factoryId = auth('api')->user()->factoryId;
        $claster = DB::table('clusters')
            ->where('factoryId', $factoryId)
            ->where('existence', '>', 1)
            ->select('clusterNameStone', DB::raw('count(*) as total'))
            ->groupBy('clusterNameStone')
            ->orderBy('total', 'desc')
            ->take(5)
            ->get();
        return response()->json($claster);
    }
    public function getGroupedClusterName()
    {
        $factoryId = auth('api')->user()->factoryId;
        $claster = DB::table('clusters')
            ->where('factoryId', $factoryId)
            ->select('clusterNameStone')
            ->groupBy('clusterNameStone')
            ->get();
        return response()->json($claster);
    }
    public function getGroupedClusterWarehouse()
    {
        $factoryId = auth('api')->user()->factoryId;
        $claster = DB::table('clusters')
            ->where('factoryId', $factoryId)
            ->select('warehouse')
            ->groupBy('warehouse')
            ->get();
        return response()->json($claster);
    }
    public function getAllClusterAnalyticsData()
    {
        $getAllGroupedCluster = $this->getAllGroupedCluster();
        $getlastMonthCluster = $this->getlastMonthCluster();
        $getMostClusterExistence = $this->getMostClusterExistence();
        $data = [$getAllGroupedCluster, $getlastMonthCluster, $getMostClusterExistence];
        return $data;
    }
    public function getAllGroupedCluster()
    {
        $factoryId = auth('api')->user()->factoryId;
        $claster = DB::table('clusters')
            ->where('factoryId', $factoryId)
            ->where('existence', '>', 1)
            ->select('clusterNameStone', DB::raw('count(*) as total'), DB::raw('sum(existence) as existence'))
            ->groupBy('clusterNameStone')
            ->orderBy('total', 'desc')
            ->take(10)
            ->get();
        return $claster;
    }
    public function getMostClusterExistence()
    {
        $factoryId = auth('api')->user()->factoryId;
        $claster = DB::table('clusters')
            ->where('factoryId', $factoryId)
            ->where('existence', '>', 1)
            ->select('clusterNameStone', DB::raw('count(*) as total'), DB::raw('sum(existence) as existence'))
            ->groupBy('clusterNameStone')
            ->orderBy('existence', 'desc')
            ->take(10)
            ->get();
        return $claster;
    }
    public function getlastMonthCluster()
    {
        $factoryId = auth('api')->user()->factoryId;
        $v = Carbon::now();
        $lastMonth = $v->subMonth();
        $cluster = DB::table('clusters')
         ->leftjoin('cl_final_stats', 'cl_final_stats.cluster_id', '=', 'clusters.id')
            ->where('factoryId', $factoryId)
            ->where('createddatein', '>=', $lastMonth)
            ->select('clusterNameStone', DB::raw('count(*) as total'), DB::raw('sum(cl_final_stats.final_existence) as existence'))
            ->groupBy('clusterNameStone')
            ->orderBy('total', 'desc')
            ->get();
        return $cluster;
    }
    public function getCountCluster()
    {
        $factoryId = auth('api')->user()->factoryId;
        $count = Clusters::where('factoryId', $factoryId)
            ->where('existence', '>', 1)
            ->count();
        return response()->json($count);
    }
    public function addCluster(Request $request)
    {
        $clPremisssonCtrl = new ClusterPremissionController();
        if ($clPremisssonCtrl->getUserClusterPremissonByIdAndTypeNOnJson(auth('api')->user()->id, 'add') == false) {
            return response()->json(false, 401);
        }

        $factoryId = auth('api')->user()->factoryId;
        if (
            Clusters::where('clusterNumber', $request->clusterNumber)
                ->where('factoryId', $factoryId)
                ->count() != 0
        ) {
            return response()->json('duplicate', 400);
        } else {
            $data = new Clusters();
            $data->factoryId = $factoryId;
            $data->clusterNumber = $request->clusterNumber;
            $data->clusterNameStone = $request->clusterNameStone;
            if ($data->clusterNameStone == null) {
                $data->clusterNameStone = 'نامشخص';
            }
            $data->ClusterTypeStones = $request->ClusterTypeStones;
            $data->clusterDegree = $request->clusterDegree;
            $data->finished_price = $request->finishedPrice;
            $data->finished_price_unit = $request->finishedPriceUnit;
            $data->type = $request->type ?? 'slab';

            $warehouseID = ClWearhouse::where('factoryId', $factoryId)
                ->where('name', $request->warehouse)
                ->first();
            if ($warehouseID != null) {
                $data->clWearhouseId = $warehouseID->id;
            } else {
                $warehouseID = ClWearhouse::where('factoryId', $factoryId)
                    ->where('name', 'انبار اصلی')
                    ->first();
                if ($warehouseID != null) {
                    $data->clWearhouseId = $warehouseID->id;
                } else {
                    $wearhouse = new ClWearhouse();
                    $wearhouse->name = 'انبار اصلی';
                    $wearhouse->factoryId = $factoryId;
                    $wearhouse->save();
                    $data->clWearhouseId = $wearhouse->id;
                }
            }
            // $warehouseID = $request->warehouse;
            $data->createddatein = $this->getMiladyDate($request->createddatein);
            $data->sharingLinks = $this->generateRandomString();
            $data->existence = 0;
            $count = Clusters::where('sharingLinks', $data->sharingLiks)->count();
            while ($count != 0) {
                $data->sharingLinks = $this->generateRandomString();
                $count = Clusters::where('sharingLinks', $data->sharingLinks)->count();
            }
            $data->save();
            if ($request->row != null || $request->col != null) {
                $clWearhouseAxel = new ClWearhouseAxel();
                $clWearhouseAxel->factoryId = $factoryId;
                $clWearhouseAxel->clusterId = $data->id;
                $clWearhouseAxel->clWearhouseId = $data->clWearhouseId;
                if ($request->row) {
                    $clWearhouseAxel->row = $request->row;
                } else {
                    $clWearhouseAxel->row = '0';
                }
                if ($request->col) {
                    $clWearhouseAxel->col = $request->col;
                } else {
                    $clWearhouseAxel->col = '0';
                }
                $clWearhouseAxel->save();
            }
            $this->newEvent('addCls', 'دسته ' . $data->clusterNumber . ' توسط ' . auth('api')->user()->name . ' اضافه گردید.', 'cluster', $data->sharingLinks);

            return response()->json([$data->id]);
        }
    }
    public function deleteCluster($id)
    {
        $clPremisssonCtrl = new ClusterPremissionController();
        if ($clPremisssonCtrl->getUserClusterPremissonByIdAndTypeNOnJson(auth('api')->user()->id, 'del') == false) {
            return response()->json(false, 401);
        }
        $factoryId = auth('api')->user()->factoryId;
        $data = Clusters::where('factoryId', $factoryId)
            ->where('id', $id)
            ->first();
        if($data->factoryId != $factoryId){
            return response()->json(false, 401);
        }
        // check cluster has order
        $clOrderSizeController = new OrderSizeController();

        $clOrderSize = $clOrderSizeController->showOrderSizeByClusterId($id);

        if ($clOrderSize->count() > 0) {
            \Log::info($clOrderSize);
            return response()->json(false, 401);
        }
        $path = public_path() . '/storage/img/cluster/' . $data->imageThumb;
        if (file_exists($path) && $data->imageThumb != 'noimage.jpg') {
            unlink($path);
        }
        $this->newEvent('delCls', 'دسته ' . $data->clusterNumber . ' توسط ' . auth('api')->user()->name . ' حذف گردید.', 'cluster', $data->sharingLinks);

        $data->delete();
        $clWearhouseAxel = ClWearhouseAxel::where('clusterId', $id)->first();
        if ($clWearhouseAxel != null) {
            $clWearhouseAxel->delete();
        }
        $cluSize = Clustersize::where('clusterId', $id);
        if ($cluSize != null) {
            $cluSize->delete();
        }
        $cluLog = ClusterLog::where('clusterId', $id);
        if ($cluLog != null) {
            $cluLog->delete();
        }

        $imgClu = ClusterImage::where('clusters_id', $id)->get();
        foreach ($imgClu as $imgsrc) {
            $path = public_path() . '/storage/img/cluster/' . $imgsrc->imageSrc;
            if (file_exists($path)) {
                unlink($path);
            }
        }
        $cluImg = ClusterImage::where('clusters_id', $id);

        if ($cluImg != null) {
            $cluImg->delete();
        }
        return response()->json(true, 200);
    }
    public function clusterAjaxSearch(Request $request)
    {
        $clPremisssonCtrl = new ClusterPremissionController();
        if ($clPremisssonCtrl->getUserClusterPremissonByIdAndTypeNOnJson(auth('api')->user()->id, 'view') == false) {
            return response()->json(false, 401);
        }

        $clusterNameStone = $request->clusterNameStone;
        $clusterNumber = $request->clusterNameStone;
        $factoryId = auth('api')->user()->factoryId;
        $data = Clusters::where('factoryId', $factoryId)
            ->orderBy('clusterNumber', 'desc')
            ->when($clusterNameStone, function ($q) use ($clusterNameStone) {
                return $q->where('clusterNameStone', 'like', '%' . $clusterNameStone . '%');
            })
            ->get();
        $data2 = Clusters::where('factoryId', $factoryId)
            ->orderBy('clusterNumber', 'desc')
            ->when($clusterNumber, function ($q) use ($clusterNumber) {
                return $q->where('clusterNumber', 'like', '%' . $clusterNumber . '%');
            })
            ->get();
        $data = $data->merge($data2);
        return $data;
    }
    public function searchInCluster(Request $request)
    {
        $clusterNameStone = $request->clusterNameStone;
        $clusterNumber = $request->clusterNumber;
        $lastdate = $this->getMiladyDate($request->dateLast);
        $firstDate = $this->getMiladyDate($request->dateFirst);
        $lowExistance = $request->lowExistance;
        $highExistance = $request->highExistance;
        $factoryId = auth('api')->user()->factoryId;

        $data = Clusters::where('factoryId', $factoryId)
            ->orderBy('clusterNumber', 'desc')
            ->when($clusterNameStone, function ($q) use ($clusterNameStone) {
                return $q->where('clusterNameStone', 'like', '%' . $clusterNameStone . '%');
            })
            ->when($clusterNumber, function ($q) use ($clusterNumber) {
                return $q->where('clusterNumber', $clusterNumber);
            })
            ->when($lowExistance, function ($q) use ($lowExistance, $highExistance) {
                return $q->whereBetween('existence', [$lowExistance, $highExistance]);
            })
            ->when($firstDate, function ($q) use ($firstDate, $lastdate) {
                return $q->whereBetween('createddatein', [$firstDate, $lastdate]);
            })

            ->get();
        return response()->json([$data]);
    }
    public function clusterReport(Request $request)
    {
        $clusterNameStone = $request->clusterNameStone;
        $clusterTypeStone = $request->ClusterTypeStones;
        $warehouse = $request->warehouse;
        $lastdate = $this->getMiladyDate($request->dateLast);
        $firstDate = $this->getMiladyDate($request->dateFirst);
        $lowExistance = $request->existenceMin;
        $highExistance = $request->existenceMax;
        $countMin = $request->countMin;
        $countMax = $request->countMax;
        $lengthMin = $request->lengthMin;
        $lengthMax = $request->lengthMax;
        $widthMin = $request->widthMin;
        $widthMax = $request->widthMax;
        if($request->warehouse != null) {
            $factoryId = auth('api')->user()->factoryId;

           $clWare =ClWearhouse::where('factoryId', $factoryId)
            ->where('name', $request->warehouse)
            ->first();
            $warehouse = $clWare->id;
        }

        $factoryId = auth('api')->user()->factoryId;
        if ($lengthMin != null || $widthMin != null) {
            $data = Clusters::where('factoryId', $factoryId)
                ->leftjoin('clustersizes', 'clustersizes.clusterId', '=', 'clusters.id')
                ->orderBy('clusterNumber', 'desc')
                ->when($clusterNameStone, function ($q) use ($clusterNameStone) {
                    return $q->where('clusters.clusterNameStone',$clusterNameStone);
                })
                ->when($clusterTypeStone, function ($q) use ($clusterTypeStone) {
                    return $q->where('clusters.ClusterTypeStones', $clusterTypeStone);
                })
                ->when($warehouse, function ($q) use ($warehouse) {
                    return $q->where('clusters.clWearhouseId', $warehouse);
                })
                ->when($lowExistance, function ($q) use ($lowExistance, $highExistance) {
                    return $q->whereBetween('clustersizes.sum', [$lowExistance, $highExistance]);
                })
                ->when($firstDate, function ($q) use ($firstDate, $lastdate) {
                    return $q->whereBetween('clusters.createddatein', [$firstDate, $lastdate]);
                })
                ->when($countMin, function ($q) use ($countMin, $countMax) {
                    return $q->whereBetween('clustersizes.count', [$countMin, $countMax]);
                })
                ->when($lengthMin, function ($q) use ($lengthMin, $lengthMax) {
                    return $q->whereBetween('clustersizes.length', [$lengthMin, $lengthMax]);
                })
                ->when($widthMin, function ($q) use ($widthMin, $widthMax) {
                    return $q->whereBetween('clustersizes.width', [$widthMin, $widthMax]);
                })
                ->get();
        } else {
            $data = Clusters::where('factoryId', $factoryId)
                ->orderBy('clusterNumber', 'desc')
                ->when($clusterNameStone, function ($q) use ($clusterNameStone) {
                    return $q->where('clusterNameStone',$clusterNameStone);
                })
                ->when($clusterTypeStone, function ($q) use ($clusterTypeStone) {
                    return $q->where('clusters.ClusterTypeStones', $clusterTypeStone);
                })
                ->when($warehouse, function ($q) use ($warehouse) {
                    return $q->where('clusters.clWearhouseId', $warehouse);
                })
                ->when($lowExistance, function ($q) use ($lowExistance, $highExistance) {
                    return $q->whereBetween('existence', [$lowExistance, $highExistance]);
                })
                ->when($firstDate, function ($q) use ($firstDate, $lastdate) {
                    return $q->whereBetween('createddatein', [$firstDate, $lastdate]);
                })
                ->when($countMin, function ($q) use ($countMin, $countMax) {
                    return $q->whereBetween('count', [$countMin, $countMax]);
                })
                ->when($countMin, function ($q) use ($countMin, $countMax) {
                    return $q->whereBetween('count', [$countMin, $countMax]);
                })
                ->get();
        }
        // return $warehouse;
        return response()->json([$data]);
    }
    public function editcluster(Request $request)
    {
        $factoryId = auth('api')->user()->factoryId;
        $clPremisssonCtrl = new ClusterPremissionController();
        if ($clPremisssonCtrl->getUserClusterPremissonByIdAndTypeNOnJson(auth('api')->user()->id, 'update') == false) {
            return response()->json(false, 401);
        }
        $data = Clusters::findOrFail($request->id);
        $data->clusterNumber = $request->clusterNumber;
        $data->clusterNameStone = $request->clusterNameStone;
        $data->clusterDegree = $request->clusterDegree;
        $warehouseID = ClWearhouse::where('factoryid', $factoryId)
            ->where('name', $request->warehouse)
            ->first();

        $data->clWearhouseId = $warehouseID->id;
        $data->ClusterTypeStones = $request->ClusterTypeStones;
        $data->finished_price = $request->finishedPrice;
        $data->finished_price_unit = $request->finishedPriceUnit;

        $data->createddatein = $this->getMiladyDate($request->createddatein);

        $data->type = $request->type ?? 'slab';
        $re = $this->addLog($data->id, 'edit', 'ویرایش مشخصات دسته');
        $this->newEvent('editCls', 'دسته ' . $data->clusterNumber . ' توسط ' . auth('api')->user()->name . ' ویرایش گردید.', 'cluster', $data->sharingLinks);

        if (Clusters::where('clusterNumber', $request->clusterNumber)->count() != 0 && $request->other == null) {
            return response()->json('duplicate', 400);
        } else {
            if ($request->row != null || $request->row == '' || $request->col != null || $request->col == '') {
                $clWearhouseAxel = ClWearhouseAxel::where('clusterId', $data->id)->first();
                if ($clWearhouseAxel != null) {
                    $clWearhouseAxel->factoryId = $factoryId;
                    $clWearhouseAxel->clusterId = $data->id;
                    $clWearhouseAxel->clWearhouseId = $data->clWearhouseId;
                    if ($request->row) {
                        $clWearhouseAxel->row = $request->row;
                    } else {
                        $clWearhouseAxel->row = '0';
                    }
                    if ($request->col) {
                        $clWearhouseAxel->col = $request->col;
                    } else {
                        $clWearhouseAxel->col = '0';
                    }
                    $clWearhouseAxel->save();
                } else {
                    $clWearhouseAxel = new ClWearhouseAxel();
                    $clWearhouseAxel->factoryId = $factoryId;
                    $clWearhouseAxel->clusterId = $data->id;
                    $clWearhouseAxel->clWearhouseId = $data->clWearhouseId;
                    if ($request->row) {
                        $clWearhouseAxel->row = $request->row;
                    } else {
                        $clWearhouseAxel->row = '0';
                    }
                    if ($request->col) {
                        $clWearhouseAxel->col = $request->col;
                    } else {
                        $clWearhouseAxel->col = '0';
                    }
                    $clWearhouseAxel->save();
                }
            }
            $data->update();
            return response()->json([true]);
        }
    }
    public function showClusterById($id)
    {
        $factoryId = auth('api')->user()->factoryId;
        return Clusters::where('factoryId', $factoryId)
            ->where('id', $id)
            ->with(['clWarehouseAxel', 'clWarehouseId'])
            ->first();
    }
    public function showFactoryCluster()
    {
        $clPremissionCtrl = new ClusterPremissionController();
        if ($clPremissionCtrl->getUserClusterPremissonByIdAndTypeNOnJson(auth('api')->user()->id, 'view') == false) {
            return response()->json(false, 401);
        }
        $factoryId = auth('api')->user()->factoryId;

        return Clusters::where('factoryId', $factoryId)
            ->orderBy('id', 'desc')
            ->get();
    }
    public function addImageCluster(Request $request)
    {
        $factoryId = auth('api')->user()->factoryId;

        $image = $request->file('file');
        $filename = 'Cluster_' . time() . $request->file->getClientOriginalName();

        $path = public_path() . "/storage/img/cluster/$factoryId/$request->clusterId";
        if (!File::isDirectory($path)) {
            File::makeDirectory($path, 0755, true, true);
        }
        $image_resize = Image::make($image->getRealPath());
        $image_resize->resize(1200, null, function ($constraint) {
            $constraint->aspectRatio();
        });
        $image_resize->save(public_path("/storage/img/cluster/$factoryId/$request->clusterId/$filename"));
        $data = new ClusterImage();
        $data->clusters_id = $request->clusterId;
        $data->imageSrc = "$factoryId/$request->clusterId/$filename";
        $data->save();
        $Cluster = Clusters::findOrFail($request->clusterId);
        if ($Cluster->hasImage == 'no') {
            $filename = 'ClusterThu_' . time() . $request->file->getClientOriginalName();
            $image_resize = Image::make($image->getRealPath());
            $image_resize->resize(200, null, function ($constraint) {
                $constraint->aspectRatio();
            });
            $image_resize->save(public_path("/storage/img/cluster/$factoryId/$request->clusterId/$filename"));

            $Cluster->hasImage = 'yes';
            $Cluster->imageThumb = "$factoryId/$request->clusterId/$filename";
            $Cluster->update();
        }
        return response()->json($filename, 200);
    }
    public function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
    public function addLog($clusterId, $oprType, $oprText)
    {
        if (ClusterLog::where('clusterId', $clusterId)->count() > 100) {
            $data = ClusterLog::where('clusterId', $clusterId)->first();
            $data->delete();
        }
        $factoryId = auth('api')->user()->factoryId;
        $userName = auth('api')->user()->name;
        $data = new ClusterLog();
        $data->factoryId = $factoryId;
        $data->clusterId = $clusterId;
        $data->userName = $userName;
        $data->oprType = $oprType;
        $data->oprText = $oprText;
        $data->save();
        return true;
    }
    public function getLastClusterTimeRetrive()
    {
        $factoryId = auth('api')->user()->factoryId;
        $data = Clusters::where('factoryId', $factoryId)
            ->orderBy('updated_at', 'desc')
            ->first();
        $date = strtotime($data->updated_at);
        return response()->json($date * 1000);
    }
    public function addFakeCluster()
    {
        $factoryId = 27;
        for ($x = 0; $x <= 213; $x++) {
            $data = new Clusters();
            $data->factoryId = $factoryId;
            $data->clusterNumber = $x;
            $data->clusterNameStone = 'تست';
            if ($data->clusterNameStone == null) {
                $data->clusterNameStone = 'نامشخص';
            }
            $data->clusterDegree = 'A+';
            $data->sharingLinks = $this->generateRandomString();
            $data->existence = 100;
            $count = Clusters::where('sharingLinks', $data->sharingLiks)->count();
            while ($count != 0) {
                $data->sharingLiks = $this->generateRandomString();
                $count = Clusters::where('sharingLinks', $data->sharingLiks)->count();
            }
            $data->save();
        }
        return response()->json(true);
    }
    public function fixAllClusterDate()
    {
        $cube = Clusters::where('factoryId', 27)->get();
        foreach ($cube as $cubeFix) {
            $cubeFix->createddatein = $this->getMiladyDate($cubeFix->createddatein);
            $cubeFix->update();
        }
        return true;
    }
    public function getCluWithImgClsizeLogOrderSize($id)
    {
        $clImageController = new ClusterImageController();
        $clSizeController = new ClustersizeController();
        $clLogController = new ClusterLogController();
        $clOrderSizeController = new OrderSizeController();
        $clData = $this->showClusterById($id);
        $clImage = $clImageController->showClusterImageByClusterId($id);
        $clSize = $clSizeController->showAllClusterSizeById($id);
        $clLog = $clLogController->allClusterLogByClId($id);
        $clOrderSize = $clOrderSizeController->showOrderSizeByClusterId($id);
        $data = [$clData, $clImage, $clSize, $clLog, $clOrderSize];
        $this->newEvent('viewCls', 'دسته ' . $clData->clusterNumber . ' توسط ' . auth('api')->user()->name . ' بازدید شد.', 'cluster', $clData->sharingLinks);

        return response()->json($data);
    }
    public function getMiladyDate($oldDate)
    {
        try {
            if ($oldDate != null) {
                $v = explode('/', $oldDate);
                $y = $v[0];
                $m = $v[1];
                $d = $v[2];

                $newDat = Verta::jalaliToGregorian($y, $m, $d);
                $car = new Carbon();
                $car->year = $newDat[0];
                $car->month = $newDat[1];
                $car->day = $newDat[2];
                return $car;
            } else {
                return null;
            }
        } catch (\Throwable $th) {
            if ($oldDate != null) {
                $v = explode('-', $oldDate);
                $y = $v[0];
                $m = $v[1];
                $d = $v[2];

                $newDat = Verta::jalaliToGregorian($y, $m, $d);
                $car = new Carbon();
                $car->year = $newDat[0];
                $car->month = $newDat[1];
                $car->day = $newDat[2];
                return $car;
            } else {
                return null;
            }
        }
    }
    ////********** main section
    public function getUserFolowedClusterData()
    {
        return Clusters::leftjoin('factories', 'factories.id', '=', 'clusters.factoryId')
            ->leftjoin('cluster_esells', 'cluster_esells.cluster_id', '=', 'clusters.id')
            ->leftjoin('esells_settings', 'esells_settings.factory_id', '=', 'factories.id')

            ->where('clusters.show_in_esells', '=', 'yes')
            ->where('clusters.hasImage', '=', 'yes')
            ->where('cluster_esells.show_price', '=', 'yes')
            ->where('cluster_esells.price', '!=', 0)
            ->where('clusters.existence', '>=', 10)
            ->where('esells_settings.show_clusters', true)

            ->select('clusters.sharingLinks', 'cluster_esells.tiny_text', 'clusters.created_at', 'cluster_esells.statistics', 'cluster_esells.rate', 'clusters.clusterNameStone', 'factories.nameFac', 'factories.logoFac', 'factories.state', 'clusters.id')
            ->with('cluster_images')
            ->orderBy('clusters.created_at', 'desc')
            ->take(18)
            ->get();
    }
    public function getTopLastCluster()
    {
        return Clusters::leftjoin('factories', 'factories.id', '=', 'clusters.factoryId')
            ->leftjoin('cluster_esells', 'cluster_esells.cluster_id', '=', 'clusters.id')
            ->leftjoin('esells_settings', 'esells_settings.factory_id', '=', 'factories.id')

            ->where('esells_settings.show_clusters', true)
            ->where('clusters.show_in_esells', '=', 'yes')
            ->where('clusters.hasImage', '=', 'yes')
            ->where('cluster_esells.show_price', '=', 'yes')
            ->where('cluster_esells.price', '!=', 0)
            ->where('clusters.existence', '>=', 10)
            ->select(
                'clusters.factoryId',
                'clusters.sharingLinks',
                'clusters.imageThumb',
                'cluster_esells.price',
                'clusters.ClusterTypeStones',
                'cluster_esells.alias_title',
                'cluster_esells.tiny_text',
                'clusters.created_at',
                'cluster_esells.statistics',
                'cluster_esells.rate',

                'clusters.clusterNumber',
                'clusters.clusterNameStone',
                'factories.nameFac',
                'factories.state',
            )
            ->orderBy('clusters.created_at', 'desc')
            ->take(18)
            ->get();
    }
    public function getTopViwedCluster()
    {
        return Clusters::leftjoin('factories', 'factories.id', '=', 'clusters.factoryId')
            ->leftjoin('cluster_esells', 'cluster_esells.cluster_id', '=', 'clusters.id')
            ->leftjoin('esells_settings', 'esells_settings.factory_id', '=', 'factories.id')

            ->where('esells_settings.show_clusters', true)
            ->where('clusters.show_in_esells', '=', 'yes')
            ->where('clusters.hasImage', '=', 'yes')
            ->where('cluster_esells.show_price', '=', 'yes')
            ->where('cluster_esells.price', '!=', 0)
            ->where('clusters.existence', '>=', 10)
            ->select('clusters.factoryId', 'clusters.sharingLinks', 'clusters.imageThumb', 'cluster_esells.price', 'clusters.ClusterTypeStones', 'cluster_esells.alias_title', 'cluster_esells.tiny_text', 'cluster_esells.statistics', 'cluster_esells.rate', 'clusters.created_at', 'clusters.clusterNumber', 'clusters.clusterNameStone', 'factories.nameFac', 'factories.state')
            ->orderBy('cluster_esells.statistics', 'desc')
            ->take(8)
            ->get();
    }
    public function getRandomCluster()
    {
        return Clusters::leftjoin('factories', 'factories.id', '=', 'clusters.factoryId')
            ->where('clusters.hasImage', '=', 'yes')
            ->where('clusters.existence', '>=', 10)
            ->select('clusters.factoryId', 'clusters.id', 'clusters.existence', 'clusters.count', 'clusters.hasImage', 'clusters.imageThumb', 'clusters.created_at', 'clusters.ClusterTypeStones', 'clusters.clusterNumber', 'clusters.clusterNameStone', 'clusters.clusterDegree', 'factories.nameFac', 'factories.logoFac')
            ->orderBy('clusters.created_at', 'desc')
            ->get()
            ->random(12);
    }
    public function showClusterByShareLink($str)
    {
        $data = Clusters::where('sharingLinks', $str)
            ->leftjoin('factories', 'factories.id', '=', 'clusters.factoryId')
            ->leftjoin('cluster_esells', 'cluster_esells.cluster_id', '=', 'clusters.id')
            ->leftjoin('socialnets', 'socialnets.factoryId', '=', 'clusters.factoryId')
            ->select('clusters.*', 'factories.nameFac', 'factories.telephoneFac', 'factories.addressFac', 'factories.logoFac', 'factories.state', 'factories.description as factoryDescription', 'socialnets.instagram', 'socialnets.twitter', 'socialnets.youtube', 'socialnets.telegram', 'socialnets.facebook', 'socialnets.linkedin', 'cluster_esells.show_price', 'cluster_esells.price', 'cluster_esells.statistics', 'cluster_esells.alias_title', 'cluster_esells.tiny_text', 'cluster_esells.description')
            ->first();
        if (ClusterEsell::where('cluster_id', $data->id)->exists()) {
            $incView = ClusterEsell::where('cluster_id', $data->id)->first();
            $incView->statistics += 1;
            $incView->save();
        }
        return $data;
    }
    public function relatedCluster($stoneName)
    {
        $data = Clusters::leftjoin('factories', 'factories.id', '=', 'clusters.factoryId')
            ->where('clusters.hasImage', '=', 'yes')
            ->leftjoin('cluster_esells', 'cluster_esells.cluster_id', '=', 'clusters.id')
            ->leftjoin('esells_settings', 'esells_settings.factory_id', '=', 'factories.id')

            ->where('clusters.existence', '>=', 10)
            ->where('esells_settings.show_clusters', true)
            ->where('clusters.show_in_esells', '=', 'yes')
            ->where('clusters.hasImage', '=', 'yes')
            ->when($stoneName, function ($q) use ($stoneName) {
                return $q->where('clusters.clusterNameStone', 'like', '%' . $stoneName . '%');
            })
            ->select('clusters.factoryId', 'clusters.sharingLinks', 'clusters.imageThumb', 'cluster_esells.price', 'clusters.ClusterTypeStones', 'cluster_esells.alias_title', 'cluster_esells.tiny_text', 'clusters.created_at', 'clusters.clusterNumber', 'clusters.clusterNameStone', 'factories.nameFac', 'factories.state')
            ->orderBy('clusters.created_at', 'desc')
            ->get();
        if (!$data->isEmpty()) {
            if ($data->count() <= 8) {
                return $data;
            } else {
                return $data->random(8);
            }
        } else {
            return false;
        }
    }
    public function getClusterByFacClusterCat(Request $request)
    {
        $ClusterTypeStones = $request->ClusterTypeStones;
        $ClusterFacNames = $request->ClusterFacNames;
        return Clusters::leftjoin('factories', 'factories.id', '=', 'clusters.factoryId')
            ->leftjoin('cluster_esells', 'cluster_esells.cluster_id', '=', 'clusters.id')
            ->where('clusters.hasImage', '=', 'yes')
            ->where('clusters.existence', '>=', 10)
            ->where('clusters.show_in_esells', '=', 'yes')
            ->when($ClusterTypeStones, function ($q) use ($ClusterTypeStones) {
                return $q->where('clusters.ClusterTypeStones', '=', $ClusterTypeStones);
            })
            ->when($ClusterFacNames, function ($q) use ($ClusterFacNames) {
                return $q->where('factories.nameFac', '=', $ClusterFacNames);
            })
            ->select('clusters.factoryId', 'clusters.sharingLinks', 'clusters.imageThumb', 'cluster_esells.price', 'cluster_esells.show_price', 'cluster_esells.statistics', 'cluster_esells.rate', 'clusters.ClusterTypeStones', 'cluster_esells.alias_title', 'cluster_esells.tiny_text', 'clusters.created_at', 'clusters.clusterNumber', 'clusters.clusterNameStone', 'factories.nameFac', 'factories.state')
            ->orderBy('clusters.created_at', 'desc')
            ->get()
            ->take(200);
    }
    public function getClusterByFacClusterName(Request $request)
    {
        $ClusterTypeStones = $request->ClusterTypeStones;
        $clusterNameStone = $request->clusterNameStone;
        $ClusterFacNames = $request->ClusterFacNames;
        return Clusters::leftjoin('factories', 'factories.id', '=', 'clusters.factoryId')
            ->where('clusters.hasImage', '=', 'yes')
            ->where('clusters.existence', '>=', 10)
            ->when($ClusterTypeStones, function ($q) use ($ClusterTypeStones) {
                return $q->where('clusters.ClusterTypeStones', '=', $ClusterTypeStones);
            })
            ->when($clusterNameStone, function ($q) use ($clusterNameStone) {
                return $q->where('clusters.clusterNameStone', '=', $clusterNameStone);
            })
            ->when($ClusterFacNames, function ($q) use ($ClusterFacNames) {
                return $q->where('factories.nameFac', '=', $ClusterFacNames);
            })
            ->select('clusters.factoryId', 'clusters.id', 'clusters.existence', 'clusters.count', 'clusters.hasImage', 'clusters.imageThumb', 'clusters.created_at', 'clusters.ClusterTypeStones', 'clusters.clusterNumber', 'clusters.clusterNameStone', 'clusters.sharingLinks', 'clusters.clusterDegree', 'factories.nameFac', 'factories.logoFac')
            ->orderBy('clusters.created_at', 'desc')
            ->get()
            ->take(200);
    }
    public function getClusterByFacName(Request $request)
    {
        $ClusterFacNames = $request->ClusterFacNames;
        return Clusters::leftjoin('factories', 'factories.id', '=', 'clusters.factoryId')
            ->where('clusters.hasImage', '=', 'yes')
            ->where('clusters.existence', '>=', 10)
            ->when($ClusterFacNames, function ($q) use ($ClusterFacNames) {
                return $q->where('factories.nameFac', '=', $ClusterFacNames);
            })
            ->select('clusters.factoryId', 'clusters.id', 'clusters.existence', 'clusters.count', 'clusters.hasImage', 'clusters.imageThumb', 'clusters.created_at', 'clusters.ClusterTypeStones', 'clusters.clusterNumber', 'clusters.clusterNameStone', 'clusters.sharingLinks', 'clusters.clusterDegree', 'factories.nameFac', 'factories.logoFac')
            ->orderBy('clusters.created_at', 'desc')
            ->get()
            ->take(200);
    }
    public function getGroupedFacClusterNameStoneByClusterTypeStones(Request $request)
    {
        $ClusterTypeStones = $request->ClusterTypeStones;
        $data = Factory::where('nameFac', $request->facName)->first();
        $facID = $data->id;
        $clusters = Clusters::where('factoryId', $facID)
            ->where('existence', '>', 1)
            ->where('show_in_esells', '=', 'yes')
            ->where('ClusterTypeStones', '=', $ClusterTypeStones)
            ->select('clusterNameStone', DB::raw('count(*) as total'))
            ->groupBy('clusterNameStone')
            ->orderBy('clusterNameStone', 'asc')
            ->get();
        return response()->json($clusters);
    }
    public function getGroupedFacClusterNameStone($name)
    {
        $data = Factory::where('nameFac', $name)->first();
        $facID = $data->id;
        $clusters = Clusters::where('factoryId', $facID)
            ->where('existence', '>', 1)
            ->where('show_in_esells', '=', 'yes')
            ->select('clusterNameStone', DB::raw('count(*) as total'))
            ->groupBy('clusterNameStone')
            ->orderBy('clusterNameStone', 'asc')
            ->get();
        return response()->json($clusters);
    }
    public function getFacClTypeStonesGroup($id)
    {
        $data = Factory::where('nameFac', $id)->first();
        $facID = $data->id;
        $clusters = Clusters::where('factoryId', $facID)
            ->where('existence', '>', 1)
            ->where('show_in_esells', '=', 'yes')
            ->select('ClusterTypeStones', DB::raw('count(*) as total'))
            ->groupBy('ClusterTypeStones')
            ->orderBy('ClusterTypeStones', 'asc')
            ->get();
        return response()->json($clusters);
    }
    public function getPriceIntervalClusterByFac($id)
    {
        $fac = Factory::where('nameFac', $id)->first();
        $facID = $fac->id;
        $max = Clusters::where('factoryId', $facID)
            ->leftjoin('cluster_esells', 'cluster_esells.cluster_id', '=', 'clusters.id')
            ->where('clusters.existence', '>', 1)
            ->where('clusters.show_in_esells', '=', 'yes')
            ->select('cluster_esells.price')
            ->orderBy('cluster_esells.price', 'desc')
            ->first();
        $min = Clusters::where('factoryId', $facID)
            ->leftjoin('cluster_esells', 'cluster_esells.cluster_id', '=', 'clusters.id')
            ->where('clusters.existence', '>', 1)
            ->where('clusters.show_in_esells', '=', 'yes')
            ->select('cluster_esells.price')
            ->orderBy('cluster_esells.price', 'asc')
            ->first();
        if (!$min->price) {
            $min->price = 0;
        }
        $data = [$min->price, $max->price];
        return response()->json($data);
    }
    public function getPriceIntervalClusterByFacStoneType(Request $request)
    {
        $ClusterTypeStones = $request->ClusterTypeStones;
        $data = Factory::where('nameFac', $request->facName)->first();
        if ($data) {
            $facID = $data->id;
            $max = Clusters::where('factoryId', $facID)
                ->leftjoin('cluster_esells', 'cluster_esells.cluster_id', '=', 'clusters.id')
                ->where('clusters.existence', '>', 1)
                ->where('ClusterTypeStones', '=', $ClusterTypeStones)
                ->where('clusters.show_in_esells', '=', 'yes')
                ->select('cluster_esells.price')
                ->orderBy('cluster_esells.price', 'desc')
                ->first();
            $min = Clusters::where('factoryId', $facID)
                ->leftjoin('cluster_esells', 'cluster_esells.cluster_id', '=', 'clusters.id')
                ->where('clusters.existence', '>', 1)
                ->where('ClusterTypeStones', '=', $ClusterTypeStones)
                ->where('clusters.show_in_esells', '=', 'yes')
                ->select('cluster_esells.price')
                ->orderBy('cluster_esells.price', 'asc')
                ->first();
            if (!$min->price) {
                $min->price = 0;
            }
            $data = [$min->price, $max->price];

            return response()->json($data);
        } else {
            abort('no data');
        }
    }
    public function getExistanceIntervalClusterByFacStoneType(Request $request)
    {
        $ClusterTypeStones = $request->ClusterTypeStones;
        $data = Factory::where('nameFac', $request->facName)->first();
        if ($data) {
            $facID = $data->id;
            $max = Clusters::where('factoryId', $facID)
                ->leftjoin('cluster_esells', 'cluster_esells.cluster_id', '=', 'clusters.id')
                ->where('clusters.existence', '>', 1)
                ->where('ClusterTypeStones', '=', $ClusterTypeStones)
                ->where('clusters.show_in_esells', '=', 'yes')
                ->select('clusters.existence')
                ->orderBy('clusters.existence', 'desc')
                ->first();
            $min = Clusters::where('factoryId', $facID)
                ->leftjoin('cluster_esells', 'cluster_esells.cluster_id', '=', 'clusters.id')
                ->where('clusters.existence', '>', 1)
                ->where('ClusterTypeStones', '=', $ClusterTypeStones)
                ->where('clusters.show_in_esells', '=', 'yes')
                ->select('clusters.existence')
                ->orderBy('clusters.existence', 'asc')
                ->first();
            if (!$min->existence) {
                $min->existence = 0;
            }
            $data = [(int) $min->existence, (int) $max->existence];
            return response()->json($data);
        } else {
            abort('no data');
        }
    }
    public function getExistanceIntervalClusterByFac($id)
    {
        $fac = Factory::where('nameFac', $id)->first();
        $facID = $fac->id;
        $max = Clusters::where('factoryId', $facID)
            ->leftjoin('cluster_esells', 'cluster_esells.cluster_id', '=', 'clusters.id')
            ->where('clusters.existence', '>', 1)
            ->where('clusters.show_in_esells', '=', 'yes')
            ->select('clusters.existence')
            ->orderBy('clusters.existence', 'desc')
            ->first();
        $min = Clusters::where('factoryId', $facID)
            ->leftjoin('cluster_esells', 'cluster_esells.cluster_id', '=', 'clusters.id')
            ->where('clusters.existence', '>', 1)
            ->where('clusters.show_in_esells', '=', 'yes')
            ->select('clusters.existence')
            ->orderBy('clusters.existence', 'asc')
            ->first();
        if (!$min->existence) {
            $min->existence = 0;
        }
        $data = [(int) $min->existence, (int) $max->existence];
        return response()->json($data);
    }
    public function clusterReportByFacName(Request $request)
    {
        $clusterNameStone = $request->clusterNameStone;
        $clusterTypeStone = $request->ClusterTypeStones;
        $priceMin = $request->priceMin;
        $priceMax = $request->priceMax;
        $lowExistance = $request->existenceMin;
        $highExistance = $request->existenceMax;
        $countMin = $request->countMin;
        $countMax = $request->countMax;
        $lengthMin = $request->lengthMin;
        $lengthMax = $request->lengthMax;
        $widthMin = $request->widthMin;
        $widthMax = $request->widthMax;

        $data = Factory::where('nameFac', $request->facName)->first();
        $factoryId = $data->id;
        if ($lengthMin != null || $widthMin != null) {
            $data = Clusters::where('factoryId', $factoryId)
                ->leftjoin('cluster_esells', 'cluster_esells.cluster_id', '=', 'clusters.id')
                ->leftjoin('clustersizes', 'clustersizes.clusterId', '=', 'clusters.id')
                ->leftjoin('factories', 'factories.id', '=', 'clusters.factoryId')
                ->where('clusters.existence', '>', 1)
                ->where('clusters.show_in_esells', '=', 'yes')
                ->when($clusterNameStone, function ($q) use ($clusterNameStone) {
                    return $q->whereIn('clusters.clusterNameStone', $clusterNameStone);
                })
                ->when($clusterTypeStone, function ($q) use ($clusterTypeStone) {
                    return $q->where('clusters.ClusterTypeStones', $clusterTypeStone);
                })
                ->when($lowExistance, function ($q) use ($lowExistance, $highExistance) {
                    return $q->whereBetween('clustersizes.sum', [$lowExistance, $highExistance]);
                })
                ->when($priceMin, function ($q) use ($priceMin, $priceMax) {
                    return $q->whereBetween('cluster_esells.price', [$priceMin, $priceMax]);
                })
                ->when($countMin, function ($q) use ($countMin, $countMax) {
                    return $q->whereBetween('clustersizes.count', [$countMin, $countMax]);
                })
                ->when($lengthMin, function ($q) use ($lengthMin, $lengthMax) {
                    return $q->whereBetween('clustersizes.length', [$lengthMin, $lengthMax]);
                })
                ->when($widthMin, function ($q) use ($widthMin, $widthMax) {
                    return $q->whereBetween('clustersizes.width', [$widthMin, $widthMax]);
                })
                ->select('clusters.*', 'cluster_esells.show_price', 'cluster_esells.price', 'cluster_esells.alias_title', 'cluster_esells.tiny_text', 'cluster_esells.description', 'factories.nameFac', 'factories.state')
                ->get();
        } else {
            $data = Clusters::where('factoryId', $factoryId)
                ->leftjoin('cluster_esells', 'cluster_esells.cluster_id', '=', 'clusters.id')
                ->leftjoin('factories', 'factories.id', '=', 'clusters.factoryId')
                ->where('existence', '>', 1)
                ->where('show_in_esells', '=', 'yes')
                ->when($clusterNameStone, function ($q) use ($clusterNameStone) {
                    return $q->whereIn('clusters.clusterNameStone', $clusterNameStone);
                })
                ->when($clusterTypeStone, function ($q) use ($clusterTypeStone) {
                    return $q->where('clusters.ClusterTypeStones', $clusterTypeStone);
                })
                ->when($lowExistance, function ($q) use ($lowExistance, $highExistance) {
                    return $q->whereBetween('clusters.existence', [$lowExistance, $highExistance]);
                })
                ->when($priceMin, function ($q) use ($priceMin, $priceMax) {
                    return $q->whereBetween('cluster_esells.price', [$priceMin, $priceMax]);
                })
                ->when($countMin, function ($q) use ($countMin, $countMax) {
                    return $q->whereBetween('clusters.count', [$countMin, $countMax]);
                })
                ->select('clusters.*', 'cluster_esells.show_price', 'cluster_esells.price', 'cluster_esells.alias_title', 'cluster_esells.tiny_text', 'cluster_esells.description', 'factories.nameFac', 'factories.state')
                ->get();
        }
        return response()->json([$data]);
    }
    public function clusterReportByClusterTypeStone(Request $request)
    {
        $clusterNameStone = $request->clusterNameStone;
        $clusterTypeStone = $request->ClusterTypeStones;
        $priceMin = $request->priceMin;
        $priceMax = $request->priceMax;
        $lowExistance = $request->existenceMin;
        $highExistance = $request->existenceMax;
        $countMin = $request->countMin;
        $countMax = $request->countMax;
        $lengthMin = $request->lengthMin;
        $lengthMax = $request->lengthMax;
        $widthMin = $request->widthMin;
        $widthMax = $request->widthMax;

        if ($lengthMin != null || $widthMin != null) {
            $data = Clusters::where('clusters.ClusterTypeStones', $clusterTypeStone)
                ->leftjoin('factories', 'factories.id', '=', 'clusters.factoryId')

                ->leftjoin('esells_settings', 'esells_settings.factory_id', '=', 'factories.id')
                ->where('esells_settings.show_clusters', true)

                ->where('clusters.existence', '>', 1)
                ->where('clusters.show_in_esells', '=', 'yes')
                ->leftjoin('cluster_esells', 'cluster_esells.cluster_id', '=', 'clusters.id')
                ->leftjoin('clustersizes', 'clustersizes.clusterId', '=', 'clusters.id')
                ->when($clusterNameStone, function ($q) use ($clusterNameStone) {
                    return $q->whereIn('clusters.clusterNameStone', $clusterNameStone);
                })
                ->when($lowExistance, function ($q) use ($lowExistance, $highExistance) {
                    return $q->whereBetween('clustersizes.sum', [$lowExistance, $highExistance]);
                })
                ->when($priceMin, function ($q) use ($priceMin, $priceMax) {
                    return $q->whereBetween('cluster_esells.price', [$priceMin, $priceMax]);
                })
                ->when($countMin, function ($q) use ($countMin, $countMax) {
                    return $q->whereBetween('clustersizes.count', [$countMin, $countMax]);
                })
                ->when($lengthMin, function ($q) use ($lengthMin, $lengthMax) {
                    return $q->whereBetween('clustersizes.length', [$lengthMin, $lengthMax]);
                })
                ->when($widthMin, function ($q) use ($widthMin, $widthMax) {
                    return $q->whereBetween('clustersizes.width', [$widthMin, $widthMax]);
                })
                ->select('clusters.*', 'cluster_esells.show_price', 'cluster_esells.price', 'cluster_esells.alias_title', 'cluster_esells.tiny_text', 'cluster_esells.description', 'factories.nameFac', 'factories.state')
                ->get();
        } else {
            $data = Clusters::where('clusters.ClusterTypeStones', $clusterTypeStone)
                ->leftjoin('cluster_esells', 'cluster_esells.cluster_id', '=', 'clusters.id')
                ->leftjoin('factories', 'factories.id', '=', 'clusters.factoryId')
                ->leftjoin('esells_settings', 'esells_settings.factory_id', '=', 'factories.id')

                ->where('esells_settings.show_clusters', true)
                ->where('existence', '>', 1)
                ->where('show_in_esells', '=', 'yes')
                ->when($clusterNameStone, function ($q) use ($clusterNameStone) {
                    return $q->whereIn('clusters.clusterNameStone', $clusterNameStone);
                })
                ->when($lowExistance, function ($q) use ($lowExistance, $highExistance) {
                    return $q->whereBetween('clusters.existence', [$lowExistance, $highExistance]);
                })
                ->when($priceMin, function ($q) use ($priceMin, $priceMax) {
                    return $q->whereBetween('cluster_esells.price', [$priceMin, $priceMax]);
                })
                ->when($countMin, function ($q) use ($countMin, $countMax) {
                    return $q->whereBetween('clusters.count', [$countMin, $countMax]);
                })
                ->select('clusters.*', 'cluster_esells.show_price', 'cluster_esells.price', 'cluster_esells.alias_title', 'cluster_esells.tiny_text', 'cluster_esells.description', 'factories.nameFac', 'factories.state')
                ->get();
        }
        if ($data) {
            return response()->json([$data]);
        } else {
            abort('NO Data');
        }
    }
    public function getGroupedClusterNameStoneByClTypeStones($type)
    {
        $clusters = Clusters::where('clusters.existence', '>', 1)
            ->leftjoin('factories', 'factories.id', '=', 'clusters.factoryId')
            ->leftjoin('esells_settings', 'esells_settings.factory_id', '=', 'factories.id')

            ->where('esells_settings.show_clusters', true)
            ->where('clusters.existence', '>', 1)
            ->where('clusters.show_in_esells', '=', 'yes')
            ->where('clusters.ClusterTypeStones', '=', $type)
            ->select('clusters.clusterNameStone', DB::raw('count(*) as total'))
            ->groupBy('clusters.clusterNameStone')
            ->orderBy('clusters.clusterNameStone', 'asc')
            ->get();
        if ($clusters) {
            return response()->json($clusters);
        } else {
            abort('No Data');
        }
    }
    public function getPriceIntervalClByStoneType($type)
    {
        $ClusterTypeStones = $type;
        $max = Clusters::where('clusters.existence', '>', 1)
            ->leftjoin('factories', 'factories.id', '=', 'clusters.factoryId')
            ->leftjoin('esells_settings', 'esells_settings.factory_id', '=', 'factories.id')

            ->leftjoin('cluster_esells', 'cluster_esells.cluster_id', '=', 'clusters.id')
            ->where('esells_settings.show_clusters', true)
            ->where('ClusterTypeStones', '=', $ClusterTypeStones)
            ->where('clusters.show_in_esells', '=', 'yes')
            ->select('cluster_esells.price')
            ->orderBy('cluster_esells.price', 'desc')
            ->first();
        $min = Clusters::where('clusters.existence', '>', 1)
            ->leftjoin('factories', 'factories.id', '=', 'clusters.factoryId')
            ->leftjoin('esells_settings', 'esells_settings.factory_id', '=', 'factories.id')

            ->leftjoin('cluster_esells', 'cluster_esells.cluster_id', '=', 'clusters.id')
            ->where('esells_settings.show_clusters', true)
            ->where('ClusterTypeStones', '=', $ClusterTypeStones)
            ->where('clusters.show_in_esells', '=', 'yes')
            ->select('cluster_esells.price')
            ->orderBy('cluster_esells.price', 'asc')
            ->first();
        if (!$min->price) {
            $min->price = 0;
        }
        $data = [$min->price, $max->price];

        return response()->json($data);
    }
    public function getExistenceIntervalClByStoneType($type)
    {
        $ClusterTypeStones = $type;
        $max = Clusters::where('clusters.existence', '>', 1)
            ->leftjoin('factories', 'factories.id', '=', 'clusters.factoryId')
            ->leftjoin('esells_settings', 'esells_settings.factory_id', '=', 'factories.id')

            ->leftjoin('cluster_esells', 'cluster_esells.cluster_id', '=', 'clusters.id')
            ->where('esells_settings.show_clusters', true)
            ->where('ClusterTypeStones', '=', $ClusterTypeStones)
            ->where('clusters.show_in_esells', '=', 'yes')
            ->select('clusters.existence')
            ->orderBy('clusters.existence', 'desc')
            ->first();
        $min = Clusters::where('clusters.existence', '>', 1)
            ->leftjoin('factories', 'factories.id', '=', 'clusters.factoryId')
            ->leftjoin('esells_settings', 'esells_settings.factory_id', '=', 'factories.id')

            ->leftjoin('cluster_esells', 'cluster_esells.cluster_id', '=', 'clusters.id')
            ->where('esells_settings.show_clusters', true)
            ->where('ClusterTypeStones', '=', $ClusterTypeStones)
            ->where('clusters.show_in_esells', '=', 'yes')
            ->select('clusters.existence')
            ->orderBy('clusters.existence', 'asc')
            ->first();
        if (!$min->existence) {
            $min->existence = 0;
        }
        $data = [$min->existence, $max->existence];

        return response()->json($data);
    }
    public function searchByClName($clusterNameStone)
    {
        $data = Clusters::where('clusters.existence', '>', 1)
            ->where('clusterNameStone', 'like', '%' . $clusterNameStone . '%')
            ->leftjoin('factories', 'factories.id', '=', 'clusters.factoryId')
            ->leftjoin('esells_settings', 'esells_settings.factory_id', '=', 'factories.id')

            ->where('esells_settings.show_clusters', true)
            ->where('clusters.show_in_esells', '=', 'yes')
            ->leftjoin('cluster_esells', 'cluster_esells.cluster_id', '=', 'clusters.id')
            ->select('clusters.factoryId', 'clusters.sharingLinks', 'clusters.imageThumb', 'cluster_esells.price', 'cluster_esells.show_price', 'cluster_esells.statistics', 'cluster_esells.rate', 'clusters.ClusterTypeStones', 'cluster_esells.alias_title', 'cluster_esells.tiny_text', 'clusters.created_at', 'clusters.clusterNumber', 'clusters.clusterNameStone', 'factories.nameFac', 'factories.state')
            ->get();
        if ($data) {
            return response()->json([$data]);
        } else {
            abort('No Resault');
        }
    }
    public function showClusterByIdEsells($id)
    {
        return Clusters::where('sharingLinks', $id)->first();
    }
    public function showClusterByShareIdEsells($id)
    {
        return Clusters::where('id', $id)->first();
    }
    public function getClusterGroupedReportFilterOption()
    {
        $factoryId = auth('api')->user()->factoryId;

        $clusterNames = DB::table('clusters')
            ->where('factoryId', $factoryId)
            ->select('clusterNameStone')
            ->groupBy('clusterNameStone')
            ->get();
            $clWarehouseController = new ClWearhouseController();
        $wareHouses = $clWarehouseController->getFactoryClWarehouse();
        $data = [$clusterNames, $wareHouses];
        return $data;
    }
    public function getClusteIdByshareId($shareId)
    {
        $data = Clusters::where('sharingLinks', $shareId)
            ->where('factoryId', auth('api')->user()->factoryId)
            ->firstOrFail();
        if ($data) {
            return $data->id;
        } else {
            return response()->json(0, 401);
        }
    }
}
