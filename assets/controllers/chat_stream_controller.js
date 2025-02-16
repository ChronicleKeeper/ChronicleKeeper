import { Controller } from '@hotwired/stimulus';
import { marked } from 'marked';

export default class extends Controller {
    static targets = ['messages', 'input', 'scrollButton'];

    connect() {
        this.messageContainer = this.messagesTarget;
        this.messageInput = this.inputTarget;
        this.scrollButton = this.scrollButtonTarget;

        marked.setOptions({
            breaks: true,
            gfm: true,
            mangle: false,
            headerIds: false
        });

        window.addEventListener('scroll', () => this.toggleScrollButton());
        this.toggleScrollButton();
        requestAnimationFrame(() => this.scrollToBottom());
    }

    toggleScrollButton() {
        const threshold = 100;
        const scrolledToBottom = (window.innerHeight + window.scrollY) >= document.documentElement.scrollHeight - threshold;
        this.scrollButton.style.display = scrolledToBottom ? 'none' : 'block';
    }

    scrollToBottom() {
        window.scrollTo({
            top: document.documentElement.scrollHeight,
            behavior: 'smooth'
        });
        this.toggleScrollButton();
    }

    async send(event) {
        event.preventDefault();

        const message = this.messageInput.value;
        this.messageInput.value = '';

        // Clone and prepare user message
        const userTemplate = document.getElementById('loading-user-message');
        const userMessage = userTemplate.cloneNode(true);
        userMessage.removeAttribute('id');
        userMessage.classList.remove('d-none');
        userMessage.querySelector('.card-body').textContent = message;
        this.messagesTarget.appendChild(userMessage);

        // Clone and prepare bot message
        const botTemplate = document.getElementById('loading-bot-message');
        const botMessage = botTemplate.cloneNode(true);
        botMessage.removeAttribute('id');
        botMessage.classList.remove('d-none');
        this.messagesTarget.appendChild(botMessage);
        const botMessageBody = botMessage.querySelector('.card-body.message');

        this.scrollToBottom();

        // Handle streaming response
        const eventSource = new EventSource(`/chat/stream/message?message=${encodeURIComponent(message)}`);
        let accumulatedText = '';

        eventSource.onmessage = (e) => {
            const data = JSON.parse(e.data);
            accumulatedText += data.chunk;
            botMessageBody.innerHTML = marked.parse(accumulatedText);
            this.scrollToBottom();
        };

        eventSource.onerror = () => {
            eventSource.close();
        };
    }
}
