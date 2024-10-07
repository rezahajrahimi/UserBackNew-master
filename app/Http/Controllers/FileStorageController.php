<?php

namespace App\Http\Controllers;

use App\Models\FileStorage;
use App\Models\Factory;
use Illuminate\Http\Request;

class FileStorageController extends Controller
{
    private function newEvent($type,$details,$itemtype,$sharingLinks) {
        $event = new EventsController();
        $event->newEvent($type,$details,$itemtype,$sharingLinks);
        return;
    }
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
    public function deleteFileById($id)
    {
        $factoryId = auth('api')->user()->factoryId;
        $data = FileStorage::where('factory_id', $factoryId)
            ->where('id', $id)
            ->first();
        $path = public_path() . '/storage/files/' . $data->file_name;
        if (file_exists($path)) {
            unlink($path);
        }
        $this->newEvent("delFile","یک فایل توسط " .auth('api')->user()->name." حذف گردید.","","");

        $data->delete();
        return response()->json($this->showAllFactoryFiles(), 200);
    }
    public function addNewFile(Request $request)
    {
        // if($this->checkUserPremisson('update') == false) {
        //     return response()->json(false, 401);
        // }
        $factoryId = auth('api')->user()->factoryId;

        $file = $request->file('file');
        $filename = $request->name . '-' . time() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('/storage/files/'), $filename);

        $data = new FileStorage();
        $data->factory_id = $factoryId;
        $data->file_name = $filename;
        $data->extension = $file->getClientOriginalExtension();
        $data->tag = '';
        $data->description = $request->description;
        $data->filesize = $request->size . 'MB';
        $data->save();
        $fileCount = FileStorage::where('factory_id', $factoryId)->count();
        $factoryServicetype = Factory::where('id', $factoryId)->first()->servicetype;
            $maxFile = $this->getOptions('fileCount');
        if ($fileCount >  $maxFile ) {
            $firstFile = FileStorage::where('factory_id', $factoryId)
                ->orderBy('id', 'asc')
                ->first();
            $dalData = $this->deleteFileById($firstFile->id);
            // return response()->json($dalData, 200);
        }

        $this->newEvent("addFile","یک فایل توسط " .auth('api')->user()->name." اضافه گردید.","","");

        return response()->json($this->showAllFactoryFiles(), 200);
    }
    public function showAllFactoryFiles()
    {
        $factoryId = auth('api')->user()->factoryId;
        return FileStorage::where('factory_id', $factoryId)
            ->select('file_storages.id', 'file_storages.file_name', 'file_storages.extension', 'file_storages.tag', 'file_storages.description', 'file_storages.filesize', 'file_storages.share_links', 'file_storages.created_at')
            ->orderBy('file_storages.id', 'desc')
            ->get();
    }
}
