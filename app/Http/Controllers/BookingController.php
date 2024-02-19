<?php

namespace App\Http\Controllers;
ini_set('memory_limit', '512M');
use App\Http\Repository\Contracts\IBookingRepository;
use App\Http\Traits\JsonResponseTrait;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class BookingController extends Controller
{
    use JsonResponseTrait;
    private $cIBookingRepository;
    public function __construct(IBookingRepository $pIBookingRepository)
    {
        $this->cIBookingRepository = $pIBookingRepository;
    }

    public function cpGetAvailability(Request $request)
    {
        $mValidate  = Validator::make($request->all(), [
            'check_in' => 'required|date|after:today',
            'check_out' => 'required|date|after:check_in',
            'destination_code' => 'nullable|string',
            'country_code' => 'nullable|string',
            'geolocation.latitude' => 'numeric',
            'geolocation.longitude' => 'numeric',
            'geolocation.radius' => 'numeric',
            'geolocation.unit' => 'string',
            'occupancies.*.room_no' => 'required',
            'occupancies.*.adults' => 'required',
            'occupancies.*.children' => 'required',
            'occupancies.*.children_age' => 'array',
            'occupancies.*.children_age.*' => 'integer',
            'filters.price.min_rate' => 'integer|gte:0', //minRate
            'filters.price.max_rate' => 'integer|gte:0', //maxRate
            'filters.rating' => 'array',
            'filters.boards.board' => 'array',
            'filters.boards.board.*' => 'string', // boards :{ board : [], included: false}
            'filters.boards.included' => 'boolean',
            'filters.hotels' => 'array',
            'filters.hotels.*' => 'integer',
            'filters.review' => 'array',
            'filters.review.*.type' => 'string', //"TRIPADVISOR", "HOTELBEDS"
            'filters.review.*.rate' => 'integer|gte:0|lte:5',
            'sortby' => 'string'
        ]);
        if ($mValidate->fails()) {
            return $this->cpFailureResponse(422, $mValidate->errors());
        }
        try {
            $mFilter = [];
            if (isset($request['filters'])) {
                $mFilter = $request->input('filters');
            }
            $mSortBy = $request->input('sortby');
            $mClientID = $request->client_id;
            unset($request['client_id'], $request['filters']);
            $mListAvailability = $this->cIBookingRepository->cpGetAvalByDestination($mClientID, $request->all(), $mFilter);
            if (!$mListAvailability) {
                return $this->cpSuccessResponse("No availabilities found.");
            }
            return $this->cpResponseWithResults($mListAvailability, "Availabilities retrieved successfully.");
        } catch (QueryException $pEx) {
            return $this->cpFailureResponse($pEx->getCode(), $pEx->getMessage(), $pEx);
        }
    }

    public function cpGetAvailabilityByHotel(Request $request)
    {
        $mValidate  = Validator::make($request->all(), [
            'check_in' => 'required|date|after:today',
            'check_out' => 'required|date|after:check_in',
            'destination_code' => 'nullable|string',
            'country_code' => 'nullable|string',
            'occupancies.*.room_no' => 'required',
            'occupancies.*.adults' => 'required',
            'occupancies.*.children' => 'required',
            'occupancies.*.children_age' => 'array',
            'occupancies.*.children_age.*' => 'integer',
            'filters.price.min_rate' => 'integer|gte:0', //minRate
            'filters.price.max_rate' => 'integer|gte:0', //maxRate
            'filters.rating' => 'array',
            'filters.boards.board' => 'array',
            'filters.boards.board.*' => 'string', // boards :{ board : [], included: false}
            'filters.boards.included' => 'boolean',
            'filters.hotels' => 'array',
            'filters.hotels.*' => 'integer',
            'filters.review' => 'array',
            'filters.review.*.type' => 'string', //"TRIPADVISOR", "HOTELBEDS"
            'filters.review.*.rate' => 'integer|gte:0|lte:5',
            'sortby' => 'string'
        ]);
        if ($mValidate->fails()) {
            return $this->cpFailureResponse(422, $mValidate->errors());
        }
        try {
            $mFilter = [];
            if (isset($request['filters'])) {
                $mFilter = $request->input('filters');
            }
            $mSortBy = $request->input('sortby');
            $mClientID = $request->client_id;
            unset($request['client_id'], $request['filters']);
            $mListAvailability = $this->cIBookingRepository->cpGetAvalByHotel($mClientID, $request->all(), $mFilter, $mSortBy);
            if (!$mListAvailability) {
                return $this->cpSuccessResponse("No availabilities found.");
            }
            return $this->cpResponseWithResults($mListAvailability, "Availabilities retrieved successfully.");
        } catch (QueryException $pEx) {
            return $this->cpFailureResponse($pEx->getCode(), $pEx->getMessage(), $pEx);
        }
    }

    public function cpGetAvalRoomsByHotel(Request $request)
    {
        $mValidate  = Validator::make($request->all(), [
            'check_in' => 'required|date|after:tomorrow',
            'check_out' => 'required|date|after:check_in',
            'destination_code' => 'string',
            'country_code' => 'string',
            'occupancies.*.room_no' => 'required',
            'occupancies.*.adults' => 'required',
            'occupancies.*.children' => 'required',
            'occupancies.*.children_age' => 'required|array',
            'occupancies.*.children_age.*' => 'integer',
            'hotel_codes' => 'required|array|min:1|max:1',
            'hotel_codes.*' => 'integer'
        ]);

        if ($mValidate->fails()) {
            return $this->cpFailureResponse(422, $mValidate->errors());
        }
        try {
            $mListAvailability = $this->cIBookingRepository->cpGetAvalRoomsByHotel($request->client_id, $request->all());
            if (!$mListAvailability) {
                return $this->cpFailureResponse(500, "Could not find availabile rooms.");
            }
            return $this->cpResponseWithResults($mListAvailability, "Availabile rooms retrieved successfully.");
        } catch (QueryException $pEx) {
            return $this->cpFailureResponse($pEx->getCode(), $pEx->getMessage(), $pEx);
        }
    }

    public function cpCheckRate(Request $request)
    {
        $mValidate  = Validator::make($request->all(), [
            'rate_key' => 'required|string'
        ]);
        if ($mValidate->fails()) {
            return $this->cpFailureResponse(422, $mValidate->errors());
        }
        try {
            $mHotelInfoWithRate = $this->cIBookingRepository->cpCheckRate($request->client_id, $request->all());
            if (!$mHotelInfoWithRate) {
                return $this->cpFailureResponse(500, "Could not find hotel information.");
            } else if (isset($mHotelInfoWithRate["error"])) {
                return $this->cpFailureResponse(500, $mHotelInfoWithRate["error"]["message"]);
            }
            return $this->cpResponseWithResults($mHotelInfoWithRate, "Hotel information retrieved successfully.");
        } catch (QueryException $pEx) {
            return $this->cpFailureResponse($pEx->getCode(), $pEx->getMessage(), $pEx);
        }
    }

    public function cpProcessBooking(Request $request)
    {
        $mValidate  = Validator::make($request->all(), [
            'holder_name' => 'required|string',
            'holder_surname' => 'required|string',
            'client_reference' => 'required|string',
            'remark' => 'nullable|string',
            'tolerance' => 'numeric',
            'rooms' => 'required|array',
            'rooms.*.rate_key' => 'required|string',
            'rooms.*.pax' => 'required|array',
            'rooms.*.pax.*.room_id' => 'integer|gte:1',
            'rooms.*.pax.*.type' => 'required|string', //AD - adult, CH - child
            'rooms.*.pax.*.age' => 'integer|gte:0|lte:99',
            'rooms.*.pax.*.name' => 'string',
            'rooms.*.pax.*.surname' => 'string',
            'total_net_sales' => 'required|numeric',
            'contact_details' => 'required|array',
            'contact_details.country' => 'required|string',
            'contact_details.email' => 'required|string',
            'contact_details.mobile' => 'required|regex:/[0-9]{10}/',
            'contact_details.message' => 'required|string'
        ]);
        if ($mValidate->fails()) {
            return $this->cpFailureResponse(422, $mValidate->errors());
        }
        try {
            $mClientID = $request->client_id ?? $request->user()->token()->client_id;
            $mBookings = $this->cIBookingRepository->cpProcessBooking($mClientID, $request->all());
            if (!$mBookings) {
                return $this->cpFailureResponse(500, "Could not process the booking.");
            } else if (isset($mBookings["error"])) {
                return $this->cpFailureResponse(500, $mBookings["error"]["message"]);
            }
            return $this->cpResponseWithResults($mBookings, "Hotel booking processed successfully.");
        } catch (QueryException $pEx) {
            return $this->cpFailureResponse($pEx->getCode(), $pEx->getMessage(), $pEx);
        }
    }

    public function cpListMyBookings(Request $request)
    {
        $mUser = Auth::user()->email;
        try {
            $mBookings = $this->cIBookingRepository->cpListMyBookings($mUser);
            if (!$mBookings) {
                return $this->cpFailureResponse(500, "Could not list the bookings.");
            }
            return $this->cpResponseWithResults($mBookings, "Hotel bookings listed successfully.");
        } catch (QueryException $pEx) {
            return $this->cpFailureResponse($pEx->getCode(), $pEx->getMessage(), $pEx);
        }
    }

    public function cpListBookings(Request $request)
    {
        $mValidate  = Validator::make($request->all(), [
            // 'from' => 'required|integer',
            // 'to' => 'required|integer',
            'start' => 'required|string',
            'end' => 'required|string',
            // 'client_reference' => 'string',
            'filter_type' => 'string',  //"CHECKIN" / "CHECKIN" / "CREATION"
            'country_code' => 'string',
            'destination_code' => 'string',
            'hotel_rating' => 'string',
            'hotel_name' => 'string'
        ]);
        if ($mValidate->fails()) {
            return $this->cpFailureResponse(422, $mValidate->errors());
        }
        try {
            if(!isset($request['filter_type'])){
                $request['filter_type'] = "CHECKIN";
            }
            $mBookings = $this->cIBookingRepository->cpListBookings($request->client_id, $request->all());
            if (!$mBookings) {
                return $this->cpFailureResponse(500, "Could not list the bookings.");
            } else if (isset($mBookings["error"])) {
                return $this->cpFailureResponse(500, $mBookings["error"]["message"]);
            }
            return $this->cpResponseWithResults($mBookings, "Hotel bookings listed successfully.");
        } catch (QueryException $pEx) {
            return $this->cpFailureResponse($pEx->getCode(), $pEx->getMessage(), $pEx);
        }
    }

    public function cpListTurnover(Request $request)
    {
        $mValidate  = Validator::make($request->all(), [
            // 'from' => 'required|integer',
            // 'to' => 'required|integer',
            'start' => 'required|string',
            'end' => 'required|string',
            // 'client_reference' => 'string',
            'filter_type' => 'string',  //"CHECKIN" / "CHECKIN" / "CREATION"
            'country_code' => 'string',
            'destination_code' => 'string',
            'hotel_rating' => 'string',
            'hotel_name' => 'string'
        ]);
        if ($mValidate->fails()) {
            return $this->cpFailureResponse(422, $mValidate->errors());
        }
        try {
            if(!isset($request['filter_type'])){
                $request['filter_type'] = "CHECKIN";
            }
            $mBookings = $this->cIBookingRepository->cpListBookings($request->client_id, $request->all());
            if (!$mBookings) {
                return $this->cpFailureResponse(500, "Could not list the bookings.");
            } else if (isset($mBookings["error"])) {
                return $this->cpFailureResponse(500, $mBookings["error"]["message"]);
            }
            return $this->cpResponseWithResults($mBookings, "Hotel bookings listed successfully.");
        } catch (QueryException $pEx) {
            return $this->cpFailureResponse($pEx->getCode(), $pEx->getMessage(), $pEx);
        }
    }

    public function cpGetBookingDetail(Request $request)
    {
        $mValidate  = Validator::make($request->all(), [
            'booking_reference' => 'required|string' // booking reference
        ]);
        if ($mValidate->fails()) {
            return $this->cpFailureResponse(422, $mValidate->errors());
        }
        try {
            $mBookingDetail = $this->cIBookingRepository->cpGetBookingDetail($request->client_id, $request->all());
            if (!$mBookingDetail) {
                return $this->cpFailureResponse(500, "Could not find the booking.");
            } else if (isset($mBookingDetail["error"])) {
                return $this->cpFailureResponse(500, $mBookingDetail["error"]["message"]);
            }
            return $this->cpResponseWithResults($mBookingDetail, "Hotel booking retrieved successfully.");
        } catch (QueryException $pEx) {
            return $this->cpFailureResponse($pEx->getCode(), $pEx->getMessage(), $pEx);
        }
    }

    public function cpCancelBooking(Request $request)
    {
        $mValidate  = Validator::make($request->all(), [
            'booking_reference' => 'required|string'
        ]);
        if ($mValidate->fails()) {
            return $this->cpFailureResponse(422, $mValidate->errors());
        }
        try {
            $mCancelledBooking = $this->cIBookingRepository->cpCancelBooking( $request->user()->token()->client_id, $request->all());
            if (!$mCancelledBooking) {
                return $this->cpFailureResponse(500, "Could not cancel the booking.");
            } else if (isset($mCancelledBooking["error"])) {
                return $this->cpFailureResponse(500, $mCancelledBooking["error"]["message"]);
            }
            return $this->cpResponseWithResults($mCancelledBooking, "Hotel bookings cancelled successfully.");
        } catch (QueryException $pEx) {
            return $this->cpFailureResponse($pEx->getCode(), $pEx->getMessage(), $pEx);
        }
    }

    public function cpBookingChange()
    {
    }
}
