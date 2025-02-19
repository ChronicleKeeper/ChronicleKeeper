import { Controller } from '@hotwired/stimulus';
import { marked } from 'marked';

/** @typedef {import('marked').MarkedOptions} MarkedOptions */

/**
 * @typedef {Object} ChatStreamTargets
 * @property {HTMLElement} messagesTarget
 * @property {HTMLInputElement} inputTarget
 * @property {HTMLButtonElement} scrollButtonTarget
 * @property {HTMLButtonElement} storeButtonTarget
 */

/**
 * @typedef {Object} BaseMessage
 * @property {keyof typeof CONFIG.MESSAGE_TYPES} type
 */

/**
 * @typedef {Object} ChunkMessage
 * @property {'chunk'} type
 * @property {string} chunk
 */

/**
 * @typedef {Object} CompleteMessage
 * @property {'complete'} type
 * @property {string} id
 */

/**
 * @typedef {Object} DebugCall
 * @property {string} tool - Name of the called function/tool
 * @property {Object} arguments - Arguments passed to the function
 * @property {Object} result - Result returned from the function
 */

/**
 * @typedef {Object} ContextDocument extends Message
 * @property {'document'} type
 * @property {string} id
 * @property {string} title
 */

/**
 * @typedef {Object} ContextImage extends Message
 * @property {'image'} type
 * @property {string} id
 * @property {string} title
 */

/**
 * @typedef {Object} Context
 * @property {ContextDocument[]} documents
 * @property {ContextImage[]} images
 */

/**
 * @typedef {Object} ContextMessage
 * @property {'context'} type
 * @property {string} id
 * @property {Context} context
 */

/**
 * @typedef {Object} DebugMessage
 * @property {'debug'} type
 * @property {string} id
 * @property {DebugCall[]} debug
 */

/**
 * @typedef {ChunkMessage | CompleteMessage | DebugMessage | ContextMessage} ChatStreamMessage
 */

const CONFIG = {
    MESSAGE_TYPES: {
        CHUNK: 'chunk',
        COMPLETE: 'complete',
        DEBUG: 'debug',
        CONTEXT: 'context'
    },
    SCROLL: {
        THRESHOLD: 100,
        BEHAVIOR: 'instant'
    },
    MARKED_OPTIONS: /** @type {MarkedOptions} */ ({
        breaks: true,
        gfm: true,
        mangle: false,
        headerIds: false
    })
};

/**
 * @extends {Controller & ChatStreamTargets}
 */
export default class ChatStreamController extends Controller {
    static targets = ['messages', 'input', 'scrollButton', 'storeButton'];

    /** @type {string} */
    #accumulatedText = '';

    /** @type {Function} */
    #boundScrollHandler;

    connect() {
        this.#initializeMarkedOptions();
        this.#setupEventListeners();
        this.#initializeUI();
    }

    disconnect() {
        window.removeEventListener('scroll', this.#boundScrollHandler);
    }

    /**
     * @private
     */
    #initializeMarkedOptions() {
        marked.setOptions(CONFIG.MARKED_OPTIONS);
    }

    /**
     * @private
     */
    #setupEventListeners() {
        this.#boundScrollHandler = () => this.#handleScroll();
        window.addEventListener('scroll', this.#boundScrollHandler);

        // Handle accordion state changes
        document.addEventListener('shown.bs.collapse', () => {
            requestAnimationFrame(() => this.#handleScroll());
        });
        document.addEventListener('hidden.bs.collapse', () => {
            requestAnimationFrame(() => this.#handleScroll());
        });

        window.addEventListener('load', () => {
            requestAnimationFrame(() => this.scrollToBottom());
            this.inputTarget.focus();
        });
    }

    /**
     * @private
     */
    #initializeUI() {
        this.#accumulatedText = '';
    }

    /**
     * @private
     */
    #handleScroll() {
        const { THRESHOLD } = CONFIG.SCROLL;
        const scrolledToBottom = (window.innerHeight + window.scrollY) >=
            document.documentElement.scrollHeight - THRESHOLD;

        this.scrollButtonTarget.style.display = scrolledToBottom ? 'none' : 'block';
    }

    scrollToBottom() {
        window.scrollTo({
            top: document.documentElement.scrollHeight,
            behavior: CONFIG.SCROLL.BEHAVIOR
        });

        this.#handleScroll();
    }

    /**
     * @param {Event} event
     */
    async send(event) {
        event.preventDefault();
        const message = this.inputTarget.value;
        const conversationId = this.element.dataset.conversationId || '';

        this.#resetUIState();
        await this.#sendMessage(message, conversationId);
    }

    /**
     * @private
     * @param {string} message
     * @param {string} conversationId
     */
    async #sendMessage(message, conversationId) {
        const userMessage = this.#createUserMessage(message);
        const botMessage = this.#createBotMessage();

        this.messagesTarget.appendChild(userMessage);
        this.messagesTarget.appendChild(botMessage);
        this.scrollToBottom();

        const eventSource = new EventSource(
            `/chat/stream/message?message=${encodeURIComponent(message)}&conversation=${conversationId}`
        );

