# PROJECT_RULES.md — Project Rules & Conventions

This document defines the core rules, conventions, and governance policies that guide both human contributors and AI-assisted workflows in the LORE project.

## Purpose

- Establish consistent project behavior
- Clarify expectations for branches, pull requests, and reviews
- Ensure AI and human contributors align on project norms

## Key Sections

### General Rules
- Use feature branches for all work
- Never commit directly to `main`
- Link PRs to issues and describe the value clearly
- Keep change sets focused and reviewable

### Collaboration
- Peer review is required for all code changes
- Document architecture decisions in `docs/adr/`
- Keep documentation updated with functional changes

### Security and Compliance
- Never commit secrets or credentials
- Use environment variables for sensitive settings
- Validate inputs and enforce authorization

### AI Participation
- AI may suggest code, docs, and architecture improvements
- Human review is mandatory before merging AI-generated contributions
- Log AI decisions in `.ai/decisions/`
