<?php

namespace App\Http\Controllers;

use App\Models\Favorites;
use App\Models\User;
use App\Models\Events;
use App\Models\Clusters;
use App\Models\ClusterEsell;
use App\Models\Cube;
use App\Models\CubeEsell;
use App\Models\Followers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoritesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function addFavorite(Request $request)
    {
        $check = Favorites::where('userId', auth('api')->user()->id)
            ->where('sharelinkId', $request->sharelinkId)
            ->get();
        if ($check->count() == 0 || $check->count() == null) {
            $data = new Favorites();
            $data->userId = auth('api')->user()->id;
            $data->sharelinkId = $request->sharelinkId;
            $data->type = $request->type;
            $data->save();
            return response()->json('add', 200);
        } else {
            $data = $check->first();
            $data->delete();
            return response()->json('del', 200);
        }
    }
    public function addAndRemoveFave($sharelinkId, $type)
    {
        $check = Favorites::where('userId', auth('api')->user()->id)
            ->where('sharelinkId', $sharelinkId)
            ->get();
        if ($check->count() == 0 || $check->count() == null) {
            $data = new Favorites();
            $data->userId = auth('api')->user()->id;
            $data->sharelinkId = $sharelinkId;
            $data->type = $type;
            $data->save();
            $event = new EventsController();
            $name = auth('api')->user()->name;
            $evenrData = new Request();
            $evenrData->type = 'favorite';
            $evenrData->user_id = auth('api')->user()->id;
            $evenrData->factory_id = 27;
            $evenrData->sharingLinks = $sharelinkId;
            $evenrData->item_type = $type;
            if ($type == 'cluster') {
                $clu = Clusters::where('sharingLinks', $sharelinkId)->first();

                $claEsells=ClusterEsell::firstOrCreate(['cluster_id' => $clu->id])->increment('statistics');
                $evenrData->details = $name . ' دسته ' . $clu->clusterNumber . '-' . $clu->clusterNameStone . ' را پسندید';

            } else {
                $cu = Cube::where('sharingLiks', $sharelinkId)->first();
                $data=CubeEsell::firstOrCreate(['cube_id' => $cu->id])->increment('statistics');

                $evenrData->details = $name . ' کوپ ' . $cu->cubeNumber . '-' . $cu->nameCube . ' را پسندید';

            }

            $event->addNewEvent($evenrData);
            return response()->json('add', 200);
        } else {
            $event = new EventsController();
            if ($type == 'cluster') {
                $clu = Clusters::where('sharingLinks', $sharelinkId)->first();

                $claEsells=ClusterEsell::firstOrCreate(['cluster_id' => $clu->id])->decrement('statistics');

             } else {
                $cu = Cube::where('sharingLiks', $sharelinkId)->first();
                $data=CubeEsell::firstOrCreate(['cube_id' => $cu->id])->decrement('statistics');

             }
            $event->deleteEvent($sharelinkId, $type);
            // decrease cluster fave count

            $data = $check->first();
            $data->delete();

            return response()->json('del', 200);
        }
    }

    public function CheckFavorite($id)
    {
        if (
            Favorites::where('userId', auth('api')->user()->id)
                ->where('sharelinkId', $id)
                ->count() == 0
        ) {
            return response()->json('false', 200);
        } else {
            return response()->json('true', 200);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Favorites  $favorites
     * @return \Illuminate\Http\Response
     */
    public function delFavorite($id)
    {
        if (auth('api')->user()->id) {
            $data = Favorites::where('userId', auth('api')->user()->id)
                ->where('sharelinkId', $id)
                ->first();
            return $data->delete();
        } else {
            return false;
        }
    }
    public function getUserFavList()
    {
        if (auth('api')->user()->id) {
            $dataCluster = Favorites::where('userId', auth('api')->user()->id)
                ->where('type', 'cluster')
                ->leftjoin('clusters', 'clusters.sharingLinks', '=', 'favorites.sharelinkId')
                ->leftjoin('esells_settings', 'esells_settings.factory_id', '=', 'clusters.factoryId')

                ->leftjoin('factories', 'factories.id', '=', 'clusters.factoryId')
                ->leftjoin('cluster_esells', 'cluster_esells.cluster_id', '=', 'clusters.id')

                ->where('clusters.show_in_esells', '=', 'yes')
                ->where('esells_settings.show_clusters', true)
                ->where('clusters.hasImage', '=', 'yes')
                ->select('clusters.id as clId', 'clusters.existence', 'clusters.count', 'clusters.imageThumb as imageThumb', 'clusters.created_at', 'clusters.ClusterTypeStones', 'clusters.clusterNumber', 'clusters.clusterNameStone', 'clusters.sharingLinks as clSharingLinks', 'clusters.created_at', 'factories.nameFac', 'factories.logoFac', 'factories.id as fId', 'factories.servicetype as actype', 'esells_settings.whatsapp_number as whatsapp')
                ->orderBy('favorites.id', 'desc')

                ->get()->take(20);
            $dataCube = Favorites::where('userId', auth('api')->user()->id)
            ->where('type', 'cube')

                ->leftjoin('cubes', 'cubes.sharingLiks', '=', 'favorites.sharelinkId')
                ->leftjoin('esells_settings', 'esells_settings.factory_id', '=', 'cubes.factoryId')
                ->leftjoin('factories', 'factories.id', '=', 'cubes.factoryId')
                ->leftjoin('cube_esells', 'cube_esells.cube_id', '=', 'cubes.id')
                ->where('cubes.show_in_esells', '=', 'yes')
                ->where('cubes.isActive', '=', 'yes')
                ->where('esells_settings.show_cubes', true)
                ->where('cubes.hasImage', '=', 'yes')
                ->select('cubes.id as cuId', 'cubes.imageThumb as imageThumb', 'cubes.cubeNumber', 'cubes.nameCube', 'cubes.weight', 'cubes.length', 'cubes.width', 'cubes.height', 'cubes.created_at', 'cubes.sharingLiks as cuSharingLinks', 'factories.nameFac', 'factories.logoFac', 'factories.id as fId', 'factories.servicetype as actype', 'esells_settings.whatsapp_number as whatsapp')
                ->orderBy('favorites.id', 'desc')

                ->get()->take(20);
                $foController = new FollowersController();

                $dataFollowingsCount = $foController->getCurrentUserFollowingCount();
                $data = array('cubes' => $dataCube,'clusters'=>$dataCluster,'following_count'=>$dataFollowingsCount);

            return $data;
        } else {
            return false;
        }
    }
}