        this.#setupMessageStream(eventSource, botMessage);
    }

    /**
     * @private
     * @param {EventSource} eventSource
     * @param {HTMLElement} botMessage
     */
    #setupMessageStream(eventSource, botMessage) {
        eventSource.onmessage = (e) => this.#handleStreamMessage(e, botMessage);
        eventSource.onerror = () => this.#handleStreamEnd(eventSource);
    }

    /**
     * @private
     * @param {MessageEvent} event
     * @param {HTMLElement} botMessage
     */
    #handleStreamMessage(event, botMessage) {
        /** @type {ChatStreamMessage} */
        const data = JSON.parse(event.data);
        const botMessageBody = botMessage.querySelector('.card-body.message');

        if (!botMessageBody) return;

        switch (data.type) {
            case CONFIG.MESSAGE_TYPES.CHUNK:
                this.#handleChunkMessage(data.chunk, botMessageBody);
                this.scrollToBottom();
                break;
            case CONFIG.MESSAGE_TYPES.DEBUG:
                botMessageBody.insertAdjacentHTML('beforeend', this.#renderDebugSection(data));
                requestAnimationFrame(() => {
                    setTimeout(() => this.scrollToBottom(), 100);
                });
                break;
            case CONFIG.MESSAGE_TYPES.CONTEXT:
                botMessageBody.insertAdjacentHTML('beforeend', this.#renderContextSection(data));
                requestAnimationFrame(() => {
                    setTimeout(() => this.scrollToBottom(), 100);
                });
                break;
            case CONFIG.MESSAGE_TYPES.COMPLETE:
                this.#handleCompleteMessage(data.id, botMessage);
                this.scrollToBottom();
                break;
        }
    }

    /**
     * @private
     * @param {string} chunk
     * @param {HTMLElement} messageBody
     */
    #handleChunkMessage(chunk, messageBody) {
        this.#accumulatedText += chunk;
        messageBody.innerHTML = marked.parse(this.#accumulatedText);
    }

    /**
     * @private
     * @param {string} messageId
     * @param {HTMLElement} message
     */
    #handleCompleteMessage(messageId, message) {
        message.id = `message-${messageId}`;
        message.dataset.messageId = messageId;
    }

    /**
     * @private
     */
    #resetUIState() {
        this.inputTarget.value = '';
        this.#accumulatedText = '';
        this.storeButtonTarget.disabled = true;
        this.inputTarget.disabled = true;
    }

    /**
     * @private
     * @param {string} message
     * @returns {HTMLElement}
     */
    #createUserMessage(message) {
        const template = document.getElementById('loading-user-message');
        const userMessage = template?.cloneNode(true);
        if (!userMessage) throw new Error('User message template not found');

        userMessage.removeAttribute('id');
        userMessage.classList.remove('d-none');
        const body = userMessage.querySelector('.card-body');
        if (body) body.textContent = message;

        return userMessage;
    }

    /**
     * @private
     * @returns {HTMLElement}
     */
    #createBotMessage() {
        const template = document.getElementById('loading-bot-message');
        const botMessage = template?.cloneNode(true);
        if (!botMessage) throw new Error('Bot message template not found');

        botMessage.removeAttribute('id');
        botMessage.classList.remove('d-none');

        return botMessage;
    }

    /**
     * @private
     * @param {EventSource} eventSource
     */
    #handleStreamEnd(eventSource) {
        eventSource.close();
        this.storeButtonTarget.disabled = false;
        this.inputTarget.disabled = false;
        this.inputTarget.focus();
    }

    /**
     * @private
     * @param {DebugMessage} data
     * @returns {string}
     */
    #renderDebugSection(data) {
        if (!data.debug || !data.debug.length) return '';

        return `
            <div class="hr-text text-warning">GPT Functions Debugging Ausgaben</div>
            <div class="accordion" id="debug-tool-calls-${data.id}">
                ${data.debug.map((call, index) => `
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading-${data.id}-${index}">
                            <button class="accordion-button collapsed" type="button"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#collapse-${data.id}-${index}">
                                Aufgerufene Funktion: ${call.tool}
                            </button>
                        </h2>
                        <div id="collapse-${data.id}-${index}" class="accordion-collapse collapse"
                            data-bs-parent="#debug-tool-calls-${data.id}">
                            <div class="accordion-body pt-0">
                                <pre>${JSON.stringify(call.arguments, null, 2)}</pre>
                                <pre>${JSON.stringify(call.result, null, 2)}</pre>
                            </div>
                        </div>
                    </div>
                `).join('')}
            </div>`;
    }

    /**
     * @private
     * @param {ContextMessage} data
     * @returns {string}
     */
    #renderContextSection(data) {
        const {documents, images} = data.context;
        if (!documents.length && !images.length) return '';

        const sections = [
            {
                type: 'documents',
                title: 'Verwendete Dokumente',
                items: documents,
                urlPrefix: '/library/document/'
            },
            {
                type: 'images',
                title: 'Verwendete Bilder',
                items: images,
                urlPrefix: '/library/image/'
            }
        ].filter(section => section.items.length > 0);

        const renderAccordionItem = ({type, title, items, urlPrefix}) => `
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#context-${type}-${data.id}">
                    ${title}
                </button>
            </h2>
            <div id="context-${type}-${data.id}" class="accordion-collapse collapse"
                 data-bs-parent="#context-information-${data.id}">
                <div class="accordion-body pt-0">
                    <div class="list-group list-group-flush">
                        ${items.map(item => `
                            <a href="${urlPrefix}${item.id}"
                               class="list-group-item list-group-item-action">
                                ${item.title}
                            </a>
                        `).join('')}
                    </div>
                </div>
            </div>
        </div>`;

        return `
        <div class="hr-text text-warning">GPT Functions Used Information</div>
        <div class="accordion" id="context-information-${data.id}">
            ${sections.map(renderAccordionItem).join('')}
        </div>`;
    }

}
