# CODING_STANDARD.md — Coding Standards

This document outlines the coding standards for the LORE project to ensure consistency, maintainability, and quality across backend and frontend development.

## Purpose

- Define shared style and structure expectations
- Reduce review overhead with consistent patterns
- Support AI and human contributors with clear conventions

## Sections

### Backend Standards
- Language: PHP 8.2+ (Laravel-ready)
- Format: PSR-12 compliant
- Structure: `Controllers`, `Requests`, `Services`, `Repositories`, `Models`
- Business logic should be in services, controllers should remain thin
- Validation via Form Requests, not inline rules
- No debug helpers in production code

### Frontend Standards
- Frontend language: modern JavaScript / TypeScript
- Framework: Vue 3 with Composition API
- Component files should be well-organized and self-contained
- Prefer `const` over `let`, avoid `var`
- Keep UI components accessible, testable, and reusable

### Documentation Standards
- Code should include meaningful comments for intent, not implementation
- Public APIs must document request/response expectations
- Major decisions require ADRs in `docs/adr/`

### AI-Generated Code
- Clearly label generated code blocks
- Verify AI output against coding standards before merging
- Maintain easy traceability of AI contributions
