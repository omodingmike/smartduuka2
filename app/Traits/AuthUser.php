<?php

    namespace App\Traits;

    trait AuthUser
    {
        public function id()
        {
            return auth() -> user() -> id;
        }
    }