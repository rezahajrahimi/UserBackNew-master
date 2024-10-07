<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\OrderPremission;
use App\Models\CubePremission;
use App\Models\ClusterPremission;
use App\Models\Factory;
// use Smsirlaravel;
use SMSIR;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Intervention\Image\ImageManagerStatic as Image;
use Carbon\Carbon;
use Throwable;

class UserController extends Controller
{
    public function forgetPass($mobile)
    {
        $valid = User::where('mobile', $mobile)->count();
        if ($valid > 0) {
            $data = User::where('mobile', $mobile)->first();
            $pass = rand(1000001, 9999999);
            $receptor = $data->mobile;
            //Laravel 9
            $result = SMSIR::ultraFastSend(['Password' => $pass], 22363, $data->mobile);
            $data->password = Hash::make($pass);
            $data->save();
            return response()->json(true);
        } else {
            return response()->json(false);
        }
    }
    public function updateProfile(Request $request)
    {
        // user can update himself profile

        $user = auth('api')->user();
        if (!empty($request->password)) {
            $this->validate($request, [
                'name' => 'required|string|max:255',
                'mobile' => 'required|min:11|max:11|regex:/^09[0-9]{9}$/',
                'password' => 'required|string|min:6',
            ]);
        } else {
            $this->validate($request, [
                'name' => 'required|string|max:255',
                'mobile' => 'required|min:11|max:11|regex:/^09[0-9]{9}$/',
            ]);
        }
        if ($user->mobile != $request->mobile) {
            $da = User::where('mobile', $request->mobile);
            if ($da->count() > 0) {
                return response('شماره موبایل قبلا ثبت شده است', 422);
            }
        }
        $currentPhoto = $user->profilepic;

        if (!empty($request->password)) {
            $request->merge(['password' => Hash::make($request['password'])]);
        }

        $user->update($request->all());
        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->token;
        $token->expires_at = Carbon::now()->addWeeks(2);
        $token->save();
        return response()->json([
            'access_token' => $tokenResult->accessToken,
            'name' => $user->name,
            'profilepic' => $user->profilepic,
            'mobile' => $user->mobile,
        ]);
        // return ['message' => "Success"];
    }
    public function updateUserProfileImg(Request $request)
    {
        $userImg = auth('api')->user()->profilepic;
        $path = public_path() . '/storage/img/user/' . $userImg;
        if (file_exists($path) && $userImg != 'profile.jpg') {
            unlink($path);
        }
        $image = $request->file('file');
        $user = auth('api')->user();
        $filename = 'UserProfile' . time() . $request->file->getClientOriginalName();
        $image_resize = Image::make($image->getRealPath());
        $image_resize->resize(350, null, function ($constraint) {
            $constraint->aspectRatio();
        });
        $image_resize->save(public_path('/storage/img/user/' . $filename));
        $user->profilepic = $filename;
        $user->update();

        return response()->json([$filename], 200);
    }
    public function updateUser(Request $request)
    {
        // manager can update users profile

        $userType = auth('api')->user()->type;
        if ($userType == 'manager') {
            $user = User::findOrFail($request['id']);
            $this->validate($request, [
                'name' => 'required|string|max:255',
                'mobile' => 'required|min:11|max:11|regex:/^09[0-9]{9}$/',
                'password' => 'required|string|min:6',
            ]);
            if ($user->mobile != $request->mobile) {
                $da = User::where('mobile', $request->mobile);
                if ($da->count() > 0) {
                    return response('شماره موبایل قبلا ثبت شده است', 422);
                }
            }
            $currentPhoto = $user->profilepic;

            if (!empty($request->password)) {
                $request->merge(['password' => Hash::make($request['password'])]);
            }

            $user->update($request->all());
            return response()->json(true);
        } else {
            return response()->json(false);
        }
    }
    public function factoryUser()
    {
        $factoryId = auth('api')->user()->factoryId;
        return User::where('factoryId', $factoryId)
            ->where('id', '!=', auth('api')->user()->id)
            ->select('users.id', 'users.name', 'users.created_at', 'users.email', 'users.factoryId', 'users.following_count', 'users.mobile', 'users.profilepic', 'users.type')

            ->get();
    }
    public function newCustomer(Request $request)
    {
        $this->validate($request, [
            'name' => ['required', 'string', 'max:255'],
            'mobile' => ['required', 'min:11', 'max:11', 'regex:/^09[0-9]{9}$/', 'unique:users'],
            'password' => ['required', 'string', 'min:6'],
        ]);
        $data = User::create([
            'name' => $request['name'],
            'type' => 'customer',
            'mobile' => $request['mobile'],
            'profilepic' => 'profile.jpg',
            'password' => Hash::make($request['password']),
        ]);
        //Laravel 9

       // $result = SMSIR::ultraFastSend(['PassWord' => $request['password']], 22363, $request->mobile);

        if ($data) {
            return response()->json(true);
        } else {
            return response()->json(false);
        }
    }
    public function newUserFac(Request $request)
    {
        $userType = auth('api')->user()->type;
        $factoryId = auth('api')->user()->factoryId;
        $factory = Factory::where('id', $factoryId)->firstOrFail();
        if ($factory->servicetype == 'free') {
            return response()->json(false, 404);
        }

        if ($userType == 'manager') {
            $this->validate($request, [
                'name' => ['required', 'string', 'max:255'],
                'mobile' => ['required', 'min:11', 'max:11', 'regex:/^09[0-9]{9}$/', 'unique:users'],
                'password' => ['required', 'string', 'min:6'],
            ]);
            $data = User::create([
                'name' => $request['name'],
                'type' => $request['type'],
                'mobile' => $request['mobile'],
                'profilepic' => 'profile.jpg',
                'password' => Hash::make($request['password']),
                'factoryId' => $factoryId,
            ]);

            $data2 = CubePremission::create([
                'user_id' => $data['id'],
                'cube_view' => $request['cube_view'],
                'cube_add' => $request['cube_add'],
                'cube_del' => $request['cube_del'],
                'cube_update' => $request['cube_update'],
                'cube_cutting' => $request['cube_cutting'],
            ]);
            $data3 = ClusterPremission::create([
                'user_id' => $data['id'],
                'cluster_view' => $request['cluster_view'],
                'cluster_add' => $request['cluster_add'],
                'cluster_del' => $request['cluster_del'],
                'cluster_update' => $request['cluster_update'],
            ]);
            $data4 = OrderPremission::create([
                'user_id' => $data['id'],
                'order_view' => $request['order_view'],
                'order_add' => $request['order_add'],
                'order_del' => $request['order_del'],
                'order_update' => $request['order_update'],
            ]);
            //Laravel 9

            // desible snd mseg
            //$result = SMSIR::ultraFastSend(['PassWord' => $request['password']], 22362, $request->mobile);

            $this->newEvent("addUser","کاربر ".$request->name." توسط ".auth('api')->user()->name." اضافه شد.","","");

            return response()->json(true);
        } else {
            return response()->json(false, 404);
        }
    }
    // public function newUserFac(Request $request)
    // {
    //     $userType = auth('api')->user()->type;
    //     $factoryId = auth('api')->user()->factoryId;
    //     $factory = Factory::where('id', $factoryId)->firstOrFail();
    //     if ($factory->servicetype == 'free') {
    //         return response()->json(false, 404);
    //     }

