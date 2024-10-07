<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Cube;
use App\Models\CubeImage;
use App\Models\CubePremission;
use App\Models\CuWarehouse;
use App\Models\User;
use App\Models\Clusters;

use DB;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\ImageManagerStatic as Image;
use Storage;
use Verta;
use Illuminate\Support\Benchmark;

class CubeController extends Controller
{
    private function newEvent($type, $details, $itemtype, $sharingLinks)
    {
        $event = new EventsController();
        $event->newEvent($type, $details, $itemtype, $sharingLinks);
        return;
    }
    public function checkUserPremisson($type)
    {
        $userID = auth('api')->user()->id;
        $cubeCtrl = new CubePremissionController();
        return $cubeCtrl->getUserCubePremissonByIdAndTypeNOnJson($userID, $type);
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function addCube(Request $request)
    {
        if ($this->checkUserPremisson('add')) {
            $factoryId = auth('api')->user()->factoryId;
            $Exist = Cube::where('factoryId', $factoryId)
                ->where('cubeNumber', $request->cubeNumber)
                ->count();
            if ($Exist === 0) {
                $data = new Cube();

                $data->factoryId = $factoryId;
                $data->nameCube = $request->nameCube;
                $data->cubeNumber = $request->cubeNumber;
                $data->minerNumber = $request->minerNumber;
                $data->minerDegree = $request->minerDegree;
                $data->truckNumber = $request->truckNumber;
                $data->minerFactorId = $request->minerFactorId;
                $data->cubeColorDegree = $request->cubeColorDegree;
                $data->bought_price = $request->boughtPrice;
                $data->weight = $request->weight;
                $data->length = $request->length;
                $data->width = $request->width;
                $data->height = $request->height;
                $warehouseID = CuWarehouse::where('factoryId', $factoryId)
                    ->where('name', $request->warehouse)
                    ->first();
                if ($warehouseID != null) {
                    $data->cuWarehouseId = $warehouseID->id;
                } else {
                    $warehouseID = CuWarehouse::where('factoryId', $factoryId)
                        ->where('name', 'انبار اصلی')
                        ->first();
                    if ($warehouseID != null) {
                        $data->cuWarehouseId = $warehouseID->id;
                    } else {
                        $warehouse = new CuWarehouse();
                        $warehouse->name = 'انبار اصلی';
                        $warehouse->factoryId = $factoryId;
                        $warehouse->save();
                        $data->cuWarehouseId = $warehouse->id;
                    }
                }
                // $data->warehouse = $request->warehouse;
                $data->timeinsert = $this->getMiladyDate($request->timeinsert);
                $data->nameMiner = $request->nameMiner;
                $data->cubeDegree = $request->cubeDegree;
                $data->sharingLiks = $this->generateRandomString();
                if ($data->nameCube == null) {
                    $data->nameCube = 'نامشخص';
                }
                // if ($data->warehouse == null) {
                //     $data->warehouse = 'انبار اصلی';
                // }
                if ($data->nameMiner == null) {
                    $data->nameMiner = 'نامشخص';
                }

                $count = Cube::where('sharingLiks', $data->sharingLiks)->count();
                while ($count != 0) {
                    $data->sharingLiks = $this->generateRandomString();
                    $count = Cube::where('sharingLiks', $data->sharingLiks)->count();
                }
                $data->save();
                $this->newEvent('addCu', 'کوپ ' . $data->cubeNumber . ' توسط ' . auth('api')->user()->name . ' اضافه گردید.', 'cube', $data->sharingLiks);

                return response()->json([$data->id]);
            } else {
                return response()->json(['duplicate cube number'], 400);
            }
        } else {
            return response()->json('Access Denied', 401);
        }
    }
    public function getCountOfCube()
    {
        $factoryId = auth('api')->user()->factoryId;
        $count = Cube::where('factoryId', $factoryId)
            ->where('isActive', 'yes')
            ->count();
        return response()->json($count);
    }
    public function getLastCube()
    {
        $factoryId = auth('api')->user()->factoryId;
        $cube = Cube::where('factoryId', $factoryId)
            ->orderBy('created_at', 'desc')
            ->first();
        return response()->json($cube);
    }
    public function getLastCubes($count)
    {
        $factoryId = auth('api')->user()->factoryId;
        $cube = Cube::where('factoryId', $factoryId)
            ->orderBy('id', 'desc')
            ->take($count)
            ->get();
        return response()->json($cube);
    }
    public function getGroupedCube()
    {
        $factoryId = auth('api')->user()->factoryId;
        /*
         $count = Cube::where('factoryId',$factoryId)->where('isActive', 'yes')->count();*/
        $cubes = DB::table('cubes')
            ->where('factoryId', $factoryId)
            ->where('isActive', 'yes')
            ->select('nameCube', DB::raw('count(*) as total'))
            ->groupBy('nameCube')
            ->orderBy('total', 'desc')
            ->take(5)
            ->get();
        return response()->json($cubes);
    }
    public function getGroupedMine()
    {
        $factoryId = auth('api')->user()->factoryId;
        $cubes = DB::table('cubes')
            ->where('factoryId', $factoryId)
            ->select('nameMiner')
            ->groupBy('nameMiner')
            ->orderBy('nameMiner', 'desc')
            ->get();
        return response()->json($cubes);
    }
    public function getGroupedWarehouse()
    {
        $cuWarehouse = new CuWarehouseController();

        $data = $cuWarehouse->getFactoryCuWarehouse();
        return response()->json($data);
    }
    public function getCubeGroupedReportFilterOption()
    {
        $factoryId = auth('api')->user()->factoryId;
        $data1 = DB::table('cubes')
            ->where('factoryId', $factoryId)
            ->select('nameCube')
            ->groupBy('nameCube')
            ->orderBy('nameCube', 'desc')
            ->get();

        $data2 = DB::table('cubes')
            ->where('factoryId', $factoryId)
            ->select('nameMiner')
            ->groupBy('nameMiner')
            ->orderBy('nameMiner', 'desc')
            ->get();
        // $data3 = DB::table('cubes')
        //     ->where('factoryId', $factoryId)
        //     ->select('warehouse')
        //     ->groupBy('warehouse')
        //     ->orderBy('warehouse', 'desc')
        //     ->get();
            $cuWarehouse = new CuWarehouseController();

        $data3 = $cuWarehouse->getFactoryCuWarehouse();
        $data = [$data1, $data2, $data3];
        return $data;
    }
    public function getGroupedCubeName()
    {
        $factoryId = auth('api')->user()->factoryId;
        $cubes = DB::table('cubes')
            ->where('factoryId', $factoryId)
            ->select('nameCube')
            ->groupBy('nameCube')
            ->orderBy('nameCube', 'desc')
            ->get();
        return response()->json($cubes);
    }
    public function getLastMonthCube()
    {
        $factoryId = auth('api')->user()->factoryId;
        $v = Carbon::now();
        $lastMonth = $v->subMonth();
        /*
         $count = Cube::where('factoryId',$factoryId)->where('isActive', 'yes')->count();*/
        $cubes = DB::table('cubes')
            ->where('factoryId', $factoryId)
            ->where('timeinsert', '>=', $lastMonth)
            ->select('nameCube', DB::raw('count(*) as total'))
            ->select('nameCube', DB::raw('count(*) as total'), DB::raw('sum(weight) as weight'))
            ->groupBy('nameCube')
            ->orderBy('total', 'desc')
            ->get();
        return $cubes;
    }
    public function getLastMonthUsedCube()
    {
        $factoryId = auth('api')->user()->factoryId;
        $v = Carbon::now();
        $lastMonth = $v->subMonth();
        /*
         $count = Cube::where('factoryId',$factoryId)->where('isActive', 'yes')->count();*/
        $cubes = DB::table('cubes')
            ->where('factoryId', $factoryId)
            ->where('isActive', 'No')
            ->where('cuttingtime', '>=', $lastMonth)
            ->select('nameCube', DB::raw('count(*) as total'))
            ->select('nameCube', DB::raw('count(*) as total'), DB::raw('sum(weight) as weight'))
            ->groupBy('nameCube')
            ->orderBy('total', 'desc')
            ->take(5)
            ->get();
        return $cubes;
    }
    public function getAllCubeAnalyticsData()
    {
        $getAllGroupedCube = $this->getAllGroupedCube();
        $getLastMonthCube = $this->getLastMonthCube();
        $getLastMonthUsedCube = $this->getLastMonthUsedCube();
        $data = [$getAllGroupedCube, $getLastMonthCube, $getLastMonthUsedCube];
        return $data;
    }
    public function getAllGroupedCube()
    {
        $factoryId = auth('api')->user()->factoryId;
        /* weight
         $count = Cube::where('factoryId',$factoryId)->where('isActive', 'yes')->count();*/
        $cubes = DB::table('cubes')
            ->where('factoryId', $factoryId)
            ->where('isActive', 'yes')
            ->select('nameCube', DB::raw('count(*) as total'), DB::raw('sum(weight) as weight'))
            ->groupBy('nameCube')
            ->orderBy('total', 'desc')
            ->take(10)
            ->get();
        return $cubes;
    }
    public function getNewCubeNumber()
    {
        $factoryId = auth('api')->user()->factoryId;
        $cubenumber = 1;
        try {
            $data = Cube::where('factoryId', $factoryId)
                ->orderBy('cubeNumber', 'desc')
                ->first();
            $cubenumber = $data->cubeNumber + 1;
        } catch (\Exception $e) {
            //$data= Cube::where('factoryId',$factoryId)->orderBy('cubeNumber', 'desc')->first();
        }
        return $cubenumber;
    }

    public function showAllFactoryCube()
    {
        if ($this->checkUserPremisson('view') == false) {
            return response()->json(false, 401);
        }
        $factoryId = auth('api')->user()->factoryId;
        return Cube::where('factoryId', $factoryId)
            ->where('isActive', 'yes')
            ->orderBy('cubeNumber', 'desc')
            ->get();
    }
    public function showAllDeActiveFactoryCube()
    {
        if ($this->checkUserPremisson('view') == false) {
            return response()->json(false, 401);
        }
        $factoryId = auth('api')->user()->factoryId;
        return Cube::where('factoryId', $factoryId)
            ->where('isActive', 'No')
            ->orderBy('cubeNumber', 'desc')
            ->get();
    }
    public function showFactoryCube()
    {
        $factoryId = auth('api')->user()->factoryId;
        return Cube::where('factoryId', $factoryId)
            ->where('isActive', 'yes')
            ->orderBy('cubeNumber', 'desc')
            ->get();
    }
    public function showUsedCube()
    {
        $factoryId = auth('api')->user()->factoryId;
        return Cube::where('factoryId', $factoryId)
            ->where('isActive', 'no')
            ->orderBy('cubeNumber', 'desc')
            ->get();
    }
    public function showCubeById($id)
    {
        $factoryId = auth('api')->user()->factoryId;
        $cube = Cube::where('factoryId', $factoryId)
            ->where('id', $id)
            ->with(['cuWarehouseId','cuSaw','cuCuttingTime','splitted_Cube'])
            ->first();
        $img = CubeImage::where('cubeId', $id)->get();
        $data = [$cube, $img];
        $this->newEvent('viewCu', 'کوپ ' . $cube->cubeNumber . ' توسط ' . auth('api')->user()->name . ' مشاهده گردید.', 'cube', $cube->sharingLiks);

        return $data;
    }
    public function showCubeByLink($str)
    {
        $data = Cube::where('cubes.sharingLiks', $str)
            ->leftjoin('factories', 'factories.id', '=', 'cubes.factoryId')
            ->leftjoin('socialnets', 'socialnets.factoryId', '=', 'cubes.factoryId')
            ->select('cubes.id', 'cubes.factoryId', 'cubes.nameCube', 'cubes.cubeNumber', 'cubes.cubeDegree', 'cubes.weight', 'cubes.length', 'cubes.imageThumb', 'cubes.width', 'cubes.height', 'cubes.sharingLiks', 'cubes.hasImage', 'factories.nameFac', 'factories.telephoneFac', 'factories.addressFac', 'factories.logoFac', 'factories.state', 'factories.description', 'factories.bannerimg', 'factories.website', 'socialnets.instagram', 'socialnets.telegram', 'socialnets.facebook', 'socialnets.youtube', 'socialnets.twitter')
            ->get();
        return response()->json($data, 200);
    }
    public function deleteCubeById($id)
    {
        $factoryId = auth('api')->user()->factoryId;
        $data = Cube::where('factoryId', $factoryId)
            ->where('id', $id)
            ->first();
        $cluster = Clusters::where('factoryId', $factoryId)->where('clusterNumber',$data->cubeNumber)->first();
        if($cluster != null){
            return response()->json("this cube has relationship with cluster", 201);
        }
        $path = public_path() . '/storage/img/cube/' . $data->imageThumb;
        if (file_exists($path) && $data->imageThumb != 'noimage.jpg') {
            unlink($path);
        }
        // $a = Storage::delete('public/img/cube/' . $data->imageThumb);
        $this->newEvent('delCu', 'کوپ ' . $data->cubeNumber . ' توسط ' . auth('api')->user()->name . ' حذف گردید.', 'cube', $data->sharingLiks);

        $data->delete();
        $imgCube = CubeImage::where('cubeId', $id)->get();
        foreach ($imgCube as $imgsrc) {
            $path = public_path() . '/storage/img/cube/' . $imgsrc->imageSrc;
            if (file_exists($path)) {
                unlink($path);
            }
        }
        $CubeImage = CubeImage::where('cubeId', $id);
        return $CubeImage->delete();
    }
    public function cuttingCube(Request $request)
    {
        $factoryId = auth('api')->user()->factoryId;
        $cubeid = $request->id;
        $cuttingtime = $this->getMiladyDate($request->cuttingtime);
        $cubeid = (int) $request->id;
        $cube = Cube::findOrFail($cubeid);
        if ($cube->factoryId == $factoryId) {
            $cube->isActive = 'No';
            $cube->cuttingtime = $cuttingtime;
            $this->newEvent('cutCu', 'کوپ ' . $cube->cubeNumber . ' توسط ' . auth('api')->user()->name . ' مصرف گردید.', 'cube', $cube->sharingLiks);

            return response()->json([$cube->update()]);
        } else {
            return response()->json(false, 401);
        }
        return $request;
    }
    public function removeCuttingCube($cubeid)
    {
        $factoryId = auth('api')->user()->factoryId;
        $cuttingtime = null;
        $cube = Cube::findOrFail($cubeid);
        if ($cube->factoryId == $factoryId) {
            $cube->isActive = 'yes';
            $cube->cuttingtime = $cuttingtime;
            $this->newEvent('delCutCu', 'مصرف کوپ ' . $cube->cubeNumber . ' توسط ' . auth('api')->user()->name . ' لغو گردید.', 'cube', $cube->sharingLiks);

            return response()->json([$cube->update()]);
        } else {
            return response()->json(false, 401);
        }
    }
    public function updateCube(Request $request)
    {
        if ($this->checkUserPremisson('update') == false) {
            return response()->json(false, 401);
        }
        $checkAuth = auth('api')->user()->factoryId;
        $cubeid = $request->id;
        $cubeid = (int) $request->id;
        $data = Cube::findOrFail($cubeid);
        $data->nameCube = $request->nameCube;
        $data->cubeNumber = $request->cubeNumber;
        $data->minerNumber = $request->minerNumber;
        $data->minerDegree = $request->minerDegree;
        $data->truckNumber = $request->truckNumber;
        $data->bought_price = $request->boughtPrice;
        $wareHouse = CuWarehouse::where('factoryid', $checkAuth)
            ->where('name', $request->warehouse)
            ->first();
        $data->cuWarehouseId = $wareHouse->id;

        // $data->warehouse = $request->warehouse;
        $data->minerFactorId = $request->minerFactorId;
        $data->cubeColorDegree = $request->cubeColorDegree;
        $data->weight = $request->weight;
        $data->length = $request->length;
        $data->width = $request->width;
        $data->height = $request->height;

        $data->timeinsert = $this->getMiladyDate($request->timeinsert);
        $data->nameMiner = $request->nameMiner;
        $data->cubeDegree = $request->cubeDegree;
        $this->newEvent('editCu', 'کوپ ' . $data->cubeNumber . ' توسط ' . auth('api')->user()->name . ' ویرایش گردید.', 'cube', $data->sharingLiks);

        return response()->json([$data->update()]);
    }
    public function cubeAjaxSearch(Request $request)
    {
        if ($this->checkUserPremisson('view') == false) {
            return response()->json(false, 401);
        }
        $nameCube = $request->nameCube;
        $cubeNumber = $request->nameCube;
        $factoryId = auth('api')->user()->factoryId;
        $isActive = 'yes';
        $data = Cube::where('factoryId', $factoryId)
            ->orderBy('cubeNumber', 'desc')
            ->when($nameCube, function ($q) use ($nameCube) {
                return $q->where('nameCube', 'like', '%' . $nameCube . '%');
            })
            ->get();
        $data2 = Cube::where('factoryId', $factoryId)
            ->orderBy('cubeNumber', 'desc')
            ->when($cubeNumber, function ($q) use ($cubeNumber) {
                return $q->where('cubeNumber', 'like', '%' . $cubeNumber . '%');
            })
            ->get();
        $data = $data->merge($data2);
        return $data;
    }
    public function searchCube(Request $request)
    {
        $nameCube = $request->nameCube;
        $cubeNumber = $request->cubeNumber;
        $lastdate = $this->getMiladyDate($request->dateLast);
        $firstDate = $this->getMiladyDate($request->dateFirst);
        $factoryId = auth('api')->user()->factoryId;
        $isActive = 'yes';
        $data = Cube::where('factoryId', $factoryId)
            ->orderBy('cubeNumber', 'desc')
            ->when($nameCube, function ($q) use ($nameCube) {
                return $q->where('nameCube', 'like', '%' . $nameCube . '%');
            })
            ->when($cubeNumber, function ($q) use ($cubeNumber) {
                return $q->where('cubeNumber', $cubeNumber);
            })
            ->when($firstDate, function ($q) use ($firstDate, $lastdate) {
                return $q->whereBetween('timeinsert', [$firstDate, $lastdate]);
            })

            ->get();
        return response()->json([$data, $lastdate, $firstDate]);
    }
    public function cubeReports(Request $request)
    {

        if ($this->checkUserPremisson('view') == false) {
            return response()->json(false, 401);
        }
        $factoryId = auth('api')->user()->factoryId;

        $nameCube = $request->nameCube;
        $nameMiner = $request->nameMiner;
        $warehouse = null;
        $warehouseID =  CuWarehouse::where('factoryid', $factoryId)
            ->where('name', $request->warehouse)
            ->first();
            if($warehouseID != null) {
                $warehouse = $warehouseID->id;
            }
        $lastdate = $this->getMiladyDate($request->dateLast);
        $firstDate = $this->getMiladyDate($request->dateFirst);
        $lastdateDeActive = $this->getMiladyDate($request->dateLastDeActive);
        $firstDateDeActive = $this->getMiladyDate($request->dateFirstDeActive);
        $isActive = $request->isActive;
        $weightMin = $request->weightMin;
        $weightMax = $request->weightMax;
        $lengthMin = $request->lengthMin;
        $lengthMax = $request->lengthMax;
        $widthMin = $request->widthMin;
        $widthMax = $request->widthMax;
        $heightMin = $request->heightMin;
        $heightMax = $request->heightMax;

        $data = Cube::where('factoryId', $factoryId)
            ->when($nameCube, function ($q) use ($nameCube) {
                return $q->where('nameCube',$nameCube );
            })
            ->when($isActive, function ($q) use ($isActive) {
                return $q->where('isActive', '=', $isActive);
            })
            ->when($nameMiner, function ($q) use ($nameMiner) {
                return $q->where('nameMiner', $nameMiner);
            })
            ->when($warehouse, function ($q) use ($warehouse) {
                return $q->where('cuWarehouseId', $warehouse);
            })
            ->when($firstDate, function ($q) use ($firstDate, $lastdate) {
                return $q->whereBetween('timeinsert', [$firstDate, $lastdate]);
            })
            ->when($firstDateDeActive, function ($q) use ($firstDateDeActive, $lastdateDeActive) {
                return $q->whereBetween('cuttingtime', [$firstDateDeActive, $lastdateDeActive]);
            })
            ->when($weightMin, function ($q) use ($weightMin, $weightMax) {
                return $q->whereBetween('weight', [$weightMin, $weightMax]);
            })
            ->when($lengthMin, function ($q) use ($lengthMin, $lengthMax) {
                return $q->whereBetween('length', [$lengthMin, $lengthMax]);
            })
            ->when($widthMin, function ($q) use ($widthMin, $widthMax) {
                return $q->whereBetween('width', [$widthMax, $widthMax]);
            })
            ->when($heightMin, function ($q) use ($heightMin, $heightMax) {
                return $q->whereBetween('height', [$heightMin, $heightMax]);
            })
            ->with(['cuWarehouseId','cuSaw','cuCuttingTime'])
            ->get();
        return response()->json([$data]);
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
    public function addImageCube(Request $request)
    {
        $factoryId = auth('api')->user()->factoryId;

        if ($this->checkUserPremisson('update') == false) {
            return response()->json(false, 401);
        }
        $image = $request->file('file');
        $filename = 'Cube_' . time() . $request->file->getClientOriginalName();

        $path = public_path() . "/storage/img/cube/$factoryId/$request->cubeId";
        if (!File::isDirectory($path)) {
            File::makeDirectory($path, 0755, true, true);
        }

        $image_resize = Image::make($image->getRealPath());
        $image_resize->resize(1200, null, function ($constraint) {
            $constraint->aspectRatio();
        });
        $image_resize->save(public_path("/storage/img/cube/$factoryId/$request->cubeId/$filename"));
        $data = new CubeImage();
        $data->cubeId = $request->cubeId;
        $data->imageSrc = "$factoryId/$request->cubeId/$filename";
        $data->save();
        $cube = Cube::findOrFail($request->cubeId);
        if ($cube->hasImage == 'no') {
            $filename = 'CubeThu_' . time() . $request->file->getClientOriginalName();
            $image_resize = Image::make($image->getRealPath());
            $image_resize->resize(200, null, function ($constraint) {
                $constraint->aspectRatio();
            });
            $image_resize->save(public_path("/storage/img/cube/$factoryId/$request->cubeId/$filename"));

            $cube->hasImage = 'yes';
            $cube->imageThumb = "$factoryId/$request->cubeId/$filename";
            $cube->update();
        }

        return response()->json([$filename], 200);
    }
    public function getLastCubeTimeRetrive()
    {
        $factoryId = auth('api')->user()->factoryId;
        $data = Cube::where('factoryId', $factoryId)
            ->orderBy('updated_at', 'desc')
            ->first();
        $date = strtotime($data->updated_at);
        return response()->json($date * 1000);
    }
    // public function fixAllCubeDate()
    // {
    //     $cube = Cube::where('factoryId', 27)->get();
    //     foreach ($cube as $cubeFix) {
    //         $cubeFix->timeinsert = $this->getMiladyDate($cubeFix->timeinsert);
    //         $cubeFix->cuttingtime = $this->getMiladyDate($cubeFix->cuttingtime);
    //         $cubeFix->update();
    //     }
    //     return true;
    // }
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
    public function getTopLastCube()
    {
        return Cube::leftjoin('factories', 'factories.id', '=', 'cubes.factoryId')
            ->leftjoin('cube_esells', 'cube_esells.cube_id', '=', 'cubes.id')
            ->where('factories.cube_esells', '=', 'yes')
            ->where('factories.cube_esells', '=', 'yes')
            ->where('cubes.show_in_esells', '=', 'yes')
            ->where('cubes.hasImage', '=', 'yes')
            ->where('cube_esells.show_price', '=', 'yes')
            ->where('cube_esells.price', '!=', 0)
            ->select('cubes.factoryId', 'cubes.sharingLiks', 'cubes.imageThumb', 'cube_esells.price', 'cube_esells.alias_title', 'cubes.nameCube', 'cubes.cubeNumber', 'cubes.nameCube')
            ->orderBy('cubes.created_at', 'desc')
            ->take(18)
            ->get();
    }
    public function modifyCuWarehouse()
    {
        $cube = Cube::all();
        foreach ($cube as $cubeFix) {
            $warehouseID = CuWarehouse::where('factoryId', $cubeFix->factoryId)
                ->where('name', $cubeFix->warehouse)
                ->first();
            if ($warehouseID != null) {
                $cubeFix->cuWarehouseId = $warehouseID->id;
            } else {
                $warehouse = new CuWarehouse();
                $warehouse->name = $cubeFix->warehouse;
                $warehouse->factoryId = $cubeFix->factoryId;
                $warehouse->save();
                $cubeFix->cuWarehouseId = $warehouse->id;
            }
            $cubeFix->update();
        }
        return true;
    }
}
