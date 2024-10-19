<?php

namespace App\Http\Controllers;

use App\Models\SplittedCube;
use Illuminate\Http\Request;

class SplittedCubeController extends Controller
{
    public function create_SplittedCube(Request $request)
    {
        \Log::info('message', [$request->all()]);

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
    public function add_cutting_SplittedCube(Request $request)
    {
        $splittedCube = SplittedCube::find($request->splitted_id);
        $factory_id = auth('api')->user()->factoryId;
        if ($splittedCube->factory_id == $factory_id) {
            $this->change_Status_SplittedCube($request->splitted_id);

            $splittedCube->cutted_at = $request->cutted_at;
            $splittedCube->saw_id = $request->saw_id;
            $splittedCube->update();
            return response()->json($splittedCube, 201);
        }
        return response()->json('Go Fuck Your Self!', 401);
    }
    public function remove_cutting_SplittedCube($splitted_id)
    {
        $splittedCube = SplittedCube::find($splitted_id);
        $factory_id = auth('api')->user()->factoryId;
        if ($splittedCube->factory_id == $factory_id) {
            $splittedCube->cutted_at = null;
            $splittedCube->saw_id = null;
            $this->change_Status_SplittedCube($splitted_id);

            $splittedCube->update();
            return response()->json($splittedCube, 201);
        }
        return response()->json('Go Fuck Your Self!', 401);
    }
    public function change_Status_SplittedCube($splitted_id)
    {
        $splittedCube = SplittedCube::find($splitted_id);
        $factory_id = auth('api')->user()->factoryId;
        if ($splittedCube->factory_id == $factory_id) {
            $splittedCube->is_active = $splittedCube->is_active == true ? false : true;
            $splittedCube->update();
            return response()->json($splittedCube, 200);
        }
        // check if all the cubes are splitted turn main cube to deActive (cutted cude)
        $cube = Cube::find($splittedCube->cube_id);

        $status = SplittedCube::where('cube_id', $splittedCube->cube_id)
            ->where('is_active', 'yes')
            ->count();
        if ($status == 0) {
            $cube->isActive = 'No';
            // assign cuttint_at last splitted cube to main cube

            // $cube->cutted_at = $splittedCube->cutted_at;
            $cube->update();
            return response()->json($cube, 200);
        }
        $cube->isActive = 'yes';
        $cube->update();

        return response()->json('Go Fuck Your Self!', 401);
    }
}
