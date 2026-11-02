<?php

return [
    // Komponen penilaian yang dapat diaktifkan Admin Sekolah (PRD Fitur 1)
    'jenis_komponen' => [
        'Nilai tugas', 'Kuis', 'Ulangan', 'PTS', 'PAS/SAS', 'Praktik', 'Proyek',
        'Portofolio', 'Sikap/karakter', 'Penilaian formatif', 'Penilaian sumatif',
    ],

    'kategori' => ['pengetahuan' => 'Pengetahuan', 'keterampilan' => 'Keterampilan', 'sikap' => 'Sikap'],

    // Konversi angka -> predikat (batas bawah, urut dari tertinggi)
    'predikat' => [
        ['min' => 92, 'huruf' => 'A'],
        ['min' => 85, 'huruf' => 'A-'],
        ['min' => 80, 'huruf' => 'B+'],
        ['min' => 75, 'huruf' => 'B'],
        ['min' => 70, 'huruf' => 'B-'],
        ['min' => 60, 'huruf' => 'C'],
        ['min' => 0,  'huruf' => 'D'],
    ],

    'kkm_default' => 75,

    // Komponen awal untuk mapel pada sekolah baru: [nama, jenis, kategori, bobot]
    'komponen_default' => [
        ['Tugas', 'Nilai tugas', 'pengetahuan', 15],
        ['Ulangan Harian', 'Ulangan', 'pengetahuan', 20],
        ['PTS', 'PTS', 'pengetahuan', 20],
        ['PAS/SAS', 'PAS/SAS', 'pengetahuan', 25],
        ['Praktik', 'Praktik', 'keterampilan', 10],
        ['Sikap', 'Sikap/karakter', 'sikap', 10],
    ],
];
