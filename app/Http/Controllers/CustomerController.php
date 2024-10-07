<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\order;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function getAllCustomer()
    {
        $factoryId = auth('api')->user()->factoryId;

        return Customer::where('factory_id', $factoryId)->get();
    }
    public function getCustomerOrCreate($name)
    {
        $factoryId = auth('api')->user()->factoryId;

        return Customer::firstOrCreate(['name' => $name, 'factory_id' => $factoryId], ['telephone' => '', 'address' => '']);
    }
    public function addNewCustomer(Request $request)
    {
        $factoryId = auth('api')->user()->factoryId;
        if ($request->name) {
            $data = Customer::firstOrCreate(['name' => $request->name, 'factory_id' => $factoryId], ['telephone' => $request->telephone, 'address' => $request->address]);

            return $this->getAllCustomer();
        }
        return response()->json(false, 201);
    }
    public function getCustomerIdOrCreate($name)
    {
        return $this->getCustomerOrCreate($name)->id;
    }
    public function deleteCustomer($name)
    {
        $factoryId = auth('api')->user()->factoryId;

        $customer = Customer::where('name', $name)
            ->where('factory_id', $factoryId)
            ->first();
        if ($customer != null) {
            $orderCount = Order::where('customer_id', $customer->id)
                ->where('factoryId', $factoryId)
                ->count();
            if ($orderCount < 1 || $orderCount == null) {
                $customer->delete();
                return $this->getAllCustomer();
            } else {
                return response()->json('order exist', 202);
            }
        }
        return response()->json(false, 201);
    }
    public function updateCustomer(Request $request)
    {
        $factoryId = auth('api')->user()->factoryId;

        $customer = Customer::where('name', $request->name)
            ->where('factory_id', $factoryId)
            ->first();
        if ($customer != null) {
            if ($request->newName != null) {
                $customer->name = $request->newName;
            }
            $customer->telephone = $request->telephone;
            $customer->address = $request->address;

            $customer->save();
            return $this->getAllCustomer();
        }
        return $this->getAllCustomer();
    }
}
