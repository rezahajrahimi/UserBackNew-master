<?php

namespace App\Http\Controllers;

use App\Models\EsellsOrderMessage;
use Illuminate\Http\Request;

class EsellsOrderMessageController extends Controller
{
    public function showOrderMessagesById($id)
    {
        $data = EsellsOrderMessage::where('esell_order_id',$id)->get();
        if($this->checkUser($data->first()->id)) {
            return $data;
        } else {
            return response()->json(false, 401);
        }

    }
    public function deleteOrderMessagesById($id)
    {
        $data = EsellsOrderMessage::find($id);
        if($this->checkUser($data->first()->id)) {
            return $data->delete();
        } else {
            return response()->json(false, 401);
        }

    }
    public function insertMessageToOrder(Request $request)
    {
        $userfactoryId = auth('api')->user()->factoryId;
        $data = new EsellsOrderMessage;
        $data->esell_order_id= $request->esell_order_id;
        $data->customer_id= $request->customer_id;
        $data->factory_id= $userfactoryId;
        $data->message= $request->message;
        $data->type= $request->type;
        return response()->json($data->save());

    }

    public function checkUser($order_id)
    {
        $userfactoryId = auth('api')->user()->factoryId;
        $data = EsellsOrderMessage::findorfail($order_id);
        if ($data->factory_id == $userfactoryId) {
            return true;
        } else {
            return false;
        }
    }
}
