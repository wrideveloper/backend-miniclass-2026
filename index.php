<?php

require_once __DIR__ . '/koneksi.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/router.php';

// Load handler absensi
require_once __DIR__ . '/handlers/absensi.php';


get('/api/absensi',         'getAllAbsensi');
get('/api/absensi/{id}',    'getAbsensiById');
post('/api/absensi',        'createAbsensi');
put('/api/absensi/{id}',    'updateAbsensi');
delete('/api/absensi/{id}', 'deleteAbsensi');

// Jalankan Router
run();
