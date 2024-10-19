<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/*Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});*/

Route::group(
    [
        'prefix' => 'auth',
    ],
    function () {
        Route::post('login', 'AuthController@login');
        Route::post('signup', 'AuthController@register');
        Route::post('hasUser', 'AuthController@hasUser');
        Route::group(
            [
                'middleware' => 'auth:api',
            ],
            function () {
                Route::get('logout', 'AuthController@logout');
                Route::get('user', 'AuthController@user');
                Route::get('getCurrentUser', 'AuthController@getCurrentUser');
                Route::get('/factory', 'FactoryController@factory');
            },
        );
    },
);
Route::middleware(['auth:api'])->group(function () {
    //Common
    Route::get('/getLastStatics', 'CommonController@getLastStatics');
    Route::get('/getUserPremissionFactoryDetails', 'CommonController@getUserPremissionFactoryDetails');
    //Factory
    Route::post('/newFactory', 'FactoryController@addFactory');
    Route::post('/updateFactory', 'FactoryController@updateFactory');

    Route::get('/getFactoryInfo', 'FactoryController@getFactoryInfo');
    Route::get('/enableFactoryEsells', 'FactoryController@enableFactoryEsells');
    Route::get('/disableFactoryEsells', 'FactoryController@disableFactoryEsells');
    Route::get('/getFactoryEsellsStatus', 'FactoryController@getFactoryEsellsStatus');
    Route::post('/addImageFactory', 'FactoryController@addImageFactory');
    Route::post('/addBannerImgFactory', 'FactoryController@addBannerImgFactory');
    // FactoryOptions

    Route::get('/getFactoryMaxUploadSize', 'FactoryOptionsController@getFactoryMaxUploadSize');

    //Cube
    Route::post('/newCube', 'CubeController@addCube');
    Route::get('/showAllFactoryCube', 'CubeController@showAllFactoryCube');
    Route::get('/showAllDeActiveFactoryCube', 'CubeController@showAllDeActiveFactoryCube');
    Route::get('/showFactoryCube', 'CubeController@showFactoryCube');
    Route::get('/showUsedCube', 'CubeController@showUsedCube');
    Route::get('/showCubeById/{id}', 'CubeController@showCubeById');
    Route::post('/cuttingCube', 'CubeController@cuttingCube');
    Route::post('/updateCube', 'CubeController@updateCube');
    Route::post('/searchCube', 'CubeController@searchCube');
    Route::post('/cubeAjaxSearch', 'CubeController@cubeAjaxSearch');
    Route::post('/cubeReports', 'CubeController@cubeReports');

    Route::get('/getNewCubeNumber', 'CubeController@getNewCubeNumber');
    Route::get('/deleteCubeById/{id}', 'CubeController@deleteCubeById');
    Route::get('/removeCuttingCube/{id}', 'CubeController@removeCuttingCube');
    Route::get('/getCountOfCube', 'CubeController@getCountOfCube');
    Route::get('/getGroupedCube', 'CubeController@getGroupedCube');
    Route::get('/getLastCube', 'CubeController@getLastCube');
    Route::get('/getLastCubes/{count}', 'CubeController@getLastCubes');
    Route::get('/getAllGroupedCube', 'CubeController@getAllGroupedCube');
    Route::get('/getLastMonthCube', 'CubeController@getLastMonthCube');
    Route::get('/getLastMonthUsedCube', 'CubeController@getLastMonthUsedCube');
    Route::get('/getGroupedMine', 'CubeController@getGroupedMine');
    Route::get('/getGroupedCubeName', 'CubeController@getGroupedCubeName');
    Route::get('/getLastCubeTimeRetrive', 'CubeController@getLastCubeTimeRetrive');
    Route::get('/getGroupedWarehouse', 'CubeController@getGroupedWarehouse');
    Route::get('/getAllCubeAnalyticsData', 'CubeController@getAllCubeAnalyticsData');
    Route::get('/getCubeGroupedReportFilterOption', 'CubeController@getCubeGroupedReportFilterOption');

    //CubeImageController

    Route::post('/addImageCube', 'CubeController@addImageCube');
    Route::get('/showCubeImageByCubeId/{id}', 'CubeImageController@showCubeImageByCubeId');
    Route::get('/deleteCubeImageById/{id}', 'CubeImageController@deleteCubeImageById');

    //CubePremisson
    Route::get('/getUserCubePremisson', 'CubePremissionController@getUserCubePremisson');
    Route::get('/getUserCubePremissonById/{userId}', 'CubePremissionController@getUserCubePremissonById');
    Route::post('/updateUserCubePremisson', 'CubePremissionController@updateUserCubePremisson');

    //CuWarehouse
    Route::get('/getFactoryCuWarehouse', 'CuWarehouseController@getFactoryCuWarehouse');
    Route::post('/addFactoryCuWarehouse', 'CuWarehouseController@addFactoryCuWarehouse');
    Route::post('/updateFactoryCuWarehouse', 'CuWarehouseController@updateFactoryCuWarehouse');
    Route::get('/deleteFactoryCuWarehouse/{name}', 'CuWarehouseController@deleteFactoryCuWarehouse');

    //CuSaw
    Route::get('/getFactoryCuSaws', 'CuSawController@getFactoryCuSaws');
    Route::get('/getFactoryCuSawsIdByName/{name}', 'CuSawController@getFactoryCuSawsIdByName');
    Route::get('/deleteFactoryCuSaws/{name}', 'CuSawController@deleteFactoryCuSaws');
    Route::post('/addFactoryCuSaws', 'CuSawController@addFactoryCuSaws');
    Route::post('/updateFactoryCuSaws', 'CuSawController@updateFactoryCuSaws');
    //CuCutting
    Route::post('/addCuttingTimeToCube', 'CuCuttingTimeController@addCuttingTimeToCube');
    Route::post('/updateCuttingTime', 'CuCuttingTimeController@updateCuttingTime');
    Route::get('/getCuttingTimeByCubeId/{cubeID}', 'CuCuttingTimeController@getCuttingTimeByCubeId');
    Route::get('/removeCuttingTime/{cubeId}', 'CuCuttingTimeController@removeCuttingTime');
    Route::get('/modifyCuCutting', 'CuCuttingTimeController@modifyCuCutting');

    //CuCuttingAddition
    Route::get('/getCuCuttingAdditions', 'CuCuttingAdditionController@getCuCuttingAdditions');
    Route::post('/addCuCuttingAdditions', 'CuCuttingAdditionController@addCuCuttingAdditions');
    Route::post('/deleteCuCuttingAdditions', 'CuCuttingAdditionController@deleteCuCuttingAdditions');

    //CuCuttingAdditionItemController
    Route::get('/getCuAdditionItemList/{cubeId}', 'CuCuttingAdditionItemController@getCuAdditionItemList');
    Route::post('/addCuAdditionItem', 'CuCuttingAdditionItemController@addCuAdditionItem');
    Route::post('/updateCuAdditionItem', 'CuCuttingAdditionItemController@updateCuAdditionItem');
    Route::post('/deleteCuAdditionItem', 'CuCuttingAdditionItemController@deleteCuAdditionItem');

    // Splitted Cube
    Route::post('/addSplittedCube', 'SplittedCubeController@create_SplittedCube');
    Route::patch('/updateSplittedCube', 'SplittedCubeController@update_SplittedCube');
    Route::post('/cuttingSplittedCube', 'SplittedCubeController@add_cutting_SplittedCube');
    Route::delete('/deleteSplittedCube/{splitted_id}', 'SplittedCubeController@delete_SplittedCube');
    Route::get('/getSplittedCubeByCubeId/{cube_id}', 'SplittedCubeController@get_SplittedCube_by_cube_id');
    Route::get('/removeSplittedCubeCutting/{splitted_id}', 'SplittedCubeController@remove_cutting_SplittedCube');
    //Cluster
    Route::post('/newCluster', 'ClustersController@addCluster');
    Route::post('/editcluster', 'ClustersController@editcluster');
    Route::get('/showClusterById/{id}', 'ClustersController@showClusterById');
    Route::get('/showFactoryCluster', 'ClustersController@showFactoryCluster');
    Route::post('/searchInCluster', 'ClustersController@searchInCluster');
    Route::post('/clusterAjaxSearch', 'ClustersController@clusterAjaxSearch');
    Route::get('/getCountCluster', 'ClustersController@getCountCluster');
    Route::get('/getGroupedCluster', 'ClustersController@getGroupedCluster');
    Route::get('/getGroupedCls', 'ClustersController@getGroupedCluster');
    Route::get('/getLastCluster', 'ClustersController@getLastCluster');
    Route::get('/getLastClusters/{count}', 'ClustersController@getLastClusters'); //take 5
    Route::get('/getAllGroupedCluster', 'ClustersController@getAllGroupedCluster'); // All Factory Cluster
    Route::get('/deleteCluster/{id}', 'ClustersController@deleteCluster');
    Route::get('/getGroupedClusterName', 'ClustersController@getGroupedClusterName');
    Route::get('/getGroupedClusterWarehouse', 'ClustersController@getGroupedClusterWarehouse');
    Route::post('/clusterReport', 'ClustersController@clusterReport');
    Route::get('/getlastMonthCluster', 'ClustersController@getlastMonthCluster');
    Route::get('/getMostClusterExistence', 'ClustersController@getMostClusterExistence');
    Route::get('/getLastClusterTimeRetrive', 'ClustersController@getLastClusterTimeRetrive');
    Route::get('/getAllClusterAnalyticsData', 'ClustersController@getAllClusterAnalyticsData');
    Route::get('/getCluWithImgClsizeLogOrderSize/{id}', 'ClustersController@getCluWithImgClsizeLogOrderSize');
    Route::get('/getClusterGroupedReportFilterOption', 'ClustersController@getClusterGroupedReportFilterOption');

    Route::get('/getClusteIdByshareId/{id}', 'ClustersController@getClusteIdByshareId');
    Route::get('/getCluWithImgClSizeFacNameImageById/{id}', 'ClusterEsellController@getCluWithImgClSizeFacNameImageById');
    Route::get('/getSellerInfoById/{id}', 'FactoryController@getSellerInfoById');

    //ClusterSize
    Route::post('/newclustersize', 'ClustersizeController@addNewSize');
    Route::get('/showSizeyId/{id}', 'ClustersizeController@showSizeyId');
    Route::get('/showAllClusterSizeById/{id}', 'ClustersizeController@showAllClusterSizeById');
    Route::post('/editclustersize', 'ClustersizeController@editclustersize');
    Route::get('/deleteSize/{id}', 'ClustersizeController@deleteSize');
    Route::get('/checkClusterSizeHasOrder/{id}', 'ClustersizeController@checkClusterSizeHasOrder');
    Route::post('/getClusterNameAndClusterSizeIdByShLinkWidthLenght', 'ClustersizeController@getClusterNameAndClusterSizeIdByShLinkWidthLenght');
    Route::get('/modifyExistNumberToClusterSize', 'ClustersizeController@modifyExistNumberToClusterSize');

    //ClusterImageController
    Route::get('/showClusterImageByClusterId/{id}', 'ClusterImageController@showClusterImageByClusterId');
    Route::post('/addImageCluster', 'ClustersController@addImageCluster');
    Route::get('/deleteCLusterImageById/{id}', 'ClusterImageController@deleteCLusterImageById');

    //ClusterLogController
    Route::get('/ClusterLogByClId/{id}', 'ClusterLogController@ClusterLogByClId');
    Route::get('/allClusterLogByClId/{id}', 'ClusterLogController@allClusterLogByClId');

    //ClusterPremisson
    Route::get('/getUserClusterPremisson', 'ClusterPremissionController@getUserClusterPremisson');
    Route::get('/getUserClusterPremissonById/{userId}', 'ClusterPremissionController@getUserClusterPremissonById');
    Route::post('/updateUserClusterPremisson', 'ClusterPremissionController@updateUserClusterPremisson');
    //ClWarehouse
    Route::get('/getFactoryClWarehouse', 'ClWearhouseController@getFactoryClWarehouse');
    Route::post('/addFactoryClWarehouse', 'ClWearhouseController@addFactoryClWarehouse');
    Route::post('/updateFactoryClWarehouse', 'ClWearhouseController@updateFactoryClWarehouse');
    Route::get('/deleteFactoryClWarehouse/{name}', 'ClWearhouseController@deleteFactoryClWarehouse');

    //ClProduceAddition
    Route::get('/getFactoryClProduceAdditions', 'ClProduceAdditionController@getFactoryClProduceAdditions');
    Route::post('/addFactoryClProduceAdditions', 'ClProduceAdditionController@addFactoryClProduceAdditions');
    Route::post('/deleteFactoryClProduceAdditions', 'ClProduceAdditionController@deleteFactoryClProduceAdditions');
    //ClProduceAdditionItem
    Route::get('/getClProduceAdditionItemList/{clusterId}', 'ClProduceAdditionItemController@getClProduceAdditionItemList');
    Route::post('/addClProduceAdditionItem', 'ClProduceAdditionItemController@addClProduceAdditionItem');
    Route::post('/updateClProduceAdditionItem', 'ClProduceAdditionItemController@updateClProduceAdditionItem');
    Route::post('/deleteClProduceAdditionItem', 'ClProduceAdditionItemController@deleteClProduceAdditionItem');

    //ClFinalStates
    Route::get('/getFinalStatsByClusterId/{clusterId}', 'ClFinalStatsController@getFinalStatsByClusterId');
    Route::get('/bindCubeToFinalExistenceCluster/{clusterId}/{cubeId}', 'ClFinalStatsController@bindCubeToFinalExistenceCluster');
    Route::post('/setFinalStatsCluster', 'ClFinalStatsController@setFinalStatsCluster');

    //Order
    Route::post('/neworder', 'OrderController@addOrder');
    Route::post('/updateOrder', 'OrderController@updateOrder');
    Route::get('/showOrderById/{id}', 'OrderController@showOrderById');
    Route::get('/showAllUnCompleteOrder', 'OrderController@showAllUnCompleteOrder');
    Route::get('/showAllCompleteOrder', 'OrderController@showAllCompleteOrder');
    Route::post('/searchUnCompleteOrder', 'OrderController@searchUnCompleteOrder');
    Route::post('/searchCompleteOrder', 'OrderController@searchCompleteOrder');
    Route::get('/getCountOfOrder', 'OrderController@getCountOfOrder');
    Route::get('/getLastOrder', 'OrderController@getLastOrder');
    Route::get('/getLastOrders/{count}', 'OrderController@getLastOrders');
    Route::get('/deleteCompOrder/{id}', 'OrderController@deleteCompOrder');
    Route::get('/deleteUnCompOrder/{id}', 'OrderController@deleteUnCompOrder');
    Route::get('/getCustomerNames', 'OrderController@getCustomerNames');
    Route::post('/orderReports', 'OrderController@orderReports');
    Route::get('/getBestCustomerLastMOnth', 'OrderController@getBestCustomerLastMOnth');
    Route::get('/getbestSelClusterLastMOnth', 'OrderController@getbestSelClusterLastMOnth');
    Route::get('/getAllOrderAnalyticsData', 'OrderController@getAllOrderAnalyticsData');
    Route::get('/showOrderAndOrderSizeById/{id}', 'OrderController@showOrderAndOrderSizeById');
    Route::get('/getOrderGroupedReportFilterOption', 'OrderController@getOrderGroupedReportFilterOption');
    Route::get('/getLastOrderNumber', 'OrderController@getLastOrderNumber');
    Route::get('/modifyOrderCustomer', 'OrderController@modifyOrderCustomer');

    //OrderSize
    Route::post('/newordersize', 'OrderSizeController@addordersize');
    Route::get('/showAllOrderSizeById/{id}', 'OrderSizeController@showAllOrderSizeById');
    Route::get('/showOrderSizeById/{id}', 'OrderSizeController@showOrderSizeById');
    Route::get('/showOrderSizeByClusterId/{id}', 'OrderSizeController@showOrderSizeByClusterId');
    Route::post('/editordersize', 'OrderSizeController@editordersize');
    Route::get('/deleteOrderSize/{id}', 'OrderSizeController@deleteOrderSize');
    Route::get('/showOrderSizewithConvert/{id}', 'OrderSizeController@showOrderSizewithConvert');
    Route::get('/changeOrderSizeStatus/{id}', 'OrderSizeController@changeOrderSizeStatus');

    Route::get('/changeOrderSizeStatusToUn/{id}', 'OrderSizeController@changeOrderSizeStatusToUn');
    Route::get('/showUncOrderSizeById/{id}', 'OrderSizeController@showUncOrderSizeById');
    Route::get('/showComOrderSizeById/{id}', 'OrderSizeController@showComOrderSizeById');

    Route::get('/showUnComOrderSizewithConvert/{id}', 'OrderSizeController@showUnComOrderSizewithConvert');
    Route::get('/showComOrderSizewithConvert/{id}', 'OrderSizeController@showComOrderSizewithConvert');
    Route::get('/retriveOrderSpecific/{id}', 'OrderSizeController@retriveOrderSpecific');
    Route::get('/retriveOrderConvertSize/{id}', 'OrderSizeController@retriveOrderConvertSize');

    //ConvertOrderSizeController
    Route::post('/addConvertOrderSize', 'ConvertOrderSizeController@addConvertOrderSize');
    Route::get('/deleteConvertSizeById/{id}', 'ConvertOrderSizeController@deleteConvertSizeById');
    Route::post('/updateConvertOrderSize', 'ConvertOrderSizeController@updateConvertOrderSize');
    Route::post('/addOutlyngToConvertSize', 'ConvertOrderSizeController@addOutlyngToConvertSize');
    Route::get('/showConvertOrderSizeByOrderID/{id}', 'ConvertOrderSizeController@showConvertOrderSizeByOrderID');

    //OrderPremisson
    Route::get('/getUserOrderPremisson', 'OrderPremissionController@getUserOrderPremisson');
    Route::get('/getUserOrderPremissonById/{userId}', 'OrderPremissionController@getUserOrderPremissonById');
    Route::post('/updateUserOrderPremisson', 'OrderPremissionController@updateUserOrderPremisson');


    //OrderLoading
    Route::post('/addNewOrderLoading', 'OrderLoadingController@addNewOrderLoading');
    Route::post('/editOrdelLoading', 'OrderLoadingController@editOrdelLoading');
    Route::get('/getOrderLoadingById/{id}', 'OrderLoadingController@getOrderLoadingById');
    Route::get('/deleteOrdelLoading/{id}', 'OrderLoadingController@deleteOrdelLoading');

    //OrderImages
    Route::post('/addImageOrder', 'OrderImagesController@addImageOrder');

    //UserController
    Route::post('/updateProfile', 'UserController@updateProfile');
    Route::post('/updateUserProfileImg', 'UserController@updateUserProfileImg');
    Route::post('/newUserFac', 'UserController@newUserFac');
    Route::post('/newUser', 'UserController@newUserFac');
    Route::post('/updateUserPremisson', 'UserController@updateUserPremisson');
    Route::get('/factoryUser', 'UserController@factoryUser');
    Route::get('/delUser/{id}', 'UserController@delUser');
    Route::get('/getFactoryUserPremissonsByID/{id}', 'UserController@getFactoryUserPremissonsByID');
    Route::post('/updateUser', 'UserController@updateUser');

    //SocialnetController
    Route::post('/addSocial', 'SocialnetController@addSocial');
    Route::post('/updateSocial', 'SocialnetController@updateSocial');
    Route::get('/getFactorySocial', 'SocialnetController@getFactorySocial');
    //File_storages
    Route::get('/deleteFileById/{id}', 'FileStorageController@deleteFileById');
    Route::get('/showAllFactoryFiles', 'FileStorageController@showAllFactoryFiles');
    Route::post('/addNewFile', 'FileStorageController@addNewFile');

    //customer
    Route::get('/getAllCustomer', 'CustomerController@getAllCustomer');
    Route::get('/getCustomerOrCreate/{name}', 'CustomerController@getCustomerOrCreate');
    Route::get('/getCustomerIdOrCreate/{name}', 'CustomerController@getCustomerIdOrCreate');
    Route::post('/addNewCustomer', 'CustomerController@addNewCustomer');
    Route::post('/updateCustomer', 'CustomerController@updateCustomer');
    Route::get('/deleteCustomer/{name}', 'CustomerController@deleteCustomer');


    /////*****************Esells */
    //Cluster Esells
    // Route::get('/showClusterEsellsList','ClusterEsellController@showClusterEsellsList');
    Route::get('/showClusterEsellsList/{page}', 'ClusterEsellController@showClusterEsellsList');
    Route::get('/showClusterEsellById/{id}', 'ClusterEsellController@showClusterEsellById');
    Route::post('/editClusterEsellById', 'ClusterEsellController@editClusterEsellById');
    Route::post('/changeClusterEsellsStatus', 'ClusterEsellController@changeClusterEsellsStatus');
    Route::post('/clusterEsellssearch', 'ClusterEsellController@clusterEsellssearch');
    //Exhibition Esells
    Route::get('/getExhibitionClustersListByFactoryId/{id}/{page}', 'ClusterEsellController@getExhibitionClustersListByFactoryId');
    Route::get('/getExhibitionCubesListByFactoryId/{id}/{page}', 'CubeEsellController@getExhibitionCubesListByFactoryId');
    Route::get('/getCuWithImgeFacNameById/{id}', 'CubeEsellController@getCuWithImgeFacNameById');
    Route::get('/getUserFollowList', 'FollowersController@getUserFollowList');
    Route::get('/removeFollowing/{factoryId}', 'FollowersController@removeFollowing');
    Route::get('/addFollowingRequest/{factoryId}', 'FollowersController@addFollowingRequest');
    Route::get('/getUserFeed/{page}', 'FollowersController@getUserFeed');
    Route::get('/getUserFollowingProfileById/{id}', 'FollowersController@getUserFollowingProfileById');
    Route::get('/addAndRemoveFave/{sharelinkId}/{type}', 'FavoritesController@addAndRemoveFave');
    Route::get('/searchInEsels/{txt}/{type}', 'FollowersController@searchInEsels');
    Route::get('/getUserDiscoverData/{page}', 'FollowersController@getUserDiscoverData');
    Route::get('/getSuggestFolloweingData', 'FollowersController@getSuggestFolloweingData');
    Route::get('/getCurrentUserFollowingCount', 'FollowersController@getCurrentUserFollowingCount');

    ///Cube Esells
    Route::get('/showCubeEsellsList/{page}', 'CubeEsellController@showCubeEsellsList');
    Route::get('/showCubeEsellById/{id}', 'CubeEsellController@showCubeEsellById');
    Route::post('/editCubeEsellById', 'CubeEsellController@editCubeEsellById');
    Route::post('/changeCubeEsellsStatus', 'CubeEsellController@changeCubeEsellsStatus');
    ///Order Esells
    Route::get('/showOrderEsellsList/{page}', 'EsellOrdersController@showOrderEsellsList');
    Route::get('/showOrderEsellById/{id}', 'EsellOrdersController@showOrderEsellById');
    Route::post('/changeSelectedOrderStatus', 'EsellOrdersController@changeSelectedOrderStatus');
    ///Order Esells Details
    Route::get('/showClusterOrderEsellDetailsById/{id}', 'EsellOrderDetailsController@showClusterOrderEsellDetailsById');
    Route::get('/showCubeOrderEsellDetailsById/{id}', 'EsellOrderDetailsController@showCubeOrderEsellDetailsById');
    // Order Esells Messages
    Route::get('/showOrderMessagesById/{id}', 'EsellsOrderMessageController@showOrderMessagesById');
    Route::get('/deleteOrderMessagesById/{id}', 'EsellsOrderMessageController@deleteOrderMessagesById');
    Route::post('/insertMessageToOrder', 'EsellsOrderMessageController@insertMessageToOrder');

    //Comment
    Route::get('/getFactoryCommentsList', 'CommentsController@getFactoryCommentsList');
    Route::get('/confirmComment/{id}', 'CommentsController@confirmComment');
    Route::get('/unConfirmComment/{id}', 'CommentsController@unConfirmComment');
    Route::get('/deleteCommentByFac/{id}', 'CommentsController@deleteCommentByFac');
    Route::post('/replayToComment', 'CommentsController@replayToComment');

    //Follow
    Route::get('/getFactoryFollowers', 'FollowersController@getFactoryFollowers');

    //Message

    Route::get('/getFactoryMessages', 'MessagesController@getFactoryMessages');
    Route::get('/showMessagesByUserId/{userId}', 'MessagesController@showMessagesByUserId');
    Route::get('/deleteMessageById/{id}', 'MessagesController@deleteMessageById');
    Route::get('/deleteAllConversationByUserId/{userId}', 'MessagesController@deleteAllConversationByUserId');
    Route::post('/sendFactoryMessageToUser', 'MessagesController@sendFactoryMessageToUser');
    Route::post('/editSelectedFactoryMessageToUser', 'MessagesController@editSelectedFactoryMessageToUser');

    //Message Group
    Route::get('/getFactoryGroupName', 'MessageGroupController@getFactoryGroupName');
    Route::post('/createNewFactoryGroup', 'MessageGroupController@createNewFactoryGroup');
    Route::get('/delFactoryGroupById/{id}', 'MessageGroupController@delFactoryGroupById');

    // Events
    Route::get('/changeAllEventStatusToRead/{requestType}', 'EventsController@changeAllEventStatusToRead');
    Route::get('/getFactoryEventsByCount/{count}', 'EventsController@getFactoryEventsByCount');
    //chat
    Route::post('/pusherAuth', 'MessagesController@pusherAuth');
    Route::post('/idFetchData', 'MessagesController@idFetchData');
    Route::post('/sendMessage', 'MessagesController@send');
    Route::post('/fetchMessage', 'MessagesController@fetch');
    Route::post('/seenMessage', 'MessagesController@seen');
    Route::post('/getContacts', 'MessagesController@getContacts');
    Route::post('/getLastMessengerActivity', 'MessagesController@getLastMessengerActivity');
    Route::post('/setFavorite', 'MessagesController@favorite');
    Route::post('/getfavorite', 'MessagesController@getFavorites');
    Route::post('/searchContacts', 'MessagesController@searchs');
    Route::post('/sharedPhotos', 'MessagesController@sharedPhotos');
    Route::post('/deleteConversation', 'MessagesController@deleteConversation');
    Route::post('/updateSettingsMessage', 'MessagesController@updateSettingsMessage');
    Route::post('/setActiveStatus', 'MessagesController@setActiveStatus');

    Route::get('/downloadMessageFile/{fileName}', 'MessagesController@download');
});
Route::middleware(['auth:api'])->group(function () {
    //all route
    Route::resource('/factory', FactoryController::class);
});
//Route::post('/addCubeImage', 'CubeImageController@addCubeImage');
//CubeShare
Route::get('/showCubeByLink/{id}', 'CubeController@showCubeByLink');
Route::get('/showCubeImageByLinkId/{id}', 'CubeImageController@showCubeImageByCubeId');
Route::get('/getCuWithImgeFacNameByshareId/{id}', 'CubeEsellController@getCuWithImgeFacNameByshareId');

