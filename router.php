<?php
require_once __DIR__ . '/helpers.php';

$routes = [];

function route(string $method, string $path, $handler): void
{
    global $routes;
    $routes[] = [
        'method'  => strtoupper($method),
        'path'    => '/' . trim($path, '/'),
        'handler' => $handler,
    ];
}

function get(string $path, $handler): void    { route('GET', $path, $handler); }
function post(string $path, $handler): void   { route('POST', $path, $handler); }
function put(string $path, $handler): void    { route('PUT', $path, $handler); }
function delete(string $path, $handler): void { route('DELETE', $path, $handler); }

function run(): void
{
    global $routes;

    header('Content-Type: application/json; charset=UTF-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');

    $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    if ($requestMethod === 'OPTIONS') {
        http_response_code(204);
        exit;
    }

    $rawUri = $_SERVER['REQUEST_URI'] ?? '/';
    $requestUri = parse_url($rawUri, PHP_URL_PATH);

    // Deteksi otomatis jika aplikasi dijalankan di dalam subfolder (misal: Apache XAMPP/Laragon)
    $scriptName = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    if ($scriptName !== '/' && $scriptName !== '.' && $scriptName !== '') {
        if (str_starts_with($requestUri, $scriptName)) {
            $requestUri = substr($requestUri, strlen($scriptName));
        }
    }

    $requestUri = '/' . trim($requestUri, '/');

    $pathMatched = false;

    foreach ($routes as $r) {
        // Ganti placeholder seperti {id} menjadi regex pattern ([^/]+)
        $pattern = preg_replace('/\{[a-zA-Z0-9_]+\}/', '([^/]+)', $r['path']);
        $pattern = '#^' . $pattern . '$#';

        if (preg_match($pattern, $requestUri, $matches)) {
            $pathMatched = true;

            if ($r['method'] === $requestMethod) {
                array_shift($matches); // buang full match string

                $result = call_user_func_array($r['handler'], $matches);

                // Jika fungsi handler mengembalikan array, otomatis response 200
                if (is_array($result)) {
                    response(200, $result);
                }
                return;
            }
        }
    }

    if ($pathMatched) {
        response(405, [
            'status'  => 'error',
            'message' => "Method $requestMethod tidak diizinkan untuk endpoint ini."
        ]);
    }

    response(404, [
        'status'  => 'error',
        'message' => 'Endpoint tidak ditemukan.'
    ]);
}
