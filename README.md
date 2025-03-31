<p align="center">
    <a href="https://github.com/ChronicleKeeper/ChronicleKeeper">
        <img 
            src="https://raw.githubusercontent.com/ChronicleKeeper/ChronicleKeeper/main/assets/images/logo.png" 
            alt="Chronicle Keeper - Roleplaying Chatbot - Knowledge Keeper" 
            width="300"
        >
    </a>
</p>

<p align="center">
  <a href="https://chroniclekeeper.github.io/docs/"><img src="https://img.shields.io/badge/docs-read%20now-blue?style=for-the-badge" alt="Documentation"></a>
  <a href="https://codecov.io/gh/ChronicleKeeper/ChronicleKeeper" ><img alt="Codecov" src="https://img.shields.io/codecov/c/gh/ChronicleKeeper/ChronicleKeeper?style=for-the-badge"></a>
  <img alt="GitHub Release" src="https://img.shields.io/github/v/release/ChronicleKeeper/ChronicleKeeper?style=for-the-badge">
  <a href="https://github.com/ChronicleKeeper/ChronicleKeeper/blob/main/LICENSE"><img src="https://img.shields.io/github/license/ChronicleKeeper/ChronicleKeeper?style=for-the-badge" alt="License"></a>
</p>

## The Chronicle Keeper

Chronicle Keeper is a comprehensive companion for role-players, designed to help you develop and navigate fantasy worlds. Whether you're a dungeon master or a player, Chronicle Keeper assists you in tracking the development within your world, organizing knowledge, and enhancing your storytelling experience.

With the integrated chatbot powered by ChatGPT, you can further develop your world or characters based on existing knowledge or create entirely new elements. Chronicle Keeper brings your world just a question away from your imagination.

## Features

**Chronicle Keeper's capabilities include:**

- **Library Management**: Organize documents, images, conversations, and more in an easily accessible system
- **World Reference**: Quick access to information about connections between elements in your world
- **Custom Calendar**: Configure a calendar for your world independent from real-world timekeeping
- **AI Image Generation**: Create illustrations for your characters and world based on your existing knowledge
- **Interactive Chat**: Make all your world knowledge interactive through conversations with the keeper
- **Data Portability**: Export and import your data for backups, sharing, or migration


## Setup

### Requirements

- Docker Environment
- [OpenAI API Key](https://platform.openai.com/api-keys)
- Brave heroes on a journey through an exciting world

### Installation

```bash
# Clone the repository
git clone https://github.com/ChronicleKeeper/ChronicleKeeper.git
cd ChronicleKeeper

# Start the container 
docker compose -f compose.yaml -f compose.prod.yaml build
HTTP_PORT=8080 HTTPS_PORT=8081 docker compose -f compose.yaml -f compose.prod.yaml up

# Access the application
open https://localhost:8081
```

### Importing Data
If you have an existing export file:

```bash
cat your-export-file.zip | docker compose exec -T php bin/console app:import --stream -ps -vvv
```

## Getting Started

1. After installation, go to the settings page and input your OpenAI API Key
2. Start adding content through the Library interface
3. Try asking questions to the keeper about your world
4. Explore the documentation for advanced features

## User Documentation

Detailed documentation can be found at [https://chroniclekeeper.github.io/docs/](https://chroniclekeeper.github.io/docs/).

## Contributions

See [CONTRIBUTING.md](CONTRIBUTING.md) for more information on contributing and developer documentation.

## License

**Chronicle Keeper** © 2025+, Denis Zunke. This project is licensed under the MIT License,
see [LICENSE](LICENSE) for the full license text.
