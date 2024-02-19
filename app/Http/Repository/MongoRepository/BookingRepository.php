<?php

namespace App\Http\Repository\MongoRepository;

use App\Helpers\CollectionHelper;
use App\Helpers\DateHelper;
use App\Http\Controllers\GeneralConfigController;
use App\Http\Enums\BookingListBy;
use App\Http\Enums\RequestTypes;
use App\Http\Enums\BookingSortBy;
use App\Http\Enums\CancelRequestStatus;
use App\Http\Repository\Contracts\IBookingRepository;
use App\Http\Traits\HBApiTrait;
use App\Http\Traits\HBRedisTrait;
use App\Mail\BookingCancellation;
use App\Models\Booking;
use App\Models\Country;
use App\Models\Markup;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use SebastianBergmann\Environment\Console;

use function PHPSTORM_META\map;
use function PHPUnit\Framework\isEmpty;

class BookingRepository extends BaseRepository implements IBookingRepository
{
    use HBApiTrait, HBRedisTrait;
    private $cMarkupRepository;
    private $cGeneralConfigRepository;
    private $cDestinationRepository;
    private $cHotelRepository;
    private $cCancelRequestRepository;
    public function __construct(
        Booking $model,
        MarkupRepository $pMarkupRepository,
        GeneralConfigRepository $pGeneralConfigRepository,
        DestinationRepository $pDestinationRepository,
        CountryRepository $pCountryRepository,
        HotelRepository $pHotelRepository,
        HotelImageRepository $pHotelImageRepository,
        CancelRequestRepository $pCancelRequestRepository
    ) {
        parent::__construct($model);
        $this->cMarkupRepository = $pMarkupRepository;
        $this->cGeneralConfigRepository = $pGeneralConfigRepository;
        $this->cDestinationRepository = $pDestinationRepository;
        $this->cCountryRepository = $pCountryRepository;
        $this->cHotelRepository = $pHotelRepository;
        $this->cHotelImageRepository = $pHotelImageRepository;
        $this->cCancelRequestRepository = $pCancelRequestRepository;    
    }

    public function cpGetAvalByDestination($pClientID, $pParams, $pFilters = [], $pSortBy = BookingSortBy::PRLH)
    {
        $mAvalHotels = [];
        $mLocation = null;
        $mCacheKey = $this->cpGenerateKey($pParams);
        // $this->cpDeleteKey($mCacheKey);
        if (!$this->cpKeyExists($mCacheKey)) {
            if (!empty($pFilters) && isset($pFilters["hotels"]) && !empty($pFilters["hotels"])) {
                $pParams["hotel_codes"] = $pFilters["hotels"];
            } else {
                $mLocation = $this->cDestinationRepository->cpGetDestWithCountry($pParams["destination_code"]);
                $mLocation = is_null($mLocation["country"]) ? $mLocation["name"] : ($mLocation["name"] . "-" . $mLocation["country"]);
            }
            $mPostData = $this->cpGetAvalByDestReqParamObj($pParams);
            $mAvalHotels = $this->cpSendApiRequest(
                'hotel-api/1.0/hotels',
                RequestTypes::POST,
                $pClientID,
                json_decode($mPostData)
            );
            $mAvalHotels = $mAvalHotels->json();
            if (isset($mAvalHotels["hotels"]["hotels"])) {
                $this->cpCacheHotelSearch($mCacheKey, json_encode($mAvalHotels["hotels"]["hotels"]));
            } else {
                return;
            }
        }
        $mAvalHotels = json_decode($this->cpGetCachedHotel($mCacheKey));
        $mAvalHotelCollection = collect($mAvalHotels)->recursive();
        if (!empty($pFilters)) {
            $mAvalHotelCollection = $this->cpApplyFilters($mAvalHotelCollection, $pFilters);
        }
        $totalResults = $mAvalHotelCollection->count();
        $mAvalHotelCollection = $this->cpSortCollection($mAvalHotelCollection, $pSortBy);
        $mMetaData = collect(json_decode($this->cpGetCachedHotelMeta($mCacheKey)))->recursive();
        if ($mMetaData->isEmpty()) {
            $mMetaData = $this->cpGetMetaData($mAvalHotelCollection, $totalResults, $mLocation);
            $this->cpCacheHotelMeta($mCacheKey, $mMetaData);
        }
        $mAvalHotels = CollectionHelper::paginate($mAvalHotelCollection, 10);
        $mAvalHotelsWithAdditional  = $this->cpAddAdditionalInfo(
            $mAvalHotels,
            $pParams,
            $pClientID
        );
        $mAvalHotels->setCollection($mAvalHotelsWithAdditional);
        $mResult = $mMetaData->merge($mAvalHotels);
        return $mResult;
    }

