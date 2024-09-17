import {Controller} from '@hotwired/stimulus';
import '@toast-ui/editor/dist/toastui-editor.css';

import '../css/toastui-editor-dark.css';

export default class extends Controller {
    connect() {
        console.log('Connect Confirm!');

        const thatElement = this.element;
        const confirmBeforeRedirect = function (e) {
            e.preventDefault();

            if (!confirm('Wirklich löschen?')) {
                // Dirty workaround for disable loader
                document.getElementById('loader').classList.add('invisible');

                return;
            }

            // Dirty workaround for enable loader because of prevent default
            document.getElementById('loader').classList.remove('invisible');

            window.location = thatElement.href + '?confirm=1';
        };

        this.element.addEventListener('click', confirmBeforeRedirect);
    }
}
