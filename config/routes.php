<?php
// FILE: /config/routes.php

$router = new Router();

// Authentication Routes
$router->get('/', 'DashboardController', 'index');
$router->get('/auth/login', 'AuthController', 'login');
$router->post('/auth/login', 'AuthController', 'login');
$router->get('/auth/register', 'AuthController', 'register');
$router->post('/auth/register', 'AuthController', 'register');
$router->get('/auth/logout', 'AuthController', 'logout');

// Dashboard Routes
$router->get('/dashboard', 'DashboardController', 'index');
$router->get('/dashboard/usage', 'DashboardController', 'usage');

// AI Text Generation Routes
$router->get('/ai/text', 'TextGenerationController', 'index');
$router->post('/ai/text/generate', 'TextGenerationController', 'generate');
$router->post('/ai/text/save', 'TextGenerationController', 'saveToContent');

// AI Image Generation Routes
$router->get('/ai/image', 'ImageGenerationController', 'index');
$router->post('/ai/image/generate', 'ImageGenerationController', 'generate');
$router->post('/ai/image/save', 'ImageGenerationController', 'saveToContent');

// AI Video Generation Routes
$router->get('/ai/video', 'VideoGenerationController', 'index');
$router->post('/ai/video/generate', 'VideoGenerationController', 'generate');
$router->post('/ai/video/save', 'VideoGenerationController', 'saveToContent');

// Content Routes
$router->get('/content', 'ContentController', 'index');
$router->get('/content/create', 'ContentController', 'create');
$router->post('/content/create', 'ContentController', 'create');
$router->get('/content/view/:id', 'ContentController', 'view');
$router->get('/content/edit/:id', 'ContentController', 'edit');
$router->post('/content/edit/:id', 'ContentController', 'edit');
$router->post('/content/delete/:id', 'ContentController', 'delete');
$router->post('/content/comment', 'ContentController', 'addComment');

// Schedule Routes
$router->get('/schedule', 'ScheduleController', 'index');
$router->get('/schedule/calendar', 'ScheduleController', 'calendar');
$router->get('/schedule/create', 'ScheduleController', 'create');
$router->post('/schedule/create', 'ScheduleController', 'create');
$router->post('/schedule/post-now', 'ScheduleController', 'postNow');
$router->post('/schedule/cancel/:id', 'ScheduleController', 'cancel');

// Social Media Routes
$router->get('/social', 'SocialController', 'index');
$router->get('/social/connect', 'SocialController', 'connect');
$router->post('/social/connect', 'SocialController', 'connect');
$router->post('/social/disconnect/:id', 'SocialController', 'disconnect');
$router->get('/social/stats/:id', 'SocialController', 'stats');

// Brand Kit Routes
$router->get('/brand', 'BrandController', 'index');
$router->get('/brand/create', 'BrandController', 'create');
$router->post('/brand/create', 'BrandController', 'create');
$router->get('/brand/edit/:id', 'BrandController', 'edit');
$router->post('/brand/edit/:id', 'BrandController', 'edit');
$router->post('/brand/delete/:id', 'BrandController', 'delete');

// Analytics Routes
$router->get('/analytics', 'AnalyticsController', 'index');
$router->get('/analytics/platform/:platform', 'AnalyticsController', 'platform');
$router->get('/analytics/content/:contentId', 'AnalyticsController', 'content');

// Template Routes
$router->get('/templates', 'TemplateController', 'index');
$router->get('/templates/create', 'TemplateController', 'create');
$router->post('/templates/create', 'TemplateController', 'create');
$router->post('/templates/delete/:id', 'TemplateController', 'delete');
$router->get('/templates/get/:id', 'TemplateController', 'get');

// REST API Routes
$router->apiPost('/text/generate', 'APIController', 'textGenerate');
$router->apiPost('/image/generate', 'APIController', 'imageGenerate');
$router->apiPost('/video/generate', 'APIController', 'videoGenerate');
$router->apiPost('/content/create', 'APIController', 'contentCreate');
$router->apiPost('/schedule/create', 'APIController', 'scheduleCreate');
$router->apiGet('/content', 'APIController', 'contentList');

return $router;
