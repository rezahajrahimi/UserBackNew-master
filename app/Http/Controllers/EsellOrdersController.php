<?php

namespace App\Http\Controllers;

use App\Models\EsellOrders;
use App\Models\Factory;
use Illuminate\Http\Request;

class EsellOrdersController extends Controller
{
    public function showOrderEsellsList($page)
    {
        $factoryId = auth('api')->user()->factoryId;
        return EsellOrders::where('factory_id', $factoryId)
            ->leftjoin('users', 'users.id', '=', 'esell_orders.customer_id')->orderBy('esell_orders.created_at', 'asc')
            ->select(
                'esell_orders.*',
                'users.name'
            )
            ->paginate(50, ['*'], 'page', $page);
    }
    public function showOrderEsellById($id)
    {
        $data = EsellOrders::where('esell_orders.id', $id)->leftjoin('users', 'users.id', '=', 'esell_orders.customer_id')
            ->leftjoin('user_details', 'user_details.user_id', '=', 'esell_orders.customer_id')
            ->select(
                'esell_orders.*',
                'user_details.order_tel',
                'user_details.order_state',
                'user_details.order_city',
                'user_details.order_address',
                'users.name',
                'users.profilepic',
            )->first();
        if ($this->checkUser($data->id)) {
            return $data;
        } else {
            return response()->json(false, 401);
        }
    }
    public function changeSelectedOrderStatus(Request $request)
    {
        $data = EsellOrders::findOrFail($request->id);
        if ($this->checkUser($data->id)) {
            $data->status = $request->status;
            return $data->update();
        } else {
            return response()->json(false, 401);
        }
    }
    public function checkUser($order_id)
    {
        $userfactoryId = auth('api')->user()->factoryId;
        $data = EsellOrders::findorfail($order_id);
        if ($data->factory_id == $userfactoryId) {
            return true;
        } else {
            return false;
        }
    }
    public function createNewEsellsOrder(Request $request)
    {
        $order = new EsellOrders();
        $order->invoice_id = $this->generateRandomString();
        $order->customer_id = auth('api')->user()->id;

        $order->factory_id = 27;
        $order->status = 0;
        $order->shipmentId = $request->shipmentId;
        $order->total = 145000;
        if ($order->save()) {
            return $order->invoice_id;
        } else return false;
    }
    public function generateRandomString($length = 10)
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}
