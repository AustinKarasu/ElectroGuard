<?php
// ElectroGuard Application Front Controller & API Gateway
// Project: ElectroGuard - Smart Electrical Worker Safety & Maintenance Coordination
// Team: GodsPlanKangra (Aayan Parmar, Manjinder Uldeen, Aayush Parmar)

// 1. Basic configuration and headers
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Autoloader for ElectroGuard namespace
spl_autoload_register(function ($class) {
    $prefix = 'ElectroGuard\\';
    $baseDir = __DIR__ . '/../app/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

// Start session
\ElectroGuard\Middleware\AuthMiddleware::startSession();

// 2. Parse URI and Method
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($requestUri, PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// If API request, set JSON header
if (str_starts_with($path, '/api/')) {
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

    if ($method === 'OPTIONS') {
        http_response_code(200);
        exit;
    }

    try {
        // === AUTH ROUTES ===
        if ($path === '/api/auth/login' && $method === 'POST') {
            \ElectroGuard\Controllers\AuthController::login();
            exit;
        }
        if ($path === '/api/auth/demo-login' && $method === 'POST') {
            \ElectroGuard\Controllers\AuthController::demoLogin();
            exit;
        }
        if ($path === '/api/auth/logout' && $method === 'POST') {
            \ElectroGuard\Controllers\AuthController::logout();
            exit;
        }
        if ($path === '/api/auth/me' && $method === 'GET') {
            \ElectroGuard\Controllers\AuthController::me();
            exit;
        }

        // === JOBS ROUTES ===
        if ($path === '/api/jobs' && $method === 'GET') {
            \ElectroGuard\Controllers\JobController::index();
            exit;
        }
        if ($path === '/api/jobs' && $method === 'POST') {
            \ElectroGuard\Controllers\JobController::create();
            exit;
        }
        if ($path === '/api/workers/me/jobs' && $method === 'GET') {
            \ElectroGuard\Controllers\JobController::workerJobs();
            exit;
        }
        if ($path === '/api/areas' && $method === 'GET') {
            \ElectroGuard\Controllers\JobController::getAreas();
            exit;
        }
        if ($path === '/api/workers' && $method === 'GET') {
            \ElectroGuard\Controllers\JobController::getWorkers();
            exit;
        }
        if (preg_match('#^/api/jobs/(\d+)$#', $path, $matches) && $method === 'GET') {
            \ElectroGuard\Controllers\JobController::get((int)$matches[1]);
            exit;
        }

        // === CLEARANCE & OTP ROUTES ===
        if ($path === '/api/clearance/verify-worker-id' && $method === 'POST') {
            \ElectroGuard\Controllers\ClearanceController::verifyWorkerId();
            exit;
        }
        if (preg_match('#^/api/jobs/(\d+)/otp/request$#', $path, $matches) && $method === 'POST') {
            \ElectroGuard\Controllers\ClearanceController::requestOtp((int)$matches[1]);
            exit;
        }
        if (preg_match('#^/api/jobs/(\d+)/otp/verify$#', $path, $matches) && $method === 'POST') {
            \ElectroGuard\Controllers\ClearanceController::verifyOtp((int)$matches[1]);
            exit;
        }
        if (preg_match('#^/api/jobs/(\d+)/clearances$#', $path, $matches) && $method === 'POST') {
            \ElectroGuard\Controllers\ClearanceController::submit((int)$matches[1]);
            exit;
        }
        if (preg_match('#^/api/jobs/(\d+)/clearances/reopen$#', $path, $matches) && $method === 'POST') {
            \ElectroGuard\Controllers\ClearanceController::reopen((int)$matches[1]);
            exit;
        }
        if (preg_match('#^/api/jobs/(\d+)/review$#', $path, $matches) && $method === 'POST') {
            \ElectroGuard\Controllers\ClearanceController::supervisorReview((int)$matches[1]);
            exit;
        }

        // === RESTORATION ROUTES ===
        if ($path === '/api/restoration-requests' && $method === 'GET') {
            \ElectroGuard\Controllers\RestorationController::index();
            exit;
        }
        if (preg_match('#^/api/jobs/(\d+)/restoration-requests/execute$#', $path, $matches) && $method === 'POST') {
            \ElectroGuard\Controllers\RestorationController::execute((int)$matches[1]);
            exit;
        }

        // === CONTROLLERS / IOT ROUTES ===
        if ($path === '/api/controllers' && $method === 'GET') {
            \ElectroGuard\Controllers\ControllerSimulationController::index();
            exit;
        }
        if (preg_match('#^/api/controllers/(\d+)/status$#', $path, $matches) && $method === 'POST') {
            \ElectroGuard\Controllers\ControllerSimulationController::updateStatus((int)$matches[1]);
            exit;
        }
        if (preg_match('#^/api/controllers/(\d+)/command$#', $path, $matches) && $method === 'POST') {
            \ElectroGuard\Controllers\ControllerSimulationController::simulateCommand((int)$matches[1]);
            exit;
        }

        // === ALERTS ROUTES ===
        if ($path === '/api/alerts' && $method === 'GET') {
            \ElectroGuard\Controllers\AlertController::index();
            exit;
        }
        if (preg_match('#^/api/alerts/(\d+)/read$#', $path, $matches) && $method === 'POST') {
            \ElectroGuard\Controllers\AlertController::markRead((int)$matches[1]);
            exit;
        }
        if ($path === '/api/alerts/mark-all-read' && $method === 'POST') {
            \ElectroGuard\Controllers\AlertController::markAllRead();
            exit;
        }

        // === AUDIT LOGS ROUTES ===
        if ($path === '/api/audit-logs' && $method === 'GET') {
            \ElectroGuard\Controllers\AuditController::index();
            exit;
        }

        // === REPORTS ROUTES ===
        if ($path === '/api/reports/summary' && $method === 'GET') {
            \ElectroGuard\Controllers\ReportController::summary();
            exit;
        }
        if ($path === '/api/reports/export-csv' && $method === 'GET') {
            \ElectroGuard\Controllers\ReportController::exportCsv();
            exit;
        }

        // Route not found
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => [
                'code' => 'NOT_FOUND',
                'message' => "API route '{$path}' [{$method}] not found."
            ]
        ]);
        exit;
    } catch (\Throwable $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => [
                'code' => 'INTERNAL_SERVER_ERROR',
                'message' => $e->getMessage()
            ]
        ]);
        exit;
    }
}

// 3. For any non-API web request, serve the main single page interface
require_once __DIR__ . '/../app/Views/app.php';