    public function cpGetAvalByHotel($pClientID, $pParams, $pFilters = [], $pSortBy = BookingSortBy::PRLH)
    {
        $mAvalHotels = [];
        $mLocation = null;
        $mIsCountry = false;
        $mCacheKey = $this->cpGenerateKey($pParams);
        $this->cpDeleteKey($mCacheKey);
        if (!$this->cpKeyExists($mCacheKey)) {
            $pParams["hotel_codes"] = [];
            if (!empty($pFilters) && isset($pFilters["hotels"]) && !empty($pFilters["hotels"])) {
                $pParams["hotel_codes"] = $pFilters["hotels"];
            } else {
                    $mLocation = $this->cDestinationRepository->cpGetDestWithCountry($pParams["destination_code"]);
                    $mLocation = is_null($mLocation["country"]) ? $mLocation["name"] : ($mLocation["name"] . "-" . $mLocation["country"]);
                    $pParams["hotel_codes"] = $this->cHotelRepository->cpListHotelCodesByDestination($pParams["destination_code"], $pClientID);
            }
            $mAvalHotels = $this->cpGetAvalByHotelCodes($pClientID, $pParams);
            if (!is_null($mAvalHotels)) {
                $this->cpCacheHotelSearch($mCacheKey, json_encode($mAvalHotels));
            } else {
                return;
            }
        }
        $mAvalHotels = json_decode($this->cpGetCachedHotel($mCacheKey));
        $mAvalHotelCollection = collect($mAvalHotels)->recursive();
        if (!empty($pFilters)) {
            $mAvalHotelCollection = $this->cpApplyFilters($mAvalHotelCollection, $pFilters);
        }
        $totalResults = $mAvalHotelCollection->count();
        $mAvalHotelCollection = $this->cpSortCollection($mAvalHotelCollection, $pSortBy);
        $mMetaData = collect(json_decode($this->cpGetCachedHotelMeta($mCacheKey)))->recursive();
        if ($mMetaData->isEmpty()) {
            $mMetaData = $this->cpGetMetaData($mAvalHotelCollection, $totalResults, $mLocation);
            $this->cpCacheHotelMeta($mCacheKey, $mMetaData);
        }
        $mAvalHotels = CollectionHelper::paginate($mAvalHotelCollection, 10); //Check
        $mAvalHotelsWithAdditional  = $this->cpAddAdditionalInfo(
            $mAvalHotels,//->getCollection(),
            $pParams,
            $pClientID
        );
        $mAvalHotels->setCollection($mAvalHotelsWithAdditional);
        $mResult = $mMetaData->merge($mAvalHotels);
        return $mResult;
    }

    private function cpGetAvalByHotelCodes($pClientID, $pParams)
    {
        $mPostData = $this->cpGetAvalByHotelReqParamObj($pParams);
        $mAvalHotels = $this->cpSendApiRequest(
            'hotel-api/1.0/hotels',
            RequestTypes::POST,
            $pClientID,
            json_decode($mPostData)
        );
        $mAvalHotels = $mAvalHotels->json();
        if (isset($mAvalHotels["hotels"]["hotels"])) {
            return $mAvalHotels["hotels"]["hotels"];
        } else {
            return null;
        }
    }

