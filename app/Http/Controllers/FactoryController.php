<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Factory;
use App\Models\User;
use App\Models\EsellsSettings;
use App\Models\FactoryOptions;

use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\ImageManagerStatic as Image;

class FactoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    private function newEvent($type, $details, $itemtype, $sharingLinks)
    {
        $event = new EventsController();
        $event->newEvent($type, $details, $itemtype, $sharingLinks);
        return;
    }
    public function index()
    {
        $id = $_GET['id'];
        return Factory::where('user_id', $id)->get();
        /*$query = Rundata::query();
        $query->with('user');

        $forms = $query->get();
        return $forms;*/
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $data = new Factory();
        $data->fill($request->all());
        $data->save();
        return $data;
    }

    public function getFactoryInfo()
    {
        $factoryId = auth('api')->user()->factoryId;
        $data = Factory::where('factories.id', $factoryId)
            ->leftjoin('esells_settings', 'esells_settings.factory_id', '=', 'factories.id')
            ->select('factories.nameFac', 'factories.telephoneFac', 'factories.addressFac', 'factories.logoFac', 'factories.servicetype', 'factories.serviceexpire', 'factories.state', 'factories.description', 'factories.website', 'esells_settings.show_cubes', 'esells_settings.show_clusters', 'esells_settings.isPublic', 'esells_settings.whatsapp_number')
            ->first();

        return $data;
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function addFactory(Request $request)
    {
        $user = auth('api')->user();
        if (!$user->factoryId) {
            $data = new Factory();
            $data->nameFac = $request->nameFac;
            $data->telephoneFac = $request->telephoneFac;
            $data->addressFac = $request->addressFac;
            $data->state = $request->state;
            $data->save();
            $user->factoryId = $data->id;
            if ($user->update()) {
                $esellsSettings = new EsellsSettings();
                $esellsSettings->factory_id = $user->factoryId;
                $esellsSettings->show_cubes = false;
                $esellsSettings->show_clusters = true;
                $esellsSettings->isPublic = true;
                $esellsSettings->whatsapp_number = auth('api')->user()->mobile;
                $esellsSettings->save();
                $faOption = new FactoryOptions();
                $faOption->max_file_uploaded = 10;
                $faOption->max_event_saved = 400;
                $faOption->max_upload_size = 20971520;
                $faOption->factory_id = $user->factoryId;
                $faOption->save();
                return $user->factoryId;
            } else {
                return response()->json('false', 401);
            }
        } else {
            return response()->json('you alreade have Factory number', 401);
        }
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $data = Factory::find($id);
        return $data;
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateFactory(Request $request)
    {
        $user = auth('api')->user();
        if ($user->type = 'manager') {
            $data = Factory::find($user->factoryId);
            $data->nameFac = $request->nameFac;
            $data->telephoneFac = $request->telephoneFac;
            $data->addressFac = $request->addressFac;
            $data->state = $request->state;
            $data->description = $request->description;
            $data->website = $request->website;
            $update = $data->save();
            $this->newEvent('editFac', 'اطلاعات مجموعه شما توسط ' . auth('api')->user()->name . ' ویرایش شد.', '', '');
            return 'Update';
        } else {
            return response()->json('false', 401);
        }
    }
    public function enableFactoryEsells()
    {
        $user = auth('api')->user()->type;
        if ($user == 'manager') {
            $data = Factory::find(auth('api')->user()->factoryId);
            $data->cluster_esells = 'yes';
            return $data->save();
        } else {
            abort(402, 'go fuck your self');
        }
    }
    public function disableFactoryEsells()
    {
        $user = auth('api')->user()->type;
        if ($user == 'manager') {
            $data = Factory::find(auth('api')->user()->factoryId);
            $data->cluster_esells = 'no';
            return $data->save();
        } else {
            abort(402, 'go fuck your self');
        }
    }
    public function changeFactoryEsellsSetting(Request $request)
    {
        $user = auth('api')->user()->type;
        if ($user == 'manager') {
            $data = EsellsSettings::where('factory_id', auth('api')->user()->factoryId)->first();
            $oprType = $request->oprType;
            $itemType = $request->itemType;
            if ($itemType == 'cube') {
                if ($oprType == true) {
                    $data->show_cubes = true;
                }
                if ($oprType == false) {
                    $data->show_cubes = false;
                }
            }
            if ($itemType == 'cluster') {
                if ($oprType == true) {
                    $data->show_clusters = true;
                }
                if ($oprType == false) {
                    $data->show_clusters = false;
                }
            }
            if ($itemType == 'isPublic') {
                if ($oprType == true) {
                    $data->isPublic = true;
                } else {
                    $data->isPublic = false;
                }
            }
            if ($itemType == 'whatsapp') {
                $this->validate($request, [
                    'whatsapp_number' => 'required|min:11|max:11|regex:/^09[0-9]{9}$/',
                ]);
                $data->whatsapp_number = $request->whatsapp_number;
            }
            $data->save();
            return response()->json($data, 200);
        } else {
            abort(402, 'go fuck your self');
        }
    }
    public function getFactoryEsellsStatus()
    {
        $user = auth('api')->user()->type;
        if ($user == 'manager') {
            $data = Factory::find(auth('api')->user()->factoryId);
            return $data->cluster_esells;
        } else {
            abort(402, 'go fuck your self');
        }
    }
    public function addImageFactory(Request $request)
    {
        $image = $request->file('file');
        $factoryId = auth('api')->user()->factoryId;

        $factory = Factory::findOrFail($factoryId);

        $path = public_path() . '/storage/img/factory/' . $factory->logoFac;
        if (file_exists($path) && $factory->logoFac != 'factory.png') {
            unlink($path);
        }
        $filename = 'FactoryLogo' . time() . $request->file->getClientOriginalName();
        $image_resize = Image::make($image->getRealPath());
        $image_resize->resize(350, null, function ($constraint) {
            $constraint->aspectRatio();
        });
        $image_resize->save(public_path('/storage/img/factory/' . $filename));
        $factory->logoFac = $filename;
        $factory->update();

        return response()->json([$filename], 200);
    }
    public function addBannerImgFactory(Request $request)
    {
        $image = $request->file('file');

        $factory = Factory::findOrFail($request->factoryId);
        $path = public_path() . '/storage/img/factory/' . $factory->bannerImg;
        if (file_exists($path) && $factory->bannerImg != 'banner.jpg') {
            unlink($path);
        }
        $filename = 'FactoryBanner' . time() . $request->file->getClientOriginalName();
        $image_resize = Image::make($image->getRealPath());
        $image_resize->resize(1180, 300, function ($constraint) {
            $constraint->aspectRatio();
        });
        $image_resize->save(public_path('/storage/img/factory/' . $filename));
        $factory->bannerImg = $filename;
        $factory->update();

        return response()->json([$filename], 200);
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $data = Factory::find($id);
        $data->delete();
        return $data;
    }
    /**
     * Get the authenticated User
     *
     * @return [json] user object
     */
    public function factory(Request $request)
    {
        return response()->json($request->factory());
    }
    ////********** main section
    public function getRandomFactory()
    {
        return Factory::where('servicetype', '=', 'gold-12')
            ->get()
            ->random(2);
    }
    public function getSellersList()
    {
        return Factory::where('cluster_esells', '=', 'yes')
            ->orderBy('servicetype', 'desc')
            ->orderBy('created_at', 'asc')
            ->select('nameFac', 'state', 'logoFac')
            ->get();
    }
    public function getSellerInfo($id)
    {
        $data = Factory::where('nameFac', '=', $id)
            ->leftjoin('socialnets', 'socialnets.factoryId', '=', 'factories.id')
            ->select('factories.nameFac', 'factories.telephoneFac', 'factories.addressFac', 'factories.logoFac', 'factories.state', 'factories.description as factoryDescription', 'factories.bannerImg', 'socialnets.instagram', 'socialnets.twitter', 'socialnets.youtube', 'socialnets.telegram', 'socialnets.facebook', 'socialnets.linkedin')
            ->first();
        if ($data) {
            return $data;
        } else {
            abort('Factory dosent exist');
        }
    }
    public function getSellerInfoById($id)
    {
        $data = Factory::where('id', $id)
            ->select('factories.id as factoryId', 'factories.nameFac', 'factories.telephoneFac', 'factories.addressFac', 'factories.logoFac', 'factories.state', 'factories.description as factoryDescription', 'factories.servicetype')
            ->get()
            ->first();
        if ($data) {
            return $data;
        } else {
            abort('Factory dosent exist');
        }
    }
    public function getSellerState()
    {
        $data = Factory::where('cluster_esells', '=', 'yes')
            ->select('state', DB::raw('count(*) as total'))
            ->groupBy('state')
            ->orderBy('state', 'asc')
            ->get();
        if ($data) {
            return $data;
        } else {
            abort('there are no Sellers');
        }
    }
    public function getSellerByStateName($state)
    {
        $data = Factory::where('cluster_esells', '=', 'yes')
            ->where('state', '=', $state)
            ->orderBy('servicetype', 'desc')
            ->orderBy('created_at', 'asc')
            ->select('nameFac', 'state', 'logoFac')
            ->get();
        if ($data) {
            return $data;
        } else {
            abort('there are no Sellers');
        }
    }
    public function getTopSellerByEnteringDate()
    {
        $data = Factory::where('cluster_esells', '=', 'yes')
            ->orderBy('servicetype', 'desc')
            ->orderBy('created_at', 'asc')
            ->select('nameFac', 'state', 'logoFac')
            ->take(8)
            ->get();
        if ($data) {
            return $data;
        } else {
            abort('there are no Sellers');
        }
    }
}
