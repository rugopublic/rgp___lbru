/**
 * @file resources/scripts/components/ui/form/typeahead.js
 * @description Reusable browser component module for ui/form/typeahead.js; owns the client-side behavior of this UI primitive.
 */

import api from "../../../http/api";

class Typeahead {
    constructor(element) {
        this.element = element;

        this.endpoint = element.dataset.typeaheadEndpoint || null;

        this.input = element.querySelector("[data-typeahead-input]");
        this.valueInput = element.querySelector("[data-typeahead-value]");

        this.selected = element.querySelector("[data-typeahead-selected]");
        this.selectedLabel = element.querySelector(
            "[data-typeahead-selected-label]"
        );
        this.clearButton = element.querySelector("[data-typeahead-clear]");

        this.results = element.querySelector("[data-typeahead-results]");
        this.loading = element.querySelector("[data-typeahead-loading]");
        this.optionsContainer = element.querySelector(
            "[data-typeahead-options]"
        );
        this.empty = element.querySelector("[data-typeahead-empty]");

        this.initialOptions = this.readInitialOptions();

        this.requestController = null;
        this.searchTimer = null;
        this.blurTimer = null;
        this.activeIndex = -1;
        this.resultsData = [];
    }

    readInitialOptions() {
        const source = this.element.querySelector(
            "[data-typeahead-initial-options]"
        );

        if (!source) {
            return [];
        }

        try {
            return JSON.parse(source.textContent);
        } catch (error) {
            console.error("[Typeahead] Failed to parse initial options.", error);
            return [];
        }
    }

    initialize() {
        this.bindEvents();

        const value = this.valueInput.value;

        if (value === "") {
            return;
        }

        const initialOption = this.initialOptions.find(
            option => String(option.value) === String(value)
        );

        if (initialOption) {
            this.setSelected(initialOption, false);
            return;
        }

        if (this.endpoint) {
            this.resolveValue(value);
        }
    }

    bindEvents() {
        this.input.addEventListener("focus", () => {
            clearTimeout(this.blurTimer);

            this.openResults();

            if (this.input.value.trim() === "") {
                this.renderOptions(this.initialOptions);
            } else {
                this.search(this.input.value);
            }
        });

        this.input.addEventListener("input", () => {
            this.clearSelectionValue();
            this.search(this.input.value);
        });

        this.input.addEventListener("keydown", event => {
            this.handleKeydown(event);
        });

        this.input.addEventListener("blur", () => {
            this.blurTimer = setTimeout(() => {
                this.closeResults();
            }, 150);
        });

        this.clearButton.addEventListener("click", event => {
            event.preventDefault();
            event.stopPropagation();

            this.clearSelection();
        });
    }

    openResults() {
        this.results.hidden = false;
    }

    closeResults() {
        this.results.hidden = true;
        this.activeIndex = -1;
    }

    setLoading(state) {
        this.loading.hidden = !state;
        this.optionsContainer.hidden = state;
        this.empty.hidden = true;
    }

    renderOptions(options) {
        this.resultsData = Array.isArray(options) ? options : [];
        this.activeIndex = -1;

        this.loading.hidden = true;
        this.optionsContainer.replaceChildren();

        if (this.resultsData.length === 0) {
            this.optionsContainer.hidden = true;
            this.empty.hidden = false;
            return;
        }

        this.optionsContainer.hidden = false;
        this.empty.hidden = true;

        for (const option of this.resultsData) {
            const button = document.createElement("button");

            button.type = "button";
            button.className = "ui-form-typeahead__option";
            button.textContent = option.label;
            button.dataset.value = option.value;

            button.addEventListener("mousedown", event => {
                event.preventDefault();
            });

            button.addEventListener("click", () => {
                this.setSelected(option);
            });

            this.optionsContainer.append(button);
        }
    }

    search(query) {
        clearTimeout(this.searchTimer);

        const normalizedQuery = query.trim();

        if (!this.endpoint) {
            this.searchTimer = setTimeout(() => {
                const queryLower = normalizedQuery.toLocaleLowerCase();

                const results = this.initialOptions.filter(option =>
                    String(option.label)
                        .toLocaleLowerCase()
                        .includes(queryLower)
                );

                this.renderOptions(results);
            }, 100);

            return;
        }

        this.searchTimer = setTimeout(() => {
            this.fetchOptions(normalizedQuery);
        }, 250);
    }

