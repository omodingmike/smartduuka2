<?php

    namespace App\Http\Controllers\Admin;


    use App\Http\Controllers\Controller;
    use App\Traits\HasAdvancedFilter;

    class AdminController extends Controller
    {
        use HasAdvancedFilter;

        public function __construct() {}
    }
