<?php
declare(strict_types=1);

final class RecommendationService
{
    public static function generate(array $in, array $out): array
    {
        $tips = [];

        $rain = (float)$in['rainfall_mm'];
        $temp = (float)$in['avg_temp_c'];
        $fert = (float)$in['fertilizer_kg'];
        $soil = strtolower((string)$in['soil_type']);
        $seed = strtolower((string)$in['seed_type']);
        $irrig = strtolower((string)$in['irrigation']);

        $tpa = (float)$out['predicted_yield_tpa'];

        if ($rain < 500) {
            $tips[] = "Rainfall is low. Consider irrigation, mulching, and drought-tolerant varieties.";
        } elseif ($rain > 1200) {
            $tips[] = "Rainfall is very high. Improve drainage and monitor for crop diseases.";
        }

        if ($temp > 33) {
            $tips[] = "Temperature is high. Consider early planting, shading where possible, and mulching to conserve moisture.";
        } elseif ($temp < 18) {
            $tips[] = "Temperature is low. Choose suitable crop varieties and adjust planting dates if possible.";
        }

        if ($soil === 'sandy') {
            $tips[] = "Sandy soil loses water quickly. Add organic manure/compost to improve water retention.";
        } elseif ($soil === 'clay') {
            $tips[] = "Clay soil can hold water. Ensure proper drainage to avoid waterlogging.";
        }

        if ($fert < 25) {
            $tips[] = "Fertilizer amount is low. Consider applying recommended fertilizer/manure for the crop.";
        }

        if ($seed === 'local') {
            $tips[] = "Consider improved or hybrid seed for higher yield potential, if available and affordable.";
        }

        if ($irrig === 'no' && $rain < 600) {
            $tips[] = "If irrigation is possible, it can reduce risk during low-rain seasons.";
        }

        if ($tpa < 1.0) {
            $tips[] = "Predicted yield is low. Review inputs (rainfall, fertilizer, seed type) and apply recommended practices.";
        }

        if (empty($tips)) {
            $tips[] = "Inputs look good. Maintain best practices: timely weeding, pest control, and proper spacing.";
        }

        return $tips;
    }
}
