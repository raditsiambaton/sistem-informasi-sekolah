<?php
/**
 * tabs/hafalan.php
 * Tampilan tab Hafalan: panel siswa, daftar surah target, modal setoran,
 * modal mushaf, toast, dan JavaScript MUSHAF_MOCK.
 * Variabel yang dipakai: $santri_list
 */
?>
<div class="hafalan-layout">
    <section class="hafalan-panel hafalan-panel-siswa" aria-label="Pilih siswa">
        <h2 class="hafalan-panel-title">
            <i class="bi bi-person"></i>
            <span>Pilih siswa</span>
        </h2>
        <div class="hafalan-search-wrap">
            <i class="bi bi-search"></i>
            <input type="search" id="searchHafalanSiswa" placeholder="Cari nama atau NIS" autocomplete="off">
        </div>
        <div id="listHafalanSiswa">
            <?php foreach ($santri_list as $s):
                $kata    = explode(' ', trim($s['nama_lengkap']));
                $inisial = strtoupper(substr($kata[0], 0, 1));
                if (isset($kata[1])) $inisial .= strtoupper(substr($kata[1], 0, 1));
            ?>
                <button type="button"
                        class="hafalan-siswa-card"
                        data-id="<?= (int)$s['id'] ?>"
                        data-nama="<?= htmlspecialchars($s['nama_lengkap'], ENT_QUOTES) ?>"
                        data-nis="<?= htmlspecialchars($s['nip_nis'], ENT_QUOTES) ?>">
                    <span class="hafalan-avatar" aria-hidden="true"><?= $inisial ?></span>
                    <span class="hafalan-siswa-meta">
                        <span class="hafalan-siswa-nama"><?= htmlspecialchars($s['nama_lengkap']) ?></span>
                        <span class="hafalan-siswa-nis">NIS: <?= htmlspecialchars($s['nip_nis']) ?></span>
                    </span>
                </button>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="hafalan-panel hafalan-panel-surah" aria-label="Daftar surah target hafalan">
        <div id="hafalanEmpty" class="hafalan-empty">
            <p>Pilih siswa terlebih dahulu di panel kiri untuk melihat daftar surah target hafalan.</p>
        </div>
        <div id="hafalanSurahArea" hidden>
            <div class="hafalan-surah-head">
                <div>
                    <h2>Daftar Surah Target Hafalan</h2>
                    <p>Target Aktif: Juz 30 (Adh-Dhuha – An-Nas)</p>
                </div>
                <span class="hafalan-siswa-pill" id="badgeSantriHafalan">Siswa: -</span>
            </div>
            <div class="table-responsive">
                <table class="hafalan-table">
                    <thead>
                        <tr>
                            <th style="width:52px;">No</th>
                            <th>Nama Surah</th>
                            <th style="width:130px;">Target Ayat</th>
                            <th style="width:160px;">Status</th>
                            <th class="col-aksi">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tbodySurahTarget"></tbody>
                </table>
            </div>
        </div>
    </section>
</div>

