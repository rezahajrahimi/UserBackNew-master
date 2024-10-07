<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Storage;

use App\Models\ClusterImage;
use App\Models\Clusters;

use Illuminate\Http\Request;

class ClusterImageController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
     private function newEvent($type,$details,$itemtype,$sharingLinks) {
        $event = new EventsController();
        $event->newEvent($type,$details,$itemtype,$sharingLinks);
        return;
    }
    public function showClusterImageByClusterId($id)
    {
        $factoryId = auth('api')->user()->factoryId;
        // $data = ClusterImage::where('clusters_id',$id)->get();
        $imgCluster = Clusters::findOrFail($id);
        if ($imgCluster->factoryId == $factoryId) {
            return ClusterImage::where('clusters_id', $id)->get();
        } else {
            return false;
        }
    }
    public function showClusterImageByClusterIdEsells($id)
    {
            return ClusterImage::where('clusters_id', $id)->get();

    }
    public function showClImgByLinkId($id)
    {
        return ClusterImage::where('clusters_id', $id)->get();
    }
    public function deleteCLusterImageById($id)
    {
        $factoryId = auth('api')->user()->factoryId;
        $a = 'a';
        $imgCluster = ClusterImage::find($id);
        $clusters_id = $imgCluster->clusters_id;
        $path = public_path() . '/storage/img/cluster/' . $imgCluster->imageSrc;
        if (file_exists($path)) {
            unlink($path);
        }
        $imgCluster->delete();
        $data = ClusterImage::where('clusters_id', $clusters_id)->count();

        if ($data == 0) {
            $cluster = Clusters::findOrFail($clusters_id);
            $path = public_path() . '/storage/img/cluster/' . $cluster->imageThumb;
            if (file_exists($path)) {
                unlink($path);
            }
            $cluster->hasImage = 'no';
            $cluster->imageThumb = 'noimage.jpg';
            $this->newEvent("delImg","تصاویر دسته ".$cluster->clusterNumber." توسط " .auth('api')->user()->name." حذف گردید.","cluster",$cluster->sharingLinks);

            return $cluster->update();
        } elseif ($data >= 1) {
            $cluster = Clusters::findOrFail($clusters_id);
            $data = ClusterImage::where('clusters_id', $clusters_id)->first();
            $cluster->imageThumb = $data->imageSrc;
            $cluster->hasImage = 'yes';
            return $cluster->update();
        }
    }
    //// public section
    public function getClusterImageByClusterShareLInk($id)
    {
        $data = Clusters::where('sharingLinks', $id)->first();
        return ClusterImage::where('clusters_id', $data->id)->get();
    }
}
