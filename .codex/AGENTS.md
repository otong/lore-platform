# AGENTS.md — AI Agent Roles & Guidelines

This document provides a structured framework for how AI agents participate in the LORE project. It defines roles, responsibilities, and boundaries to ensure safe, consistent, and auditable AI-assisted development.

## Purpose

- Capture expected AI agent behavior
- Document allowed and prohibited activities
- Provide a reference for team onboarding and governance

## Sections

### Agent Roles
- Code Agent: supports code generation, refactoring, and scaffolding
- Review Agent: evaluates quality, security, and standards adherence
- Docs Agent: assists with documentation, ADRs, and changelog updates
- Ops Agent: proposes CI/CD and tooling improvements

### Governance
- Agents are advisory, not autonomous
- Human review is required for all merge decisions
- AI outputs must be traceable and labeled

### Logging
- Record major decisions in `.ai/decisions/`
- Flag AI-generated content clearly
- Maintain auditability for all agent contributions
