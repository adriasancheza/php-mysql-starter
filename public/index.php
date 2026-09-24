<?php

declare(strict_types=1);

use App\Auth\AuthController;
use App\Auth\AuthService;
use App\Auth\RateLimiter;
use App\Auth\UserRepository;
use App\Core\Database;
use App\Core\Middleware\AuthMiddleware;
use App\Core\Middleware\CsrfMiddleware;
use App\Core\Request;
use App\Core\Response;
use App\Core\Router;
use App\Core\Session;
use App\Core\View;
use App\Notes\NoteRepository;
use App\Notes\NotesController;

require dirname(__DIR__) . '/src/bootstrap.php';

Session::start();

$pdo = Database::connection();
$view = new View(dirname(__DIR__) . '/views');

$users = new UserRepository($pdo);
$rateLimiter = new RateLimiter($pdo);
$auth = new AuthService($users, $rateLimiter);
$authController = new AuthController($auth, $users, $view);

$notes = new NoteRepository($pdo);
$notesController = new NotesController($notes, $auth, $view);

$request = Request::fromGlobals();
$router = new Router();

// CSRF check runs first for every non-GET request.
$csrfFailure = CsrfMiddleware::handle($request);
if ($csrfFailure instanceof Response) {
    $csrfFailure->send();

    return;
}

$router->get('/', function (Request $request) use ($view): Response {
    return Response::html($view->render('home.welcome'));
});

$router->get('/register', $authController->showRegister(...));
$router->post('/register', $authController->register(...));
$router->get('/login', $authController->showLogin(...));
$router->post('/login', $authController->login(...));
$router->post('/logout', $authController->logout(...));

$guard = static function (callable $handler) use ($auth): callable {
    return static function (Request $request) use ($auth, $handler): Response {
        $redirect = AuthMiddleware::handle($request, $auth);
        if ($redirect instanceof Response) {
            return $redirect;
        }

        return $handler($request);
    };
};

$router->get('/notes', $guard($notesController->index(...)));
$router->get('/notes/create', $guard($notesController->create(...)));
$router->post('/notes', $guard($notesController->store(...)));
$router->get('/notes/{id}/edit', $guard($notesController->edit(...)));
$router->put('/notes/{id}', $guard($notesController->update(...)));
$router->post('/notes/{id}/delete', $guard($notesController->destroy(...)));

$response = $router->dispatch($request);
$response->send();
