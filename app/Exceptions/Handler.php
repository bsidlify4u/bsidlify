<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Illuminate\Encryption\MissingAppKeyException;
use Symfony\Component\Console\Output\ConsoleOutput;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            // Here you can integrate with external error reporting services
            // like Sentry, Bugsnag, or Flare
            // \Sentry\captureException($e);
        });
        
        // Handle MissingAppKeyException with a more helpful error message
        $this->renderable(function (MissingAppKeyException $e) {
            $isConsole = app()->runningInConsole();
            
            if ($isConsole) {
                $output = new ConsoleOutput();
                $output->writeln('<bg=red;fg=white>                                                                 </>');
                $output->writeln('<bg=red;fg=white>   APPLICATION KEY MISSING                                       </>');
                $output->writeln('<bg=red;fg=white>                                                                 </>');
                $output->writeln('');
                $output->writeln('<fg=red>The application encryption key (APP_KEY) is not set.</>');
                $output->writeln('');
                $output->writeln('<fg=green>Run the following command to generate a new key:</>');
                $output->writeln('    <fg=yellow>php bsidlify key:generate</>');
                $output->writeln('');
                $output->writeln('<fg=green>If you don\'t have a .env file, create one first:</>');
                $output->writeln('    <fg=yellow>cp .env.example .env</>');
                $output->writeln('    <fg=yellow>php bsidlify key:generate</>');
                
                exit(1);
            }
            
            // For HTTP requests
            if (request()->expectsJson()) {
                return response()->json([
                    'message' => 'Application key is missing. Run "php bsidlify key:generate".',
                    'status' => 'error'
                ], 500);
            }
            
            return response()->view('errors.app-key-missing', [], 500);
        });
        
        // Handle ModelNotFoundException
        $this->renderable(function (ModelNotFoundException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Resource not found.',
                    'status' => 'error'
                ], 404);
            }
        });
        
        // Handle NotFoundHttpException
        $this->renderable(function (NotFoundHttpException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'The requested URL was not found.',
                    'status' => 'error'
                ], 404);
            }
        });
        
        // Handle ValidationException
        $this->renderable(function (ValidationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'The given data is invalid.',
                    'errors' => $e->errors(),
                    'status' => 'error'
                ], 422);
            }
        });
        
        // Handle ThrottleRequestsException
        $this->renderable(function (ThrottleRequestsException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Too many requests. Please try again later.',
                    'status' => 'error'
                ], 429);
            }
        });
        
        // Handle MethodNotAllowedHttpException
        $this->renderable(function (MethodNotAllowedHttpException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'The HTTP method used is not allowed for this resource.',
                    'status' => 'error'
                ], 405);
            }
        });
        
        // Handle AuthenticationException
        $this->renderable(function (AuthenticationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Unauthenticated.',
                    'status' => 'error'
                ], 401);
            }
        });
    }
} 