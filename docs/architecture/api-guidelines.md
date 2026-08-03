# API Guidelines

## Scope and Status

These guidelines define the RESTful HTTP API standards for the LORE Enterprise Platform. Future API routes in `routes/api.php` or module route files must strictly comply with these conventions.

## API Boundary and Routing

- **Versioning**: All public APIs must be versioned under a uniform URI prefix: `/api/v1/...`.
- **Presentation Delegation**: Controllers reside in `Presentation/Http/Controllers/`. They must remain thin: validate transport input, delegate to an Application use case, and format the output.
- **No Direct Database Access**: Controllers must never query models or execute database calls directly.
- **Module Boundary Respect**: Endpoints represent domain capabilities. Cross-module data retrieval via HTTP endpoints must use explicit, versioned internal contracts.

## Resource Design

- **Resource Naming**: Use plural, lower-case kebab-case nouns for collections (e.g., `/api/v1/knowledge-items`, `/api/v1/users`).
- **HTTP Method Semantics**:
  - `GET`: Retrieve resource or collection (idempotent, safe).
  - `POST`: Create a resource or execute a domain action.
  - `PUT`: Replace a resource entirely.
  - `PATCH`: Update resource fields partially.
  - `DELETE`: Remove or deactivate a resource.
- **Explicit Actions**: For non-CRUD operations, use sub-resource action paths (e.g., `/api/v1/documents/{id}/publish`).

## Request Validation

- All incoming request payloads must be validated at the Presentation edge using dedicated Laravel Form Request classes (`Presentation/Http/Requests/`).
- Validation rules must strictly validate input types, length, format, and allowed values.
- Unknown fields should be stripped or rejected to prevent parameter injection.

## Standard Response Contract

### Success Response

Successful single resource:
```json
{
  "data": {
    "id": "018f3a2b-1234-7000-8000-000000000001",
    "type": "knowledge_item",
    "attributes": {
      "title": "Architecture Blueprint",
      "status": "published"
    }
  }
}
```

Successful paginated collection:
```json
{
  "data": [
    {
      "id": "018f3a2b-1234-7000-8000-000000000001",
      "type": "knowledge_item",
      "attributes": {
        "title": "Architecture Blueprint"
      }
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 100
  },
  "links": {
    "first": "/api/v1/knowledge-items?page=1",
    "next": "/api/v1/knowledge-items?page=2",
    "last": "/api/v1/knowledge-items?page=7"
  }
}
```

### Error Response

All errors return a standardized JSON payload and appropriate HTTP status code:
```json
{
  "error": {
    "code": "VALIDATION_FAILED",
    "message": "The request payload failed validation.",
    "details": [
      {
        "field": "title",
        "issue": "The title field is required."
      }
    ]
  }
}
```

## HTTP Status Codes

- `200 OK`: Request succeeded.
- `201 Created`: Resource successfully created.
- `204 No Content`: Successful deletion or action with no response body.
- `400 Bad Request`: Malformed JSON or invalid syntax.
- `401 Unauthorized`: Missing or invalid authentication token.
- `403 Forbidden`: Authenticated user lacks necessary permission.
- `404 Not Found`: Resource does not exist.
- `409 Conflict`: Resource state conflict (e.g., duplicate unique key).
- `422 Unprocessable Entity`: Validation rule failure.
- `429 Too Many Requests`: Rate limit exceeded.
- `500 Internal Server Error`: Generic unhandled server error (stack trace concealed in production).

## AI Endpoint Guidelines

Endpoints interacting with AI capabilities (e.g., `/api/v1/ai/completions`, `/api/v1/ai/embeddings`) must:
- Accept tool definition contracts and parameters safely.
- Support asynchronous execution or streaming responses where latency exceeds standard HTTP timeouts.
- Include correlation IDs for prompt/completion auditing.