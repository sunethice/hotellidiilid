<?php

namespace App\Http\Repository\MongoRepository;

use App\Http\Enums\CancelRequestStatus;
use App\Http\Repository\Contracts\ICancelRequestRepository;
use App\Http\Traits\HBApiTrait;
use App\Models\CancellationRequests;
use Illuminate\Support\Facades\Log;

class CancelRequestRepository extends BaseRepository implements ICancelRequestRepository
{
    use HBApiTrait;
    public function __construct(CancellationRequests $model)
    {
        parent::__construct($model);
    }

    public function cpRequest($pReference){
        $mRequest = [];
        $mRequest['reference'] = $pReference;
        $mRequest['status'] = CancelRequestStatus::PENDING;
        $mSaved = $this->model->create($mRequest);
        if (!$mSaved) {
            return null;
        }
        return $mRequest;
    }

    public function cpUpdateStatus($pReference, $pStatus){
        $mRequest = $this->model->where('reference',$pReference)->first();
        if(isset($mRequest)){
            $mRequest['status'] = $pStatus;
            $mUpdated = $mRequest->save();
            return $mUpdated;
        }
        else
            return null;
    }

    public function cpListCancellationRequests($pRequestStatus = CancelRequestStatus::ALL){
        $mRequests = [];
        if($pRequestStatus == CancelRequestStatus::ALL){
            $mRequests = $this->model->all();
        }
        else{
            $mRequests = $this->model->where('status',$pRequestStatus)->get();
        }
        return $mRequests;
    }
}
