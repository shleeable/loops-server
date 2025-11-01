<?php

namespace App\Services;

class IntlService
{
    public function getLanguages()
    {
        return [
            'af' => 'Afrikaans',
            'sq' => 'Albanian',
            'am' => 'Amharic',
            'ar' => 'Arabic',
            'hy' => 'Armenian',
            'az' => 'Azerbaijani',
            'eu' => 'Basque',
            'be' => 'Belarusian',
            'bn' => 'Bengali',
            'bs' => 'Bosnian',
            'bg' => 'Bulgarian',
            'my' => 'Burmese',
            'ca' => 'Catalan',
            'zh' => 'Chinese (Simplified)',
            'zh-TW' => 'Chinese (Traditional)',
            'hr' => 'Croatian',
            'cs' => 'Czech',
            'da' => 'Danish',
            'nl' => 'Dutch',
            'en' => 'English',
            'et' => 'Estonian',
            'fi' => 'Finnish',
            'fr' => 'French',
            'ka' => 'Georgian',
            'de' => 'German',
            'el' => 'Greek',
            'ha' => 'Hausa',
            'he' => 'Hebrew',
            'hi' => 'Hindi',
            'hu' => 'Hungarian',
            'is' => 'Icelandic',
            'id' => 'Indonesian',
            'ga' => 'Irish',
            'it' => 'Italian',
            'ja' => 'Japanese',
            'kn' => 'Kannada',
            'kk' => 'Kazakh',
            'km' => 'Khmer',
            'ko' => 'Korean',
            'lv' => 'Latvian',
            'lt' => 'Lithuanian',
            'ms' => 'Malay',
            'ml' => 'Malayalam',
            'mr' => 'Marathi',
            'mn' => 'Mongolian',
            'ne' => 'Nepali',
            'no' => 'Norwegian',
            'fa' => 'Persian',
            'pl' => 'Polish',
            'pt' => 'Portuguese',
            'pa' => 'Punjabi',
            'ro' => 'Romanian',
            'ru' => 'Russian',
            'sr' => 'Serbian',
            'sk' => 'Slovak',
            'sl' => 'Slovenian',
            'so' => 'Somali',
            'es' => 'Spanish',
            'sw' => 'Swahili',
            'sv' => 'Swedish',
            'ta' => 'Tamil',
            'te' => 'Telugu',
            'th' => 'Thai',
            'tr' => 'Turkish',
            'uk' => 'Ukrainian',
            'ur' => 'Urdu',
            'uz' => 'Uzbek',
            'vi' => 'Vietnamese',
            'cy' => 'Welsh',
        ];
    }

    public function keys()
    {
        return array_keys($this->getLanguages());
    }