    public function cpGetAvalRoomsByHotel($pClientID, $pParams)
    {
        $mCacheKey = $this->cpGenerateKey($pParams);
        if ($this->cpKeyExists($mCacheKey)) {
            $mHotel = json_decode($this->cpGetCachedHotel($mCacheKey));
        } else {
            $mHotel = $this->cpGetAvalByHotelCodes($pClientID, $pParams);
        }
        if (!is_null($mHotel)) {
            $mAvalHotelCollection = collect($mHotel)->recursive();
            $mRooms = $mAvalHotelCollection->first()->get('rooms');
            return $mRooms;
        } else {
            return;
        }
    }

    public function cpGetAvalByGeoLocation($pClientID, $pParams, $pFilters = [])
    {
    }

    private function cpGetMetaData($pAvalHotels, $pTotalResults, $pLocation)
    {
        $mMetaData = [];
        $mMetaData["meta"]["minRate"] = $pAvalHotels->min('minRate');
        $mMetaData["meta"]["maxRate"] = $pAvalHotels->max('maxRate');
        $mMetaData["meta"]["total"] = $pTotalResults;
        $mMetaData["meta"]["searchedLocation"] = $pLocation;
        $mMetaData["meta"]["avalMarkers"] = $pAvalHotels->map(function ($mHotel) {
            return collect($mHotel->toArray())
                ->only(['code', 'latitude', 'longitude'])
                ->all();
        });
        $mMetaData = collect($mMetaData)->recursive();;
        return $mMetaData;
    }

    private function cpSortCollection($pAvalHotels, $pSortBy)
    {
        $resultCollection = null;
        switch ($pSortBy) {
            case BookingSortBy::PRHL:
                $resultCollection = $pAvalHotels->sortByDesc('combined_min_rate', SORT_NUMERIC)->values();
                break;
            case BookingSortBy::SRLH:
                $resultCollection = $pAvalHotels->sortBy('categoryCode', SORT_STRING)->values();
                break;
            case BookingSortBy::SRHL:
                $resultCollection = $pAvalHotels->sortByDesc('categoryCode', SORT_STRING)->values();
                break;
            case BookingSortBy::HNLH:
                $resultCollection = $pAvalHotels->sortBy('name', SORT_STRING)->values();
                break;
            case BookingSortBy::HNHL:
                $resultCollection = $pAvalHotels->sortByDesc('name', SORT_STRING)->values();
                break;
            default:
                $resultCollection = $pAvalHotels->sortBy('combined_min_rate', SORT_NUMERIC)->values();
                break;
        }
        return $resultCollection;
    }

