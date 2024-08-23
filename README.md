# Novalis Document Management (Pending Name)

ChatGPT Supported document management to make the documents questionable from a system prompt.

## Features

- **Document Management**: Efficiently manage and organize your documents for Context knowledge.
- **ChatGPT Integration**: Make your documents interactive and queryable through system prompts.
- **PHPDesktop Support**: Build and run the application using PHPDesktop.

## Requirements

- PHP 8.3
- Composer
- Symfony CLI (for development server)
- OpenAI API Key

## Installation for Users

1. **Download the PHPDesktop Application**:
    - Go to the [latest release](https://github.com/DZunke/novdoc/releases/latest) of the project.
    - Download the `NovDoc-PHPDesktop.zip` file.


2. **Extract the Application**:
    - Extract the contents of the `NovDoc-PHPDesktop.zip` file to your desired location.


3. **Set up Environment Variables**:
    - Open the extracted folder and locate the `.env` file.
    - Open the `.env` file in a text editor and add your OpenAI API Key:
      ```dotenv
      OPENAI_API_KEY="your_openai_api_key_here"
      ```


4. **Run the Application**:
    - Navigate to the extracted folder and run `php-desktop.exe`.

## Installation for Development

1. **Clone the repository**:
    ```sh
    git clone https://github.com/DZunke/novdoc.git
    cd novdoc
    ```

2. **Install dependencies**:
    ```sh
    composer install
    ```

3. **Set up environment variables**:
   Copy the `.env` file and update the necessary values.
    ```sh
    cp .env .env.local
    ```
   
## Usage

### Development Server

Start the development server using Symfony CLI:
```sh
make serve-web
```

### PHPDesktop Application

Build with `make phpdesktop` creates a `build` directory. Copy the `.env.local` file to the `www` directory
of the build application and copy the `build` directory where ever you want to execute the `php-desktop.exe`

### Code Quality Checks

Run the following commands to ensure code quality:

```sh
make lint-php
make check-cs
make static-analysis
make phpunit
```

## Contributing

Contributions are welcome! Please open an issue or submit a pull request.

## License

**NovDoc** © 2024+, Denis Zunke. Published within the [MIT LIcense](https://mit-license.org/).

> GitHub [@dzunke](https://github.com/DZunke) &nbsp;&middot;&nbsp;
> Twitter [@DZunke](https://twitter.com/DZunke)

> Supported by &nbsp;&middot;&nbsp;
> [PHPDesktop](https://github.com/cztomczak/phpdesktop)&nbsp;&middot;&nbsp;
> [Tabler Dashboard](https://tabler.io)
