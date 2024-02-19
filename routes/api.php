<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BoardController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CancellationRequestsController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\DestinationController;
use App\Http\Controllers\FacilityController;
use App\Http\Controllers\FacilityGroupController;
use App\Http\Controllers\GeneralConfigController;
use App\Http\Controllers\GroupCategoryController;
use App\Http\Controllers\HotelbedsCredentialController;
use App\Http\Controllers\HotelController;
use App\Http\Controllers\HotelimageController;
use App\Http\Controllers\ImageTypesController;
use App\Http\Controllers\ImportExcelController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\MarkupController;
use App\Http\Controllers\RoomController;
use App\Models\CancellationRequests;
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

// Route::get('list_loc_by_phrase', [LocationController::class, 'cpListLocationsByPhrase']);
// Route::post('get_aval_by_hotel', [BookingController::class, 'cpGetAvailabilityByHotel']);

Route::group(['middleware' => ['cors', 'json.response']], function () {
    // unauthenticated admin routes here
    Route::post('signup', [AuthController::class, 'cpSignUp'])->middleware('client', 'localization');
    Route::post('signin', [AuthController::class, 'cpSignIn'])->middleware('client', 'localization');
    Route::post('reset_password', [AuthController::class, 'cpPasswordReset'])->middleware('client', 'localization');
    Route::post('send_reset_link', [AuthController::class, 'cpSendLink'])->middleware('client', 'localization');

    //Publicly accessible routes (with client credentials grant)
    Route::get('publicly', [AuthController::class, 'cpPublicAccess'])->middleware('client', 'localization');
    // Route::post('publicly', [AuthController::class, 'cpPublicAccess'])->middleware('client', 'localization');
    Route::get('public', [AuthController::class, 'cpPublic'])->middleware('client', 'localization');


    /*============================================== Booking process ==============================================*/
    Route::post('get_availability', [BookingController::class, 'cpGetAvailability'])->middleware('client');
    Route::post('get_aval_by_hotel', [BookingController::class, 'cpGetAvailabilityByHotel'])->middleware('client');
    Route::post('get_aval_rooms_by_hotel', [BookingController::class, 'cpGetAvalRoomsByHotel'])->middleware('client');
    Route::post('check_rates', [BookingController::class, 'cpCheckRate'])->middleware('client');
    Route::post('process_booking', [BookingController::class, 'cpProcessBooking'])->middleware('client');


    //HB credentials
    Route::post('add_credentials', [HotelbedsCredentialController::class, 'cpStoreHBApiKey'])->middleware('client');

    /*============================================== hotels ==============================================*/
    Route::get('list_hotels', [HotelController::class, 'cpIndexModel'])->middleware('client', 'localization');
    Route::get('get_hotel_by_code', [HotelController::class, 'cpGetHotelByCode'])->middleware('client', 'localization');
    Route::get('get_hotel_by_name', [HotelController::class, 'cpGetHotelByName'])->middleware('client', 'localization');
    Route::get('get_facs_by_hotel', [HotelController::class, 'cpGetFacilitiesByHotelCode'])->middleware('client', 'localization');
    Route::get('list_hotels_by_country', [HotelController::class, 'cpListHotelsByCountryCode'])->middleware('client', 'localization');
    Route::get('list_hotels_by_zone', [HotelController::class, 'cpListHotelsByZone'])->middleware('client', 'localization');
    /*============================================== End hotels ==============================================*/

    /*============================================== hotel Images ==============================================*/
    Route::get('list_hotel_images', [HotelimageController::class, 'cpGetImagesByHotelID'])->middleware('client', 'localization');
    /*============================================== End hotel Images ==============================================*/

    /*============================================== image types ==============================================*/
    Route::get('list_image_types', [ImageTypesController::class, 'cpListImageTypes'])->middleware('client', 'localization');
    /*============================================== End image types ==============================================*/

    /*============================================== boards ==============================================*/
    Route::get('list_boards', [BoardController::class, 'cpIndexBoard'])->middleware('client', 'localization');
    Route::post('list_boards_by_board_codes', [BoardController::class, 'cpListBoardsByBoardCodes'])->middleware('client', 'localization');
    /*============================================== End boards ==============================================*/

    /*============================================== Facilities ==============================================*/
    Route::get('list_facilities', [FacilityController::class, 'cpIndexModel'])->middleware('client', 'localization');
    // Route::get('get_facility', [FacilityController::class, 'cpGetFacilityByCode'])->middleware('client', 'localization');
    Route::get('list_facility_by_codes', [FacilityController::class, 'cpListFacilitiesByFacilityCodes'])->middleware('client', 'localization');
    Route::get('get_facility_by_phrase', [FacilityController::class, 'cpGetFacilityByPhrase'])->middleware('client', 'localization');
    /*============================================== End Facilities ==============================================*/

    /*============================================== Facility Groups ==============================================*/
    Route::get('list_fac_groups', [FacilityGroupController::class, 'cpIndexModel'])->middleware('client', 'localization');
    /*============================================== End Facility Groups ==============================================*/

    /*============================================== Locations ==============================================*/
    Route::get('list_loc_by_phrase', [LocationController::class, 'cpListLocationsByPhrase'])->middleware('client', 'localization');
    Route::get('list_loc_by_phrase_wc', [LocationController::class, 'cpListLocationsByPhraseWC'])->middleware('client', 'localization');

    //Country
    Route::get('list_countries', [CountryController::class, 'cpIndexCountries'])->middleware('client', 'localization');
    Route::get('list_country_states', [CountryController::class, 'cpListStatesByCountry'])->middleware('client', 'localization');

    //Destination
    Route::get('list_destinations', [DestinationController::class, 'cpIndexDest'])->middleware('client', 'localization');
    Route::get('list_country_destinations', [DestinationController::class, 'cpIndexByCountryCode'])->middleware('client', 'localization');
    Route::get('list_destination_zones', [DestinationController::class, 'cpListZonesByDest'])->middleware('client', 'localization');
    /*============================================== End Locations ==============================================*/

    /*============================================== Category & Group Category ==============================================*/
    // Route::get('index_model', [CategoryController::class, 'cpIndexModel'])->middleware('client', 'localization'); // to be removed
    // Route::get('list_cat_by_accom', [CategoryController::class, 'cpListCatByAccomType'])->middleware('client', 'localization');
    Route::get('list_cat_by_cat_codes', [CategoryController::class, 'cpListCatByCatCodes'])->middleware('client', 'localization');
    Route::get('list_cat_by_cat_group', [CategoryController::class, 'cpListCatByCatGroup'])->middleware('client', 'localization');

    // Route::get('index_model', [GroupCategoryController::class, 'cpIndexModel'])->middleware('client', 'localization'); // to be removed
    /*============================================== End Category & Group Category ==============================================*/


    /*============================================== Room ==============================================*/
    Route::post('list_rooms_by_room_codes', [RoomController::class, 'cpListRoomsByRoomCodes'])->middleware('client', 'localization');
    // Route::get('index_model', [RoomController::class, 'cpIndexModel'])->middleware('client', 'localization'); // to be removed
    /*============================================== End Room ==============================================*/

    //Admin accessible routes (with client credentials grant)
    Route::middleware('client:admin')->get('adminscopeclient', [AuthController::class, 'cpAdminScopeClient']);

    //User accessible routes (with client credentials grant)
    Route::middleware('client:user')->get('userscopeclient', [AuthController::class, 'cpUserScopeClient']);

    // Route::post('import_excel',  [ImportExcelController::class, 'cpImport'])->middleware('client', 'localization');

    // Route::group(['middleware' => ['auth:admin-api', 'scopes:admin']], function () {

    Route::group(['middleware' => ['auth:api']], function () {

        Route::post('signout', [AuthController::class, 'cpSignOut']);
        //to be removed
        // Route::get('feed_countries', [CountryController::class, 'cpIndexCountry'])->middleware();
        // Route::get('feed_destinations', [DestinationController::class, 'cpIndexDestinations'])->middleware('client', 'localization');
        //to be removed end

        //import data from excel
        Route::post('import_excel',  [ImportExcelController::class, 'cpImport'])->middleware(['scope:admin']);

        /*============= Admin accessible routes (with password grant) ============= */

        //Board - admin accessible routes
        Route::post('update_board_descr', [BoardController::class, 'cpUpdateBoardDescr'])->middleware(['scope:admin']);

        //Destination - admin accessible routes
        Route::post('update_destination_attribute', [DestinationController::class, 'cpUpdateDestAttribute'])->middleware(['scope:admin']);
        Route::post('update_zone_descr', [DestinationController::class, 'cpUpdateZoneDescr'])->middleware(['scope:admin']);

        //Country - admin accessible routes
        // Route::post('update_country_description', [CountryController::class, 'cpUpdateCountryDescr'])->middleware(['scope:admin']);
        Route::post('update_custom_description', [CountryController::class, 'cpUpdateCountryDescr'])->middleware(['scope:admin']);

        //category - admin acccessible routes
        Route::post('update_category_attribute', [CategoryController::class, 'cpUpdateCatAttribute'])->middleware(['scope:admin']);
        //group category - admin acccessible routes
        Route::post('update_group_category_attribute', [GroupCategoryController::class, 'cpUpdateCatGroupAttribute'])->middleware(['scope:admin']);

        //room - admin accessible routes
        Route::post('update_room_attribute', [RoomController::class, 'cpUpdateRoomAttribute'])->middleware(['scope:admin']);

        //facility - admin accessible routes
        // Route::get('update_facility_description', [FacilityController::class, 'cpUpdateFacilityDescr'])->middleware(['scope:admin']);

        //Markup - admin accessible routes
        Route::post('create_markup', [MarkupController::class, 'cpCreateMarkup'])->middleware(['scope:admin']);
        Route::get('search_markup', [MarkupController::class, 'cpSearchMarkup'])->middleware(['scope:admin']);
        Route::post('update_markup', [MarkupController::class, 'cpUpdateMarkup'])->middleware(['scope:admin']);
        //General Markup - admin accessible routes
        Route::post('update_general_markup', [GeneralConfigController::class, 'cpSetGeneralMarkUp'])->middleware('scope:admin');

        // Image types - admin accessible routes
        Route::get('update_image_description', [ImageTypesController::class, 'cpUpdateImageDescr'])->middleware(['scope:admin']);

        // Hotel images - admin accessible routes
        Route::get('add_hotel_image', [HotelimageController::class, 'cpAddImageToHotel'])->middleware(['scope:admin']);
        Route::get('update_image_status', [HotelimageController::class, 'cpUpdateActiveStatus'])->middleware(['scope:admin']);

        // Hotels - admin accessible routes
        Route::get('update_hotel_attribute', [HotelController::class, 'cpUpdateHotelAttribute'])->middleware(['scope:admin']);
        Route::get('update_hotel_attribute', [HotelController::class, 'cpUpdateActiveStatus'])->middleware(['scope:admin']);

        // Bookings - admin accessible routes
        Route::get('list_bookings', [BookingController::class, 'cpListBookings'])->middleware('scope:admin');
        Route::get('list_turnover', [BookingController::class, 'cpListTurnover'])->middleware('scope:admin');
        Route::get('list_my_bookings', [BookingController::class, 'cpListMyBookings']);
        Route::post('request_cancellation', [CancellationRequestsController::class, 'cpRequestCancellation']);
        Route::get('list_cancel_requests',[CancellationRequestsController::class, 'cpListCancellationRequests'])->middleware(['scope:admin']);
        Route::get('get_booking_details', [BookingController::class, 'cpGetBookingDetail'])->middleware('scope:admin');
        Route::post('cancel_booking', [BookingController::class, 'cpCancelBooking'])->middleware('scope:admin');

        //Example
        /*============= Admin accessible routes (with password grant) ============= */
        Route::middleware(['scope:admin'])->get('adminpassgrant', [AuthController::class, 'cpAdminPass']);
        /*============= User accessible routes (with password grant) ============== */
        Route::middleware(['scope:user'])->get('userpassgrant', [AuthController::class, 'cpUserPass']);
    });
});
Route::fallback(function () {
    return response()->json([
        'message' => 'Page Not Found.'
    ], 404);
});
