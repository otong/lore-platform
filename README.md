# LORE

LORE is an enterprise-grade platform for Learning, Operation, Regulation, and Enterprise applications. This repository is structured to support long-term growth, strong governance, and a disciplined engineering workflow.

## Project Overview

LORE is designed to serve regulated and enterprise environments with a modular architecture that separates backend services, frontend applications, documentation, automation, and tooling.

## Architecture Overview

The Laravel 12 backend is designed to evolve as a Modular Monolith. Each business domain owns its application use cases, domain rules, infrastructure adapters, and presentation adapters. Cross-module database access is strictly prohibited; modules communicate via Services, Contracts, Events, or internal APIs. The architecture also establishes foundational AI Readiness (AI Gateway, Prompt Layer, RAG Knowledge Layer, Tool Calling, and LLM Independence). This Sprint 002 baseline establishes architecture documentation only and introduces no executable runtime code.

Architecture references:

- [Backend architecture](docs/architecture/backend-architecture.md)
- [Module structure](docs/architecture/module-structure.md)
- [API guidelines](docs/architecture/api-guidelines.md)
- [Coding conventions](docs/architecture/coding-conventions.md)

## Vision

To build a reliable, extensible enterprise platform that enables organizations to manage learning, operations, and regulatory workflows while supporting future expansion into analytics, automation, and distributed operations.

## Goals

- Establish a robust repository structure for long-term development
- Support multiple delivery teams with clear boundaries between backend, frontend, and documentation
- Enable consistent onboarding through documentation and working standards
- Maintain flexibility for future technology choices while keeping initial setup lightweight

## Repository Structure

```
LORE/
├── backend/          # Backend service and API code
├── frontend/         # Frontend application code
├── docs/             # Project documentation and governance artifacts
│   ├── architecture/ # Architecture decision and system design
│   ├── api/          # API design and documentation
│   ├── adr/          # Architecture Decision Records
│   ├── meeting/      # Meeting notes and action items
│   ├── sprint/       # Sprint planning, backlog, and reviews
│   └── user-guide/   # End-user and operational guides
├── docker/           # Docker and container orchestration configuration
├── scripts/          # Automation and utility scripts
├── tools/            # Developer tooling and helpers
└── .github/          # GitHub workflows, issue templates, and community files
```

## Technology Stack

- Backend: Laravel 12 (PHP 8.2+)
- Frontend: TBD (Vue.js / React / Svelte)
- Containerization: Docker
- CI/CD: GitHub Actions
- Documentation: Markdown with structured docs folders

## Development Workflow

1. Clone the repository.
2. Create feature branches from `main`.
3. Work in `backend/` or `frontend/` as appropriate.
4. Add documentation to `docs/` for architecture, API, ADR, and user guides.
5. Commit with clear, conventional messages.
6. Open a pull request and follow review and approval processes.

## Branch Strategy

- `main` — production-ready baseline
- `develop` — integration and ongoing development
- `feature/*` — new feature work
- `hotfix/*` — urgent production fixes
- `release/*` — release preparation and stabilization

## Coding Standards

- Use consistent indentation and formatting across all files.
- Keep documentation up to date alongside code changes.
- Write clear commit messages following a conventional style.
- Separate infrastructure, automation, and application code into dedicated folders.
- Track architectural decisions in `docs/adr/`.
- Follow the module boundaries and layering conventions in `docs/architecture/` when business implementation begins.

## Future Roadmap

- Add initial backend scaffold in `backend/`
- Add frontend application scaffold in `frontend/`
- Add CI/CD workflows in `.github/workflows/`
- Add Docker compose and deployment templates in `docker/`
- Develop governance documentation in `docs/`
- Establish security, compliance, and test automation practices

## Getting Started

```bash
git clone https://github.com/otong/lore-platform.git
cd Lore
```

## Backend Setup

1. Install PHP 8.2 or newer and Composer.
2. Change into the backend directory:
   ```bash
   cd backend
   ```
3. Install PHP dependencies:
   ```bash
   composer install
   ```
4. Copy the example environment file if needed:
   ```bash
   cp .env.example .env
   ```
5. Start the Laravel development server:
   ```bash
   php artisan serve --host=127.0.0.1 --port=8000
   ```
6. Open the app in your browser at `http://127.0.0.1:8000`.

> Note: Do not configure PostgreSQL or Redis for this initial bootstrap. The bootstrap only includes the default Laravel development server.

## License

This project is licensed under the MIT License. See [LICENSE](LICENSE) for details.
