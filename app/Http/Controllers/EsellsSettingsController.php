<?php

namespace App\Http\Controllers;
use App\Models\EsellsSettings;

use Illuminate\Http\Request;

class EsellsSettingsController extends Controller
{
    public function getFactoryEsellsSettings()
    {
        return EsellsSettings::firstOrCreate(['factory_id' => auth('api')->user()->factoryId], ['show_cubes' => false], ['show_clusters' => true], ['isPublic' => true], ['whatsapp_number' => auth('api')->user()->mobile])->where('factory_id', auth('api')->user()->factoryId);
    }
    public function updateFactoryEsellsSettings(Request $request)
    {
        $factoryEsellsSettings = EsellsSettings::where('factory_id', auth('api')->user()->factoryId)->first();

        $factoryEsellsSettings->show_clusters = $request->show_clusters;
        $factoryEsellsSettings->show_cubes = $request->show_cubes;
        $factoryEsellsSettings->isPublic = $request->isPublic;
        $factoryEsellsSettings->whatsapp_number = $request->whatsapp_number;
        return $factoryEsellsSettings->update();
    }
    public function isPublic($factoryId) {
        $data = EsellsSettings::where('factory_id',$factoryId)->first();
        return $data->isPublic;
    }
}
