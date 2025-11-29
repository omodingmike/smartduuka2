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
                    'url'        => 'admin' ,
                    'icon'       => 'LayoutDashboard' ,
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
                    'icon'       => 'ChartLine' ,
                    'priority'   => 100 ,
                    'svg'        => NULL ,
                    'status'     => 1 ,
                    'created_at' => now() ,
                    'updated_at' => now() ,
                    'children'   => [
                        [
                            'name'       => 'POS' ,
                            'url'        => 'admin/sales/pos' ,
                            'language'   => 'pos' ,
                            'icon'       => 'lab lab-line-pos' ,
                            'priority'   => 100 ,
                            'svg'        => NULL ,
                            'status'     => 1 ,
                            'created_at' => now() ,
                            'updated_at' => now()

                        ] ,
//                        [
//                            'name'       => 'Add Sale' ,
//                            'url'        => 'add' ,
//                            'language'   => 'pos' ,
//                            'icon'       => 'lab lab-line-pos' ,
//                            'priority'   => 100 ,
//                            'svg'        => NULL ,
//                            'status'     => 1 ,
//                            'created_at' => now() ,
//                            'updated_at' => now()
//                        ] ,
//                        [
//                            'name'       => 'Quotations' ,
//                            'url'        => 'quotation' ,
//                            'language'   => 'quotation' ,
//                            'icon'       => 'lab lab-line-pos' ,
//                            'priority'   => 100 ,
//                            'svg'        => NULL ,
//                            'status'     => 1 ,
//                            'created_at' => now() ,
//                            'updated_at' => now()
//                        ]
//                        ,
//                        [
//                            'name'       => 'Sales' ,
//                            'language'   => 'pos_orders' ,
//                            'url'        => 'sales' ,
//                            'icon'       => 'lab lab-line-push-notification' ,
//                            'priority'   => 100 ,
//                            'svg'        => NULL ,
//                            'status'     => 1 ,
//                            'created_at' => now() ,
//                            'updated_at' => now()
//                        ] ,
//                        [
//                            'name'       => 'Credit Sales' ,
//                            'language'   => 'credit' ,
//                            'url'        => 'credit' ,
//                            'icon'       => 'lab lab-line-orders' ,
//                            'priority'   => 100 ,
//                            'svg'        => NULL ,
//                            'status'     => 1 ,
//                            'created_at' => now() ,
//                            'updated_at' => now()
//                        ] ,
//                        [
//                            'name'       => 'Deposited Sales' ,
//                            'language'   => 'pos_deposit_orders' ,
//                            'url'        => 'deposit' ,
//                            'icon'       => 'lab lab-line-pages' ,
//                            'priority'   => 100 ,
//                            'svg'        => NULL ,
//                            'status'     => 1 ,
//                            'created_at' => now() ,
//                            'updated_at' => now()
//                        ]
                    ] ,
                ] ,

