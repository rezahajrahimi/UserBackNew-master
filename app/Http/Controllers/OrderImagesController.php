<?php

namespace App\Http\Controllers;

use App\Models\OrderImages;
use App\Models\Order;
use App\Models\OrderLoading;
use Illuminate\Http\Request;
use File;
use Intervention\Image\ImageManagerStatic as Image;
use Storage;

class OrderImagesController extends Controller
{
    public function addImageOrder(Request $request)
    {
        $factoryId = auth('api')->user()->factoryId;

        $image = $request->file('file');
        $filename = 'Order' . time() . $request->file->getClientOriginalName();

        $path = public_path() . "/storage/img/order/$factoryId/$request->order_id";
        if (!File::isDirectory($path)) {
            File::makeDirectory($path, 0755, true, true);
        }
        $image_resize = Image::make($image->getRealPath());
        $image_resize->resize(1200, null, function ($constraint) {
            $constraint->aspectRatio();
        });
        $image_resize->save(public_path("/storage/img/order/$factoryId/$request->order_id/$filename"));
        $orderImage = new OrderImages();
        $orderImage->order_id = $request->order_id;
        $orderImage->order_loading_id = $request->order_loading_id;
        $orderImage->image_url = "$factoryId/$request->order_id/$filename";
        $orderImage->save();
        $order = OrderLoading::findOrFail($request->order_loading_id);
        if ($order->has_image == false || $order->has_image == 0) {
            $order->has_image = true;
            $order->update();
        }
        return response()->json(true, 200);
    }
    public function deleteOrderImageByOrderLoadingId($id)
    {
        $factoryId = auth('api')->user()->factoryId;

        $orderImage = OrderImages::where('order_loading_id', $id)->get();
        foreach ($orderImage as $imgsrc) {
            $path = public_path() . '/storage/img/order/' . $imgsrc->image_url;
            if (file_exists($path)) {
                unlink($path);
            }
            $imgsrc->delete();

        }


        return true;
    }
    public function deleteOrderImageByOrderByID($id)
    {
        $factoryId = auth('api')->user()->factoryId;

        $orderImage = OrderImages::where('id', $id)->first();
        $path = public_path() . '/storage/img/order/' . $orderImage->image_url;
        if (file_exists($path)) {
            unlink($path);
        }
        $orderImage->delete();
        return true;
    }
}
