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

        const addFullscreenButton = function createLastButton()
        {
            const button = document.createElement('button');

            button.className             = 'toastui-editor-toolbar-icons last';
            button.style.backgroundImage = 'none';
            button.style.margin          = '-2px -1px';
            button.style.position        = 'fixed';
            button.style.color           = 'white';
            button.innerHTML             = `<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" class="icon icon-tabler icons-tabler-outline icon-tabler-link" style="vertical-align: middle; --tblr-icon-size: 1.5rem;" viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 4h4v4m-6 2l6-6M8 20H4v-4m0 4l6-6m6 6h4v-4m-6-2l6 6M8 4H4v4m0-4l6 6"/></svg>`;

            button.addEventListener('click', (event) => {
                event.preventDefault();

                let el = $editorElement;

                if (el.style.height !== "100vh") {
                    el.style = "height:100vh; width:100vw; position:fixed;z-index:10000000000;top:0px;left:0px;background-color:white;";
                } else {
                    el.style = "height:500px;"
                }
            });

            return button;
        }

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
                ['image', 'table'],
                [{ el: addFullscreenButton(), tooltip: 'Toggle Fullscreen' }]
            ]
        });

        editor.on('change', function () {
            let markdown           = editor.getMarkdown();
            $formElement.value     = markdown;
            $formElement.innerHTML = markdown;

            $formElement.dispatchEvent(new Event('change', {bubbles: true}));
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
