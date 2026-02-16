<?php

/* ----------------------------------------------------------------------------
 * Clientverse - Self-Hosted CRM
 *
 * @package     Clientverse
 * @author      A.Tselegidis <alextselegidis@gmail.com>
 * @copyright   Copyright (c) Alex Tselegidis
 * @license     https://opensource.org/licenses/GPL-3.0 - GPLv3
 * @link        https://clientverse.org
 * ---------------------------------------------------------------------------- */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class File extends Model
{
    protected $fillable = [
        'fileable_id',
        'fileable_type',
        'uploaded_by',
        'original_name',
        'stored_name',
        'mime_type',
        'size',
    ];

    protected $casts = [
        'size' => 'integer',
    ];

    /**
     * Allowed document MIME types (no scripts).
     */
    public static array $allowedMimeTypes = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'application/rtf',
        'text/plain',
        'text/csv',
        'application/zip',
        'application/x-zip-compressed',
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
    ];

    /**
     * Allowed file extensions.
     */
    public static array $allowedExtensions = [
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx',
        'rtf', 'txt', 'csv', 'zip', 'jpg', 'jpeg', 'png', 'gif', 'webp',
    ];

    /**
     * Get the parent fileable model.
     */
    public function fileable()
    {
        return $this->morphTo();
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get human-readable file size.
     */
    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get the file path in storage.
     */
    public function getPathAttribute(): string
    {
        return 'files/' . $this->stored_name;
    }

    /**
     * Check if file exists in storage.
     */
    public function existsInStorage(): bool
    {
        return Storage::disk('local')->exists($this->path);
    }

    /**
     * Delete the file from storage.
     */
    public function deleteFromStorage(): bool
    {
        if ($this->existsInStorage()) {
            return Storage::disk('local')->delete($this->path);
        }
        return true;
    }

    /**
     * Get PHP upload limits.
     */
    public static function getUploadLimits(): array
    {
        $uploadMaxFilesize = self::parseSize(ini_get('upload_max_filesize'));
        $postMaxSize = self::parseSize(ini_get('post_max_size'));
        $maxFileUploads = (int) ini_get('max_file_uploads');

        return [
            'max_file_size' => min($uploadMaxFilesize, $postMaxSize),
            'max_file_size_formatted' => self::formatBytes(min($uploadMaxFilesize, $postMaxSize)),
            'max_files' => $maxFileUploads,
            'allowed_extensions' => self::$allowedExtensions,
        ];
    }

    /**
     * Parse PHP size string to bytes.
     */
    private static function parseSize(string $size): int
    {
        $unit = strtolower(substr($size, -1));
        $value = (int) $size;

        return match ($unit) {
            'g' => $value * 1024 * 1024 * 1024,
            'm' => $value * 1024 * 1024,
            'k' => $value * 1024,
            default => $value,
        };
    }

    /**
     * Format bytes to human readable.
     */
    private static function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
