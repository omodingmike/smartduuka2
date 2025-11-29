<?php

    namespace App\Traits;

    use Illuminate\Support\Facades\Cache;

    trait ForgetsCacheOnCRUD
    {
        protected static function bootForgetsCacheOnCRUD(): void
        {
            // Get an instance of the model to call the instance method
            $model = new static();
            $keys = (array) $model->getCacheKeysToForget();

            if (empty($keys)) {
                // No keys configured, so we prevent listeners from being registered.
                return;
            }

            // Define a closure to forget all configured keys
            $forgetCache = function () use ($keys) {
                foreach ($keys as $key) {
                    Cache::forget($key);
                }
            };

            // Register the closure for all relevant model events
            static::created($forgetCache);
            static::updated($forgetCache);
            static::deleted($forgetCache);
        }

        /**
         * Define the cache key(s) to be forgotten.
         * Models using this trait MUST override this method.
         *
         * @return string|array
         */
        protected function getCacheKeysToForget(): string|array
        {
            // IMPORTANT: Override this method in your model!
            // Returning an empty array will prevent any cache operations.
            return [];
        }
    }