    //     if ($userType == 'manager') {
    //         $this->validate($request, [
    //             'name' => ['required', 'string', 'max:255'],
    //             'mobile' => ['required', 'min:11', 'max:11', 'regex:/^09[0-9]{9}$/', 'unique:users'],
    //             'password' => ['required', 'string', 'min:6'],
    //         ]);
    //         $data = User::create([
    //             'name' => $request['name'],
    //             'type' => $request['type'],
    //             'mobile' => $request['mobile'],
    //             'profilepic' => 'profile.jpg',
    //             'password' => Hash::make($request['password']),
    //             'factoryId' => $factoryId,
    //         ]);

    //         $data2 = CubePremission::create([
    //             'user_id' => $data['id'],
    //             'cube_view' => $request['cube_view'],
    //             'cube_add' => $request['cube_add'],
    //             'cube_del' => $request['cube_del'],
    //             'cube_update' => $request['cube_update'],
    //             'cube_cutting' => $request['cube_cutting'],
    //         ]);
    //         $data3 = ClusterPremission::create([
    //             'user_id' => $data['id'],
    //             'cluster_view' => $request['cluster_view'],
    //             'cluster_add' => $request['cluster_add'],
    //             'cluster_del' => $request['cluster_del'],
    //             'cluster_update' => $request['cluster_update'],
    //         ]);
    //         $data4 = OrderPremission::create([
    //             'user_id' => $data['id'],
    //             'order_view' => $request['order_view'],
    //             'order_add' => $request['order_add'],
    //             'order_del' => $request['order_del'],
    //             'order_update' => $request['order_update'],
    //         ]);
    //         //Laravel 9
    //         // $result = Smsirlaravel::ultraFastSend(['PassWord' => $request['password']], 22362, $request['mobile']);

