<?php

namespace App\Http\Controllers;

use App\Models\OrderLoading;
use Illuminate\Http\Request;
use Storage;
use Verta;
use File;
use Carbon\Carbon;
use GuzzleHttp\RetryMiddleware;

class OrderLoadingController extends Controller
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
        $cubeCtrl = new OrderPremissionController();
        return $cubeCtrl->getUserOrderPremissonByIdAndTypeNOnJson($userID, $type);
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
    public function addNewOrderLoading(Request $request)
    {
        $factoryId = auth('api')->user()->factoryId;
        if ($this->checkUserPremisson('update') == false) {
            return response()->json('GO Fuck YourSelf 1', 401);
        }
        $ordCntrl = new OrderController();
        $ordFacId = $ordCntrl->getFactoryIdByOrderId($request->order_id);
        if ($factoryId == $ordFacId) {
            $data = new OrderLoading();
            $data->factory_id = $factoryId;
            $data->order_id = $request->order_id;
            $data->driver_name = $request->driver_name;
            $data->truck_number = $request->truck_number;
            $data->driver_phone = $request->driver_phone;

            $data->loading_date = $this->getMiladyDate($request->loading_date) ?? Carbon::now();
            $data->cargo_weight = $request->cargo_weight;
            $data->description = $request->description;
            $data->shipping_address = $request->shipping_address;
            if ($request->has_image != null) {
                if ($request->hasImage == 'true' || $request->hasImage == 1) {
                    $data->has_image = true;
                }
            } else {
                $data->has_image = false;
            }
            $data->save();
            $this->newEvent('addOrderLoading', 'افزودن بارگیری به خروجی', 'order', '');
            return response()->json($data->id);
        } else {
            return response()->json('GO Fuck YourSelf', 401);
        }
    }
    public function editOrdelLoading(Request $request)
    {
        $factoryId = auth('api')->user()->factoryId;
        if ($this->checkUserPremisson('update') == false) {
            return response()->json('GO Fuck YourSelf', 401);
        }
        $data = OrderLoading::find($request->id);
        if ($factoryId == $data->factory_id) {
            $data->driver_name = $request->driver_name;
            $data->truck_number = $request->truck_number;
            $data->driver_phone = $request->driver_phone;

            $data->loading_date = $this->getMiladyDate($request->loading_date) ?? Carbon::now();
            $data->cargo_weight = $request->cargo_weight;
            $data->description = $request->description;
            $data->shipping_address = $request->shipping_address;
            if ($request->has_image != null) {
                if ($request->hasImage == 'true' || $request->hasImage == 1) {
                    $data->has_image = true;
                }
            } else {
                $data->has_image = false;
            }
            if ($request->deleted_img_list != null && count($request->deleted_img_list) > 0) {
                $deleted_img_list = $request->deleted_img_list;
                $imagCntrl = new OrderImagesController();

                foreach ($deleted_img_list as $value) {
                    $imagCntrl->deleteOrderImageByOrderByID($value);
                }
            }
            $data->update();
            // $this->newEvent('updateOrderLoading', 'ویرایش بارگیری', 'order', '');
            return response()->json($data);
        } else {
            return response()->json('GO Fuck YourSelf', 401);
        }
    }
    public function getOrderLoadingById($id)
    {
        $factoryId = auth('api')->user()->factoryId;
        if ($this->checkUserPremisson('view') == false) {
            return response()->json('GO Fuck YourSelf', 401);
        }
        $data = OrderLoading::where('id', $id)
            ->with('order_images')
            ->first();
        if ($factoryId == $data->factory_id) {
            return response()->json($data);
        }
    }
    public function deleteOrdelLoading($id)
    {
        $factoryId = auth('api')->user()->factoryId;
        if ($this->checkUserPremisson('update') == false) {
            return response()->json('GO Fuck YourSelf', 401);
        }
        $data = OrderLoading::find($id);
        if ($factoryId == $data->factory_id) {
            $imagCntrl = new OrderImagesController();

            $imagCntrl->deleteOrderImageByOrderLoadingId($data->id);
            $data->delete();
            $this->newEvent('deleteOrderLoading', 'حذف بارگیری', 'order', '');
            return response()->json($data);
        } else {
            return response()->json('GO Fuck YourSelf', 401);
        }
    }
    public function deleteOrderLoadingByOrderId($id)

    {
        $factoryId = auth('api')->user()->factoryId;
        if ($this->checkUserPremisson('update') == false) {
            return response()->json('GO Fuck YourSelf', 401);
        }
        $data = OrderLoading::where('order_id', $id)->first();
        if (null != $data && $factoryId == $data->factory_id ) {
            $imagCntrl = new OrderImagesController();
            $imagCntrl->deleteOrderImageByOrderLoadingId($data->id);

            $data->delete();
            $this->newEvent('deleteOrderLoading', 'حذف بارگیری', 'order', '');
            return response()->json($data);
        } else {
            return response()->json('GO Fuck YourSelf', 401);
        }
    }
}
