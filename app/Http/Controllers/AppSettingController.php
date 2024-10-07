<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use Illuminate\Http\Request;

class AppSettingController extends Controller
{
    public function getAppSetting()
    {
        $appSetting = AppSetting::first();

        return response()->json(
            [
                'pwa_version' => $appSetting->pwa_version,
                'apk_version' => $appSetting->apk_version,
                'exe_version' => $appSetting->exe_version,
                'version_description' => $appSetting->version_description,
                'download_url' => $appSetting->download_url,
            ],
            200,
        );
    }
}
