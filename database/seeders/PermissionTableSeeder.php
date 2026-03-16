<?php

    namespace Database\Seeders;

    use App\Enums\Role as EnumRole;
    use App\Libraries\AppLibrary;
    use Illuminate\Database\Seeder;
    use Illuminate\Support\Str;
    use Spatie\Permission\Models\Permission;
    use Spatie\Permission\Models\Role;

    class PermissionTableSeeder extends Seeder
    {
        public function run() : void
        {
            $data = [
                [
                    'group' => 'Dashboard' ,
                    'items' => [
                        [ 'title' => 'Dashboard' , 'name' => 'dashboard' , 'url' => '/' , 'items' => [] ]
                    ]
                ] ,
                [
                    'group' => 'Sales' ,
                    'items' => [
                        [ 'title' => 'POS' , 'name' => 'pos' , 'url' => 'pos' , 'items' => [] ] ,
                        [ 'title' => 'Add Sale' , 'name' => 'add_sale' , 'url' => 'addsale' , 'items' => [] ] ,
                        [
                            'title' => 'Quotation' ,
                            'name'  => 'quotation' ,
                            'url'   => 'quotation' ,
                            'items' => [
                                [ 'title' => 'Quotation Create' , 'name' => 'quotation_create' , 'url' => 'quotation/create' , 'items' => [] ] ,
                                [ 'title' => 'Quotation Edit' , 'name' => 'quotation_edit' , 'url' => 'quotation/edit' , 'items' => [] ] ,
                                [ 'title' => 'Quotation Delete' , 'name' => 'quotation_delete' , 'url' => 'quotation/delete' , 'items' => [] ] ,
                                [ 'title' => 'Quotation Show' , 'name' => 'quotation_show' , 'url' => 'quotation/show' , 'items' => [] ] ,
                            ]
                        ] ,
                        [ 'title' => 'Sales' , 'name' => 'sales' , 'url' => 'salesorders' , 'items' => [] ] ,
                        [ 'title' => 'Credit Sales' , 'name' => 'credit_sales' , 'url' => 'salesorders/credit' , 'items' => [] ] ,
                        [ 'title' => 'Deposited Sales' , 'name' => 'deposited_sales' , 'url' => 'salesorders/deposited' , 'items' => [] ] ,
                        [ 'title' => 'Pre-Orders' , 'name' => 'pre_orders' , 'url' => 'salesorders/preorders' , 'items' => [] ] ,
                        [ 'title' => 'Sales Returns' , 'name' => 'sales_returns' , 'url' => 'salesorders/salesreturns' , 'items' => [] ]
                    ]
                ] ,
                [
                    'group' => 'Commission' ,
                    'items' => [
                        [ 'title' => 'Commission' , 'name' => 'commission' , 'url' => 'commission' , 'items' => [] ]
                    ]
                ] ,
                [
                    'group' => 'Services' ,
                    'items' => [
                        [
                            'title' => 'Services' ,
                            'name'  => 'services' ,
                            'url'   => 'services' ,
                            'items' => [
                                [ 'title' => 'Service Create' , 'name' => 'service_create' , 'url' => 'services/create' , 'items' => [] ] ,
                                [ 'title' => 'Service Edit' , 'name' => 'service_edit' , 'url' => 'services/edit' , 'items' => [] ] ,
                                [ 'title' => 'Service Delete' , 'name' => 'service_delete' , 'url' => 'services/delete' , 'items' => [] ] ,
                            ]
                        ]
                    ]
                ] ,
                [
                    'group' => 'Customers' ,
                    'items' => [
                        [
                            'title' => 'Customer List' ,
                            'name'  => 'customer_list' ,
                            'url'   => 'customers' ,
                            'items' => [
                                [ 'title' => 'Customer List Create' , 'name' => 'customer_list_create' , 'url' => 'customers/create' , 'items' => [] ] ,
                                [ 'title' => 'Customer List Edit' , 'name' => 'customer_list_edit' , 'url' => 'customers/edit' , 'items' => [] ] ,
                                [ 'title' => 'Customer List Delete' , 'name' => 'customer_list_delete' , 'url' => 'customers/delete' , 'items' => [] ] ,
                            ]
                        ] ,
                        [ 'title' => 'Loyalty / Membership' , 'name' => 'loyalty_membership' , 'url' => 'customers/loyalty' , 'items' => [] ]
                    ]
                ] ,
                [
                    'group' => 'Inventory' ,
                    'items' => [
                        [
                            'title' => 'Inventory' ,
                            'name'  => 'inventory' ,
                            'url'   => 'inventory' ,
                            'items' => [
                                [ 'title' => 'Inventory Create' , 'name' => 'inventory_create' , 'url' => 'inventory/create' , 'items' => [] ] ,
                                [ 'title' => 'Inventory Edit' , 'name' => 'inventory_edit' , 'url' => 'inventory/edit' , 'items' => [] ] ,
                                [ 'title' => 'Inventory Delete' , 'name' => 'inventory_delete' , 'url' => 'inventory/delete' , 'items' => [] ] ,
                                [ 'title' => 'Inventory Show' , 'name' => 'inventory_show' , 'url' => 'inventory/show' , 'items' => [] ] ,
                            ]
                        ]
                    ]
                ] ,
                [
                    'group' => 'Distribution' ,
                    'items' => [
                        [
                            'title' => 'Distribution' ,
                            'name'  => 'distribution' ,
                            'url'   => 'distribution' ,
                            'items' => [
                                [ 'title' => 'Distribution Create' , 'name' => 'distribution_create' , 'url' => 'distribution/create' , 'items' => [] ] ,
                                [ 'title' => 'Distribution Edit' , 'name' => 'distribution_edit' , 'url' => 'distribution/edit' , 'items' => [] ] ,
                                [ 'title' => 'Distribution Delete' , 'name' => 'distribution_delete' , 'url' => 'distribution/delete' , 'items' => [] ] ,
                            ]
                        ]
                    ]
                ] ,
                [
                    'group' => 'Stock' ,
                    'items' => [
                        [
                            'title' => 'Stock' ,
                            'name'  => 'stock' ,
                            'url'   => 'stock' ,
                            'items' => [
                                [ 'title' => 'Stock Create' , 'name' => 'stock_create' , 'url' => 'stock/create' , 'items' => [] ] ,
                                [ 'title' => 'Stock Edit' , 'name' => 'stock_edit' , 'url' => 'stock/edit' , 'items' => [] ] ,
                                [ 'title' => 'Stock Delete' , 'name' => 'stock_delete' , 'url' => 'stock/delete' , 'items' => [] ] ,
                                [ 'title' => 'Stock Show' , 'name' => 'stock_show' , 'url' => 'stock/show' , 'items' => [] ] ,
                            ]
                        ]
                    ]
                ] ,
                [
                    'group' => 'Warehouse & Storage' ,
                    'items' => [
                        [
                            'title' => 'Warehouse & Storage' ,
                            'name'  => 'warehouse_storage' ,
                            'url'   => 'warehouses&storage' ,
                            'items' => [
                                [ 'title' => 'Warehouse & Storage Create' , 'name' => 'warehouse_storage_create' , 'url' => 'warehouses&storage/create' , 'items' => [] ] ,
                                [ 'title' => 'Warehouse & Storage Edit' , 'name' => 'warehouse_storage_edit' , 'url' => 'warehouses&storage/edit' , 'items' => [] ] ,
                                [ 'title' => 'Warehouse & Storage Delete' , 'name' => 'warehouse_storage_delete' , 'url' => 'warehouses&storage/delete' , 'items' => [] ] ,
                                [ 'title' => 'Warehouse & Storage Show' , 'name' => 'warehouse_storage_show' , 'url' => 'warehouses&storage/show' , 'items' => [] ] ,
                            ]
                        ]
                    ]
                ] ,
                [
                    'group' => 'Procurement' ,
                    'items' => [
                        [
                            'title' => 'Procurement' ,
                            'name'  => 'procurement' ,
                            'url'   => 'procurement' ,
                            'items' => [
                                [ 'title' => 'Procurement Create' , 'name' => 'procurement_create' , 'url' => 'procurement/create' , 'items' => [] ] ,
                                [ 'title' => 'Procurement Edit' , 'name' => 'procurement_edit' , 'url' => 'procurement/edit' , 'items' => [] ] ,
                                [ 'title' => 'Procurement Delete' , 'name' => 'procurement_delete' , 'url' => 'procurement/delete' , 'items' => [] ] ,
                            ]
                        ]
                    ]
                ] ,
                [
                    'group' => 'Production' ,
                    'items' => array_map( fn($item) => [
                        'title' => $item ,
                        'name'  => strtolower( str_replace( ' ' , '_' , $item ) ) ,
                        'url'   => strtolower( str_replace( ' ' , '-' , $item ) ) ,
                        'items' => ( $item !== 'Production Output' ) ? [
                            [ 'title' => "$item Create" , 'name' => strtolower( str_replace( ' ' , '_' , $item ) ) . '_create' , 'url' => strtolower( str_replace( ' ' , '-' , $item ) ) . '/create' , 'items' => [] ] ,
                            [ 'title' => "$item Edit" , 'name' => strtolower( str_replace( ' ' , '_' , $item ) ) . '_edit' , 'url' => strtolower( str_replace( ' ' , '-' , $item ) ) . '/edit' , 'items' => [] ] ,
                            [ 'title' => "$item Delete" , 'name' => strtolower( str_replace( ' ' , '_' , $item ) ) . '_delete' , 'url' => strtolower( str_replace( ' ' , '-' , $item ) ) . '/delete' , 'items' => [] ] ,
                        ] : []
                    ] , [ 'Raw Materials' , 'Machinery' , 'Production Setup' , 'Production Processes' , 'Production Output' ] )
                ] ,
                [
                    'group' => 'Projects' ,
                    'items' => array_map( fn($item) => [
                        'title' => $item[ 'title' ] ,
                        'name'  => strtolower( str_replace( ' ' , '_' , $item[ 'title' ] ) ) ,
                        'url'   => $item[ 'url' ] ,
                        'items' => [
                            [ 'title' => "{$item['title']} Create" , 'name' => strtolower( str_replace( ' ' , '_' , $item[ 'title' ] ) ) . '_create' , 'url' => "{$item['url']}/create" , 'items' => [] ] ,
                            [ 'title' => "{$item['title']} Edit" , 'name' => strtolower( str_replace( ' ' , '_' , $item[ 'title' ] ) ) . '_edit' , 'url' => "{$item['url']}/edit" , 'items' => [] ] ,
                            [ 'title' => "{$item['title']} Delete" , 'name' => strtolower( str_replace( ' ' , '_' , $item[ 'title' ] ) ) . '_delete' , 'url' => "{$item['url']}/delete" , 'items' => [] ] ,
                        ]
                    ] , [
                        [ 'title' => 'Projects List' , 'url' => 'projects/list' ] ,
                        [ 'title' => 'Tasks' , 'url' => 'projects/tasks' ] ,
                        [ 'title' => 'Timesheets' , 'url' => 'projects/timesheets' ] ,
                        [ 'title' => 'Settings' , 'url' => 'projects/settings' ]
                    ] )
                ] ,
                [
                    'group' => 'Expenses' ,
                    'items' => [
                        [
                            'title' => 'Expenses' ,
                            'name'  => 'expenses' ,
                            'url'   => 'expenses' ,
                            'items' => [
                                [ 'title' => 'Expenses Create' , 'name' => 'expenses_create' , 'url' => 'expenses/create' , 'items' => [] ] ,
                                [ 'title' => 'Expenses Edit' , 'name' => 'expenses_edit' , 'url' => 'expenses/edit' , 'items' => [] ] ,
                                [ 'title' => 'Expenses Delete' , 'name' => 'expenses_delete' , 'url' => 'expenses/delete' , 'items' => [] ] ,
                            ]
                        ]
                    ]
                ] ,
                [
                    'group' => 'Branches' ,
                    'items' => [
                        [
                            'title' => 'Branches' ,
                            'name'  => 'branches' ,
                            'url'   => 'branches' ,
                            'items' => [
                                [ 'title' => 'Branch Create' , 'name' => 'branch_create' , 'url' => 'branches/create' , 'items' => [] ] ,
                                [ 'title' => 'Branch Edit' , 'name' => 'branch_edit' , 'url' => 'branches/edit' , 'items' => [] ] ,
                                [ 'title' => 'Branch Delete' , 'name' => 'branch_delete' , 'url' => 'branches/delete' , 'items' => [] ] ,
                            ]
                        ]
                    ]
                ] ,
                [
                    'group' => 'Asset Management' ,
                    'items' => [
                        [ 'title' => 'Asset Management' , 'name' => 'asset_management' , 'url' => 'assets' , 'items' => [] ]
                    ]
                ] ,
                [
                    'group' => 'Accounting' ,
                    'items' => array_map( fn($item) => [
                        'title' => $item[ 'title' ] ,
                        'name'  => strtolower( str_replace( ' ' , '_' , $item[ 'title' ] ) ) ,
                        'url'   => $item[ 'url' ] ,
                        'items' => [
                            [ 'title' => "{$item['title']} Create" , 'name' => strtolower( str_replace( ' ' , '_' , $item[ 'title' ] ) ) . '_create' , 'url' => "{$item['url']}/create" , 'items' => [] ] ,
                            [ 'title' => "{$item['title']} Edit" , 'name' => strtolower( str_replace( ' ' , '_' , $item[ 'title' ] ) ) . '_edit' , 'url' => "{$item['url']}/edit" , 'items' => [] ] ,
                            [ 'title' => "{$item['title']} Delete" , 'name' => strtolower( str_replace( ' ' , '_' , $item[ 'title' ] ) ) . '_delete' , 'url' => "{$item['url']}/delete" , 'items' => [] ] ,
                        ]
                    ] , [
                        [ 'title' => 'Transactions' , 'url' => 'transactions' ] ,
                        [ 'title' => 'Chart of Accounts' , 'url' => 'chart-of-accounts' ] ,
                        [ 'title' => 'Journal Entry' , 'url' => 'journal-entries' ] ,
                        [ 'title' => 'Settings' , 'url' => 'accounting-settings' ]
                    ] )
                ] ,
                [
                    'group' => 'HR Mgt' ,
                    'items' => array_map( fn($item) => [
                        'title' => $item[ 'title' ] ,
                        'name'  => strtolower( str_replace( ' ' , '_' , $item[ 'title' ] ) ) ,
                        'url'   => $item[ 'url' ] ,
                        'items' => [
                            [ 'title' => "{$item['title']} Create" , 'name' => strtolower( str_replace( ' ' , '_' , $item[ 'title' ] ) ) . '_create' , 'url' => "{$item['url']}/create" , 'items' => [] ] ,
                            [ 'title' => "{$item['title']} Edit" , 'name' => strtolower( str_replace( ' ' , '_' , $item[ 'title' ] ) ) . '_edit' , 'url' => "{$item['url']}/edit" , 'items' => [] ] ,
                            [ 'title' => "{$item['title']} Delete" , 'name' => strtolower( str_replace( ' ' , '_' , $item[ 'title' ] ) ) . '_delete' , 'url' => "{$item['url']}/delete" , 'items' => [] ] ,
                        ]
                    ] , [
                        [ 'title' => 'Employee Mgt' , 'url' => 'hr/employees' ] ,
                        [ 'title' => 'Payroll' , 'url' => 'hr/payroll' ] ,
                        [ 'title' => 'Leave Mgt' , 'url' => 'hr/leave' ] ,
                        [ 'title' => 'Recruitment' , 'url' => 'hr/recruitment' ] ,
                        [ 'title' => 'Performance' , 'url' => 'hr/performance' ]
                    ] )
                ] ,
                [
                    'group' => 'Users' ,
                    'items' => array_map( fn($item) => [
                        'title' => $item[ 'title' ] ,
                        'name'  => strtolower( str_replace( ' ' , '_' , $item[ 'title' ] ) ) ,
                        'url'   => $item[ 'url' ] ,
                        'items' => [
                            [ 'title' => "{$item['title']} Create" , 'name' => strtolower( str_replace( ' ' , '_' , $item[ 'title' ] ) ) . '_create' , 'url' => "{$item['url']}/create" , 'items' => [] ] ,
                            [ 'title' => "{$item['title']} Edit" , 'name' => strtolower( str_replace( ' ' , '_' , $item[ 'title' ] ) ) . '_edit' , 'url' => "{$item['url']}/edit" , 'items' => [] ] ,
                            [ 'title' => "{$item['title']} Delete" , 'name' => strtolower( str_replace( ' ' , '_' , $item[ 'title' ] ) ) . '_delete' , 'url' => "{$item['url']}/delete" , 'items' => [] ] ,
                        ]
                    ] , [
                        [ 'title' => 'Administrators' , 'url' => 'administrators' ] ,
                        [ 'title' => 'Employees' , 'url' => 'employees' ]
                    ] )
                ] ,
                [
                    'group' => 'Reports' ,
                    'items' => array_map( fn($item) => [ 'title' => $item[ 'title' ] , 'name' => strtolower( str_replace( ' ' , '_' , $item[ 'title' ] ) ) , 'url' => $item[ 'url' ] , 'items' => [] ] , [
                        [ 'title' => 'Sales Reports' , 'url' => 'salesreports' ] ,
                        [ 'title' => 'Inventory Reports' , 'url' => 'inventoryreports' ] ,
                        [ 'title' => 'Production Reports' , 'url' => 'productionreports' ] ,
                        [ 'title' => 'Procurement Reports' , 'url' => 'procurementreports' ] ,
                        [ 'title' => 'Accounting Reports' , 'url' => 'accountingreports' ] ,
                        [ 'title' => 'Expenses Reports' , 'url' => 'expensesreports' ] ,
                        [ 'title' => 'HR Reports' , 'url' => 'hrreports' ] ,
                        [ 'title' => 'Product Reports' , 'url' => 'productsreports' ]
                    ] )
                ] ,
                [
                    'group' => 'Subscriptions' ,
                    'items' => [
                        [ 'title' => 'Subscriptions' , 'name' => 'subscriptions' , 'url' => 'subscriptions' , 'items' => [] ]
                    ]
                ] ,
                [
                    'group' => 'Activity Logs' ,
                    'items' => array_map( fn($item) => [ 'title' => $item[ 'title' ] , 'name' => strtolower( str_replace( ' ' , '_' , $item[ 'title' ] ) ) , 'url' => $item[ 'url' ] , 'items' => [] ] , [
                        [ 'title' => 'Audit Trails' , 'url' => 'audit-trails' ] ,
                        [ 'title' => 'Transaction Logs' , 'url' => 'transactions' ]
                    ] )
                ] ,
                [
                    'group' => 'Modules' ,
                    'items' => [
                        [ 'title' => 'Modules' , 'name' => 'modules' , 'url' => 'modules' , 'items' => [] ]
                    ]
                ] ,
                [
                    'group' => 'Settings' ,
                    'items' => [
                        [ 'title' => 'Settings' , 'name' => 'settings' , 'url' => 'settings' , 'items' => [] ]
                    ]
                ]
            ];

            $permissions = [];
            $now         = now();

            // First, process all parent/group permissions
            foreach ( $data as $group ) {
                $groupName = Str::slug( $group[ 'group' ] , '_' );
                $groupUrl  = Str::slug( $group[ 'group' ] , '-' );

                $parentPermission = [
                    'title'      => $group[ 'group' ] ,
                    'name'       => $groupName ,
                    'guard_name' => 'sanctum' ,
                    'url'        => $groupUrl ,
                    'created_at' => $now ,
                    'updated_at' => $now ,
                    'children'   => []
                ];

                foreach ( $group[ 'items' ] as $item ) {
                    // Prepare item structure for children
                    $itemNode = [
                        'title'      => $item[ 'title' ] ,
                        'name'       => $item[ 'name' ] ,
                        'guard_name' => 'sanctum' ,
                        'url'        => $item[ 'url' ] ,
                        'created_at' => $now ,
                        'updated_at' => $now ,
                    ];

                    // If this item has deep CRUD items (recursive items)
                    if ( ! empty( $item[ 'items' ] ) ) {
                        $itemNode[ 'children' ] = array_map( function ($subItem) use ($now) {
                            return [
                                'title'      => $subItem[ 'title' ] ,
                                'name'       => $subItem[ 'name' ] ,
                                'guard_name' => 'sanctum' ,
                                'url'        => $subItem[ 'url' ] ,
                                'created_at' => $now ,
                                'updated_at' => $now ,
                            ];
                        } , $item[ 'items' ] );
                    }

                    $parentPermission[ 'children' ][] = $itemNode;
                }

                $permissions[] = $parentPermission;
            }

//            $flattenedPermissions = AppLibrary::recursiveFlattenPermissions( $permissions );
//
//            foreach ( $flattenedPermissions as $perm ) {
//                Permission::firstOrCreate(
//                    [ 'name' => $perm[ 'name' ] , 'guard_name' => $perm[ 'guard_name' ] ] ,
//                    $perm
//                );
//            }
//
//            $adminRole = Role::where( 'name' , EnumRole::ADMIN )->first();
//            if ( $adminRole ) {
//                $allPermissions = Permission::all();
//                $adminRole->syncPermissions( $allPermissions );
//            }

            $flattenedPermissions = AppLibrary::recursiveFlattenPermissions( $permissions );

            $definedPermNames = array_column( $flattenedPermissions , 'name' );

            $existingPermNames = Permission::where( 'guard_name' , 'sanctum' )->pluck( 'name' )->toArray();

            $missingPerms = array_diff( $definedPermNames , $existingPermNames );

            if ( empty( $missingPerms ) ) {
                return;
            }

            foreach ( $flattenedPermissions as $perm ) {
                if ( in_array( $perm[ 'name' ] , $missingPerms ) ) {
                    Permission::firstOrCreate(
                        [ 'name' => $perm[ 'name' ] , 'guard_name' => $perm[ 'guard_name' ] ] ,
                        $perm
                    );
                }
            }

            $adminRole = Role::where( 'name' , EnumRole::ADMIN )->first();
            if ( $adminRole ) {
                $adminRole->givePermissionTo( $missingPerms );
            }
        }
    }