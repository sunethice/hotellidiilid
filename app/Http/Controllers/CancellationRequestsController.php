<?php

namespace App\Http\Controllers;

use App\Http\Enums\CancelRequestStatus;
use App\Http\Repository\Contracts\IBookingRepository;
use App\Http\Repository\Contracts\ICancelRequestRepository;
use App\Http\Traits\JsonResponseTrait;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CancellationRequestsController extends Controller
{
    use JsonResponseTrait;
    private $cIBookingRepository;
    private $cCancelRequestRepository;
    public function __construct(IBookingRepository $pIBookingRepository, ICancelRequestRepository $pCancelRequestRepository)
    {
        $this->cIBookingRepository = $pIBookingRepository;
        $this->cCancelRequestRepository = $pCancelRequestRepository;
    }

    public function cpRequestCancellation(Request $request){
        $mValidator = Validator::make($request->all(),[
            'reference'=>'required|string'
        ]);
        if($mValidator->fails()){
            return $this->cpFailureResponse(422,$mValidator->fails());
        }
        try{
            $mRequested = $this->cIBookingRepository->cpRequestCancellation($request['reference']);
            if($mRequested == null){
                return $this->cpFailureResponse(500,"Cancellation request could not be submitted.");
            }
            return $this->cpSuccessResponse("Cancellation request submitted successfully.");
        }
        catch(QueryException $pEx){
            return $this->cpFailureResponse($pEx->getCode(), $pEx->getMessage(), $pEx);
        }
    }

    public function cpListCancellationRequests(Request $request){
        $mValidator = Validator::make($request->all(),[
            'status'=>'string'
        ]);
        if($mValidator->fails()){
            return $this->cpFailureResponse(422,$mValidator->fails());
        }
        try{
            $mRequests = $this->cCancelRequestRepository->cpListCancellationRequests($request['status']??CancelRequestStatus::ALL);
            return $this->cpResponseWithResults($mRequests,"Cancellation requests listed successfully.");
        }
        catch(QueryException $pEx){
            return $this->cpFailureResponse($pEx->getCode(), $pEx->getMessage(), $pEx);
        }
    }
}
