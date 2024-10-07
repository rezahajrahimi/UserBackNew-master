<?php

namespace App\Http\Controllers;

use App\Models\Followers;
use App\Models\User;
use App\Models\Factory;
use App\Models\Clusters;
use App\Models\Cube;
use DB;
use App\Models\EsellsSettings;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;

class FollowersController extends Controller
{
    //Panel
    public function getFactoryFollowers()
    {
        return Followers::where('factory_id', auth('api')->user()->factoryId)
            ->leftjoin('users', 'users.id', '=', 'followers.user_id')
            ->select('users.name', 'users.id', 'users.profilepic', 'followers.created_at')
            ->get();
    }

    //Main
    public function userFollowing(Request $request)
    {
        $fac = Factory::where('nameFac', $request->nameFac)->first();
        $user = User::find(auth('api')->user()->id);

        $check = Followers::where('user_id', $user->id)
            ->where('factory_id', $fac->id)
            ->get();
        if ($check->count() == 0 || $check->count() == null) {
            $data = new Followers();
            $data->user_id = $user->id;
            $data->factory_id = $fac->id;
            $data->save();
            $fac->increment('followers_count', 1);
            $fac->save();
            $user->increment('following_count', 1);
            $user->save();
            if ($request->profile) {
                return $this->getUserFollowList();
            } else {
                return response()->json('add', 200);
            }
        } else {
            $data = $check->first();
            $data->delete();
            $fac->decrement('followers_count', 1);
            $fac->save();
            $user->decrement('following_count', 1);
            $user->save();
            if ($request->profile) {
                return $this->getUserFollowList();
            } else {
                return response()->json('del', 200);
            }
        }
    }
    public function getFollowChecking($nameFac)
    {
        $fac = Factory::where('nameFac', $nameFac)->first();
        if (
            Followers::where('user_id', auth('api')->user()->id)
                ->where('factory_id', $fac->id)
                ->count() == 0
        ) {
            return response()->json('false', 200);
        } else {
            return response()->json('true', 200);
        }
    }
    public function getFollowCheckingByFactoryId($factory_id)
    {
        if (
            Followers::where('user_id', auth('api')->user()->id)
                ->where('factory_id', $factory_id)
                ->count() == 0
        ) {
            return false;
        } else {
            return true;
        }
    }
    public function getUserFollowList()
    {
        if (auth('api')->user()->id) {
            $data = Followers::where('user_id', auth('api')->user()->id)
                ->where('status', 1)
                ->leftjoin('factories', 'factories.id', '=', 'followers.factory_id')
                ->leftjoin('esells_settings', 'esells_settings.factory_id', '=', 'followers.factory_id')
                ->select('factories.nameFac', 'factories.state', 'factories.logoFac', 'followers.factory_id', 'followers.id', 'factories.description', 'factories.telephoneFac', 'factories.followers_count', 'factories.servicetype', 'factories.website', 'esells_settings.show_cubes', 'esells_settings.show_clusters', 'esells_settings.isPublic', 'esells_settings.whatsapp_number')
                ->get();
            return $data;
        } else {
            return false;
        }
    }
    public function getUserFollowingProfileById($factoryId)
    {
        if (auth('api')->user()->id) {
            $data = Followers::where('user_id', auth('api')->user()->id)
                ->where('status', 1)
                ->leftjoin('factories', 'factories.id', '=', 'followers.factory_id')
                ->leftjoin('esells_settings', 'esells_settings.factory_id', '=', 'followers.factory_id')
                ->where('factories.id', $factoryId)
                ->select('factories.nameFac', 'factories.state', 'factories.logoFac', 'followers.factory_id', 'followers.id', 'factories.description', 'factories.telephoneFac', 'factories.followers_count', 'factories.servicetype', 'factories.website', 'esells_settings.show_cubes', 'esells_settings.show_clusters', 'esells_settings.isPublic', 'esells_settings.whatsapp_number',DB::raw("true as isFollowed"))
                ->first();

            if ($data != null) {
                return $data;
            } else {
                $data = Factory::where('factories.id', $factoryId)
                    ->leftjoin('esells_settings', 'esells_settings.factory_id', '=', 'factories.id')
                    ->select('factories.nameFac', 'factories.state', 'factories.logoFac', 'factories.id as factory_id', 'factories.description', 'factories.telephoneFac', 'factories.followers_count', 'factories.servicetype', 'factories.website', 'esells_settings.show_cubes', 'esells_settings.show_clusters', 'esells_settings.isPublic', 'esells_settings.whatsapp_number',DB::raw("false as isFollowed"))
                    ->first();
                return $data;
            }
        } else {
            return false;
        }
    }
    public function removeFollowing($factoryId)
    {
        $fac = Factory::where('id', $factoryId)->first();
        if ($fac != null && $fac != '') {
            $user = User::find(auth('api')->user()->id);

            $check = Followers::where('user_id', $user->id)
                ->where('factory_id', $factoryId)
                ->get();
            if ($check->count() > 0) {
                $data = $check->first();
                $data->delete();
                $fac->decrement('followers_count', 1);
                $fac->save();
                $user->decrement('following_count', 1);
                $user->save();
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    public function addFollowingRequest($factoryId)
    {
        $user = User::find(auth('api')->user()->id);

        $check = Followers::where('user_id', $user->id)
            ->where('factory_id', $factoryId)
            ->get();
        if ($check->count() == 0 || $check->count() == null) {
            $data = new Followers();
            $data->user_id = $user->id;
            $data->factory_id = $factoryId;
            $essel = new EsellsSettingsController();
            if ($essel->isPublic($factoryId)) {
                $data->status = 1;
                $fac = Factory::where('id', $factoryId)->first();

                $fac->increment('followers_count', 1);
                $fac->save();
                $user->increment('following_count', 1);
                $user->save();
            } else {
                $data->status = 0;
            }
            if ($data->save()) {
                return true;
            } else {
                return false;
            }
        }
    }
    public function getUserFeed($page)
    {
        if (auth('api')->user()->id) {
            $data1 = Followers::where('user_id', auth('api')->user()->id)
                ->leftjoin('esells_settings', 'esells_settings.factory_id', '=', 'followers.factory_id')

                ->leftjoin('factories', 'factories.id', '=', 'followers.factory_id')

                ->leftJoin('clusters', 'clusters.factoryId', '=', 'esells_settings.factory_id')
                ->leftJoin('cluster_esells', 'cluster_esells.cluster_id', '=', 'clusters.id')

                ->where('esells_settings.show_clusters', true)
                ->where('clusters.hasImage', '=', 'yes')
                ->where('clusters.existence', '>', 1)
                ->where('clusters.show_in_esells', '=', 'yes')

                ->leftJoin('cluster_images', function ($join) {
                    $join->on('cluster_images.clusters_id', '=', 'clusters.id')->limit(1);
                })
                ->leftJoin('favorites', function ($join) {
                    $join->on('favorites.sharelinkId', '=', 'clusters.sharingLinks')->limit(1);
                    $join->where('favorites.type', '=', 'cluster');
                    $join->where('favorites.userId', '=', auth('api')->user()->id);
                })
                ->select('clusters.id as clId', 'clusters.existence', 'clusters.count', 'cluster_images.imageSrc as imageThumb', 'clusters.created_at', 'clusters.ClusterTypeStones', 'clusters.clusterNumber', 'clusters.clusterNameStone', 'clusters.sharingLinks as clSharingLinks', 'clusters.created_at', 'factories.nameFac', 'factories.logoFac', 'factories.id as fId', 'factories.servicetype as actype', 'esells_settings.whatsapp_number as whatsapp', 'favorites.id as favId', 'cluster_esells.statistics')
                ->orderBy('clusters.created_at', 'desc')

                ->get()
                ->take(80);
            $data2 = Followers::where('user_id', auth('api')->user()->id)
                ->leftjoin('esells_settings', 'esells_settings.factory_id', '=', 'followers.factory_id')

                ->leftjoin('factories', 'factories.id', '=', 'followers.factory_id')
                ->leftjoin('cubes', 'cubes.factoryId', '=', 'esells_settings.factory_id')

                ->leftJoin('cube_esells', 'cube_esells.cube_id', '=', 'cubes.id')

                ->leftjoin('cube_images', function ($join) {
                    $join->on('cube_images.cubeId', '=', 'cubes.id')->limit(1);
                })
                ->leftJoin('favorites', function ($join) {
                    $join->on('favorites.sharelinkId', '=', 'cubes.sharingLiks')->limit(1);
                    $join->where('favorites.type', '=', 'cube');
                    $join->where('favorites.userId', '=', auth('api')->user()->id);
                })
                ->where('cubes.show_in_esells', '=', 'yes')
                ->where('cubes.isActive', '=', 'yes')
                ->where('cubes.hasImage', '=', 'yes')

                ->where('esells_settings.show_cubes', true)
                ->select('cubes.id as cuId', 'cube_images.imageSrc as imageThumb', 'cubes.cubeNumber', 'cubes.nameCube', 'cubes.weight', 'cubes.length', 'cubes.width', 'cubes.height', 'cubes.created_at', 'cubes.sharingLiks as cuSharingLinks', 'factories.nameFac', 'factories.logoFac', 'factories.id as fId', 'factories.servicetype as actype', 'esells_settings.whatsapp_number as whatsapp', 'favorites.id as favId', 'cube_esells.statistics')
                ->orderBy('cubes.id', 'desc')

                ->get()
                ->take(80);
            if ($data1->count() > 0 && $data2->count() > 0) {
                $data1 = $data1->unique('clId');
                $data2 = $data2->unique('cuId');

                $data = $data1->concat($data2)->sortByDesc('created_at');
                return new LengthAwarePaginator($data->forPage($page, 18), $data->count(), 18, $page);
            } elseif ($data1->count() == 0 && $data2->count() > 0) {
                $data = $data2->unique('cuId');
                return new LengthAwarePaginator($data->forPage($page, 18), $data->count(), 18, $page);
            } elseif ($data1->count() > 0 && $data2->count() == 0) {
                $data = $data1->unique('clId');
                return new LengthAwarePaginator($data->forPage($page, 18), $data->count(), 18, $page);
            } else {
                // when we havent any data or user havent followed any factory yet
                $data1 = Factory::leftjoin('esells_settings', 'esells_settings.factory_id', '=', 'factories.id')

                    ->leftJoin('clusters', 'clusters.factoryId', '=', 'factories.id')
                    ->leftJoin('cluster_esells', 'cluster_esells.cluster_id', '=', 'clusters.id')

                    ->where('esells_settings.show_clusters', true)
                    ->where('clusters.hasImage', '=', 'yes')
                    ->where('clusters.existence', '>', 1)
                    ->where('clusters.show_in_esells', '=', 'yes')
                    ->leftJoin('cluster_images', function ($join) {
                        $join->on('cluster_images.clusters_id', '=', 'clusters.id')->limit(1);
                    })
                    ->leftJoin('favorites', function ($join) {
                        $join->on('favorites.sharelinkId', '=', 'clusters.sharingLinks')->limit(1);
                        $join->where('favorites.type', '=', 'cluster');
                        $join->where('favorites.userId', '=', auth('api')->user()->id);
                    })
                    ->select('clusters.id as clId', 'clusters.existence', 'clusters.count', 'cluster_images.imageSrc as imageThumb', 'clusters.created_at', 'clusters.ClusterTypeStones', 'clusters.clusterNumber', 'clusters.clusterNameStone', 'clusters.sharingLinks as clSharingLinks', 'clusters.created_at', 'factories.nameFac', 'factories.logoFac', 'factories.id as fId', 'factories.servicetype as actype', 'esells_settings.whatsapp_number as whatsapp', 'favorites.id as favId', 'cluster_esells.statistics')
                    ->orderBy('clusters.id', 'desc')
                    ->get()
                    ->take(80);

                $data2 = Factory::leftjoin('esells_settings', 'esells_settings.factory_id', '=', 'factories.id')
                    ->leftjoin('cubes', 'cubes.factoryId', '=', 'factories.id')
                    ->leftJoin('cube_esells', 'cube_esells.cube_id', '=', 'cubes.id')
                    ->leftjoin('cube_images', function ($join) {
                        $join->on('cube_images.cubeId', '=', 'cubes.id')->limit(1);
                    })
                    ->leftJoin('favorites', function ($join) {
                        $join->on('favorites.sharelinkId', '=', 'cubes.sharingLiks')->limit(1);
                        $join->where('favorites.type', '=', 'cube');
                        $join->where('favorites.userId', '=', auth('api')->user()->id);
                    })
                    ->where('cubes.show_in_esells', '=', 'yes')
                    ->where('cubes.isActive', '=', 'yes')
                    ->where('cubes.hasImage', '=', 'yes')

                    ->where('esells_settings.show_cubes', true)
                    ->select('cubes.id as cuId', 'cube_images.imageSrc as imageThumb', 'cubes.cubeNumber', 'cubes.nameCube', 'cubes.weight', 'cubes.length', 'cubes.width', 'cubes.height', 'cubes.created_at', 'cubes.sharingLiks as cuSharingLinks', 'factories.nameFac', 'factories.logoFac', 'factories.id as fId', 'factories.servicetype as actype', 'esells_settings.whatsapp_number as whatsapp', 'favorites.id as favId', 'cube_esells.statistics')
                    ->orderBy('cubes.id', 'desc')
                    ->get()
                    ->take(80);
                $data1 = $data1->unique('clId');
                $data2 = $data2->unique('cuId');

                $data = $data1->concat($data2)->sortByDesc('created_at');
                return new LengthAwarePaginator($data->forPage($page, 18), $data->count(), 18, $page);
            }
        } else {
            return false;
        }
    }
    public function searchInEsels($serchTxt, $type)
    {
        if ($type == 'cluster' && strlen($serchTxt) > 2) {
            $data1 = Clusters::where('existence', '>=', 1.0)
                ->leftjoin('cluster_esells', 'cluster_esells.cluster_id', '=', 'clusters.id')
                ->leftjoin('esells_settings', 'esells_settings.factory_id', '=', 'clusters.factoryId')
                ->leftjoin('factories', 'factories.id', '=', 'clusters.factoryId')

                ->where('clusters.show_in_esells', '=', 'yes')
                ->where('esells_settings.show_clusters', true)
                ->where('clusters.hasImage', '=', 'yes')
                ->where('clusters.existence', '>', 1)

                ->when($serchTxt, function ($q) use ($serchTxt) {
                    return $q->where('clusterNameStone', 'like', '%' . $serchTxt . '%');
                })
                ->select('clusters.id as clId', 'clusters.existence', 'clusters.count', 'clusters.imageThumb as imageThumb', 'clusters.created_at', 'clusters.ClusterTypeStones', 'clusters.clusterNumber', 'clusters.clusterNameStone', 'clusters.sharingLinks as clSharingLinks', 'clusters.created_at', 'factories.nameFac', 'factories.logoFac', 'factories.id as fId', 'factories.servicetype as actype', 'esells_settings.whatsapp_number as whatsapp')
                ->orderBy('clusters.id', 'desc')
                ->get()
                ->take(80);
            if ($data1->count() > 0) {
                // $data1 = $data1->unique("clId");

                return $data1;
            } else {
                return response()->json(['message' => 'No data found'], 404);
            }
        } elseif ($type == 'cube' && strlen($serchTxt) > 2) {
            $data2 = Cube::leftjoin('esells_settings', 'esells_settings.factory_id', '=', 'cubes.factoryId')
                ->leftjoin('factories', 'factories.id', '=', 'cubes.factoryId')

                ->where('cubes.show_in_esells', '=', 'yes')
                ->where('cubes.isActive', '=', 'yes')
                ->where('esells_settings.show_cubes', true)
                ->where('cubes.hasImage', '=', 'yes')

                ->when($serchTxt, function ($q) use ($serchTxt) {
                    return $q->where('nameCube', 'like', '%' . $serchTxt . '%');
                })
                ->select('cubes.id as cuId', 'cubes.imageThumb as imageThumb', 'cubes.cubeNumber', 'cubes.nameCube', 'cubes.weight', 'cubes.length', 'cubes.width', 'cubes.height', 'cubes.created_at', 'cubes.sharingLiks as cuSharingLinks', 'factories.nameFac', 'factories.logoFac', 'factories.id as fId', 'factories.servicetype as actype', 'esells_settings.whatsapp_number as whatsapp')

                ->orderBy('cubes.id', 'desc')

                ->get()
                ->take(80);
            if ($data2->count() > 0) {
                // $data2 = $data2->unique("clId");

                return $data2;
            } else {
                return response()->json(['message' => 'No data found'], 404);
            }
        } elseif ($type == 'factory' && strlen($serchTxt) > 2) {
            $data3 = Factory::leftjoin('esells_settings', 'esells_settings.factory_id', '=', 'factories.id')
                ->when($serchTxt, function ($q) use ($serchTxt) {
                    return $q->where('nameFac', 'like', '%' . $serchTxt . '%');
                })
                ->select('factories.nameFac', 'factories.logoFac', 'factories.id as fId', 'factories.servicetype as actype', 'esells_settings.whatsapp_number as whatsapp')
                ->get()
                ->take(80);
            if ($data3->count() > 0) {
                // $data3 = $data3->unique("clId");

                return $data3;
            } else {
                return response()->json(['message' => 'No data found'], 404);
            }
        }
        return response()->json(['message' => 'No data found'], 404);
    }
    public function getUserDiscoverData($page)
    {
        if (auth('api')->user()->id) {
            $data1 = Clusters::where('existence', '>=', 1.0)
                ->leftjoin('cluster_esells', 'cluster_esells.cluster_id', '=', 'clusters.id')
                ->leftjoin('esells_settings', 'esells_settings.factory_id', '=', 'clusters.factoryId')
                ->leftjoin('factories', 'factories.id', '=', 'clusters.factoryId')
                ->where('clusters.show_in_esells', '=', 'yes')
                ->where('esells_settings.show_clusters', true)
                ->where('clusters.hasImage', '=', 'yes')
                ->where('clusters.existence', '>', 1)
                ->select('clusters.id as clId', 'clusters.existence', 'clusters.count', 'clusters.imageThumb as imageThumb', 'clusters.created_at', 'clusters.ClusterTypeStones', 'clusters.clusterNumber', 'clusters.clusterNameStone', 'clusters.sharingLinks as clSharingLinks', 'clusters.created_at', 'factories.nameFac', 'factories.logoFac', 'factories.id as fId', 'factories.servicetype as actype', 'esells_settings.whatsapp_number as whatsapp', 'cluster_esells.statistics')
                ->orderBy('clusters.id', 'desc')
                ->orderBy('cluster_esells.statistics', 'desc')
                ->get()
                ->take(80);
            $data2 = Cube::leftjoin('esells_settings', 'esells_settings.factory_id', '=', 'cubes.factoryId')
                ->leftjoin('factories', 'factories.id', '=', 'cubes.factoryId')
                ->leftjoin('cube_esells', 'cube_esells.cube_id', '=', 'cubes.id')

                ->where('cubes.show_in_esells', '=', 'yes')
                ->where('cubes.isActive', '=', 'yes')
                ->where('esells_settings.show_cubes', true)
                ->where('cubes.hasImage', '=', 'yes')
                ->select('cubes.id as cuId', 'cubes.imageThumb as imageThumb', 'cubes.cubeNumber', 'cubes.nameCube', 'cubes.weight', 'cubes.length', 'cubes.width', 'cubes.height', 'cubes.created_at', 'cubes.sharingLiks as cuSharingLinks', 'factories.nameFac', 'factories.logoFac', 'factories.id as fId', 'factories.servicetype as actype', 'esells_settings.whatsapp_number as whatsapp', 'cube_esells.statistics')

                ->orderBy('cubes.id', 'desc')
                ->orderBy('cube_esells.statistics', 'desc')

                ->get()
                ->take(80);
            $data1 = $data1->unique('clId');
            $data2 = $data2->unique('cuId');
            $data = $data1->concat($data2)->sortByDesc('created_at');
            return new LengthAwarePaginator($data->forPage($page, 18), $data->count(), 18, $page);
        } else {
            return false;
        }
    }
    public function getSuggestFolloweingData()
    {
        $userID = auth('api')->user()->id;
        if ($userID) {
            $data = \DB::table('factories')
                ->select('factories.nameFac', 'factories.id as factory_id', 'factories.nameFac', 'factories.state', 'factories.logoFac', 'factories.description', 'factories.telephoneFac', 'factories.followers_count', 'factories.servicetype', 'factories.website', 'esells_settings.show_cubes', 'esells_settings.show_clusters', 'esells_settings.isPublic', 'esells_settings.whatsapp_number')
                ->whereNotExists(function ($query) use ($userID) {
                    $query
                        ->select(DB::raw(1))
                        ->from('followers')
                        ->whereRaw('followers.factory_id = factories.id')
                        ->where('followers.user_id', '=', $userID);
                })
                ->leftjoin('esells_settings', 'esells_settings.factory_id', '=', 'factories.id')
                ->orderby('factories.followers_count', 'desc')
                ->get()
                ->take(5);
            return $data;
        } else {
            return response()->json(['message' => 'No Auth'], 403);
        }
    }
    public function getCurrentUserFollowingCount() {
        return Followers::where('user_id', auth('api')->user()->id)->count();
    }
}
