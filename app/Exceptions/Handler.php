<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

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
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function render($request, Exception $exception)
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
                'error' => config('xpb.errors')[$exception->getStatusCode()],
                'message' => $exception->getMessage()
            ], $exception->getStatusCode());
        }

        return parent::render($request, $exception);
    }
}