<!-- Modal: Input Setoran Hafalan -->
<div class="modal fade" id="modalInputSetoran" tabindex="-1" aria-labelledby="judulInputSetoran" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered hafalan-modal-setoran">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="judulInputSetoran">Input Setoran Hafalan</h5>
                    <p class="hafalan-modal-sub" id="subjudulInputSetoran"></p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div class="hafalan-form-grid">
                    <div>
                        <label class="hafalan-label" for="inputAyatAwal">Ayat Awal</label>
                        <select id="inputAyatAwal" class="form-select hafalan-field"></select>
                    </div>
                    <div>
                        <label class="hafalan-label" for="inputAyatAkhir">Ayat Akhir</label>
                        <select id="inputAyatAkhir" class="form-select hafalan-field"></select>
                    </div>
                    <div>
                        <label class="hafalan-label" for="inputKualitas">Kualitas Hafalan</label>
                        <select id="inputKualitas" class="form-select hafalan-field">
                            <option value="Lancar">Lancar</option>
                            <option value="Kurang Lancar">Kurang Lancar</option>
                            <option value="Tidak Lancar">Tidak Lancar</option>
                        </select>
                    </div>
                    <div>
                        <label class="hafalan-label" for="inputTanggalSetoran">Tanggal</label>
                        <input type="date" id="inputTanggalSetoran" class="form-control hafalan-field"
                               value="<?= date('Y-m-d') ?>" data-hari-ini="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="hafalan-form-full">
                        <label class="hafalan-label" for="inputCatatanSetoran">Catatan (Opsional)</label>
                        <textarea id="inputCatatanSetoran" class="form-control hafalan-field" rows="3"
                                  placeholder="Catatan hafalan (opsional)..."></textarea>
                    </div>
                </div>
                <p id="hafalanAyatError" class="hafalan-ayat-error" hidden></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-simpan-setoran" id="btnSimpanHafalan">
                    <i class="bi bi-floppy"></i>
                    <span>Simpan Hafalan</span>
                </button>
                <button type="button" class="btn-batal-setoran" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i>
                    <span>Batal</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Baca Surah (Mushaf) -->
<div class="modal fade" id="modalBacaSurah" tabindex="-1" aria-labelledby="mushafJudul" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered hafalan-modal-mushaf">
        <div class="modal-content">
            <div class="mushaf-header">
                <div>
                    <h5 id="mushafJudul"></h5>
                    <p id="mushafSubjudul"></p>
                </div>
                <div class="mushaf-header-tools">
                    <div class="mushaf-filter">
                        <label for="filterAyatMushaf">Ayat</label>
                        <select id="filterAyatMushaf" class="form-select"></select>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
            </div>
            <div class="mushaf-body" id="mushafBody"></div>
            <div class="mushaf-footer">
                <button type="button" class="btn-tutup-mushaf" data-bs-dismiss="modal">Tutup Mushaf</button>
            </div>
        </div>
    </div>
</div>

<!-- Toast Konfirmasi Setoran -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1090;">
    <div id="toastHafalan" class="toast" role="status" aria-live="polite" aria-atomic="true">
        <div class="toast-body">
            <i class="bi bi-check-circle-fill text-success"></i>
            <span id="toastHafalanPesan"></span>
        </div>
    </div>
</div>