    private function cpApplyFilters($pAvalHotels, $pFilters)
    {
        $mHotels = collect($pAvalHotels)->recursive();
        if (isset($pFilters["price"]["min_rate"])) {
            $mHotels = $mHotels->where('minRate', '>=', $pFilters["price"]["min_rate"])->values();
        }
        if (isset($pFilters["price"]["max_rate"])) {
            $mHotels = $mHotels->where('maxRate', '<=', $pFilters["price"]["max_rate"])->values();
        }
        if (isset($pFilters["rating"]) && !empty($pFilters["rating"])) {
            $pRatingArr = $pFilters["rating"];
            $pRatingArr = array_map(function ($value) {
                return $value . "EST";
            }, $pRatingArr);
            $mHotels = $mHotels->whereIn('categoryCode', $pRatingArr)->values();
        }
        if (isset($pFilters["boards"]["board"]) && !empty($pFilters["boards"]["board"])) {
            // $mHotels = $mHotels->where('rooms.*.name', $pFilters["boards"]["board"][0]);
            $filterBoards = $pFilters["boards"]["board"];
            $mHotels = $mHotels->filter(function ($value) use ($filterBoards) {
                $mResult = null;
                $mExists = false;
                if (isset($value["rooms"])) {
                    foreach ($value["rooms"] as $inx => $room) {
                        $avalRates = $room["rates"];
                        $mResult = $avalRates->whereIn('boardName', $filterBoards)->values();
                        if (count($mResult) > 0) {
                            $mExists = true;
                            $value["rooms"][$inx]["rates"] = $mResult;
                        }
                    }
                }
                return $mExists;
            });
        }
        if (isset($pFilters["hotels"]) && !empty($pFilters["hotels"])) {
            $mHotels = $mHotels->whereIn('code', $pFilters["hotels"]);
        }
        if (isset($pFilters["review"]) && !empty($pFilters["review"])) {
            $filterReviews = $pFilters["review"][0];
            $mHotels = $mHotels->filter(function ($value) use ($filterReviews) {
                $mReview = $value["reviews"][0];
                if ($mReview["type"] == "TRIPADVISOR" && $mReview["rate"] >= intval($filterReviews["rate"])) {
                    return true;
                }
                return false;
            });
            $mHotels = collect($mHotels->values()->all());
        }
        return $mHotels;
    }

    public function cpCheckRate($pClientID, $pParams)
    {
        $mReqParams = array("rooms" => [["rateKey" => $pParams["rate_key"]]]);
        $mPostData = json_encode($mReqParams);
        $mHotelRate = $this->cpSendApiRequest(
            'hotel-api/1.0/checkrates',
            RequestTypes::POST,
            $pClientID,
            json_decode($mPostData)
        );
        $mHotelRate = $mHotelRate->json();
        if (!isset($mHotelRate["hotel"])) {
            return array("error" => $mHotelRate["error"]);
        }
        return $mHotelRate["hotel"];
    }

    public function cpProcessBooking($pClientID, $pParams)
    {
        $mPostData = $this->cpGetHBProcBkgReqParamObj($pParams);
        $mHotelBooking = $this->cpSendApiRequest(
            'hotel-api/1.0/bookings',
            RequestTypes::POST,
            $pClientID,
            json_decode($mPostData)
        );
        $mHotelBooking = $mHotelBooking->json();
        if (!isset($mHotelBooking["booking"])) {
            return array("error" => $mHotelBooking["error"]);
        }
        $mHotelBooking = $mHotelBooking["booking"];
        $mBookingCountry = $this->cDestinationRepository->cpGetCountryCodeOfDest($mHotelBooking["hotel"]["destinationCode"]);
        $mBookingInfo = [];
        $mBookingInfo['check_in'] = $mHotelBooking["hotel"]["checkIn"];
        $mBookingInfo['check_out'] = $mHotelBooking["hotel"]["checkOut"];
        $mBookingInfo['country_code'] = $mBookingCountry["country_code"];
        $mBookingInfo['destination_code'] = $mHotelBooking["hotel"]["destinationCode"];
        $mBookingInfo['reference'] = $mHotelBooking["reference"];
        $mBookingInfo['client_reference'] = $mHotelBooking["clientReference"];
        $mBookingInfo['status'] = $mHotelBooking["status"];
        $mBookingInfo['cancellation_policy'] = $mHotelBooking["modificationPolicies"]["cancellation"];
        $mBookingInfo['modification_policy'] = $mHotelBooking["modificationPolicies"]["modification"];
        $mBookingInfo['used_hb_key'] = $mHotelBooking["creationUser"];
        $mBookingInfo['holder_name'] = $mHotelBooking["holder"]["name"];
        $mBookingInfo['holder_surname'] = $mHotelBooking["holder"]["surname"];
        $mBookingInfo['remark'] = $mHotelBooking["remark"];
        $mBookingInfo['total_net'] = $mHotelBooking["totalNet"];
        $mBookingInfo['net_with_markup'] = $pParams["total_net_sales"];
        $mBookingInfo['pending_amount'] = $mHotelBooking["pendingAmount"];
        $mBookingInfo['currency'] = $mHotelBooking["currency"];
        $mBookingInfo['hotel'] = collect($mHotelBooking["hotel"])->all();
        $mBookingInfo['client_id'] = $pClientID;
        $mBookingInfo['contact_details'] = $pParams["contact_details"];
        $mSaved = $this->model->create($mBookingInfo);
        if (!$mSaved) {
            return null;
        }
        return $mBookingInfo;
    }

