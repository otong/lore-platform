# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Initial enterprise repository structure
- Documentation folders and project scaffolding
- Project metadata files and GitHub remote setup
- Laravel 12 backend scaffold created in `backend/`
- Verified PHP/Composer setup and Laravel bootstrap by starting the built-in development server
- Sprint 002 enterprise architecture documentation (`docs/architecture/backend-architecture.md`, `docs/architecture/module-structure.md`, `docs/architecture/api-guidelines.md`, `docs/architecture/coding-conventions.md`)
- Domain Module Dependency Rules (strict unidirection flow, zero direct database access across modules, communication via Services, Contracts, Events, or APIs)
- AI Readiness architectural framework (AI Gateway, Prompt Layer, RAG Knowledge Layer, Tool Calling, and LLM Independence)

### Changed
- Updated `README.md` architecture overview to summarize Modular Monolith dependency rules and AI Readiness baseline

### Deprecated

### Removed

### Fixed

### Security
