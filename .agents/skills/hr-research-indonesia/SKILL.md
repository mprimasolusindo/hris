---
name: hr-research-indonesia
description: |
  Use when the user asks about Indonesian HR / HRIS / payroll topics —
  labor law, contract employment (PKWT/PKWTT), outsourcing/alih daya,
  payroll tax (PPh21), BPJS (Kesehatan + Ketenagakerjaan), allowances
  (THR, transport, meal, etc.), deductions, severance, or work-time rules.
  Triggers on Indonesian terms (PKWT, PKWTT, BPJS, PPh21, THR, alih daya,
  tunjangan, potongan, pesangon, NPWP, PTKP) and English equivalents.
  Activates whenever the discovery / implementation work touches the
  HRIS product in this repo.
priority: high
---

# Skill: Indonesian HR Research

A research and citation discipline for Indonesian-context HR questions in
this HRIS Laravel codebase. Use it whenever you're advising on,
reviewing, or implementing logic that touches Indonesian labor law,
payroll, tax, BPJS, contract types, or outsourcing.

> If you need *deep* research with browsing, dispatch the
> [`hr-researcher-indonesia`](../../../.claude/agents/hr-researcher-indonesia.md)
> sub-agent instead. This skill is for in-line, in-session reasoning.

---

## When to activate

Activate when **any** of the following appear in the user's message, your
plan, or the file you're editing:

**Indonesian terms:** PKWT, PKWTT, BPJS, BPJS Kesehatan, BPJS
Ketenagakerjaan, PPh21, PPh 21, THR, alih daya, outsourcing, tunjangan,
potongan, pesangon, uang penghargaan, NPWP, PTKP, NIK, JHT, JP, JKK, JKM,
JKP, lembur, cuti, kontrak, karyawan tetap, karyawan kontrak, PKB, UMP,
UMK, upah minimum.

**English equivalents:** Indonesian payroll, Indonesian tax, fixed-term
contract, severance pay, social security Indonesia, employee allowance,
income tax Indonesia, outsourcing law Indonesia.

**Code / file signals:** any payroll calculator code, any contract /
employment model, anything reading the `cfg_bpjs` or `cfg_tax_rules`
tables, anything emitting `pay_payroll_items.type = 'deduction'`, any
migration / Eloquent model under the `emp_*`, `pay_*`, `lv_*`, `ot_*`,
or `cfg_*` table prefix.

---

## Research output discipline

Every claim about Indonesian HR rules MUST follow this format:

> **Claim** + **regulation reference** (UU / PP / PMK / Perpres + pasal +
> ayat where relevant) + **effective date** + **edge case / exception**.

Example:

> PKWT can run for a maximum of **5 years** (previously 3) per **PP
> 35/2021 ps. 8 ayat 1**, in force since 2 Feb 2021. Edge case: if the
> nature of the work doesn't fit a fixed-term description, the
> relationship is treated as PKWTT regardless of the written contract
> (PP 35/2021 ps. 5).

If the regulation can't be cited, label the statement
`[unverified — needs research]` and either invoke the sub-agent or ask
the user for confirmation.

---

## Authoritative sources (use these first)

### Primary law
- **UU 13/2003** — Ketenagakerjaan (the original Manpower Act). Many
  pasals amended by Cipta Kerja but still the base text.
- **UU 6/2023** — Penetapan Perppu 2/2022 menjadi UU (Cipta Kerja). This
  is the **current** umbrella for amendments to UU 13/2003. (UU 11/2020
  was struck down by MK and replaced via Perppu 2/2022 → UU 6/2023.)

### Implementing regulations (PP)
- **PP 35/2021** — PKWT, Alih Daya (outsourcing), Waktu Kerja & Istirahat,
  PHK. Implementing regulation under Cipta Kerja.
- **PP 36/2021** — Pengupahan (wages, UM, structure & scale).
- **PP 78/2015** — Pengupahan (older — superseded for many provisions by
  PP 36/2021 but still cited for legacy contracts).
- **PP 84/2013** — BPJS Ketenagakerjaan (program & iuran framework).
- **PP 58/2023** — basis for PMK 168/2023 (PPh21 TER).

