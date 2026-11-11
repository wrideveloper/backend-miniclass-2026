<?php

// ============================================================================
// HELPER VALIDASI (DRY: Dipakai oleh createAbsensi dan updateAbsensi)
// ============================================================================
function validateAbsensi(array $input): array
{
    if (empty($input['nama']) || empty($input['nim']) || empty($input['status']) || empty($input['tanggal'])) {
        response(422, [
            'status'  => 'error',
            'message' => 'Semua field (nama, nim, status, tanggal) wajib diisi!'
        ]);
    }

    return [
        'nama'    => $input['nama'],
        'nim'     => $input['nim'],
        'status'  => $input['status'],
        'tanggal' => $input['tanggal']
    ];
}

// ============================================================================
// HANDLER ENDPOINT CRUD
// ============================================================================

// [GET] /api/absensi - Mengambil seluruh data absensi
function getAllAbsensi(): void
{
    $stmt = db()->query(
        'SELECT id, nama, nim, status, tanggal
         FROM tb_absensi
         ORDER BY id DESC'
    );
    $data = $stmt->fetchAll();

    response(200, [
        'status'     => 'success',
        'total_data' => count($data),
        'data'       => $data,
    ]);
}

// [GET] /api/absensi/{id} - Mengambil satu data absensi berdasarkan ID
function getAbsensiById($id): void
{
    $id = filter_var($id, FILTER_VALIDATE_INT);
    if ($id === false || $id < 1) {
        response(400, ['status' => 'error', 'message' => 'ID harus bilangan bulat positif.']);
    }

    $stmt = db()->prepare(
        'SELECT id, nama, nim, status, tanggal
         FROM tb_absensi
         WHERE id = :id'
    );
    $stmt->execute(['id' => $id]);
    $data = $stmt->fetch();

    if (!$data) {
        response(404, ['status' => 'error', 'message' => 'Data tidak ditemukan.']);
    }

    response(200, ['status' => 'success', 'data' => $data]);
}

// [POST] /api/absensi - Menambahkan data absensi baru
function createAbsensi(): void
{
    $input = requestInput();
    $data  = validateAbsensi($input);

    $stmt = db()->prepare(
        'INSERT INTO tb_absensi (nama, nim, status, tanggal)
         VALUES (:nama, :nim, :status, :tanggal)'
    );
    $stmt->execute($data);

    response(201, [
        'status'  => 'success',
        'message' => 'Data absensi berhasil ditambahkan.',
        'data'    => array_merge(['id' => (int) db()->lastInsertId()], $data),
    ]);
}

// [PUT] /api/absensi/{id} - Memperbarui data absensi
function updateAbsensi($id): void
{
    $id = filter_var($id, FILTER_VALIDATE_INT);
    if ($id === false || $id < 1) {
        response(400, ['status' => 'error', 'message' => 'ID harus bilangan bulat positif.']);
    }

    // Pastikan data yang mau diupdate ada di database
    $check = db()->prepare('SELECT id FROM tb_absensi WHERE id = :id');
    $check->execute(['id' => $id]);
    if (!$check->fetch()) {
        response(404, ['status' => 'error', 'message' => 'Data tidak ditemukan.']);
    }

    $input = requestInput();
    $data  = validateAbsensi($input);

    $stmt = db()->prepare(
        'UPDATE tb_absensi
         SET nama = :nama, nim = :nim, status = :status, tanggal = :tanggal
         WHERE id = :id'
    );
    $data['id'] = $id;
    $stmt->execute($data);

    response(200, [
        'status'  => 'success',
        'message' => 'Data absensi berhasil diperbarui.',
        'data'    => $data,
    ]);
}

// [DELETE] /api/absensi/{id} - Menghapus data absensi
function deleteAbsensi($id): void
{
    $id = filter_var($id, FILTER_VALIDATE_INT);
    if ($id === false || $id < 1) {
        response(400, ['status' => 'error', 'message' => 'ID harus bilangan bulat positif.']);
    }

    $stmt = db()->prepare('DELETE FROM tb_absensi WHERE id = :id');
    $stmt->execute(['id' => $id]);

    if ($stmt->rowCount() === 0) {
        response(404, ['status' => 'error', 'message' => 'Data tidak ditemukan.']);
    }

    response(200, [
        'status'  => 'success',
        'message' => 'Data absensi berhasil dihapus.',
    ]);
}
