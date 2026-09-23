# HRIS SaaS — Data Flow Diagrams (DFD)

This document provides the complete Data Flow Architecture for the Indonesian HRIS SaaS application. It models data flows across **Level 0 (Context Diagram)**, **Level 1 (Core Subsystems & Data Stores)**, and **Level 2 (Deep-dive Operational Workflows)**.

---

## Quick Import Guides

### 1. Miro (`miro.com`)
1. Open your board in Miro.
2. In the left toolbar or command palette, search for the **Mermaid** app (or click **More tools (`+`) → Mermaid**).
3. Copy any of the Mermaid code blocks below and paste it into the Mermaid code editor.
4. Click **Generate** to render editable visual cards and arrows directly on your board.

### 2. Draw.io (`app.diagrams.net`)
- **Option A (Direct File Open)**: Open `docs/architecture/hris-data-flow-diagram.drawio` directly in [app.diagrams.net](https://app.diagrams.net/) via **File → Open From → Device**.
- **Option B (Mermaid Import)**: In Draw.io, go to **Arrange → Insert → Advanced → Mermaid**, paste the code block, and click **Insert**.

---

## 1. Level 0: System Context Diagram

The Context Diagram defines the system boundary, external entities interacting with the HRIS, and high-level input/output data flows.

```mermaid
flowchart TD
    %% External Entities
    EMP["👤 Employee / User"]
    HR["👔 HR Administrator / HR Manager"]
    MGR["👥 Department Manager / Supervisor"]
    CAND["💼 Job Candidate / Applicant"]
    VEND["🏢 Outsourcing Vendor / Supplier"]
    GOV["🏛️ Statutory & Tax Authorities<br/>(DJP PPh 21 / BPJS)"]
    SADMIN["⚙️ SaaS / Super Admin"]

    %% Central Process
    HRIS(("🏢 Indonesian HRIS<br/>SaaS Platform<br/>(Laravel + MySQL)"))

    %% Employee Flows
    EMP -->|"Self Check-in/out, GPS coordinates"| HRIS
    EMP -->|"Leave requests & Overtime claims"| HRIS
    EMP -->|"Personal profile updates & Document uploads"| HRIS
    HRIS -->|"Payslips, Shift roster, Leave balance"| EMP
    HRIS -->|"Attendance confirmation & Task notifications"| EMP

    %% HR Admin Flows
    HR -->|"Employee master records & Contracts (PKWT/PKWTT)"| HRIS
    HR -->|"Shift schedules, Holiday calendars & Work policies"| HRIS
    HR -->|"Salary components, Allowance & Deduction assignments"| HRIS
    HR -->|"Payroll run trigger & Final approval"| HRIS
    HRIS -->|"Payroll summary, Tax reports, Headcount analytics"| HRIS
    HRIS -->|"Compliance alerts & Contract expiry reminders"| HR

    %% Department Manager Flows
    MGR -->|"Leave & Overtime approvals"| HRIS
    MGR -->|"Performance reviews & 9-Box talent ratings"| HRIS
    MGR -->|"Shift roster adjustments & Training nominations"| HRIS
    HRIS -->|"Team attendance dashboard & Pending approval queue"| MGR

    %% Candidate Flows
    CAND -->|"Job applications, Resume & Profile details"| HRIS
    HRIS -->|"Application status & Interview invitations"| CAND

    %% Vendor Flows
    VEND -->|"Outsourced worker profiles & Assignment rosters"| HRIS
    VEND -->|"Vendor billing invoices & Proof of compliance"| HRIS
    HRIS -->|"Placement tracking, Timesheet summaries & Payment status"| VEND

    %% Government & Regulatory Flows
    HRIS -->|"PPh 21 TER monthly withholding reports (PMK 168/2023)"| GOV
    HRIS -->|"BPJS Kesehatan & Ketenagakerjaan wage contribution reports"| GOV
    HRIS -->|"PKWT contract registration records (PP 35/2021)"| GOV

    %% SaaS Admin Flows
    SADMIN -->|"Tenant setup, Plan provisioning & Feature permissions"| HRIS
    HRIS -->|"Tenant usage metrics, Subscription invoices & Error logs"| SADMIN

    classDef entity fill:#1e293b,stroke:#0284c7,stroke-width:2px,color:#f8fafc;
    classDef system fill:#0369a1,stroke:#38bdf8,stroke-width:3px,color:#ffffff,font-weight:bold;
    class EMP,HR,MGR,CAND,VEND,GOV,SADMIN entity;
    class HRIS system;
```

---

## 2. Level 1: Core Subsystems & Data Stores DFD

The Level 1 DFD decomposes the HRIS into 10 key operational processes and maps their bidirectional interactions with the 9 database table clusters.

```mermaid
flowchart TD
    %% External Entities
    subgraph ENTITIES ["External Entities"]
        E_EMP["👤 Employee"]
        E_HR["👔 HR Admin"]
        E_MGR["👥 Dept Manager"]
        E_CAND["💼 Candidate"]
        E_VEND["🏢 Outsourcing Vendor"]
        E_SAAS["⚙️ SaaS Admin"]
    end

    %% Subsystems / Processes
    subgraph PROCESSES ["Core HRIS Subsystems (Processes)"]
        P1["1.0<br/>Auth, RBAC &<br/>Tenant Control"]
        P2["2.0<br/>Organization &<br/>Structure Setup"]
        P3["3.0<br/>Employee Master &<br/>Lifecycle Mgmt"]
        P4["4.0<br/>Time, Attendance &<br/>Overtime Engine"]
        P5["5.0<br/>Leave Entitlement &<br/>Approval Workflow"]
        P6["6.0<br/>Indonesian Payroll &<br/>Statutory Engine"]
        P7["7.0<br/>Recruitment &<br/>Applicant Tracking (ATS)"]
        P8["8.0<br/>Talent, Performance &<br/>Succession Mgmt"]
        P9["9.0<br/>Outsourcing, Vendor &<br/>Compliance Mgmt"]
        P10["10.0<br/>SaaS Subscriptions &<br/>Billing Admin"]
    end

    %% Data Stores
    subgraph STORES ["Data Stores (MySQL Database)"]
        D1[("[(D1) sys_tenants, users, roles, permissions]")]
        D2[("[(D2) org_companies, sites, departments, positions]")]
        D3[("[(D3) emp_employees, identities, jobs, contracts, allowances, loans, tax_profiles]")]
        D4[("[(D4) att_shifts, rel_employee_shifts, att_attendances, ot_overtimes, cfg_schedules]")]
        D5[("[(D5) lv_leaves, lv_leave_types]")]
        D6[("[(D6) cfg_salary_components, cfg_bpjs, cfg_tax_rules, pay_payrolls, pay_payroll_items]")]
        D7[("[(D7) trx_jobs, trx_candidates, trx_applications, trx_interviews]")]
        D8[("[(D8) perf_reviews, nine_box_assessments, talent_pools, succession_plans, trainings]")]
        D9[("[(D9) rel_vendor_employees, outsourcing_compliance_records, bill_vendor_invoices]")]
    end

    %% Entity Interactions with Processes
    E_SAAS <-->|"Tenant provision & Plan config"| P10
    P10 <--> D1
    E_SAAS <-->|"User credentials & Role bindings"| P1
    P1 <--> D1

    E_HR -->|"Company, site & dept setup"| P2
    P2 <--> D2

    E_HR -->|"Employee profiles, PKWT/PKWTT contracts"| P3
    E_EMP -->|"Personal identity, family & bank updates"| P3
    P3 <--> D3
    P3 -.->|"Ref: Org metadata"| D2

    E_EMP -->|"Clock in/out, overtime requests"| P4
    E_MGR -->|"Approve overtime"| P4
    P4 <--> D4
    P4 -.->|"Ref: Employee shift & site"| D3

    E_EMP -->|"Submit leave request"| P5
    E_MGR -->|"Approve / Reject leave"| P5
    P5 <--> D5
    P5 -.->|"Verify balance against employee"| D3

    %% Cross-process to Payroll
    D3 -->|"Base salary, fixed allowances, deductions, loans"| P6
    D4 -->|"Payable attendance days, overtime hours"| P6
    D5 -->|"Unpaid leave deductions"| P6
    D6 -->|"BPJS rates, TER tax tables PMK 168/2023"| P6
    P6 -->|"Calculated payroll runs & payslip line items"| D6
    E_HR -->|"Execute payroll run & lock batch"| P6
    P6 -->|"Generate payslips"| E_EMP

    %% Recruitment ATS
    E_HR -->|"Create job vacancy"| P7
    E_CAND -->|"Submit application & resume"| P7
    E_MGR -->|"Submit interview scorecards"| P7
    P7 <--> D7
    P7 -->|"Onboard hired candidate → Create master profile"| P3

    %% Talent Management
    E_MGR -->|"Performance ratings & 9-Box grid evaluation"| P8
    E_HR -->|"Succession planning & training assignments"| P8
    P8 <--> D8
    P8 -.->|"Ref: Employee performance history"| D3

    %% Outsourcing & Vendor Placements
    E_VEND -->|"Submit outsourced worker roster & Invoices"| P9
    E_HR -->|"Audit labor law compliance & verify billing"| P9
    P9 <--> D9
    P9 -.->|"Cross-reference site placement"| D2

    classDef proc fill:#0284c7,stroke:#0369a1,stroke-width:2px,color:#ffffff,font-weight:bold;
    classDef store fill:#0f766e,stroke:#14b8a6,stroke-width:2px,color:#ffffff;
    classDef ent fill:#334155,stroke:#94a3b8,stroke-width:2px,color:#f8fafc;

    class P1,P2,P3,P4,P5,P6,P7,P8,P9,P10 proc;
    class D1,D2,D3,D4,D5,D6,D7,D8,D9 store;
    class E_EMP,E_HR,E_MGR,E_CAND,E_VEND,E_SAAS ent;
```

---

## 3. Level 2: Detailed Workflow DFDs

### 3.1 DFD 2.1 — Indonesian Payroll & Statutory Calculation Engine
*Complies with Indonesian Labor Law (UU Cipta Kerja), Tax Regulation PMK 168/2023 (PPh 21 TER), and BPJS regulations.*

```mermaid
flowchart TD
    %% External Triggers
    HR_TRIGGER["👔 HR Admin (Trigger Payroll Period)"]
    
    %% Input Data Assembly
    subgraph STEP1 ["1. Input Data Collection"]
        P_EMP_DATA["Fetch Employee Profile & Tax Status<br/>(PTKP: TK/0 - K/3, NPWP Status)"]
        P_BASE_PAY["Fetch Base Salary & Contract Info<br/>(PKWT / PKWTT)"]
        P_ALLOWANCE["Aggregate Recurring Allowances<br/>(Fixed: Position, Functional<br/>Variable: Transport, Meal)"]
        P_DEDUCTIONS["Aggregate Employee Deductions & Active Loan Installments"]
        P_ATTENDANCE["Aggregate Approved Overtime Hours<br/>& Deduct Unpaid Absences / Leaves"]
    end

    %% Gross Calculation
    subgraph STEP2 ["2. Gross Pay Calculation"]
        P_GROSS_CALC["Calculate Total Gross Salary =<br/>Base Salary + Allowances + Overtime Pay"]
    end

    %% BPJS Calculation
    subgraph STEP3 ["3. Indonesian BPJS Statutory Engine"]
        BPJS_KES["BPJS Kesehatan (4% Employer, 1% Employee)<br/>*Max Cap: Rp 12,000,000*"]
        BPJS_TK_JHT["BPJS Ketenagakerjaan JHT<br/>(3.7% Employer, 2% Employee)"]
        BPJS_TK_JP["BPJS Ketenagakerjaan JP<br/>(2% Employer, 1% Employee)<br/>*Max Cap Indexed Annually*"]
        BPJS_TK_JKK["BPJS Ketenagakerjaan JKK<br/>(0.24% - 1.74% Employer Risk Group)"]
        BPJS_TK_JKM["BPJS Ketenagakerjaan JKM (0.3% Employer)"]
        BPJS_TK_JKP["BPJS Ketenagakerjaan JKP (Funded by Gov/APBN)"]
    end

    %% Tax Calculation
    subgraph STEP4 ["4. PPh 21 TER Calculation (PMK 168/2023)"]
        TAX_MATCH["Determine TER Category (A, B, or C)<br/>based on PTKP Status"]
        TAX_BRACKET["Lookup Effective Monthly Rate (%)<br/>from cfg_tax_rules table based on Gross Taxable Income"]
        TAX_WITHHOLD["Compute PPh 21 Withholding =<br/>Gross Taxable Income × TER Rate"]
    end

    %% Net Calculation & Storage
    subgraph STEP5 ["5. Net Pay & Finalization"]
        P_NET["Compute Take-Home Pay (THP) =<br/>Gross + Employer BPJS - (Employee BPJS + PPh21 + Loan Installments + Deductions)"]
        STORE_PAYROLL[("Save to pay_payrolls & pay_payroll_items")]
        GEN_PAYSLIP["Generate Confidential PDF / Digital Payslip"]
        NOTIFY_EMP["Notify Employee & Send Bank Transfer Batch"]
    end

    HR_TRIGGER --> P_EMP_DATA
    HR_TRIGGER --> P_BASE_PAY
    HR_TRIGGER --> P_ALLOWANCE
    HR_TRIGGER --> P_DEDUCTIONS
    HR_TRIGGER --> P_ATTENDANCE

    P_EMP_DATA & P_BASE_PAY & P_ALLOWANCE & P_ATTENDANCE --> P_GROSS_CALC

    P_GROSS_CALC --> BPJS_KES & BPJS_TK_JHT & BPJS_TK_JP & BPJS_TK_JKK & BPJS_TK_JKM & BPJS_TK_JKP

    P_GROSS_CALC & BPJS_KES & BPJS_TK_JKK & BPJS_TK_JKM --> TAX_MATCH
    TAX_MATCH --> TAX_BRACKET --> TAX_WITHHOLD

    P_GROSS_CALC & BPJS_KES & BPJS_TK_JHT & BPJS_TK_JP & TAX_WITHHOLD & P_DEDUCTIONS --> P_NET
    P_NET --> STORE_PAYROLL --> GEN_PAYSLIP --> NOTIFY_EMP

    classDef step fill:#0f172a,stroke:#38bdf8,stroke-width:1px,color:#f8fafc;
    classDef calc fill:#0369a1,stroke:#0284c7,stroke-width:2px,color:#ffffff;
    classDef result fill:#047857,stroke:#10b981,stroke-width:2px,color:#ffffff,font-weight:bold;
    class P_EMP_DATA,P_BASE_PAY,P_ALLOWANCE,P_DEDUCTIONS,P_ATTENDANCE step;
    class P_GROSS_CALC,BPJS_KES,BPJS_TK_JHT,BPJS_TK_JP,BPJS_TK_JKK,BPJS_TK_JKM,BPJS_TK_JKP,TAX_MATCH,TAX_BRACKET,TAX_WITHHOLD,P_NET calc;
    class STORE_PAYROLL,GEN_PAYSLIP,NOTIFY_EMP result;
```

---

### 3.2 DFD 2.2 — Recruitment Pipeline to Employee Onboarding Flow

```mermaid
flowchart TD
    %% Process Stages
    subgraph STAGE1 ["1. Vacancy & Sourcing"]
        HR_JOB["HR creates Job Posting in trx_jobs"]
        CAND_APPLY["Candidate submits profile & CV into trx_candidates"]
        CREATE_APP["System creates record in trx_applications (Stage: Applied)"]
    end

    subgraph STAGE2 ["2. Screening & Interviews"]
        SCREEN["HR Screens Profile → Stage: Screening"]
        INTERVIEW_SCHED["Schedule Interview in trx_interviews"]
        INTERVIEW_EVAL["Interviewers record evaluation & score"]
        STAGE_OFFER["Progress to Offering / Background Check"]
    end

    subgraph STAGE3 ["3. One-Click Hiring & Master Provisioning"]
        HIRE_ACTION["HR clicks 'Hire Candidate' in PipelineController"]
        CREATE_EMP["Create Master Record in emp_employees"]
        CREATE_IDENTITY["Create Identity Record in emp_identities (KTP, NPWP)"]
        CREATE_JOB["Create Job History in emp_jobs (Dept, Position, Grade)"]
        CREATE_CONTRACT["Create Contract in emp_contracts (PKWT/PKWTT)"]
        CREATE_SITE["Assign Site Placement in rel_employee_sites"]
        CREATE_USER["Optionally link User account in users"]
    end

    HR_JOB --> CAND_APPLY --> CREATE_APP --> SCREEN --> INTERVIEW_SCHED --> INTERVIEW_EVAL --> STAGE_OFFER --> HIRE_ACTION
    HIRE_ACTION --> CREATE_EMP
    CREATE_EMP --> CREATE_IDENTITY & CREATE_JOB & CREATE_CONTRACT & CREATE_SITE & CREATE_USER

    classDef stage fill:#0f172a,stroke:#64748b,stroke-width:1px,color:#f8fafc;
    classDef action fill:#0284c7,stroke:#0369a1,stroke-width:2px,color:#ffffff;
    classDef target fill:#15803d,stroke:#22c55e,stroke-width:2px,color:#ffffff;
    class HR_JOB,CAND_APPLY,CREATE_APP,SCREEN,INTERVIEW_SCHED,INTERVIEW_EVAL,STAGE_OFFER stage;
    class HIRE_ACTION action;
    class CREATE_EMP,CREATE_IDENTITY,CREATE_JOB,CREATE_CONTRACT,CREATE_SITE,CREATE_USER target;
```

---

### 3.3 DFD 2.3 — Attendance, Shift Roster & Leave Processing Flow

```mermaid
flowchart TD
    %% Entities
    EMP["👤 Employee"]
    MGR["👥 Manager"]
    CRON["⏰ Automated Nightly Engine"]

    %% Shift Roster
    subgraph ROSTER ["1. Shift Scheduling"]
        ASSIGN_SHIFT["Assign Shifts in rel_employee_shifts"]
        WORK_SCHED["Define Work Schedules & Holiday Exclusions"]
    end

    %% Daily Attendance
    subgraph DAILY_ATT ["2. Daily Check-in/out Engine"]
        CLOCK_IN["Clock In (GPS Lat/Lng, Site Geofence validation)"]
        CLOCK_OUT["Clock Out (Calculates Work Duration & Late Arrival)"]
        SAVE_ATT[("att_attendances")]
    end

    %% Overtime Workflow
    subgraph OT_FLOW ["3. Overtime Workflow (PP 35/2021)"]
        SUBMIT_OT["Submit Overtime Request (ot_overtimes)"]
        MGR_DECIDE_OT{"Manager Approval?"}
        OT_APPROVED["Approved OT hours queued for Payroll"]
        OT_REJECTED["Request Rejected"]
    end

    %% Leave Workflow
    subgraph LEAVE_FLOW ["4. Leave Entitlement & Balance Engine"]
        SUBMIT_LV["Submit Leave Request (lv_leaves)"]
        CHECK_BAL{"Sufficient Balance in lv_leave_types?"}
        MGR_DECIDE_LV{"Manager Approval?"}
        LV_APPROVED["Deduct Balance & Mark Attendance as On Leave"]
        LV_REJECTED["Request Rejected"]
    end

    %% Monthly Sync
    subgraph SYNC_PAYROLL ["5. Payroll Aggregation Engine"]
        AGGREGATE["Summarize: Payable Days, Late Deductions, Overtime Hours, Unpaid Leaves"]
        FEED_PAYROLL[("Feed to Payroll Engine (pay_payrolls)")]
    end

    WORK_SCHED & ASSIGN_SHIFT --> CLOCK_IN
    EMP --> CLOCK_IN --> CLOCK_OUT --> SAVE_ATT
    EMP --> SUBMIT_OT --> MGR_DECIDE_OT
    MGR --> MGR_DECIDE_OT
    MGR_DECIDE_OT -->|Yes| OT_APPROVED
    MGR_DECIDE_OT -->|No| OT_REJECTED

    EMP --> SUBMIT_LV --> CHECK_BAL
    CHECK_BAL -->|Yes| MGR_DECIDE_LV
    MGR --> MGR_DECIDE_LV
    MGR_DECIDE_LV -->|Yes| LV_APPROVED
    MGR_DECIDE_LV -->|No| LV_REJECTED

    SAVE_ATT & OT_APPROVED & LV_APPROVED --> CRON --> AGGREGATE --> FEED_PAYROLL

    classDef ent fill:#334155,stroke:#94a3b8,stroke-width:2px,color:#f8fafc;
    classDef step fill:#0369a1,stroke:#0284c7,stroke-width:1px,color:#ffffff;
    classDef decision fill:#854d0e,stroke:#eab308,stroke-width:2px,color:#ffffff;
    classDef done fill:#15803d,stroke:#22c55e,stroke-width:2px,color:#ffffff;

    class EMP,MGR,CRON ent;
    class ASSIGN_SHIFT,WORK_SCHED,CLOCK_IN,CLOCK_OUT,SAVE_ATT,SUBMIT_OT,OT_APPROVED,OT_REJECTED,SUBMIT_LV,LV_APPROVED,LV_REJECTED,AGGREGATE,FEED_PAYROLL step;
    class MGR_DECIDE_OT,CHECK_BAL,MGR_DECIDE_LV decision;
```

---

## 4. Entity-Process-Data Store Mapping Matrix

| Process # | Process Name | Primary Entities | Key Input Data | Read Stores | Write Stores | Key Output Data |
|---|---|---|---|---|---|---|
| **1.0** | Auth & RBAC | SaaS Admin, All Users | Login credentials, Role permissions | `D1 (sys_)` | `D1 (sys_)` | JWT / Session Token, Nav permissions |
| **2.0** | Org Setup | HR Admin | Companies, Sites, Depts, Positions | `D2 (org_)` | `D2 (org_)` | Org Chart, Master structural metadata |
| **3.0** | Employee Lifecycle | HR Admin, Employee | Personal info, KTP/NPWP, Family, Bank, PKWT/PKWTT contracts | `D2, D3` | `D3 (emp_)` | Master profile, Document archive, Reminders |
| **4.0** | Attendance & Overtime | Employee, Manager | Clock-in/out, GPS coordinates, Overtime forms | `D3, D4` | `D4 (att_, ot_)` | Timesheets, Overtime pay hours, Tardiness |
| **5.0** | Leave Management | Employee, Manager | Leave dates, Medical certificates, Approvals | `D3, D5` | `D5 (lv_)` | Leave calendar, Remaining balance |
| **6.0** | Payroll Engine | HR Admin, Employee | Pay period trigger, Allowances, Loan cuts, Overtime | `D3, D4, D5, D6`| `D6 (pay_)` | PPh 21 TER, BPJS breakdown, Net Payslips |
| **7.0** | Recruitment (ATS) | Candidate, HR, Interviewer| Job postings, Resumes, Interview scorecards | `D2, D7` | `D7 (trx_), D3`| Hire action, Onboarded employee record |
| **8.0** | Talent & Succession | Dept Manager, HR Admin | Performance review criteria, 9-Box grid matrix | `D3, D8` | `D8 (talent_)`| High-potential pool, Succession chart |
| **9.0** | Outsourcing & Vendor | Vendor, HR Admin | Vendor placements, Billing invoices, Compliance logs | `D2, D9` | `D9 (vendor_)`| Placement tracking, Verified invoices |
| **10.0** | SaaS Administration | SaaS Admin | Subscription plans, Tenant billing, Bug tickets | `D1, D10` | `D1, D10` | Tenant access status, Payment receipts |

---

## 5. File Artifacts Generated

1. **`docs/architecture/data-flow-diagram.md`** *(this file)*: Markdown documentation with Mermaid syntax for Miro and direct rendering.
2. **`docs/architecture/hris-data-flow-diagram.drawio`**: Multi-page Draw.io diagram file compatible with [app.diagrams.net](https://app.diagrams.net/).
