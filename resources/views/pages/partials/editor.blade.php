<style>
    .ck-editor__editable_inline {
        min-height: 24rem;
    }
</style>

<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const editorElement = document.querySelector('#content');
        const uploadUrl = @json(route('dashboard.pages.upload-image'));
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        if (! editorElement || typeof ClassicEditor === 'undefined') {
            return;
        }

        function PageImageUploadAdapter(loader) {
            this.loader = loader;
        }

        PageImageUploadAdapter.prototype.upload = function () {
            return this.loader.file.then(function (file) {
                return new Promise(function (resolve, reject) {
                    const formData = new FormData();
                    const xhr = new XMLHttpRequest();

                    formData.append('upload', file);

                    xhr.open('POST', uploadUrl, true);
                    xhr.responseType = 'json';

                    if (csrfToken) {
                        xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
                    }

                    xhr.addEventListener('error', function () {
                        reject('Image upload failed.');
                    });

                    xhr.addEventListener('abort', function () {
                        reject('Image upload aborted.');
                    });

                    xhr.addEventListener('load', function () {
                        const response = xhr.response;

                        if (xhr.status < 200 || xhr.status >= 300 || ! response || ! response.url) {
                            reject(response?.message || 'Image upload failed.');

                            return;
                        }

                        resolve({
                            default: response.url,
                        });
                    });

                    xhr.send(formData);
                });
            });
        };

        PageImageUploadAdapter.prototype.abort = function () {
            return;
        };

        function PageImageUploadAdapterPlugin(editor) {
            editor.plugins.get('FileRepository').createUploadAdapter = function (loader) {
                return new PageImageUploadAdapter(loader);
            };
        }

        ClassicEditor
            .create(editorElement, {
                extraPlugins: [PageImageUploadAdapterPlugin],
                toolbar: [
                    'heading', '|',
                    'bold', 'italic', 'link', '|',
                    'bulletedList', 'numberedList', '|',
                    'uploadImage', 'blockQuote', 'insertTable', '|',
                    'undo', 'redo'
                ],
                image: {
                    toolbar: [
                        'imageTextAlternative', '|',
                        'imageStyle:inline',
                        'imageStyle:block',
                        'imageStyle:side'
                    ]
                }
            })
            .then(function (editor) {
                const form = editorElement.closest('form');

                if (! form) {
                    return;
                }

                form.addEventListener('submit', function () {
                    editor.updateSourceElement();
                });
            })
            .catch(function (error) {
                console.error('CKEditor failed to initialize.', error);
            });
    });
</script>
