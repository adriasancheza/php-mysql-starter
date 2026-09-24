<?php

declare(strict_types=1);

namespace App\Notes;

use App\Auth\AuthService;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Support\Validator;

final class NotesController
{
    public function __construct(
        private readonly NoteRepository $notes,
        private readonly AuthService $auth,
        private readonly View $view,
    ) {
    }

    public function index(Request $request): Response
    {
        $user = $this->requireUser();

        $notes = $this->notes->allForUser($user->id);

        return Response::html($this->view->render('notes.index', [
            'user' => $user,
            'notes' => $notes,
        ]));
    }

    public function create(Request $request): Response
    {
        $this->requireUser();

        return Response::html($this->view->render('notes.create', [
            'errors' => [],
            'old' => [],
        ]));
    }

    public function store(Request $request): Response
    {
        $user = $this->requireUser();

        $data = [
            'title' => trim((string) $request->input('title', '')),
            'body' => trim((string) $request->input('body', '')),
        ];

        $validator = new Validator($request->all(), [
            'title' => ['required', 'max:191'],
            'body' => ['required', 'max:10000'],
        ]);

        if ($validator->fails()) {
            return Response::html($this->view->render('notes.create', [
                'errors' => $validator->errors(),
                'old' => $data,
            ]), 422);
        }

        $this->notes->create($user->id, $data['title'], $data['body']);

        Session::flash('success', 'Note created.');

        return Response::redirect('/notes');
    }

    public function edit(Request $request): Response
    {
        $user = $this->requireUser();
        $id = (int) $request->routeParam('id');

        $note = $this->notes->findForUser($id, $user->id);
        if ($note === null) {
            return Response::notFound();
        }

        return Response::html($this->view->render('notes.edit', [
            'note' => $note,
            'errors' => [],
        ]));
    }

    public function update(Request $request): Response
    {
        $user = $this->requireUser();
        $id = (int) $request->routeParam('id');

        $note = $this->notes->findForUser($id, $user->id);
        if ($note === null) {
            return Response::notFound();
        }

        $title = trim((string) $request->input('title', ''));
        $body = trim((string) $request->input('body', ''));

        $validator = new Validator($request->all(), [
            'title' => ['required', 'max:191'],
            'body' => ['required', 'max:10000'],
        ]);

        if ($validator->fails()) {
            return Response::html($this->view->render('notes.edit', [
                'note' => $note,
                'errors' => $validator->errors(),
            ]), 422);
        }

        $this->notes->update($id, $user->id, $title, $body);

        Session::flash('success', 'Note updated.');

        return Response::redirect('/notes');
    }

    public function destroy(Request $request): Response
    {
        $user = $this->requireUser();
        $id = (int) $request->routeParam('id');

        $this->notes->delete($id, $user->id);

        Session::flash('success', 'Note deleted.');

        return Response::redirect('/notes');
    }

    private function requireUser(): \App\Auth\User
    {
        $user = $this->auth->currentUser();
        if ($user === null) {
            // In a real app this would throw a redirect exception caught by
            // the front controller; kept simple here for a starter kit.
            throw new \RuntimeException('Not authenticated. Route should be guarded by AuthMiddleware.');
        }

        return $user;
    }
}
