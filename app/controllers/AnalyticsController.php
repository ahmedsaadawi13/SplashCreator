<?php
// FILE: /app/controllers/AnalyticsController.php

class AnalyticsController extends Controller {

    public function index() {
        $this->requireAuth();

        $analyticsModel = $this->model('Analytics');

        $overallMetrics = $analyticsModel->getOverallMetrics();
        $dashboardStats = $analyticsModel->getDashboardStats();
        $topContent = $analyticsModel->getTopPerformingContent('impressions', 10);

        $this->view->render('analytics/index', [
            'user' => $this->getCurrentUser(),
            'overall_metrics' => $overallMetrics,
            'dashboard_stats' => $dashboardStats,
            'top_content' => $topContent
        ]);
    }

    public function platform($platform) {
        $this->requireAuth();

        $allowedPlatforms = ['instagram', 'facebook', 'twitter', 'tiktok', 'linkedin', 'youtube'];
        if (!in_array($platform, $allowedPlatforms)) {
            http_response_code(404);
            $this->view->render('errors/404', ['message' => 'Platform not found']);
            return;
        }

        $analyticsModel = $this->model('Analytics');
        $metrics = $analyticsModel->getMetricsByPlatform($platform, 50);

        $this->view->render('analytics/platform', [
            'user' => $this->getCurrentUser(),
            'platform' => $platform,
            'metrics' => $metrics
        ]);
    }

    public function content($contentId) {
        $this->requireAuth();

        $analyticsModel = $this->model('Analytics');
        $contentModel = $this->model('Content');

        $content = $contentModel->findById($contentId);

        if (!$content) {
            http_response_code(404);
            $this->view->render('errors/404', ['message' => 'Content not found']);
            return;
        }

        $performance = $analyticsModel->getContentPerformance($contentId);

        $this->view->render('analytics/content', [
            'user' => $this->getCurrentUser(),
            'content' => $content,
            'performance' => $performance
        ]);
    }
}
