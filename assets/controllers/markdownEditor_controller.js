import {Controller} from '@hotwired/stimulus';
import Editor from '@toast-ui/editor';
import '@toast-ui/editor/dist/toastui-editor.css';

import '../css/toastui-editor-dark.css';

export default class extends Controller {
    connect() {
        let $elementIdentifier = this.element.getAttribute('id');
        let $mainElement = document.getElementById($elementIdentifier);

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
                ['table']
            ]
        });

        editor.on('change', function () {
            $formElement.value = editor.getMarkdown();
        });
    }
}