    public function cpGetBookingDetail($pClientID, $pParams)
    {
        $mBooking = $this->model->where('reference', $pParams["booking_reference"])->first();
        if (!$mBooking) {
            $mPostData = json_encode(["bookingId" => $pParams["booking_id"]]);
            $mHotelRate = $this->cpSendApiRequest(
                'hotel-api/1.0/bookings',
                RequestTypes::GET,
                $pClientID,
                json_decode($mPostData)
            );
            $mHotelRate = $mHotelRate->json();
            if (!isset($mHotelRate["booking"])) {
                return array("error" => $mHotelRate["error"]);
            }
            return $mHotelRate["booking"];
        }
        return $mBooking;
    }

    public function cpListBookings($pClientID, $pParams)
    {
        $mFilterDateType = "";
        if ($pParams["filter_type"] === BookingListBy::CHECKIN) {
            $mFilterDateType = "check_in";
        } else if ($pParams["filter_type"] === BookingListBy::CHECKOUT) {
            $mFilterDateType = "check_out";
        } else if ($pParams["filter_type"] === BookingListBy::CREATION) {
            $mFilterDateType = "created_at";
        }
        $mHotelBookings = $this->model
            ->whereBetween(
                $mFilterDateType,
                [
                    // Carbon::createFromFormat('Y-m-d', $pParams['start']),
                    // Carbon::createFromFormat('Y-m-d', $pParams['end'])
                    date('Y-m-d', strtotime($pParams['start'])),
                    date('Y-m-d', strtotime($pParams['end']))
                ]
            )
            // ->where('country_code', $pParams["country_code"])
            // ->where('destination_code', $pParams["destination_code"])
            ->get();

        $mAdditionalData = [];
        $mAdditionalData["total_sales_value"] = 0;
        $mAdditionalData["total_cost_of_sales"] = 0;
        foreach ($mHotelBookings as $key => $value) {
            $mAdditionalData["total_sales_value"] = $mAdditionalData["total_sales_value"] + $value['net_with_markup'];
            $mAdditionalData["total_cost_of_sales"] = $mAdditionalData["total_cost_of_sales"] + $value['total_net'];
        }
        $mAdditionalData = collect($mAdditionalData);
        $mHotelBookings = CollectionHelper::paginate($mHotelBookings, 10);
        $mHotelBookings = $mAdditionalData->merge($mHotelBookings);
        return $mHotelBookings;
    }

    public function cpListMyBookings($pUserEmail)
    {
        $mBookings = $this->model->where('contact_details.email', $pUserEmail)->get();
        return $mBookings;
    }

    public function cpRequestCancellation($pReference){
       $pRequested = $this->cCancelRequestRepository->cpRequest($pReference);
       return $pRequested;
    }

