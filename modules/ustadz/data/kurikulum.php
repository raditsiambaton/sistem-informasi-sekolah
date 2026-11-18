<?php
/**
 * data/kurikulum.php
 * Definisi kurikulum target hafalan: Juz 1–5 dan Juz 30.
 * Di-require dari modules/ustadz/index.php.
 * Variabel yang dihasilkan: $juz30_target_sumber, $label_juz30_target,
 *                           $kurikulum_target, $urutan_opsi_target
 */

$juz30_target_sumber = [
    ["An-Naba'", 40], ["An-Nazi'at", 46], ["'Abasa", 42], ['At-Takwir', 29],
    ['Al-Infitar', 19], ['Al-Muthaffifin', 36], ['Al-Insyiqaq', 25], ['Al-Buruj', 22],
    ['At-Tariq', 17], ["Al-A'la", 19], ['Al-Ghasyiyah', 26], ['Al-Fajr', 30],
    ['Al-Balad', 20], ['Asy-Syams', 15], ['Al-Lail', 21], ['Adh-Dhuha', 11],
    ['Al-Insyirah', 8], ['At-Tin', 8], ["Al-'Alaq", 19], ['Al-Qadr', 5],
    ['Al-Bayyinah', 8], ['Az-Zalzalah', 8], ["Al-'Adiyat", 11], ["Al-Qari'ah", 11],
    ['At-Takatsur', 8], ["Al-'Asr", 3], ['Al-Humazah', 9], ['Al-Fil', 5],
    ['Quraisy', 4], ["Al-Ma'un", 7], ['Al-Kautsar', 3], ['Al-Kafirun', 6],
    ['An-Nasr', 3], ['Al-Lahab', 5], ['Al-Ikhlas', 4], ['Al-Falaq', 5], ['An-Nas', 6],
];

$label_juz30_target = [];
foreach ($juz30_target_sumber as $item_juz30) {
    $label_juz30_target[] = 'Juz 30 – ' . $item_juz30[0] . ' (Ayat 1–' . $item_juz30[1] . ')';
}

$kurikulum_target = [
    'Juz 1' => [
        'Juz 1 – Al-Fatihah (Ayat 1–7)',
        'Juz 1 – Al-Baqarah (Ayat 1–141)',
    ],
    'Juz 2' => [
        'Juz 2 – Al-Baqarah (Ayat 142–252)',
    ],
    'Juz 3' => [
        'Juz 3 – Al-Baqarah (Ayat 253–286)',
        "Juz 3 – Ali 'Imran (Ayat 1–92)",
    ],
    'Juz 4' => [
        "Juz 4 – Ali 'Imran (Ayat 93–200)",
        "Juz 4 – An-Nisa' (Ayat 1–23)",
    ],
    'Juz 5' => [
        "Juz 5 • An-Nisa' (Ayat 24–147)",
    ],
    'Juz 30' => $label_juz30_target,
];

$urutan_opsi_target = [];
foreach ($kurikulum_target as $daftar_opsi) {
    foreach ($daftar_opsi as $label_opsi) {
        $urutan_opsi_target[] = $label_opsi;
    }
}

// Fallback daftar santri jika database tidak tersedia
$santri_list_fallback = [
    ['id'=>3, 'username'=>'siswa',  'nama_lengkap'=>'Muhammad Fadhlan',       'nip_nis'=>'102601','halaqah'=>'Al-Furqan'],
    ['id'=>4, 'username'=>'102602', 'nama_lengkap'=>'Ahmad Zaid',              'nip_nis'=>'102602','halaqah'=>'Al-Furqan'],
    ['id'=>5, 'username'=>'102603', 'nama_lengkap'=>'Radit Gilang Ramadhan S', 'nip_nis'=>'102603','halaqah'=>'Al-Furqan'],
    ['id'=>6, 'username'=>'102604', 'nama_lengkap'=>'M Hafizh Habibillah',     'nip_nis'=>'102604','halaqah'=>'Al-Furqan'],
    ['id'=>7, 'username'=>'102605', 'nama_lengkap'=>'Khadijah Azzahra',        'nip_nis'=>'102605','halaqah'=>'Al-Furqan'],
    ['id'=>8, 'username'=>'102606', 'nama_lengkap'=>'Zaid Al-Ghifari',         'nip_nis'=>'102606','halaqah'=>'Al-Furqan'],
    ['id'=>9, 'username'=>'102607', 'nama_lengkap'=>'Aisyah Humaira',          'nip_nis'=>'102607','halaqah'=>'Al-Furqan'],
    ['id'=>10,'username'=>'102608', 'nama_lengkap'=>'Abdullah Hasan',           'nip_nis'=>'102608','halaqah'=>'Al-Furqan'],
    ['id'=>11,'username'=>'102609', 'nama_lengkap'=>'Fatimah Zahra',            'nip_nis'=>'102609','halaqah'=>'Al-Furqan'],
    ['id'=>12,'username'=>'102610', 'nama_lengkap'=>'Muhammad Farhan',          'nip_nis'=>'102610','halaqah'=>'Al-Furqan'],
];
