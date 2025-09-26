import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

const randomUuid = () => {
    if (window.crypto?.randomUUID) {
        return window.crypto.randomUUID();
    }

    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (c) => {
        const r = (Math.random() * 16) | 0;
        const v = c === 'x' ? r : (r & 0x3) | 0x8;
        return v.toString(16);
    });
};

const formatTemplate = (template, replacements) => {
    return Object.entries(replacements).reduce((carry, [key, value]) => {
        return carry.replace(new RegExp(`:${key}`, 'g'), value);
    }, template);
};

const setupChunkUpload = (form) => {
    const input = form.querySelector('[data-chunk-input]');
    const submitButton = form.querySelector('button[type="submit"]');
    const errorElement = form.querySelector('[data-upload-error]');
    const statusContainer = form.parentElement.querySelector('[data-upload-status]');
    const csrfToken = form.querySelector('input[name="_token"]').value;

    const limit = Number.parseInt(form.dataset.limit ?? '5', 10);
    let currentCount = Number.parseInt(form.dataset.existingCount ?? '0', 10);
    const maxSize = Number.parseInt(form.dataset.maxSize ?? (120 * 1024 * 1024), 10);
    const sizeTemplate = form.dataset.sizeTemplate ?? '';
    const limitMessage = form.dataset.limitMessage ?? '';

    const templates = {
        ready: form.dataset.readyTemplate ?? 'Ready to upload: :name',
        uploading: form.dataset.uploadingTemplate ?? 'Uploading :name (:percent%)',
        complete: form.dataset.completeTemplate ?? 'Upload complete: :name',
        error: form.dataset.errorTemplate ?? 'Upload failed: :name',
    };

    const chunkSize = 5 * 1024 * 1024;

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        errorElement?.classList.add('hidden');
        if (errorElement) {
            errorElement.textContent = '';
        }

        if (!input?.files?.length) {
            return;
        }

        const files = Array.from(input.files);
        const allowed = limit - currentCount;

        if (files.length > allowed) {
            if (errorElement) {
                errorElement.textContent = limitMessage || 'Maximum limit reached.';
                errorElement.classList.remove('hidden');
            }
            return;
        }

        const oversize = files.find((file) => file.size > maxSize);
        if (oversize) {
            if (errorElement) {
                const limitMb = Math.floor(maxSize / (1024 * 1024));
                errorElement.textContent = formatTemplate(sizeTemplate || 'File too large (:limit MB max).', {
                    limit: String(limitMb),
                });
                errorElement.classList.remove('hidden');
            }
            return;
        }

        if (submitButton) {
            submitButton.disabled = true;
            submitButton.classList.add('opacity-50', 'cursor-not-allowed');
        }
        const hintElement = form.querySelector('[data-upload-hint]');
        hintElement?.classList.add('text-slate-500');

        const uploadResults = [];

        for (const file of files) {
            const statusItem = document.createElement('div');
            statusItem.className = 'border border-slate-200 rounded px-3 py-2 text-sm bg-white shadow-sm';
            statusItem.textContent = formatTemplate(templates.ready, { name: file.name });
            statusContainer?.appendChild(statusItem);

            const uploadUuid = randomUuid();
            const totalChunks = Math.max(Math.ceil(file.size / chunkSize), 1);
            let success = true;

            for (let index = 0; index < totalChunks; index += 1) {
                const start = index * chunkSize;
                const chunk = file.slice(start, start + chunkSize);
                const percent = Math.min(100, Math.round(((index + 1) / totalChunks) * 100));

                statusItem.textContent = formatTemplate(templates.uploading, {
                    name: file.name,
                    percent: String(percent),
                });

                const formData = new FormData();
                formData.append('_token', csrfToken);
                formData.append('file', chunk, file.name);
                formData.append('filename', file.name);
                formData.append('upload_uuid', uploadUuid);
                formData.append('chunk_index', String(index));
                formData.append('chunk_total', String(totalChunks));

                try {
                    const response = await fetch(form.dataset.endpoint ?? '', {
                        method: 'POST',
                        headers: {
                            Accept: 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: formData,
                    });

                    if (response.status === 202) {
                        continue;
                    }

                    if (!response.ok) {
                        const data = await response.json().catch(() => ({}));
                        const message = data?.message
                            || Object.values(data?.errors ?? {})
                                .flat()
                                .join(' ')
                            || formatTemplate(templates.error, { name: file.name });
                        statusItem.textContent = message;
                        statusItem.classList.add('border-red-300', 'text-red-600');
                        success = false;
                        break;
                    }
                } catch (error) {
                    statusItem.textContent = formatTemplate(templates.error, { name: file.name });
                    statusItem.classList.add('border-red-300', 'text-red-600');
                    success = false;
                    break;
                }
            }

            if (success) {
                statusItem.textContent = formatTemplate(templates.complete, { name: file.name });
                statusItem.classList.add('border-green-300', 'text-green-700');
                currentCount += 1;
            } else {
                uploadResults.push(false);
            }
        }

        input.value = '';

        if (submitButton) {
            submitButton.disabled = false;
            submitButton.classList.remove('opacity-50', 'cursor-not-allowed');
        }

        if (!uploadResults.includes(false)) {
            window.setTimeout(() => {
                window.location.reload();
            }, 800);
        }
    });
};

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-chunk-upload]').forEach((form) => {
        setupChunkUpload(form);
    });
});
