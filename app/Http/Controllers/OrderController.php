<?php

namespace App\Http\Controllers;

use App\Models\Clusters;
use App\Models\Clustersize;
use App\Models\Order;
use App\Models\OrderSize;
use App\Models\User;
use App\Models\Customer;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Verta;
use Carbon\Carbon;

class OrderController extends Controller
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
    public function getLastOrder()
    {
        $factoryId = auth('api')->user()->factoryId;
        $order = Order::where('factoryId', $factoryId)
            ->where('status', 0)
            ->orderBy('created_at', 'desc')
            ->first();
        return response()->json($order);
    }
    public function getLastOrders($count)
    {
        $factoryId = auth('api')->user()->factoryId;
        $order = Order::where('factoryId', $factoryId)
            ->with('customer')
            ->orderBy('id', 'desc')
            ->take($count)
            ->get();
        return response()->json($order);
    }
    public function getCountOfOrder()
    {
        $factoryId = auth('api')->user()->factoryId;
        $count = Order::where('factoryId', $factoryId)
            ->where('status', 0)
            ->count();
        return response()->json($count);
    }
    public function getCustomerNames()
    {
        $factoryId = auth('api')->user()->factoryId;
        $customer = Customer::where('factory_id', $factoryId)
            ->select('name')
            ->groupBy('name')
            ->orderBy('name', 'desc')
            ->get();
        return response()->json($customer);
    }
    public function getLastOrderNumber()
    {
        $factoryId = auth('api')->user()->factoryId;

        $order = Order::where('factoryId', $factoryId)
            ->orderBy('created_at', 'desc')
            ->first();
        if ($order) {
            return $order->orderNumber;
        } else {
            return 1;
        }
    }
    public function checkDuplicateOrderNumber($orderNumber)
    {
        $factoryId = auth('api')->user()->factoryId;

        $order = Order::where('factoryId', $factoryId)
            ->where('orderNumber', $orderNumber)
            ->first();
        if ($order) {
            return true;
        } else {
            return false;
        }
    }
    public function addOrder(Request $request)
    {
        if ($this->checkUserPremisson('add') == false) {
            return response()->json(false, 401);
        }
        $factoryId = auth('api')->user()->factoryId;
        $user_name = auth('api')->user()->name;

        $data = new Order();
        $data->factoryId = $factoryId;
        $data->user_name = $user_name;
        $customercontroller = new CustomerController();

        $data->customer_id = $customercontroller->getCustomerIdOrCreate($request->customerName);
        if ($request->orderNumber != null) {
            if ($this->checkDuplicateOrderNumber($request->orderNumber) != true) {
                $data->orderNumber = $request->orderNumber;
            } else {
                return response()->json('duplicate order number', 201);
            }
        } else {
            $data->orderNumber = $this->getLastOrderNumber() + 1;
        }

        $data->status = 0;
        $data->createddatein = $this->getMiladyDate($request->createddatein);
        $data->existence = 0;
        $data->save();
        $this->newEvent('addOrd', 'خروجی ' . $data->customerName . ' توسط ' . auth('api')->user()->name . ' اضافه گردید.', '', '');

        return $data->id;
    }
    public function showOrderAndOrderSizeById($id)
    {
        $dataOrder = $this->showOrderById($id);
        $orderSize = new OrderSizeController();
        $dataUnOrderSize = $orderSize->showUncOrderSizeById($id);
        $dataComOrderSize = $orderSize->showComOrderSizeById($id);
        $data = [$dataOrder, $dataUnOrderSize, $dataComOrderSize];
        return $data;
    }
    public function showOrderById($id)
    {
        $factoryId = auth('api')->user()->factoryId;

        return Order::where('id', $id)
            ->where('factoryId', $factoryId)
            ->with('customer', 'order_loading')
            ->first();
        // return Order::findOrFail($id)->with('customer');
    }
    public function showAllUnCompleteOrder()
    {
        if ($this->checkUserPremisson('view') == false) {
            return response()->json(false, 401);
        }
        $factoryId = auth('api')->user()->factoryId;
        return Order::where('factoryId', $factoryId)
            ->with('customer')
            ->where('status', 0)
            ->orderBy('created_at', 'desc')
            ->get();
    }
    public function showAllCompleteOrder()
    {
        if ($this->checkUserPremisson('view') == false) {
            return response()->json(false, 401);
        }
        $factoryId = auth('api')->user()->factoryId;
        return Order::where('factoryId', $factoryId)
            ->with('customer')

            ->where('status', 1)
            ->orderBy('created_at', 'desc')
            ->get();
    }
    public function searchUnCompleteOrder(Request $request)
    {
        if ($this->checkUserPremisson('view') == false) {
            return response()->json(false, 401);
        }
        $customerName = $request->customerName;
        $firstDate = $this->getMiladyDate($request->firstDate);
        $lastdate = $this->getMiladyDate($request->lastdate);
        $factoryId = auth('api')->user()->factoryId;
        if ($firstDate != null) {
            $data = Order::where('factoryId', $factoryId)
                ->where('status', 0)
                ->orderBy('created_at', 'desc')
                ->whereBetween('createddatein', [$firstDate, $lastdate])
                ->when($customerName, function ($q) use ($customerName) {
                    return $q->where('customerName', 'like', '%' . $customerName . '%');
                })
                ->get();
        } else {
            $data = Order::where('factoryId', $factoryId)
                ->where('status', 0)
                ->orderBy('created_at', 'desc')
                ->when($customerName, function ($q) use ($customerName) {
                    return $q->where('customerName', 'like', '%' . $customerName . '%');
                })
                ->get();
        }
        return response()->json([$data]);
    }
    public function searchCompleteOrder(Request $request)
    {
        if ($this->checkUserPremisson('view') == false) {
            return response()->json(false, 401);
        }
        $customerName = $request->customerName;
        $firstDate = $this->getMiladyDate($request->firstDate);
        $lastdate = $this->getMiladyDate($request->lastdate);
        $factoryId = auth('api')->user()->factoryId;
        if ($firstDate != null) {
            $data = Order::where('factoryId', $factoryId)
                ->where('status', 1)
                ->orderBy('created_at', 'desc')
                ->whereBetween('createddatein', [$firstDate, $lastdate])
                ->when($customerName, function ($q) use ($customerName) {
                    return $q->where('customerName', 'like', '%' . $customerName . '%');
                })
                ->get();
        } else {
            $data = Order::where('factoryId', $factoryId)
                ->where('status', 1)
                ->orderBy('created_at', 'desc')
                ->when($customerName, function ($q) use ($customerName) {
                    return $q->where('customerName', 'like', '%' . $customerName . '%');
                })
                ->get();
        }
        return response()->json([$data]);
    }
    public function deleteCompOrder($id)
    {
        if ($this->checkUserPremisson('del') == false) {
            return response()->json(false, 401);
        }
        $factoryId = auth('api')->user()->factoryId;
        $data = Order::where('factoryId', $factoryId)
            ->where('status', 1)
            ->where('id', $id);
        if ($data) {
            $this->newEvent('delOrd', 'خروجی ' . $data->customerName . ' توسط ' . auth('api')->user()->name . ' حذف گردید.', '', '');
            $orLoadingCntrl = new OrderLoadingController();
            $orLoadingCntrl->deleteOrderLoadingByOrderId($data->id);
            $data->delete();
            $orderSize = OrderSize::where('orderId', $id);
            return response()->json($orderSize->delete());
        } else {
            return false;
        }
    }
    public function deleteUnCompOrder($id)
    {
        if ($this->checkUserPremisson('del') == false) {
            return response()->json(false, 401);
        }
        $factoryId = auth('api')->user()->factoryId;

        $data = Order::where('id', $id)
            ->where('factoryId', $factoryId)
            ->first();
        $orLoadingCntrl = new OrderLoadingController();
        $orLoadingCntrl->deleteOrderLoadingByOrderId($data->id);

        $ordSizeCntr = new OrderSizeController();
        if ($data->existence == 0) {
            return response()->json($data->delete());
        } elseif ($data->existence != 0) {
            $orderSize = OrderSize::where('orderId', $id)->get();

            foreach ($orderSize as $orSize) {
                $ordSizeCntr->deleteOrderSize($orSize->id);
            }

            $data = Order::where('id', $id)
                ->where('factoryId', $factoryId)
                ->first();
            $this->newEvent('delOrd', 'خروجی ' . $data->customerName . ' توسط ' . auth('api')->user()->name . ' حذف گردید.', '', '');

            return response()->json($data->delete());
        } else {
            return false;
        }
    }
    public function orderReports(Request $request)
    {
        if ($this->checkUserPremisson('view') == false) {
            return response()->json(false, 401);
        }
        $customerid = null;
        if ($request->customerName) {
            $customer = new CustomerController();
            $customerid = $customer->getCustomerIdOrCreate($request->customerName);
        }
        $lastdate = $this->getMiladyDate($request->dateLast);
        $firstDate = $this->getMiladyDate($request->dateFirst);
        $status = $request->status;
        $clusterNameStone = $request->clusterNameStone;
        $lengthMin = $request->lengthMin;
        $lengthMax = $request->lengthMax;
        $widthMin = $request->widthMin;
        $widthMax = $request->widthMax;
        $existenceMin = $request->existenceMin;
        $existenceMax = $request->existenceMax;
        $factoryId = auth('api')->user()->factoryId;
        if ($lengthMin != null || $widthMin != null || $clusterNameStone != null) {
            if ($status == 1 || $status == null) {
                $data = Order::where('factoryId', $factoryId)
                    ->leftjoin('order_sizes', 'order_sizes.orderId', '=', 'orders.id')
                    ->when($clusterNameStone, function ($q) use ($clusterNameStone) {
                        return $q->where('order_sizes.clusterNameStone', $clusterNameStone);
                    })
                    ->when($existenceMin, function ($q) use ($existenceMin, $existenceMax) {
                        return $q->whereBetween('order_sizes.sum', [$existenceMin, $existenceMax]);
                    })
                    ->when($lengthMin, function ($q) use ($lengthMin, $lengthMax) {
                        return $q->whereBetween('order_sizes.length', [$lengthMin, $lengthMax]);
                    })
                    ->when($widthMin, function ($q) use ($widthMin, $widthMax) {
                        return $q->whereBetween('order_sizes.width', [$widthMin, $widthMax]);
                    })
                    ->when($customerid, function ($q) use ($customerid) {
                        return $q->where('orders.customer_id', $customerid);
                    })
                    ->when($firstDate, function ($q) use ($firstDate, $lastdate) {
                        return $q->whereBetween('orders.createddatein', [$firstDate, $lastdate]);
                    })
                    ->when($status, function ($q) use ($status) {
                        return $q->where('orders.status', 1);
                    })
                    ->with('customer')
                    ->get();
            } else {
                $data = Order::where('factoryId', $factoryId)
                    ->where('orders.status', 0)
                    ->leftjoin('order_sizes', 'order_sizes.orderId', '=', 'orders.id')
                    ->when($clusterNameStone, function ($q) use ($clusterNameStone) {
                        return $q->where('order_sizes.clusterNameStone', $clusterNameStone);
                    })
                    ->when($existenceMin, function ($q) use ($existenceMin, $existenceMax) {
                        return $q->whereBetween('order_sizes.sum', [$existenceMin, $existenceMax]);
                    })
                    ->when($lengthMin, function ($q) use ($lengthMin, $lengthMax) {
                        return $q->whereBetween('order_sizes.length', [$lengthMin, $lengthMax]);
                    })
                    ->when($widthMin, function ($q) use ($widthMin, $widthMax) {
                        return $q->whereBetween('clustersizes.width', [$widthMin, $widthMax]);
                    })
                    ->when($customerid, function ($q) use ($customerid) {
                        return $q->where('orders.customer_id', $customerid);
                    })
                    ->when($firstDate, function ($q) use ($firstDate, $lastdate) {
                        return $q->whereBetween('orders.createddatein', [$firstDate, $lastdate]);
                    })
                    ->when($status, function ($q) use ($status) {
                        return $q->where('orders.status', 1);
                    })
                    ->with('customer')
                    ->get();
            }
        } else {
            if ($status == 1 || $status == null) {
                $data = Order::where('factoryId', $factoryId)
                    // ->leftjoin('order_sizes', 'order_sizes.orderId', '=', 'orders.id')
                    // ->leftjoin('customers', 'customers.id', '=', 'orders.customer_id')

                    ->when($status, function ($q) use ($status) {
                        return $q->where('status', 1);
                    })
                    ->when($customerid, function ($q) use ($customerid) {
                        return $q->where('orders.customer_id', $customerid);
                    })
                    ->when($existenceMin, function ($q) use ($existenceMin, $existenceMax) {
                        return $q->whereBetween('existence', [$existenceMin, $existenceMax]);
                    })
                    ->when($firstDate, function ($q) use ($firstDate, $lastdate) {
                        return $q->whereBetween('createddatein', [$firstDate, $lastdate]);
                    })
                    ->with('customer','order_size_names')
                    ->get();
            } else {
                $data = Order::where('factoryId', $factoryId)

                    ->where('status', 0)
                    ->when($customerid, function ($q) use ($customerid) {
                        return $q->where('orders.customer_id', $customerid);
                    })
                    ->when($existenceMin, function ($q) use ($existenceMin, $existenceMax) {
                        return $q->whereBetween('existence', [$existenceMin, $existenceMax]);
                    })
                    ->when($firstDate, function ($q) use ($firstDate, $lastdate) {
                        return $q->whereBetween('createddatein', [$firstDate, $lastdate]);
                    })
                    ->with('customer','order_size_names')

                    ->get();
            }
        }
        return response()->json([$data]);
    }
    public function updateOrder(Request $request)
    {
        if ($this->checkUserPremisson('update') == false) {
            return response()->json(false, 401);
        }
        if (auth('api')->user()) {
            $orderid = $request->orderId;
            $data = Order::findOrFail($orderid);
            $customercontroller = new CustomerController();

            $data->customer_id = $customercontroller->getCustomerIdOrCreate($request->customerName);
            if ($request->orderNumber != null && $request->orderNumber != $data->orderNumber) {
                if ($this->checkDuplicateOrderNumber($request->orderNumber) != true) {
                    $data->orderNumber = $request->orderNumber;
                } else {
                    return response()->json('duplicate order number', 201);
                }
            }
            // $data->customerName = $request->customerName;
            $data->createddatein = $this->getMiladyDate($request->createddatein);
            $this->newEvent('editOrd', 'خروجی ' . $data->customerName . ' توسط ' . auth('api')->user()->name . ' ویرایش گردید.', '', '');

            return response()->json([$data->update()]);
        }
    }
    public function getAllOrderAnalyticsData()
    {
        $getbestSelClusterLastMOnth = $this->getbestSelClusterLastMOnth();
        $getBestCustomerLastMOnth = $this->getBestCustomerLastMOnth();
        $data = [$getbestSelClusterLastMOnth, $getBestCustomerLastMOnth];
        return $data;
    }
    public function getBestCustomerLastMOnth()
    {
        $v = Carbon::now();
        $lastMonth = $v->subMonth();
        $factoryId = auth('api')->user()->factoryId;
        $data = DB::table('orders')
            ->where('factoryId', $factoryId)
            ->where('createddatein', '>=', $lastMonth)
            ->select('customerName', DB::raw('count(*) as total'), DB::raw('sum(existence) as existence'))
            ->groupBy('customerName')
            ->orderBy('total', 'desc')
            ->take(10)
            ->get();
        return $data;
    }
    public function getbestSelClusterLastMOnth()
    {
        $v = Carbon::now();
        $lastMonth = $v->subMonth();
        $factoryId = auth('api')->user()->factoryId;
        $data = Order::where('factoryId', $factoryId)
            ->leftjoin('order_sizes', 'order_sizes.orderId', '=', 'orders.id')
            ->where('factoryId', $factoryId)
            ->where('order_sizes.created_at', '>=', $lastMonth)
            ->select('order_sizes.clusterNameStone', DB::raw('count(*) as total'), DB::raw('sum(order_sizes.sum) as existenceCl'))
            ->groupBy('order_sizes.clusterNameStone')
            ->orderBy('total', 'desc')
            ->take(10)
            ->get();
        return $data;
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
    public function getOrderGroupedReportFilterOption()
    {
        $factoryId = auth('api')->user()->factoryId;
        $customerName = Customer::where('factory_id', $factoryId)
            ->select('name')
            ->groupBy('name')
            ->orderBy('name', 'desc')
            ->get();
        $claster = DB::table('clusters')
            ->where('factoryId', $factoryId)
            ->select('clusterNameStone')
            ->groupBy('clusterNameStone')
            ->get();
        $orderData = [$customerName, $claster];
        return $orderData;
    }
    public function getFactoryIdByOrderId($orderId)
    {
        $data = Order::find($orderId);
        if ($data != null) {
            return $data->factoryId;
        }
    }
    public function modifyOrderCustomer()
    {
        $order = Order::all();
        $customercontroller = new CustomerController();

        foreach ($order as $orderFix) {
            $cus = Customer::firstOrCreate(['name' => $orderFix->customerName, 'factory_id' => $orderFix->factoryId], ['telephone' => '', 'address' => '']);
            $orderFix->customer_id = $cus->id;
            $orderFix->update();
        }
        return true;
    }
}