    public function get()
    {
        return [
            [
                'code' => 'af',
                'name' => 'Afrikaans',
            ],
            [
                'code' => 'sq',
                'name' => 'Albanian',
            ],
            [
                'code' => 'am',
                'name' => 'Amharic',
            ],
            [
                'code' => 'ar',
                'name' => 'Arabic',
            ],
            [
                'code' => 'hy',
                'name' => 'Armenian',
            ],
            [
                'code' => 'az',
                'name' => 'Azerbaijani',
            ],
            [
                'code' => 'eu',
                'name' => 'Basque',
            ],
            [
                'code' => 'be',
                'name' => 'Belarusian',
            ],
            [
                'code' => 'bn',
                'name' => 'Bengali',
            ],
            [
                'code' => 'bs',
                'name' => 'Bosnian',
            ],
            [
                'code' => 'bg',
                'name' => 'Bulgarian',
            ],
            [
                'code' => 'my',
                'name' => 'Burmese',
            ],
            [
                'code' => 'ca',
                'name' => 'Catalan',
            ],
            [
                'code' => 'zh',
                'name' => 'Chinese (Simplified)',
            ],
            [
                'code' => 'zh-TW',
                'name' => 'Chinese (Traditional)',
            ],
            [
                'code' => 'hr',
                'name' => 'Croatian',
            ],
            [
                'code' => 'cs',
                'name' => 'Czech',
            ],
            [
                'code' => 'da',
                'name' => 'Danish',
            ],
            [
                'code' => 'nl',
                'name' => 'Dutch',
            ],
            [
                'code' => 'en',
                'name' => 'English',
            ],
            [
                'code' => 'et',
                'name' => 'Estonian',
            ],
            [
                'code' => 'fi',
                'name' => 'Finnish',
            ],
            [
                'code' => 'fr',
                'name' => 'French',
            ],
            [
                'code' => 'ka',
                'name' => 'Georgian',
            ],
            [
                'code' => 'de',
                'name' => 'German',
            ],
            [
                'code' => 'el',
                'name' => 'Greek',
            ],
            [
                'code' => 'ha',
                'name' => 'Hausa',
            ],
            [
                'code' => 'he',
                'name' => 'Hebrew',
            ],
            [
                'code' => 'hi',
                'name' => 'Hindi',
            ],
            [
                'code' => 'hu',
                'name' => 'Hungarian',
            ],
            [
                'code' => 'is',
                'name' => 'Icelandic',
            ],
            [
                'code' => 'id',
                'name' => 'Indonesian',
            ],
            [
                'code' => 'ga',
                'name' => 'Irish',
            ],
            [
                'code' => 'it',
                'name' => 'Italian',
            ],
            [
                'code' => 'ja',
                'name' => 'Japanese',
            ],
            [
                'code' => 'kn',
                'name' => 'Kannada',
            ],
            [
                'code' => 'kk',
                'name' => 'Kazakh',
            ],
            [
                'code' => 'km',
                'name' => 'Khmer',
            ],
            [
                'code' => 'ko',
                'name' => 'Korean',
            ],
            [
                'code' => 'lv',
                'name' => 'Latvian',
            ],
            [
                'code' => 'lt',
                'name' => 'Lithuanian',
            ],
            [
                'code' => 'ms',
                'name' => 'Malay',
            ],
            [
                'code' => 'ml',
                'name' => 'Malayalam',
            ],
            [
                'code' => 'mr',
                'name' => 'Marathi',
            ],
            [
                'code' => 'mn',
                'name' => 'Mongolian',
            ],
            [
                'code' => 'ne',
                'name' => 'Nepali',
            ],
            [
                'code' => 'no',
                'name' => 'Norwegian',
            ],
            [
                'code' => 'fa',
                'name' => 'Persian',
            ],
            [
                'code' => 'pl',
                'name' => 'Polish',
            ],
            [
                'code' => 'pt',
                'name' => 'Portuguese',
            ],
            [
                'code' => 'pa',
                'name' => 'Punjabi',
            ],
            [
                'code' => 'ro',
                'name' => 'Romanian',
            ],
            [
                'code' => 'ru',
                'name' => 'Russian',
            ],
            [
                'code' => 'sr',
                'name' => 'Serbian',
            ],
            [
                'code' => 'sk',
                'name' => 'Slovak',
            ],
            [
                'code' => 'sl',
                'name' => 'Slovenian',
            ],
            [
                'code' => 'so',
                'name' => 'Somali',
            ],
            [
                'code' => 'es',
                'name' => 'Spanish',
            ],
            [
                'code' => 'sw',
                'name' => 'Swahili',
            ],
            [
                'code' => 'sv',
                'name' => 'Swedish',
            ],
            [
                'code' => 'ta',
                'name' => 'Tamil',
            ],
            [
                'code' => 'te',
                'name' => 'Telugu',
            ],
            [
                'code' => 'th',
                'name' => 'Thai',
            ],
            [
                'code' => 'tr',
                'name' => 'Turkish',
            ],
            [
                'code' => 'uk',
                'name' => 'Ukrainian',
            ],
            [
                'code' => 'ur',
                'name' => 'Urdu',
            ],
            [
                'code' => 'uz',
                'name' => 'Uzbek',
            ],
            [
                'code' => 'vi',
                'name' => 'Vietnamese',
            ],
            [
                'code' => 'cy',
                'name' => 'Welsh',
            ],
        ];
    }
}
