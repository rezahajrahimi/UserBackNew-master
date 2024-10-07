<?php

namespace App\Http\Controllers;

use App\Models\Clusters;
use App\Models\Clustersize;
use App\Models\Order;
use App\Models\ClusterLog;
use App\Models\OrderSize;
use App\Models\ConvertOrderSize;
use Illuminate\Http\Request;

class OrderSizeController extends Controller
{
    public function checkUserPremisson($type)
    {
        $userID = auth('api')->user()->id;
        $cubeCtrl = new OrderPremissionController();
        return $cubeCtrl->getUserOrderPremissonByIdAndTypeNOnJson($userID, $type);
    }
    public function addordersize(Request $request)
    {
        if ($this->checkUserPremisson('update') == false) {
            return response()->json(false, 401);
        }
        $factoryId = auth('api')->user()->factoryId;

        $clustersize = Clustersize::findOrFail($request->selectedClusterSizeiD);
        $cluster = Clusters::findOrFail($clustersize->clusterId);
        if ($factoryId != $cluster->factoryId) {
            return response()->json('Go Fuck Your Self', 401);
        }
        $data = OrderSize::where('clusterNumber', $request->clusterNumber)
            ->where('orderId', $request->orderId)
            ->where('hasConvert', 0)
            ->where('width', $request->width)
            ->where('length', $request->length);
        if ($data->count() > 0) {
            $data = OrderSize::find($data->first()->id);

            $data->count = $data->count + $request->count;
            $data->sum = $data->count * $data->width * $data->length;
            $data->ordered_number = $request->orderedNumber;
            $data->save();
        } else {
            $data = new OrderSize();
            $data->orderId = $request->orderId;
            $data->length = $request->length;
            $data->width = $request->width;
            $data->count = $request->count;
            $data->clusterNameStone = $request->clusterNameStone;
            $data->clusterNumber = $request->clusterNumber;
            $data->sum = $request->count * $request->width * $request->length;
            $data->clusterSizeId = $request->selectedClusterSizeiD;
            $data->ordered_number = $request->orderedNumber;
            $data->save();
        }
        $order = Order::findOrFail($request->orderId);
        $factoryId = auth('api')->user()->factoryId;
        if ($order->factoryId != $factoryId) {
            return response()->json('Go Fuck Your Self!!!', 401);
        }
        $order->existence = $order->existence + $data->sum;
        $order->count = $order->count + $request->count;
        $order->update();

        // $clustersize->width = $request->width;
        if ($cluster->type == 'slab' || $cluster->type == 'tile') {
            $cluster->count = $cluster->count - $request->count;

            $clustersize->count = $clustersize->count - $request->count;
            // $clustersize->length =  $request->length;

            $clustersize->sum = $clustersize->sum - $request->count * $request->width * $request->length;
            if ($clustersize->count == 0) {
                $clustersize->sum = 0;
                $clustersize->exist_number = '';
            } else {
                $arrExist = explode(',', $clustersize->exist_number);
                $arrOrder = explode(',', $request->orderedNumber);
                $newExist = array_diff($arrExist, $arrOrder);
                // return $newExist;
                $clustersize->exist_number = implode(',', $newExist);
            }
            $re = $this->addLog($clustersize->clusterId, 'order', "ثبت در خروجی {$order->customerName} با متراژ {$data->sum}");
            $clustersize->update();
        } else {
            $clustersize->sum = $clustersize->sum - $request->width * $request->length;
            $clustersize->length = $clustersize->length - $request->length;

            if ($clustersize->sum == 0) {
                if($cluster->count == 1) {
                    $cluster->count = 0;
                } else {
                    $cluster->count = $cluster->count - 1;
                }

                $clustersize->sum = 0;
                $clustersize->count = 0;
                $clustersize->exist_number = '';
            } else {
                $clustersize->count = 1;
                // $cluster->count = 1;

                $clustersize->exist_number = '1';
            }
            $re = $this->addLog($clustersize->clusterId, 'order', "ثبت در خروجی {$order->customerName} با متراژ {$data->sum}");
            $clustersize->update();
        }
        // $re = $this->addLog($clustersize->clusterId, "order", "ثبت در خروجی با متراژ " . $data->sum ." متر");

        /// ******** //////////**********************
        $cluster->existence = $cluster->existence - $data->sum;
        // $cluster->count = $cluster->count - $request->count;
        if ($cluster->count == 0) {
            $cluster->existence = 0;
        }
        if ($cluster->update()) {
            return Clustersize::where('clusterId', $data->clusterId)->get();
        } else {
            return response()->json([$cluster->update()]);
        }
    }
    public function editordersize(Request $request)
    {
        if ($this->checkUserPremisson('update') == false) {
            return response()->json(false, 401);
        }
        $ordersizeid = $request->id;
        $newcount = $request->newcount;
        $orderSize = OrderSize::findOrFail($ordersizeid);
        $clusterSizeId = $orderSize->clusterSizeId;
        if ($newcount > $orderSize->count) {
            $dif = $newcount - $orderSize->count;
            $orderSize->count = $orderSize->count + $dif;
            $orderSize->sum = $orderSize->count * $orderSize->width * $orderSize->length;
            $orderSize->update();
            $order = Order::findOrFail($orderSize->orderId);
            $order->count = $order->count + $dif;
            $order->existence = $order->existence + $dif * $orderSize->width * $orderSize->length;
            $order->update();
            $clustersize = Clustersize::findOrFail($clusterSizeId);
            $clustersize->count = $clustersize->count - $dif;

            //$temp = ($clustersize->count)*($clustersize->width)*($clustersize->length);
            $clustersize->sum = $clustersize->count * $clustersize->width * $clustersize->length;
            if ($clustersize->count == 0) {
                $clustersize->sum = 0;
            }
            //$difclustersize = $clustersize->sum - $temp ;
            $re = $this->addLog($clustersize->clusterId, 'order', "تغییر در خروجی {$order->customerName} با متراژ {$orderSize->sum}");
            // $re = $this->addLog($clustersize->clusterId, "order", "تغییر در خروجی با متراژ " . $orderSize->sum ." متر");

            $clustersize->update();

            $existence = Clustersize::where('clusterId', $clustersize->clusterId)->sum('sum');
            $count = Clustersize::where('clusterId', $clustersize->clusterId)->sum('count');
            $cluster = Clusters::findOrFail($clustersize->clusterId);
            $cluster->existence = $existence;
            $cluster->count = $count;

            return response()->json([$cluster->update()]);
        } elseif ($newcount < $orderSize->count) {
            $dif = $orderSize->count - $newcount;
            $orderSize->count = $orderSize->count - $dif;
            $orderSize->sum = $orderSize->count * $orderSize->width * $orderSize->length;
            $orderSize->update();
            $order = Order::findOrFail($orderSize->orderId);
            $order->count = $order->count - $dif;
            $order->existence = $order->existence - $dif * $orderSize->width * $orderSize->length;
            $order->update();
            $clustersize = Clustersize::findOrFail($clusterSizeId);
            $clustersize->count = $clustersize->count + $dif;
            $clustersize->sum = $clustersize->count * $clustersize->width * $clustersize->length;
            $re = $this->addLog($clustersize->clusterId, 'order', "تغییر در خروجی {$order->customerName} با متراژ {$orderSize->sum}");

            $clustersize->update();

            $existence = Clustersize::where('clusterId', $clustersize->clusterId)->sum('sum');
            $count = Clustersize::where('clusterId', $clustersize->clusterId)->sum('count');
            $cluster = Clusters::findOrFail($clustersize->clusterId);
            $cluster->existence = $existence;
            $cluster->count = $count;
            if ($clustersize->count == 0) {
                $clustersize->sum = 0;
            }

            return response()->json([$cluster->update()]);
        }
    }
    public function deleteOrderSize($id)
    {
        if ($this->checkUserPremisson('update') == false) {
            return response()->json(false, 401);
        }
        $factoryId = auth('api')->user()->factoryId;

        $ordersizeid = $id;
        $orderSize = OrderSize::findOrFail($ordersizeid);
        $Ordercount = $orderSize->count;
        $clusterSizeId = $orderSize->clusterSizeId;
        $existenceback = $orderSize->sum; // برای ثبت در log
        $orderId = $orderSize->orderId;

        $clustersize = Clustersize::findOrFail($clusterSizeId);

        $cluster = Clusters::findOrFail($clustersize->clusterId);
        if ($factoryId != $cluster->factoryId) {
            return response()->json('Go Fuck Your Self', 401);
        }
        // /////////////

        // $count = OrderSize::where('orderId', $orderId)->sum('count');
        // $existence = OrderSize::where('orderId', $orderId)->sum('sum');
        // $order = Order::findOrFail($orderId);
        // $order->count = $count;
        // $order->existence = $existence;

        // $order->update();
        // ////////////////////////
        if ($cluster->type == 'slab' || $cluster->type == 'tile') {
            $clustersize->count = $clustersize->count + $Ordercount;
            if ($clustersize->exist_number != null || $clustersize->exist_number != '') {
                $clustersize->exist_number = "{$clustersize->exist_number},{$orderSize->ordered_number}";
            } else {
                $clustersize->exist_number = $orderSize->ordered_number;
            }
            $clustersize->sum = $clustersize->count * $clustersize->width * $clustersize->length;
            if ($clustersize->count == 0) {
                $clustersize->sum = 0;
                $clustersize->exist_number = '';
            }

            $clustersize->update();
            $orderSize->delete();

            $existence = Clustersize::where('clusterId', $clustersize->clusterId)->sum('sum');
            $count = Clustersize::where('clusterId', $clustersize->clusterId)->sum('count');
            $cluster->existence = $existence;
            $cluster->count = $count;
            if ($clustersize->count == 0) {
                $clustersize->sum = 0;
            }
            /////////////

            $count = OrderSize::where('orderId', $orderId)->sum('count');
            $existence = OrderSize::where('orderId', $orderId)->sum('sum');
            $order = Order::findOrFail($orderId);
            $order->count = $count;
            $order->existence = $existence;

            $order->update();
            $re = $this->addLog($clustersize->clusterId, 'orderdel', "بازگشت از خروجی {$order->customerName} با متراژ {$existenceback}");

            ////////////////////////

            return response()->json([$cluster->update()]);
        } else {
            $clustersize->exist_number = '1';

            $clustersize->sum = $clustersize->sum + $existenceback;
            $clustersize->length = $clustersize->sum / $clustersize->width;
            if ($clustersize->count == 0) {
                $clustersize->count = 1;
            }

            $clustersize->update();
            $orderSize->delete();

            // $re = $this->addLog($clustersize->clusterId, 'orderdel', "بازگشت از خروجی {$order->customerName} با متراژ {$existenceback}");

            $existence = Clustersize::where('clusterId', $clustersize->clusterId)->sum('sum');
            $count = Clustersize::where('clusterId', $clustersize->clusterId)->sum('count');
            $cluster->existence = $existence;
            $cluster->count = $count;
            // if ($clustersize->count == 0) {
            //     $clustersize->sum = 0;
            // }
            /////////////

            $count = OrderSize::where('orderId', $orderId)->sum('count');
            $existence = OrderSize::where('orderId', $orderId)->sum('sum');
            $order = Order::findOrFail($orderId);
            $order->count = $count;
            $order->existence = $existence;

            $order->update();
            $re = $this->addLog($clustersize->clusterId, 'orderdel', "بازگشت از خروجی {$order->customerName} با متراژ {$existenceback}");

            ////////////////////////

            return response()->json([$cluster->update()]);
        }
    }
    public function showAllOrderSizeById($id)
    {
        return response()->json([OrderSize::where('orderId', $id)->where('hasConvert', 0)->get()]);
    }
    public function showOrderSizeById($id)
    {
        return response()->json([OrderSize::where('id', $id)->get()]);
    }
    public function showUncOrderSizeById($id)
    {
        // retern un complete size
        return OrderSize::where('orderId', $id)->where('hasConvert', 0)->where('status', 0)->get();
    }
    public function showComOrderSizeById($id)
    {
        // retern  complete size
        return OrderSize::where('orderId', $id)->where('hasConvert', 0)->where('status', 1)->get();
    }
    public function showOrderSizewithConvert($orderId)
    {
        // show All Convert OrderSize By OrderId
        $orderSize = OrderSize::where('orderId', $orderId)->where('.hasConvert', 1)->orderBy('updated_at', 'desc')->get();
        return $orderSize;
    }
    public function showUnComOrderSizewithConvert($orderId)
    {
        // show All Convert OrderSize By OrderId wherer not be complete
        $orderSize = OrderSize::where('orderId', $orderId)->where('status', 0)->where('hasConvert', 1)->orderBy('updated_at', 'desc')->get();
        return $orderSize;
    }
    public function showComOrderSizewithConvert($orderId)
    {
        // show All Convert OrderSize By OrderId wherer  be complete
        $orderSize = OrderSize::where('orderId', $orderId)->where('status', 1)->where('hasConvert', 1)->orderBy('updated_at', 'desc')->get();
        return $orderSize;
    }
    public function changeOrderSizeStatus($id)
    {
        $orderSize = OrderSize::findOrFail($id);
        $orderSize->status = 1;
        $orderSize->save();
        if (
            OrderSize::where('orderId', $orderSize->orderId)
                ->where('status', 0)
                ->count() == 0
        ) {
            $order = Order::findOrFail($orderSize->orderId);
            $order->status = 1;
            $order->save();
        }

        $showUncOrderSizeById = $this->showUncOrderSizeById($orderSize->orderId);
        $showComOrderSizeById = $this->showComOrderSizeById($orderSize->orderId);
        $data = [$showUncOrderSizeById, $showComOrderSizeById];
        return $data;
    }
    public function changeOrderSizeStatusToUn($id)
    {
        // chenge order size to un compelete
        $orderSize = OrderSize::findOrFail($id);
        $orderSize->status = 0;
        $orderSize->save();
        $order = Order::findOrFail($orderSize->orderId);
        $order->status = 0;
        $order->save();
        $showUncOrderSizeById = $this->showUncOrderSizeById($orderSize->orderId);
        $showComOrderSizeById = $this->showComOrderSizeById($orderSize->orderId);
        $data = [$showUncOrderSizeById, $showComOrderSizeById];
        return $data;
    }
    public function addLog($clusterId, $oprType, $oprText)
    {
        if (ClusterLog::where('clusterId', $clusterId)->count() > 20) {
            $data = ClusterLog::where('clusterId', $clusterId)->first();
            $data->delete();
        }
        $factoryId = auth('api')->user()->factoryId;
        $userName = auth('api')->user()->name;
        $data = new ClusterLog();
        $data->factoryId = $factoryId;
        $data->clusterId = $clusterId;
        $data->userName = $userName;
        $data->oprType = $oprType;
        $data->oprText = $oprText;
        $data->save();
        return true;
    }
    public function showOrderSizeByClusterId($id)
    {
        // show all ordersize for selectedcluster view
        $factoryId = auth('api')->user()->factoryId;
        // $data = ClusterImage::where('clusterId',$id)->get();
        $checkFactoryId = Clusters::findOrFail($id);
        if ($checkFactoryId->factoryId == $factoryId) {
            return OrderSize::leftjoin('clustersizes', 'clustersizes.id', '=', 'order_sizes.clusterSizeId')->leftjoin('orders', 'orders.id', '=', 'order_sizes.orderId')->leftjoin('customers', 'customers.id', '=', 'orders.customer_id')->where('clustersizes.clusterId', $id)->where('order_sizes.status', 0)->select('order_sizes.*', 'customers.name as customerName')->get();
        } else {
            return response()->json(false, 401);
        }
    }
    public function retriveOrderSpecific($orderId)
    {
        if ($this->checkUser($orderId)) {
            $showOrderSizewithConvert = $this->showOrderSizewithConvert($orderId);
            $showUnComOrderSizewithConvert = $this->showUnComOrderSizewithConvert($orderId);
            $showComOrderSizewithConvert = $this->showComOrderSizewithConvert($orderId);
            $showUncOrderSizeById = $this->showUncOrderSizeById($orderId);
            $showComOrderSizeById = $this->showComOrderSizeById($orderId);
            $showConvertOrderSizeByOrderID = ConvertOrderSize::where('orderId', $orderId)->get();
            $data = [$showOrderSizewithConvert, $showUnComOrderSizewithConvert, $showComOrderSizewithConvert, $showUncOrderSizeById, $showComOrderSizeById, $showConvertOrderSizeByOrderID];
            return $data;
        } else {
            abort('No Auth', 422);
        }
    }
    public function retriveOrderConvertSize($orderId)
    {
        if ($this->checkUser($orderId)) {
            $showUnComOrderSizewithConvert = $this->showUnComOrderSizewithConvert($orderId);
            $showComOrderSizewithConvert = $this->showComOrderSizewithConvert($orderId);
            $data = [$showUnComOrderSizewithConvert, $showComOrderSizewithConvert];
            return $data;
        } else {
            abort('No Auth', 422);
        }
    }
    public function checkUser($orderId)
    {
        $user = auth('api')->user()->factoryId;
        $order = Order::find($orderId)->factoryId;
        if ($user == $order) {
            return true;
        } else {
            return false;
        }
    }
}
