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
    public function successResponse(mixed $data, int $statusCode = Response::HTTP_OK): JsonResponse
    {
        $request = Request::instance();
        if ($request->attributes->get('route') === 'api') {
            $data = ['status' => true] + $data;
        } else {
            $data = ['status' => 'success'] + $data;
        }
        return new JsonResponse($data, $statusCode);
    }

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
        } else {
            $data = [
                'status' => 'error',
                'message' => $message,
            ];
        }
        return new JsonResponse($data, $statusCode);
    }

    public function okResponse(mixed $data): JsonResponse
    {
        return $this->successResponse($data);
    }

    public function createdResponse(mixed $data): JsonResponse
    {
        return $this->successResponse($data, Response::HTTP_CREATED);
    }

    public function noContentResponse(): JsonResponse
    {
        return $this->successResponse([], Response::HTTP_NO_CONTENT);
    }

    public function badRequestResponse(mixed $data, string $message = ''): JsonResponse
    {
        return $this->errorResponse($data, $message, Response::HTTP_BAD_REQUEST);
    }

    public function unauthorizedResponse(mixed $data, string $message = ''): JsonResponse
    {
        return $this->errorResponse($data, $message, Response::HTTP_UNAUTHORIZED);
    }

    public function forbiddenResponse(mixed $data, string $message = ''): JsonResponse
    {
        return $this->errorResponse($data, $message, Response::HTTP_FORBIDDEN);
    }

    public function notFoundResponse(mixed $data, string $message = ''): JsonResponse
    {
        return $this->errorResponse($data, $message, Response::HTTP_NOT_FOUND);
    }

    public function conflictResponse(mixed $data, string $message = ''): JsonResponse
    {
        return $this->errorResponse($data, $message, Response::HTTP_CONFLICT);
    }

    public function unprocessableResponse(mixed $data, string $message = ''): JsonResponse
    {
        return $this->errorResponse($data, $message, Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function customError($message, $httpStatusCode = Response::HTTP_UNPROCESSABLE_ENTITY)
    {
        $response = response()->json([
            'status' => 'validation_error',
            'message' => $message,
        ], $httpStatusCode);
        $validator = Validator::make([], []);
        throw new ValidationException($validator, $response);
    }

    public function customErrorAPI($message, $httpStatusCode = Response::HTTP_UNPROCESSABLE_ENTITY)
    {
        $response = $this->errorResponse([], $message, $httpStatusCode);
        throw new HttpResponseException($response);
    }
}