### Tax (Direktorat Jenderal Pajak)
- **PMK 168/2023** — Tarif Efektif Rata-rata (TER) for PPh21. **Effective
  1 Januari 2024.** Replaces PMK 252/2008. Three PTKP categories (A, B,
  C) with monthly TER + daily TER. December reconciliation still uses
  Pasal 17 UU PPh.
- **UU PPh** (UU 7/1983 sebagaimana diubah terakhir dengan UU 7/2021
  HPP) — base income tax law. Pasal 17 progressive rates.

### Social security
- **Perpres 64/2020** (jo. Perpres 75/2019) — BPJS Kesehatan iuran. PPU
  formal: 5% of upah (employer 4% + employee 1%), upper cap on basis.
- **PP 84/2013** + **PP 44/2015** + **PP 45/2015** + **PP 46/2015** —
  Ketenagakerjaan programs (JHT, JKK, JKM, JP) and their iuran rules.
- **PP 37/2021** — Jaminan Kehilangan Pekerjaan (JKP).

### Government portals (use for verification, not as primary citation)
- pajak.go.id (DJP)
- kemnaker.go.id
- bpjsketenagakerjaan.go.id, bpjs-kesehatan.go.id
- peraturan.bpk.go.id (full regulation text)

---

## Quick-reference: contract types

| Type | Indonesian | Description | Key rules |
|---|---|---|---|
| Permanent | PKWTT (Perjanjian Kerja Waktu Tidak Tertentu) | Indefinite | Probation up to 3 months allowed (UU 13/2003 ps. 60). Severance per PP 35/2021 ps. 40. |
| Fixed-term | PKWT (Perjanjian Kerja Waktu Tertentu) | Time- or project-limited | Max 5 years incl. extension (PP 35/2021 ps. 8). Must be written. **No probation period.** Compensation due at expiry (PP 35/2021 ps. 15). |
| Outsourcing | Alih daya | Worker employed by vendor, deployed at user company | Vendor must be badan hukum with central-gov license (PP 35/2021 ps. 18). Worker contract can be PKWT or PKWTT (PP 35/2021 ps. 19). |
| Internship | Magang / PKL | Training-oriented | Permenaker 6/2020 (Magang Dalam Negeri). |

---

## Quick-reference: payroll deduction percentages (verify before locking)

> These are the figures circulating in 2024-2026. **Treat as draft until
> confirmed against the specific PP / Perpres + pasal for the calculation
> period you're computing.** Store them in `cfg_bpjs` / `cfg_tax_rules`
> tables, not hard-coded.

### BPJS Kesehatan (Perpres 64/2020)
- Employee: **1%** of upah
- Employer: **4%** of upah
- Salary cap: Rp 12.000.000 (verify current cap; cap was raised before)

### BPJS Ketenagakerjaan
| Program | Total | Employee | Employer | Notes |
|---|---|---|---|---|
| **JHT** (Jaminan Hari Tua) | 5,7% | 2% | 3,7% | PP 46/2015 |
| **JP** (Jaminan Pensiun) | 3% | 1% | 2% | PP 45/2015. Salary cap (verify current). |
| **JKK** (Jaminan Kecelakaan Kerja) | 0,24% – 1,74% | 0% | 100% | PP 44/2015. Rate by risk tier (sangat rendah / rendah / sedang / tinggi / sangat tinggi). |
| **JKM** (Jaminan Kematian) | 0,3% | 0% | 100% | PP 44/2015. |
| **JKP** (Jaminan Kehilangan Pekerjaan) | 0,46% | 0% | 0% (gov + recomposition) | PP 37/2021. 0,22% pemerintah pusat + 0,14% from JKK + 0,10% from JKM. |

### PPh 21 — TER (PMK 168/2023, effective 1 Jan 2024)
TER tabel monthly, three PTKP categories:
- **A:** TK/0, TK/1, K/0 — PTKP Rp 54–58,5 jt/yr — 44 brackets, max 34%
- **B:** TK/2, TK/3, K/1, K/2 — PTKP Rp 63–67,5 jt/yr — 40 brackets, max 34%
- **C:** K/3 — PTKP Rp 72 jt/yr — 41 brackets, max 34%

