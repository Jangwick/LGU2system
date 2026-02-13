# Combined Features — LLRM + LRA

This document combines the current LLRM core features with the Legislative Research & Analysis (LRA) features into a single, consolidated outline for reference, planning, and implementation.

---

## Overview
The combined system supports core legislative records management plus advanced analytics and research features (LRA). The goal is to integrate LRA capabilities as a natural extension of the LLRM platform.

---

Legend: [LLRM] = Core LLRM feature; [LRA] = Legislative Research & Analysis feature; [LLRM + LRA] = applicable to both


## 7 Essential Features (prioritized)

1. Analytics Dashboard — Visual metrics and KPIs [LLRM + LRA]
   - System-wide KPIs, trending metrics, top topics, author stats and charts.

2. Voting Pattern Analysis — Councilor behavior insights [LRA]
   - Capture and analyze voting records to find similarity, alliances and patterns.

3. Legislative Trend Analysis — Topic tracking over time [LRA]
   - Topic extraction (TF-IDF / LDA) and time-series visualizations for topic frequency.

4. Comparative Document Analysis — Find similar documents [LRA]
   - TF-IDF/embedding-based similarity index to locate related or precedent documents.

5. Advanced Search Enhancement — Multi-criteria filters [LLRM + LRA]
   - Enhance search with topic filters, councilor vote filters, and similarity thresholds.

6. Executive Summary Reports — Auto-generated insights [LRA]
   - Rule-based (or LLM-augmented) short summaries for document sets and trends.

7. Data Export Portal — For external analysis [LLRM]
   - Export votes, topics, similarity, and trend datasets; scheduled and ad-hoc exports with export logs.

## Concise 7-Feature Outline (Labeled)

1. Document Storage & Versioning — Core Document Management [LLRM]
   - Upload, download, metadata, previews, versioning, rollback.

2. Advanced Search, Discovery & Comparative Document Analysis — Search + Similarity [LLRM + LRA]
   - Full-text / field search, filters; TF-IDF/embeddings similarity API.

3. Analytics Dashboard — Visual Metrics & LRA KPIs [LLRM + LRA]
   - Core usage metrics and LRA-specific KPIs (topics, voting trends).

4. Voting Pattern Analysis — Councilor Behavior Insights [LRA]
   - Voting records, similarity/clustering, alliance detection.

5. Legislative Trend Analysis & Tagging — Topic Tracking Over Time [LRA]
   - Topic extraction (TF-IDF/LDA), timeseries charts and trends.

6. Executive Summary Reports & Export Portal — Auto Summaries + External Exports [LLRM + LRA]
   - Auto-generated insights, scheduled/ad-hoc exports (CSV/JSON) with logs.

7. Audit, Access Control & Integrations — Security & Notifications [LLRM]
   - RBAC, audit logs, webhooks, API keys and notifications.

---

## Core Platform Features (LLRM)
 - Document Storage & Management [LLRM]
  - Upload, download, file storage, metadata, preview, versioning.
 - Version Control System [LLRM]
  - Document version history and revert/download support.
 - Advanced Search & Retrieval [LLRM]
  - Full-text/field search, tags, filters, UI and API.
 - Document Linking System [LLRM]
  - Cross-document linking, references and management.
 - Tagging & Categorization [LLRM]
  - Tag management and tag-based filtering.
 - Audit Trail & Compliance [LLRM]
  - Logging of user & system events, exportable audit reports.
 - Access Control & Security [LLRM]
  - Authentication and RBAC; middleware enforcement for protected routes.
 - Notifications & External Integration [LLRM]
  - Webhooks, internal notifications and API-key-based integration.
 - Dashboard & Reporting [LLRM]
  - KPI cards, charts, exports and analytics.
 - Export Portal & Data Exports [LLRM]
  - CSV/JSON export portal with logs and notifications.
 - Background Jobs & Batch Processing [LLRM]
  - Scheduled tasks for compute-heavy processes (indexing, vectorization).
 - UI Layout & Assets [LLRM]

> Notes: Items above are primarily LLRM core features; some (e.g., Advanced Search & Retrieval, Dashboard & Reporting, Export Portal) are commonly extended by LRA components such as similarity search and LRA KPIs.
  - Shared header, footer, navbar, assets and frontend components.

---

## LRA (Legislative Research & Analysis) — Key Additions
 - Voting Pattern Analysis: `council_votes` table + voting similarity/clustering services. [LRA]
 - Trend Analysis: `lra_topics` for topic counts and trend aggregation. [LRA]
 - Document Similarity: Precomputed `document_vectors` for TF-IDF/embedding search and a similarity API. [LRA]
 - Executive Summaries: Auto-generated summaries for document sets, optionally LLM-assisted. [LRA]
 - Advanced Search: Category/topic/vote-based filters and similarity search integration. [LLRM + LRA]
 - Data Export Portal: `export_logs`, scheduled exports, notifications on completion. [LLRM]
 

---

## Suggested DB Additions
- `council_votes` — record per-document council votes [LRA]
- `lra_topics` — topic counts per period for trend analysis [LRA]
- `document_vectors` — precomputed vectors or embeddings for similarity [LRA]
- `lra_voting_similarity` — councilor similarity table [LRA]
- `export_logs` — track export jobs and files [LLRM]
- `lra_analysis_runs` — job runs & parameters for batch analytics [LRA]

---

## Implementation Roadmap (MVP → Full)
- Phase 1 (MVP): Basic TF-IDF `similarity` endpoint, initial `trend-analysis` API, dashboard widget, minimal migrations (votes/export_logs).
- Phase 2: Voting analysis clustering, topic extraction batch jobs, advanced search integration, exports.
- Phase 3: Embedding & vector DB support, LLM summaries, predictive models and scaling architecture.

---

## Quick Next Steps (pick one)
- `Scaffold LRA` — create `modules/lra` skeleton (controllers, services, models, api, views).
- `Migrations` — create SQL migrations for `council_votes`, `export_logs`, `lra_topics`.
- `Similarity MVP` — implement TF-IDF `similarity` endpoint and UI hook in document details.

---

If you want, I can start with any of the next steps above — which would you like me to implement next?