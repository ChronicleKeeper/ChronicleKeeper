# ChronicleKeeper Development Instructions

This file provides instructions for developers working on the ChronicleKeeper project. It includes guidelines for setting up the development environment, running tests, and contributing to the codebase.

## Code Requirements

- PHP 8.4 and upwards is usable for coding
- Symfony 7.3, the latest version of the Symfony framework, is used
- The code must follow the doctrine coding standards
- The code must be compatible with PHPStan Level 8
- Symfony UX with Stimulus is used for frontend development

## Project Command Overview

### Starting the Development Server

```
docker compose --profile dev up -d
```

This command starts the development server in detached mode. It uses Docker Compose to set up the necessary services defined in the `docker-compose.yml` file. After a successful start, you can access the application at `https://localhost` with a self-signed certificate that is acceptable.

### Executing Tests

```
make test
```

This command executes the full test suite using PHPUnit. It runs all tests defined in the project, ensuring that the codebase is functioning as expected.

### Code Quality Checks

```
make qa                     # Executes all code quality checks after each other
make check-cs               # Executes code style checks only
make static-analysis        # Executes static analysis checks only
```

This command runs various code quality checks, including static analysis and linting. It helps maintain code standards and ensures that the code adheres to best practices. It is recommended to always execute the `qa` target after work has done to ensure code quality.

### Code Quality Fixer

```
make fix-all                # Executes all code quality fixers, codestyle and rector - prefered
make fix-cs                 # Executes code style fixer only
make rector                 # Executes Rector only
```

This command applies automatic fixes to the codebase using tools PHPCBF and Rector. It helps in maintaining a clean and consistent code style across the project. It is recommended to always execute the `fix-all` target after work has done to ensure code quality.

### Application Structure

The application is structured into several directories, each serving a specific purpose:

- `src/`: Contains the main application code, including controllers, services, and entities, organized in modules with a `Shared` module for common logic.
- `tests/`: Contains the test cases for the application, organized by module.
- `config/`: Contains configuration files for the application, including routing and service definitions.
- `templates/`: Contains Twig templates for rendering views.

### Existing Modules

- `Calendar`: Fantasy Calendar implementation that has nothing to do with the Gregorian calendar. Provides custom timekeeping and event management for roleplay scenarios.
- `Chat`: The chatbot module that allows users to interact with the application LLM layer through a chat interface. Supports conversational AI and context-aware messaging.
- `Document`: Document management module that allows users to create, edit, and manage documents. Documents are utilized in the Library module and can be linked to other entities.
- `Favorizer`: Module for favoriting or bookmarking entities across the application, enabling users to quickly access preferred documents, images, or other resources.
- `Image`: Handles image management, including uploading, storing, and retrieving images for use in documents, world entities, and the library.
- `ImageGenerator`: Provides AI-powered image generation capabilities, allowing users to create custom images for their documents or world entities.
- `Library`: Library module that allows users to manage and organize documents, images, chats, and other resources from other modules. Acts as a central repository for user content.
- `Settings`: Settings module that allows users to manage application settings and configurations, including user preferences and system options.
- `Shared`: Contains shared logic, utilities, and base classes used across multiple modules to promote code reuse and maintainability.
- `World`: A graph-like implementation for world entities to give the users' roleplay world more content. Supports relationships, locations, and entity management for immersive storytelling.

### Module Structure and Domain Driven Design

The ChronicleKeeper application is organized according to Domain Driven Design (DDD) principles. Each module under `src/` represents a distinct domain or bounded context, encapsulating its own logic, data, and interactions. This structure promotes maintainability, scalability, and clear separation of concerns.

#### Canonical Module Directory Layout

A typical module (e.g., `Calendar`, `Chat`, `Document`, etc.) is structured as follows:

```
src/
  <ModuleName>/
    Application/
      Command/         # Write operations: commands and their handlers
      Event/           # Application-level events for orchestration
      Query/           # Read operations: queries and their handlers
      Service/         # Application services coordinating workflows
    Domain/
      Entity/          # Core business objects and aggregates
      Event/           # Domain events reflecting state changes
      Exception/       # Domain-specific error handling
      Service/         # Domain services encapsulating business logic
      ValueObject/     # Immutable value objects without identity
    Infrastructure/
      Database/        # Persistence logic, converters, schema providers
      LLMChain/        # Integration with external AI systems
      Serializer/      # Serialization/deserialization of domain objects
      ValueResolver/   # Value resolution logic
    Presentation/
      Controller/      # HTTP controllers for request/response handling
      Form/            # Symfony form types for user input
      Twig/            # Symfony UX extensions and classes, NOT templates. Backed by templates in the global `templates/` directory
```

#### Directory Purpose and Best Practices

- **Application/**: Orchestrates use cases, mediates between presentation and domain layers. Contains commands, queries, events, and services for business workflows.
- **Domain/**: Encapsulates core business logic, rules, and state. Entities, value objects, domain events, exceptions, and services reside here.
- **Infrastructure/**: Integrates with external systems and handles technical concerns (e.g., database, AI, serialization).
- **Presentation/**: Manages user interaction and view rendering (controllers, forms, Symfony UX extensions/classes). Note: Templates are located in the global `templates/` directory.

#### DDD Reflection

This structure ensures:
- Each module is a bounded context with clear responsibility.
- Domain logic is isolated from infrastructure and presentation concerns.
- Code is organized for testability, reusability, and ease of onboarding.

#### Example: Chat Module

```
src/Chat/
  Application/
    Command/
    Event/
    Query/
    Service/
  Domain/
    Entity/
    Event/
    ValueObject/
  Infrastructure/
    Database/
    LLMChain/
    Serializer/
    ValueResolver/
  Presentation/
    Controller/
    Form/
    Twig/
```

> Not all modules will have every subdirectory, but this structure is recommended for consistency and maintainability. Shared logic should be placed in the `Shared` module.
