<?php

namespace App\Http\Controllers;

use App\Models\OrderShipments;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderShipmentsController extends Controller
{
    public function getUserShipments()
    {
        $customer_id = auth('api')->user()->id;
        return OrderShipments::where('customer_id', $customer_id)
            ->leftjoin('iran_states', 'iran_states.id', '=', 'order_shipments.state')
            ->leftjoin('iran_counties', 'iran_counties.id', '=', 'order_shipments.city')
            ->select('order_shipments.*', 'iran_states.name as state', 'iran_counties.name as county')
            ->get();
    }
    public function getUserShipmentById($id)
    {
        $customer_id = auth('api')->user()->id;
        return OrderShipments::where('customer_id', $customer_id)
            ->where('id', $id)
            ->first();
    }
    public function createNewUserShipment(Request $request)
    {
        $customer_id = auth('api')->user()->id;
        $data = new OrderShipments();
        $data->customer_id = $customer_id;
        $data->state = $request->state;
        $data->city = $request->city;
        $data->address = $request->address;
        $data->mobile = $request->mobile;
        $data->phone = $request->phone;
        $data->save();
        return OrderShipments::where('customer_id', $customer_id)
            ->leftjoin('iran_states', 'iran_states.id', '=', 'order_shipments.state')
            ->leftjoin('iran_counties', 'iran_counties.id', '=', 'order_shipments.city')
            ->select('order_shipments.*', 'iran_states.name as state', 'iran_counties.name as county')
            ->get();
    }
    public function updateUserShipment(Request $request)
    {
        $customer_id = auth('api')->user()->id;
        $data =  OrderShipments::find($request->id);
        $data->customer_id = $customer_id;
        $data->state = $request->state;
        $data->city = $request->city;
        $data->address = $request->address;
        $data->mobile = $request->mobile;
        $data->phone = $request->phone;
        $data->update();
        return OrderShipments::where('customer_id', $customer_id)
            ->leftjoin('iran_states', 'iran_states.id', '=', 'order_shipments.state')
            ->leftjoin('iran_counties', 'iran_counties.id', '=', 'order_shipments.city')
            ->select('order_shipments.*', 'iran_states.name as state', 'iran_counties.name as county')
            ->get();
    }
}
