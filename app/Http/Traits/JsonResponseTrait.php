<?php

namespace App\Http\Traits;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Log;

trait JsonResponseTrait
{

    protected function cpResponse($pData = [], int $pStatus = 200, $pHeaders = [])
    {
        $responseStructure = [
            'pSuccess' => $pData['success'],
            'pMessage' => $pData['message'] ?? null,
            'pResultObj' => $pData['result'] ?? null,
        ];
        if (isset($pData['errors'])) {
            $responseStructure['errors'] = $pData['errors'];
        }

        if (isset($pData['status'])) {
            $pStatus = $pData['status'];
        }

        if (isset($pData['exception']) && ($pData['exception'] instanceof \Error || $pData['exception'] instanceof \Exception)) {
            if (config('app.env') !== 'production') {
                $responseStructure['exception'] = [
                    'message' => $pData['exception']->getMessage(),
                    'file' => $pData['exception']->getFile(),
                    'line' => $pData['exception']->getLine(),
                    'code' => $pData['exception']->getCode(),
                    'trace' => $pData['exception']->getTrace(),
                ];
            }
            if ($pStatus === 200) {

                $pStatus = 500;
            }
        }
        if ($pData['success'] === false) {
            if (isset($pData['error_code'])) {
                $responseStructure['error_code'] = $pData['error_code'];
            } else {
                $responseStructure['error_code'] = 1;
            }
        }

        return response()->json(
            $responseStructure,
            $pStatus,
            $pHeaders
        );
    }

    protected function cpResponseWithResults($pResource, $pMessage, $pStatus = 200, $pHeaders = [])
    {
        return $this->cpResponse([
            'success' => true,
            'message' => $pMessage,
            'result' => $pResource
        ], $pStatus, $pHeaders);
    }

    protected function cpResponseWithResultCollection(ResourceCollection $pResourceCollection, $pMessage, $pStatus = 200, $pHeaders = [])
    {
        return $this->cpResponse([
            'success' => true,
            'message' => $pMessage,
            'result' => $pResourceCollection->response()->getData()
        ], $pStatus, $pHeaders);
    }

    protected function cpSuccessResponse($pMessage)
    {
        return $this->cpResponse(['success' => true, 'message' => $pMessage]);
    }

    protected function cpFailureResponse(int $pStatus, $pMessage = null, \Exception $pException = null, $pErrorCode = null)
    {
        return $this->cpResponse([
            'success' => false,
            'message' => $pMessage,
            'exception' => $pException,
            'error_code' => $pErrorCode
        ], $pStatus);
    }
}
