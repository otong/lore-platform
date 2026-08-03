# Coding Conventions

## Language Baseline and Code Formatting

- **Target Specification**: PHP 8.2+ and Laravel 12.
- **Strict Typing**: All PHP files must enforce strict typing at the top of the file:
  ```php
  declare(strict_types=1);
  ```
- **Standard**: Follow PSR-12 coding style and Laravel conventions. Format code using Laravel Pint.
- **Language**: All code, class names, method signatures, variable names, documentation, and inline comments must be written in English.

## Naming Conventions

- **Classes, Interfaces, Enums, Traits**: `PascalCase` (e.g., `UserRepository`, `CreateOrderCommand`).
- **Methods and Properties**: `camelCase` (e.g., `findUserById`, `isPublished`).
- **Constants & Enum Cases**: `UPPER_SNAKE_CASE` (e.g., `STATUS_ACTIVE`, `MAX_RETRIES`).
- **Interfaces**: Name interfaces by capability or role (e.g., `UserRepositoryInterface` or `UserRepository`, `NotifierInterface`), avoiding prefixed noise like `InterfaceUser`.
- **Command / Query Handlers**: Name after explicit intentions (e.g., `RegisterUserCommandHandler`, `FetchAuditLogsQueryHandler`).

## Layer-Specific Coding Rules

1. **Domain Layer (`Domain/`)**
   - Must be pure PHP.
   - Prohibited imports: Laravel Facades, Eloquent models, `Illuminate\*` packages, HTTP requests, or external vendor SDKs.
   - Use Value Objects for domain invariants (e.g., `EmailAddress`, `Money`).
2. **Application Layer (`Application/`)**
   - Coordinates domain operations via abstractions.
   - Use Data Transfer Objects (DTOs) to pass data into and out of application services.
   - Inter-module communication must invoke interfaces residing in `Application/Contracts/`. Direct database calls across module boundaries are strictly forbidden.
3. **Infrastructure Layer (`Infrastructure/`)**
   - Implements repositories and external clients.
   - Encapsulate ORM logic here. Eloquent models remain private to the module's `Infrastructure/Persistence/` folder.
4. **Presentation Layer (`Presentation/`)**
   - Responsible strictly for request handling and response mapping.
   - Controller methods must not contain business decisions or DB queries.

## Error Handling and Logging

- **Domain Exceptions**: Throw typed domain exceptions (e.g., `UserNotFoundException`, `InsufficientFundsException`) rather than generic PHP `Exception` classes.
- **Exception Mapping**: Catch domain exceptions in the Presentation layer or global handler and map them to standard HTTP status codes and error JSON payloads.
- **Sensitive Data Safety**: Never log passwords, API keys, credentials, tokens, or PII.

## AI Integration Coding Standards

- **LLM Provider Isolation**: Never call LLM SDKs (e.g., OpenAI SDK, Anthropic SDK) directly inside business modules. Always invoke the `AIGatewayInterface` provided by `app/Modules/AI/`.
- **Tool Calling Contracts**: Define tools exposed to LLMs as explicit DTOs with validated JSON Schemas.
- **Prompt Templates**: Keep prompts as versioned templates inside the AI module prompt repository.

## Testing Standards

- Tests for a domain module must be placed in `tests/Modules/<Domain>/`.
- Unit tests cover domain entities, value objects, and application use cases with mock dependencies.
- Integration tests verify repository implementations and external integrations against test databases or stubs.