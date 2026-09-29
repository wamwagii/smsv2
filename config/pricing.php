<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Currency & locale
    |--------------------------------------------------------------------------
    */
    'currency' => [
        'code'   => env('PRICING_CURRENCY_CODE', 'KES'),
        'symbol' => env('PRICING_CURRENCY_SYMBOL', 'KES'),
        'locale' => env('PRICING_LOCALE', 'en_KE'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Billing terms
    |--------------------------------------------------------------------------
    |
    | How many terms make up a full academic year. Used to compute the
    | "pay yearly and save X%" badge, so you only need to change prices,
    | never the percentage.
    |
    */
    'terms_per_year' => 3,

    /*
    |--------------------------------------------------------------------------
    | Minimum termly amount (per school)
    |--------------------------------------------------------------------------
    |
    | Pricing is per school, not per student or per class. This is the floor
    | for any plan's termly price — if a plan is configured below it, the
    | rendered price is raised to this value. Set to null to disable the floor.
    |
    */
    'minimum_per_term' => env('PRICING_MINIMUM_PER_TERM', 15000),

    /*
    |--------------------------------------------------------------------------
    | Plans
    |--------------------------------------------------------------------------
    |
    | Pricing is per school. Each plan has:
    |   key           — stable identifier, used as the DOM id and CTA ref
    |   name          — display name
    |   tagline       — one-line description under the name
    |   price_term    — KES per term, for the whole school (integer, unformatted)
    |   price_year    — KES per year, for the whole school (integer, unformatted)
    |   popular       — whether to mark it "Most popular"
    |   badge         — optional short ribbon text (null to hide)
    |   features      — array of strings, rendered as a checklist
    |   cta           — button label
    |   cta_url       — where the button goes (usually /admin/register)
    |
    */
    'plans' => [

        [
            'key'        => 'basic',
            'name'       => 'Basic',
            'tagline'    => 'Everything a small school needs to get started.',
            'price_term' => 15000,
            'price_year' => 40000,
            'popular'    => false,
            'badge'      => null,
            'features'   => [
                'Up to 200 students',
                'Student records & profiles',
                'Fee tracking and receipts',
                'Attendance register',
                'Exam results & report cards',
                'Email support',
            ],
            'cta'        => 'Choose Basic',
            'cta_url'    => '/admin/register',
        ],

        [
            'key'        => 'premium',
            'name'       => 'Premium',
            'tagline'    => 'For growing schools that need more room.',
            'price_term' => 25000,
            'price_year' => 65000,
            'popular'    => true,
            'badge'      => 'Most popular',
            'features'   => [
                'Unlimited students',
                'Everything in Basic',
                'SMS to parents (1,000 free / term)',
                'Timetable & scheduling',
                'Advanced reports & analytics',
                'Priority phone & WhatsApp support',
                'Free onboarding and training',
            ],
            'cta'        => 'Choose Premium',
            'cta_url'    => '/admin/register',
        ],

    ],

];