    public function cpCancelBooking($pClientID, $pParams)
    {
        $mQueryData = ["cancellationFlag" => "CANCELLATION"];
        $mHotelRate = $this->cpSendApiRequest(
            'hotel-api/1.0/bookings/' . $pParams["booking_reference"],
            RequestTypes::DELETE,
            $pClientID,
            $mQueryData
        );
        $mHotelRate = $mHotelRate->json();
        if (!isset($mHotelRate["booking"])) {
            return array("error" => $mHotelRate["error"]);
        }
        else{
            $mBooking = $this->model->where('reference', $pParams["booking_reference"])->first();
            if ($mBooking !== null) {
                $mBooking['status'] = "CANCELLED";
                $mSaved = $mBooking->save();
                if($mSaved){
                    $this->cCancelRequestRepository->cpUpdateStatus($pParams["booking_reference"],CancelRequestStatus::PROCESSED);
                    $cancellation = [
                        "reference"=> $mHotelRate["booking"]["reference"],
                        "cancellationReference"=> $mHotelRate["booking"]["cancellationReference"],
                        "clientReference"=> $mHotelRate["booking"]["clientReference"],
                        "creationDate"=> $mHotelRate["booking"]["creationDate"],
                        "status"=>$mHotelRate["booking"]["status"],
                        "creationUser"=> $mHotelRate["booking"]["creationUser"],
                        "holder"=> $mHotelRate["booking"]["holder"]["name"]." ".$mHotelRate["booking"]["holder"]["surname"]
                    ];
                    Mail::to($mBooking['contact_details']['email'])->send(new BookingCancellation($cancellation));
                    //----------------- send to hotellidiilid admin also --------------------
                    // Mail::to($mBooking['contact_details']['email'])->send(new BookingCancellation($cancellation));
                    return $mHotelRate["booking"];
                }
            }
            else{
                return null;
            }
            
        }
    }

    private function cpGetOccupKeyList($pOccupancy){
        $mKeys = [];
        foreach($pOccupancy as $occupancy){
            $mCombination = $occupancy['adults']."-".$occupancy['children'];
            if(intval($occupancy["children"]) > 0){
                sort($occupancy["children_age"]);
                $mCombination = $mCombination."-".implode("-",$occupancy["children_age"]);
            }
            if(array_search($mCombination, $mKeys) === false){
                array_push($mKeys,$mCombination);
            }
        };
        return $mKeys;
    }

    private function cpCalculateMinRate($pFilteredRates){
        $mMinRate = 0.0;
        foreach($pFilteredRates as $key => $value){
            $mMinRate = $mMinRate + round($value['netWithMarkup'], 2);
        }
        return $mMinRate;
    }

    private function cpAddAdditionalInfo($pHotels, $pParams, $pClientID)
    {
        $mKeys = $this->cpGetOccupKeyList($pParams['occupancies']);
        $mHotels = $pHotels->map(function ($value,$inx) use ($pParams, $pClientID, $mKeys) {    
            $mTempArrCombinedOccup = $mKeys;
            $mCombinedMinRateSet = false;
            $mFilteredRates = [];
            if (isset($value["rooms"])) {
                foreach ($value["rooms"] as $room) {
                    foreach ($room["rates"] as $rate) {
                        $rate["netWithMarkup"] = $this->cpCalculateMarkup($rate['net'], $pParams["check_in"], $pParams["destination_code"], $pClientID);
                        if(!$mCombinedMinRateSet && ($rate["rateType"] == "BOOKABLE" || $rate["rateType"] == "RECHECK")){
                            $mCombinationInRate = $rate['adults']."-".$rate['children'];
                            if($rate["children"]>0){
                                $mAgeArr = explode(",",$rate["childrenAges"]);
                                sort($mAgeArr);
                                $mCombinationInRate = $mCombinationInRate."-".implode("-",$mAgeArr);
                            }
                            if(in_array($mCombinationInRate, $mTempArrCombinedOccup)){
                                $mIndx = array_search($mCombinationInRate, $mTempArrCombinedOccup);
                                $mFilteredRates[$mCombinationInRate] = $rate;
                                array_splice($mTempArrCombinedOccup, $mIndx, 1);
                                if(count($mTempArrCombinedOccup) == 0){
                                    $mMinRate = $this->cpCalculateMinRate($mFilteredRates);
                                    Log::info("min rate");
                                    Log::info($mMinRate);
                                    $value['combined_min_rate'] = $mMinRate;
                                    $mCombinedMinRateSet = true;
                                    // break;
                                }
                            }
                        }
                    }
                }
            }
            //check if minRate here is needed anymore
            if(isset($value['minRate'])){
                $value['minRate'] = number_format($this->cpCalculateMarkup($value['minRate'], $pParams["check_in"], $pParams["destination_code"], $pClientID),2);
            }
            $mHotleImg = $this->cHotelImageRepository->cpGetImageByHotelIDOrder($value["code"], 1);
            if ($mHotleImg)
                $value["primary_img"] = $mHotleImg["path"];
            else
                $value["primary_img"] = "";
            return $value;
        });
        return collect($mHotels);
    }

