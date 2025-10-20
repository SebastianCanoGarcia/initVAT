<?php
namespace App\Controllers;

use App\Services\VatValidator;
use App\Models\Database;

class VatController
{
    protected $validator;
    protected $db;

    public function __construct()
    {
        $this->validator = new VatValidator();
        $this->db = Database::getInstance()->getPdo();
    }

    public function index()
    {
        $stmt = $this->db->query("SELECT * FROM vat_numbers ORDER BY created_at DESC");
        $all = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $valid = array_filter($all, function($r) {
          return $r['status'] === 'valid';
        });

        $corrected = array_filter($all, function($r) {
          return $r['status'] === 'corrected';
        });

        $invalid = array_filter($all, function($r) {
          return $r['status'] === 'invalid';
        });

        return ['valid' => $valid, 'corrected' => $corrected, 'invalid' => $invalid];
    }

    public function handleUpload()
    {
        if (!isset($_FILES['csv']) || $_FILES['csv']['error'] !== UPLOAD_ERR_OK) {
            header('Location: /?error=upload');
            exit;
        }

        $tmp = $_FILES['csv']['tmp_name'];
        $filename = basename($_FILES['csv']['name']);
        $dest = __DIR__ . '/../uploads/' . $filename;
        move_uploaded_file($tmp, $dest);

        // Insert upload record
        $stmt = $this->db->prepare("INSERT INTO uploads (filename, status, total_rows, processed_rows) VALUES (?, 'processing', 0, 0)");
        $stmt->execute([$filename]);
        $uploadId = $this->db->lastInsertId();

        // Count rows for progress tracking
        if (($h = fopen($dest, 'r')) !== false) {
            $count = 0;
            while (fgetcsv($h) !== false) $count++;
            fclose($h);
            $stmt = $this->db->prepare("UPDATE uploads SET total_rows=? WHERE id=?");
            $stmt->execute([$count - 1, $uploadId]);
        }

        header("Location: ?uploadId=$uploadId");
        exit;
    }

    public function progress($uploadId)
    {
        $stmt = $this->db->prepare("SELECT total_rows, processed_rows, status FROM uploads WHERE id=?");
        $stmt->execute([$uploadId]);
        $upload = $stmt->fetch(\PDO::FETCH_ASSOC);

        header('Content-Type: application/json');
        echo json_encode($upload);
        exit;
    }

    public function handleTest()
    {
        $vat = $_POST['vat'] ?? '';
        $vat = $this->sanitizeInput($vat);

        $res = $this->validator->validate($vat);
        $this->saveResult($vat, $res);

        header('Location: ?tested=1');
        exit;
    }

    public function processUploadBatch($uploadId, $batchSize = 100)
    {
        $stmt = $this->db->prepare("SELECT filename, processed_rows, total_rows FROM uploads WHERE id=?");
        $stmt->execute([$uploadId]);
        $upload = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$upload) return;

        $file = __DIR__ . '/../uploads/' . $upload['filename'];
        $start = (int) $upload['processed_rows'];

        if (($h = fopen($file, 'r')) !== false) {
            $header = fgetcsv($h); // skip header
            for ($i = 0; $i < $start; $i++) fgetcsv($h); // skip processed rows

            $count = 0;
            while (($row = fgetcsv($h)) !== false && $count < $batchSize) {
                if (count($row) < 2) continue;
                $vat = trim($row[1]);
                if ($vat === '') continue;

                $vat = $this->sanitizeInput($vat);
                $res = $this->validator->validate($vat);
                $this->saveResult($vat, $res);
                $count++;
            }
            fclose($h);

            $stmt = $this->db->prepare("UPDATE uploads SET processed_rows = processed_rows + ? WHERE id=?");
            $stmt->execute([$count, $uploadId]);

            if ($start + $count >= $upload['total_rows']) {
                $stmt = $this->db->prepare("UPDATE uploads SET status='done' WHERE id=?");
                $stmt->execute([$uploadId]);
            }
        }
    }

    protected function saveResult($original, $res)
    {
        // Sanitize before saving to prevent XSS in display
        $cleanOriginal = $this->sanitizeInput($original);
        $cleanFinal = $this->sanitizeInput($res['vat'] ?? '');
        $status = $res['status'] ?? 'invalid';
        $cleanCorrection = $this->sanitizeInput($res['correction'] ?? ($res['error'] ?? ''));

        $stmt = $this->db->prepare(
            'INSERT INTO vat_numbers (original_value, final_value, status, correction_or_error)
             VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$cleanOriginal, $cleanFinal, $status, $cleanCorrection]);
    }

    /**
     * Sanitize input to prevent HTML/script injection (XSS)
     */
    protected function sanitizeInput($value)
    {
        if (is_null($value)) return null;
        // Remove potential HTML tags but keep normal characters
        $value = strip_tags($value);
        // Remove any invisible or control characters
        $value = preg_replace('/[\x00-\x1F\x7F]/u', '', $value);
        // Trim spaces
        return trim($value);
    }
}
