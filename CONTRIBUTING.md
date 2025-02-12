# Contributing to Chronicle Keeper

Welcome to Chronicle Keeper! We're excited you're here and want to contribute to making this RPG companion even better.
With this documentation we will try to give you a little handbook to start into developing with us. 

## Quick Start into Development

The first step is to fork the repository to have it at your namespace and to be able to push to your repository.

```bash
# Clone the repository
git clone git@github.com:yourusername/ChronicleKeeper.git
cd ChronicleKeeper

# Start the development environment of your choice
make dev DB=sqlite     # For SQLite development
# OR
make dev DB=pgsql      # For PostgreSQL development

# After first start you need to initially setup your database
make setup-database
```

That's it. You are ready to go. Based on the setup you now can open your browser 

## Testing

### Running Tests

```bash
# Run all tests (both SQLite and PostgreSQL)
make test-all

# Run SQLite tests only
make test DB=sqlite

# Run PostgreSQL tests only
make test DB=pgsql

# Generate coverage report
make coverage
```

### Existing Test Groups
- `sqlite`: SQLite-specific tests
- `pgsql`: PostgreSQL-specific tests
- `large`: All tests containing database usage
- `small`: Database agnostic tests

## Quality Assurance

```bash
# Run all QA tools
make qa

# Individual checks
make check-cs         # Code style
make static-analysis  # PHPStan
make lint-php         # PHP syntax
```

### Auto Fixes

```
make fix-all # Execute Rector and Codestyle Fixes
```

### Utilized Codestyle

The project utilizes the [doctrine coding standard](https://github.com/doctrine/coding-standard) with only the
modification of the line length allowed. Please follow those coding standards for your contributions.

## Project Structure

The project is structured in a way that the main application code is located in the `src` directory. The test code can
be found in the `tests` directory. The `public` directory contains the entry point of the application while the
assets are located in the `assets` directory. The templates that are renderable by the presentation controllers 
are located in the `templates` directory.

The `src` directory is further structured in a modularized way. Each module has its own directory and contains basically
the same structure. Please try to follow those structures when contributing to the project.

### Module Structure

Each module contains the following directories:

```text
- Module                # The module directory
    - Application       # Layer code that is utilized for working within the module
        - Command       # Command classes that are used to execute actions based on the module data
        - Event         # Event handlers and subsribers to modify behavior based on modules events
        - Query         # Query classes that are used to fetch data from the module
        - Service       # Service classes that are used to provide functionality to the module
    - Domain            # Domain code that is utilized for the business data behavior
        - Entity        # Entity classes that are used to represent the business data
        - Event         # Event classes that are emitted by the domain of this module to the application
        - ValueObject   # Value object classes that are used to represent the business data
    - Infrastructure    # Infrastructure code that is utilized to implement behavior to work with 3rd party systems
        - Database      # Database classes that give information of behavior of the module to the database
        - LLMChain      # Behavior that is added to the chatbot, mostly LLM Functions (Tools) to be utilized in the chatbot
    - Presentation      # Presentation code that is utilized to render views and handle user input
        - Command       # CLI utilizations that are delivered by this module
        - Controller    # WEB Controller classes that are used to render views and handle user input
        - Form          # Form classes to be utilized within the WEB controller as data mappers for user input
        - Twig          # Twig extensions and components to be utilized within the templates
```

## Documentation

The documentation of the project is currently only an end user documentation in which we try to explain the usage
and the features of the Chronicle Keeper in depth. Please feel free to contribute to this documentation as well. 

You can find the documentation in an own repository [here](https://github.com/ChronicleKeeper/docs). 

## License

By contributing to Chronicle Keeper, you agree that your contributions will be licensed under its MIT license. You can
find the complete license text in the [LICENSE](LICENSE) file.