//ClusterShare
Route::get('/showClusterByShareLink/{id}', 'ClustersController@showClusterByShareLink');
Route::get('/showAllClSizeByLinkId/{id}', 'ClustersizeController@showAllClSizeByLinkId');

Route::get('/showClImgByLinkId/{id}', 'ClusterImageController@showClImgByLinkId');
Route::get('/forgetPass/{email}', 'UserController@forgetPass');
Route::get('/getCluWithImgClSizeFacNameImageByshareId/{id}', 'ClusterEsellController@getCluWithImgClSizeFacNameImageByshareId');


// app seting
Route::get('/getAppSetting', 'AppSettingController@getAppSetting');

//************main section */
//cluster
Route::get('/getTopLastCluster', 'ClustersController@getTopLastCluster');
Route::get('/getUserFolowedClusterData', 'ClustersController@getUserFolowedClusterData');
Route::get('/getRandomCluster', 'ClustersController@getRandomCluster');
Route::get('/relatedCluster/{id}', 'ClustersController@relatedCluster');
Route::post('/getClusterByFacClusterCat', 'ClustersController@getClusterByFacClusterCat');
Route::post('/getGroupedFacClusterNameStoneByClusterTypeStones', 'ClustersController@getGroupedFacClusterNameStoneByClusterTypeStones');
Route::get('/getFacClTypeStonesGroup/{id}', 'ClustersController@getFacClTypeStonesGroup');
Route::get('/getGroupedFacClusterNameStone/{id}', 'ClustersController@getGroupedFacClusterNameStone');
Route::post('/getClusterByFacClusterName', 'ClustersController@getClusterByFacClusterName');
Route::post('/getClusterByFacName', 'ClustersController@getClusterByFacName');
Route::get('/showClusterEsellsListByFacName/{id}', 'ClusterEsellController@showClusterEsellsListByFacName');
Route::post('/clusterReportByFacName', 'ClustersController@clusterReportByFacName');
Route::get('/getPriceIntervalClusterByFac/{id}', 'ClustersController@getPriceIntervalClusterByFac');
Route::get('/getPriceIntervalClByStoneType/{id}', 'ClustersController@getPriceIntervalClByStoneType');
Route::get('/getExistenceIntervalClByStoneType/{id}', 'ClustersController@getExistenceIntervalClByStoneType');
Route::post('/getPriceIntervalClusterByFacStoneType', 'ClustersController@getPriceIntervalClusterByFacStoneType');
Route::get('/getExistanceIntervalClusterByFac/{id}', 'ClustersController@getExistanceIntervalClusterByFac');
Route::post('/getExistanceIntervalClusterByFacStoneType', 'ClustersController@getExistanceIntervalClusterByFacStoneType');
Route::post('/clusterReportByClusterTypeStone', 'ClustersController@clusterReportByClusterTypeStone');
Route::get('/getGroupedClNameStoneByClTypeStones/{type}', 'ClustersController@getGroupedClusterNameStoneByClTypeStones');
Route::get('/searchByClName/{clusterNameStone}', 'ClustersController@searchByClName');
Route::get('/getTopViwedCluster', 'ClustersController@getTopViwedCluster');

