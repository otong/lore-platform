# REVIEW_CHECKLIST.md — Code Review Checklist

This checklist is a lightweight guide for PR reviewers and contributors, ensuring each change aligns with the LORE project standards and quality expectations.

## Purpose

- Provide consistent review criteria
- Reduce overlooked issues in code and documentation
- Support reliable and repeatable review practices

## Checklist Categories

### General
- [ ] PR description explains scope and intent
- [ ] Issue reference included
- [ ] Change set is focused and relevant
- [ ] Branch is current with base branch

### Quality
- [ ] Code follows `CODING_STANDARD.md`
- [ ] No debug statements or temporary artifacts
- [ ] Naming and structure are clear
- [ ] Implementation is easy to understand

### Security
- [ ] No secrets or credentials in source
- [ ] Input validation and authorization are enforced
- [ ] Sensitive data handling is appropriate

### Backend
- [ ] Business logic is separated from controllers
- [ ] Validation uses request objects or equivalent patterns
- [ ] Database changes are reversible and documented

### Frontend
- [ ] Component structure is consistent and accessible
- [ ] Props and state are clearly defined
- [ ] UI handles loading and error states gracefully

### Testing and Documentation
- [ ] Tests added or updated for new behavior
- [ ] Documentation updated as needed
- [ ] ADRs added for major architecture decisions
