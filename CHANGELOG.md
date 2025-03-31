# Changelog

## [alpha-0.8] - Steps to the cloud

**Added**
- Database: PostgreSQL support for enhanced scalability.
- Chat Interface: Live streaming of LLM responses to show real-time "thinking".
- Calendar: A new navigation entry with a table calendar view can be found in the menu.
    - Removed the old calendar settings with text-based calendar and moon cycle.
    - Added new section for configuration of the calendar.
    - Calendar configurations can be shared with an extra export/import functionality.
    - The calendar can be configured with custom epochs, months, weekdays, days and moon cycle.
    - A month can contain leap days that don't count toward weekdays and stand between other days.
    - There are LLM functionalities that access the calendar system.
- Added user documentation link to footer

**Changed**
- Some forms now contain an improved footer with more button submit choiced.
    - The default is moving back to the list.
    - You can move to the view mode of the created item.
    - You can create an additional item after the current one.
- The Makefile environment has widely changed to be more open for the different database environments.
- There is a docker setup again for the PostgreSQL environment.
- The CI is now testing SQLite and PostgreSQL environments during development of features.
- The directory cache in the library is gone again, it is not needed anymore after SQLite and PostgreSQL implementation.
- Update Dependencies to the latest versions.

**Fixed**
- Favorites now properly track renamed entries.
- Add missing hamburger menu for small displays.

**Removed**
- PHPDesktop release is given up because of poor local performance with larger application

## [alpha-0.7] - Moving Forward

**Added**
- Introduced the "World" module for managing and interacting with items and their relationships.
    - Handling items with types and short descriptions.
    - Adding items to shortcuts.
    - Managing media references, including viewing, adding, and removing media.
    - Establishing relationships between items.
    - Accessing bi-directional connections of items.
    - Integrating the world database with the chatbot.
    - Extending the system prompt to utilize the world database.
    - Viewing linked database entries in document and image headers.

**Changed**
- Refactored the full data layer to utilize mostly a SQLite database instead of files.
- Added a vector storage plugin for SQLite to store and search vectors in the database.
  - **Caution:** The SQLite extensions are stopping PHP from running in multi-threaded environments.
- Move the project back to the MIT License from this version on.
- Favorites dropdown changed to an offsite canvas for better overview.
- Removed the favorites limit of 10 because of better visualization.
- Favorites are now sorted by their title.
- Replaced vanilla JS confirm dialog on deletion links with a fully-featured dialog.
- Updated cztomczak/phpdesktop build to lates version 130.
- Updated to PHP 8.4 and utilized it during builds.

## [alpha-0.6] - Performance and Stability

**Added**
- Implemented a directory cache for better performance while browsing the library.
- The directory cache will be fully cleared with every completed export to prevent inconsistencies between the cache and data.
- Added a library header function to clear the directory cache manually.
- The search index for documents and images of the library is now automated on changes, a global refresh is not generally needed anymore.
- The file access now has a request-based cache layer to improve file access performance.
- Overhauled system prompt configuration to now allow configuring all system prompts in the system and have multiple prompts for different situations.
- A new conversation is now not only a "reset" button but a dialog with title and prompt selection.
- Added a prompt selection to the upload documents form.
- Added a prompt selection to the upload image form.
- Added a prompt selection to the image generator first step for creating the DALL-E prompt.

**Changed**
- Refactored the document-related code within the library to be a module on its own.
- Upgraded many dependencies to the current stable versions.
- Improved PHP configuration to be more efficient in the local PHP Desktop environment.
- Optimized performance within document and image vectors by reducing the data loaded from the database for search.
- Improved the performance of embedding generation by generating all document and image text chunks at once.
- Utilized a minimum vector content chunk length to reduce the failure rate for `only-dot` content cases.
- Optimized the conversation storage to not keep copies of the library within its own storage.
- BC: Imported conversations older than version 0.6 will be deleted from the database and from favorites.
- Refactored Images, Conversations, Documents, and Directories to emit events on changes to allow hook extended logic into them.
- BC: Existing system prompt configuration will be lost as it is not only a textbox anymore.

## [alpha-0.5.1]
+
**Fixed**
- Fixed a bug where a document upload with enabled LLM optimization had an error in data handling.

## [alpha-0.5] - Fun with images

