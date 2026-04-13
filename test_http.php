<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Login
$user = App\Models\User::first();
Auth::login($user);

// Simulate internal request
$request = Illuminate\Http\Request::create('/groups/2', 'DELETE');
$request->setUserResolver(function() use ($user) { return $user; });
// Bypass CSRF for internal test
$app->make('router')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

$response = $kernel->handle($request);

echo "Status Code: " . $response->getStatusCode() . "\n";
echo "Redirect Target: " . $response->headers->get('Location') . "\n";
if ($response->getStatusCode() != 302) {
    echo "Content: \n" . strip_tags(substr($response->getContent(), 0, 500));
}
