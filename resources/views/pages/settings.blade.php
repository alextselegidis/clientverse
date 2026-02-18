{{--
/* ----------------------------------------------------------------------------
 * Clientverse - Simple Bookmark Manager
 *
 * @package     Clientverse
 * @author      A.Tselegidis <alextselegidis@gmail.com>
 * @copyright   Copyright (c) Alex Tselegidis
 * @license     https://opensource.org/licenses/GPL-3.0 - GPLv3
 * @link        https://github.com/alextselegidis/clientverse
 * ---------------------------------------------------------------------------- */
--}}

@extends('layouts.main-layout')

@section('pageTitle')
    {{ __('localization') }}
@endsection

@section('breadcrumbs')
    @include('shared.breadcrumb', ['breadcrumbs' => [
        ['label' => __('setup')],
        ['label' => __('localization')]
    ]])
@endsection

@section('content')
    <div class="d-flex flex-column flex-lg-row gap-4">
        <!-- Sidebar -->
        <div class="flex-shrink-0" style="min-width: 200px;">
            @include('shared.setup-sidebar')
        </div>

        <!-- Main Content -->
        <div class="flex-grow-1">


            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-4">
                    <form action="{{ route('setup.localization.update') }}" method="POST" id="settings-form">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="default-locale" class="form-label text-dark small fw-medium">
                                {{ __('default_locale') }} <span class="text-danger">*</span>
                            </label>
                            @include('shared.locale-dropdown', [
                                'name' => 'default_locale',
                                'id' => 'default-locale',
                                'value' => $defaultLocale,
                                'required'=> true,
                            ])
                            @error('default_locale')
                            <span class="form-text text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="default-timezone" class="form-label text-dark small fw-medium">
                                {{ __('default_timezone') }} <span class="text-danger">*</span>
                            </label>

                            @include('shared.timezone-dropdown', [
                                'name' => 'default_timezone',
                                'id' => 'default-timezone',
                                'value' => $defaultTimezone,
                                'required'=> true,
                            ])

                            @error('default_timezone')
                                <span class="form-text text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="default-currency" class="form-label text-dark small fw-medium">
                                {{ __('default_currency') }} <span class="text-danger">*</span>
                            </label>
                            <select name="default_currency" id="default-currency" class="form-select" required>
                                <optgroup label="Common Currencies">
                                    <option value="USD" {{ $defaultCurrency === 'USD' ? 'selected' : '' }}>USD - US Dollar</option>
                                    <option value="EUR" {{ $defaultCurrency === 'EUR' ? 'selected' : '' }}>EUR - Euro</option>
                                    <option value="GBP" {{ $defaultCurrency === 'GBP' ? 'selected' : '' }}>GBP - British Pound</option>
                                    <option value="JPY" {{ $defaultCurrency === 'JPY' ? 'selected' : '' }}>JPY - Japanese Yen</option>
                                    <option value="CAD" {{ $defaultCurrency === 'CAD' ? 'selected' : '' }}>CAD - Canadian Dollar</option>
                                    <option value="AUD" {{ $defaultCurrency === 'AUD' ? 'selected' : '' }}>AUD - Australian Dollar</option>
                                    <option value="CHF" {{ $defaultCurrency === 'CHF' ? 'selected' : '' }}>CHF - Swiss Franc</option>
                                    <option value="CNY" {{ $defaultCurrency === 'CNY' ? 'selected' : '' }}>CNY - Chinese Yuan</option>
                                    <option value="INR" {{ $defaultCurrency === 'INR' ? 'selected' : '' }}>INR - Indian Rupee</option>
                                    <option value="BRL" {{ $defaultCurrency === 'BRL' ? 'selected' : '' }}>BRL - Brazilian Real</option>
                                </optgroup>
                                <optgroup label="All Currencies">
                                    <option value="AED" {{ $defaultCurrency === 'AED' ? 'selected' : '' }}>AED - UAE Dirham</option>
                                    <option value="AFN" {{ $defaultCurrency === 'AFN' ? 'selected' : '' }}>AFN - Afghan Afghani</option>
                                    <option value="ALL" {{ $defaultCurrency === 'ALL' ? 'selected' : '' }}>ALL - Albanian Lek</option>
                                    <option value="AMD" {{ $defaultCurrency === 'AMD' ? 'selected' : '' }}>AMD - Armenian Dram</option>
                                    <option value="ANG" {{ $defaultCurrency === 'ANG' ? 'selected' : '' }}>ANG - Netherlands Antillean Guilder</option>
                                    <option value="AOA" {{ $defaultCurrency === 'AOA' ? 'selected' : '' }}>AOA - Angolan Kwanza</option>
                                    <option value="ARS" {{ $defaultCurrency === 'ARS' ? 'selected' : '' }}>ARS - Argentine Peso</option>
                                    <option value="AWG" {{ $defaultCurrency === 'AWG' ? 'selected' : '' }}>AWG - Aruban Florin</option>
                                    <option value="AZN" {{ $defaultCurrency === 'AZN' ? 'selected' : '' }}>AZN - Azerbaijani Manat</option>
                                    <option value="BAM" {{ $defaultCurrency === 'BAM' ? 'selected' : '' }}>BAM - Bosnia-Herzegovina Convertible Mark</option>
                                    <option value="BBD" {{ $defaultCurrency === 'BBD' ? 'selected' : '' }}>BBD - Barbadian Dollar</option>
                                    <option value="BDT" {{ $defaultCurrency === 'BDT' ? 'selected' : '' }}>BDT - Bangladeshi Taka</option>
                                    <option value="BGN" {{ $defaultCurrency === 'BGN' ? 'selected' : '' }}>BGN - Bulgarian Lev</option>
                                    <option value="BHD" {{ $defaultCurrency === 'BHD' ? 'selected' : '' }}>BHD - Bahraini Dinar</option>
                                    <option value="BIF" {{ $defaultCurrency === 'BIF' ? 'selected' : '' }}>BIF - Burundian Franc</option>
                                    <option value="BMD" {{ $defaultCurrency === 'BMD' ? 'selected' : '' }}>BMD - Bermudan Dollar</option>
                                    <option value="BND" {{ $defaultCurrency === 'BND' ? 'selected' : '' }}>BND - Brunei Dollar</option>
                                    <option value="BOB" {{ $defaultCurrency === 'BOB' ? 'selected' : '' }}>BOB - Bolivian Boliviano</option>
                                    <option value="BSD" {{ $defaultCurrency === 'BSD' ? 'selected' : '' }}>BSD - Bahamian Dollar</option>
                                    <option value="BTN" {{ $defaultCurrency === 'BTN' ? 'selected' : '' }}>BTN - Bhutanese Ngultrum</option>
                                    <option value="BWP" {{ $defaultCurrency === 'BWP' ? 'selected' : '' }}>BWP - Botswanan Pula</option>
                                    <option value="BYN" {{ $defaultCurrency === 'BYN' ? 'selected' : '' }}>BYN - Belarusian Ruble</option>
                                    <option value="BZD" {{ $defaultCurrency === 'BZD' ? 'selected' : '' }}>BZD - Belize Dollar</option>
                                    <option value="CDF" {{ $defaultCurrency === 'CDF' ? 'selected' : '' }}>CDF - Congolese Franc</option>
                                    <option value="CLP" {{ $defaultCurrency === 'CLP' ? 'selected' : '' }}>CLP - Chilean Peso</option>
                                    <option value="COP" {{ $defaultCurrency === 'COP' ? 'selected' : '' }}>COP - Colombian Peso</option>
                                    <option value="CRC" {{ $defaultCurrency === 'CRC' ? 'selected' : '' }}>CRC - Costa Rican Colón</option>
                                    <option value="CUP" {{ $defaultCurrency === 'CUP' ? 'selected' : '' }}>CUP - Cuban Peso</option>
                                    <option value="CVE" {{ $defaultCurrency === 'CVE' ? 'selected' : '' }}>CVE - Cape Verdean Escudo</option>
                                    <option value="CZK" {{ $defaultCurrency === 'CZK' ? 'selected' : '' }}>CZK - Czech Koruna</option>
                                    <option value="DJF" {{ $defaultCurrency === 'DJF' ? 'selected' : '' }}>DJF - Djiboutian Franc</option>
                                    <option value="DKK" {{ $defaultCurrency === 'DKK' ? 'selected' : '' }}>DKK - Danish Krone</option>
                                    <option value="DOP" {{ $defaultCurrency === 'DOP' ? 'selected' : '' }}>DOP - Dominican Peso</option>
                                    <option value="DZD" {{ $defaultCurrency === 'DZD' ? 'selected' : '' }}>DZD - Algerian Dinar</option>
                                    <option value="EGP" {{ $defaultCurrency === 'EGP' ? 'selected' : '' }}>EGP - Egyptian Pound</option>
                                    <option value="ERN" {{ $defaultCurrency === 'ERN' ? 'selected' : '' }}>ERN - Eritrean Nakfa</option>
                                    <option value="ETB" {{ $defaultCurrency === 'ETB' ? 'selected' : '' }}>ETB - Ethiopian Birr</option>
                                    <option value="FJD" {{ $defaultCurrency === 'FJD' ? 'selected' : '' }}>FJD - Fijian Dollar</option>
                                    <option value="FKP" {{ $defaultCurrency === 'FKP' ? 'selected' : '' }}>FKP - Falkland Islands Pound</option>
                                    <option value="GEL" {{ $defaultCurrency === 'GEL' ? 'selected' : '' }}>GEL - Georgian Lari</option>
                                    <option value="GHS" {{ $defaultCurrency === 'GHS' ? 'selected' : '' }}>GHS - Ghanaian Cedi</option>
                                    <option value="GIP" {{ $defaultCurrency === 'GIP' ? 'selected' : '' }}>GIP - Gibraltar Pound</option>
                                    <option value="GMD" {{ $defaultCurrency === 'GMD' ? 'selected' : '' }}>GMD - Gambian Dalasi</option>
                                    <option value="GNF" {{ $defaultCurrency === 'GNF' ? 'selected' : '' }}>GNF - Guinean Franc</option>
                                    <option value="GTQ" {{ $defaultCurrency === 'GTQ' ? 'selected' : '' }}>GTQ - Guatemalan Quetzal</option>
                                    <option value="GYD" {{ $defaultCurrency === 'GYD' ? 'selected' : '' }}>GYD - Guyanaese Dollar</option>
                                    <option value="HKD" {{ $defaultCurrency === 'HKD' ? 'selected' : '' }}>HKD - Hong Kong Dollar</option>
                                    <option value="HNL" {{ $defaultCurrency === 'HNL' ? 'selected' : '' }}>HNL - Honduran Lempira</option>
                                    <option value="HRK" {{ $defaultCurrency === 'HRK' ? 'selected' : '' }}>HRK - Croatian Kuna</option>
                                    <option value="HTG" {{ $defaultCurrency === 'HTG' ? 'selected' : '' }}>HTG - Haitian Gourde</option>
                                    <option value="HUF" {{ $defaultCurrency === 'HUF' ? 'selected' : '' }}>HUF - Hungarian Forint</option>
                                    <option value="IDR" {{ $defaultCurrency === 'IDR' ? 'selected' : '' }}>IDR - Indonesian Rupiah</option>
                                    <option value="ILS" {{ $defaultCurrency === 'ILS' ? 'selected' : '' }}>ILS - Israeli New Shekel</option>
                                    <option value="IQD" {{ $defaultCurrency === 'IQD' ? 'selected' : '' }}>IQD - Iraqi Dinar</option>
                                    <option value="IRR" {{ $defaultCurrency === 'IRR' ? 'selected' : '' }}>IRR - Iranian Rial</option>
                                    <option value="ISK" {{ $defaultCurrency === 'ISK' ? 'selected' : '' }}>ISK - Icelandic Króna</option>
                                    <option value="JMD" {{ $defaultCurrency === 'JMD' ? 'selected' : '' }}>JMD - Jamaican Dollar</option>
                                    <option value="JOD" {{ $defaultCurrency === 'JOD' ? 'selected' : '' }}>JOD - Jordanian Dinar</option>
                                    <option value="KES" {{ $defaultCurrency === 'KES' ? 'selected' : '' }}>KES - Kenyan Shilling</option>
                                    <option value="KGS" {{ $defaultCurrency === 'KGS' ? 'selected' : '' }}>KGS - Kyrgystani Som</option>
                                    <option value="KHR" {{ $defaultCurrency === 'KHR' ? 'selected' : '' }}>KHR - Cambodian Riel</option>
                                    <option value="KMF" {{ $defaultCurrency === 'KMF' ? 'selected' : '' }}>KMF - Comorian Franc</option>
                                    <option value="KPW" {{ $defaultCurrency === 'KPW' ? 'selected' : '' }}>KPW - North Korean Won</option>
                                    <option value="KRW" {{ $defaultCurrency === 'KRW' ? 'selected' : '' }}>KRW - South Korean Won</option>
                                    <option value="KWD" {{ $defaultCurrency === 'KWD' ? 'selected' : '' }}>KWD - Kuwaiti Dinar</option>
                                    <option value="KYD" {{ $defaultCurrency === 'KYD' ? 'selected' : '' }}>KYD - Cayman Islands Dollar</option>
                                    <option value="KZT" {{ $defaultCurrency === 'KZT' ? 'selected' : '' }}>KZT - Kazakhstani Tenge</option>
                                    <option value="LAK" {{ $defaultCurrency === 'LAK' ? 'selected' : '' }}>LAK - Laotian Kip</option>
                                    <option value="LBP" {{ $defaultCurrency === 'LBP' ? 'selected' : '' }}>LBP - Lebanese Pound</option>
                                    <option value="LKR" {{ $defaultCurrency === 'LKR' ? 'selected' : '' }}>LKR - Sri Lankan Rupee</option>
                                    <option value="LRD" {{ $defaultCurrency === 'LRD' ? 'selected' : '' }}>LRD - Liberian Dollar</option>
                                    <option value="LSL" {{ $defaultCurrency === 'LSL' ? 'selected' : '' }}>LSL - Lesotho Loti</option>
                                    <option value="LYD" {{ $defaultCurrency === 'LYD' ? 'selected' : '' }}>LYD - Libyan Dinar</option>
                                    <option value="MAD" {{ $defaultCurrency === 'MAD' ? 'selected' : '' }}>MAD - Moroccan Dirham</option>
                                    <option value="MDL" {{ $defaultCurrency === 'MDL' ? 'selected' : '' }}>MDL - Moldovan Leu</option>
                                    <option value="MGA" {{ $defaultCurrency === 'MGA' ? 'selected' : '' }}>MGA - Malagasy Ariary</option>
                                    <option value="MKD" {{ $defaultCurrency === 'MKD' ? 'selected' : '' }}>MKD - Macedonian Denar</option>
                                    <option value="MMK" {{ $defaultCurrency === 'MMK' ? 'selected' : '' }}>MMK - Myanmar Kyat</option>
                                    <option value="MNT" {{ $defaultCurrency === 'MNT' ? 'selected' : '' }}>MNT - Mongolian Tugrik</option>
                                    <option value="MOP" {{ $defaultCurrency === 'MOP' ? 'selected' : '' }}>MOP - Macanese Pataca</option>
                                    <option value="MRU" {{ $defaultCurrency === 'MRU' ? 'selected' : '' }}>MRU - Mauritanian Ouguiya</option>
                                    <option value="MUR" {{ $defaultCurrency === 'MUR' ? 'selected' : '' }}>MUR - Mauritian Rupee</option>
                                    <option value="MVR" {{ $defaultCurrency === 'MVR' ? 'selected' : '' }}>MVR - Maldivian Rufiyaa</option>
                                    <option value="MWK" {{ $defaultCurrency === 'MWK' ? 'selected' : '' }}>MWK - Malawian Kwacha</option>
                                    <option value="MXN" {{ $defaultCurrency === 'MXN' ? 'selected' : '' }}>MXN - Mexican Peso</option>
                                    <option value="MYR" {{ $defaultCurrency === 'MYR' ? 'selected' : '' }}>MYR - Malaysian Ringgit</option>
                                    <option value="MZN" {{ $defaultCurrency === 'MZN' ? 'selected' : '' }}>MZN - Mozambican Metical</option>
                                    <option value="NAD" {{ $defaultCurrency === 'NAD' ? 'selected' : '' }}>NAD - Namibian Dollar</option>
                                    <option value="NGN" {{ $defaultCurrency === 'NGN' ? 'selected' : '' }}>NGN - Nigerian Naira</option>
                                    <option value="NIO" {{ $defaultCurrency === 'NIO' ? 'selected' : '' }}>NIO - Nicaraguan Córdoba</option>
                                    <option value="NOK" {{ $defaultCurrency === 'NOK' ? 'selected' : '' }}>NOK - Norwegian Krone</option>
                                    <option value="NPR" {{ $defaultCurrency === 'NPR' ? 'selected' : '' }}>NPR - Nepalese Rupee</option>
                                    <option value="NZD" {{ $defaultCurrency === 'NZD' ? 'selected' : '' }}>NZD - New Zealand Dollar</option>
                                    <option value="OMR" {{ $defaultCurrency === 'OMR' ? 'selected' : '' }}>OMR - Omani Rial</option>
                                    <option value="PAB" {{ $defaultCurrency === 'PAB' ? 'selected' : '' }}>PAB - Panamanian Balboa</option>
                                    <option value="PEN" {{ $defaultCurrency === 'PEN' ? 'selected' : '' }}>PEN - Peruvian Sol</option>
                                    <option value="PGK" {{ $defaultCurrency === 'PGK' ? 'selected' : '' }}>PGK - Papua New Guinean Kina</option>
                                    <option value="PHP" {{ $defaultCurrency === 'PHP' ? 'selected' : '' }}>PHP - Philippine Peso</option>
                                    <option value="PKR" {{ $defaultCurrency === 'PKR' ? 'selected' : '' }}>PKR - Pakistani Rupee</option>
                                    <option value="PLN" {{ $defaultCurrency === 'PLN' ? 'selected' : '' }}>PLN - Polish Zloty</option>
                                    <option value="PYG" {{ $defaultCurrency === 'PYG' ? 'selected' : '' }}>PYG - Paraguayan Guarani</option>
                                    <option value="QAR" {{ $defaultCurrency === 'QAR' ? 'selected' : '' }}>QAR - Qatari Rial</option>
                                    <option value="RON" {{ $defaultCurrency === 'RON' ? 'selected' : '' }}>RON - Romanian Leu</option>
                                    <option value="RSD" {{ $defaultCurrency === 'RSD' ? 'selected' : '' }}>RSD - Serbian Dinar</option>
                                    <option value="RUB" {{ $defaultCurrency === 'RUB' ? 'selected' : '' }}>RUB - Russian Ruble</option>
                                    <option value="RWF" {{ $defaultCurrency === 'RWF' ? 'selected' : '' }}>RWF - Rwandan Franc</option>
                                    <option value="SAR" {{ $defaultCurrency === 'SAR' ? 'selected' : '' }}>SAR - Saudi Riyal</option>
                                    <option value="SBD" {{ $defaultCurrency === 'SBD' ? 'selected' : '' }}>SBD - Solomon Islands Dollar</option>
                                    <option value="SCR" {{ $defaultCurrency === 'SCR' ? 'selected' : '' }}>SCR - Seychellois Rupee</option>
                                    <option value="SDG" {{ $defaultCurrency === 'SDG' ? 'selected' : '' }}>SDG - Sudanese Pound</option>
                                    <option value="SEK" {{ $defaultCurrency === 'SEK' ? 'selected' : '' }}>SEK - Swedish Krona</option>
                                    <option value="SGD" {{ $defaultCurrency === 'SGD' ? 'selected' : '' }}>SGD - Singapore Dollar</option>
                                    <option value="SHP" {{ $defaultCurrency === 'SHP' ? 'selected' : '' }}>SHP - Saint Helena Pound</option>
                                    <option value="SLL" {{ $defaultCurrency === 'SLL' ? 'selected' : '' }}>SLL - Sierra Leonean Leone</option>
                                    <option value="SOS" {{ $defaultCurrency === 'SOS' ? 'selected' : '' }}>SOS - Somali Shilling</option>
                                    <option value="SRD" {{ $defaultCurrency === 'SRD' ? 'selected' : '' }}>SRD - Surinamese Dollar</option>
                                    <option value="SSP" {{ $defaultCurrency === 'SSP' ? 'selected' : '' }}>SSP - South Sudanese Pound</option>
                                    <option value="STN" {{ $defaultCurrency === 'STN' ? 'selected' : '' }}>STN - São Tomé and Príncipe Dobra</option>
                                    <option value="SYP" {{ $defaultCurrency === 'SYP' ? 'selected' : '' }}>SYP - Syrian Pound</option>
                                    <option value="SZL" {{ $defaultCurrency === 'SZL' ? 'selected' : '' }}>SZL - Swazi Lilangeni</option>
                                    <option value="THB" {{ $defaultCurrency === 'THB' ? 'selected' : '' }}>THB - Thai Baht</option>
                                    <option value="TJS" {{ $defaultCurrency === 'TJS' ? 'selected' : '' }}>TJS - Tajikistani Somoni</option>
                                    <option value="TMT" {{ $defaultCurrency === 'TMT' ? 'selected' : '' }}>TMT - Turkmenistani Manat</option>
                                    <option value="TND" {{ $defaultCurrency === 'TND' ? 'selected' : '' }}>TND - Tunisian Dinar</option>
                                    <option value="TOP" {{ $defaultCurrency === 'TOP' ? 'selected' : '' }}>TOP - Tongan Paʻanga</option>
                                    <option value="TRY" {{ $defaultCurrency === 'TRY' ? 'selected' : '' }}>TRY - Turkish Lira</option>
                                    <option value="TTD" {{ $defaultCurrency === 'TTD' ? 'selected' : '' }}>TTD - Trinidad and Tobago Dollar</option>
                                    <option value="TWD" {{ $defaultCurrency === 'TWD' ? 'selected' : '' }}>TWD - New Taiwan Dollar</option>
                                    <option value="TZS" {{ $defaultCurrency === 'TZS' ? 'selected' : '' }}>TZS - Tanzanian Shilling</option>
                                    <option value="UAH" {{ $defaultCurrency === 'UAH' ? 'selected' : '' }}>UAH - Ukrainian Hryvnia</option>
                                    <option value="UGX" {{ $defaultCurrency === 'UGX' ? 'selected' : '' }}>UGX - Ugandan Shilling</option>
                                    <option value="UYU" {{ $defaultCurrency === 'UYU' ? 'selected' : '' }}>UYU - Uruguayan Peso</option>
                                    <option value="UZS" {{ $defaultCurrency === 'UZS' ? 'selected' : '' }}>UZS - Uzbekistan Som</option>
                                    <option value="VES" {{ $defaultCurrency === 'VES' ? 'selected' : '' }}>VES - Venezuelan Bolívar</option>
                                    <option value="VND" {{ $defaultCurrency === 'VND' ? 'selected' : '' }}>VND - Vietnamese Dong</option>
                                    <option value="VUV" {{ $defaultCurrency === 'VUV' ? 'selected' : '' }}>VUV - Vanuatu Vatu</option>
                                    <option value="WST" {{ $defaultCurrency === 'WST' ? 'selected' : '' }}>WST - Samoan Tala</option>
                                    <option value="XAF" {{ $defaultCurrency === 'XAF' ? 'selected' : '' }}>XAF - CFA Franc BEAC</option>
                                    <option value="XCD" {{ $defaultCurrency === 'XCD' ? 'selected' : '' }}>XCD - East Caribbean Dollar</option>
                                    <option value="XOF" {{ $defaultCurrency === 'XOF' ? 'selected' : '' }}>XOF - CFA Franc BCEAO</option>
                                    <option value="XPF" {{ $defaultCurrency === 'XPF' ? 'selected' : '' }}>XPF - CFP Franc</option>
                                    <option value="YER" {{ $defaultCurrency === 'YER' ? 'selected' : '' }}>YER - Yemeni Rial</option>
                                    <option value="ZAR" {{ $defaultCurrency === 'ZAR' ? 'selected' : '' }}>ZAR - South African Rand</option>
                                    <option value="ZMW" {{ $defaultCurrency === 'ZMW' ? 'selected' : '' }}>ZMW - Zambian Kwacha</option>
                                    <option value="ZWL" {{ $defaultCurrency === 'ZWL' ? 'selected' : '' }}>ZWL - Zimbabwean Dollar</option>
                                </optgroup>
                            </select>
                            @error('default_currency')
                                <span class="form-text text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </form>
                </div>

                <!-- Card Footer with Save Button -->
                <div class="card-footer bg-body-secondary border-top text-end py-3 px-4">
                    <button type="submit" form="settings-form" class="btn btn-dark">
                        {{ __('save') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection
