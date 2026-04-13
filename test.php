<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::first();
Auth::login($user);

// Create request matching the user
$request = Illuminate\Http\Request::create('/groups/1/force-delete', 'GET');
$request->setUserResolver(function () use ($user) {
    return $user;
});

foreach (App\Models\Group::all() as $group) {
    try {
        app(App\Http\Controllers\GroupController::class)->destroy($request, $group);
        echo "Deleted group {$group->id}\n";
    } catch (\Throwable $e) {
        echo "Failed group {$group->id}: " . $e->getMessage() . "\n";
    }
}
