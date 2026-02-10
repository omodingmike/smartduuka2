<?php

    use App\Http\Middleware\AddCurrencySymbol;
    use App\Http\Middleware\CheckActiveRegister;
    use App\Http\Middleware\ForceAdminLogin;
    use App\Http\Middleware\PermissionMiddleware;
    use App\Jobs\SendExceptionJob;
    use Illuminate\Auth\AuthenticationException;
    use Illuminate\Foundation\Application;
    use Illuminate\Foundation\Configuration\Exceptions;
    use Illuminate\Foundation\Configuration\Middleware;
    use Illuminate\Validation\ValidationException;
    use Stancl\Tenancy\Exceptions\TenantCouldNotBeIdentifiedOnDomainException;

    return Application::configure( basePath: dirname( __DIR__ ) )
                      ->withRouting(
                          web: __DIR__ . '/../routes/web.php' ,
                          api: __DIR__ . '/../routes/api.php' ,
                          commands: __DIR__ . '/../routes/console.php' ,
                          channels: __DIR__ . '/../routes/channels.php' ,
                          health: '/up' ,
                      )
                      ->withMiddleware( function (Middleware $middleware) : void {
                          $middleware->alias( [
                              'permission' => PermissionMiddleware::class ,
                              'local.auth' => ForceAdminLogin::class ,
                              'register'   => CheckActiveRegister::class ,
                          ] );
                          $middleware->append( [
                              AddCurrencySymbol::class
                          ] );
                          $middleware->statefulApi();
                      } )
                      ->withExceptions( function (Exceptions $exceptions) : void {
//                          $exceptions->reportable( function (Throwable $e) {
//                              if (
//                                  app()->isProduction() &&
//                                  ! in_array( get_class( $e ) , [
//                                      ValidationException::class ,
//                                      AuthenticationException::class ,
//                                  ] )
//                              ) {
//                                  SendExceptionJob::dispatchException( $e );
//                              }
//                          } );
                          $exceptions->render( function (Illuminate\Auth\Access\AuthorizationException $e , $request) {
                              return response()->json( [
                                  'success' => FALSE ,
                                  'message' => [ 'message' => 'User does not have the right permissions.' , 'details' => $e->getMessage() ]
                              ] , 403 );
                          } );
                          $exceptions->render( function (TenantCouldNotBeIdentifiedOnDomainException $e , $request) {
                              return response()->json( [
                                  'success' => FALSE ,
                                  'message' => [ 'message' => 'Tenant error' , 'details' => $e->getMessage() ]
                              ] , 403 );
                          } );

                          // Handle model not found
                          $exceptions->render( function (Illuminate\Database\Eloquent\ModelNotFoundException $e , $request) {
                              return response()->json( [
                                  'success' => FALSE ,
                                  'message' => [ 'message' => 'No query results for model.' , 'details' => $e->getMessage() ]
                              ] , 404 );
                          } );

                          // Handle method not allowed
                          $exceptions->render( function (Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException $e , $request) {
                              return response()->json( [
                                  'success' => FALSE ,
                                  'message' => [ 'message' => 'Method not supported for the route.' , 'details' => $e->getMessage() ]
                              ] , 405 );
                          } );

                          // Handle not found (invalid route)
                          $exceptions->render( function (Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e , $request) {
                              return response()->json( [
                                  'success' => FALSE ,
                                  'message' => [ 'message' => 'The specified URL cannot be found.' , 'details' => $e->getMessage() ]
                              ] , 404 );
                          } );

                          // Handle general HTTP exceptions
                          $exceptions->render( function (Symfony\Component\HttpKernel\Exception\HttpException $e , $request) {
                              return response()->json( [
                                  'success' => FALSE ,
                                  'message' => [ 'message' => 'Http exception' , 'details' => $e->getMessage() ]
                              ] , 422 );
                          } );

                          // Handle query exceptions
                          $exceptions->render( function (Illuminate\Database\QueryException $e , $request) {
                              return response()->json( [
                                  'success' => FALSE ,
                                  'message' => [ 'message' => 'Query exception' , 'details' => $e->getMessage() ]
                              ] , 422 );
                          } );

                          // Optional: log for debugging (like your `reportable` callback)
                          $exceptions->report( function (Throwable $e) {
                              info( "{$e->getFile()} line {$e->getLine()}" );
                              info( $e->getTraceAsString() );
                          } );
                      } )->create();
