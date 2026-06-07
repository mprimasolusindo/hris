<?php

namespace Database\Seeders\Support;

use Faker\Factory;
use Faker\Generator;

/**
 * Curated Indonesian labels and helpers for HRIS demo seeding.
 * Person names prefer Faker locale id_ID when available.
 */
final class IndonesianDemoData
{
    /** @var list<string> */
    public const DEPARTMENT_NAMES = [
        'Operasional & Produksi',
        'Keuangan & Akuntansi',
        'Sumber Daya Manusia',
        'Teknologi Informasi',
        'Pemasaran & Penjualan',
        'Logistik & Gudang',
        'Hukum & Kepatuhan',
        'Riset & Pengembangan',
        'Customer Service',
        'Manajemen Umum',
    ];

    /** @var list<string> */
    public const POSITION_NAMES = [
        'Staff Administrasi',
        'Analis Keuangan',
        'HR Generalist',
        'Software Engineer',
        'Account Executive',
        'Supervisor Gudang',
        'Legal Officer',
        'Data Analyst',
        'Customer Service Representative',
        'Office Manager',
        'Koordinator Proyek',
        'Teknisi Lapangan',
        'Purchasing Staff',
        'Internal Auditor',
        'Product Owner',
    ];

    /** @var list<string> */
    public const RELIGIONS = ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'];

    /** @var list<string> */
    public const COMPANY_NAMES = [
        'PT Mitra Sejahtera Indonesia',
        'PT Nusantara Abadi Sentosa',
    ];

    public static function makeFaker(): Generator
    {
        return Factory::create('id_ID');
    }

    /**
     * Deterministic work email — guaranteed unique per numeric id.
     */
    public static function workEmail(int $employeeSequence): string
    {
        return sprintf('karyawan.%05d@demo-seed.hris.local', $employeeSequence);
    }

    /**
     * Indonesian-style mobile number (08xx).
     */
    public static function indonesianMobile(Generator $faker): string
    {
        return '08'.$faker->numerify('##########');
    }
}
