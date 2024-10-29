import {Controller} from '@hotwired/stimulus';
import Editor from '@toast-ui/editor';
import '@toast-ui/editor/dist/toastui-editor.css';

import '../css/toastui-editor-dark.css';

export default class extends Controller {
    connect()
    {
        let $elementIdentifier = this.element.getAttribute('id');
        let $mainElement       = document.getElementById($elementIdentifier);

        const $formElement = $mainElement.getElementsByClassName('editor-content')[0];
        let $editorElement = $mainElement.getElementsByClassName('editor')[0];

        const editor = new Editor({
            el: $editorElement,
            height: '500px',
            initialValue: $formElement.value,
            initialEditType: 'wysiwyg',
            previewStyle: 'vertical',
            theme: 'dark',
            usageStatistics: false,
            toolbarItems: [
                ['heading', 'bold', 'italic'],
                ['hr', 'quote'],
                ['ul', 'ol', 'indent', 'outdent'],
                ['image', 'table']
            ]
        });

        editor.on('change', function () {
            let markdown = editor.getMarkdown();
            $formElement.value = markdown;
            $formElement.innerHTML = markdown;

            $formElement.dispatchEvent(new Event('change', { bubbles: true }));
        });

        /**
         * Hide the image upload option in the editor to prevent large base64 media embeddings in Markdown documents.
         * Images should be linked and managed separately in the library for additional features.
         */
        window.addEventListener('click', (e) => {
            let element = document.querySelector('.toastui-editor-tabs .tab-item[aria-label="URL"]');
            if (element) {
                element.click();
            }

            if (document.querySelector('[aria-label="File"]')) {
                document.querySelector('[aria-label="File"]').style.display = "none"
            }
        })
    }
}
