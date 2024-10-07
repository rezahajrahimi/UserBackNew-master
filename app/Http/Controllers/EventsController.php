<?php

namespace App\Http\Controllers;
use Illuminate\Database\Migrations\Migration;

use App\Models\Events;
use Illuminate\Http\Request;

class EventsController extends Controller
{
    private function getOptions($type) {
        $event = new FactoryOptionsController();
        // $event->newEvent($type,$details,$itemtype,$sharingLinks);
        if($type == 'eventCount') {
            return $event->getFactoryMaxEventSaved();
        }
        if($type == 'fileCount') {
            return $event->getFactoryMaxFileUpload();
        }
        if($type == 'fileSize') {
            return $event->getFactoryMaxUploadSize();
        }
        return;
    }
    public function addNewEvent(Request $request)
    {
        $data = new Events();
        $data->type = $request->type;
        $data->user_id = auth('api')->user()->id;
        $data->factory_id = $request->factory_id;
        $data->sharingLinks = $request->sharingLinks;
        $data->item_type = $request->item_type;
        $data->status = true;
        $data->details = $request->details;
        if ($data->save()) {
            return true;
        } else {
            return false;
        }
    }
    public function newEvent($type,$details,$itemtype,$sharingLinks)
    {

        $data = new Events();
        $data->type = $type;
        $data->user_id = auth('api')->user()->id;
        $data->factory_id = auth('api')->user()->factoryId;
        $data->sharingLinks = $sharingLinks;
        $data->item_type = $itemtype;
        $data->status = true;
        $data->details = $details;
        if ($data->save()) {
            $eventCount = Events::where('factory_id', auth('api')->user()->factoryId)->count();
            $savedEvent = $this->getOptions('eventCount');
            if($eventCount > $savedEvent) {
                $this->deleteFirstFactoryEvent();
            }
            return true;
        } else {
            return false;
        }
    }
    public function deleteEvent($sharelinkId, $type)
    {
        $data = Events::where('sharingLinks', $sharelinkId)
            ->where('item_type', $type)
            ->first();
        // $data = Events::find($request->id);
        if ($data->delete()) {
            return true;
        } else {
            return false;
        }
    }
    public function deleteFirstFactoryEvent() {
            $data = Events::where('factory_id', auth('api')->user()->factoryId)
                ->orderBy('id', 'asc')
                ->first();
            return response()->json($data->delete(), 200);
    }
    public function getFactoryEventsByCount($count) {
            $data = Events::where('factory_id', auth('api')->user()->factoryId)
                ->where('item_type','!=','favorite')
                ->select('id','item_type','details','sharingLinks','status','created_at','type')
                ->orderBy('id', 'desc')
                ->take($count)
                ->get();
            return response()->json($data, 200);
    }
    public function changeEventStatusToRead($id)
    {
        $data = Events::find($id);
        $data->status = false;

        if ($data->save()) {
            return true;
        } else {
            return false;
        }
    }
    public function changeAllEventStatusToRead($requestType)
    {
        if ($requestType == 'user') {
            $data = Events::where('user_id', auth('api')->user()->id);
            $data->status = false;

            if ($data->save()) {
                return true;
            } else {
                return false;
            }
        }
        if ($requestType == 'factory') {
            $data = Events::where('factory_id', auth('api')->user()->factoryId);
            $data->status = false;

            if ($data->save()) {
                return true;
            } else {
                return false;
            }
        }
    }
}
