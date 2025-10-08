<?php

namespace App\Services;

use App\Models\EquipmentBooking;
use Carbon\Carbon;

class TargetSuggestionService
{
    /**
     * Retourne une liste de cibles suggérées avec un score de visibilité.
     *
     * @param  EquipmentBooking  $booking
     * @param  int  $limit
     * @return array<int, array<string, mixed>>
     */
    public function suggest(EquipmentBooking $booking, int $limit = 5): array
    {
        $targets = config('observation_targets', []);
        $observatory = config('observatory');

        $start = $booking->start_datetime->copy()->setTimezone($observatory['timezone']);
        $end = $booking->end_datetime->copy()->setTimezone($observatory['timezone']);

        $latitude = $observatory['latitude'];
        $longitude = $observatory['longitude'];

        $scored = [];

        foreach ($targets as $target) {
            $visibility = $this->evaluateVisibility($target, $latitude, $longitude, $start, $end);

            $target['visibility'] = $visibility;
            $target['score'] = $visibility['score'];
            $target['recommended_duration'] = $this->calculateRecommendedDuration($target);

            $scored[] = $target;
        }

        usort($scored, static function (array $a, array $b): int {
            return $b['score'] <=> $a['score'];
        });

        return array_slice($scored, 0, $limit);
    }

    /**
     * @param  array<string, mixed>  $target
     * @return array<string, mixed>
     */
    private function evaluateVisibility(array $target, float $latitude, float $longitude, Carbon $start, Carbon $end): array
    {
        $sampleCount = 6;
        $intervalMinutes = max(5, (int) $start->diffInMinutes($end) / max(1, $sampleCount - 1));

        $samples = [];
        $current = $start->copy();

        $peakAltitude = -90.0;
        $aboveThirtyCount = 0;

        for ($i = 0; $i < $sampleCount; $i++) {
            $altitude = $this->computeAltitude(
                $target['ra_hours'],
                $target['dec_degrees'],
                $latitude,
                $longitude,
                $current
            );

            $samples[] = [
                'time' => $current->toIso8601String(),
                'altitude' => round($altitude, 2),
            ];

            if ($altitude > 30.0) {
                $aboveThirtyCount++;
            }

            $peakAltitude = max($peakAltitude, $altitude);

            $current->addMinutes($intervalMinutes);
        }

        $timeScore = $aboveThirtyCount / $sampleCount;
        $altitudeScore = max(0, ($peakAltitude - 30.0) / 40.0);
        $seasonScore = $this->seasonScore($target, $start);

        $score = max(0, min(1, ($timeScore * 0.5) + ($altitudeScore * 0.35) + ($seasonScore * 0.15)));

        return [
            'score' => round($score * 100, 1),
            'peak_altitude' => round($peakAltitude, 1),
            'samples' => $samples,
            'season_match' => $seasonScore,
        ];
    }

    /**
     * Calcul de l'altitude d'une cible pour un instant donné.
     */
    private function computeAltitude(float $raHours, float $decDegrees, float $latitude, float $longitude, Carbon $datetime): float
    {
        $latRad = deg2rad($latitude);
        $decRad = deg2rad($decDegrees);

        $lst = $this->localSiderealTime($datetime, $longitude);
        $ha = $lst - ($raHours * 15.0);
        $haRad = deg2rad($this->normalizeAngle($ha));

        $altitude = asin(
            sin($decRad) * sin($latRad) +
            cos($decRad) * cos($latRad) * cos($haRad)
        );

        return rad2deg($altitude);
    }

    private function localSiderealTime(Carbon $datetime, float $longitude): float
    {
        $julianDay = $datetime->copy()->setTimezone('UTC')->timestamp / 86400 + 2440587.5;
        $d = $julianDay - 2451545.0;
        $lst = 280.46061837 + 360.98564736629 * $d + $longitude;

        return $this->normalizeAngle($lst);
    }

    private function normalizeAngle(float $angle): float
    {
        $normalized = fmod($angle, 360.0);

        if ($normalized < 0) {
            $normalized += 360.0;
        }

        return $normalized;
    }

    /**
     * @param  array<string, mixed>  $target
     * @return array<string, mixed>
     */
    private function calculateRecommendedDuration(array $target): array
    {
        $total = 0;

        foreach ($target['recommended_filters'] ?? [] as $filter) {
            $total += ($filter['exposure'] ?? 0) * ($filter['frames'] ?? 0);
        }

        return [
            'seconds' => $total,
            'hours' => round($total / 3600, 2),
        ];
    }

    /**
     * Score saison basé sur la présence du mois courant dans les mois recommandés.
     */
    private function seasonScore(array $target, Carbon $start): float
    {
        if (empty($target['best_months'])) {
            return 0.5;
        }

        $currentMonth = $start->format('F');

        return in_array($currentMonth, $target['best_months'], true) ? 1.0 : 0.25;
    }
}
