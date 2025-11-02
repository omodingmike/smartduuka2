<?php

    namespace Database\Seeders;

    use App\Libraries\AppLibrary;
    use App\Models\Menu;
    use Illuminate\Database\Seeder;

    class MenuTableSeeder extends Seeder
    {
        public function run() : void
        {
            $menus = [
                [
                    'name'       => 'Dashboard' ,
                    'language'   => 'dashboard' ,
                    'url'        => 'dashboard' ,
                    'icon'       => 'lab lab-line-dashboard' ,
                    'priority'   => 100 ,
                    'svg'        => NULL ,
                    'status'     => 1 ,
                    'created_at' => now() ,
                    'updated_at' => now()
                ] ,
                [
                    'name'       => 'Sales' ,
                    'language'   => 'sales' ,
                    'url'        => '#' ,
                    'icon'       => 'lab lab-pos' ,
                    'priority'   => 100 ,
                    'svg'        => NULL ,
                    'status'     => 1 ,
                    'created_at' => now() ,
                    'updated_at' => now() ,
                    'children'   => [
                        [
                            'name'       => 'POS' ,
                            'url'        => 'pos' ,
                            'language'   => 'pos' ,
                            'icon'       => 'lab lab-line-pos' ,
                            'priority'   => 100 ,
                            'svg'        => NULL ,
                            'status'     => 1 ,
                            'created_at' => now() ,
                            'updated_at' => now()

                        ] ,
                        [
                            'name'       => 'Add Sale' ,
                            'url'        => 'sales/add' ,
                            'language'   => 'pos' ,
                            'icon'       => 'lab lab-line-pos' ,
                            'priority'   => 100 ,
                            'svg'        => NULL ,
                            'status'     => 1 ,
                            'created_at' => now() ,
                            'updated_at' => now()
                        ] ,
                        [
                            'name'       => 'Quotations' ,
                            'url'        => 'quotation' ,
                            'language'   => 'quotation' ,
                            'icon'       => 'lab lab-line-pos' ,
                            'priority'   => 100 ,
                            'svg'        => NULL ,
                            'status'     => 1 ,
                            'created_at' => now() ,
                            'updated_at' => now()
                        ]
                        ,

                        [
                            'name'       => 'Orders & Sales' ,
                            'language'   => 'pos_orders' ,
                            'url'        => 'pos-orders' ,
                            'icon'       => 'lab lab-line-push-notification' ,
                            'priority'   => 100 ,
                            'svg'        => NULL ,
                            'status'     => 1 ,
                            'created_at' => now() ,
                            'updated_at' => now()
                        ] ,
                        [
                            'name'       => 'Credit Orders' ,
                            'language'   => 'pos_credit_orders' ,
                            'url'        => 'pos-orders/credit' ,
                            'icon'       => 'lab lab-line-orders' ,
                            'priority'   => 100 ,
                            'svg'        => NULL ,
                            'status'     => 1 ,
                            'created_at' => now() ,
                            'updated_at' => now()
                        ] ,
                        [
                            'name'       => 'Deposited Orders' ,
                            'language'   => 'pos_deposit_orders' ,
                            'url'        => 'pos-orders/deposit' ,
                            'icon'       => 'lab lab-line-pages' ,
                            'priority'   => 100 ,
                            'svg'        => NULL ,
                            'status'     => 1 ,
                            'created_at' => now() ,
                            'updated_at' => now()
                        ]
                    ] ,
                ] ,
                [
                    'name'       => 'Distribution Hub' ,
                    'language'   => 'distribution_hub' ,
                    'url'        => '#' ,
                    'icon'       => 'lab lab-pos' ,
                    'priority'   => 100 ,
                    'svg'        => NULL ,
                    'status'     => 1 ,
                    'created_at' => now() ,
                    'updated_at' => now() ,
                    'children'   => [
                        [
                            'name'       => 'Distribution Routes' ,
                            'url'        => 'distribution-routes' ,
                            'language'   => 'pos' ,
                            'icon'       => 'lab lab-line-pos' ,
                            'priority'   => 100 ,
                            'svg'        => NULL ,
                            'status'     => 1 ,
                            'created_at' => now() ,
                            'updated_at' => now()
                        ] ,
                        [
                            'name'       => 'Truck Stock' ,
                            'url'        => 'distribution-routes/truck-stock' ,
                            'language'   => 'pos' ,
                            'icon'       => 'lab lab-line-pos' ,
                            'priority'   => 100 ,
                            'svg'        => NULL ,
                            'status'     => 1 ,
                            'created_at' => now() ,
                            'updated_at' => now()
                        ]
                    ]
                ] ,
                [
                    'name'       => 'Commission' ,
                    'language'   => 'commission' ,
                    'url'        => '#' ,
                    'icon'       => 'lab lab-pos' ,
                    'priority'   => 100 ,
                    'svg'        => NULL ,
                    'status'     => 1 ,
                    'created_at' => now() ,
                    'updated_at' => now() ,
                    'children'   => [
                        [
                            'name'       => 'Commission Rules' ,
                            'url'        => 'distribution-routes/commission' ,
                            'language'   => 'pos' ,
                            'icon'       => 'lab lab-line-pos' ,
                            'priority'   => 100 ,
                            'svg'        => NULL ,
                            'status'     => 1 ,
                            'created_at' => now() ,
                            'updated_at' => now()
                        ] ,
                        [
                            'name'       => 'Commission Summary' ,
                            'url'        => 'distribution-routes/commission-summary' ,
                            'language'   => 'pos' ,
                            'icon'       => 'lab lab-line-pos' ,
                            'priority'   => 100 ,
                            'svg'        => NULL ,
                            'status'     => 1 ,
                            'created_at' => now() ,
                            'updated_at' => now()
                        ] , [
                            'name'       => 'Commission Payout' ,
                            'url'        => 'distribution-routes/commission-payout' ,
                            'language'   => 'pos' ,
                            'icon'       => 'lab lab-line-pos' ,
                            'priority'   => 100 ,
                            'svg'        => NULL ,
                            'status'     => 1 ,
                            'created_at' => now() ,
                            'updated_at' => now()
                        ] ,
                    ]
                ] ,
                [
                    'name'       => 'Accounting' ,
                    'language'   => 'accounting' ,
                    'url'        => '#' ,
                    'icon'       => 'lab lab-pos' ,
                    'priority'   => 100 ,
                    'svg'        => NULL ,
                    'status'     => 1 ,
                    'created_at' => now() ,
                    'updated_at' => now() ,
                    'children'   => [
                        [
                            'name'       => 'Payment Accounts' ,
                            'url'        => 'accounting/payment_accounts' ,
                            'language'   => 'payment_accounts' ,
                            'icon'       => 'lab lab-line-pos' ,
                            'priority'   => 100 ,
                            'svg'        => NULL ,
                            'status'     => 1 ,
                            'created_at' => now() ,
                            'updated_at' => now()

                        ] ,
                        [
                            'name'       => 'Chart of Accounts' ,
                            'url'        => 'accounting/chart_of_accounts' ,
                            'language'   => 'chart_of_accounts' ,
                            'icon'       => 'lab lab-line-pos' ,
                            'priority'   => 100 ,
                            'svg'        => NULL ,
                            'status'     => 1 ,
                            'created_at' => now() ,
                            'updated_at' => now()
                        ] ,
                        [
                            'name'       => 'Journal Entries' ,
                            'language'   => 'journal_entries' ,
                            'url'        => 'accounting/journal_entries' ,
                            'icon'       => 'lab lab-line-push-notification' ,
                            'priority'   => 100 ,
                            'svg'        => NULL ,
                            'status'     => 1 ,
                            'created_at' => now() ,
                            'updated_at' => now()
                        ] ,
                    ] ,
                ] ,

                [
                    'name'       => 'Products' ,
                    'language'   => 'pos_and_orders' ,
                    'url'        => '#' ,
                    'icon'       => 'lab lab-pos' ,
                    'priority'   => 100 ,
                    'svg'        => NULL ,
                    'status'     => 1 ,
                    'created_at' => now() ,
                    'updated_at' => now() ,
                    'children'   => [
                        [
                            'name'       => 'Products List' ,
                            'language'   => 'products' ,
                            'url'        => 'products' ,
                            'icon'       => 'lab lab-line-items' ,
                            'priority'   => 100 ,
                            'svg'        => NULL ,
                            'status'     => 1 ,
                            'created_at' => now() ,
                            'updated_at' => now()
                        ] ,
                        [
                            'name'       => 'Product Settings' ,
                            'language'   => 'product_settings' ,
                            'url'        => 'product' ,
                            'icon'       => 'lab lab-line-items' ,
                            'priority'   => 100 ,
                            'svg'        => NULL ,
                            'status'     => 1 ,
                            'created_at' => now() ,
                            'updated_at' => now()
                        ] ,
                    ] ,
                ] ,
                [
                    'name'       => 'Inventory' ,
                    'language'   => 'pos_and_orders' ,
                    'url'        => '#' ,
                    'icon'       => 'lab lab-pos' ,
                    'priority'   => 100 ,
                    'svg'        => NULL ,
                    'status'     => 1 ,
                    'created_at' => now() ,
                    'updated_at' => now() ,
                    'children'   => [
                        [
                            'name'       => 'Stock' ,
                            'url'        => 'stock' ,
                            'language'   => 'stock' ,
                            'icon'       => 'lab lab-line-stock' ,
                            'priority'   => 100 ,
                            'svg'        => NULL ,
                            'status'     => 1 ,
                            'created_at' => now() ,
                            'updated_at' => now()
                        ] ,
                        [
                            'name'       => 'Stock Reconciliation' ,
                            'url'        => 'stock_reconciliation' ,
                            'language'   => 'stock_reconciliation' ,
                            'icon'       => 'lab lab-line-stock' ,
                            'priority'   => 100 ,
                            'svg'        => NULL ,
                            'status'     => 1 ,
                            'created_at' => now() ,
                            'updated_at' => now()
                        ] ,
                        [
                            'name'       => 'Stock Damages' ,
                            'url'        => 'damages' ,
                            'language'   => 'damages' ,
                            'svg'        => NULL ,
                            'icon'       => 'lab lab-line-addons' ,
                            'priority'   => 100 ,
                            'status'     => 1 ,
                            'created_at' => now() ,
                            'updated_at' => now()
                        ] ,
                    ] ,
                ] ,

                [
                    'name'       => 'Warehouse & Storage' ,
                    'language'   => 'warehouse_and_storage' ,
                    'url'        => '#' ,
                    'icon'       => 'lab' ,
                    'priority'   => 100 ,
                    'svg'        => NULL ,
                    'status'     => 1 ,
                    'created_at' => now() ,
                    'updated_at' => now() ,
                    'children'   => [
                        [
                            'name'       => 'Warehouse' ,
                            'language'   => 'warehouses_list' ,
                            'url'        => 'warehouses' ,
                            'icon'       => 'lab lab-pos-orders' ,
                            'svg'        => 'ingredient.svg' ,
                            'priority'   => 100 ,
                            'status'     => 1 ,
                            'created_at' => now() ,
                            'updated_at' => now()
                        ] ,
                        [
                            'name'       => 'Stock Requests' ,
                            'url'        => 'stock_requests' ,
                            'language'   => 'purchase' ,
                            'icon'       => 'lab lab-line-add-purchase' ,
                            'priority'   => 100 ,
                            'svg'        => NULL ,
                            'status'     => 1 ,
                            'created_at' => now() ,
                            'updated_at' => now()
                        ] ,
                        [
                            'name'       => 'Stock Transfers' ,
                            'url'        => 'stock_transfers' ,
                            'language'   => 'stock_transfers' ,
                            'icon'       => 'lab lab-line-add-purchase' ,
                            'priority'   => 100 ,
                            'svg'        => NULL ,
                            'status'     => 1 ,
                            'created_at' => now() ,
                            'updated_at' => now()
                        ] ,
                    ] ,
                ] ,

                [
                    'name'       => 'Production' ,
                    'language'   => 'warehouse_and_storage' ,
                    'url'        => '#' ,
                    'icon'       => 'lab' ,
                    'priority'   => 100 ,
                    'svg'        => NULL ,
                    'status'     => 1 ,
                    'created_at' => now() ,
                    'updated_at' => now() ,
                    'children'   => [
                        [
                            'name'       => 'Raw Materials' ,
                            'url'        => 'ingredients_and_stock' ,
                            'language'   => 'purchase' ,
                            'icon'       => 'lab lab-line-add-purchase' ,
                            'priority'   => 100 ,
                            'svg'        => 'Ingredient_purchase.svg' ,
                            'status'     => 1 ,
                            'created_at' => now() ,
                            'updated_at' => now()
                        ] ,
                        [
                            'name'       => 'Production Setup' ,
                            'language'   => 'warehouses_list' ,
                            'url'        => 'production/setup' ,
                            'icon'       => 'lab lab-pos-orders' ,
                            'svg'        => 'ingredient.svg' ,
                            'priority'   => 100 ,
                            'status'     => 1 ,
                            'created_at' => now() ,
                            'updated_at' => now()
                        ] ,
                        [
                            'name'       => 'Production Processes' ,
                            'url'        => 'production/production' ,
                            'language'   => 'purchase' ,
                            'icon'       => 'lab lab-line-add-purchase' ,
                            'priority'   => 100 ,
                            'svg'        => NULL ,
                            'status'     => 1 ,
                            'created_at' => now() ,
                            'updated_at' => now()
                        ] ,
                        [
                            'name'       => 'Production Results' ,
                            'url'        => 'production/output' ,
                            'language'   => 'purchase' ,
                            'icon'       => 'lab lab-line-add-purchase' ,
                            'priority'   => 100 ,
                            'svg'        => NULL ,
                            'status'     => 1 ,
                            'created_at' => now() ,
                            'updated_at' => now()
                        ] ,
                    ] ,
                ] ,

                [
                    'name'       => 'Purchases' ,
                    'language'   => 'purchases' ,
                    'url'        => '#' ,
                    'icon'       => 'lab' ,
                    'priority'   => 100 ,
                    'svg'        => NULL ,
                    'status'     => 1 ,
                    'created_at' => now() ,
                    'updated_at' => now() ,
                    'children'   => [
                        [
                            'name'       => 'Suppliers' ,
                            'language'   => 'suppliers' ,
                            'url'        => 'suppliers' ,
                            'icon'       => 'lab lab-line-supplier' ,
                            'svg'        => 'ingredient.svg' ,
                            'priority'   => 100 ,
                            'status'     => 1 ,
                            'created_at' => now() ,
                            'updated_at' => now()
                        ] ,
                        [
                            'name'       => 'Stock Purchases' ,
                            'url'        => 'purchase' ,
                            'language'   => 'purchase' ,
                            'icon'       => 'lab lab-line-add-purchase' ,
                            'priority'   => 100 ,
                            'svg'        => NULL ,
                            'status'     => 1 ,
                            'created_at' => now() ,
                            'updated_at' => now()
                        ] ,
                        [
                            'name'       => 'Raw Materials Purchases' ,
                            'url'        => 'raw_material_purchase' ,
                            'language'   => 'raw_material_purchase' ,
                            'icon'       => 'lab lab-line-add-purchase' ,
                            'priority'   => 100 ,
                            'svg'        => 'Ingredient_purchase.svg' ,
                            'status'     => 1 ,
                            'created_at' => now() ,
                            'updated_at' => now()
                        ] ,
                        [
                            'name'       => 'Purchase Returns' ,
                            'url'        => 'purchase_returns' ,
                            'language'   => 'purchase' ,
                            'icon'       => 'lab lab-line-add-purchase' ,
                            'priority'   => 100 ,
                            'svg'        => 'Ingredient_purchase.svg' ,
                            'status'     => 1 ,
                            'created_at' => now() ,
                            'updated_at' => now()
                        ] ,
                    ] ,
                ] ,

                [
                    'name'       => 'Expenses' ,
                    'language'   => 'expenses' ,
                    'url'        => '#' ,
                    'svg'        => NULL ,
                    'icon'       => 'lab lab-item' ,
                    'priority'   => 100 ,
                    'status'     => 1 ,
                    'created_at' => now() ,
                    'updated_at' => now() ,
                    'children'   => [
                        [
                            'name'       => 'Categories' ,
                            'language'   => 'categories' ,
                            'url'        => 'categories' ,
                            'icon'       => 'lab lab-line-items' ,
                            'priority'   => 100 ,
                            'svg'        => 'expenses_category.svg' ,
                            'status'     => 1 ,
                            'created_at' => now() ,
                            'updated_at' => now()

                        ] ,
                        [
                            'name'       => 'Expenses' ,
                            'url'        => 'expenses' ,
                            'language'   => 'expenses' ,
                            'icon'       => 'lab lab-line-add-purchase' ,
                            'priority'   => 100 ,
                            'svg'        => 'expenses.svg' ,
                            'status'     => 1 ,
                            'created_at' => now() ,
                            'updated_at' => now()
                        ] ,
                    ]
                ] ,

                [
                    'name'       => 'Users/People' ,
                    'language'   => 'users' ,
                    'url'        => '#' ,
                    'icon'       => 'lab ' ,
                    'priority'   => 100 ,
                    'svg'        => NULL ,
                    'status'     => 1 ,
                    'created_at' => now() ,
                    'updated_at' => now() ,
                    'children'   => [
                        [
                            'name'       => 'Administrators' ,
                            'language'   => 'administrators' ,
                            'url'        => 'administrators' ,
                            'icon'       => 'lab lab-line-administrator' ,
                            'priority'   => 100 ,
                            'svg'        => NULL ,
                            'status'     => 1 ,
                            'created_at' => now() ,
                            'updated_at' => now()
                        ] ,
                        [
                            'name'       => 'Customers' ,
                            'language'   => 'customers' ,
                            'url'        => 'customers' ,
                            'icon'       => 'lab lab-line-cunstomers' ,
                            'priority'   => 100 ,
                            'svg'        => NULL ,
                            'status'     => 1 ,
                            'created_at' => now() ,
                            'updated_at' => now()
                        ] ,
                        [
                            'name'       => 'Employees' ,
                            'language'   => 'employees' ,
                            'url'        => 'employees' ,
                            'icon'       => 'lab lab-line-users' ,
                            'priority'   => 100 ,
                            'svg'        => NULL ,
                            'status'     => 1 ,
                            'created_at' => now() ,
                            'updated_at' => now()
                        ] ,
                    ]
                ] ,


                [
                    'name'       => 'Reports' ,
                    'language'   => 'reports' ,
                    'url'        => '#' ,
                    'icon'       => 'lab ' ,
                    'svg'        => NULL ,
                    'priority'   => 100 ,
                    'status'     => 1 ,
                    'created_at' => now() ,
                    'updated_at' => now() ,
                    'children'   => [
                        [
                            'name'       => 'Sales Report' ,
                            'language'   => 'sales_report' ,
                            'url'        => 'sales-report' ,
                            'svg'        => NULL ,
                            'icon'       => 'lab lab-line-sales-report' ,
                            'priority'   => 100 ,
                            'status'     => 1 ,
                            'created_at' => now() ,
                            'updated_at' => now()

                        ] ,
                        [
                            'name'       => 'Stock Expiry Report' ,
                            'language'   => 'stock_expiry_report' ,
                            'url'        => 'stock-expiry-report' ,
                            'svg'        => NULL ,
                            'icon'       => 'lab lab-line-sales-report' ,
                            'priority'   => 100 ,
                            'status'     => 1 ,
                            'created_at' => now() ,
                            'updated_at' => now()
                        ] ,
                        [
                            'name'       => 'Products Report' ,
                            'language'   => 'products_report' ,
                            'url'        => 'products-report' ,
                            'icon'       => 'lab lab-line-items-report' ,
                            'priority'   => 100 ,
                            'svg'        => NULL ,
                            'status'     => 1 ,
                            'created_at' => now() ,
                            'updated_at' => now()
                        ] ,
                        [
                            'name'       => 'Production Report' ,
                            'language'   => 'production_report' ,
                            'url'        => 'production-report' ,
                            'icon'       => 'lab lab-line-items-report' ,
                            'priority'   => 100 ,
                            'status'     => 1 ,
                            'svg'        => NULL ,
                            'created_at' => now() ,
                            'updated_at' => now()
                        ] ,
                    ]
                ] ,

                [
                    'name'       => 'Payments' ,
                    'language'   => 'subscriptions' ,
                    'url'        => '#' ,
                    'icon'       => 'lab ' ,
                    'svg'        => NULL ,
                    'priority'   => 100 ,
                    'status'     => 1 ,
                    'created_at' => now() ,
                    'updated_at' => now() ,
                    'children'   => [
                        [
                            'name'       => 'Subscriptions' ,
                            'language'   => 'subscriptions' ,
                            'url'        => 'subscriptions' ,
                            'icon'       => 'lab lab-line-settings' ,
                            'priority'   => 100 ,
                            'svg'        => NULL ,
                            'status'     => 1 ,
                            'created_at' => now() ,
                            'updated_at' => now()
                        ]
                    ]
                ] ,
                [
                    'name'       => 'Activity Logs' ,
                    'language'   => 'activity_logs' ,
                    'url'        => '#' ,
                    'icon'       => 'lab ' ,
                    'svg'        => NULL ,
                    'priority'   => 100 ,
                    'status'     => 1 ,
                    'created_at' => now() ,
                    'updated_at' => now() ,
                    'children'   => [
                        [
                            'name'       => 'Activity Logs' ,
                            'language'   => 'activity_logs' ,
                            'url'        => 'activity_logs' ,
                            'icon'       => 'lab lab-line-settings' ,
                            'priority'   => 100 ,
                            'svg'        => NULL ,
                            'status'     => 1 ,
                            'created_at' => now() ,
                            'updated_at' => now()
                        ]
                    ]
                ] ,
                [
                    'name'       => 'Setup' ,
                    'language'   => 'setup' ,
                    'url'        => '#' ,
                    'icon'       => 'lab ' ,
                    'priority'   => 100 ,
                    'svg'        => NULL ,
                    'status'     => 1 ,
                    'created_at' => now() ,
                    'updated_at' => now() ,
                    'children'   => [
                        [
                            'name'       => 'Settings' ,
                            'language'   => 'settings' ,
                            'url'        => 'settings' ,
                            'icon'       => 'lab lab-line-settings' ,
                            'priority'   => 100 ,
                            'svg'        => NULL ,
                            'status'     => 1 ,
                            'created_at' => now() ,
                            'updated_at' => now()
                        ]
                    ]
                ]
            ];

            Menu::insert( AppLibrary::associativeToNumericArrayBuilder( $menus ) );
        }
    }