//Factory
Route::get('/getSellerInfo/{id}', 'FactoryController@getSellerInfo');
Route::get('/getSellersList', 'FactoryController@getSellersList');
Route::get('/getSellerState', 'FactoryController@getSellerState');
Route::get('/getSellerByStateName/{state}', 'FactoryController@getSellerByStateName');
Route::get('/getTopSellerByEnteringDate', 'FactoryController@getTopSellerByEnteringDate');
Route::post('/changeFactoryEsellsSetting', 'FactoryController@changeFactoryEsellsSetting');

// clusterImage
Route::get('/getClusterImageByClusterShareLInk/{id}', 'ClusterImageController@getClusterImageByClusterShareLInk');

//cubes
Route::get('/getTopLastCube', 'CubeController@getTopLastCube');
Route::get('/modifyCuWarehouse', 'CubeController@modifyCuWarehouse');
//factory
Route::get('/getRandomFactory', 'FactoryController@getRandomFactory');

//Iran states
Route::get('/getIranStateList', 'IranStateController@getIranStateList');
Route::get('/getIranCountyByStateId/{id}', 'IranCountyController@getIranCountyByStateId');

//comment
Route::get('/getCommentbyItemId/{id}', 'CommentsController@getCommentbyItemId');

//// Registration
Route::post('/newCustomer', 'UserController@newCustomer');

