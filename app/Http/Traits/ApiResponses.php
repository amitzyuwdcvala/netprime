<?php

namespace App\Http\Traits;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

trait ApiResponses
{
    /**
     * Success Response.
     */
    public function successResponse(mixed $data, int $statusCode = Response::HTTP_OK): JsonResponse
    {
        $request = Request::instance();
        if ($request->attributes->get('route') === 'api') {
            $data = ['status' => true] + $data;
        } elseif ($request->attributes->get('route') === 'web') {
            $data = ['status' => 'success'] + $data;
        }
        return new JsonResponse($data, $statusCode);
    }

    /**
     * Error Response.
     */
    public function errorResponse(mixed $data, string $message = '', int $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR): JsonResponse
    {
        if (!$message) {
            $message = Response::$statusTexts[$statusCode] ?? 'Error';
        }

        $request = Request::instance();
        if ($request->attributes->get('route') === 'api') {
            $data = [
                'status' => false,
                'message' => $message,
            ];
        } elseif ($request->attributes->get('route') === 'web') {
            $data = [
                'status' => 'error',
                'message' => $message,
            ];
        }
        return new JsonResponse($data, $statusCode);
    }

    /**
     * Response with status code 200.
     */
    public function okResponse(mixed $data): JsonResponse
    {
        return $this->successResponse($data);
    }

    /**
     * Response with status code 201.
     */
    public function createdResponse(mixed $data): JsonResponse
    {
        return $this->successResponse($data, Response::HTTP_CREATED);
    }

    /**
     * Response with status code 204.
     */
    public function noContentResponse(): JsonResponse
    {
        return $this->successResponse([], Response::HTTP_NO_CONTENT);
    }

    /**
     * Response with status code 400.
     */
    public function badRequestResponse(mixed $data, string $message = ''): JsonResponse
    {
        return $this->errorResponse($data, $message, Response::HTTP_BAD_REQUEST);
    }

    /**
     * Response with status code 401.
     */
    public function unauthorizedResponse(mixed $data, string $message = ''): JsonResponse
    {
        return $this->errorResponse($data, $message, Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Response with status code 403.
     */
    public function forbiddenResponse(mixed $data, string $message = ''): JsonResponse
    {
        return $this->errorResponse($data, $message, Response::HTTP_FORBIDDEN);
    }

    /**
     * Response with status code 404.
     */
    public function notFoundResponse(mixed $data, string $message = ''): JsonResponse
    {
        return $this->errorResponse($data, $message, Response::HTTP_NOT_FOUND);
    }

    /**
     * Response with status code 409.
     */
    public function conflictResponse(mixed $data, string $message = ''): JsonResponse
    {
        return $this->errorResponse($data, $message, Response::HTTP_CONFLICT);
    }

    /**
     * Response with status code 422.
     */
    public function unprocessableResponse(mixed $data, string $message = ''): JsonResponse
    {
        return $this->errorResponse($data, $message, Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * Response with status code 422 for only Request class in web routes.
     */
    public function customError($message, $httpStatusCode = Response::HTTP_UNPROCESSABLE_ENTITY)
    {
        $response = response()->json([
            'status' => 'validation_error',
            'message' => $message,
        ], $httpStatusCode);
        $validator = Validator::make([], []);
        throw new ValidationException($validator, $response);
    }

    /**
     * Response with status code 422 for only Request class in API routes.
     */
    public function customErrorAPI($message, $httpStatusCode = Response::HTTP_UNPROCESSABLE_ENTITY)
    {
        $response = $this->errorResponse([], $message, $httpStatusCode);
        throw new HttpResponseException($response);
    }
}
