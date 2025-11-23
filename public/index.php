<?php
// FILE: /public/index.php

// Start session
session_start();

// Load configuration
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Model.php';
require_once __DIR__ . '/../app/core/View.php';
require_once __DIR__ . '/../app/core/Controller.php';
require_once __DIR__ . '/../app/core/Router.php';

// Load helpers
require_once __DIR__ . '/../app/helpers/ValidationHelper.php';
require_once __DIR__ . '/../app/helpers/SecurityHelper.php';
require_once __DIR__ . '/../app/helpers/AITextHelper.php';
require_once __DIR__ . '/../app/helpers/AIImageHelper.php';
require_once __DIR__ . '/../app/helpers/AIVideoHelper.php';
require_once __DIR__ . '/../app/helpers/SocialPosterHelper.php';
require_once __DIR__ . '/../app/helpers/ActivityHelper.php';
require_once __DIR__ . '/../app/helpers/QuotaHelper.php';

// Load config
require_once __DIR__ . '/../config/config.php';

// Load and dispatch routes
$router = require __DIR__ . '/../config/routes.php';
$router->dispatch();
