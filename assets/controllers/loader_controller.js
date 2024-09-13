import {Controller} from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        const loaderElement = this.element;

        const showLoader = () => {
            console.log('Show Loader');

            loaderElement.classList.remove("invisible");
        };

        const hideLoader = () => {
            console.log('Hide Loader');
            loaderElement.classList.add("invisible");
        };

        const attachFormSubmitListener = () => {
            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', (event) => {
                    event.preventDefault();
                    showLoader();
                    form.submit();
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

            if (target.attributes.href === undefined) {
                console.log('Just something that is a link clicked, no loader.')
                return;
            }

            let linkUrl = new URL(target.getAttribute('href'), window.location.href); // Using the second argument to handle relative URLs
            let currentHostname = window.location.hostname;

            if (linkUrl.hostname !== currentHostname) {
                console.log('External Link clicked, no loader.')
                return;
            }

            showLoader();
        });

        attachFormSubmitListener();

        window.onbeforeunload = showLoader;
        window.onpageshow = () => {
            hideLoader();
            attachFormSubmitListener();
        };
    }


}
