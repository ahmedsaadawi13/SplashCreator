<?php
// FILE: /app/controllers/ScheduleController.php

class ScheduleController extends Controller {

    public function index() {
        $this->requireAuth();

        $scheduleModel = $this->model('Schedule');

        $upcomingPosts = $scheduleModel->getUpcomingPosts(50);
        $postedHistory = $scheduleModel->getPostedHistory(50);

        $this->view->render('schedule/index', [
            'user' => $this->getCurrentUser(),
            'upcoming_posts' => $upcomingPosts,
            'posted_history' => $postedHistory
        ]);
    }

    public function calendar() {
        $this->requireAuth();

        $month = $_GET['month'] ?? date('m');
        $year = $_GET['year'] ?? date('Y');

        $startDate = "{$year}-{$month}-01 00:00:00";
        $endDate = date('Y-m-t 23:59:59', strtotime($startDate));

        $scheduleModel = $this->model('Schedule');
        $calendarPosts = $scheduleModel->getCalendarView($startDate, $endDate);

        $this->view->render('schedule/calendar', [
            'user' => $this->getCurrentUser(),
            'posts' => $calendarPosts,
            'month' => $month,
            'year' => $year
        ]);
    }

    public function create() {
        $this->requireAuth();
        $this->requireRole(['tenant_admin', 'content_creator', 'social_manager']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();

            $contentId = $_POST['content_id'] ?? 0;
            $socialAccountId = $_POST['social_account_id'] ?? 0;
            $scheduledTime = $_POST['scheduled_time'] ?? '';

            $errors = [];

            if (!$contentId) {
                $errors[] = 'Content is required';
            }

            if (!$socialAccountId) {
                $errors[] = 'Social account is required';
            }

            if (!ValidationHelper::required($scheduledTime)) {
                $errors[] = 'Scheduled time is required';
            }

            // Check quota
            $quota = QuotaHelper::checkQuota($this->getCurrentTenantId(), 'scheduled_posts');
            if (!$quota['allowed']) {
                $errors[] = $quota['message'];
            }

            if (empty($errors)) {
                $socialModel = $this->model('SocialAccount');
                $socialAccount = $socialModel->findById($socialAccountId);

                $scheduleModel = $this->model('Schedule');
                $scheduleId = $scheduleModel->schedulePost([
                    'tenant_id' => $this->getCurrentTenantId(),
                    'content_item_id' => $contentId,
                    'social_account_id' => $socialAccountId,
                    'platform' => $socialAccount['platform'],
                    'scheduled_time_utc' => date('Y-m-d H:i:s', strtotime($scheduledTime)),
                    'status' => 'scheduled'
                ]);

                $this->logActivity('schedule', $scheduleId, 'scheduled', 'Scheduled new post');

                $this->json(['status' => 'success', 'schedule_id' => $scheduleId]);
                return;
            }

            $this->json(['status' => 'error', 'message' => implode(', ', $errors)], 400);
        } else {
            $contentModel = $this->model('Content');
            $socialModel = $this->model('SocialAccount');

            $content = $contentModel->findAll([], 'created_at DESC', 100);
            $socialAccounts = $socialModel->getAllActive();

            $this->view->render('schedule/create', [
                'user' => $this->getCurrentUser(),
                'content' => $content,
                'social_accounts' => $socialAccounts,
                'csrf_token' => $this->generateCSRF()
            ]);
        }
    }

    public function postNow() {
        $this->requireAuth();
        $this->requireRole(['tenant_admin', 'social_manager']);
        $this->validateCSRF();

        $contentId = $_POST['content_id'] ?? 0;
        $socialAccountId = $_POST['social_account_id'] ?? 0;

        $contentModel = $this->model('Content');
        $socialModel = $this->model('SocialAccount');

        $content = $contentModel->findById($contentId);
        $socialAccount = $socialModel->findById($socialAccountId);

        if (!$content || !$socialAccount) {
            $this->json(['status' => 'error', 'message' => 'Invalid content or social account'], 404);
            return;
        }

        // Post immediately
        $postData = [
            'text' => $content['content_text'],
            'media_path' => $content['media_path']
        ];

        $result = SocialPosterHelper::post($socialAccount['platform'], $postData, $socialAccountId);

        if ($result['status'] === 'success') {
            // Create schedule record as "posted"
            $scheduleModel = $this->model('Schedule');
            $scheduleId = $scheduleModel->create([
                'tenant_id' => $this->getCurrentTenantId(),
                'content_item_id' => $contentId,
                'social_account_id' => $socialAccountId,
                'platform' => $socialAccount['platform'],
                'scheduled_time_utc' => date('Y-m-d H:i:s'),
                'status' => 'posted',
                'log_message' => $result['message'],
                'posted_at' => date('Y-m-d H:i:s')
            ]);

            $this->logActivity('social_post', $scheduleId, 'posted', 'Posted content immediately');

            $this->json(['status' => 'success', 'message' => $result['message']]);
        } else {
            $this->json(['status' => 'error', 'message' => $result['message']], 500);
        }
    }

    public function cancel($id) {
        $this->requireAuth();
        $this->requireRole(['tenant_admin', 'social_manager']);
        $this->validateCSRF();

        $scheduleModel = $this->model('Schedule');
        $scheduleModel->update($id, ['status' => 'cancelled']);

        $this->logActivity('schedule', $id, 'cancelled', 'Cancelled scheduled post');

        $this->json(['status' => 'success']);
    }
}
