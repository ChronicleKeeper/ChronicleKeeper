# Changelog

## [alpha-0.4] - Conversations will save the world

### Added
- Docker development environment with [FrankenPHP](https://frankenphp.dev/) support as an alternative to the Symfony local server.
- Confirmation dialog for resetting settings.
- Image button in the markdown editor for embedding image links.
- Error pages for 404 and 500 HTTP status codes. The 500 error page includes debug information for developers.
- Storable conversations are added to the library. So a stored conversation will be placed in the library.

### Changed
- Began project file structure cleanup, moving from prototyping to clean code.
- Navigation now displays the project logo and is sticky at the top.
- Reverted default GPT model to GPT4o-mini from GPT4o due to lack of improvement.
- The chatbot now embeds images if their descriptions match the user's request.
- When uploading images, descriptive context data is now attempted to be retrieved from documents based on the file name.
- Completely reworked the conversation area to multiple conversations.
- Import / Export from application is now also containing conversations.

### Fixed
- Resolved build process failure caused by a missing content block in the loader component.
- Disabled unnecessary Turbo streams activation.

## [alpha-0.3] - Images will rule the world

### Added
- Show referenced documents from vector storage during chat, disable with general chatbot setting.
- Library extended with image management: upload, view, edit, delete, and vector storage for search.
- Images in the library get LLM-guessed descriptions on upload for search and chatbot context.
- New settings in the general chatbot section to define the number of context images and whether to collect them for chat output.
- Chatbot fine-tuning options for general temperature and vector storage distance settings for images and documents.
- Responses from the chatbot can now be turned into documents as content template for the editor.
- Upload documents in Plain Text or Word format, automatically converting them to Markdown.
- Enable LLM-supported optimization for uploaded documents with an upload form checkbox.
- Export, implemented with alpha-0.2, is now also importable - only older versions can be imported. 
- Library can be pruned before import is starting.
- Collect debugging information about called tools for analysis. Displayed within the response if the setting is enabled.

### Changed
- Chatbot messages now store context in a custom design instead of directly in the chat response.
- Vector storage search for context documents and images now uses a max distance to reduce false positives.
- Upgraded tabler.io design from beta-20 to beta-21.
- The chat with "Rostbart" now uses GPT-4o instead of GPT-4o-mini to improve context interpretation.

### Fixed
- Media deletion in the library could be executed when the loader shows and the mouse hovers over the delete action.
- Loader spinner no longer displayed on history back.
- Library sorting now respects German umlauts.

## [alpha-0.2] - Improve Document Workflows

### Added
- Implement Symfony UX with a Loader Component to indicate page loading.
- Implement TOAST UI Editor for creating and editing documents to have improved markdown access.
- Implement virtual directories to the library functionality to bring some order to it.
- New setting for custom naming the chatbot, shown in the menu and in replies to conversations.
- New setting for custom naming the user, shown in dialogs.
- Add a footer page link to the rendered changelog from the filesystem.
- Implement view for documents with parsed output, including full directory breadcrumb navigation.
- Add GPT Functions for extended information to the calendar system, holidays, and moon calendar, configurable within the settings.
- Added an export functionality to the settings area as preparation for alpha-0.3 import and migration.

### Changed
- Replace Font Awesome full icon set reference with Symfony UX Icons and utilize tabler.io icons.
- Rename the document section to library for better wording.
- Design change of the settings page in preparation for upcoming extended settings.
- Change log configuration to a rotation model, keeping logs for the last 14 days instead of a single endless file.
- Overhaul of the settings - split up into sections for specific settings.

## [alpha-0.1] - Dungeon Master Showcase Prototype

### Added
- Implement a base system for document management using vector embeddings.
- Implement settings for system prompt, number of documents searched in chat, and the current play date.
- Implement a basic chat feature with a single conversation store utilizing the OpenAI API.
