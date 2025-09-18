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

    // NOUVELLE MÉTHODE - Helper pour les spécifications principales (max 4)
    public function getMainSpecs()
    {
        if (!$this->specifications || !is_array($this->specifications)) {
            return [];
        }

        $formatted = [];
        $count = 0;

        foreach ($this->specifications as $key => $value) {
            if ($count >= 4) break;

            // Traitement spécial pour les objets JSON encodés en string
            if (is_string($value) && (str_starts_with($value, '{') || str_starts_with($value, '['))) {
                $decoded = json_decode($value, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    if (is_array($decoded)) {
                        if (isset($decoded['brand']) && isset($decoded['model'])) {
                            $formatted[ucfirst(str_replace('_', ' ', $key))] = $decoded['brand'] . ' ' . $decoded['model'];
                            if (isset($decoded['type'])) {
                                $formatted[ucfirst(str_replace('_', ' ', $key))] .= ' (' . $decoded['type'] . ')';
                            }
                        } else {
                            $formatted[ucfirst(str_replace('_', ' ', $key))] = implode(', ', $decoded);
                        }
                    } else {
                        $formatted[ucfirst(str_replace('_', ' ', $key))] = $decoded;
                    }
                } else {
                    $formatted[ucfirst(str_replace('_', ' ', $key))] = $value;
                }
            } elseif (is_array($value)) {
                if (isset($value['brand']) && isset($value['model'])) {
                    $formatted[ucfirst(str_replace('_', ' ', $key))] = $value['brand'] . ' ' . $value['model'];
                    if (isset($value['type'])) {
                        $formatted[ucfirst(str_replace('_', ' ', $key))] .= ' (' . $value['type'] . ')';
                    }
                } else {
                    $formatted[ucfirst(str_replace('_', ' ', $key))] = implode(', ', $value);
                }
            } else {
                $formatted[ucfirst(str_replace('_', ' ', $key))] = $value;
            }

            $count++;
        }

        return $formatted;
    }

    // Helper pour l'icône selon le type
    public function getTypeIcon()
    {
        $icons = [
            'telescope' => '<path d="m21.64 3.64-1.28-1.28a1.21 1.21 0 0 0-1.72 0L2.36 18.64a1.21 1.21 0 0 0 0 1.72l1.28 1.28a1.2 1.2 0 0 0 1.72 0L21.64 5.36a1.2 1.2 0 0 0 0-1.72Z"/><path d="m14 7 3 3"/><path d="M5 6v4"/><path d="M19 14v4"/><path d="M10 2v2"/><path d="M7 8H3"/><path d="M21 16h-4"/><path d="M11 3H9"/>',
            'mount' => '<path d="M12 2v6"/><path d="M12 18v4"/><path d="m4.93 4.93 4.24 4.24"/><path d="m14.83 14.83 4.24 4.24"/><path d="M2 12h6"/><path d="M16 12h6"/><path d="m4.93 19.07 4.24-4.24"/><path d="m14.83 9.17 4.24-4.24"/>',
            'camera' => '<path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/><circle cx="12" cy="13" r="3"/>',
            'complete_setup' => '<rect width="16" height="20" x="4" y="2" rx="2" ry="2"/><path d="M9 22v-4h6v4"/><path d="M8 6h.01"/><path d="M16 6h.01"/><path d="M12 6h.01"/><path d="M12 10h.01"/><path d="M12 14h.01"/><path d="M16 10h.01"/><path d="M16 14h.01"/><path d="M8 10h.01"/><path d="M8 14h.01"/>',
            'accessory' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1 1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>',
        ];

        return $icons[$this->type] ?? $icons['accessory'];
    }

    // Helper pour le libellé du type
    public function getTypeLabel()
    {
        return self::TYPES[$this->type] ?? 'Équipement';
    }

    // Helper pour le statut avec badge
    public function getStatusBadge()
    {
        $badges = [
            'available' => '<div class="status-badge status-available">Disponible</div>',
            'unavailable' => '<div class="status-badge status-unavailable">Indisponible</div>',
            'maintenance' => '<div class="status-badge status-maintenance">Maintenance</div>',
            'reserved' => '<div class="status-badge status-reserved">Réservé</div>',
        ];

        return $badges[$this->status] ?? '';
    }

    // Helper pour l'image principale
    public function getMainImage()
    {
        if ($this->images && is_array($this->images) && count($this->images) > 0) {
            return asset('storage/' . $this->images[0]);
        }

        // Image par défaut selon le type
        $defaultImages = [
            'telescope' => 'images/equipment-defaults/telescope.jpg',
            'mount' => 'images/equipment-defaults/mount.jpg',
            'camera' => 'images/equipment-defaults/camera.jpg',
            'complete_setup' => 'images/equipment-defaults/setup.jpg',
            'accessory' => 'images/equipment-defaults/accessory.jpg'
        ];

        return asset($defaultImages[$this->type] ?? $defaultImages['accessory']);
    }

    // Méthodes existantes
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
