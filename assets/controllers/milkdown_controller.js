import {Controller} from '@hotwired/stimulus';

import { Crepe } from 'https://cdn.jsdelivr.net/npm/@milkdown/crepe@7.5.7/+esm';


export default class extends Controller {
    connect() {
        console.log('Connect Crepe Editor');
        console.log(this.element);
        console.log(this.element.getAttribute('id'));

        const crepe = new Crepe({
            root: this.element,
            defaultValue: 'Hello, Milkdown!',
        });

        crepe.create().then(() => {
            console.log('Crepe Editor is ready to use!');
        });
    }
}
