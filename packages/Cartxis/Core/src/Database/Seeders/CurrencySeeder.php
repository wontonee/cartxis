<?php

declare(strict_types=1);

namespace Cartxis\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CurrencySeeder extends Seeder
{
    /**
     * Seed currencies derived from the countries table.
     * The countries table is the single source of truth for currency_code and currency_symbol.
     * Additional metadata (name, decimal_places, symbol_position) uses sensible defaults
     * and can be refined later via the admin settings panel.
     */
    public function run(): void
    {
        // Currency names for common ISO 4217 codes
        $names = [
            'AED' => 'UAE Dirham',             'AFN' => 'Afghan Afghani',
            'ALL' => 'Albanian Lek',           'AMD' => 'Armenian Dram',
            'AOA' => 'Angolan Kwanza',         'ARS' => 'Argentine Peso',
            'AUD' => 'Australian Dollar',      'AZN' => 'Azerbaijani Manat',
            'BAM' => 'Bosnia-Herzegovina Mark','BBD' => 'Barbadian Dollar',
            'BDT' => 'Bangladeshi Taka',       'BGN' => 'Bulgarian Lev',
            'BHD' => 'Bahraini Dinar',         'BMD' => 'Bermudian Dollar',
            'BND' => 'Brunei Dollar',          'BOB' => 'Bolivian Boliviano',
            'BRL' => 'Brazilian Real',         'BSD' => 'Bahamian Dollar',
            'BTN' => 'Bhutanese Ngultrum',     'BWP' => 'Botswanan Pula',
            'BYR' => 'Belarusian Ruble',       'BZD' => 'Belize Dollar',
            'CAD' => 'Canadian Dollar',        'CDF' => 'Congolese Franc',
            'CHF' => 'Swiss Franc',            'CLP' => 'Chilean Peso',
            'CNY' => 'Chinese Yuan',           'COP' => 'Colombian Peso',
            'CRC' => 'Costa Rican Colón',      'CUP' => 'Cuban Peso',
            'CVE' => 'Cape Verdean Escudo',    'CZK' => 'Czech Koruna',
            'DJF' => 'Djiboutian Franc',       'DKK' => 'Danish Krone',
            'DOP' => 'Dominican Peso',         'DZD' => 'Algerian Dinar',
            'EGP' => 'Egyptian Pound',         'ERN' => 'Eritrean Nakfa',
            'ETB' => 'Ethiopian Birr',         'EUR' => 'Euro',
            'FJD' => 'Fijian Dollar',          'GBP' => 'British Pound',
            'GEL' => 'Georgian Lari',          'GHS' => 'Ghanaian Cedi',
            'GMD' => 'Gambian Dalasi',         'GNF' => 'Guinean Franc',
            'GTQ' => 'Guatemalan Quetzal',     'GYD' => 'Guyanese Dollar',
            'HKD' => 'Hong Kong Dollar',       'HNL' => 'Honduran Lempira',
            'HRK' => 'Croatian Kuna',          'HTG' => 'Haitian Gourde',
            'HUF' => 'Hungarian Forint',       'IDR' => 'Indonesian Rupiah',
            'ILS' => 'Israeli New Shekel',     'INR' => 'Indian Rupee',
            'IQD' => 'Iraqi Dinar',            'IRR' => 'Iranian Rial',
            'ISK' => 'Icelandic Króna',        'JMD' => 'Jamaican Dollar',
            'JOD' => 'Jordanian Dinar',        'JPY' => 'Japanese Yen',
            'KES' => 'Kenyan Shilling',        'KGS' => 'Kyrgystani Som',
            'KHR' => 'Cambodian Riel',         'KPW' => 'North Korean Won',
            'KRW' => 'South Korean Won',       'KWD' => 'Kuwaiti Dinar',
            'KYD' => 'Cayman Islands Dollar',  'KZT' => 'Kazakhstani Tenge',
            'LAK' => 'Laotian Kip',            'LBP' => 'Lebanese Pound',
            'LKR' => 'Sri Lankan Rupee',       'LRD' => 'Liberian Dollar',
            'LSL' => 'Lesotho Loti',           'LYD' => 'Libyan Dinar',
            'MAD' => 'Moroccan Dirham',        'MDL' => 'Moldovan Leu',
            'MKD' => 'Macedonian Denar',       'MMK' => 'Myanmar Kyat',
            'MNT' => 'Mongolian Tögrög',       'MOP' => 'Macanese Pataca',
            'MRO' => 'Mauritanian Ouguiya',    'MUR' => 'Mauritian Rupee',
            'MVR' => 'Maldivian Rufiyaa',      'MWK' => 'Malawian Kwacha',
            'MXN' => 'Mexican Peso',           'MYR' => 'Malaysian Ringgit',
            'MZN' => 'Mozambican Metical',     'NAD' => 'Namibian Dollar',
            'NGN' => 'Nigerian Naira',         'NIO' => 'Nicaraguan Córdoba',
            'NOK' => 'Norwegian Krone',        'NPR' => 'Nepalese Rupee',
            'NZD' => 'New Zealand Dollar',     'OMR' => 'Omani Rial',
            'PAB' => 'Panamanian Balboa',      'PEN' => 'Peruvian Sol',
            'PGK' => 'Papua New Guinean Kina', 'PHP' => 'Philippine Peso',
            'PKR' => 'Pakistani Rupee',        'PLN' => 'Polish Złoty',
            'PYG' => 'Paraguayan Guaraní',     'QAR' => 'Qatari Riyal',
            'RON' => 'Romanian Leu',           'RSD' => 'Serbian Dinar',
            'RUB' => 'Russian Ruble',          'RWF' => 'Rwandan Franc',
            'SAR' => 'Saudi Riyal',            'SBD' => 'Solomon Islands Dollar',
            'SCR' => 'Seychellois Rupee',      'SDG' => 'Sudanese Pound',
            'SEK' => 'Swedish Krona',          'SGD' => 'Singapore Dollar',
            'SLL' => 'Sierra Leonean Leone',   'SOS' => 'Somali Shilling',
            'SRD' => 'Surinamese Dollar',      'STD' => 'São Tomé Dobra',
            'SVC' => 'Salvadoran Colón',       'SYP' => 'Syrian Pound',
            'SZL' => 'Swazi Lilangeni',        'THB' => 'Thai Baht',
            'TJS' => 'Tajikistani Somoni',     'TMT' => 'Turkmenistani Manat',
            'TND' => 'Tunisian Dinar',         'TOP' => 'Tongan Paʻanga',
            'TRY' => 'Turkish Lira',           'TTD' => 'Trinidad & Tobago Dollar',
            'TWD' => 'New Taiwan Dollar',      'TZS' => 'Tanzanian Shilling',
            'UAH' => 'Ukrainian Hryvnia',      'UGX' => 'Ugandan Shilling',
            'USD' => 'US Dollar',              'UYU' => 'Uruguayan Peso',
            'UZS' => 'Uzbekistani Som',        'VEF' => 'Venezuelan Bolívar',
            'VND' => 'Vietnamese Dong',        'VUV' => 'Vanuatu Vatu',
            'WST' => 'Samoan Tala',            'XAF' => 'Central African CFA Franc',
            'XOF' => 'West African CFA Franc', 'YER' => 'Yemeni Rial',
            'ZAR' => 'South African Rand',     'ZMW' => 'Zambian Kwacha',
            'ZWL' => 'Zimbabwean Dollar',
        ];

        // Zero-decimal currencies (no fractional units)
        $zeroDecimal = ['BIF','CLP','DJF','GNF','IDR','JPY','KMF','KRW','MGA','PYG','RWF','TZS','UGX','VND','VUV','XAF','XOF','XPF'];

        // Currencies where symbol goes after the amount
        $symbolAfter = ['PLN','CZK','HUF','RON','SEK','DKK','NOK','ISK','HRK'];

        $now = now();

        // Derive all currencies from the countries table
        $rows = DB::table('countries')
            ->whereNotNull('currency_code')
            ->where('currency_code', '!=', '')
            ->selectRaw('currency_code, MIN(currency_symbol) as currency_symbol')
            ->groupBy('currency_code')
            ->orderBy('currency_code')
            ->get();

        $currencies = [];
        $sort = 1;

        foreach ($rows as $row) {
            $code = strtoupper(trim($row->currency_code));
            $currencies[] = [
                'code'            => $code,
                'name'            => $names[$code] ?? $code,
                'symbol'          => $row->currency_symbol ?? $code,
                'symbol_position' => in_array($code, $symbolAfter) ? 'after' : 'before',
                'decimal_places'  => in_array($code, $zeroDecimal) ? 0 : 2,
                'exchange_rate'   => 1.0,
                'is_default'      => $code === 'USD',
                'is_active'       => true,
                'sort_order'      => $sort++,
                'created_at'      => $now,
                'updated_at'      => $now,
            ];
        }

        DB::table('currencies')->upsert(
            $currencies,
            ['code'],
            ['name', 'symbol', 'symbol_position', 'decimal_places', 'is_default', 'is_active', 'sort_order', 'updated_at']
        );
    }
}
