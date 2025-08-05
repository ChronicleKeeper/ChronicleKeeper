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

- `Calendar`: Fantasy Calendar implementation that has nothing to do with the Gregorian calendar.
- `Chat`: The chatbot module that allows users to interact with the application llm layer through a chat interface.
- `Document`: Document management module that allows users to create, edit, and manage documents, they will be utilized in the `Library` module.
- `Library`: Library module that allows users to manage and organize documents, images, chats, and other resources from other modules.
- `Settings`: Settings module that allows users to manage application settings and configurations.
- `World`: A graph like implementation for world entities to give the users role play world more content.
