<?php

namespace App\Http\Controllers;

use App\Models\MessageGroup;
use App\Models\MessageGroupMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageGroupController extends Controller
{
    public function getFactoryGroupName() {
        $data = MessageGroup::where('factory_id',auth('api')->user()->factoryId)->get();
        if($data) {
            return $data;
        } else {
            abort(404,'No Data');
        }
    }
    public function createNewFactoryGroup (Request $request) {
        $data = new MessageGroup();
        $data->factory_id = auth('api')->user()->factoryId;
        $data->group_name = $request->group_name;
        if( $data->save()) {
            return $this->getFactoryGroupName();
        } else {
            abort(404,'Error On group creation');
        }
    }
    public function delFactoryGroupById ($id) {
        $data = MessageGroup::where('id',$id)->where('factory_id',auth('api')->user()->factoryId);
        if( $data->delete()) {
            $member = MessageGroupMember::where('group_id',$id);
            $member->delete();
            return $this->getFactoryGroupName();
        } else {
            abort(404,'Error On group creation');
        }
    }
}
