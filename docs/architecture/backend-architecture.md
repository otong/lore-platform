# Backend Architecture

## Purpose

This document defines the target backend architecture for the LORE Enterprise Platform. The current Laravel application serves as the foundation scaffold; this document establishes the architectural standards, layering rules, module boundaries, inter-module communication patterns, and AI integration guidelines for all future development.

## Architectural Style

LORE is architected as a **Modular Monolith** on Laravel 12. Business capability is divided into cohesive, independent domain modules deployed within a unified runtime. 

Each domain module owns its business rules, use cases, persistent data models, and technical adapters. This style delivers operational simplicity, strong domain boundaries, high maintainability, and clear pathways to extract standalone microservices if scaling or organizational needs dictate.

## Primary Layers

Each module strictly follows a four-layer Clean / Hexagonal Architecture:

1. **Domain Layer** (`Domain/`)
   - Contains core business entities, value objects, domain events, domain policies, and repository contracts.
   - Pure PHP code with zero dependencies on Laravel, database ORM, HTTP transport, or external SDKs.
2. **Application Layer** (`Application/`)
   - Contains use cases, command handlers, query handlers, DTOs, and application interfaces.
   - Coordinates domain operations and manages application flow without exposing transport or storage specifics.
3. **Infrastructure Layer** (`Infrastructure/`)
   - Contains technical implementations of domain and application contracts.
   - Handles database persistence (Eloquent models/mappers), message queues, external API integration, file storage, and framework service providers.
4. **Presentation Layer** (`Presentation/`)
   - Contains delivery adapters such as HTTP Controllers, API Requests/Resources, Console Commands, and Event Listeners.
   - Translates transport input into application contracts, invokes application use cases, and formats responses.

## Dependency Rules

- **Domain** must not depend on Application, Infrastructure, Presentation, or framework facades.
- **Application** depends only on Domain and abstract contracts; it does not depend on Presentation or concrete Infrastructure implementations.
- **Infrastructure** implements Domain and Application contracts.
- **Presentation** delegates incoming transport requests to Application use cases. It contains no business logic and does not access persistence directly.

## Domain Module Dependency Rules

To maintain strict domain isolation and prevent architectural degradation:

1. **Communication Hierarchy**: Module dependencies follow a strict unidirection downstream flow:
   ```text
   Identity
     ↓
   Organization
     ↓
   Monitoring
     ↓
   Billing / Finance
   ```
2. **Database Isolation**: Modules **must never** access another module's database tables or database connection directly. Foreign keys across module boundaries at the database level are prohibited.
3. **Approved Inter-Module Channels**:
   - **Service Contracts / Interfaces**: Modules expose public PHP interfaces in `Application/Contracts/` for synchronous inter-module queries.
   - **Domain Events**: Modules publish domain events to an asynchronous event bus for decoupled, side-effect processing.
   - **Internal APIs**: Modules may expose versioned internal REST or RPC endpoints for explicit cross-boundary operations.

## Shared Platform Capabilities

Cross-cutting technical concerns reside in `app/Shared/`. Shared capabilities are strictly limited to technical primitives (e.g., base value objects, correlation IDs, standardized response interfaces, system clock, base exception classes). Business domain models must remain inside their owning module and are never placed in `Shared`.

## AI Readiness

The LORE architecture incorporates **AI Readiness** as a core enterprise capability. Future AI integration operates under the following architectural pillars:

1. **AI Gateway**: A centralized abstraction layer in `app/Modules/AI/` that routes, rate-limits, monitors, and audits all AI and LLM interactions across the platform.
2. **Prompt Layer**: Structured prompt templates, versioning control, and context injection management decoupled from underlying model implementations.
3. **RAG-ready Knowledge Layer**: Vector storage adapters, embedding pipelines, semantic indexing, and chunking capabilities integrated within `app/Modules/Knowledge/` to support retrieval-augmented generation.
4. **Tool Calling Interface**: Standardized tool/function definitions mapping LLM function-calling capabilities safely to application use cases with strict schema validation and permission checks.
5. **LLM Independence**: Complete decoupling from specific LLM vendors (e.g., OpenAI, Gemini, Anthropic, or self-hosted models via Ollama/vLLM) through vendor-agnostic provider adapters.

## Evolution Principles

- Validate module boundaries before implementation.
- Enforce strict public contracts between domain modules.
- Require Architecture Decision Records (ADRs) in `docs/adr/` for structural architectural changes.
- Postpone physical microservice extraction until operational, security, or scaling demands justify distributed system complexity.