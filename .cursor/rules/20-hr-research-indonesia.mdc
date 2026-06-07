---
description: Activate Indonesian HR research discipline whenever the conversation touches Indonesian labor law, payroll tax (PPh21), BPJS, allowances/deductions, contract types (PKWT/PKWTT), or outsourcing/alih daya. Loaded on demand.
---

# Indonesian HR Research — Routing Rule

When the user's request, your plan, or the file you're editing involves
**any** Indonesian-HR domain topic, you must use one of the project's
two HR-research mechanisms.

## Trigger phrases

Activate this rule when you see (Indonesian or English):

- PKWT, PKWTT, kontrak kerja, fixed-term contract, permanent employment
- BPJS, BPJS Kesehatan, BPJS Ketenagakerjaan, JHT, JP, JKK, JKM, JKP
- PPh21, PPh 21, pajak penghasilan, income tax Indonesia, TER, PTKP
- THR, tunjangan, allowance, potongan, deduction, lembur, overtime
- Pesangon, severance, uang penghargaan, uang penggantian hak
- Alih daya, outsourcing, vendor, pegawai vendor
- NPWP, NIK, UMP, UMK, upah minimum
- Cipta Kerja, UU 13/2003, UU 6/2023, PP 35/2021, PMK 168/2023

…or any code/data field referencing the same domain (e.g. `cfg_bpjs`,
`cfg_tax_rules`, `emp_tax_profiles`, `pay_payroll_items`,
`employment_type`, `tax_status`).

## Two mechanisms — pick one

### 1. In-session skill (default for short answers)

Read and follow:

- `.cursor/skills/hr-research-indonesia/SKILL.md`

Use this for inline reasoning — "what's the JHT employee percentage?",
"can a PKWT have a probation period?", "is THR taxable?", etc.

### 2. Sub-agent dispatch (for deeper / multi-source research)

Dispatch the `hr-researcher-indonesia` sub-agent defined at:

- `.claude/agents/hr-researcher-indonesia.md`

Use this when the question needs **browsing / multi-source synthesis /
regulation cross-checking**, e.g. "compare JKP eligibility before and
after PP 37/2021", "build a TER bracket table for category B and verify
against DJP", "what compensation is owed when a 4-year PKWT ends
early?".

## Output discipline (mandatory regardless of mechanism)

Every claim about Indonesian HR rules must include:

1. **Regulation reference** — UU / PP / PMK / Perpres + pasal + ayat
   where relevant.
2. **Effective date.**
3. **Edge case or exception** if any.

If you can't cite, label `[unverified — needs research]` and either
dispatch the sub-agent or ask the user.

## Don't

- Don't invent percentages, thresholds, or salary caps.
- Don't cite UU 11/2020 as current Cipta Kerja — it was replaced by UU
  6/2023 (via Perppu 2/2022).
- Don't cite PMK 252/2008 for current PPh21 calc — replaced by PMK
  168/2023 effective 1 Jan 2024.
- Don't conflate company policy (probation length, attendance bonus) with
  statute.
- Don't hard-code monetary figures in code — use `cfg_bpjs` /
  `cfg_tax_rules` tables.
