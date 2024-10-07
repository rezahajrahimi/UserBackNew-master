<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use App\Models\CubeImage;
use App\Models\Cube;

class CubeImageController extends Controller
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
    public function showCubeImageByCubeId($id)
    {
        return CubeImage::where('cubeId',$id)->get();
    }
    public function deleteCubeImageById($id)
    {
        $factoryId = auth('api')->user()->factoryId;
        $imgCube = CubeImage::where('id', $id)->first();
        $cubeId= $imgCube->cubeId;
          $path = public_path()."/storage/img/cube/".$imgCube->imageSrc;
          if(file_exists($path)) {
            unlink($path);
            }
          $imgCube->delete();
        $data = CubeImage::where('cubeId',$cubeId)->count();

        if ($data == 0 ) {
            $cube = Cube::findOrFail($cubeId);
            $path = public_path()."/storage/img/cube/".$cube->imageThumb;
            if(file_exists($path)) {
                unlink($path);
                }
            $cube->hasImage = "no";
            $cube->imageThumb = "noimage.jpg";
            $this->newEvent("delCuImg","تصاویر کوپ ".$cube->cubeNumber." توسط " .auth('api')->user()->name." حذف گردید.","cube",$cube->sharingLiks);

            return $cube->update();
        } else if($data >= 1 ) {
            $cube = Cube::findOrFail($cubeId);
            $data = CubeImage::where('cubeId',$cubeId)->first();
            $cube->imageThumb = $data->imageSrc;
            $cube->hasImage = "yes";
            return $cube->update();
        }


    }
}
