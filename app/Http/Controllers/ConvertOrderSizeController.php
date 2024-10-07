<?php

namespace App\Http\Controllers;

use App\Models\ConvertOrderSize;
use App\Models\Clusters;
use App\Models\Clustersize;
use App\Models\Order;
use App\Models\OrderSize;
use Illuminate\Http\Request;

class ConvertOrderSizeController extends Controller
{
    public function showConvertOrderSizeByOrderID ($orderId) {
        $data= ConvertOrderSize::where('orderId',$orderId)->get();
        return response()->json([$data]);

    }
    public function addOutlyngToConvertSize(Request $request) {
        $convert = ConvertOrderSize::where('orderSizeId',$request->orderSizeId)->first();
        $orderSize = OrderSize::findOrFail($convert->orderSizeId);
        $data = new ConvertOrderSize();
        $data->orderSizeId = $convert->orderSizeId;
        $data->lengthCs = $request->length;
        $data->widthCs = $request->width;
        $data->countCs = $request->count;
        $data->orderId = $convert->orderId;
        $data->sumCs = ($request->count) * ($request->width) * ($request->length);
        $data->save();
        $sumWidth = ConvertOrderSize::where('orderSizeId',$request->orderSizeId)->sum('widthCs');
        $sumLenght = ConvertOrderSize::where('orderSizeId',$request->orderSizeId)->sum('lengthCs');

        $orderSize->outlying = $request->outlying;
        return response()->json([$orderSize->update()]);
    }
    public function addConvertOrderSize(Request $request)
    {
        /*
        $data = new ConvertOrderSize();
        $data->orderSizeId = $request->orderSizeId;
        $data->lengthCs = $request->length;
        $data->widthCs = $request->width;
        $data->countCs = $request->count;
        $data->sumCs = ($request->count) * ($request->width) * ($request->length);
        $data->save();
        $sumWidth = ConvertOrderSize::where('orderSizeId',$request->orderSizeId)->sum('widthCs');
        $sumLenght = ConvertOrderSize::where('orderSizeId',$request->orderSizeId)->sum('lengthCs');
        $convert = ConvertOrderSize::findOrFail($request->orderSizeId);
        $orderSize = OrderSize::findOrFail($convert->orderSizeId);
        $orderSize->hasConvert = 1;
        $orderSize->outlying = $request->outlying;
        $orderSize->widthOut = ($orderSize->width) - $sumWidth;
        $orderSize->lenghtOut = ($orderSize->length) - $sumLenght;
        return response()->json([$orderSize->update()]);
        */
        $orderSize = OrderSize::findOrFail($request->orderSizeId);
        $data = new ConvertOrderSize();
        $data->orderSizeId = $request->orderSizeId;
        $data->lengthCs = $request->length;
        $data->widthCs = $request->width;
        $data->countCs = $request->count;
        $data->orderId = $orderSize->orderId;
        $data->sumCs = ($request->count) * ($request->width) * ($request->length);
        $data->save();
        $sumWidth = ConvertOrderSize::where('orderSizeId',$request->orderSizeId)->sum('widthCs');
        $sumLenght = ConvertOrderSize::where('orderSizeId',$request->orderSizeId)->sum('lengthCs');

        $orderSize->hasConvert = 1;
        $orderSize->outlying = $request->outlying;
        $orderSize->widthOut = ($orderSize->width) - $sumWidth;
        $orderSize->lenghtOut = ($orderSize->length) - $sumLenght;
        return response()->json([$orderSize->update()]);
    }
    public function deleteConvertSizeById($id)
    {
        $convertsize = ConvertOrderSize::where('orderSizeId',$id);
        $convertsize->delete();
        $orderSize = OrderSize::findOrFail($id);
        $Ordercount = $orderSize->count;
        $clusterSizeId = $orderSize->clusterSizeId;
        $orderId = $orderSize->orderId;
        $orderSize->delete();

        $count = OrderSize::where('orderId', $orderId)->sum('count');
        $existence = OrderSize::where('orderId', $orderId)->sum('sum');
        $order = Order::findOrFail($orderId);
        $order->count = $count;
        $order->existence = $existence;
        $order->update();

        $clustersize = Clustersize::findOrFail($clusterSizeId);
        $clustersize->count = $clustersize->count + $Ordercount;

        $clustersize->sum = ($clustersize->count) * ($clustersize->width) * ($clustersize->length);
        $clustersize->update();

        $existence = Clustersize::where('clusterId', $clustersize->clusterId)->sum('sum');
        $count = Clustersize::where('clusterId', $clustersize->clusterId)->sum('count');
        $cluster = Clusters::findOrFail($clustersize->clusterId);
        $cluster->existence = $existence;
        $cluster->count = $count;

        return response()->json([$cluster->update()]);

    }

}
