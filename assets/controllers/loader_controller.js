import {Controller} from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        return;

        const loaderElement = this.element;

        const showLoader = () => {
            loaderElement.classList.remove("invisible");
        };

        const hideLoader = () => {
            loaderElement.classList.add("invisible");
        };

        // Initially call hide on connect method
        hideLoader();

        const attachFormSubmitListener = () => {
            const form = document.querySelector('form');
            const isLiveActionForm = form && form.getAttribute('data-action') !== null;

            if (form && !isLiveActionForm) {
                form.addEventListener('submit', (event) => {
                    showLoader();
                });
            }
        };

        document.addEventListener('click', (event) => {
            let target = event.target;
            if (target.tagName !== 'A') {
                target = event.target.closest('a');
                if (target === null) {
                    return;
                }
            }

            if (event.defaultPrevented === true) {
                return;
            }

            if (target.attributes.href === undefined) {
                return;
            }

            if (target.classList.contains('no-loader') === true) {
                return;
            }

            let linkUrl = new URL(target.getAttribute('href'), window.location.href); // Using the second argument to handle relative URLs
            let currentHostname = window.location.hostname;

            if (linkUrl.hostname !== currentHostname) {
                return;
            }

            showLoader();
        });

        attachFormSubmitListener();

        document.addEventListener('keydown', (e) => {
            if (e.key === 'F5') {
                showLoader();
            }
        });

        window.onpageshow = () => {
            hideLoader();
            attachFormSubmitListener();
        };

        window.addEventListener('loader:show', () => {
            showLoader();
        });

        window.addEventListener('loader:hide', () => {
            hideLoader();
        });
    }


}
