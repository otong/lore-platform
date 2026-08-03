# Module Structure

## Target Structure

The following structure defines the standardized layout for backend domain modules. All future business domain implementations must adhere to this folder blueprint.

```text
backend/
├── app/
│   ├── Modules/
│   │   └── <Domain>/
│   │       ├── Application/
│   │       │   ├── Commands/
│   │       │   ├── Queries/
│   │       │   ├── Contracts/
│   │       │   ├── DTOs/
│   │       │   └── Services/
│   │       ├── Domain/
│   │       │   ├── Entities/
│   │       │   ├── ValueObjects/
│   │       │   ├── Events/
│   │       │   ├── Policies/
│   │       │   └── Contracts/
│   │       ├── Infrastructure/
│   │       │   ├── Persistence/
│   │       │   │   ├── Models/
│   │       │   │   └── Repositories/
│   │       │   ├── Integrations/
│   │       │   └── Providers/
│   │       └── Presentation/
│   │           ├── Http/
│   │           │   ├── Controllers/
│   │           │   ├── Requests/
│   │           │   └── Resources/
│   │           ├── Console/
│   │           └── Listeners/
│   └── Shared/
│       ├── Application/
│       ├── Domain/
│       ├── Infrastructure/
│       └── Presentation/
├── routes/
├── config/
├── database/
└── tests/
    ├── Unit/
    ├── Feature/
    └── Modules/
        └── <Domain>/
```

## Directory Purpose Mapping

| Directory | Layer / Purpose |
| --- | --- |
| `app/Modules/` | Root directory for isolated business domains. |
| `Application/` | Use cases, orchestrations, command/query handlers, DTOs, and interface contracts. |
| `Domain/` | Core business logic, domain entities, value objects, domain events, and domain rules. |
| `Infrastructure/` | Framework adapters, Eloquent models, repository implementations, third-party APIs. |
| `Presentation/` | Delivery mechanisms (HTTP controllers, console commands, queue listeners). |
| `app/Shared/` | Cross-cutting technical utilities and shared base interfaces (no domain logic). |
| `tests/Modules/<Domain>/` | Dedicated unit and integration tests owned by the domain module. |

## Naming and Domain Isolation Rules

- **Module Naming**: Modules use singular PascalCase (e.g., `Identity`, `Organization`, `Monitoring`, `Billing`, `Knowledge`, `AI`).
- **Database Boundary**: A module owns its migration files and database tables. Modules **never** execute direct cross-domain SQL queries, joins, or ORM relationships into another module's database tables.
- **Inter-Module Communication**: Access across modules occurs exclusively via:
  1. `Application/Contracts/` public interfaces.
  2. Asynchronous Domain Events in `Domain/Events/`.
  3. Formal HTTP/RPC internal API endpoints.

## Planned Domain Modules

Initial planned domain modules for LORE:
- `Identity` — User accounts, authentication credentials, permissions.
- `Organization` — Enterprise structures, tenants, departments.
- `Knowledge` — Documentation, semantic index, vector embeddings (RAG-ready).
- `Monitoring` — System telemetry, metrics, operational logs.
- `Regulation` — Compliance checks, policy enforcement, audit trails.
- `Finance` — Billing, invoicing, financial transactions.
- `Inventory` — Asset tracking, resource management.
- `AI` — AI Gateway, prompt templates, tool execution contracts, LLM adapters.
- `Audit` — System-wide security and operational activity logging.
- `Notification` — Multi-channel dispatch (Email, SMS, Webhooks).
- `Integration` — External enterprise system connectors.

## AI Readiness Architectural Structure

The `AI` and `Knowledge` modules incorporate specific structural support for AI integration:
- `app/Modules/AI/Infrastructure/Adapters/`: Pluggable adapters for LLM vendors (OpenAI, Gemini, Anthropic, Ollama).
- `app/Modules/AI/Application/Services/PromptService`: Manages prompt assembly and templates.
- `app/Modules/AI/Application/Services/ToolRegistry`: Registers and validates safe application tool execution contracts.
- `app/Modules/Knowledge/Infrastructure/Persistence/Vector/`: Store and query semantic vector embeddings for RAG retrieval.