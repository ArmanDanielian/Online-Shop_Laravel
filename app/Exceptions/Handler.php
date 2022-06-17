<?php
namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\ViewErrorBag;
use Illuminate\Validation\ValidationException;
use Str;
use Throwable;
class Handler extends ExceptionHandler
{
    /**
     * @inheritdoc
     */
    public function render($request, Throwable $exception)
    {
        if (Str::startsWith(request()->path(), 'api')) {
            $response = [];
            if ($exception instanceof ModelNotFoundException) {
                $statusCode = 404;
                $response['message'] = 'Item you are searching does not exist';
            } elseif ($exception instanceof \Illuminate\Database\QueryException ) {
                $statusCode = 500;
                $response['message'] = 'Whoops, looks like something went wrong';
            } elseif (method_exists($exception, 'getStatusCode')) {
                $statusCode = $exception->getStatusCode();
            } elseif (property_exists($exception, 'status')) {
                $statusCode = $exception->status;
            } elseif (method_exists($exception, 'getCode')) {
                $statusCode = $exception->getCode();
                if ($statusCode === 0) {
                    $statusCode = 500;
                }
            } else {
                $statusCode = 500;
            }
            if ($exception instanceof AuthorizationException && $statusCode === 500) {
                $statusCode = 401;
            }
            switch ($statusCode) {
                case 401:
                    $response['message'] = $exception->getMessage();
                    if (empty($response['message'])) {
                        $response['message'] = 'Unauthorized';
                    }
                    break;
                case 403:
                    $response['message'] = 'Forbidden';
                    break;
                case 404:
                    if (empty($response['message'])) {
                        $response['message'] = 'Not Found';
                    }
                    break;
                case 405:
                    $response['message'] = 'Method Not Allowed';
                    break;
                case 422:
                    $response['message'] = $exception->getMessage();
                    if ($exception instanceof ValidationException) {
                        $response['errors'] = $exception->validator->errors()->messages();
                    }
                    break;
                default:
                    $response['message'] = ($statusCode == 500)
                        ? 'Whoops, looks like something went wrong'
                        : $exception->getMessage();
                    $statusCode = 500;
                    break;
            }
            $response['status'] = $statusCode;
            if (config('app.debug')) {
//                $response['code'] = $exception->getCode();
//                $response['trace'] = $exception->getTrace();
            }
            return response()->json($response, $statusCode);
        }
        if ($exception instanceof TokenMismatchException && $request->expectsJson()) {
            return response()->json([
                trans('messages.csrf_token_mismatch')
            ], 419);
        }
        return parent::render($request, $exception);
    }
    /**
     * @inheritdoc
     */
    protected function convertExceptionToResponse(Throwable $exception)
    {
        if (method_exists($exception, 'getStatusCode')) {
            $statusCode = $exception->getStatusCode();
        } elseif (property_exists($exception, 'status')) {
            $statusCode = $exception->status;
        } else {
            $statusCode = 500;
        }
        if (method_exists($exception, 'getHeaders')) {
            $headers = $exception->getHeaders();
        } else {
            $headers = [];
        }
        if (!in_array($statusCode, [400, 401, 403, 404, 405, 419, 429, 500, 503], false)) {
            if ($statusCode < 500) {
                return response()->view('errors.400', [
                    'errors'    => new ViewErrorBag,
                    'exception' => $exception,
                ], $statusCode, $headers);
            }
            return response()->view('errors.500', [
                'errors'    => new ViewErrorBag,
                'exception' => $exception,
            ], $statusCode, $headers);
        }
        return parent::convertExceptionToResponse($exception);
    }

    public function report(Throwable $exception)
    {
        if(app()->bound('sentry') && $this->shouldReport($exception)){
            app('sentry')->captureException($exception);
        }
        parent::report($exception);
    }
}