    //         return response()->json(true);
    //     } else {
    //         return response()->json(false, 404);
    //     }
    // }
    public function updateUserPremisson(Request $request)
    {
        $userType = auth('api')->user()->type;
        $factoryId = auth('api')->user()->factoryId;
        $factory = Factory::where('id', $factoryId)->firstOrFail();
        if ($factory->servicetype == 'free') {
            return response()->json(false, 404);
        }

        if ($userType == 'manager') {
            $user = User::where('id', $request->userId)
                ->where('factoryId', $factoryId)
                ->firstOrFail();
            if ($user == null) {
                return response()->json(false, 404);
            }
            // $data = User::update([
            //     'name' => $request['name'],
            //     'type' => $user->type,
            //     'mobile' => $request['mobile'],
            //     'profilepic' => $user->profilepic,
            //     'password' => Hash::make($request['password']),
            //     'factoryId' => $user->factoryId,
            // ]);
            $cuPremission = CubePremission::where('user_id', $request->userId)->firstOrFail();
            $cuPremission->cube_view = $request['cube_view'];
            $cuPremission->cube_add = $request['cube_add'];
            $cuPremission->cube_del = $request['cube_del'];
            $cuPremission->cube_update = $request['cube_update'];
            $cuPremission->cube_cutting = $request['cube_cutting'];
            $cuPremission->update();

            $clPremissoon = ClusterPremission::where('user_id', $request->userId)->firstOrFail();
            $clPremissoon->cluster_view = $request['cluster_view'];
            $clPremissoon->cluster_add = $request['cluster_add'];
            $clPremissoon->cluster_del = $request['cluster_del'];
            $clPremissoon->cluster_update = $request['cluster_update'];
            $clPremissoon->update();

            $orPremisson = OrderPremission::where('user_id', $request->userId)->firstOrFail();
            $orPremisson->order_view = $request['order_view'];
            $orPremisson->order_add = $request['order_add'];
            $orPremisson->order_del = $request['order_del'];
            $orPremisson->order_update = $request['order_update'];
            $orPremisson->update();

            // $result = Smsirlaravel::ultraFastSend(['PassWord' => $request['password']], 22362, $request['mobile']);
            $this->newEvent("editUser","مجوز‌های دسترسی ".$user->name." توسط ".auth('api')->user()->name." تغییر کرد.","","");

            return response()->json(true);
        } else {
            return response()->json(false, 404);
        }
    }
    public function getFactoryUserPremissonsByID($userID)
    {
        $userType = auth('api')->user()->type;
        $factoryId = auth('api')->user()->factoryId;
        $factory = Factory::where('id', $factoryId)->firstOrFail();
        if ($factory->servicetype == 'free') {
            return response()->json(false, 404);
        }

        if ($userType == 'manager') {
            $user = User::where('id', $userID)
                ->where('factoryId', $factoryId)
                ->firstOrFail();
            if ($user == null) {
                return response()->json(false, 404);
            }

            $cuPreController = new CubePremissionController();
            $clPreController = new ClusterPremissionController();
            $orPreController = new OrderPremissionController();

            $cuPremissons = $cuPreController->getUserCubePremissonByIdNOnJson($userID);
            $clPremissons = $clPreController->getUserClusterPremissonByIdNOnJson($userID);
            $orPremissons = $orPreController->getUserOrderPremissonByIdNOnJson($userID);
            $data = [$cuPremissons, $clPremissons, $orPremissons];
            return response()->json($data);
        } else {
            return response()->json(false, 404);
        }
    }
    public function delUser($id)
    {
        $userType = auth('api')->user()->type;
        $userFacId = auth('api')->user()->factoryId;
        if ($userType == 'manager') {
            $user = User::where('id', $id)->firstOrFail();
            if ($user->factoryId == $userFacId && $user != null) {
                $user->delete();
                $this->newEvent("delUser",$user->name." توسط " .auth('api')->user()->name." حذف گردید.","","");
                return response()->json(true, 200);
            }
            return response()->json('Go Fuck Your Self!', 404);
        } else {
            return response()->json('Go Fuck Your Self!', 404);
        }
    }
    private function newEvent($type,$details,$itemtype,$sharingLinks) {
        $event = new EventsController();
        $event->newEvent($type,$details,$itemtype,$sharingLinks);
        return;
    }
}
