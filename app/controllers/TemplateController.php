<?php
// FILE: /app/controllers/TemplateController.php

class TemplateController extends Controller {

    public function index() {
        $this->requireAuth();

        $templateModel = $this->model('Template');
        $type = $_GET['type'] ?? null;

        $globalTemplates = $templateModel->getGlobalTemplates($type);
        $tenantTemplates = $templateModel->getTenantTemplates($type);

        $this->view->render('templates/index', [
            'user' => $this->getCurrentUser(),
            'global_templates' => $globalTemplates,
            'tenant_templates' => $tenantTemplates,
            'current_type' => $type
        ]);
    }

    public function create() {
        $this->requireAuth();
        $this->requireRole(['tenant_admin', 'content_creator']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();

            $title = $_POST['title'] ?? '';
            $type = $_POST['type'] ?? 'text';
            $promptTemplate = $_POST['prompt_template'] ?? '';
            $category = $_POST['category'] ?? 'general';

            $errors = [];

            if (!ValidationHelper::required($title)) {
                $errors[] = 'Title is required';
            }

            if (!ValidationHelper::inEnum($type, ['text', 'image', 'video'])) {
                $errors[] = 'Invalid type';
            }

            if (!ValidationHelper::required($promptTemplate)) {
                $errors[] = 'Prompt template is required';
            }

            if (empty($errors)) {
                $templateModel = $this->model('Template');
                $templateId = $templateModel->createTemplate([
                    'tenant_id' => $this->getCurrentTenantId(),
                    'type' => $type,
                    'title' => $title,
                    'prompt_template' => $promptTemplate,
                    'category' => $category
                ]);

                $this->logActivity('template', $templateId, 'created', 'Created template');

                $this->redirect('/templates');
                return;
            }

            $this->view->render('templates/create', [
                'user' => $this->getCurrentUser(),
                'errors' => $errors,
                'old' => $_POST,
                'csrf_token' => $this->generateCSRF()
            ]);
        } else {
            $this->view->render('templates/create', [
                'user' => $this->getCurrentUser(),
                'csrf_token' => $this->generateCSRF()
            ]);
        }
    }

    public function delete($id) {
        $this->requireAuth();
        $this->requireRole(['tenant_admin']);
        $this->validateCSRF();

        $templateModel = $this->model('Template');
        $templateModel->delete($id);

        $this->logActivity('template', $id, 'deleted', 'Deleted template');

        $this->json(['status' => 'success']);
    }

    public function get($id) {
        $this->requireAuth();

        $templateModel = $this->model('Template');
        $templateModel->tenantScope = false;
        $template = $templateModel->findById($id);

        if (!$template) {
            $this->json(['status' => 'error', 'message' => 'Template not found'], 404);
            return;
        }

        // Check if template belongs to tenant or is global
        if ($template['tenant_id'] !== null && $template['tenant_id'] != $this->getCurrentTenantId()) {
            $this->json(['status' => 'error', 'message' => 'Access denied'], 403);
            return;
        }

        $this->json(['status' => 'success', 'template' => $template]);
    }
}
