import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        url: String,
        accept: Array,
    };

    static targets = ['body', 'rowTemplate', 'file', 'dropzone'];

    delete(event) {
        event.target.closest('tr').remove();
    }

    open() {
        this.fileTarget.click();
    }

    change(event) {
        this.#addFile(event.target.files);
        event.target.value = '';
    }

    dragover(event) {
        if (!this.#isFiles(event.dataTransfer)) {
            return;
        }

        event.preventDefault();
        event.stopPropagation();
        clearTimeout(this.dropTimer);
        this.dropzoneTarget.style.display = '';
    }

    activate(event) {
        if (!this.#isFiles(event.dataTransfer)) {
            return;
        }

        clearTimeout(this.dropTimer);
        this.dropTarget = event.target;
        // event.preventDefault();
        // event.stopPropagation();
        this.dropzoneTarget.style.display = '';
    }

    deactivate(event) {
        if (!this.#isFiles(event.dataTransfer)) {
            return;
        }

        if (event.target === this.dropTarget || event.target === document) {
            // event.preventDefault();
            // event.stopPropagation();
            this.dropTimer = setTimeout(() => {
                this.dropzoneTarget.style.display = 'none';
            }, 100);
        }
    }

    drop() {
        this.dropzoneTarget.style.display = 'none';
    }

    updateSorting() {
        Array.from(this.bodyTarget.children).forEach((tr, i) => {
            tr.querySelectorAll('input, select, textarea').forEach((el) => {
                if (el.id) el.id = el.id.replace(/_[0-9]+_([a-z])/g, `_${i}_$1`);
                el.name = el.name.replace(/\[[0-9]+]/g, `[${i}]`);
            });
            tr.querySelectorAll('label').forEach((el) => {
                el.setAttribute('for', el.getAttribute('for').replace(/_[0-9]+_([a-z])/g, `_${i}_$1`));
            });
        });
    }

    #addFile(files) {
        [...files].forEach(async (file) => {
            if (!this.#isAccepted(file)) {
                return;
            }

            this.bodyTarget.insertAdjacentHTML('beforeend', this.rowTemplateTarget.innerHTML);
            const row = this.bodyTarget.lastElementChild;

            const formdata = new FormData();
            formdata.append('file', file);

            const response = await fetch(this.urlValue, {
                method: 'POST',
                body: formdata,
                redirect: 'manual',
            });

            if (response.status !== 200) {
                window.location.reload();
                return;
            }

            const data = await response.json();

            if (data.success) {
                row.querySelector('td.image img').src = data.file;
                row.querySelector('td.image a').href = data.file;
                row.querySelector('td.image input[type="hidden"]').value = data.file;
            } else {
                alert(data.error);
                row.remove();
            }
        });

        this.updateSorting();
    }

    #isAccepted(file) {
        const extension = file.name.substring(file.name.lastIndexOf('.')).toLowerCase();

        return this.acceptValue.length > 0 && this.acceptValue.contains(extension);
    }

    #isFiles(dataTransfer) {
        return dataTransfer.types && dataTransfer.types.length === 1 && dataTransfer.types[0] === 'Files';
    }
}