**Added**
- Mechtild, the new image artist, is now available for image generation based on DALL-E 3.
- Added shortcuts to the navigation for quick access to images, documents, and conversations.
- Added a "notifications" event in UX available for live-action notifications.
- Split the searchable embeddings of documents into 800 string length chunks for better search results, which also allows more document responses.
- Split the searchable embeddings of image descriptions into 800 string length chunks for better search results, which also allows more image responses.
- There is a welcome page now when the system starts first without settings - choose between import or manual configuration.
- Added a button for markdown editor to toggle fullscreen mode.
- Implemented that descriptions for chatbot functions are editable within the settings.

**Changed**
- Raised default library responses to ChatGPT to 20 documents and 15 images within the default settings.
- The OpenAI API key is now set up in application settings instead of the environment.

**Fixed**
- Resolved importing from alpha-0.2 does not assign all files and images to a directory.
- Fixed import library archives being limited to 10 MB.
- Fixed various spelling errors.

## [alpha-0.4] - Conversations will save the world

**Added**
- Docker development environment with [FrankenPHP](https://frankenphp.dev/) support as an alternative to the Symfony local server.
- Confirmation dialog for resetting settings.
- Image button in the markdown editor for embedding image links.
- Error pages for 404 and 500 HTTP status codes. The 500 error page includes debug information for developers.
- Completely reworked the conversation area to multiple conversations with custom settings and storage per conversation.
- Storable conversations are added to the library. So a stored conversation will be placed in the library.
- Verbosity of function debugging in chat output is enhanced with distance calculations from vector search.
- PDF files are now supported for document upload functionality.
- Directories can be moved between parent directories. The content within a directory will move with the directory.
- Directories can now be deleted and one can choose to move or purge the content.

**Changed**
- Began project file structure cleanup, moving from prototyping to clean code.
- Navigation now displays the project logo and is sticky at the top.
- Reverted default GPT model to GPT4o-mini from GPT4o due to lack of improvement.
- The chatbot now embeds images if their descriptions match the user's request.
- When uploading images, descriptive context data is now attempted to be retrieved from documents based on the file name.
- Import / Export from application is now also containing conversations.
- The GPT function "novalis_documents" is now named "library_documents".
- The GPT function "novalis_images" is now named "library_images".
- Improvements for the GPT function descriptions, enhanced with examples to give a better understanding when to use them.
- Improved the System Prompt to have better knowledge about the calendar and improve the understanding of what to do with the user.

**Fixed**
- Resolved build process failure caused by a missing content block in the loader component.
- Disabled unnecessary Turbo streams activation.
- Utilize 64bit PHP insteaf of 32bit for Windows Desktop build to fix twig cache problems utilizing large integers.

## [alpha-0.3] - Images will rule the world

**Added**
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

**Changed**
- Chatbot messages now store context in a custom design instead of directly in the chat response.
- Vector storage search for context documents and images now uses a max distance to reduce false positives.
- Upgraded tabler.io design from beta-20 to beta-21.
- The chat with "Rostbart" now uses GPT-4o instead of GPT-4o-mini to improve context interpretation.

**Fixed**
- Media deletion in the library could be executed when the loader shows and the mouse hovers over the delete action.
- Loader spinner no longer displayed on history back.
- Library sorting now respects German umlauts.

## [alpha-0.2] - Improve Document Workflows

**Added**
- Implement Symfony UX with a Loader Component to indicate page loading.
- Implement TOAST UI Editor for creating and editing documents to have improved markdown access.
- Implement virtual directories to the library functionality to bring some order to it.
- New setting for custom naming the chatbot, shown in the menu and in replies to conversations.
- New setting for custom naming the user, shown in dialogs.
- Add a footer page link to the rendered changelog from the filesystem.
- Implement view for documents with parsed output, including full directory breadcrumb navigation.
- Add GPT Functions for extended information to the calendar system, holidays, and moon calendar, configurable within the settings.
- Added an export functionality to the settings area as preparation for alpha-0.3 import and migration.
- 
**Changed**
- Replace Font Awesome full icon set reference with Symfony UX Icons and utilize tabler.io icons.
- Rename the document section to library for better wording.
- Design change of the settings page in preparation for upcoming extended settings.
- Change log configuration to a rotation model, keeping logs for the last 14 days instead of a single endless file.
- Overhaul of the settings - split up into sections for specific settings.

## [alpha-0.1] - Dungeon Master Showcase Prototype

**Added**
- Implement a base system for document management using vector embeddings.
- Implement settings for system prompt, number of documents searched in chat, and the current play date.
- Implement a basic chat feature with a single conversation store utilizing the OpenAI API.
