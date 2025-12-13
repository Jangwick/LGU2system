# LRA Implementation Plan — LLRM System

This document details a practical, phased implementation plan to integrate the new Legislative Research & Analysis (LRA) features into the existing LLRM system.

Purpose
- Provide a step-by-step plan to add 7 LRA features into LLRM without disrupting the current system.
- Specify modules to modify/create, database changes, APIs, services, background jobs, UI updates, and testing/ops needs.

Assumptions
- LLRM is running on PHP 8 with PDO and a standard MVC layout.
- Existing components: document management, audit, dashboard, search, notifications, user management.
- Team can schedule background jobs via cron/supervisor and can optionally run microservices for heavy NLP tasks.

# Overview & Phases

Phases
- Phase 0 — Prep & Discovery (1 week)
  - Confirm data sources for votes and external imports
  - Confirm which LRA features are top priority from stakeholders
  - Repo scaffolding: create `modules/lra` placeholder
- Phase 1 — MVP Basic Analytics & Similarity (2–3 weeks)
  - Implement `similarity` (TF-IDF) endpoint and dashboard widget
  - Add `council_votes` and `export_logs` migration and APIs for scheduled exports
  - Minimal `trend-analysis` counting (tag-based) endpoints
- Phase 2 — Voting & Trend Analysis (4–6 weeks)
  - Voting similarity cluster calculation and timeline views
  - Topic extraction (TF-IDF), `lra_topics` table, trend charts
  - Advanced search filters integrated into `modules/search`
- Phase 3 — Executive Summary + Export Portal + Scale (4–8 weeks)
  - Executive summary generation (rule-based → optionally microservice LLM)
  - Scheduled exports with `export_logs`, notification integration
  - Vector embeddings, batch processes and potential external microservice for heavy computation

# Milestones & Deliverables
- M1 — `modules/lra` sandbox & migrations
- M2 — TF-IDF similarity API + Document UI button
- M3 — Trend API + dashboard widget
- M4 — Voting analysis + voter similarity UI
- M5 — Executive summary endpoint + export portal
- M6 — Embeddings support, vector DB integration, advanced analytics

# Core Implementation Tasks (detailed)

## Phase 0 — Prep & Repo Scaffolding
Deliverables
- `modules/lra` created with boilerplate files:
  - `controllers/` (controller stubs)
  - `services/` (service stubs)
  - `models/` (models if needed)
  - `api/` (api endpoints stubs)
  - `views/` (initial dashboard widget)
- Add `docus/LRA_IMPLEMENTATION_PLAN.md` (this file)

Acceptance
- New folder created, basic README + stubs validated in dev environment

## Phase 1 — MVP (Similarity & Trends)
Tasks
1. Add DB migrations (SQL)
   - `council_votes` (if data source exists, else implement import endpoint)
   - `export_logs` for export job tracking
2. Implement TF-IDF similarity endpoint (pure PHP in `modules/lra/api/similarity.php`)
   - Service: `modules/lra/services/SimilarityService.php` with TF-IDF vectorizer and cosine similarity
   - API that returns a top-N ranked list
   - UI: Add "Find Similar" to `modules/document-management/views/document_details.php`
3. Implement trend counts endpoint
   - Service: `modules/lra/services/TrendAnalysisService.php` that returns tag counts per period
   - API: `modules/lra/api/trend-analysis.php`
4. Dashboard widget
   - Add small charts and top tags to `modules/dashboard/views/index.php` to visualize results

Acceptance
- Document detail page shows "Find Similar" and returns a list of results
- Dashboard shows top tags and monthly upload counts
- Migrations applied without schema errors

## Phase 2 — Voting & Advanced Trend Analysis
Tasks
1. Voting data capture & analysis
   - Add migration for `council_votes`
   - Provide `modules/lra/api/vote-import.php` endpoint to import historical vote data
   - Service `VotingAnalysisService.php` to compute similarity and clusters
2. Voting UI & charts
   - Add `modules/reports-analytics/views/voting-patterns.php`
   - Add ballots per councilor timeline and cluster view
3. Topic extraction & trend detection
   - Batch job to extract topics from `documents` (TF-IDF / LDA)
   - Add result persistence table `lra_topics` to store topic labels per timeframe
