<?php

namespace App\Http\Controllers;

use App\Models\Comments;
use App\Models\Clusters;
use App\Models\ClusterEsell;

use Illuminate\Http\Request;
////// موقع تاید نظر، درصورتیکه نظر تایید بشه باید فیلد ریت کلاستر را بروزرسانی کنی
class CommentsController extends Controller
{
    // Factory Section
    public function getFactoryCommentsList()
    {
        $factoryId = auth('api')->user()->factoryId;
        $data = Comments::where('comments.factoryId', $factoryId)
            ->leftjoin('users', 'users.id', '=', 'comments.userId')
            ->leftjoin('clusters', 'clusters.sharingLinks', '=', 'comments.itemId')
            ->select('comments.rateNumber', 'comments.id', 'comments.hasChild', 'comments.itemType', 'comments.itemId', 'comments.parentId', 'comments.comment', 'comments.created_at', 'comments.status', 'clusters.imageThumb', 'clusters.id as ClusterId', 'clusters.clusterNameStone', 'clusters.clusterNumber', 'users.name', 'users.profilepic')
            ->get();
        if ($data) {
            return $data;
        } else {
            abort(401, 'No Data Exsists');
        }
    }
    public function confirmComment($id)
    {
        $data = Comments::find($id);
        $factoryId = auth('api')->user()->factoryId;
        if ($data->factoryId == $factoryId) {
            $data->status = 1;
            $new = $data->save();
            $this->rateCounting($data->itemId);
            return $this->getFactoryCommentsList();
        } else {
            abort(401, 'No Data Exsists');
        }
    }
    public function unConfirmComment($id)
    {
        $data = Comments::find($id);
        $factoryId = auth('api')->user()->factoryId;

        if ($data->factoryId == $factoryId) {
            $data->status = 0;
            $new = $data->save();
            $this->rateCounting($data->itemId);
            return $this->getFactoryCommentsList();
        } else {
            abort(401, 'No Data Exsists');
        }
    }
    public function deleteCommentByFac($id)
    {
        $data = Comments::find($id);
        $factoryId = auth('api')->user()->factoryId;
        if ($data->factoryId == $factoryId) {
            if($data->hasChild == 1) {
                $child = Comments::where('parentId', $id)->first();
                $child->delete();
            }
            $new = $data->delete();
            $this->rateCounting($data->itemId);
            return $this->getFactoryCommentsList();
        } else {
            abort(401, 'No Data Exsists');
        }
    }
    public function rateCounting($itemId)
    {
        $newRate = 0;
        $clusterId = Clusters::where('sharingLinks', $itemId)->first()->id;
        $sumComments = Comments::where('itemId', $itemId)
            ->where('status', 1)
            ->where('parentId', 0)
            ->sum('rateNumber');
        $countComments = Comments::where('itemId', $itemId)
            ->where('status', 1)
            ->where('parentId', 0)
            ->get()
            ->count('rateNumber');
        if ($sumComments !== 0) {
            $newRate = $sumComments / $countComments;
        }
        $data = ClusterEsell::where('cluster_id', $clusterId)->first();
        $data->rate = $newRate;
        $data->save();
    }

    public function replayToComment(Request $request)
    {
        if ($this->confirmComment($request->parentId)) {
            $com = Comments::find($request->parentId);
            if ($com->hasChild == 1) {
                $data = Comments::where('parentId', $request->parentId)->first();
                $data->comment = $request->comment;
                $data->update();
            } else {
                $com->hasChild = 1;
                $com->save();
                $data = new Comments();
                $data->userId = auth('api')->user()->id;
                $data->factoryId = auth('api')->user()->factoryId;

                $data->parentId = $request->parentId;
                $data->itemId = $request->itemId;
                $data->rateNumber = 0;
                if ($request->itemType) {
                    $data->itemType = $request->itemType;
                }
                $data->comment = $request->comment;
                $data->status = 1;
                $data->save();
            }

            return $this->getFactoryCommentsList();
        } else {
            abort(401, 'go fuck your self');
        }
    }

    /// Public Section
    public function setNewCommentToItem(Request $request)
    {
        $data = new Comments();
        $data->userId = auth('api')->user()->id;
        if ($request->parentId) {
            $data->parentId = $request->parentId;
        }
        $data->itemId = $request->itemId;
        if ($request->rateNumber) {
            $data->rateNumber = $request->rateNumber;
        } else {
            $data->rateNumber = 0;
        }
        if ($request->itemType) {
            $data->itemType = $request->itemType;
        }
        $data->comment = $request->comment;
        $ca = Clusters::where('sharingLinks', $request->itemId)->first();
        $data->factoryId = $ca->factoryId;
        $data->save();
        return response(
            Comments::where('itemId', $request->itemId)
                ->where('status', 1)
                ->leftjoin('users', 'users.id', '=', 'comments.userId')
                ->select('comments.rateNumber', 'comments.hasChild', 'comments.id', 'comments.parentId', 'comments.comment', 'comments.created_at', 'users.name', 'users.profilepic')
                ->get(),
            200,
        );
    }
    public function getCommentbyItemId($id)
    {
        return response(
            Comments::where('itemId', $id)
                ->where('status', 1)
                ->leftjoin('users', 'users.id', '=', 'comments.userId')
                ->select('comments.rateNumber', 'comments.hasChild', 'comments.id', 'comments.parentId', 'comments.comment', 'comments.created_at', 'users.name', 'users.profilepic')
                ->get(),
            200,
        );
    }
    public function getUserCommentsList()
    {
        $userId = auth('api')->user()->id;
        $data = Comments::where('userId', $userId)
            ->leftjoin('factories', 'factories.id', '=', 'comments.factoryId')
            ->leftjoin('clusters', 'clusters.sharingLinks', '=', 'comments.itemId')
            ->select('comments.rateNumber', 'comments.hasChild', 'comments.id', 'comments.itemType', 'comments.parentId', 'comments.comment', 'comments.created_at', 'comments.status', 'factories.nameFac', 'clusters.imageThumb', 'clusters.sharingLinks', 'clusters.clusterNameStone')
            ->get();
        if ($data) {
            return $data;
        } else {
            abort(401, 'No Data Exsists');
        }
    }
}
