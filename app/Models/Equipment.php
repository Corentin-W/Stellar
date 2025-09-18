<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Equipment extends Model
{
    protected $fillable = [
        'name',
        'description',
        'type',
        'status',
        'location',
        'specifications',
        'images',
        'videos',
        'price_per_hour_credits',
        'is_featured',
        'is_active',
        'sort_order'
    ];

    protected $casts = [
        'specifications' => 'array',
        'images' => 'array',
        'videos' => 'array',
        'price_per_hour_credits' => 'integer',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer'
    ];

    // Constantes pour les énumérations
    const TYPES = [
        'telescope' => 'Télescope',
        'mount' => 'Monture',
        'camera' => 'Caméra',
        'accessory' => 'Accessoire',
        'complete_setup' => 'Setup Complet'
    ];

    const STATUSES = [
        'available' => 'Disponible',
        'unavailable' => 'Non disponible',
        'maintenance' => 'En maintenance',
        'reserved' => 'Réservé'
    ];

    // Relations
    public function reservations(): HasMany
    {
        return $this->hasMany(EquipmentReservation::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    // Accessors
    protected function statusLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => self::STATUSES[$this->status] ?? $this->status,
        );
    }

    protected function typeLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => self::TYPES[$this->type] ?? $this->type,
        );
    }

    protected function isAvailable(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->status === 'available' && $this->is_active,
        );
    }

    protected function mainImage(): Attribute
    {
        return Attribute::make(
            get: fn () => !empty($this->images) ? $this->images[0] : null,
        );
    }

    protected function specificationsFormatted(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->specifications) return [];

                $formatted = [];
                foreach ($this->specifications as $key => $value) {
                    if (is_array($value)) {
                        if (isset($value['brand']) && isset($value['model'])) {
                            $formatted[$key] = $value['brand'] . ' ' . $value['model'];
                            if (isset($value['type'])) {
                                $formatted[$key] .= ' (' . $value['type'] . ')';
                            }
                        } else {
                            $formatted[$key] = implode(', ', $value);
                        }
                    } else {
                        $formatted[$key] = $value;
                    }
                }
                return $formatted;
            }
        );
    }

    // Méthodes
    public function addImage(string $imagePath): void
    {
        $images = $this->images ?? [];
        $images[] = $imagePath;
        $this->update(['images' => $images]);
    }

    public function removeImage(string $imagePath): void
    {
        $images = $this->images ?? [];
        $images = array_filter($images, fn($img) => $img !== $imagePath);
        $this->update(['images' => array_values($images)]);
    }

    public function addVideo(string $videoPath): void
    {
        $videos = $this->videos ?? [];
        $videos[] = $videoPath;
        $this->update(['videos' => $videos]);
    }

    public function removeVideo(string $videoPath): void
    {
        $videos = $this->videos ?? [];
        $videos = array_filter($videos, fn($vid) => $vid !== $videoPath);
        $this->update(['videos' => array_values($videos)]);
    }

    public function setSpecification(string $key, $value): void
    {
        $specs = $this->specifications ?? [];
        $specs[$key] = $value;
        $this->update(['specifications' => $specs]);
    }

    public function getSpecification(string $key, $default = null)
    {
        return $this->specifications[$key] ?? $default;
    }

    public function canBeReserved(): bool
    {
        return $this->is_available;
    }

    public function getStatusBadgeClass(): string
    {
        return match($this->status) {
            'available' => 'bg-green-500',
            'unavailable' => 'bg-red-500',
            'maintenance' => 'bg-orange-500',
            'reserved' => 'bg-blue-500',
            default => 'bg-gray-500'
        };
    }
}
