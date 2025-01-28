import {Controller} from '@hotwired/stimulus';
import {Modal} from 'bootstrap';

export default class extends Controller {
    connect() {
        const modal = new Modal(this.element);

        const elements = document.querySelectorAll('[data-confirm]');
        if (elements.length > 0) {
            elements.forEach((element) => {
                element.addEventListener('click', (event) => {
                    event.preventDefault();

                    const iconClass = element.getAttribute('data-icon-color') || 'text-danger';
                    const title = element.getAttribute('title') || 'Bist du sicher?';
                    const message = element.getAttribute('data-confirm-message') || 'Bist du sicher?';
                    const confirmButtonText = element.getAttribute('data-confirm-button-text') || 'Ja';
                    const cancelButtonText = element.getAttribute('data-cancel-button-text') || 'Nein';
                    const statusColor = element.getAttribute('data-confirm-status-color') || 'bg-danger';
                    const buttonClass = element.getAttribute('data-confirm-button-class') || 'btn-danger';
                    const href = element.getAttribute('href');

                    const confirmButton = this.element.querySelector('.btn-4');
                    confirmButton.textContent = confirmButtonText;
                    confirmButton.className = `btn ${buttonClass} btn-4 w-100`;

                    this.element.querySelector('.modal-body .icon').setAttribute('class', `icon mb-2 ${iconClass} icon-lg`);
                    this.element.querySelector('.modal-body h3').textContent = title;
                    this.element.querySelector('.modal-body .confirmation-message').textContent = message;
                    this.element.querySelector('.modal-status').className = `modal-status ${statusColor}`;
                    this.element.querySelector('.btn-3').textContent = cancelButtonText;

                    const confirmClickHandler = () => {
                        window.dispatchEvent(new Event('loader:show'));
                        window.location.href = `${href}?confirm=1`;
                    };

                    confirmButton.addEventListener('click', confirmClickHandler);

                    this.element.addEventListener('hidden.bs.modal', () => {
                        confirmButton.removeEventListener('click', confirmClickHandler);
                    }, { once: true });

                    modal.show();
                });
            });
        }
    }
}