    async fetchOptions(query) {
        if (this.requestController) {
            this.requestController.abort();
        }

        this.requestController = new AbortController();

        this.openResults();
        this.setLoading(true);

        try {
            const response = await api.get(this.endpoint, {
                params: {
                    q: query,
                },
                signal: this.requestController.signal,
            });

            this.renderOptions(
                Array.isArray(response.data)
                    ? response.data
                    : response.data.results ?? []
            );
        } catch (error) {
            if (error.name === "CanceledError" || error.code === "ERR_CANCELED") {
                return;
            }

            console.error("[Typeahead] Search failed.", error);

            this.renderOptions([]);
        } finally {
            this.requestController = null;
        }
    }

    async resolveValue(value) {
        if (!this.endpoint) {
            return;
        }

        this.openResults();
        this.setLoading(true);

        try {
            const response = await api.get(this.endpoint, {
                params: {
                    id: value,
                },
            });

            const results = Array.isArray(response.data)
                ? response.data
                : response.data.results ?? [];

            const option = results.find(
                item => String(item.value) === String(value)
            );

            if (option) {
                this.setSelected(option, false);
            } else {
                this.clearSelectionValue();
            }
        } catch (error) {
            console.error("[Typeahead] Failed to resolve selected value.", error);

            this.clearSelectionValue();
        } finally {
            this.closeResults();
        }
    }

    setSelected(option, dispatchEvent = true) {
        this.valueInput.value = option.value;
        this.selectedLabel.textContent = option.label;

        this.selected.classList.add("is-visible");
        this.input.classList.add("has-selection");

        this.input.value = "";
        this.closeResults();

        if (dispatchEvent) {
            this.dispatchValueEvent();
        }
    }

    clearSelectionValue() {
        this.valueInput.value = "";

        this.selected.classList.remove("is-visible");
        this.input.classList.remove("has-selection");

        this.selectedLabel.textContent = "";
    }

    clearSelection() {
        this.clearSelectionValue();

        this.input.focus();

        this.openResults();
        this.renderOptions(this.initialOptions);

        this.dispatchValueEvent();
    }

    dispatchValueEvent() {
        this.valueInput.dispatchEvent(
            new Event("input", {
                bubbles: true,
            })
        );

        this.valueInput.dispatchEvent(
            new Event("change", {
                bubbles: true,
            })
        );
    }

    handleKeydown(event) {
        if (this.results.hidden) {
            return;
        }

        if (event.key === "ArrowDown") {
            event.preventDefault();
            this.moveActive(1);
        }

        if (event.key === "ArrowUp") {
            event.preventDefault();
            this.moveActive(-1);
        }

        if (event.key === "Enter") {
            event.preventDefault();

            if (this.activeIndex >= 0) {
                this.setSelected(
                    this.resultsData[this.activeIndex]
                );
            }
        }

        if (event.key === "Escape") {
            event.preventDefault();
            this.closeResults();
        }
    }

    moveActive(direction) {
        if (this.resultsData.length === 0) {
            return;
        }

        this.activeIndex += direction;

        if (this.activeIndex < 0) {
            this.activeIndex = this.resultsData.length - 1;
        }

        if (this.activeIndex >= this.resultsData.length) {
            this.activeIndex = 0;
        }

        const buttons = this.optionsContainer.querySelectorAll(
            "[data-value]"
        );

        buttons.forEach((button, index) => {
            button.classList.toggle(
                "is-active",
                index === this.activeIndex
            );
        });

        buttons[this.activeIndex]?.scrollIntoView({
            block: "nearest",
        });
    }
}

document.addEventListener("DOMContentLoaded", () => {
    document
        .querySelectorAll("[data-typeahead]")
        .forEach(element => {
            try {
                const typeahead = new Typeahead(element);

                typeahead.initialize();

                element.__typeahead = typeahead;
            } catch (error) {
                console.error(
                    "[Typeahead] Failed to initialize.",
                    error
                );
            }
        });
});
