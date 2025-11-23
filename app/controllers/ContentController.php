<?php
// FILE: /app/controllers/ContentController.php

class ContentController extends Controller {

    public function index() {
        $this->requireAuth();

        $contentModel = $this->model('Content');
        $type = $_GET['type'] ?? null;

        if ($type && !in_array($type, ['text', 'image', 'video'])) {
            $type = null;
        }

        $content = $type ? $contentModel->getContentByType($type, 50) : $contentModel->findAll([], 'created_at DESC', 50);

        $this->view->render('content/index', [
            'user' => $this->getCurrentUser(),
            'content' => $content,
            'current_type' => $type
        ]);
    }

    public function view($id) {
        $this->requireAuth();

        $contentModel = $this->model('Content');
        $content = $contentModel->getWithUser($id);

        if (!$content) {
            http_response_code(404);
            $this->view->render('errors/404', ['message' => 'Content not found']);
            return;
        }

        $comments = $contentModel->getComments($id);

        $this->view->render('content/view', [
            'user' => $this->getCurrentUser(),
            'content' => $content,
            'comments' => $comments,
            'csrf_token' => $this->generateCSRF()
        ]);
    }

    public function create() {
        $this->requireAuth();
        $this->requireRole(['tenant_admin', 'content_creator', 'social_manager']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();

            $title = $_POST['title'] ?? '';
            $type = $_POST['type'] ?? 'text';
            $contentText = $_POST['content_text'] ?? '';
            $tags = $_POST['tags'] ?? '';

            $errors = [];

            if (!ValidationHelper::required($title)) {
                $errors[] = 'Title is required';
            }

            if (!ValidationHelper::inEnum($type, ['text', 'image', 'video'])) {
                $errors[] = 'Invalid content type';
            }

            if (empty($errors)) {
                $contentModel = $this->model('Content');

                $tagsArray = $tags ? array_map('trim', explode(',', $tags)) : [];

                $contentId = $contentModel->create([
                    'tenant_id' => $this->getCurrentTenantId(),
                    'user_id' => $_SESSION['user_id'],
                    'type' => $type,
                    'title' => $title,
                    'content_text' => $contentText,
                    'tags_json' => json_encode($tagsArray)
                ]);

                $this->logActivity('content', $contentId, 'created', 'Created new content item');

                $this->redirect('/content/view/' . $contentId);
                return;
            }

            $this->view->render('content/create', [
                'user' => $this->getCurrentUser(),
                'errors' => $errors,
                'old' => $_POST,
                'csrf_token' => $this->generateCSRF()
            ]);
        } else {
            $this->view->render('content/create', [
                'user' => $this->getCurrentUser(),
                'csrf_token' => $this->generateCSRF()
            ]);
        }
    }

    public function edit($id) {
        $this->requireAuth();
        $this->requireRole(['tenant_admin', 'content_creator', 'social_manager']);

        $contentModel = $this->model('Content');
        $content = $contentModel->findById($id);

        if (!$content) {
            http_response_code(404);
            $this->view->render('errors/404', ['message' => 'Content not found']);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();

            $title = $_POST['title'] ?? '';
            $contentText = $_POST['content_text'] ?? '';
            $tags = $_POST['tags'] ?? '';

            $contentModel->update($id, [
                'title' => $title,
                'content_text' => $contentText,
                'tags_json' => json_encode(array_map('trim', explode(',', $tags)))
            ]);

            $this->logActivity('content', $id, 'updated', 'Updated content item');

            $this->redirect('/content/view/' . $id);
        } else {
            $tagsArray = json_decode($content['tags_json'], true) ?? [];
            $content['tags_string'] = implode(', ', $tagsArray);

            $this->view->render('content/edit', [
                'user' => $this->getCurrentUser(),
                'content' => $content,
                'csrf_token' => $this->generateCSRF()
            ]);
        }
    }

    public function delete($id) {
        $this->requireAuth();
        $this->requireRole(['tenant_admin', 'content_creator']);
        $this->validateCSRF();

        $contentModel = $this->model('Content');
        $contentModel->delete($id);

        $this->logActivity('content', $id, 'deleted', 'Deleted content item');

        $this->redirect('/content');
    }

    public function addComment() {
        $this->requireAuth();
        $this->validateCSRF();

        $contentId = $_POST['content_id'] ?? 0;
        $commentText = $_POST['comment_text'] ?? '';

        if (!ValidationHelper::required($commentText)) {
            $this->json(['status' => 'error', 'message' => 'Comment text is required'], 400);
            return;
        }

        $contentModel = $this->model('Content');
        $contentModel->addComment($contentId, $_SESSION['user_id'], $commentText);

        $this->logActivity('content', $contentId, 'commented', 'Added comment to content');

        $this->json(['status' => 'success']);
    }
}
