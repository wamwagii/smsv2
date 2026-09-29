<?php

namespace App\Support;

class Pricing
{
    /**
     * All plans, with the minimum-term floor applied and derived fields
     * (yearly saving, formatted prices) merged in.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function plans(): array
    {
        $plans   = config('pricing.plans', []);
        $floor   = config('pricing.minimum_per_term');
        $terms   = max(1, (int) config('pricing.terms_per_year', 3));
        $code    = config('pricing.currency.symbol', 'KES');

        return array_map(function (array $plan) use ($floor, $terms, $code) {
            $termPrice = (int) $plan['price_term'];
            if ($floor !== null) {
                $termPrice = max($termPrice, (int) $floor);
            }

            $yearPrice = (int) $plan['price_year'];
            $fullYear  = $termPrice * $terms;

            $saving = $fullYear > 0
                ? (int) round((1 - ($yearPrice / $fullYear)) * 100)
                : 0;

            return $plan + [
                'price_term'            => $termPrice,
                'price_year'            => $yearPrice,
                'price_term_formatted'  => $code . ' ' . number_format($termPrice),
                'price_year_formatted'  => $code . ' ' . number_format($yearPrice),
                'saving_percent'        => max(0, $saving),
                'is_popular'            => (bool) ($plan['popular'] ?? false),
            ];
        }, $plans);
    }

    /**
     * Convenience: get one plan by key, or null.
     */
    public static function plan(string $key): ?array
    {
        foreach (static::plans() as $plan) {
            if ($plan['key'] === $key) {
                return $plan;
            }
        }

        return null;
    }
}