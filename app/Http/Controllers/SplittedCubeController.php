<?php

namespace App\Http\Controllers;

use App\Models\SplittedCube;
use Illuminate\Http\Request;

class SplittedCubeController extends Controller
{
    public function create_SplittedCube(Request $request)
    {
        \Log::info("message", [$request->all()]);

        $request->validate([
            'cube_id' => 'required',
            'weight' => 'required',
            'height' => 'required',
            'length' => 'required',
            'width' => 'required',
            'splitted_at' => 'required',
        ]);
        $factory_id = auth('api')->user()->factoryId;
        $request['factory_id'] = $factory_id;
        $splittedCube = SplittedCube::create($request->all());
        return response()->json($splittedCube, 201);
    }
    public function update_SplittedCube(Request $request, SplittedCube $splittedCube)
    {
        $request->validate([
            'cube_id' => 'required',
            'weight' => 'required',
            'height' => 'required',
            'length' => 'required',
            'width' => 'required',
            'splitted_at' => 'required',
        ]);
        $factory_id = auth('api')->user()->factoryId;
        $request['factory_id'] = $factory_id;
        if ($splittedCube->factory_id == $factory_id) {
            $splittedCube->update($request->all());
            return response()->json($splittedCube, 200);
        }
        return response()->json('Go Fuck Your Self!', 401);
    }
    public function delete_SplittedCube($splitted_id)
    {
        $splittedCube = SplittedCube::find($splitted_id);
        $factory_id = auth('api')->user()->factoryId;
        if ($splittedCube->factory_id == $factory_id) {
            $splittedCube->delete();
            return response()->json(null, 204);
        }
        return response()->json('Go Fuck Your Self!', 401);
    }
    public function get_SplittedCube_by_cube_id($cube_id)
    {
        $factory_id = auth('api')->user()->factoryId;

        $splittedCube = SplittedCube::where('cube_id', $cube_id)->where('factory_id', $factory_id)->get();
        return response()->json($splittedCube, 200);
    }
}
