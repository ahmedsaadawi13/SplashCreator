<?php
// FILE: /app/controllers/DashboardController.php

class DashboardController extends Controller {

    public function index() {
        $this->requireAuth();

        $contentModel = $this->model('Content');
        $scheduleModel = $this->model('Schedule');
        $analyticsModel = $this->model('Analytics');
        $aiModel = $this->model('AIGeneration');

        // Get usage stats
        $usageStats = QuotaHelper::getUsageStats($this->getCurrentTenantId());

        // Get recent content
        $recentContent = $contentModel->getRecentContent(5);

        // Get upcoming posts
        $upcomingPosts = $scheduleModel->getUpcomingPosts(5);

        // Get analytics overview
        $analyticsOverview = $analyticsModel->getDashboardStats();

        // Get recent AI generations
        $recentGenerations = $aiModel->getRecentGenerations(5);

        $data = [
            'user' => $this->getCurrentUser(),
            'usage_stats' => $usageStats,
            'recent_content' => $recentContent,
            'upcoming_posts' => $upcomingPosts,
            'analytics' => $analyticsOverview,
            'recent_generations' => $recentGenerations
        ];

        $this->view->render('dashboard/index', $data);
    }

    public function usage() {
        $this->requireAuth();

        $usageStats = QuotaHelper::getUsageStats($this->getCurrentTenantId());

        $this->view->render('dashboard/usage', [
            'user' => $this->getCurrentUser(),
            'usage_stats' => $usageStats
        ]);
    }
}
