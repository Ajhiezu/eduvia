<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use InvalidArgumentException;

class SecureUploadService
{
    /**
     * Dangerous file extensions strictly forbidden under all circumstances.
     */
    protected const DISALLOWED_EXTENSIONS = [
        'php', 'php3', 'php4', 'php5', 'phtml', 'phar',
        'exe', 'dll', 'so', 'bin', 'sh', 'bash', 'bat', 'cmd', 'ps1',
        'js', 'jsp', 'asp', 'aspx', 'py', 'pl', 'cgi', 'htaccess',
        'vbs', 'scr', 'msi', 'jar', 'com',
    ];

    /**
     * Allowed MIME types and their validated extensions.
     */
    protected const ALLOWED_MIME_TYPES = [
        // Images
        'image/jpeg' => ['jpg', 'jpeg'],
        'image/png' => ['png'],
        'image/webp' => ['webp'],
        'image/svg+xml' => ['svg'],
        // Documents
        'application/pdf' => ['pdf'],
        'application/msword' => ['doc'],
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => ['docx'],
        'application/vnd.ms-excel' => ['xls'],
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => ['xlsx'],
        'application/vnd.ms-powerpoint' => ['ppt'],
        'application/vnd.openxmlformats-officedocument.presentationml.presentation' => ['pptx'],
        'text/plain' => ['txt', 'csv'],
        'text/csv' => ['csv'],
        // Archives
        'application/zip' => ['zip'],
        'application/x-zip-compressed' => ['zip'],
    ];

    /**
     * Upload and securely store a file.
     *
     * @param  string  $category  e.g., 'assignments', 'materials', 'documents', 'proofs'
     * @param  string  $disk  'local' for private files, 'public' for public images
     * @return array{path: string, original_name: string, mime_type: string, size: int}
     */
    public function storeSecurely(
        UploadedFile $file,
        string $category = 'general',
        string $disk = 'local',
        ?int $schoolId = null,
        int $maxKilobytes = 10240
    ): array {
        $this->validateFileSecurity($file, $maxKilobytes);

        $clientExtension = strtolower($file->getClientOriginalExtension());
        $resolvedSchoolId = $schoolId ?? app(TenantManager::class)->getSchoolId() ?? 'common';

        // Cryptographically random filename
        $fileName = Str::random(40).'.'.$clientExtension;
        $directory = "schools/{$resolvedSchoolId}/{$category}";

        $storedPath = $file->storeAs($directory, $fileName, $disk);

        return [
            'path' => $storedPath,
            'file_name' => $fileName,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'disk' => $disk,
        ];
    }

    /**
     * Alias for storeSecurely.
     *
     * @return array{path: string, original_name: string, mime_type: string, size: int}
     */
    public function validateAndStore(
        UploadedFile $file,
        string $category = 'general',
        string $disk = 'local',
        ?int $schoolId = null,
        int $maxKilobytes = 10240
    ): array {
        return $this->storeSecurely($file, $category, $disk, $schoolId, $maxKilobytes);
    }

    /**
     * Perform deep security inspection on the uploaded file.
     */
    public function validateFileSecurity(UploadedFile $file, int $maxKilobytes = 10240): void
    {
        // 1. File existence and upload error check
        if (! $file->isValid()) {
            throw new InvalidArgumentException('File upload gagal atau korup: '.$file->getErrorMessage());
        }

        // 2. Size validation
        if ($file->getSize() > ($maxKilobytes * 1024)) {
            throw new InvalidArgumentException("Ukuran file melebihi batas maksimum ({$maxKilobytes} KB).");
        }

        // 3. Extension blacklist check
        $extension = strtolower($file->getClientOriginalExtension());
        if (in_array($extension, self::DISALLOWED_EXTENSIONS, true)) {
            throw new InvalidArgumentException("Ekstensi file .{$extension} dilarang demi keamanan sistem.");
        }

        // 4. Genuine MIME type inspection via fileinfo magic bytes
        $detectedMime = $file->getMimeType();
        if (! array_key_exists($detectedMime, self::ALLOWED_MIME_TYPES)) {
            throw new InvalidArgumentException("Format konten file ({$detectedMime}) tidak diizinkan.");
        }

        // 5. Cross-check client extension against magic bytes
        $allowedExtensions = self::ALLOWED_MIME_TYPES[$detectedMime];
        if (! in_array($extension, $allowedExtensions, true)) {
            throw new InvalidArgumentException("Ekstensi file (.{$extension}) tidak sesuai dengan konten aslinya ({$detectedMime}).");
        }
    }

    /**
     * Generate a temporary signed URL for a private stored document.
     */
    public function generateTemporaryDownloadUrl(string $route, array $parameters, int $minutes = 30): string
    {
        return URL::temporarySignedRoute(
            $route,
            now()->addMinutes($minutes),
            $parameters
        );
    }
}
