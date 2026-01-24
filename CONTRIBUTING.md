# Contributing to Desh Courier SDK

Thank you for your interest in contributing! This document provides guidelines for contributing to the project.

---

## How to Contribute

### Reporting Bugs

1. Check if the bug has already been reported in [Issues](https://github.com/millat/desh-courier/issues)
2. If not, create a new issue with:
   - Clear description of the bug
   - Steps to reproduce
   - Expected vs actual behavior
   - PHP version, SDK version, and environment details

### Suggesting Features

1. Check if the feature has already been suggested
2. Create a new issue with:
   - Clear description of the feature
   - Use cases and benefits
   - Possible implementation approach

### Adding Courier Drivers

See [ADDING_COURIER.md](ADDING_COURIER.md) for detailed instructions.

**Quick checklist:**
- [ ] Create driver directory
- [ ] Implement relevant interfaces
- [ ] Create mapper class
- [ ] Add configuration class
- [ ] Write tests
- [ ] Update documentation
- [ ] Submit pull request

---

## Development Setup

### Prerequisites

- PHP 8.1 or higher
- Composer
- Git

### Setup

```bash
# Clone repository
git clone https://github.com/millat/desh-courier.git
cd desh-courier

# Install dependencies
composer install

# Run tests
composer test
```

---

## Code Standards

### PHP Standards

- Follow [PSR-12](https://www.php-fig.org/psr/psr-12/) coding standard
- Use type hints everywhere
- Add PHPDoc comments for all public methods
- Keep methods small and focused

### Naming Conventions

- Classes: `PascalCase`
- Methods: `camelCase`
- Constants: `UPPER_SNAKE_CASE`
- Variables: `camelCase`

### Code Structure

- One class per file
- Namespace matches directory structure
- Keep classes under 300 lines when possible
- Use dependency injection

---

## Testing

### Writing Tests

- Write tests for all new features
- Aim for >80% code coverage
- Test both success and error cases
- Mock external dependencies (APIs, HTTP clients)

### Running Tests

```bash
# Run all tests
composer test

# Run with coverage
composer test:coverage

# Run specific test file
vendor/bin/phpunit tests/PathaoCourierTest.php
```

---

## Pull Request Process

1. **Fork the repository**
2. **Create a feature branch**: `git checkout -b feature/your-feature`
3. **Make your changes**
4. **Write/update tests**
5. **Update documentation**
6. **Run tests**: `composer test`
7. **Check code style**: `composer cs-check`
8. **Commit changes**: Use clear, descriptive commit messages
9. **Push to your fork**: `git push origin feature/your-feature`
10. **Create Pull Request**

### PR Checklist

- [ ] Code follows PSR-12 standards
- [ ] Tests pass
- [ ] Documentation updated
- [ ] No breaking changes (or clearly documented)
- [ ] CHANGELOG updated (if applicable)

---

## Commit Messages

Use clear, descriptive commit messages:

```
feat: Add Steadfast courier driver
fix: Handle null tracking ID in Pathao mapper
docs: Update installation guide
refactor: Simplify CourierRegistry
test: Add tests for StatusMapper
```

Prefixes:
- `feat:` - New feature
- `fix:` - Bug fix
- `docs:` - Documentation
- `refactor:` - Code refactoring
- `test:` - Tests
- `chore:` - Maintenance

---

## Adding a New Courier Driver

### Requirements

1. **Implement Interfaces**: Only implement interfaces your courier supports
2. **Status Mapping**: Use `StatusMapper` to normalize statuses
3. **Error Handling**: Throw appropriate exceptions
4. **Tests**: Write comprehensive tests
5. **Documentation**: Update README and capability matrix

### Example Structure

```
src/Drivers/YourCourier/
├── YourCourier.php
├── YourCourierConfig.php
└── YourCourierMapper.php
```

See [ADDING_COURIER.md](ADDING_COURIER.md) for detailed guide.

---

## Documentation

### Updating Documentation

- Update README.md for user-facing changes
- Update USAGE.md for usage examples
- Update ARCHITECTURE.md for architectural changes
- Add examples in `examples/` directory

### Documentation Standards

- Use clear, concise language
- Include code examples
- Keep examples up-to-date
- Add screenshots/diagrams when helpful

---

## Code Review

All contributions require code review. Reviewers will check:

- Code quality and standards
- Test coverage
- Documentation completeness
- Performance implications
- Security considerations

### Responding to Feedback

- Be open to suggestions
- Address all review comments
- Ask questions if unclear
- Update PR based on feedback

---

## Questions?

- **Issues**: [GitHub Issues](https://github.com/millat/desh-courier/issues)
- **Email**: dev@deshcourier.com
- **Discussions**: [GitHub Discussions](https://github.com/millat/desh-courier/discussions)

---

## Code of Conduct

- Be respectful and inclusive
- Welcome newcomers
- Focus on constructive feedback
- Respect different viewpoints

---

Thank you for contributing! 🎉