<script>
(function () {
    const MUSHAF_MOCK = {
        'adh-dhuha': {
            no: 93, nama: 'Adh-Dhuha', arab: 'الضحى', arti: 'Duha', jumlah: 11, golongan: 'Makkiyah',
            ayat: [
                { arab: 'وَالضُّحَىٰ', latin: 'Waḍ-ḍuḥā.', arti: 'Demi waktu duha' },
                { arab: 'وَاللَّيْلِ إِذَا سَجَىٰ', latin: 'Wal-laili izā sajā.', arti: 'dan demi waktu malam apabila telah sunyi' },
                { arab: 'مَا وَدَّعَكَ رَبُّكَ وَمَا قَلَىٰ', latin: 'Mā wadda\'aka rabbuka wa mā qalā.', arti: 'Tuhanmu tidak meninggalkan engkau dan tidak (pula) membencimu' },
                { arab: 'وَلَلْآخِرَةُ خَيْرٌ لَّكَ مِنَ الْأُولَىٰ', latin: 'Wa lal-ākhiratu khairul laka minal-ūlā.', arti: 'Dan sungguh, akhirat itu lebih baik bagimu daripada yang permulaan' },
                { arab: 'وَلَسَوْفَ يُعْطِيكَ رَبُّكَ فَتَرْضَىٰ', latin: 'Wa lasaufa yu\'ṭīka rabbuka fa tarḍā.', arti: 'Dan sungguh, kelak Tuhanmu pasti memberikan karunia-Nya kepadamu, sehingga engkau menjadi puas' },
                { arab: 'أَلَمْ يَجِدْكَ يَتِيمًا فَآوَىٰ', latin: 'Alam yajidka yatīman fa āwā.', arti: 'Bukankah Dia mendapatimu sebagai seorang yatim, lalu Dia melindungi(-mu)' },
                { arab: 'وَوَجَدَكَ ضَالًّا فَهَدَىٰ', latin: 'Wa wajadaka ḍāllan fa hadā.', arti: 'Dan Dia mendapatimu sebagai seorang yang bingung, lalu Dia memberikan petunjuk' },
                { arab: 'وَوَجَدَكَ عَائِلًا فَأَغْنَىٰ', latin: 'Wa wajadaka \'ā\'ilan fa aghnā.', arti: 'Dan Dia mendapatimu sebagai seorang yang kekurangan, lalu Dia memberikan kecukupan' },
                { arab: 'فَأَمَّا الْيَتِيمَ فَلَا تَقْهَرْ', latin: 'Fa ammal-yatīma fa lā taqhar.', arti: 'Maka terhadap anak yatim janganlah engkau berlaku sewenang-wenang' },
                { arab: 'وَأَمَّا السَّائِلَ فَلَا تَنْهَرْ', latin: 'Wa ammas-sā\'ila fa lā tanhar.', arti: 'Dan terhadap orang yang meminta-minta janganlah engkau menghardik(-nya)' },
                { arab: 'وَأَمَّا بِنِعْمَةِ رَبِّكَ فَحَدِّثْ', latin: 'Wa ammā bini\'mati rabbika fa ḥaddiṡ.', arti: 'Dan terhadap nikmat Tuhanmu hendaklah engkau nyatakan (dengan bersyukur)' }
            ]
        },
        'al-insyirah': {
            no: 94, nama: 'Al-Insyirah', arab: 'الشرح', arti: 'Kelapangan', jumlah: 8, golongan: 'Makkiyah',
            ayat: [
                { arab: 'أَلَمْ نَشْرَحْ لَكَ صَدْرَكَ', latin: 'Alam nasyraḥ laka ṣadrak.', arti: 'Bukankah Kami telah melapangkan dadamu (Muhammad)?' },
                { arab: 'وَوَضَعْنَا عَنكَ وِزْرَكَ', latin: 'Wa waḍa\'nā \'anka wizrak.', arti: 'dan Kami pun telah menurunkan bebanmu darimu' },
                { arab: 'الَّذِي أَنقَضَ ظَهْرَكَ', latin: 'Allażī anqaḍa ẓahrak.', arti: 'yang memberatkan punggungmu' },
                { arab: 'وَرَفَعْنَا لَكَ ذِكْرَكَ', latin: 'Wa rafa\'nā laka żikrak.', arti: 'dan Kami tinggikan sebutan (nama)mu bagimu' },
                { arab: 'فَإِنَّ مَعَ الْعُسْرِ يُسْرًا', latin: 'Fa inna ma\'al-\'usri yusrā.', arti: 'Maka sesungguhnya beserta kesulitan ada kemudahan' },
                { arab: 'إِنَّ مَعَ الْعُسْرِ يُسْرًا', latin: 'Inna ma\'al-\'usri yusrā.', arti: 'sesungguhnya beserta kesulitan ada kemudahan' },
                { arab: 'فَإِذَا فَرَغْتَ فَانصَبْ', latin: 'Fa iżā faraghta fanṣab.', arti: 'Maka apabila engkau telah selesai (dari sesuatu urusan), tetaplah bekerja keras (untuk urusan yang lain)' },
                { arab: 'وَإِلَىٰ رَبِّكَ فَارْغَبْ', latin: 'Wa ilā rabbika farghab.', arti: 'dan hanya kepada Tuhanmulah engkau berharap' }
            ]
        },
        'at-tin': {
            no: 95, nama: 'At-Tin', arab: 'التين', arti: 'Buah Tin', jumlah: 8, golongan: 'Makkiyah',
            ayat: [
                { arab: 'وَالتِّينِ وَالزَّيْتُونِ', latin: 'Wat-tīni waz-zaitūn.', arti: 'Demi (buah) Tin dan (buah) Zaitun' },
                { arab: 'وَطُورِ سِينِينَ', latin: 'Wa ṭūri sīnīn.', arti: 'demi gunung Sinai' },
                { arab: 'وَهَٰذَا الْبَلَدِ الْأَمِينِ', latin: 'Wa hażal-baladil-amīn.', arti: 'dan demi negeri (Mekah) yang aman ini' },
                { arab: 'لَقَدْ خَلَقْنَا الْإِنسَانَ فِي أَحْسَنِ تَقْوِيمٍ', latin: 'Laqad khalaqnal-insāna fī aḥsani taqwīm.', arti: 'Sungguh, Kami telah menciptakan manusia dalam bentuk yang sebaik-baiknya' },
                { arab: 'ثُمَّ رَدَدْنَاهُ أَسْفَلَ سَافِلِينَ', latin: 'Ṡumma radadnāhu asfala sāfilīn.', arti: 'kemudian Kami kembalikan dia ke tempat yang serendah-rendahnya' },
                { arab: 'إِلَّا الَّذِينَ آمَنُوا وَعَمِلُوا الصَّالِحَاتِ فَلَهُمْ أَجْرٌ غَيْرُ مَمْنُونٍ', latin: 'Illal-lażīna āmanū wa \'amiluṣ-ṣāliḥāti falahum ajrun gairu mamnūn.', arti: 'kecuali orang-orang yang beriman dan mengerjakan kebajikan; maka mereka akan mendapat pahala yang tidak putus-putusnya' },
                { arab: 'فَمَا يُكَذِّبُكَ بَعْدُ بِالدِّينِ', latin: 'Fama yukażżibuka ba\'du bid-dīn.', arti: 'Maka apa yang menyebabkan (mereka) mendustakanmu (tentang) hari pembalasan setelah (adanya keterangan-keterangan) itu?' },
                { arab: 'أَلَيْسَ اللَّهُ بِأَحْكَمِ الْحَاكِمِينَ', latin: 'Alaisallāhu bi-aḥkamil-ḥākimīn.', arti: 'Bukankah Allah hakim yang paling adil?' }
            ]
        },
        'al-alaq': {
            no: 96, nama: "Al-'Alaq", arab: 'العلق', arti: 'Segumpal Darah', jumlah: 19, golongan: 'Makkiyah',
            ayat: [
                { arab: 'اقْرَأْ بِاسْمِ رَبِّكَ الَّذِي خَلَقَ', latin: 'Iqra\' bismi rabbikal-lażī khalaq.', arti: 'Bacalah dengan (menyebut) nama Tuhanmu yang menciptakan' },
                { arab: 'خَلَقَ الْإِنسَانَ مِنْ عَلَقٍ', latin: 'Khalaqal-insāna min \'alaq.', arti: 'Dia telah menciptakan manusia dari segumpal darah' },
                { arab: 'اقْرَأْ وَرَبُّكَ الْأَكْرَمُ', latin: 'Iqra\' wa rabbukal-akram.', arti: 'Bacalah, dan Tuhanmulah Yang Mahamulia' },
                { arab: 'الَّذِي عَلَّمَ بِالْقَلَمِ', latin: 'Allażī \'allama bil-qalam.', arti: 'Yang mengajar (manusia) dengan pena' },
                { arab: 'عَلَّمَ الْإِنسَانَ مَا لَمْ يَعْلَمْ', latin: '\'Allamal-insāna mā lam ya\'lam.', arti: 'Dia mengajarkan manusia apa yang tidak diketahuinya' },
                { arab: 'كَلَّا إِنَّ الْإِنسَانَ لَيَطْغَىٰ', latin: 'Kallā innal-insāna laya ṭgā.', arti: 'Sekali-kali tidak! Sungguh, manusia itu benar-benar melampaui batas' },
                { arab: 'أَن رَّآهُ اسْتَغْنَىٰ', latin: 'Ar ra\'āhustagnā.', arti: 'apabila melihat dirinya serba berkecukupan' },
                { arab: 'إِنَّ إِلَىٰ رَبِّكَ الرُّجْعَىٰ', latin: 'Inna ilā rabbikar-ruj\'ā.', arti: 'Sungguh, hanya kepada Tuhanmulah kembalimu' },
                { arab: 'أَرَأَيْتَ الَّذِي يَنْهَىٰ', latin: 'Ara\'aital-lażī yanhā.', arti: 'Bagaimana pendapatmu tentang orang yang melarang' },
                { arab: 'عَبْدًا إِذَا صَلَّىٰ', latin: '\'Abdan iżā ṣallā.', arti: 'seorang hamba ketika dia melaksanakan salat' },
                { arab: 'أَرَأَيْتَ إِن كَانَ عَلَى الْهُدَىٰ', latin: 'Ara\'aita in kāna \'alal-hudā.', arti: 'bagaimana pendapatmu jika dia (yang dilarang salat itu) berada di atas kebenaran' },
                { arab: 'أَوْ أَمَرَ بِالتَّقْوَىٰ', latin: 'Au amara bit-taqwā.', arti: 'atau dia menyuruh bertakwa (kepada Allah)?' },
                { arab: 'أَرَأَيْتَ إِن كَذَّبَ وَتَوَلَّىٰ', latin: 'Ara\'aita in każżaba wa tawallā.', arti: 'Bagaimana pendapatmu jika dia (orang yang melarang) itu mendustakan dan berpaling?' },
                { arab: 'أَلَمْ يَعْلَم بِأَنَّ اللَّهَ يَرَىٰ', latin: 'Alam ya\'lam bi\'annallāha yarā.', arti: 'Tidakkah dia mengetahui bahwa sesungguhnya Allah melihat (segala perbuatannya)?' },
                { arab: 'كَلَّا لَئِن لَّمْ يَنتَهِ لَنَسْفَعًا بِالنَّاصِيَةِ', latin: 'Kallā la\'il lam yantahi lanasfa\'am bin-nāṣiyah.', arti: 'Sekali-kali tidak! Sungguh, jika dia tidak berhenti (berbuat demikian) niscaya Kami tarik ubun-ubunnya' },
                { arab: 'نَاصِيَةٍ كَاذِبَةٍ خَاطِئَةٍ', latin: 'Nāṣiyatin kāżibatin khāṭi\'ah.', arti: '(yaitu) ubun-ubun orang yang mendustakan dan durhaka' },
                { arab: 'فَلْيَدْعُ نَادِيَهُ', latin: 'Falyad\'u nādiyah.', arti: 'maka biarlah dia memanggil golongannya (untuk menolongnya)' },
                { arab: 'سَنَدْعُ الزَّبَانِيَةَ', latin: 'Sanad\'uz-zabāniyah.', arti: 'Kelak Kami akan memanggil Malaikat Zabaniyah' },
                { arab: 'كَلَّا لَا تُطِعْهُ وَاسْجُدْ وَاقْتَرِب ۩', latin: 'Kallā lā tuṭi\'hu wasjud waqtarib.', arti: 'Sekali-kali tidak! Janganlah kamu patuh kepadanya; dan sujudlah serta dekatkanlah (dirimu kepada Allah)' }
            ]
        },
        'al-qadr': {
            no: 97, nama: 'Al-Qadr', arab: 'القدر', arti: 'Kemuliaan', jumlah: 5, golongan: 'Makkiyah',
            ayat: [
                { arab: 'إِنَّا أَنزَلْنَاهُ فِي لَيْلَةِ الْقَدْرِ', latin: 'Innā anzalnāhu fī lailatil-qadr.', arti: 'Sesungguhnya Kami telah menurunkannya (Al-Qur\'an) pada malam qadar' },
                { arab: 'وَمَا أَدْرَاكَ مَا لَيْلَةُ الْقَدْرِ', latin: 'Wa mā adrāka mā lailatul-qadr.', arti: 'Dan tahukah kamu apakah malam kemuliaan itu?' },
                { arab: 'لَيْلَةُ الْقَدْرِ خَيْرٌ مِّنْ أَلْفِ شَهْرٍ', latin: 'Lailatul-qadri khairum min alfi syahr.', arti: 'Malam kemuliaan itu lebih baik daripada seribu bulan' },
                { arab: 'تَنَزَّلُ الْمَلَائِكَةُ وَالرُّوحُ فِيهَا بِإِذْنِ رَبِّهِم مِّن كُلِّ أَمْرٍ', latin: 'Tanazzalul-malā\'ikatu war-rūḥu fīhā bi\'izni rabbihim min kulli amr.', arti: 'Pada malam itu turun para malaikat dan Ruh (Jibril) dengan izin Tuhannya untuk mengatur semua urusan' },
                { arab: 'سَلَامٌ هِيَ حَتَّىٰ مَطْلَعِ الْفَجْرِ', latin: 'Salāmun hiya ḥattā maṭla\'il-fajr.', arti: 'Sejahteralah (malam itu) sampai terbit fajar' }
            ]
        }
    };

    const SURAH_TARGET_AWAL = [
        { key: 'adh-dhuha',   status: 'selesai'  },
        { key: 'al-insyirah', status: 'selesai'  },
        { key: 'at-tin',      status: 'berjalan' },
        { key: 'al-alaq',     status: 'belum'    },
        { key: 'al-qadr',     status: 'belum'    }
    ];

    const STATUS_TAMPILAN = {
        selesai:  { label: 'Selesai',         kelas: 'hafalan-badge-selesai'  },
        berjalan: { label: 'Sedang Berjalan', kelas: 'hafalan-badge-berjalan' },
        belum:    { label: 'Belum Setor',     kelas: 'hafalan-badge-belum'    }
    };

    const hafalanStatusSiswa = {};
    let siswaHafalanTerpilih = null;
    let surahSetoranAktif    = null;

    function escHtml(nilai) {
        return String(nilai)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function pastikanState(id) {
        if (!hafalanStatusSiswa[id]) {
            hafalanStatusSiswa[id] = SURAH_TARGET_AWAL.map(item => ({ key: item.key, status: item.status }));
        }
        return hafalanStatusSiswa[id];
    }

    function renderTabelSurah() {
        if (!siswaHafalanTerpilih) return;
        const daftar = pastikanState(siswaHafalanTerpilih.id);
        document.getElementById('tbodySurahTarget').innerHTML = daftar.map(function (item, indeks) {
            const meta   = MUSHAF_MOCK[item.key];
            const status = STATUS_TAMPILAN[item.status] || STATUS_TAMPILAN.belum;
            return '<tr>'
                + '<td style="color:#94A3B8;font-size:13px;">' + (indeks + 1) + '.</td>'
                + '<td><div class="hafalan-surah-cell">'
                + '<span class="hafalan-surah-nama">' + escHtml(meta.nama) + ' &ndash; <span class="hafalan-arab">' + meta.arab + '</span></span>'
                + '<button type="button" class="btn-baca-ayat" data-aksi="baca" data-key="' + item.key + '" aria-label="Baca Surah ' + escHtml(meta.nama) + '"><i class="bi bi-book"></i></button>'
                + '</div></td>'
                + '<td style="color:#64748B;font-size:13.5px;">Ayat 1\u2013' + meta.jumlah + '</td>'
                + '<td><span class="hafalan-badge ' + status.kelas + '">' + status.label + '</span></td>'
                + '<td class="col-aksi"><button type="button" class="btn-setoran-plus" data-aksi="setor" data-key="' + item.key + '" aria-label="Input setoran ' + escHtml(meta.nama) + '"><i class="bi bi-plus-lg"></i></button></td>'
                + '</tr>';
        }).join('');
    }

    function pilihSiswaHafalan(kartu) {
        siswaHafalanTerpilih = { id: kartu.dataset.id, nama: kartu.dataset.nama, nis: kartu.dataset.nis };
        document.querySelectorAll('.hafalan-siswa-card').forEach(el => el.classList.remove('aktif'));
        kartu.classList.add('aktif');
        document.getElementById('hafalanEmpty').hidden       = true;
        document.getElementById('hafalanSurahArea').hidden   = false;
        document.getElementById('badgeSantriHafalan').textContent = 'Siswa: ' + siswaHafalanTerpilih.nama;
        renderTabelSurah();
    }

    function isiOpsiAyat(idSelect, jumlah, terpilih) {
        const select = document.getElementById(idSelect);
        let html = '';
        for (let n = 1; n <= jumlah; n++) html += '<option value="' + n + '">' + n + '</option>';
        select.innerHTML = html;
        select.value = String(terpilih);
    }

    function validasiRentangAyat() {
        const awal  = parseInt(document.getElementById('inputAyatAwal').value, 10);
        const akhir = parseInt(document.getElementById('inputAyatAkhir').value, 10);
        const pesan = document.getElementById('hafalanAyatError');
        if (akhir < awal) {
            pesan.textContent = 'Ayat akhir tidak boleh lebih kecil dari ayat awal.';
            pesan.hidden = false;
            return false;
        }
        pesan.hidden = true;
        return true;
    }

    function bukaSetoran(key) {
        const meta = MUSHAF_MOCK[key];
        if (!meta || !siswaHafalanTerpilih) return;
        surahSetoranAktif = key;
        document.getElementById('subjudulInputSetoran').textContent =
            'Siswa: ' + siswaHafalanTerpilih.nama + ' \u2013 Surah ' + meta.nama + ' (' + meta.jumlah + ' Ayat)';
        isiOpsiAyat('inputAyatAwal',  meta.jumlah, 1);
        isiOpsiAyat('inputAyatAkhir', meta.jumlah, meta.jumlah);
        document.getElementById('inputKualitas').value      = 'Lancar';
        document.getElementById('inputCatatanSetoran').value = '';
        const tanggal = document.getElementById('inputTanggalSetoran');
        tanggal.value = tanggal.dataset.hariIni || tanggal.value;
        document.getElementById('hafalanAyatError').hidden  = true;
        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalInputSetoran')).show();
    }

    function bukaMushaf(key) {
        const data = MUSHAF_MOCK[key];
        if (!data) return;
        document.getElementById('mushafJudul').innerHTML =
            escHtml(data.nama) + ' - <span class="mushaf-judul-arab">' + data.arab + '</span> (QS. ' + data.no + ')';
        document.getElementById('mushafSubjudul').textContent =
            data.arti + ' \u2013 ' + data.jumlah + ' Ayat \u2013 ' + data.golongan;

        const select = document.getElementById('filterAyatMushaf');
        const tubuh  = document.getElementById('mushafBody');
        let opsi = '<option value="">Pilih Ayat</option>';
        tubuh.innerHTML = data.ayat.map(function (ayat, indeks) {
            const nomor = indeks + 1;
            opsi += '<option value="' + nomor + '">Ayat ' + nomor + '</option>';
            return '<article class="mushaf-ayat" id="mushafAyat-' + nomor + '">'
                + '<div class="mushaf-ayat-top"><span class="mushaf-no">' + nomor + '</span><p class="mushaf-arab">' + ayat.arab + '</p></div>'
                + '<p class="mushaf-latin">' + escHtml(ayat.latin) + '</p>'
                + '<p class="mushaf-terjemah">' + escHtml(ayat.arti) + '</p>'
                + '<p class="mushaf-ket"><i class="bi bi-volume-up" aria-hidden="true"></i> QS. ' + escHtml(data.nama) + ' ayat ke-' + nomor + '</p>'
                + '</article>';
        }).join('');
        select.innerHTML = opsi;
        select.value     = '';
        tubuh.scrollTop  = 0;
        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalBacaSurah')).show();
    }

    function gulirKeAyat() {
        const nomor = document.getElementById('filterAyatMushaf').value;
        const tubuh = document.getElementById('mushafBody');
        tubuh.querySelectorAll('.mushaf-ayat').forEach(el => el.classList.remove('aktif'));
        if (!nomor) return;
        const target = document.getElementById('mushafAyat-' + nomor);
        if (!target) return;
        target.classList.add('aktif');
        tubuh.scrollTo({ top: target.offsetTop - 8, behavior: 'smooth' });
    }

    function simpanSetoran() {
        if (!validasiRentangAyat() || !siswaHafalanTerpilih || !surahSetoranAktif) return;
        const awal  = parseInt(document.getElementById('inputAyatAwal').value, 10);
        const akhir = parseInt(document.getElementById('inputAyatAkhir').value, 10);
        const meta  = MUSHAF_MOCK[surahSetoranAktif];
        const daftar = pastikanState(siswaHafalanTerpilih.id);
        const item   = daftar.find(baris => baris.key === surahSetoranAktif);
        if (item && meta) {
            item.status = (awal === 1 && akhir === meta.jumlah) ? 'selesai' : 'berjalan';
        }
        renderTabelSurah();
        const modal = bootstrap.Modal.getInstance(document.getElementById('modalInputSetoran'));
        if (modal) modal.hide();
        document.getElementById('toastHafalanPesan').textContent =
            'Setoran hafalan ' + siswaHafalanTerpilih.nama + ' untuk Surah ' + meta.nama + ' (Ayat ' + awal + '\u2013' + akhir + ') berhasil disimpan.';
        bootstrap.Toast.getOrCreateInstance(document.getElementById('toastHafalan'), { delay: 3400 }).show();
    }

    document.getElementById('listHafalanSiswa').addEventListener('click', function (event) {
        const kartu = event.target.closest('.hafalan-siswa-card');
        if (kartu) pilihSiswaHafalan(kartu);
    });
    document.getElementById('tbodySurahTarget').addEventListener('click', function (event) {
        const tombol = event.target.closest('[data-aksi]');
        if (!tombol) return;
        if (tombol.dataset.aksi === 'baca')  bukaMushaf(tombol.dataset.key);
        if (tombol.dataset.aksi === 'setor') bukaSetoran(tombol.dataset.key);
    });
    document.getElementById('searchHafalanSiswa').addEventListener('input', function () {
        const kata = this.value.trim().toLowerCase();
        document.querySelectorAll('.hafalan-siswa-card').forEach(function (kartu) {
            const nama = (kartu.dataset.nama || '').toLowerCase();
            const nis  = (kartu.dataset.nis  || '').toLowerCase();
            kartu.style.display = (nama.includes(kata) || nis.includes(kata)) ? '' : 'none';
        });
    });
    document.getElementById('inputAyatAwal').addEventListener('change',  validasiRentangAyat);
    document.getElementById('inputAyatAkhir').addEventListener('change', validasiRentangAyat);
    document.getElementById('btnSimpanHafalan').addEventListener('click', simpanSetoran);
    document.getElementById('filterAyatMushaf').addEventListener('change', gulirKeAyat);

    const preset = new URLSearchParams(window.location.search).get('santri');
    if (preset) {
        const kunci = preset.toLowerCase();
        const kartu = Array.from(document.querySelectorAll('.hafalan-siswa-card'));
        const cocok = kartu.find(el => (el.dataset.nama || '').toLowerCase() === kunci)
                   || kartu.find(el => (el.dataset.nama || '').toLowerCase().includes(kunci));
        if (cocok) cocok.click();
    }
})();
</script>
