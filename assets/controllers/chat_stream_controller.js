import { Controller } from '@hotwired/stimulus';
import { marked } from 'marked';

const MessageEventType = {
    CHUNK: 'chunk',
    COMPLETE: 'complete',
    DEBUG: 'debug',
    CONTEXT: 'context'
};

export default class extends Controller {
    static targets = ['messages', 'input', 'scrollButton', 'storeButton'];

    connect() {
        this.messageContainer = this.messagesTarget;
        this.messageInput = this.inputTarget;
        this.scrollButton = this.scrollButtonTarget;
        this.storeButton = this.storeButtonTarget;
        this.accumulatedText = '';

        marked.setOptions({
            breaks: true,
            gfm: true,
            mangle: false,
            headerIds: false
        });

        window.addEventListener('scroll', () => this.toggleScrollButton());

        window.addEventListener('load', () => {
            requestAnimationFrame(() => this.scrollToBottom());
            this.messageInput.focus();
        });
    }

    toggleScrollButton() {
        const threshold = 100;
        const scrolledToBottom = (window.innerHeight + window.scrollY) >= document.documentElement.scrollHeight - threshold;
        this.scrollButton.style.display = scrolledToBottom ? 'none' : 'block';
    }

    scrollToBottom() {
        window.scrollTo({
            top: document.documentElement.scrollHeight,
            behavior: 'instant'
        });

        this.toggleScrollButton();
    }

    async send(event) {
        event.preventDefault();

        const message = this.messageInput.value;
        this.messageInput.value = '';
        this.accumulatedText = ''; // Reset accumulated text
        this.disableInputs();

        // Get conversation ID from data attribute if available
        const conversationId = this.element.dataset.conversationId || '';
        const userMessage = this.createUserMessage(message);
        const botMessage = this.createBotMessage();

        // Add messages to the chat and scroll back to the new bottom of the window
        this.messagesTarget.appendChild(userMessage);
        this.messagesTarget.appendChild(botMessage);
        this.scrollToBottom();

        // Handle streaming response
        const eventSource = new EventSource(`/chat/stream/message?message=${encodeURIComponent(message)}&conversation=${conversationId}`);

        eventSource.onmessage = (e) => this.handleStreamMessage(e, botMessage);
        eventSource.onerror = () => this.handleStreamEnd(eventSource);
        eventSource.onclose = () => this.handleStreamEnd(eventSource);
    }

    handleStreamMessage(event, botMessage) {
        const data = JSON.parse(event.data);
        const botMessageBody = botMessage.querySelector('.card-body.message');

        switch (data.type) {
            case MessageEventType.CHUNK:
                this.accumulatedText += data.chunk;
                botMessageBody.innerHTML = marked.parse(this.accumulatedText);
                break;

            case MessageEventType.COMPLETE:
                botMessage.id = `message-${data.id}`;
                botMessage.dataset.messageId = data.id;
                break;
        }

        this.scrollToBottom();
    }

    createUserMessage(message) {
        const userTemplate = document.getElementById('loading-user-message');
        const userMessage = userTemplate.cloneNode(true);
        userMessage.removeAttribute('id');
        userMessage.classList.remove('d-none');
        userMessage.querySelector('.card-body').textContent = message;

        return userMessage;
    }

    createBotMessage() {
        const botTemplate = document.getElementById('loading-bot-message');
        const botMessage = botTemplate.cloneNode(true);
        botMessage.removeAttribute('id');
        botMessage.classList.remove('d-none');

        return botMessage;
    }

    disableInputs() {
        this.storeButton.disabled = true;
        this.messageInput.disabled = true;
    }

    handleStreamEnd(eventSource) {
        eventSource.close();
        this.storeButton.disabled = false;
        this.messageInput.disabled = false;
        this.messageInput.focus();
    }
}
