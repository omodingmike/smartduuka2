<?php

    namespace Database\Seeders;

    use App\Libraries\AppLibrary;
    use Illuminate\Database\Seeder;
    use Illuminate\Support\Str;
    use Spatie\Permission\Models\Permission;

    class PermissionTableSeeder extends Seeder
    {
        public function run() : void
        {
            $data = [
                [ 'group' => "Dashboard" , 'items' => [ "Dashboard Overview" ] ] ,
                [ 'group' => "Sales" , 'items' => [ "POS" , "Add Sale" , "Quotation" , "Sales Orders" , "Credit Sales" , "Deposited Sales" ] ] ,
                [ 'group' => "Commission" , 'items' => [ "Commission Rules" , "Commission Pay-out" , "Commission Summary" ] ] ,
                [ 'group' => "Inventory (Products)" , 'items' => [ "Product List" , "Product Categories" , "Product Attributes" , "Product Units" , "Product Brands" ] ] ,
                [ 'group' => "Stock Management" , 'items' => [ "Stock List" , "Stock Takings" , "Stock Reconciliation" , "Stock Expiry" , "Stock Damages" , "Stock Requests" , "Stock Transfers" ] ] ,
                [ 'group' => "Procurement" , 'items' => [ "Stock Purchases" , "Purchase Requests" , "Raw Material Purchases" , "Purchase Returns" , "Suppliers" ] ] ,
                [ 'group' => "Distribution" , 'items' => [ "Distribution Routes" , "Truck Assigned Stock" ] ] ,
                [ 'group' => "Expenses" , 'items' => [ "Expenses List" , "Expense Categories" , "Recurring Expenses" , "Expense Analytics" ] ] ,
                [ 'group' => "Production" , 'items' => [ "Raw Materials" , "Production Setup" , "Processes" , "Output" ] ] ,
                [ 'group' => "Customers" , 'items' => [ "Customer List" , "Loyalty / Membership" ] ] ,
                [ 'group' => "Projects" , 'items' => [ "Projects List" , "Tasks" , "Timesheets" , "Project Settings" ] ] ,
                [ 'group' => "Accounting" , 'items' => [ "Transactions" , "Chart of Accounts" , "Journal Entry" , "Accounting Settings" ] ] ,
                [ 'group' => "Human Resource" , 'items' => [ "Employee Mgt" , "Payroll" , "Leave Mgt" , "Recruitment" , "Performance" ] ] ,
                [ 'group' => "Cleaning & Care" , 'items' => [ "Order Mgt Board" , "Cleaning Services" , "Cleaning Settings" ] ] ,
                [ 'group' => "Services" , 'items' => [ "Services List" ] ] ,
                [ 'group' => "Branches & Assets" , 'items' => [ "Branches" , "Asset Management" ] ] ,
                [ 'group' => "Users" , 'items' => [ "Administrators" , "Employees" ] ] ,
                [ 'group' => "Reports" , 'items' => [ "Sales Reports" , "Inventory Reports" , "Production Reports" , "Accounting Reports" , "Expenses Reports" , "HR Reports" , "Product Reports" ] ] ,
                [ 'group' => "System" , 'items' => [ "Subscriptions" , "Activity Logs" , "Modules" , "Settings" ] ] ,
            ];

            $permissions = [];
            foreach ( $data as $group ) {
                $children = [];
                foreach ( $group[ 'items' ] as $item ) {
                    $children[] = [
                        'title'      => $item ,
                        'name'       => Str::slug( $item , '_' ) ,
                        'guard_name' => 'web' ,
                        'url'        => Str::slug( $item ) ,
                        'created_at' => now() ,
                        'updated_at' => now() ,
                    ];
                }

                $permissions[] = [
                    'title'      => $group[ 'group' ] ,
                    'name'       => Str::slug( $group[ 'group' ] , '_' ) ,
                    'guard_name' => 'web' ,
                    'url'        => Str::slug( $group[ 'group' ] ) ,
                    'created_at' => now() ,
                    'updated_at' => now() ,
                    'children'   => $children
                ];
            }

            $permissions = AppLibrary::associativeToNumericArrayBuilder( $permissions );
            Permission::insert( $permissions );
        }
    }
