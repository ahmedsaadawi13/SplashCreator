<?php
// FILE: /app/controllers/TextGenerationController.php

class TextGenerationController extends Controller {

    public function index() {
        $this->requireAuth();

        $aiModel = $this->model('AIGeneration');
        $templateModel = $this->model('Template');

        $generations = $aiModel->getTextGenerations(50);
        $templates = $templateModel->getTemplatesByType('text');

        $this->view->render('ai/text', [
            'user' => $this->getCurrentUser(),
            'generations' => $generations,
            'templates' => $templates,
            'csrf_token' => $this->generateCSRF()
        ]);
    }

    public function generate() {
        $this->requireAuth();
        $this->validateCSRF();

        // Check quota
        $quota = QuotaHelper::checkQuota($this->getCurrentTenantId(), 'text_generations');

        if (!$quota['allowed']) {
            $this->json(['status' => 'error', 'message' => $quota['message']], 403);
            return;
        }

        $prompt = $_POST['prompt'] ?? '';
        $tone = $_POST['tone'] ?? 'professional';

        if (!ValidationHelper::required($prompt)) {
            $this->json(['status' => 'error', 'message' => 'Prompt is required'], 400);
            return;
        }

        // Rate limiting
        if (!SecurityHelper::rateLimit('ai_text_gen', 10, 1)) {
            $this->json(['status' => 'error', 'message' => 'Rate limit exceeded'], 429);
            return;
        }

        // Generate text using AI helper
        $result = AITextHelper::generate($prompt, ['tone' => $tone]);

        if ($result['success']) {
            // Save to database
            $aiModel = $this->model('AIGeneration');
            $generationId = $aiModel->saveTextGeneration([
                'tenant_id' => $this->getCurrentTenantId(),
                'user_id' => $_SESSION['user_id'],
                'prompt' => $prompt,
                'result_text' => $result['text'],
                'model_used' => $result['model'],
                'tokens_count' => $result['tokens']
            ]);

            // Increment quota
            QuotaHelper::incrementUsage($this->getCurrentTenantId(), 'text_generations');

            // Log activity
            $this->logActivity('textgen', $generationId, 'created', 'Generated AI text');

            $this->json([
                'status' => 'success',
                'text' => $result['text'],
                'id' => $generationId,
                'tokens' => $result['tokens']
            ]);
        } else {
            $this->json(['status' => 'error', 'message' => 'Generation failed'], 500);
        }
    }

    public function saveToContent() {
        $this->requireAuth();
        $this->validateCSRF();

        $textGenerationId = $_POST['generation_id'] ?? 0;
        $title = $_POST['title'] ?? '';

        $aiModel = $this->model('AIGeneration');
        $aiModel->table = 'ai_text_requests';
        $generation = $aiModel->findById($textGenerationId);

        if (!$generation) {
            $this->json(['status' => 'error', 'message' => 'Generation not found'], 404);
            return;
        }

        $contentModel = $this->model('Content');
        $contentId = $contentModel->create([
            'tenant_id' => $this->getCurrentTenantId(),
            'user_id' => $_SESSION['user_id'],
            'type' => 'text',
            'title' => $title ?: 'Generated Text - ' . date('Y-m-d H:i:s'),
            'content_text' => $generation['result_text']
        ]);

        $this->logActivity('content', $contentId, 'created', 'Saved AI text to content library');

        $this->json(['status' => 'success', 'content_id' => $contentId]);
    }
}
