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
 * @typedef {ChunkMessage | CompleteMessage} ChatStreamMessage
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
                break;
            case CONFIG.MESSAGE_TYPES.COMPLETE:
                this.#handleCompleteMessage(data.id, botMessage);
                break;
        }

        this.scrollToBottom();
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
}
