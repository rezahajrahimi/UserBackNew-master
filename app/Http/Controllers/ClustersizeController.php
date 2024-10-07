<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Clusters;
use App\Models\Clustersize;
use App\Models\ClusterLog;
use App\Models\OrderSize;
use App\Models\ClFinalStats;

use Illuminate\Support\Facades\Auth;
class ClustersizeController extends Controller
{
    public function changeFinalExistence($clusterId, $existence)
    {
        $clFinal = new ClFinalStatsController();
        $clFinal->setFinalExistence($clusterId, $existence);
        return true;
    }
    public function addNewSize(Request $request)
    {
        $cluster = Clusters::findOrFail($request->clusterId);
        $factoryId = auth('api')->user()->factoryId;

        if ($factoryId != $cluster->factoryId) {
            return response()->json('Go Fuck Your Self', 401);
        }
        if ($cluster->type != 'longitudinal') {
            $da = Clustersize::where('clusterId', $request->clusterId)
                ->where('length', $request->length)
                ->where('width', $request->width);
            if ($da->count() > 0) {
                $data = Clustersize::find($da->first()->id);
                $data->count = $data->count + $request->count;
                $data->sum = $data->count * $request->width * $request->length;
                if ($request->count != 0 && $cluster->type == 'slab') {
                    $exist_number = range(1, $request->count);
                    $data->exist_number = implode(',', $exist_number);
                }
                if ($cluster->type == 'tile') {
                    $data->exist_number = '1';
                }

                $data->save();
                $sum = $request->count * $request->width * $request->length;
                $re = $this->addLog($data->clusterId, 'inc', 'افزودن موجودی جدید با متراژ ' . $sum . ' متر');
                $cluster = Clusters::findOrFail($request->clusterId);
                $cluster->existence = $cluster->existence + $sum;
                $cluster->count = $cluster->count + $request->count;
                if ($cluster->update()) {
                    $this->changeFinalExistence($cluster->id, $cluster->existence);

                    return Clustersize::where('clusterId', $data->clusterId)->get();
                } else {
                    return response()->json([$cluster->update()]);
                }
            } else {
                $data = new Clustersize();
                $data->clusterId = $request->clusterId;
                $data->length = $request->length;
                $data->width = $request->width;
                $data->count = $request->count;
                if ($request->count != 0 && $cluster->type == 'slab') {
                    $exist_number = range(1, $request->count);
                    $data->exist_number = implode(',', $exist_number);
                }
                 else {
                    $data->exist_number = '1';

                }

                $data->sum = $request->count * $request->width * $request->length;
                $re = $this->addLog($data->clusterId, 'inc', 'افزودن ردیف جدید با متراژ ' . $data->sum . ' متر');
                $data->save();
                $cluster->existence = $cluster->existence + $data->sum;
                $cluster->count = $cluster->count + $data->count;
                if ($cluster->update()) {
                    $this->changeFinalExistence($cluster->id, $cluster->existence);

                    return Clustersize::where('clusterId', $data->clusterId)->get();
                } else {
                    return response()->json([$cluster->update()]);
                }
            }
        }
        $da = Clustersize::where('clusterId', $request->clusterId)->where('width', $request->width);
        if ($da->count() > 0) {
            $data = Clustersize::find($da->first()->id);
            $data->count = 1;
            $sum = $request->width * $request->length;

            $data->sum = $data->sum + $sum;
            $data->length = $data->length + $request->length;
            $data->exist_number = '1';

            $data->update();
            $re = $this->addLog($data->clusterId, 'inc', 'افزودن موجودی جدید با متراژ ' . $sum . ' متر');
            $cluster = Clusters::findOrFail($request->clusterId);
            $cluster->existence = $cluster->existence + $sum;
            if ($cluster->update()) {
                $this->changeFinalExistence($cluster->id, $cluster->existence);

                return Clustersize::where('clusterId', $data->clusterId)->get();
            } else {
                return response()->json([$cluster->update()]);
            }
        } else {
            $data = new Clustersize();
            $data->clusterId = $request->clusterId;
            $data->length = $request->length;
            $data->width = $request->width;
            $data->count = 1;
            $data->exist_number = '1';

            $sum = $request->width * $request->length;

            $data->sum = $sum;
            $re = $this->addLog($data->clusterId, 'inc', 'افزودن ردیف جدید با متراژ ' . $sum . ' متر');
            $data->save();
            $cluster->existence = $cluster->existence + $data->sum;
            $cluster->count = $cluster->count + $data->count;
            if ($cluster->update()) {
                $this->changeFinalExistence($cluster->id, $cluster->existence);

                return Clustersize::where('clusterId', $data->clusterId)->get();
            } else {
                return response()->json([$cluster->update()]);
            }
        }
    }
    public function editclustersize(Request $request)
    {
        $factoryId = auth('api')->user()->factoryId;
        $data = Clustersize::findOrFail($request->id);
        $re = $this->addLog($data->clusterId, 'edit', 'ویرایش سایز');
        $data->length = $request->length;
        $data->width = $request->width;
        $data->count = $request->count;
        $data->sum = $request->count * $request->width * $request->length;
        if ($request->count != 0 && $cluster->type == 'slab') {
            $exist_number = range(1, $request->count);
            $data->exist_number = implode(',', $exist_number);
        }
        if ($cluster->type == 'tile') {
            $data->exist_number = '1';
        }

        $data->update();
        $existence = Clustersize::where('clusterId', $data->clusterId)->sum('sum');
        $count = Clustersize::where('clusterId', $data->clusterId)->sum('count');

        $cluster = Clusters::findOrFail($data->clusterId);
        $cluster->existence = $existence;
        $cluster->count = $count;
        if ($cluster->update()) {
            $this->changeFinalExistence($cluster->id, $cluster->existence);

            return Clustersize::where('clusterId', $data->clusterId)->get();
        } else {
            return response()->json([$cluster->update()]);
        }
    }
    public function showSizeyId($id)
    {
        return response()->json([Clustersize::where('id', $id)->get()]);
    }
    public function showAllClusterSizeById($id)
    {
        $factoryId = auth('api')->user()->factoryId;
        // $data = ClusterImage::where('clusterId',$id)->get();
        $checkFactoryId = Clusters::findOrFail($id);
        if ($checkFactoryId->factoryId == $factoryId) {
            return Clustersize::where('clusterId', $id)->get();
        } else {
            return response()->json(false, 401);
        }
    }
    public function showAllClusterSizeByIdEsells($id)
    {
        return Clustersize::where('clusterId', $id)->get();
    }
    public function deleteSize($id)
    {
        $factoryId = auth('api')->user()->factoryId;
        $data = Clustersize::findOrFail($id);
        $re = $this->addLog($data->clusterId, 'dec', 'حذف ردیف با متراژ ' . $data->sum . ' متر');
        $data->delete();
        $existence = Clustersize::where('clusterId', $data->clusterId)->sum('sum');
        $count = Clustersize::where('clusterId', $data->clusterId)->sum('count');
        $cluster = Clusters::findOrFail($data->clusterId);
        $cluster->existence = $existence;
        $cluster->count = $count;
        if ($cluster->update()) {
            $this->changeFinalExistence($cluster->id, $cluster->existence);

            return Clustersize::where('clusterId', $data->clusterId)->get();
        } else {
            return response()->json([$cluster->update()]);
        }
    }
    public function checkClusterSizeHasOrder($id)
    {
        $factoryId = auth('api')->user()->factoryId;
        $orderSize = OrderSize::where('clusterSizeId', $id)
            ->where('status', 0)

            ->count();
        if ($orderSize > 0) {
            return response()->json(true, 200);
        } else {
            return response()->json(false, 201);
        }
    }
    public function addLog($clusterId, $oprType, $oprText)
    {
        if (ClusterLog::where('clusterId', $clusterId)->count() > 10) {
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
    public function getClusterNameAndClusterSizeIdByShLinkWidthLenght(Request $request)
    {
        $cluster = Clusters::where('sharingLinks', $request->sharingLinks)->first();
        $factoryId = auth('api')->user()->factoryId;
        if ($cluster->factoryId == $factoryId) {
            $clusterNameStone = $cluster->clusterNameStone;
            $clusterNumber = $cluster->clusterNumber;
            $clusterSize = Clustersize::where('clusterId', $cluster->id)
                ->where('length', $request->length)
                ->where('width', $request->width)
                ->first();
            return response()->json([$clusterNameStone, $clusterNumber, $clusterSize->id], 200);
        } else {
            return response()->json('this cluster not relevent to your factory', 401);
        }
    }
    ///////// public section
    public function showAllClSizeByLinkId($id)
    {
        $data = Clusters::where('sharingLinks', $id)->first();
        return response()->json([Clustersize::where('clustersizes.clusterId', $data->id)->get()]);
    }
    public function modifyExistNumberToClusterSize()
    {
        $clusterSize = Clustersize::all();
        foreach ($clusterSize as $clS) {
            if ($clS->count != 0) {
                $exist_number = range(1, $clS->count);
                $clS->exist_number = implode(',', $exist_number);
            }

            $clS->save();
        }
        return response()->json(true, 200);
    }
}
