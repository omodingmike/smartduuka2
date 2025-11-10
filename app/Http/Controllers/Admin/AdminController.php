<?php

    namespace App\Http\Controllers\Admin;


    use App\Http\Controllers\Controller;
    use App\Traits\HasAdvancedIndex;

    class AdminController extends Controller
    {
        use HasAdvancedIndex;

        public function __construct() {}
    }
