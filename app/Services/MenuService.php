<?php

    namespace App\Services;

    use App\Enums\Modules;
    use App\Libraries\AppLibrary;
    use App\Models\Menu;
    use Exception;
    use Illuminate\Support\Facades\Log;
    use Smartisan\Settings\Facades\Settings;
    use Spatie\Permission\Models\Permission;
    use Spatie\Permission\Models\Role;

    class MenuService
    {
        /**
         * @throws Exception
         */
        public function menu(Role $role) : array
        {
            try {
                $module_warehouse = Settings::group('module')->get('module_warehouse');
                $menus            = Menu::when($module_warehouse == 0 , function ($query) {
                    $query->where('name' , '!=' , 'Warehouse & Storage');
                })
                                        ->when(! moduleEnabled('accounting') , function ($query) {
                                            $query->where('name' , '!=' , 'Accounting');
                                        })
                                        ->when(! moduleEnabled(Modules::DISTRIBUTION) , function ($query) {
                                            $query->where('name' , '!=' , 'Distribution Hub');
                                        })
                                        ->when(! moduleEnabled(Modules::COMMISSION) , function ($query) {
                                            $query->where('name' , '!=' , 'Commission');
                                        })
                                        ->when(( config('system.quotations') == false ) , function ($query) {
                                            $query->where('name' , '!=' , 'Quotations');
                                        })
                                        ->when(! moduleEnabled('production') , function ($query) {
                                            $query->where('name' , '!=' , 'Production');
                                        })->get()->toArray();
                $permissions      = Permission::get();
                $rolePermissions  = Permission::join(
                    "role_has_permissions" ,
                    "role_has_permissions.permission_id" ,
                    "=" ,
                    "permissions.id"
                )->where("role_has_permissions.role_id" , $role->id)->get()->pluck('name' , 'id');
                $permissions      = AppLibrary::permissionWithAccess($permissions , $rolePermissions);
                $permissions      = AppLibrary::pluck($permissions , 'obj' , 'url');
                return AppLibrary::numericToAssociativeArrayBuilder(AppLibrary::menu($menus , $permissions));
            } catch ( Exception $exception ) {
                Log::info($exception->getMessage());
                throw new Exception($exception->getMessage() , 422);
            }
        }
    }
