<?php
// FILE: /app/controllers/APIController.php

class APIController extends Controller {

    private $apiKey;
    private $tenantId;

    public function __construct() {
        parent::__construct();

        // Authenticate API requests
        $this->authenticateAPI();
    }

    private function authenticateAPI() {
        $apiKey = $_SERVER['HTTP_X_API_KEY'] ?? '';

        if (!$apiKey) {
            $this->json(['status' => 'error', 'message' => 'API key required', 'code' => 401], 401);
            exit;
        }

        $sql = "SELECT tak.*, t.status as tenant_status
                FROM tenant_api_keys tak
                JOIN tenants t ON tak.tenant_id = t.id
                WHERE tak.api_key = :api_key AND tak.is_active = 1";

        $keyData = $this->db->fetch($sql, ['api_key' => $apiKey]);

        if (!$keyData || $keyData['tenant_status'] !== 'active') {
            $this->json(['status' => 'error', 'message' => 'Invalid or inactive API key', 'code' => 401], 401);
            exit;
        }

        $this->apiKey = $apiKey;
        $this->tenantId = $keyData['tenant_id'];

        // Update last used timestamp
        $sql = "UPDATE tenant_api_keys SET last_used_at = NOW() WHERE api_key = :api_key";
        $this->db->execute($sql, ['api_key' => $apiKey]);

        // Set tenant in session for model scope
        $_SESSION['tenant_id'] = $this->tenantId;
        $_SESSION['api_request'] = true;
    }

    // POST /api/text/generate
    public function textGenerate() {
        $input = json_decode(file_get_contents('php://input'), true);

        $prompt = $input['prompt'] ?? '';
        $tone = $input['tone'] ?? 'professional';

        if (!ValidationHelper::required($prompt)) {
            $this->json(['status' => 'error', 'message' => 'Prompt is required', 'code' => 400], 400);
            return;
        }

        // Check quota
        $quota = QuotaHelper::checkQuota($this->tenantId, 'text_generations');
        if (!$quota['allowed']) {
            $this->json(['status' => 'error', 'message' => $quota['message'], 'code' => 403], 403);
            return;
        }

        // Generate text
        $result = AITextHelper::generate($prompt, ['tone' => $tone]);

        if ($result['success']) {
            // Save to database
            $aiModel = $this->model('AIGeneration');
            $generationId = $aiModel->saveTextGeneration([
                'tenant_id' => $this->tenantId,
                'user_id' => null,
                'prompt' => $prompt,
                'result_text' => $result['text'],
                'model_used' => $result['model'],
                'tokens_count' => $result['tokens']
            ]);

            // Increment quota
            QuotaHelper::incrementUsage($this->tenantId, 'text_generations');

            $this->json([
                'status' => 'success',
                'data' => [
                    'id' => $generationId,
                    'text' => $result['text'],
                    'tokens' => $result['tokens'],
                    'model' => $result['model']
                ]
            ]);
        } else {
            $this->json(['status' => 'error', 'message' => 'Generation failed', 'code' => 500], 500);
        }
    }

    // POST /api/image/generate
    public function imageGenerate() {
        $input = json_decode(file_get_contents('php://input'), true);

        $prompt = $input['prompt'] ?? '';
        $style = $input['style'] ?? 'realistic';
        $width = (int)($input['width'] ?? 1024);
        $height = (int)($input['height'] ?? 1024);

        if (!ValidationHelper::required($prompt)) {
            $this->json(['status' => 'error', 'message' => 'Prompt is required', 'code' => 400], 400);
            return;
        }

        // Check quota
        $quota = QuotaHelper::checkQuota($this->tenantId, 'image_generations');
        if (!$quota['allowed']) {
            $this->json(['status' => 'error', 'message' => $quota['message'], 'code' => 403], 403);
            return;
        }

        // Generate image
        $result = AIImageHelper::generate($prompt, [
            'style' => $style,
            'width' => $width,
            'height' => $height,
            'tenant_id' => $this->tenantId
        ]);

        if ($result['success']) {
            // Save to database
            $aiModel = $this->model('AIGeneration');
            $generationId = $aiModel->saveImageGeneration([
                'tenant_id' => $this->tenantId,
                'user_id' => null,
                'prompt' => $prompt,
                'image_path' => $result['image_path'],
                'style' => $style,
                'width' => $width,
                'height' => $height
            ]);

            // Increment quota
            QuotaHelper::incrementUsage($this->tenantId, 'image_generations');

            $this->json([
                'status' => 'success',
                'data' => [
                    'id' => $generationId,
                    'image_path' => $result['image_path'],
                    'width' => $width,
                    'height' => $height
                ]
            ]);
        } else {
            $this->json(['status' => 'error', 'message' => 'Generation failed', 'code' => 500], 500);
        }
    }