    private function cpCalculateMarkup($pNetRate, $pCheckIn, $pLocationCode, $pClientID)
    {
        $mCheckIn = date('Y-m-d', strtotime($pCheckIn));
        $mMarkupValue = 0.0;
        // if ($pIsCountry) {
        //     $mMarkupValue = $this->cMarkupRepository->cpSearchMarkup($mCheckIn, $pLocationCode);
        //     if (is_null($mMarkupValue)) {
        //         $mMarkupValue = $this->cGeneralConfigRepository->cpGetGeneralMarkup($pClientID, 'General_Markup');
        //         if (!isEmpty($mMarkupValue)) {
        //             $mMarkupValue = $mMarkupValue["configuration"];
        //         }
        //     } else {
        //         $mMarkupValue = $mMarkupValue["markup_pct"];
        //     }
        // } else {
        $mMarkupValue = $this->cMarkupRepository->cpSearchMarkup($mCheckIn, null, $pLocationCode);
        if (is_null($mMarkupValue)) {
            $mCountry = $this->cDestinationRepository->cpGetCountryCodeOfDest($pLocationCode);
            if (!is_null($mCountry)) {
                $mMarkupValue = $this->cMarkupRepository->cpSearchMarkup($mCheckIn, $mCountry["country_code"]);
                if (is_null($mMarkupValue)) {
                    $mMarkupValue = $this->cGeneralConfigRepository->cpGetGeneralMarkup($pClientID, 'General_Markup');
                    if ($mMarkupValue) {
                        $mMarkupValue = doubleval($mMarkupValue["configuration"]);
                    } else{
                        $mMarkupValue = 0.0;
                    }
                        
                } else{
                    $mMarkupValue = $mMarkupValue["markup_pct"];
                }    
            }
        } else {
            $mMarkupValue = $mMarkupValue["markup_pct"];
        }
        // }
        if ($mMarkupValue === 0.0) {
            return doubleval($pNetRate);
        } else {
            return (doubleval($pNetRate) + (doubleval($pNetRate) * doubleval($mMarkupValue) / 100));
        }
    }

    private function cpGetBasicAvalReqParamObj($pReqParams)
    {
        $mReqParams = array();
        $mReqParams["stay"] = [
            "checkIn" => $pReqParams["check_in"],
            "checkOut" => $pReqParams["check_out"]
        ];
        $mReqOccupancies = array();
        foreach ($pReqParams["occupancies"] as $occupancy) {
            $mKey = $occupancy['adults'].'-'.$occupancy["children"];
            if(intval($occupancy["children"]) > 0){
                sort($occupancy["children_age"]);
                $mKey = $mKey."-".implode("-",$occupancy["children_age"]);
            }
            if(in_array($mKey,array_keys($mReqOccupancies))){
                $mReqOccupancies[$mKey]["rooms"] = $mReqOccupancies[$mKey]["rooms"] + 1;
            }
            else{
                $mReqOccupancies[$mKey] = [
                    "rooms" => $occupancy["room_no"],
                    "adults" => $occupancy["adults"],
                    "children" => $occupancy["children"],
                ];
                if (intval($occupancy["children"]) > 0) {
                    $paxes = array_map(function ($value) {
                        return ["type" => "CH", "age" => $value];
                    }, $occupancy["children_age"]);
                    $mReqOccupancies[$mKey]["paxes"] = $paxes;
                }
            }
        }
        $mReqParams["occupancies"] = array_values($mReqOccupancies);
        return $mReqParams;
    }

