<?php
declare(strict_types=1);

final class PredictionService
{
    public static function predict(array $in): array
    {
        $crop = strtolower(trim((string)($in['crop_type'] ?? 'other')));

        $farmSize = (float)$in['farm_size_acres'];
        $rain = (float)$in['rainfall_mm'];
        $temp = (float)$in['avg_temp_c'];
        $fert = (float)$in['fertilizer_kg'];

        $soil = strtolower(trim((string)($in['soil_type'] ?? 'other')));
        $seed = strtolower(trim((string)($in['seed_type'] ?? 'other')));
        $irrig = strtolower(trim((string)($in['irrigation'] ?? 'no')));

        // 1) Baseline tons-per-acre (TPA) by crop
        $baseTPA = match ($crop) {
            'maize'   => 1.80,
            'beans'   => 0.90,
            'rice'    => 2.20,
            'cassava' => 3.00,
            'matooke' => 4.00,
            default   => 1.50,
        };

        // 2) Rainfall factor (simple piecewise)
        $rainFactor = 1.0;
        if ($rain < 400)        $rainFactor = 0.65;
        elseif ($rain < 600)    $rainFactor = 0.80;
        elseif ($rain <= 900)   $rainFactor = 1.05;   // good range
        elseif ($rain <= 1200)  $rainFactor = 1.00;   // ok but not extra
        else                    $rainFactor = 0.95;   // too much can reduce

        // 3) Temperature factor (best ~20–28C)
        $tempFactor = 1.0;
        if ($temp < 18)         $tempFactor = 0.85;
        elseif ($temp <= 28)    $tempFactor = 1.05;
        elseif ($temp <= 33)    $tempFactor = 0.95;
        else                    $tempFactor = 0.80;

        // 4) Soil factor
        $soilFactor = match ($soil) {
            'loam'  => 1.10,
            'clay'  => 1.02,
            'silt'  => 1.00,
            'sandy' => 0.90,
            default => 1.00,
        };

        // 5) Seed factor
        $seedFactor = match ($seed) {
            'hybrid'   => 1.12,
            'improved' => 1.06,
            'local'    => 1.00,
            default    => 1.00,
        };

        // 6) Fertilizer factor (capped so it doesn't blow up)
        // Assumption: benefit rises until ~200kg then flattens
        $fertCap = min(max($fert, 0.0), 200.0);
        $fertFactor = 1.0 + (0.15 * ($fertCap / 200.0)); // max +15%

        // 7) Irrigation bonus
        $irrigFactor = ($irrig === 'yes') ? 1.07 : 1.00;

        // Calculate TPA
        $tpa = $baseTPA * $rainFactor * $tempFactor * $soilFactor * $seedFactor * $fertFactor * $irrigFactor;

        // Clamp to sensible minimum/maximum
        $tpa = max(0.10, min($tpa, 15.0));

        // Total tons = tpa * acres
        $totalTons = $tpa * max(0.01, $farmSize);

        // Risk level
        $risk = 'medium';
        if ($rain < 500 || $temp < 18 || $temp > 33 || $tpa < 1.0) $risk = 'high';
        if ($rain >= 600 && $rain <= 900 && $temp >= 20 && $temp <= 28 && $tpa >= 1.5) $risk = 'low';

        return [
            'predicted_yield_tpa'  => round($tpa, 2),
            'predicted_yield_tons' => round($totalTons, 2),
            'risk_level'           => $risk,
        ];
    }
}
