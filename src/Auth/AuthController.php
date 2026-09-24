<?php

declare(strict_types=1);

namespace App\Auth;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Support\Validator;

final class AuthController
{
    public function __construct(
        private readonly AuthService $auth,
        private readonly UserRepository $users,
        private readonly View $view,
    ) {
    }

    public function showRegister(Request $request): Response
    {
        return Response::html($this->view->render('auth.register', [
            'errors' => [],
            'old' => [],
        ]));
    }

    public function register(Request $request): Response
    {
        $data = [
            'name' => trim((string) $request->input('name', '')),
            'email' => strtolower(trim((string) $request->input('email', ''))),
            'password' => (string) $request->input('password', ''),
        ];

        $validator = new Validator($request->all(), [
            'name' => ['required', 'min:2', 'max:100'],
            'email' => ['required', 'email', 'max:191'],
            'password' => ['required', 'min:8', 'confirmed'],
        ]);

        if ($validator->fails()) {
            return Response::html($this->view->render('auth.register', [
                'errors' => $validator->errors(),
                'old' => $data,
            ]), 422);
        }

        if ($this->users->emailExists($data['email'])) {
            return Response::html($this->view->render('auth.register', [
                'errors' => ['email' => 'That email is already registered.'],
                'old' => $data,
            ]), 422);
        }

        $this->auth->register($data['name'], $data['email'], $data['password']);

        Session::flash('success', 'Welcome! Your account has been created.');

        return Response::redirect('/notes');
    }

    public function showLogin(Request $request): Response
    {
        return Response::html($this->view->render('auth.login', [
            'error' => null,
            'old' => [],
        ]));
    }

    public function login(Request $request): Response
    {
        $email = strtolower(trim((string) $request->input('email', '')));
        $password = (string) $request->input('password', '');
        $ipAddress = (string) $request->server('REMOTE_ADDR', '0.0.0.0');

        if ($email === '' || $password === '') {
            return Response::html($this->view->render('auth.login', [
                'error' => 'Please enter your email and password.',
                'old' => ['email' => $email],
            ]), 422);
        }

        $result = $this->auth->attemptLogin($email, $password, $ipAddress);

        if (is_string($result)) {
            return Response::html($this->view->render('auth.login', [
                'error' => $result,
                'old' => ['email' => $email],
            ]), 422);
        }

        Session::flash('success', 'Welcome back, ' . $result->name . '.');

        return Response::redirect('/notes');
    }

    public function logout(Request $request): Response
    {
        $this->auth->logout();

        return Response::redirect('/login');
    }
}
