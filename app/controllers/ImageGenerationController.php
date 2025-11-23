<?php
// FILE: /app/controllers/ImageGenerationController.php

class ImageGenerationController extends Controller {

    public function index() {
        $this->requireAuth();

        $aiModel = $this->model('AIGeneration');
        $templateModel = $this->model('Template');

        $generations = $aiModel->getImageGenerations(50);
        $templates = $templateModel->getTemplatesByType('image');

        $this->view->render('ai/image', [
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
        $quota = QuotaHelper::checkQuota($this->getCurrentTenantId(), 'image_generations');

        if (!$quota['allowed']) {
            $this->json(['status' => 'error', 'message' => $quota['message']], 403);
            return;
        }

        $prompt = $_POST['prompt'] ?? '';
        $style = $_POST['style'] ?? 'realistic';
        $width = (int)($_POST['width'] ?? 1024);
        $height = (int)($_POST['height'] ?? 1024);

        if (!ValidationHelper::required($prompt)) {
            $this->json(['status' => 'error', 'message' => 'Prompt is required'], 400);
            return;
        }

        // Rate limiting
        if (!SecurityHelper::rateLimit('ai_image_gen', 5, 1)) {
            $this->json(['status' => 'error', 'message' => 'Rate limit exceeded'], 429);
            return;
        }

        // Generate image using AI helper
        $result = AIImageHelper::generate($prompt, [
            'style' => $style,
            'width' => $width,
            'height' => $height,
            'tenant_id' => $this->getCurrentTenantId()
        ]);

        if ($result['success']) {
            // Save to database
            $aiModel = $this->model('AIGeneration');
            $generationId = $aiModel->saveImageGeneration([
                'tenant_id' => $this->getCurrentTenantId(),
                'user_id' => $_SESSION['user_id'],
                'prompt' => $prompt,
                'image_path' => $result['image_path'],
                'style' => $style,
                'width' => $width,
                'height' => $height
            ]);

            // Increment quota
            QuotaHelper::incrementUsage($this->getCurrentTenantId(), 'image_generations');

            // Log activity
            $this->logActivity('imagegen', $generationId, 'created', 'Generated AI image');

            $this->json([
                'status' => 'success',
                'image_path' => $result['image_path'],
                'id' => $generationId
            ]);
        } else {
            $this->json(['status' => 'error', 'message' => 'Generation failed'], 500);
        }
    }

    public function saveToContent() {
        $this->requireAuth();
        $this->validateCSRF();

        $imageGenerationId = $_POST['generation_id'] ?? 0;
        $title = $_POST['title'] ?? '';

        $aiModel = $this->model('AIGeneration');
        $aiModel->table = 'ai_images';
        $generation = $aiModel->findById($imageGenerationId);

        if (!$generation) {
            $this->json(['status' => 'error', 'message' => 'Generation not found'], 404);
            return;
        }

        $contentModel = $this->model('Content');
        $contentId = $contentModel->create([
            'tenant_id' => $this->getCurrentTenantId(),
            'user_id' => $_SESSION['user_id'],
            'type' => 'image',
            'title' => $title ?: 'Generated Image - ' . date('Y-m-d H:i:s'),
            'media_path' => $generation['image_path']
        ]);

        $this->logActivity('content', $contentId, 'created', 'Saved AI image to content library');

        $this->json(['status' => 'success', 'content_id' => $contentId]);
    }
}