    // POST /api/video/generate
    public function videoGenerate() {
        $input = json_decode(file_get_contents('php://input'), true);

        $scriptText = $input['script_text'] ?? '';
        $resolution = $input['resolution'] ?? '1920x1080';

        if (!ValidationHelper::required($scriptText)) {
            $this->json(['status' => 'error', 'message' => 'Script text is required', 'code' => 400], 400);
            return;
        }

        // Check quota
        $quota = QuotaHelper::checkQuota($this->tenantId, 'video_generations');
        if (!$quota['allowed']) {
            $this->json(['status' => 'error', 'message' => $quota['message'], 'code' => 403], 403);
            return;
        }

        // Generate video
        $result = AIVideoHelper::generate($scriptText, [
            'resolution' => $resolution,
            'tenant_id' => $this->tenantId
        ]);

        if ($result['success']) {
            // Save to database
            $aiModel = $this->model('AIGeneration');
            $generationId = $aiModel->saveVideoGeneration([
                'tenant_id' => $this->tenantId,
                'user_id' => null,
                'script_text' => $scriptText,
                'video_path' => $result['video_path'],
                'duration_seconds' => $result['duration_seconds'],
                'resolution' => $resolution
            ]);

            // Increment quota
            QuotaHelper::incrementUsage($this->tenantId, 'video_generations');

            $this->json([
                'status' => 'success',
                'data' => [
                    'id' => $generationId,
                    'video_path' => $result['video_path'],
                    'duration_seconds' => $result['duration_seconds']
                ]
            ]);
        } else {
            $this->json(['status' => 'error', 'message' => 'Generation failed', 'code' => 500], 500);
        }
    }

    // POST /api/content/create
    public function contentCreate() {
        $input = json_decode(file_get_contents('php://input'), true);

        $title = $input['title'] ?? '';
        $type = $input['type'] ?? 'text';
        $contentText = $input['content_text'] ?? '';
        $mediaPath = $input['media_path'] ?? null;

        if (!ValidationHelper::required($title)) {
            $this->json(['status' => 'error', 'message' => 'Title is required', 'code' => 400], 400);
            return;
        }

        if (!ValidationHelper::inEnum($type, ['text', 'image', 'video'])) {
            $this->json(['status' => 'error', 'message' => 'Invalid type', 'code' => 400], 400);
            return;
        }

        $contentModel = $this->model('Content');
        $contentId = $contentModel->create([
            'tenant_id' => $this->tenantId,
            'user_id' => null,
            'type' => $type,
            'title' => $title,
            'content_text' => $contentText,
            'media_path' => $mediaPath
        ]);

        $this->json([
            'status' => 'success',
            'data' => [
                'id' => $contentId,
                'title' => $title,
                'type' => $type
            ]
        ]);
    }

    // POST /api/schedule/create
    public function scheduleCreate() {
        $input = json_decode(file_get_contents('php://input'), true);

        $contentId = $input['content_id'] ?? 0;
        $socialAccountId = $input['social_account_id'] ?? 0;
        $scheduledTime = $input['scheduled_time'] ?? '';

        if (!$contentId || !$socialAccountId || !$scheduledTime) {
            $this->json(['status' => 'error', 'message' => 'Missing required fields', 'code' => 400], 400);
            return;
        }

        // Check quota
        $quota = QuotaHelper::checkQuota($this->tenantId, 'scheduled_posts');
        if (!$quota['allowed']) {
            $this->json(['status' => 'error', 'message' => $quota['message'], 'code' => 403], 403);
            return;
        }

        $socialModel = $this->model('SocialAccount');
        $socialAccount = $socialModel->findById($socialAccountId);

        if (!$socialAccount) {
            $this->json(['status' => 'error', 'message' => 'Social account not found', 'code' => 404], 404);
            return;
        }

        $scheduleModel = $this->model('Schedule');
        $scheduleId = $scheduleModel->schedulePost([
            'tenant_id' => $this->tenantId,
            'content_item_id' => $contentId,
            'social_account_id' => $socialAccountId,
            'platform' => $socialAccount['platform'],
            'scheduled_time_utc' => date('Y-m-d H:i:s', strtotime($scheduledTime)),
            'status' => 'scheduled'
        ]);

        $this->json([
            'status' => 'success',
            'data' => [
                'schedule_id' => $scheduleId,
                'scheduled_time' => $scheduledTime
            ]
        ]);
    }

    // GET /api/content
    public function contentList() {
        $type = $_GET['type'] ?? null;
        $limit = min((int)($_GET['limit'] ?? 50), 100);

        $contentModel = $this->model('Content');

        if ($type && in_array($type, ['text', 'image', 'video'])) {
            $content = $contentModel->getContentByType($type, $limit);
        } else {
            $content = $contentModel->findAll([], 'created_at DESC', $limit);
        }

        $this->json([
            'status' => 'success',
            'data' => $content
        ]);
    }
}
