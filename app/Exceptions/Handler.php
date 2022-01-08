<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        if ($exception instanceof NotFoundHttpException || $exception instanceof MethodNotAllowedHttpException)
        {
            $response = [
                'status' => 'error',
                'code' => 404,
                'error' => config('xpb.errors.404'),
                'message' => $exception->getMessage()
            ];

            $content = json_encode($response, JSON_PRETTY_PRINT);
            $syntax = 'json';
    
            return response(view('paste', compact('content', 'syntax')));
        }

        if ($exception instanceof HttpException)
        {
            return response()->json([
                'status' => 'error',
                'code' => $exception->getStatusCode(),
                'error' => config('xpb.errors')[$exception->getStatusCode()] ?? 'Error ' . $exception->getStatusCode(),
                'message' => $exception->getMessage()
            ], $exception->getStatusCode());
        }

        return parent::render($request, $exception);
    }
}