// user cart
Route::middleware(['auth:api'])->group(function () {
    Route::post('/addItemToCarts', 'EsellsCartController@addItemToCarts');
    Route::post('/updateItemInCarts', 'EsellsCartController@updateItemInCarts');
    Route::get('/showCartsByUser', 'EsellsCartController@getUserCart');
    Route::get('/removeItemFromCartsById/{id}', 'EsellsCartController@removeItemFromCartsById');
    Route::get('/getUserShipments', 'OrderShipmentsController@getUserShipments');
    Route::post('/createNewUserShipment', 'OrderShipmentsController@createNewUserShipment');
    Route::post('/updateUserShipment', 'OrderShipmentsController@updateUserShipment');
    Route::get('/getUserShipmentById/{id}', 'OrderShipmentsController@getUserShipmentById');

    Route::post('/createNewEsellsOrder', 'EsellOrdersController@createNewEsellsOrder');

    //favirite
    Route::post('/addFavorite', 'FavoritesController@addFavorite');
    Route::get('/delFavorite/{id}', 'FavoritesController@delFavorite');
    Route::get('/CheckFavorite/{id}', 'FavoritesController@CheckFavorite');
    Route::get('/getUserFavList', 'FavoritesController@getUserFavList');

    //Comments
    Route::post('/setNewCommentToItem', 'CommentsController@setNewCommentToItem');
    Route::get('/getUserCommentsList', 'CommentsController@getUserCommentsList');

    //Followers
    Route::post('/userFollowing', 'FollowersController@userFollowing');
    Route::get('/getFollowChecking/{nameFac}', 'FollowersController@getFollowChecking');
});