December (last month) reconciliation uses **Pasal 17 UU PPh** progressive
rates against full-year `Penghasilan Kena Pajak`.

---

## Quick-reference: allowances & deductions

### Common allowances (tunjangan)
- **THR** (Tunjangan Hari Raya) — religious holiday allowance, mandatory.
  ≥ 1× monthly wage if ≥ 12 months service; pro-rata if 1–12 months.
  **Permenaker 6/2016**. Must be paid ≥ 7 days before holiday.
- **Tunjangan transport** — usually fixed monthly, taxable.
- **Tunjangan makan** — usually fixed monthly, taxable.
- **Tunjangan jabatan** — position allowance, fixed.
- **Tunjangan komunikasi** — communication allowance.
- **Tunjangan kehadiran / absensi** — attendance bonus, often forfeited
  on lateness/absence.
- **Bonus** — discretionary; taxable; treated as irregular income for
  TER purposes.
- **Lembur** (overtime) — PP 35/2021 ps. 30–32, hourly basis = 1/173 ×
  monthly wage.

### Statutory deductions (potongan)
- BPJS Kesehatan, JHT, JP (employee portions, see table above)
- PPh 21 (income tax)
- Other voluntary: koperasi, loan repayment (`emp_loans`), late penalty,
  unpaid leave, etc.

---

## Glossary

| Term | Meaning |
|---|---|
| PKWT | Perjanjian Kerja Waktu Tertentu — fixed-term employment contract |
| PKWTT | Perjanjian Kerja Waktu Tidak Tertentu — permanent employment |
| Alih daya | Outsourcing |
| BPJS | Badan Penyelenggara Jaminan Sosial |
| BPJS Kes | BPJS Kesehatan (health insurance) |
| BPJS TK | BPJS Ketenagakerjaan (social security: JHT/JP/JKK/JKM/JKP) |
| JHT | Jaminan Hari Tua — old-age savings |
| JP | Jaminan Pensiun — pension |
| JKK | Jaminan Kecelakaan Kerja — work-accident insurance |
| JKM | Jaminan Kematian — death benefit |
| JKP | Jaminan Kehilangan Pekerjaan — unemployment benefit |
| PPh 21 | Pajak Penghasilan Pasal 21 — withholding income tax on employment income |
| TER | Tarif Efektif Rata-rata — effective average rate (PPh21 monthly calc method) |
| PTKP | Penghasilan Tidak Kena Pajak — non-taxable income threshold |
| NPWP | Nomor Pokok Wajib Pajak — taxpayer ID |
| NIK | Nomor Induk Kependudukan — citizen ID; now also functions as NPWP |
| THR | Tunjangan Hari Raya — religious holiday allowance |
| Pesangon | Severance pay |
| Uang penghargaan | Long-service award |
| UMP / UMK | Upah Minimum Provinsi / Kabupaten — provincial / regency minimum wage |

---

## Repository pointers

- HRIS conceptual blueprint (in product-discovery sibling repo):
  `../../../../HRIS/product-discovery/output/reference/hris-system-blueprint.md`
- Database schema (with `cfg_bpjs` / `cfg_tax_rules`) — same path:
  `../../../../HRIS/product-discovery/output/reference/hris-saas-database-structure.md`
- Sub-agent for deeper research (browsing + multi-source synthesis):
  [`.claude/agents/hr-researcher-indonesia.md`](../../../.claude/agents/hr-researcher-indonesia.md)

---

## Operating rules

1. **Cite or label.** Every Indonesian-HR claim is either citation-backed
   or labeled `[unverified — needs research]`.
2. **Don't hard-code monetary numbers.** Use `cfg_bpjs` / `cfg_tax_rules`
   tables.
3. **Prefer the latest implementing regulation.** Cite UU 6/2023 (not UU
   11/2020) as the current Cipta Kerja umbrella; cite PMK 168/2023 (not
   PMK 252/2008) for PPh21.
4. **Distinguish statute vs company policy.** Many "rules" (e.g.
   probation length, allowance amounts) are policy, not law — say so.
5. **When the regulation is post-cutoff or you're unsure**, dispatch the
   sub-agent or ask the user, don't invent.
