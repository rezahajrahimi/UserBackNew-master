<?php

namespace App\Http\Controllers;

use App\Models\FactoryOptions;
use Illuminate\Http\Request;

class FactoryOptionsController extends Controller
{
    public function getFactoryMaxUploadSize()
    {
        $factoryId = auth('api')->user()->factoryId;
        $data = FactoryOptions::where('factory_id', $factoryId)->first();
        if ($data != null) {
            return response()->json($data->max_upload_size, 200);
        } else {
            $data = new FactoryOptions;
            $data->max_upload_size = 5242880;
            $data->factory_id = $factoryId;
            $data->save();
            return response()->json($data->max_upload_size, 200);
        }
    }
    public function getFactoryMaxFileUpload()
    {
        $factoryId = auth('api')->user()->factoryId;
        $data = FactoryOptions::where('factory_id', $factoryId)->first();
        if ($data != null) {
            return $data->max_file_uploaded;
        } else {
            $data = new FactoryOptions;
            $data->max_file_uploaded = 10;
            $data->factory_id = $factoryId;
            $data->save();
            return response()->json($data->max_file_uploaded,200);
        }
    }
    public function getFactoryMaxEventSaved()
    {
        $factoryId = auth('api')->user()->factoryId;
        $data = FactoryOptions::where('factory_id', $factoryId)->first();
        if ($data != null) {
            return $data->max_event_saved;
        } else {
            $data = new FactoryOptions;
            $data->max_event_saved = 10;
            $data->factory_id = $factoryId;
            $data->save();
            return response()->json($data->max_event_saved);
        }
    }
}