    private function cpGetAvalByDestReqParamObj($pReqParams)
    {
        $mReqParams = $this->cpGetBasicAvalReqParamObj($pReqParams);
        // if (isset($pReqParams["destination_code"])) {
        $mReqParams["destination"] = [
            "code" => $pReqParams["destination_code"]
        ];
        // } else if (isset($pReqParams["country_code"])) {
        //     $mReqParams["country"] = [
        //         "code" => $pReqParams["country_code"]
        //     ];
        // }
        // $mReqParams["filter"] = array("maxHotels" => 10); // To be removed
        if (isset($pReqParams["hotel_codes"])) {
            $mReqParams["hotels"] = [
                "hotel" => $pReqParams["hotel_codes"]
            ];
        }
        $mReqParams["reviews"] = [
            [
                "type" => "TRIPADVISOR",
                "maxRate" => 5,
                "minRate" => 1
            ]
        ];
        $mPostData = json_encode($mReqParams);
        return $mPostData;
    }

    private function cpGetAvalByGeoLocationReqParamObj($pReqParams)
    {
        $mReqParams = $this->cpGetBasicAvalReqParamObj($pReqParams);
        if (isset($pReqParams["country_code"])) {
            $mReqParams["country"] = [
                "code" => $pReqParams["country_code"]
            ];
        }
        $mReqParams["filter"] = array("maxHotels" => 10); // To be removed
        $mReqParams["reviews"] = [
            [
                "type" => "TRIPADVISOR",
                "maxRate" => 5,
                "minRate" => 1
            ]
        ];
        $mPostData = json_encode($mReqParams);
        return $mPostData;
    }

    private function cpGetAvalByHotelReqParamObj($pReqParams)
    {
        $mReqParams = $this->cpGetBasicAvalReqParamObj($pReqParams);
        if(isset($pReqParams["geolocation"])){
            $mReqParams["geolocation"] = $pReqParams["geolocation"];
        }
        else if(isset($pReqParams["hotel_codes"])) {
            $mReqParams["hotels"] = [
                "hotel" => $pReqParams["hotel_codes"]
            ];
        }
        $mReqParams["reviews"] = [
            [
                "type" => "TRIPADVISOR",
                "maxRate" => 5,
                "minRate" => 1
            ]
        ];
        // else if(){

        // }
        // $mReqParams["filter"] = array("maxHotels" => 10); // To be removed
        $mPostData = json_encode($mReqParams);
        return $mPostData;
    }

    private function cpGetHBProcBkgReqParamObj($pReqParams)
    {
        $mReqParams = array();
        $mReqParams["holder"] = [
            "name" => $pReqParams["holder_name"],
            "surname" => $pReqParams["holder_surname"]
        ];
        $mReqParams["clientReference"] =  $pReqParams["client_reference"];
        if (isset($pReqParams["remark"])) {
            $mReqParams["remark"] = $pReqParams["remark"];
        }
        if (isset($pReqParams["tolerance"])) {
            $mReqParams["tolerance"] = $pReqParams["tolerance"];
        }
        $mReqParams["rooms"] = array();
        foreach ($pReqParams["rooms"] as $room) {
            $item = [
                "rateKey" => $room["rate_key"],
                "paxes" => array_map(function ($value) {
                    return [
                        'roomId' => $value["room_id"],
                        'type' => $value["type"],
                        'age' => $value["age"] ?? 0,
                        'name' => $value["name"],
                        'surname' => $value["surname"]
                    ];
                }, $room["pax"])
            ];
            array_push($mReqParams["rooms"], $item);
        }
        $mPostData = json_encode($mReqParams);
        return $mPostData;
    }
}
