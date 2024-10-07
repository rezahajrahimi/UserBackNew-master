<?php

namespace App\Http\Controllers;

use App\Models\CubeEsell;
use Illuminate\Http\Request;
use App\Models\Factory;
use App\Models\Cube;
use App\Models\CubeImage;
use Illuminate\Support\Facades\Auth;



class CubeEsellController extends Controller
{
    public function showCubeEsellsList($page)
    {
        $factoryId = auth('api')->user()->factoryId;
        $checkPremission = Factory::findOrFail($factoryId);
        if($checkPremission->cube_esells == 'yes') {
            return Cube::where('factoryId', $factoryId)->where('isActive','yes')
            ->leftjoin('cube_esells', 'cube_esells.cube_id', '=', 'cubes.id')->orderBy('cubes.created_at', 'asc')
            ->select('cubes.id','cubes.imageThumb','cubes.cubeNumber','cubes.nameCube','cubes.weight','cubes.length','cubes.width','cubes.height'
            ,'cubes.show_in_esells','cube_esells.price','cubes.warehouse','cubes.timeinsert'
                )
            ->paginate(200,['*'],'page',$page);
        } else return response()->json(false,401);
    }
    public function showCubeEsellById($id)
    {
        $data=CubeEsell::firstOrCreate(['cube_id' => $id]);
        if($this->checkUser($data->cube_id)){
            return $data;
        }else{
            return response()->json(false,401);
        }
    }
    public function editCubeEsellById(Request $request)
    {
        if($this->checkUser($request->cube_id)){
        $data = CubeEsell::findOrFail($request->id);
        $data->show_price = $request->show_price;
        $data->price = $request->price;
        $data->alias_title = $request->alias_title;
        $data->tiny_text = $request->tiny_text;
        $data->description = $request->description;
        $cube = Cube::findorfail($request->cube_id);
        $cube->show_in_esells = $request->status;
        $cube->update();
            return response()->json([$data->update()]);
        }else{
            return response()->json(false,401);
        }

    }
    public function changeCubeEsellsStatus(Request $request)
    {
        $cubeCtrl = new CubePremissionController();
        if($cubeCtrl->getUserCubePremissonByIdAndTypeNOnJson( auth('api')->user()->id,"update")==false)
        {
            return response()->json(false, 401);
        }
        if($this->checkUser($request->cube_id)){
        $cube = Cube::find($request->cube_id);
        $cube->show_in_esells = $request->status;
        return response()->json([$cube->update()]);
        }else{
            return response()->json(false,401);
        }

    }
    public function checkUser($cube_id)
    {
        $userfactoryId = auth('api')->user()->factoryId;
        $data = Cube::findorfail($cube_id);
        if ($data->factoryId == $userfactoryId) {
            return true;
        } else{
            return false;
        }
    }
    public function getExhibitionCubesListByFactoryId($factoryId,$page)
    {
        $followersController = new FollowersController();
        // $followersController->getFollowCheckingByFactoryId($factoryId);
        $esellsSettingsController = new EsellsSettingsController();
        $ispublic = $esellsSettingsController->isPublic($factoryId);

        if ($followersController->getFollowCheckingByFactoryId($factoryId) == true ||$ispublic==true) {
            return Cube::where('factoryId', $factoryId)
                ->where('isActive', '=', "yes")
                ->where('show_in_esells', '=', 'yes')
                ->orderBy('created_at', 'desc')
                ->paginate(18, ['*'], 'page', $page);

        } else {
            return response()->json(false, 401);
        }
    }
    public function getCuWithImgeFacNameById($id)
    {
        $facController = new FactoryController();

        $cube = Cube::where('id', $id)->first();
        $img = CubeImage::where('cubeId',$id)->get();
        $facInfo = $facController->getSellerInfoById($cube->factoryId);

        $data = array($cube,$img,$facInfo);
        return $data;
    }
    public function getCuWithImgeFacNameByshareId($sharingLiks)
    {
        $facController = new FactoryController();

        $cube = Cube::where('sharingLiks', $sharingLiks)->first();
        $img = CubeImage::where('cubeId',$cube->id)->get();
        $facInfo = $facController->getSellerInfoById($cube->factoryId);

        $data = array($cube,$img,$facInfo);
        return $data;
    }
}
