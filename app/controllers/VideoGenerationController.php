<?php
// FILE: /app/controllers/VideoGenerationController.php

class VideoGenerationController extends Controller {

    public function index() {
        $this->requireAuth();

        $aiModel = $this->model('AIGeneration');
        $templateModel = $this->model('Template');

        $generations = $aiModel->getVideoGenerations(50);
        $templates = $templateModel->getTemplatesByType('video');

        $this->view->render('ai/video', [
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
        $quota = QuotaHelper::checkQuota($this->getCurrentTenantId(), 'video_generations');

        if (!$quota['allowed']) {
            $this->json(['status' => 'error', 'message' => $quota['message']], 403);
            return;
        }

        $scriptText = $_POST['script_text'] ?? '';
        $resolution = $_POST['resolution'] ?? '1920x1080';

        if (!ValidationHelper::required($scriptText)) {
            $this->json(['status' => 'error', 'message' => 'Script text is required'], 400);
            return;
        }

        // Rate limiting (stricter for video)
        if (!SecurityHelper::rateLimit('ai_video_gen', 3, 1)) {
            $this->json(['status' => 'error', 'message' => 'Rate limit exceeded'], 429);
            return;
        }

        // Generate video using AI helper
        $result = AIVideoHelper::generate($scriptText, [
            'resolution' => $resolution,
            'tenant_id' => $this->getCurrentTenantId()
        ]);

        if ($result['success']) {
            // Save to database
            $aiModel = $this->model('AIGeneration');
            $generationId = $aiModel->saveVideoGeneration([
                'tenant_id' => $this->getCurrentTenantId(),
                'user_id' => $_SESSION['user_id'],
                'script_text' => $scriptText,
                'video_path' => $result['video_path'],
                'duration_seconds' => $result['duration_seconds'],
                'resolution' => $resolution
            ]);

            // Increment quota
            QuotaHelper::incrementUsage($this->getCurrentTenantId(), 'video_generations');

            // Log activity
            $this->logActivity('videogeneration', $generationId, 'created', 'Generated AI video');

            $this->json([
                'status' => 'success',
                'video_path' => $result['video_path'],
                'id' => $generationId,
                'duration' => $result['duration_seconds']
            ]);
        } else {
            $this->json(['status' => 'error', 'message' => 'Generation failed'], 500);
        }
    }

    public function saveToContent() {
        $this->requireAuth();
        $this->validateCSRF();

        $videoGenerationId = $_POST['generation_id'] ?? 0;
        $title = $_POST['title'] ?? '';

        $aiModel = $this->model('AIGeneration');
        $aiModel->table = 'ai_videos';
        $generation = $aiModel->findById($videoGenerationId);

        if (!$generation) {
            $this->json(['status' => 'error', 'message' => 'Generation not found'], 404);
            return;
        }

        $contentModel = $this->model('Content');
        $contentId = $contentModel->create([
            'tenant_id' => $this->getCurrentTenantId(),
            'user_id' => $_SESSION['user_id'],
            'type' => 'video',
            'title' => $title ?: 'Generated Video - ' . date('Y-m-d H:i:s'),
            'content_text' => $generation['script_text'],
            'media_path' => $generation['video_path']
        ]);

        $this->logActivity('content', $contentId, 'created', 'Saved AI video to content library');

        $this->json(['status' => 'success', 'content_id' => $contentId]);
    }
}
