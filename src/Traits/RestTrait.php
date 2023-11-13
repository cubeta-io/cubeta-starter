<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use JetBrains\PhpStorm\ArrayShape;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\ApiController;

trait RestTrait
{
    /**
     * this function will determine the api response structure to make all responses has the same structure
     *
     * @param null $data
     * @param null $message
     * @param null $paginate
     */
    public function apiResponse($data = null, int $code = 200, $message = null, $paginate = null): JsonResponse
    {
        $arrayResponse = [
            'data' => $data,
            'status' => $code == 200 || $code == 201 || $code == 204 || $code == 205,
            'message' => $message,
            'code' => $code,
            'paginate' => $paginate,
        ];

        return response()->json($arrayResponse, $code, [], JSON_PRETTY_PRINT);
    }

    /**
     * to handle validations
     */
    public function apiValidation($request, $array): JsonResponse|array
    {
        $validator = Validator::make($request->all(), $array);
        if ($validator->fails()) {
            $msg = [
                'text' => 'the given data is invalid',
                'errors' => $validator->errors(),
            ];

            return $this->apiResponse(null, ApiController::STATUS_VALIDATION, $msg);
        }

        return $validator->valid();
    }

    /**
     * standard for pagination
     */
    #[ArrayShape(['currentPage' => 'mixed', 'from' => 'mixed', 'to' => 'mixed', 'total' => 'mixed', 'per_page' => 'mixed'])]
    public function formatPaginateData($data): array
    {
        $paginated_arr = $data->toArray();

        return [
            'currentPage' => $paginated_arr['current_page'],
            'from' => $paginated_arr['from'],
            'to' => $paginated_arr['to'],
            'total' => $paginated_arr['total'],
            'per_page' => $paginated_arr['per_page'],
        ];
    }

    /**
     * @param mixed $response
     * @return JsonResponse
     */
    public function noData(mixed $response): JsonResponse
    {
        return $this->apiResponse($response, ApiController::STATUS_OK, __('site.there_is_no_data'));
    }
}
