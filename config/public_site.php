<?php

/*
|--------------------------------------------------------------------------
| Public Site Presentation Fallbacks
|--------------------------------------------------------------------------
|
| The CMS tables intentionally do not store an image column for departments
| (see 2026_09_23_000020). These values are presentation-only defaults used
| when a record has no uploaded asset, so no schema change is required.
| Database values always take precedence once a real image column exists.
|
*/

return [

    /*
    | Cover image per department `code` and `short_name`.
    | Mapped to match the 4 jurusan in the JHIC template:
    |   Teknik Pemesinan  → jurusan-pemesinan.png
    |   Teknik Pembuatan Kain (Tekstil) → jurusan-kain.png
    |   Teknik Ototronik → jurusan-ototronik.png
    |   Rekayasa Perangkat Lunak (RPL) → jurusan-rpl.png
    */
    'department_covers' => [
        'default' => 'assets/images/hero/hero-jurusan.png',

        // By short_name (uppercase)
        'RPL' => 'assets/images/jurusan/jurusan-rpl.png',
        'MESIN' => 'assets/images/jurusan/jurusan-pemesinan.png',
        'TEKSTIL' => 'assets/images/jurusan/jurusan-kain.png',
        'OTOMOTIF' => 'assets/images/jurusan/jurusan-ototronik.png',
        'OTOTRONIK' => 'assets/images/jurusan/jurusan-ototronik.png',

        // By code (various formats)
        'TKR' => 'assets/images/jurusan/jurusan-ototronik.png',
        'TPK' => 'assets/images/jurusan/jurusan-kain.png',
        'TPM' => 'assets/images/jurusan/jurusan-pemesinan.png',
        'DKV' => 'assets/images/jurusan/jurusan-kain.png',
        'Mekatronika' => 'assets/images/jurusan/jurusan-pemesinan.png',
    ],

    /*
    | Jurusan image map used by the floating-art card pattern (jurusan-item).
    | Keys match `short_name` or `code` in uppercase — first match wins.
    */
    'department_art' => [
        'default' => 'assets/images/hero/hero-jurusan.png',
        'RPL' => 'assets/images/jurusan/jurusan-rpl.png',
        'MESIN' => 'assets/images/jurusan/jurusan-pemesinan.png',
        'TEKSTIL' => 'assets/images/jurusan/jurusan-kain.png',
        'OTOMOTIF' => 'assets/images/jurusan/jurusan-ototronik.png',
        'OTOTRONIK' => 'assets/images/jurusan/jurusan-ototronik.png',
        'TKR' => 'assets/images/jurusan/jurusan-ototronik.png',
        'TPK' => 'assets/images/jurusan/jurusan-kain.png',
        'TPM' => 'assets/images/jurusan/jurusan-pemesinan.png',
        'DKV' => 'assets/images/jurusan/jurusan-kain.png',
        'Mekatronika' => 'assets/images/jurusan/jurusan-pemesinan.png',
    ],

    /*
    | Fallback artwork used when `school_profile.hero_image` or
    | `school_profile.logo` is null.
    */
    'hero_fallback' => 'assets/images/hero/hero-jurusan.png',

    'logo_fallback' => 'assets/images/logo/logo.png',

    'alumni_fallback' => 'assets/images/landing/lulusan-terbaik-w800.jpg',

    /*
    | Number of items shown on the landing page for each section.
    */
    'landing_limits' => [
        'articles' => 4,
        'achievements' => 4,
        'products' => 4,
        'opportunities' => 5,
    ],

];
