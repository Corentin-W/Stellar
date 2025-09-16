<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class SupportAttachment extends Model
{
    protected $fillable = [
        'ticket_id',
        'message_id',
        'user_id',
        'filename',
        'original_filename',
        'file_path',
        'file_size',
        'mime_type',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'ticket_id' => 'integer',
        'message_id' => 'integer',
        'user_id' => 'integer',
    ];

    // Relations
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class, 'ticket_id');
    }

    public function message(): BelongsTo
    {
        return $this->belongsTo(SupportMessage::class, 'message_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Accessors
    public function getFileSizeHumanAttribute()
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getFileExtensionAttribute()
    {
        return strtolower(pathinfo($this->original_filename, PATHINFO_EXTENSION));
    }

    public function getIsImageAttribute()
    {
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg', 'webp'];
        return in_array($this->file_extension, $imageExtensions);
    }

    public function getIsDocumentAttribute()
    {
        $documentExtensions = ['pdf', 'doc', 'docx', 'txt', 'rtf'];
        return in_array($this->file_extension, $documentExtensions);
    }

    public function getFileIconAttribute()
    {
        return match($this->file_extension) {
            'pdf' => '📄',
            'doc', 'docx' => '📝',
            'txt' => '📋',
            'jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg', 'webp' => '🖼️',
            'zip', 'rar', '7z' => '📦',
            'mp3', 'wav', 'ogg' => '🎵',
            'mp4', 'avi', 'mov' => '🎬',
            default => '📎'
        };
    }

    public function getDownloadUrlAttribute()
    {
        return route('admin.support.tickets.attachment.download', $this->id);
    }

    // Méthodes
    public function exists()
    {
        return Storage::exists($this->file_path);
    }

    public function deleteFile()
    {
        if ($this->exists()) {
            Storage::delete($this->file_path);
        }
    }

    // Events
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($attachment) {
            $attachment->deleteFile();
        });
    }
}
