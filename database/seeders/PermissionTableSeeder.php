<?php

    namespace Database\Seeders;

    use App\Libraries\AppLibrary;
    use Illuminate\Database\Seeder;
    use Illuminate\Support\Str;
    use Spatie\Permission\Models\Permission;

    class PermissionTableSeeder extends Seeder
    {
        public function run(): void
        {
            $data = [
                [
                    'group' => 'Dashboard',
                    'items' => [
                        ['title' => 'Dashboard Overview', 'name' => 'dashboard_overview', 'url' => 'dashboard/overview', 'items' => []]
                    ]
                ],
                [
                    'group' => 'Sales',
                    'items' => [
                        ['title' => 'POS', 'name' => 'pos', 'url' => 'sales/pos', 'items' => []],
                        ['title' => 'Add Sale', 'name' => 'add_sale', 'url' => 'sales/add', 'items' => []],
                        [
                            'title' => 'Quotation',
                            'name'  => 'quotation',
                            'url'   => 'sales/quotation',
                            'items' => [
                                ['title' => 'Quotation Create', 'name' => 'quotation_create', 'url' => 'sales/quotation/create', 'items' => []],
                                ['title' => 'Quotation Edit', 'name' => 'quotation_edit', 'url' => 'sales/quotation/edit', 'items' => []],
                                ['title' => 'Quotation Delete', 'name' => 'quotation_delete', 'url' => 'sales/quotation/delete', 'items' => []],
                                ['title' => 'Quotation Show', 'name' => 'quotation_show', 'url' => 'sales/quotation/show', 'items' => []],
                            ]
                        ],
                        ['title' => 'Sales Orders', 'name' => 'sales_orders', 'url' => 'sales/orders', 'items' => []],
                        ['title' => 'Credit Sales', 'name' => 'credit_sales', 'url' => 'sales/credit', 'items' => []],
                        ['title' => 'Deposited Sales', 'name' => 'deposited_sales', 'url' => 'sales/deposited', 'items' => []]
                    ]
                ],
                [
                    'group' => 'Commission',
                    'items' => [
                        ['title' => 'Commission Rules', 'name' => 'commission_rules', 'url' => 'commission/rules', 'items' => []],
                        ['title' => 'Commission Pay-out', 'name' => 'commission_payout', 'url' => 'commission/payout', 'items' => []],
                        ['title' => 'Commission Summary', 'name' => 'commission_summary', 'url' => 'commission/summary', 'items' => []]
                    ]
                ],
                [
                    'group' => 'Inventory (Products)',
                    'items' => array_map(fn($item) => [
                        'title' => $item,
                        'name'  => strtolower(str_replace(' ', '_', $item)),
                        'url'   => 'inventory/' . strtolower(str_replace(' ', '-', $item)),
                        'items' => [
                            ['title' => "$item Create", 'name' => strtolower(str_replace(' ', '_', $item)) . '_create', 'url' => 'inventory/' . strtolower(str_replace(' ', '-', $item)) . '/create', 'items' => []],
                            ['title' => "$item Edit", 'name' => strtolower(str_replace(' ', '_', $item)) . '_edit', 'url' => 'inventory/' . strtolower(str_replace(' ', '-', $item)) . '/edit', 'items' => []],
                            ['title' => "$item Delete", 'name' => strtolower(str_replace(' ', '_', $item)) . '_delete', 'url' => 'inventory/' . strtolower(str_replace(' ', '-', $item)) . '/delete', 'items' => []],
                            ['title' => "$item Show", 'name' => strtolower(str_replace(' ', '_', $item)) . '_show', 'url' => 'inventory/' . strtolower(str_replace(' ', '-', $item)) . '/show', 'items' => []],
                        ]
                    ], ['Product List', 'Product Categories', 'Product Attributes', 'Product Units', 'Product Brands'])
                ],
                [
                    'group' => 'Stock Management',
                    'items' => array_map(fn($item) => [
                        'title' => $item,
                        'name'  => strtolower(str_replace(' ', '_', $item)),
                        'url'   => 'stock/' . strtolower(str_replace(' ', '-', $item)),
                        'items' => in_array($item, ['Stock Transfers', 'Stock Requests', 'Stock Damages', 'Stock Reconciliation', 'Stock List']) ? [
                            ['title' => "$item Create", 'name' => strtolower(str_replace(' ', '_', $item)) . '_create', 'url' => 'stock/' . strtolower(str_replace(' ', '-', $item)) . '/create', 'items' => []],
                            ['title' => "$item Edit", 'name' => strtolower(str_replace(' ', '_', $item)) . '_edit', 'url' => 'stock/' . strtolower(str_replace(' ', '-', $item)) . '/edit', 'items' => []],
                            ['title' => "$item Delete", 'name' => strtolower(str_replace(' ', '_', $item)) . '_delete', 'url' => 'stock/' . strtolower(str_replace(' ', '-', $item)) . '/delete', 'items' => []],
                            ['title' => "$item Show", 'name' => strtolower(str_replace(' ', '_', $item)) . '_show', 'url' => 'stock/' . strtolower(str_replace(' ', '-', $item)) . '/show', 'items' => []],
                        ] : []
                    ], ['Stock List', 'Stock Takings', 'Stock Reconciliation', 'Stock Expiry', 'Stock Damages', 'Stock Requests', 'Stock Transfers'])
                ],
                [
                    'group' => 'Procurement',
                    'items' => array_map(fn($item) => [
                        'title' => $item,
                        'name'  => strtolower(str_replace(' ', '_', $item)),
                        'url'   => 'procurement/' . strtolower(str_replace(' ', '-', $item)),
                        'items' => [
                            ['title' => "$item Create", 'name' => strtolower(str_replace(' ', '_', $item)) . '_create', 'url' => 'procurement/' . strtolower(str_replace(' ', '-', $item)) . '/create', 'items' => []],
                            ['title' => "$item Edit", 'name' => strtolower(str_replace(' ', '_', $item)) . '_edit', 'url' => 'procurement/' . strtolower(str_replace(' ', '-', $item)) . '/edit', 'items' => []],
                            ['title' => "$item Delete", 'name' => strtolower(str_replace(' ', '_', $item)) . '_delete', 'url' => 'procurement/' . strtolower(str_replace(' ', '-', $item)) . '/delete', 'items' => []],
                        ]
                    ], ['Stock Purchases', 'Purchase Requests', 'Raw Material Purchases', 'Purchase Returns', 'Suppliers'])
                ],
                [
                    'group' => 'Distribution',
                    'items' => array_map(fn($item) => [
                        'title' => $item,
                        'name'  => strtolower(str_replace(' ', '_', $item)),
                        'url'   => 'distribution/' . strtolower(str_replace(' ', '-', $item)),
                        'items' => [
                            ['title' => "$item Create", 'name' => strtolower(str_replace(' ', '_', $item)) . '_create', 'url' => 'distribution/' . strtolower(str_replace(' ', '-', $item)) . '/create', 'items' => []],
                            ['title' => "$item Edit", 'name' => strtolower(str_replace(' ', '_', $item)) . '_edit', 'url' => 'distribution/' . strtolower(str_replace(' ', '-', $item)) . '/edit', 'items' => []],
                            ['title' => "$item Delete", 'name' => strtolower(str_replace(' ', '_', $item)) . '_delete', 'url' => 'distribution/' . strtolower(str_replace(' ', '-', $item)) . '/delete', 'items' => []],
                        ]
                    ], ['Distribution Routes', 'Truck Assigned Stock'])
                ],
                [
                    'group' => 'Expenses',
                    'items' => array_map(fn($item) => [
                        'title' => $item,
                        'name'  => strtolower(str_replace(' ', '_', $item)),
                        'url'   => 'expenses/' . strtolower(str_replace(' ', '-', $item)),
                        'items' => ($item !== 'Expense Analytics') ? [
                            ['title' => "$item Create", 'name' => strtolower(str_replace(' ', '_', $item)) . '_create', 'url' => 'expenses/' . strtolower(str_replace(' ', '-', $item)) . '/create', 'items' => []],
                            ['title' => "$item Edit", 'name' => strtolower(str_replace(' ', '_', $item)) . '_edit', 'url' => 'expenses/' . strtolower(str_replace(' ', '-', $item)) . '/edit', 'items' => []],
                            ['title' => "$item Delete", 'name' => strtolower(str_replace(' ', '_', $item)) . '_delete', 'url' => 'expenses/' . strtolower(str_replace(' ', '-', $item)) . '/delete', 'items' => []],
                        ] : []
                    ], ['Expenses List', 'Expense Categories', 'Recurring Expenses', 'Expense Analytics'])
                ],
                [
                    'group' => 'Production',
                    'items' => array_map(fn($item) => [
                        'title' => $item,
                        'name'  => strtolower(str_replace(' ', '_', $item)),
                        'url'   => 'production/' . strtolower(str_replace(' ', '-', $item)),
                        'items' => ($item !== 'Output') ? [
                            ['title' => "$item Create", 'name' => strtolower(str_replace(' ', '_', $item)) . '_create', 'url' => 'production/' . strtolower(str_replace(' ', '-', $item)) . '/create', 'items' => []],
                            ['title' => "$item Edit", 'name' => strtolower(str_replace(' ', '_', $item)) . '_edit', 'url' => 'production/' . strtolower(str_replace(' ', '-', $item)) . '/edit', 'items' => []],
                            ['title' => "$item Delete", 'name' => strtolower(str_replace(' ', '_', $item)) . '_delete', 'url' => 'production/' . strtolower(str_replace(' ', '-', $item)) . '/delete', 'items' => []],
                        ] : []
                    ], ['Raw Materials', 'Production Setup', 'Processes', 'Output'])
                ],
                [
                    'group' => 'Customers',
                    'items' => array_map(fn($item) => [
                        'title' => $item,
                        'name'  => strtolower(str_replace(' ', '_', $item)),
                        'url'   => 'customers/' . strtolower(str_replace(' ', '-', $item)),
                        'items' => [
                            ['title' => "$item Create", 'name' => strtolower(str_replace(' ', '_', $item)) . '_create', 'url' => 'customers/' . strtolower(str_replace(' ', '-', $item)) . '/create', 'items' => []],
                            ['title' => "$item Edit", 'name' => strtolower(str_replace(' ', '_', $item)) . '_edit', 'url' => 'customers/' . strtolower(str_replace(' ', '-', $item)) . '/edit', 'items' => []],
                            ['title' => "$item Delete", 'name' => strtolower(str_replace(' ', '_', $item)) . '_delete', 'url' => 'customers/' . strtolower(str_replace(' ', '-', $item)) . '/delete', 'items' => []],
                        ]
                    ], ['Customer List', 'Loyalty / Membership'])
                ],
                [
                    'group' => 'Projects',
                    'items' => array_map(fn($item) => [
                        'title' => $item,
                        'name'  => strtolower(str_replace(' ', '_', $item)),
                        'url'   => 'projects/' . strtolower(str_replace(' ', '-', $item)),
                        'items' => [
                            ['title' => "$item Create", 'name' => strtolower(str_replace(' ', '_', $item)) . '_create', 'url' => 'projects/' . strtolower(str_replace(' ', '-', $item)) . '/create', 'items' => []],
                            ['title' => "$item Edit", 'name' => strtolower(str_replace(' ', '_', $item)) . '_edit', 'url' => 'projects/' . strtolower(str_replace(' ', '-', $item)) . '/edit', 'items' => []],
                            ['title' => "$item Delete", 'name' => strtolower(str_replace(' ', '_', $item)) . '_delete', 'url' => 'projects/' . strtolower(str_replace(' ', '-', $item)) . '/delete', 'items' => []],
                        ]
                    ], ['Projects List', 'Tasks', 'Timesheets', 'Project Settings'])
                ],
                [
                    'group' => 'Accounting',
                    'items' => [
                        ['title' => 'Transactions', 'name' => 'transactions', 'url' => 'accounting/transactions', 'items' => []],
                        [
                            'title' => 'Chart of Accounts',
                            'name'  => 'chart_of_accounts',
                            'url'   => 'accounting/coa',
                            'items' => [
                                ['title' => 'Account Create', 'name' => 'account_create', 'url' => 'accounting/coa/create', 'items' => []],
                                ['title' => 'Account Edit', 'name' => 'account_edit', 'url' => 'accounting/coa/edit', 'items' => []],
                                ['title' => 'Account Delete', 'name' => 'account_delete', 'url' => 'accounting/coa/delete', 'items' => []],
                            ]
                        ],
                        [
                            'title' => 'Journal Entry',
                            'name'  => 'journal_entry',
                            'url'   => 'accounting/journal',
                            'items' => [
                                ['title' => 'Journal Create', 'name' => 'journal_create', 'url' => 'accounting/journal/create', 'items' => []],
                                ['title' => 'Journal Edit', 'name' => 'journal_edit', 'url' => 'accounting/journal/edit', 'items' => []],
                            ]
                        ],
                        ['title' => 'Accounting Settings', 'name' => 'accounting_settings', 'url' => 'accounting/settings', 'items' => []]
                    ]
                ],
                [
                    'group' => 'Human Resource',
                    'items' => array_map(fn($item) => [
                        'title' => $item,
                        'name'  => strtolower(str_replace(' ', '_', $item)),
                        'url'   => 'hr/' . strtolower(str_replace(' ', '-', $item)),
                        'items' => [
                            ['title' => "$item Create", 'name' => strtolower(str_replace(' ', '_', $item)) . '_create', 'url' => 'hr/' . strtolower(str_replace(' ', '-', $item)) . '/create', 'items' => []],
                            ['title' => "$item Edit", 'name' => strtolower(str_replace(' ', '_', $item)) . '_edit', 'url' => 'hr/' . strtolower(str_replace(' ', '-', $item)) . '/edit', 'items' => []],
                            ['title' => "$item Delete", 'name' => strtolower(str_replace(' ', '_', $item)) . '_delete', 'url' => 'hr/' . strtolower(str_replace(' ', '-', $item)) . '/delete', 'items' => []],
                        ]
                    ], ['Employee Mgt', 'Payroll', 'Leave Mgt', 'Recruitment', 'Performance'])
                ],
                [
                    'group' => 'Services',
                    'items' => [
                        [
                            'title' => 'Services List',
                            'name'  => 'services_list',
                            'url'   => 'services/list',
                            'items' => [
                                ['title' => 'Service Create', 'name' => 'service_create', 'url' => 'services/list/create', 'items' => []],
                                ['title' => 'Service Edit', 'name' => 'service_edit', 'url' => 'services/list/edit', 'items' => []],
                                ['title' => 'Service Delete', 'name' => 'service_delete', 'url' => 'services/list/delete', 'items' => []],
                            ]
                        ]
                    ]
                ],
                [
                    'group' => 'Branches & Assets',
                    'items' => [
                        [
                            'title' => 'Branches',
                            'name'  => 'branches',
                            'url'   => 'branches',
                            'items' => [
                                ['title' => 'Branch Create', 'name' => 'branch_create', 'url' => 'branches/create', 'items' => []],
                                ['title' => 'Branch Edit', 'name' => 'branch_edit', 'url' => 'branches/edit', 'items' => []],
                                ['title' => 'Branch Delete', 'name' => 'branch_delete', 'url' => 'branches/delete', 'items' => []],
                            ]
                        ],
                        ['title' => 'Asset Management', 'name' => 'asset_management', 'url' => 'assets', 'items' => []]
                    ]
                ],
                [
                    'group' => 'Users',
                    'items' => array_map(fn($item) => [
                        'title' => $item,
                        'name'  => strtolower(str_replace(' ', '_', $item)),
                        'url'   => 'users/' . strtolower(str_replace(' ', '-', $item)),
                        'items' => [
                            ['title' => "$item Create", 'name' => strtolower(str_replace(' ', '_', $item)) . '_create', 'url' => 'users/' . strtolower(str_replace(' ', '-', $item)) . '/create', 'items' => []],
                            ['title' => "$item Edit", 'name' => strtolower(str_replace(' ', '_', $item)) . '_edit', 'url' => 'users/' . strtolower(str_replace(' ', '-', $item)) . '/edit', 'items' => []],
                            ['title' => "$item Delete", 'name' => strtolower(str_replace(' ', '_', $item)) . '_delete', 'url' => 'users/' . strtolower(str_replace(' ', '-', $item)) . '/delete', 'items' => []],
                        ]
                    ], ['Administrators', 'Employees'])
                ],
                [
                    'group' => 'Reports',
                    'items' => array_map(fn($item) => ['title' => $item, 'name' => strtolower(str_replace(' ', '_', $item)), 'url' => 'reports/' . strtolower(str_replace(' ', '-', $item)), 'items' => []], ['Sales Reports', 'Inventory Reports', 'Production Reports', 'Accounting Reports', 'Expenses Reports', 'HR Reports', 'Product Reports'])
                ],
                [
                    'group' => 'System',
                    'items' => array_map(fn($item) => ['title' => $item, 'name' => strtolower(str_replace(' ', '_', $item)), 'url' => 'system/' . strtolower(str_replace(' ', '-', $item)), 'items' => []], ['Subscriptions', 'Activity Logs', 'Modules', 'Settings'])
                ]
            ];

            $permissions = [];
            $now = now();

            foreach ($data as $group) {
                $groupName = Str::slug($group['group'], '_');
                $groupUrl  = Str::slug($group['group'], '-');

                $parentPermission = [
                    'title'      => $group['group'],
                    'name'       => $groupName,
                    'guard_name' => 'sanctum',
                    'url'        => $groupUrl,
                    'created_at' => $now,
                    'updated_at' => $now,
                    'children'   => []
                ];

                foreach ($group['items'] as $item) {
                    // Prepare item structure for children
                    $itemNode = [
                        'title'      => $item['title'],
                        'name'       => $item['name'],
                        'guard_name' => 'sanctum',
                        'url'        => $item['url'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];

                    // If this item has deep CRUD items (recursive items)
                    if (!empty($item['items'])) {
                        $itemNode['children'] = array_map(function($subItem) use ($now) {
                            return [
                                'title'      => $subItem['title'],
                                'name'       => $subItem['name'],
                                'guard_name' => 'sanctum',
                                'url'        => $subItem['url'],
                                'created_at' => $now,
                                'updated_at' => $now,
                            ];
                        }, $item['items']);
                    }

                    $parentPermission['children'][] = $itemNode;
                }

                $permissions[] = $parentPermission;
            }

            // Convert the nested associative array into a numeric array with parent IDs for database insertion
            $permissions = AppLibrary::recursiveFlattenPermissions($permissions);

            Permission::insert($permissions);
        }
    }