<?php

    use App\Http\Middleware\AddCurrencySymbol;
    use App\Http\Middleware\AfterMiddleware;
    use App\Http\Middleware\CheckActiveRegister;
    use App\Http\Middleware\ForceAdminLogin;
    use App\Http\Middleware\PermissionMiddleware;
    use Illuminate\Foundation\Application;
    use Illuminate\Foundation\Configuration\Exceptions;
    use Illuminate\Foundation\Configuration\Middleware;
    use Stancl\Tenancy\Contracts\TenantCouldNotBeIdentifiedException;
    use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
    use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

    return Application::configure( basePath: dirname( __DIR__ ) )
                      ->withRouting(
                          web: __DIR__ . '/../routes/web.php' ,
                          api: __DIR__ . '/../routes/api.php' ,
                          commands: __DIR__ . '/../routes/console.php' ,
                          health: '/up' ,
                      )
                      ->withBroadcasting(
                          __DIR__ . '/../routes/channels.php' ,
                          [
                              'prefix'     => 'api' ,
                              'middleware' => [
                                  'api' ,
                                  InitializeTenancyByDomain::class ,
                                  PreventAccessFromCentralDomains::class ,
                                  'auth:sanctum'
                              ] ,
                          ]
                      )
                      ->withMiddleware( function (Middleware $middleware) : void {
                          $middleware->alias( [
                              'permission'      => PermissionMiddleware::class ,
                              'local.auth'      => ForceAdminLogin::class ,
                              'register'        => CheckActiveRegister::class ,
                              'afterMiddleware' => AfterMiddleware::class ,
                          ] );
                          $middleware->append( [
                              AddCurrencySymbol::class ,
                              AfterMiddleware::class
                          ] );
                      } )
                      ->withExceptions( function (Exceptions $exceptions) : void {

                          $exceptions->render( function (Illuminate\Auth\Access\AuthorizationException $e , $request) {
                              return response()->json( [
                                  'success' => FALSE ,
                                  'status'  => 403 ,
                                  'message' => [ 'message' => 'User does not have the right permissions.' , 'details' => $e->getMessage() ]
                              ] , 403 );
                          } );

                          $exceptions->render( function (TenantCouldNotBeIdentifiedException $e , $request) {
                              return response()->json( [
                                  'success' => FALSE ,
                                  'status'  => 400 ,
                                  'message' => [ 'message' => 'Tenant error' , 'details' => $e->getMessage() ]
                              ] , 400 );
                          } );

                          $exceptions->render( function (Illuminate\Database\Eloquent\ModelNotFoundException $e , $request) {
                              return response()->json( [
                                  'success' => FALSE ,
                                  'status'  => 404 ,
                                  'message' => [ 'message' => 'No query results for model.' , 'details' => $e->getMessage() ]
                              ] , 404 );
                          } );

                          $exceptions->render( function (Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException $e , $request) {
                              return response()->json( [
                                  'success' => FALSE ,
                                  'status'  => 405 ,
                                  'message' => [ 'message' => 'Method not supported for the route.' , 'details' => $e->getMessage() ]
                              ] , 405 );
                          } );

                          $exceptions->render( function (Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e , $request) {
                              return response()->json( [
                                  'success' => FALSE ,
                                  'status'  => 404 ,
                                  'message' => [ 'message' => 'The specified URL cannot be found.' , 'details' => $e->getMessage() ]
                              ] , 404 );
                          } );

                          $exceptions->render( function (Symfony\Component\HttpKernel\Exception\HttpException $e , $request) {
                              return response()->json( [
                                  'success' => FALSE ,
                                  'status'  => $e->getStatusCode() ,
                                  'message' => [ 'message' => 'Http exception' , 'details' => $e->getMessage() ]
                              ] , $e->getStatusCode() );
                          } );

                          $exceptions->render( function (Illuminate\Database\QueryException $e , $request) {
                              return response()->json( [
                                  'success' => FALSE ,
                                  'status'  => 500 ,
                                  'message' => [ 'message' => 'Query exception' , 'details' => $e->getMessage() ]
                              ] , 500 );
                          } );

                      } )->create();