//                [
//                    'name'       => 'Commission' ,
//                    'language'   => 'products' ,
//                    'url'        => 'commission' ,
//                    'icon'       => 'lab lab-pos' ,
//                    'priority'   => 100 ,
//                    'svg'        => NULL ,
//                    'status'     => 1 ,
//                    'created_at' => now() ,
//                    'updated_at' => now()
//                ] ,
                [
                    'name'       => 'Cleaning & Care' ,
                    'language'   => 'distribution_hub' ,
                    'url'        => '#' ,
                    'icon'       => 'SoapDispenserDroplet' ,
                    'priority'   => 100 ,
                    'svg'        => NULL ,
                    'status'     => 1 ,
                    'created_at' => now() ,
                    'updated_at' => now() ,
                    'children'   => [
                        [
                            'name'       => 'Order Mgt Board' ,
                            'url'        => 'admin/cleaning/orders' ,
                            'language'   => 'pos' ,
                            'icon'       => 'lab lab-line-pos' ,
                            'priority'   => 100 ,
                            'svg'        => NULL ,
                            'status'     => 1 ,
                            'created_at' => now() ,
                            'updated_at' => now()
                        ] ,
                        [
                            'name'       => 'Cleaning Services' ,
                            'url'        => 'admin/cleaning/services/services' ,
                            'language'   => 'pos' ,
                            'icon'       => 'lab lab-line-pos' ,
                            'priority'   => 100 ,
                            'svg'        => NULL ,
                            'status'     => 1 ,
                            'created_at' => now() ,
                            'updated_at' => now()
                        ] , [
                            'name'       => 'Cleaning Settings' ,
                            'url'        => 'admin/cleaning/settings' ,
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
//                [
//                    'name'       => 'Services' ,
//                    'language'   => 'products' ,
//                    'url'        => 'services' ,
//                    'icon'       => 'lab lab-pos' ,
//                    'priority'   => 100 ,
//                    'svg'        => NULL ,
//                    'status'     => 1 ,
//                    'created_at' => now() ,
//                    'updated_at' => now()
//                ] ,
//                [
//                    'name'       => 'Customers' ,
//                    'language'   => 'distribution_hub' ,
//                    'url'        => '#' ,
//                    'icon'       => 'lab lab-pos' ,
//                    'priority'   => 100 ,
//                    'svg'        => NULL ,
//                    'status'     => 1 ,
//                    'created_at' => now() ,
//                    'updated_at' => now() ,
//                    'children'   => [
//                        [
//                            'name'       => 'Customer List' ,
//                            'url'        => 'distribution-routes' ,
//                            'language'   => 'pos' ,
//                            'icon'       => 'lab lab-line-pos' ,
//                            'priority'   => 100 ,
//                            'svg'        => NULL ,
//                            'status'     => 1 ,
//                            'created_at' => now() ,
//                            'updated_at' => now()
//                        ] ,
//                        [
//                            'name'       => 'Loyalty / Membership' ,
//                            'url'        => 'distribution-routes/truck-stock' ,
//                            'language'   => 'pos' ,
//                            'icon'       => 'lab lab-line-pos' ,
//                            'priority'   => 100 ,
//                            'svg'        => NULL ,
//                            'status'     => 1 ,
//                            'created_at' => now() ,
//                            'updated_at' => now()
//                        ]
//                    ]
//                ] ,
//                [
//                    'name'       => 'Inventory' ,
//                    'language'   => 'products' ,
//                    'url'        => 'inventory' ,
//                    'icon'       => 'lab lab-pos' ,
//                    'priority'   => 100 ,
//                    'svg'        => NULL ,
//                    'status'     => 1 ,
//                    'created_at' => now() ,
//                    'updated_at' => now()
//                ] ,
//                [
//                    'name'       => 'Distribution' ,
//                    'language'   => 'products' ,
//                    'url'        => 'distribution' ,
//                    'icon'       => 'lab lab-pos' ,
//                    'priority'   => 100 ,
//                    'svg'        => NULL ,
//                    'status'     => 1 ,
//                    'created_at' => now() ,
//                    'updated_at' => now()
//                ] ,
//                [
//                    'name'       => 'Stock' ,
//                    'language'   => 'products' ,
//                    'url'        => 'stock' ,
//                    'icon'       => 'lab lab-pos' ,
//                    'priority'   => 100 ,
//                    'svg'        => NULL ,
//                    'status'     => 1 ,
//                    'created_at' => now() ,
//                    'updated_at' => now()
//                ] ,
//                [
//                    'name'       => 'Warehouse & Storage' ,
//                    'language'   => 'products' ,
//                    'url'        => 'stock' ,
//                    'icon'       => 'lab lab-pos' ,
//                    'priority'   => 100 ,
//                    'svg'        => NULL ,
//                    'status'     => 1 ,
//                    'created_at' => now() ,
//                    'updated_at' => now()
//                ] ,
//                [
//                    'name'       => 'Procurement' ,
//                    'language'   => 'products' ,
//                    'url'        => 'stock' ,
//                    'icon'       => 'lab lab-pos' ,
//                    'priority'   => 100 ,
//                    'svg'        => NULL ,
//                    'status'     => 1 ,
//                    'created_at' => now() ,
//                    'updated_at' => now()
//                ] ,
//                [
//                    'name'       => 'Production / Manufacturing' ,
//                    'language'   => 'distribution_hub' ,
//                    'url'        => '#' ,
//                    'icon'       => 'lab lab-pos' ,
//                    'priority'   => 100 ,
//                    'svg'        => NULL ,
//                    'status'     => 1 ,
//                    'created_at' => now() ,
//                    'updated_at' => now() ,
//                    'children'   => [
//                        [
//                            'name'       => 'Raw Materials' ,
//                            'url'        => 'raw-materials' ,
//                            'language'   => 'pos' ,
//                            'icon'       => 'lab lab-line-pos' ,
//                            'priority'   => 100 ,
//                            'svg'        => NULL ,
//                            'status'     => 1 ,
//                            'created_at' => now() ,
//                            'updated_at' => now()
//                        ] ,
//                        [
//                            'name'       => 'Production Setup' ,
//                            'url'        => 'production-setup' ,
//                            'language'   => 'pos' ,
//                            'icon'       => 'lab lab-line-pos' ,
//                            'priority'   => 100 ,
//                            'svg'        => NULL ,
//                            'status'     => 1 ,
//                            'created_at' => now() ,
//                            'updated_at' => now()
//                        ] ,
//                        [
//                            'name'       => 'Production Processes' ,
//                            'url'        => 'production-processes' ,
//                            'language'   => 'pos' ,
//                            'icon'       => 'lab lab-line-pos' ,
//                            'priority'   => 100 ,
//                            'svg'        => NULL ,
//                            'status'     => 1 ,
//                            'created_at' => now() ,
//                            'updated_at' => now()
//                        ],
//                        [
//                            'name'       => 'Production Output' ,
//                            'url'        => 'production-output' ,
//                            'language'   => 'pos' ,
//                            'icon'       => 'lab lab-line-pos' ,
//                            'priority'   => 100 ,
//                            'svg'        => NULL ,
//                            'status'     => 1 ,
//                            'created_at' => now() ,
//                            'updated_at' => now()
//                        ],
//                    ]
//                ] ,
//                [
//                    'name'       => 'Projects' ,
//                    'language'   => 'distribution_hub' ,
//                    'url'        => '#' ,
//                    'icon'       => 'lab lab-pos' ,
//                    'priority'   => 100 ,
//                    'svg'        => NULL ,
//                    'status'     => 1 ,
//                    'created_at' => now() ,
//                    'updated_at' => now() ,
//                    'children'   => [
//                        [
//                            'name'       => 'Projects List' ,
//                            'url'        => 'projects' ,
//                            'language'   => 'pos' ,
//                            'icon'       => 'lab lab-line-pos' ,
//                            'priority'   => 100 ,
//                            'svg'        => NULL ,
//                            'status'     => 1 ,
//                            'created_at' => now() ,
//                            'updated_at' => now()
//                        ] ,
//                        [
//                            'name'       => 'Tasks' ,
//                            'url'        => 'tasks' ,
//                            'language'   => 'pos' ,
//                            'icon'       => 'lab lab-line-pos' ,
//                            'priority'   => 100 ,
//                            'svg'        => NULL ,
//                            'status'     => 1 ,
//                            'created_at' => now() ,
//                            'updated_at' => now()
//                        ] ,
//                        [
//                            'name'       => 'Timesheets' ,
//                            'url'        => 'timesheets' ,
//                            'language'   => 'pos' ,
//                            'icon'       => 'lab lab-line-pos' ,
//                            'priority'   => 100 ,
//                            'svg'        => NULL ,
//                            'status'     => 1 ,
//                            'created_at' => now() ,
//                            'updated_at' => now()
//                        ],
//                        [
//                            'name'       => 'Project Settings' ,
//                            'url'        => 'settings' ,
//                            'language'   => 'pos' ,
//                            'icon'       => 'lab lab-line-pos' ,
//                            'priority'   => 100 ,
//                            'svg'        => NULL ,
//                            'status'     => 1 ,
//                            'created_at' => now() ,
//                            'updated_at' => now()
//                        ],
//                    ]
//                ] ,
//                [
//                    'name'       => 'Expenses' ,
//                    'language'   => 'products' ,
//                    'url'        => 'expenses' ,
//                    'icon'       => 'lab lab-pos' ,
//                    'priority'   => 100 ,
//                    'svg'        => NULL ,
//                    'status'     => 1 ,
//                    'created_at' => now() ,
//                    'updated_at' => now()
//                ] ,
//                [
//                    'name'       => 'Branches' ,
//                    'language'   => 'products' ,
//                    'url'        => 'branches' ,
//                    'icon'       => 'lab lab-pos' ,
//                    'priority'   => 100 ,
//                    'svg'        => NULL ,
//                    'status'     => 1 ,
//                    'created_at' => now() ,
//                    'updated_at' => now()
//                ] ,
//                [
//                    'name'       => 'Asset Management' ,
//                    'language'   => 'products' ,
//                    'url'        => 'asset-management' ,
//                    'icon'       => 'lab lab-pos' ,
//                    'priority'   => 100 ,
//                    'svg'        => NULL ,
//                    'status'     => 1 ,
//                    'created_at' => now() ,
//                    'updated_at' => now()
//                ] ,
//                [
//                    'name'       => 'Accounting' ,
//                    'language'   => 'accounting' ,
//                    'url'        => '#' ,
//                    'icon'       => 'lab lab-pos' ,
//                    'priority'   => 100 ,
//                    'svg'        => NULL ,
//                    'status'     => 1 ,
//                    'created_at' => now() ,
//                    'updated_at' => now() ,
//                    'children'   => [
//                        [
//                            'name'       => 'Payment Accounts' ,
//                            'url'        => 'accounting/payment_accounts' ,
//                            'language'   => 'payment_accounts' ,
//                            'icon'       => 'lab lab-line-pos' ,
//                            'priority'   => 100 ,
//                            'svg'        => NULL ,
//                            'status'     => 1 ,
//                            'created_at' => now() ,
//                            'updated_at' => now()
//
//                        ] ,
//                        [
//                            'name'       => 'Chart of Accounts' ,
//                            'url'        => 'accounting/chart_of_accounts' ,
//                            'language'   => 'chart_of_accounts' ,
//                            'icon'       => 'lab lab-line-pos' ,
//                            'priority'   => 100 ,
//                            'svg'        => NULL ,
//                            'status'     => 1 ,
//                            'created_at' => now() ,
//                            'updated_at' => now()
//                        ] ,
//                        [
//                            'name'       => 'Journal Entries' ,
//                            'language'   => 'journal_entries' ,
//                            'url'        => 'accounting/journal_entries' ,
//                            'icon'       => 'lab lab-line-push-notification' ,
//                            'priority'   => 100 ,
//                            'svg'        => NULL ,
//                            'status'     => 1 ,
//                            'created_at' => now() ,
//                            'updated_at' => now()
//                        ] ,
//                    ] ,
//                ] ,
//                [
//                    'name'       => 'Human Resource' ,
//                    'language'   => 'distribution_hub' ,
//                    'url'        => '#' ,
//                    'icon'       => 'lab lab-pos' ,
//                    'priority'   => 100 ,
//                    'svg'        => NULL ,
//                    'status'     => 1 ,
//                    'created_at' => now() ,
//                    'updated_at' => now() ,
//                    'children'   => [
//                        [
//                            'name'       => 'Employee Management' ,
//                            'url'        => 'employee-management' ,
//                            'language'   => 'pos' ,
//                            'icon'       => 'lab lab-line-pos' ,
//                            'priority'   => 100 ,
//                            'svg'        => NULL ,
//                            'status'     => 1 ,
//                            'created_at' => now() ,
//                            'updated_at' => now()
//                        ] ,
//                        [
//                            'name'       => 'Payroll' ,
//                            'url'        => 'payroll' ,
//                            'language'   => 'pos' ,
//                            'icon'       => 'lab lab-line-pos' ,
//                            'priority'   => 100 ,
//                            'svg'        => NULL ,
//                            'status'     => 1 ,
//                            'created_at' => now() ,
//                            'updated_at' => now()
//                        ],
//                        [
//                            'name'       => 'Leave Management' ,
//                            'url'        => 'leave-management' ,
//                            'language'   => 'pos' ,
//                            'icon'       => 'lab lab-line-pos' ,
//                            'priority'   => 100 ,
//                            'svg'        => NULL ,
//                            'status'     => 1 ,
//                            'created_at' => now() ,
//                            'updated_at' => now()
//                        ],
//                        [
//                            'name'       => 'Recruitment' ,
//                            'url'        => 'recruitment' ,
//                            'language'   => 'pos' ,
//                            'icon'       => 'lab lab-line-pos' ,
//                            'priority'   => 100 ,
//                            'svg'        => NULL ,
//                            'status'     => 1 ,
//                            'created_at' => now() ,
//                            'updated_at' => now()
//                        ],
//                        [
//                            'name'       => 'Performance' ,
//                            'url'        => 'performance' ,
//                            'language'   => 'pos' ,
//                            'icon'       => 'lab lab-line-pos' ,
//                            'priority'   => 100 ,
//                            'svg'        => NULL ,
//                            'status'     => 1 ,
//                            'created_at' => now() ,
//                            'updated_at' => now()
//                        ],
//                    ]
//                ] ,
//                [
//                    'name'       => 'Users' ,
//                    'language'   => 'users' ,
//                    'url'        => '#' ,
//                    'icon'       => 'lab ' ,
//                    'priority'   => 100 ,
//                    'svg'        => NULL ,
//                    'status'     => 1 ,
//                    'created_at' => now() ,
//                    'updated_at' => now() ,
//                    'children'   => [
//                        [
//                            'name'       => 'Administrators' ,
//                            'language'   => 'administrators' ,
//                            'url'        => 'administrators' ,
//                            'icon'       => 'lab lab-line-administrator' ,
//                            'priority'   => 100 ,
//                            'svg'        => NULL ,
//                            'status'     => 1 ,
//                            'created_at' => now() ,
//                            'updated_at' => now()
//                        ] ,
//
//                        [
//                            'name'       => 'Employees' ,
//                            'language'   => 'employees' ,
//                            'url'        => 'employees' ,
//                            'icon'       => 'lab lab-line-users' ,
//                            'priority'   => 100 ,
//                            'svg'        => NULL ,
//                            'status'     => 1 ,
//                            'created_at' => now() ,
//                            'updated_at' => now()
//                        ] ,
//                    ]
//                ] ,
//                [
//                    'name'       => 'Reports' ,
//                    'language'   => 'reports' ,
//                    'url'        => '#' ,
//                    'icon'       => 'lab ' ,
//                    'svg'        => NULL ,
//                    'priority'   => 100 ,
//                    'status'     => 1 ,
//                    'created_at' => now() ,
//                    'updated_at' => now() ,
//                    'children'   => [
//                        [
//                            'name'       => 'Sales Report' ,
//                            'language'   => 'sales_report' ,
//                            'url'        => 'sales-report' ,
//                            'svg'        => NULL ,
//                            'icon'       => 'lab lab-line-sales-report' ,
//                            'priority'   => 100 ,
//                            'status'     => 1 ,
//                            'created_at' => now() ,
//                            'updated_at' => now()
//
//                        ] ,
//                        [
//                            'name'       => 'Inventory Report' ,
//                            'language'   => 'sales_report' ,
//                            'url'        => 'inventory-report' ,
//                            'svg'        => NULL ,
//                            'icon'       => 'lab lab-line-sales-report' ,
//                            'priority'   => 100 ,
//                            'status'     => 1 ,
//                            'created_at' => now() ,
//                            'updated_at' => now()
//                        ] ,
//                        [
//                            'name'       => 'Production Report' ,
//                            'language'   => 'sales_report' ,
//                            'url'        => 'production-report' ,
//                            'svg'        => NULL ,
//                            'icon'       => 'lab lab-line-sales-report' ,
//                            'priority'   => 100 ,
//                            'status'     => 1 ,
//                            'created_at' => now() ,
//                            'updated_at' => now()
//                        ] ,
//                        [
//                            'name'       => 'Accounting Report' ,
//                            'language'   => 'sales_report' ,
//                            'url'        => 'accounting-report' ,
//                            'svg'        => NULL ,
//                            'icon'       => 'lab lab-line-sales-report' ,
//                            'priority'   => 100 ,
//                            'status'     => 1 ,
//                            'created_at' => now() ,
//                            'updated_at' => now()
//                        ] ,
//                        [
//                            'name'       => 'Expense Report' ,
//                            'language'   => 'sales_report' ,
//                            'url'        => 'expense-report' ,
//                            'svg'        => NULL ,
//                            'icon'       => 'lab lab-line-sales-report' ,
//                            'priority'   => 100 ,
//                            'status'     => 1 ,
//                            'created_at' => now() ,
//                            'updated_at' => now()
//                        ] ,
//                        [
//                            'name'       => 'HR Report' ,
//                            'language'   => 'sales_report' ,
//                            'url'        => 'hr-report' ,
//                            'svg'        => NULL ,
//                            'icon'       => 'lab lab-line-sales-report' ,
//                            'priority'   => 100 ,
//                            'status'     => 1 ,
//                            'created_at' => now() ,
//                            'updated_at' => now()
//                        ] ,
//                        [
//                            'name'       => 'Item / Product Report' ,
//                            'language'   => 'sales_report' ,
//                            'url'        => 'item-report' ,
//                            'svg'        => NULL ,
//                            'icon'       => 'lab lab-line-sales-report' ,
//                            'priority'   => 100 ,
//                            'status'     => 1 ,
//                            'created_at' => now() ,
//                            'updated_at' => now()
//                        ]
//                    ]
//                ] ,
//                [
//                    'name'       => 'Subscriptions' ,
//                    'language'   => 'products' ,
//                    'url'        => 'subscriptions' ,
//                    'icon'       => 'lab lab-pos' ,
//                    'priority'   => 100 ,
//                    'svg'        => NULL ,
//                    'status'     => 1 ,
//                    'created_at' => now() ,
//                    'updated_at' => now()
//                ] ,
//                [
//                    'name'       => 'Activity Logs' ,
//                    'language'   => 'activity_logs' ,
//                    'url'        => '#' ,
//                    'icon'       => 'lab ' ,
//                    'svg'        => NULL ,
//                    'priority'   => 100 ,
//                    'status'     => 1 ,
//                    'created_at' => now() ,
//                    'updated_at' => now() ,
//                    'children'   => [
//                        [
//                            'name'       => 'Audit Trail' ,
//                            'language'   => 'activity_logs' ,
//                            'url'        => 'audit-trail' ,
//                            'icon'       => 'lab lab-line-settings' ,
//                            'priority'   => 100 ,
//                            'svg'        => NULL ,
//                            'status'     => 1 ,
//                            'created_at' => now() ,
//                            'updated_at' => now()
//                        ],
//                        [
//                            'name'       => 'Transaction Logs' ,
//                            'language'   => 'activity_logs' ,
//                            'url'        => 'transaction-logs' ,
//                            'icon'       => 'lab lab-line-settings' ,
//                            'priority'   => 100 ,
//                            'svg'        => NULL ,
//                            'status'     => 1 ,
//                            'created_at' => now() ,
//                            'updated_at' => now()
//                        ],
//                    ]
//                ] ,
//                [
//                    'name'       => 'Modules' ,
//                    'language'   => 'products' ,
//                    'url'        => 'modules' ,
//                    'icon'       => 'lab lab-pos' ,
//                    'priority'   => 100 ,
//                    'svg'        => NULL ,
//                    'status'     => 1 ,
//                    'created_at' => now() ,
//                    'updated_at' => now()
//                ] ,
                [
                    'name'       => 'Modules' ,
                    'language'   => 'products' ,
                    'url'        => 'admin/modules' ,
                    'icon'       => 'Puzzle' ,
                    'priority'   => 100 ,
                    'svg'        => NULL ,
                    'status'     => 1 ,
                    'created_at' => now() ,
                    'updated_at' => now()
                ] ,
                [
                    'name'       => 'Settings' ,
                    'language'   => 'products' ,
                    'url'        => 'admin/settings' ,
                    'icon'       => 'Settings' ,
                    'priority'   => 100 ,
                    'svg'        => NULL ,
                    'status'     => 1 ,
                    'created_at' => now() ,
                    'updated_at' => now()
                ] ,
            ];

            Menu::insert( AppLibrary::associativeToNumericArrayBuilder( $menus ) );
        }
    }
