/**
 * @file resources/scripts/http/tools/fileUploader.js
 * @description HTTP client module; centralizes API requests, upload transport and browser-facing request behavior.
 */

import config from "../../config/app";
import api from "../api";

const fileUploader = {
    /**
     * Yields file chunks sequentially.
     *
     * @param {File} file - The file object from an input field.
     * @param {number} chunkSize - Size of each chunk in bytes.
     * @returns {Generator<Blob>}
     */
    *_chunks(file, chunkSize)
    {
        for (let start = 0; start < file.size; start += chunkSize) {
            yield file.slice(
                start,
                Math.min(start + chunkSize, file.size)
            );
        }
    },

    /**
     * Calculates the maximum time allowed for an upload request.
     *
     * The timeout depends on the total size of the original file,
     * because the final chunk request may also perform server-side
     * assembly of the complete upload.
     *
     * @param {File} file
     * @returns {number} Timeout in milliseconds.
     */
    _getTimeout(file)
    {
        const { base, perGiB } = config.upload.timeout;

        const gib = Math.ceil(file.size / 1024 ** 3);

        return base + gib * perGiB;
    },

    /**
     * Uploads a file sequentially in chunks.
     *
     * @param {File} file
     * @param {number} chunkSize
     * @param {Object} options
     * @param {(progress: {percentage: number}) => void} [options.onProgress]
     * @returns {Promise<{upload_id: string}>}
     */
    async _uploadChunks(file, chunkSize, options = {})
    {
        const chunksTotal = Math.ceil(file.size / chunkSize);
        const timeout = this._getTimeout(file);

        let uploadId = null;
        let chunkNumber = 1;

        for (const chunk of this._chunks(file, chunkSize)) {
            const formData = new FormData();

            formData.append('file', chunk, file.name);
            formData.append('chunk_number', chunkNumber);
            formData.append('chunks_total', chunksTotal);

            if (uploadId !== null) {
                formData.append('upload_id', uploadId);
            }

            const response = await api.post('/upload', formData, {
                timeout,

                onUploadProgress: event => {
                    if (!event.total) {
                        return;
                    }

                    const uploadedBytes =
                        (chunkNumber - 1) * chunkSize + event.loaded;

                    const percentage =
                        uploadedBytes / file.size * 100;

                    options.onProgress?.({
                        percentage: Math.min(percentage, 100),
                    });
                },
            });

            uploadId = response.data.upload_id;

            chunkNumber++;
        }

        return { upload_id: uploadId };
    },

    /**
     * Uploads a file in a single request.
     *
     * @param {File} file
     * @param {Object} options
     * @param {(progress: {percentage: number}) => void} [options.onProgress]
     * @returns {Promise<{upload_id: string}>}
     */
    async _uploadWhole(file, options = {})
    {
        const formData = new FormData();

        formData.append('file', file);

        const response = await api.post('/upload', formData, {
            timeout: this._getTimeout(file),

            onUploadProgress: event => {
                if (!event.total) {
                    return;
                }

                options.onProgress?.({
                    percentage: event.loaded / event.total * 100,
                });
            },
        });

        return {
            upload_id: response.data.upload_id,
        };
    },

    /**
     * Uploads a file to the backend.
     *
     * Selects the upload strategy automatically:
     * - whole upload for files up to the configured chunk size;
     * - chunked upload for larger files.
     *
     * @param {File} file
     * @param {Object} options
     * @param {(progress: {percentage: number}) => void} [options.onProgress]
     * @returns {Promise<{upload_id: string}>}
     */
    async upload(file, options = {})
    {
        const chunkSize = config.upload.chunkSize;

        return file.size <= chunkSize
            ? this._uploadWhole(file, options)
            : this._uploadChunks(file, chunkSize, options);
    },
};

export default fileUploader;
