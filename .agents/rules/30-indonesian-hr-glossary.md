---
description: Quick-reference glossary of Indonesian HR / payroll / tax / labor-law terms. Attach manually with @ when you want it as in-context cheat-sheet.
---

# Indonesian HR Glossary (quick reference)

Cheat-sheet of terms used across this codebase. For full regulation-cited
explanations, use the HR research skill or sub-agent (see
`20-hr-research-indonesia.mdc`).

## Employment types

| Term | Indonesian | Meaning |
|---|---|---|
| PKWTT | Perjanjian Kerja Waktu Tidak Tertentu | Permanent / indefinite employment |
| PKWT | Perjanjian Kerja Waktu Tertentu | Fixed-term contract; max 5 yrs total per PP 35/2021 ps. 8 |
| Alih daya | — | Outsourcing — worker employed by vendor, deployed at user company |
| Magang | — | Apprenticeship / internship |
| Karyawan tetap | — | Permanent employee (PKWTT) |
| Karyawan kontrak | — | Contract employee (PKWT) |

## Tax (PPh 21)

| Term | Meaning |
|---|---|
| PPh 21 | Pajak Penghasilan Pasal 21 — withholding tax on employment income |
| TER | Tarif Efektif Rata-rata — effective average rate (PMK 168/2023, monthly calc) |
| PTKP | Penghasilan Tidak Kena Pajak — non-taxable income threshold |
| NPWP | Nomor Pokok Wajib Pajak — taxpayer ID |
| NIK | Nomor Induk Kependudukan — citizen ID; now also functions as NPWP |
| TK/0…TK/3 | Tidak Kawin (single) with 0–3 dependents |
| K/0…K/3 | Kawin (married) with 0–3 dependents |
| HPP | Harmonisasi Peraturan Perpajakan (UU 7/2021) |

### TER categories (PMK 168/2023)

- **Category A:** TK/0, TK/1, K/0
- **Category B:** TK/2, TK/3, K/1, K/2
- **Category C:** K/3

## Social security (BPJS)

| Program | Indonesian | Total | Employee | Employer | Notes |
|---|---|---|---|---|---|
| BPJS Kesehatan | Health insurance | 5% | 1% | 4% | Perpres 64/2020. Salary cap. |
| JHT | Jaminan Hari Tua | 5,7% | 2% | 3,7% | PP 46/2015. Old-age savings. |
| JP | Jaminan Pensiun | 3% | 1% | 2% | PP 45/2015. Pension. Salary cap. |
| JKK | Jaminan Kecelakaan Kerja | 0,24%–1,74% | 0% | 100% | PP 44/2015. Risk-tier based. |
| JKM | Jaminan Kematian | 0,3% | 0% | 100% | PP 44/2015. Death benefit. |
| JKP | Jaminan Kehilangan Pekerjaan | 0,46% | 0% | 0% (govt + recomp) | PP 37/2021. Unemployment. |

> Treat percentages as draft until verified against the current
> regulation. Store in `cfg_bpjs` table, never hard-code.

## Allowances (tunjangan)

| Term | Meaning |
|---|---|
| THR | Tunjangan Hari Raya — religious holiday allowance, mandatory (Permenaker 6/2016) |
| Tunjangan transport | Transport allowance |
| Tunjangan makan | Meal allowance |
| Tunjangan jabatan | Position allowance |
| Tunjangan komunikasi | Communication allowance |
| Tunjangan kehadiran | Attendance allowance (often forfeited on absence/lateness) |

## Termination & wages

| Term | Meaning |
|---|---|
| Pesangon | Severance pay (PP 35/2021 ps. 40) |
| Uang penghargaan masa kerja | Long-service award |
| Uang penggantian hak | Compensation for unused entitlements |
| Lembur | Overtime — `1/173 × monthly wage` per hour (PP 35/2021 ps. 30–32) |
| UMP | Upah Minimum Provinsi — provincial minimum wage |
| UMK | Upah Minimum Kabupaten/Kota — regency / city minimum wage |

## Leave & absence

| Term | Meaning |
|---|---|
| Cuti tahunan | Annual leave (≥ 12 days/yr after 12 months service, UU 13/2003 ps. 79) |
| Cuti besar | Long service leave |
| Cuti sakit | Sick leave |
| Cuti melahirkan | Maternity leave (3 months, UU 13/2003 ps. 82) |
| Izin | Permitted absence (varies by event — marriage, death, etc.) |

## Key regulations cited in this repo

| Reg | Topic |
|---|---|
| UU 13/2003 | Ketenagakerjaan (Manpower Act) — base text |
| UU 6/2023 | Cipta Kerja (current, replaces UU 11/2020 via Perppu 2/2022) |
| UU 7/2021 | HPP — tax harmonization |
| PP 35/2021 | PKWT, alih daya, waktu kerja, PHK |
| PP 36/2021 | Pengupahan (wages) |
| PP 44/2015, 45/2015, 46/2015 | BPJS programs (JKK/JKM, JP, JHT) |
| PP 37/2021 | JKP |
| PP 58/2023 | Basis for PMK 168/2023 |
| PMK 168/2023 | PPh 21 TER (effective 1 Jan 2024) |
| Perpres 64/2020 | BPJS Kesehatan iuran |
| Permenaker 6/2016 | THR |