4. Advanced search filters
   - Extend `modules/search/services/SearchService.php` and `SearchController` to accept `topic`, `councilor_id`, `similarity_score` filters

Acceptance
- Voting similarity and cluster results available via API and UI
- Trend widgets updated with topic time-series
- Search supports multiple filters for LRA-related fields

## Phase 3 — Executive Summaries, Export Portal & Scale
Tasks
1. Executive summary generation
   - Service: `modules/lra/services/ExecutiveSummaryService.php` to create concise summaries; initial rule-based with option to plug in LLM microservice
   - API: `modules/lra/api/executive-summary.php` to return summary text
2. Export portal and scheduler
   - UI: `modules/reports-analytics/views/export-portal.php`
   - API: `modules/lra/api/export.php` + `export_logs` and scheduler
3. Batch & vectorization improvements
   - Embeddings & vector DB (NG optional) for production similarity
   - Background worker process to generate vectors and rebuild analyses

Acceptance
- Summaries can be generated for document sets (1–3 paragraphs)
- Exports scheduled and completed with notifications and logs
- Vector DB integration enabling faster similarity queries

# Database Migration Examples (initial)
- `council_votes` minimal SQL:
```sql
CREATE TABLE `council_votes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `document_id` INT NOT NULL,
  `councilor_id` INT NOT NULL,
  `vote` ENUM('yes', 'no', 'abstain'),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX(document_id),
  INDEX(councilor_id)
);
```
- `export_logs` minimal SQL:
```sql
CREATE TABLE `export_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `export_type` VARCHAR(50),
  `parameters` TEXT,
  `status` ENUM('pending','processing','complete','failed') DEFAULT 'pending',
  `file_path` VARCHAR(255),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```
- `lra_topics` minimal SQL:
```sql
CREATE TABLE `lra_topics` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `topic_hash` VARCHAR(64),
  `topic_label` VARCHAR(255),
  `document_count` INT, 
  `period` DATE,
  INDEX(topic_hash),
  INDEX(period)
);
```

# API Surface (examples)
- `GET /modules/lra/api/similarity.php` — returns top similar docs
- `GET /modules/lra/api/trend-analysis.php` — returns trend counts for topics
- `GET /modules/lra/api/voting-analysis.php` — voting pattern analysis
- `POST /modules/lra/api/executive-summary.php` — generates summary from doc IDs
- `POST /modules/lra/api/export.php` — requests dataset exports

# Services & Background Jobs
- Services in `modules/lra/services/` handle core logic
- Batch jobs (cron) to generate vectors, topic extractions, and cluster recompute
- Use a temporary `lra_analysis_runs` table to store job status and parameters

# Permissions & Security
- Add `lra.view` and higher role permissions
- Protect endpoints with `PermissionMiddleware` checks
- Audit LRA operations using `Logger::log()` to `activity_logs`

# Testing & Validation
- Unit tests for service logic (`SimilarityService`, `VotingAnalysisService`)
- Integration tests for API endpoints
- User acceptance test (UAT) story for LRA usage (similar doc find, trend chart, voting analysis)

# Deployment & Monitoring
- Add metrics to monitor compute-heavy endpoints (API latency, vector job times)
- Logging & alerts for job failure and export failures
- Use `export_logs` for retry management

# Risk & Mitigation
- Large corpora and long compute times: mitigate with background jobs & caching
- LLM/embedding costs: gated features conditional on configuration; optional microservice
- Sensitive exports: add admin approvals & audit trails

# Timeline & Estimation (Rough)
- Phase 0 — 1 week
- Phase 1 — 2–3 weeks
- Phase 2 — 4–6 weeks
- Phase 3 — 4+ weeks (embeddings & scaling)

---

## Next steps (choose one)
- [ ] I will scaffold `modules/lra` + add basic TF-IDF `similarity` endpoint (quick MVP)
- [ ] I will generate SQL migrations for `council_votes`, `export_logs`, `lra_topics`
- [ ] I will scaffold `VotingAnalysisService` + simple clustering

Please pick the first item you'd like implemented and I’ll start building it.
