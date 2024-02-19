<?php

namespace App\Http\Repository\Contracts;

interface ICancelRequestRepository
{
    public function cpRequest($pReference);
    public function cpUpdateStatus($pReference, $pStatus);
    public function cpListCancellationRequests();
}
