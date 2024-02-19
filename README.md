# hotellidiilid-hotelbeds-api

New Hotellidiilid v2.0 Hotelbeds API integration. (PHP/Laravel)

---------------- Passport token authentication ---------------

# Requesting password grant token

- Retrieving token
  $response = Http::asForm()->post('http://Hotellidiilid.com/oauth/token',
  [
  'grant_type' => 'password',
  'client_id' => 'client-id',
  'client_secret' => 'client-secret',
  'username' => 'user / admin email',
  'password' => 'password',
  'scope' => '',
  ]);

scope : '\*' - for all scopes, 'admin' - for admin scope, 'user' - for client scope

# Requesting client credentials grant token

- passport configurations

  - add the 'CheckClientCredentials' middleware to the $routeMiddleware property in kernel.php
    protected $routeMiddleware = [
    'client' => CheckClientCredentials::class,
    ];

  - attach the middleware to a route
    Route::get('/publicRoute', function (Request $request) {
    ...
    })->middleware('client');

  - restrict access to the route to specific scopes
    Route::get('/scopedroute', function (Request $request) {
    ...
    })->middleware('client:admin');

- Retrieving token
  $response = Http::asForm()->post('http://Hotellidiilid.com/oauth/token', [
  'grant_type' => 'client_credentials',
  'client_id' => 'client-id',
  'client_secret' => 'client-secret',
  'scope' => 'scope',
  ]);

# Hotelbeds api keys are saved against client id in the databse

# MongoDB configurations

    - https://programming.vip/docs/using-mongodb-in-laravel.html
    - MongoDB configured using Jensseger/MongoDB
    - Mongodb passport fix - in order to enable passport with mongoDB (https://github.com/sadnub/laravel-mongodb-passport-fix) - php artisan fix:passport
        - Without this fix it creates the following error when creating clients (php artisan passport:install)

        - this replaces 'Illuminate\Database\Eloquent\Model;' with 'Jenssegers\Mongodb\Eloquent\Model;' in vendor/laravel/passport/src

    - In user and admin models, other than extending the 'Jenssegers\Mongodb\Eloquent\Model' it needs to implement following inorder for the token issuing to work with mongoDB
        - 'Illuminate\Contracts\Auth\Authenticatable'
        - 'Illuminate\Contracts\Auth\Access\Authorizable'
        - 'Illuminate\Contracts\Auth\CanResetPassword'

    - Implement localization on laravel-mongo with the use of https://github.com/spatie/laravel-translatable
        - Write a middleware to capture lang preference passed through X-localization and produce response accordingly
        - https://github.com/codezero-be/laravel-unique-translation - for uniques translation entries

# for Enums, used https://github.com/BenSampo/laravel-enum

# Note: Following might be usefull in the future to work with mongodb

    - https://github.com/designmynight/laravel-mongodb-passport
    - https://moloquent.github.io/master/

# For using redis

- installed predis by 'composer require predis/predis'
- configure attributes under 'redis' in config/databse.php if needed
- changed SESSION_DRIVER=file to SESSION_DRIVER=redis

# the logic is basically maintained within repositories.

- a route may direct to a method in a controller and within the method it invokes a method in the relevant repository
- repository logic is to work with mongodb. to change the implementation into another, need to alter repository class paths in RepositoryServiceProvider to new implmentation paths, So that the controller will remain the same.
