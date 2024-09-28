import {Controller} from '@hotwired/stimulus';
import {getComponent} from '@symfony/ux-live-component';

export default class extends Controller {
    async initialize() {
        this.component = await getComponent(this.element);

        const input = document.getElementById('chat-message');
        input.addEventListener('keypress', (event) => {
            if (event.key === 'Enter') {
                this.submitMessage();
            }
        });
        input.focus(); // Initial page load focus to the text box

        const submitButton = document.getElementById('chat-submit');
        submitButton.addEventListener('click', (event) => {
            // @TODO: validate message
            this.submitMessage();
        });

        this.component.on('loading.state:started', (e,r) => {
            document.getElementById('loading-bot-message').classList.remove('d-none');
            document.getElementById('loading-user-message').classList.remove('d-none');

            document.getElementById('chat-message-form').classList.add('search-loading');
            input.setAttribute('disabled', 'disabled');
        });

        this.component.on('loading.state:finished', () => {
            document.getElementById('loading-bot-message').classList.add('d-none');
            document.getElementById('loading-user-message').classList.add('d-none');
        });

        this.component.on('render:finished', () => {
            document.getElementById('chat-message-form').classList.remove('search-loading');

            input.removeAttribute('disabled');
            input.focus();
        });
    }

    submitMessage() {
        const input = document.getElementById('chat-message');
        const message = input.value;

        document
            .getElementById('loading-user-message')
            .getElementsByClassName('message')[0].innerHTML = message;

        this.component.action('submit', {message});
        input.value = '';
    }
}
