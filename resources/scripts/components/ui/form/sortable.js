/**
 * @file resources/scripts/components/ui/form/sortable.js
 * @description Reusable browser component module for ui/form/sortable.js; owns the client-side behavior of this UI primitive.
 * @exports SortableField, initSortable, getSortable, initSortables
 */

import SortableJS from 'sortablejs';
import api from '../../../http/api';

/**
 * Generic hierarchical Sortable form field.
 *
 * Supported structure:
 *
 * Ungrouped:
 *
 * Sortable
 * ├── root items
 * │   ├── Item
 * │   └── Item
 * │
 * └── groups
 *     ├── Group
 *     │   ├── Item
 *     │   └── Item
 *     └── Group
 *         └── Item
 *
 * Grouped:
 *
 * Sortable
 * └── groups
 *     ├── Group
 *     │   ├── Item
 *     │   └── Item
 *     └── Group
 *         └── Item
 *
 * The component is intentionally domain-agnostic.
 *
 * Sortable is responsible for:
 *
 * - group ordering;
 * - item ordering;
 * - moving items between root and groups;
 * - moving items between groups;
 * - group creation/removal;
 * - item creation/removal;
 * - group title editing;
 * - serialization of structural state;
 * - exposing component events;
 * - synchronizing the hidden form input;
 * - optional initial-state loading from an endpoint.
 *
 * Domain-specific logic belongs outside this component.
 *
 * Serialized state:
 *
 * {
 *     groups: [
 *         {
 *             id: 'group-1',
 *             label: 'Group',
 *             position: 0,
 *             items: [
 *                 {
 *                     id: 'item-1',
 *                     value: '17',
 *                     label: 'Item',
 *                     data: {
 *                         amount: 300,
 *                         unit: 'g',
 *                     },
 *                     position: 0,
 *                 },
 *             ],
 *         },
 *     ],
 *
 *     items: [
 *         {
 *             id: 'item-2',
 *             value: '42',
 *             label: 'Root item',
 *             data: {},
 *             position: 0,
 *         },
 *     ],
 * }
 *
 * When grouped === true:
 *
 * {
 *     groups: [
 *         {
 *             id: 'group-1',
 *             label: 'Group',
 *             position: 0,
 *             items: [],
 *         },
 *     ],
 *     items: [],
 * }
 *
 * Initial state priority:
 *
 * 1. Non-empty hidden input value.
 * 2. Configured endpoint.
 * 3. Existing server-rendered DOM.
 *
 * Endpoint response may be:
 *
 * {
 *     groups: [],
 *     items: [],
 * }
 *
 * or:
 *
 * {
 *     data: {
 *         groups: [],
 *         items: [],
 *     },
 * }
 *
 * or:
 *
 * {
 *     success: true,
 *     data: {
 *         groups: [],
 *         items: [],
 *     },
 * }
 */
export class SortableField {
    /**
     * @param {HTMLElement} root
     */
    constructor(root) {
        /**
         * Root Sortable element.
         *
         * @type {HTMLElement}
         */
        this.root = root;

        /**
         * Hidden input containing serialized state.
         *
         * @type {HTMLInputElement|null}
         */
        this.input = root.querySelector(
            '[data-sortable-value]'
        );

        /**
         * Root item container.
         *
         * It is absent when grouped === true.
         *
         * @type {HTMLElement|null}
         */
        this.rootItemsRoot = root.querySelector(
            '[data-sortable-root-items]'
        );

        /**
         * Group container.
         *
         * @type {HTMLElement|null}
         */
        this.groupsRoot = root.querySelector(
            '[data-sortable-groups]'
        );

        /**
         * Optional endpoint used to load initial state.
         *
         * @type {string|null}
         */
        this.endpoint =
            root.dataset.sortableEndpoint || null;

        /**
         * Form field name.
         *
         * @type {string}
         */
        this.name =
            root.dataset.sortableName ||
            this.input?.name ||
            '';

        /**
         * Determines whether all items must belong to a group.
         *
         * When true:
         *
         * - no root item container is expected;
         * - root items are ignored from incoming state;
         * - serialized root items are always empty.
         *
         * @type {boolean}
         */
        this.grouped =
            root.dataset.sortableGrouped === 'true';

        /**
         * SortableJS instance responsible for groups.
         *
         * @type {Sortable|null}
         */
        this.groupsSortable = null;

        /**
         * SortableJS instance responsible for root items.
         *
         * @type {Sortable|null}
         */
        this.rootItemsSortable = null;

        /**
         * SortableJS instances for group item containers.
         *
         * @type {Map<HTMLElement, Sortable>}
         */
        this.itemSortables = new Map();

        /**
         * SortableJS group name shared by root and group
         * item containers of this component.
         *
         * A per-component name prevents separate Sortable
         * components from exchanging items.
         *
         * @type {string}
         */
        this.itemGroupName =
            `ui-sortable-items-${root.id || this.generateId()}`;

        /**
         * Initialization state.
         *
         * @type {boolean}
         */
        this.initialized = false;

        /**
         * Prevent concurrent initialization.
         *
         * @type {boolean}
         */
        this.initializing = false;

        /**
         * Per-item field state.
         *
         * @type {WeakMap<HTMLElement, Map<string, unknown>>}
         */
        this.itemFieldValues = new WeakMap();

        /**
         * Bound delegated click handler.
         *
         * @type {((event: Event) => void)|null}
         */
        this.boundClickHandler = null;

        /**
         * Bound delegated input handler.
         *
         * @type {((event: Event) => void)|null}
         */
        this.boundInputHandler = null;

        /**
         * Bound delegated change handler.
         *
         * @type {((event: Event) => void)|null}
         */
        this.boundChangeHandler = null;
    }

    /**
     * Initialize the component.
     *
     * @returns {Promise<SortableField>}
     */
    async init() {
        if (
            this.initialized ||
            this.initializing
        ) {
            return this;
        }

        this.initializing = true;

        try {
            let state =
                this.readInitialValue();

            /**
             * A non-empty hidden value is authoritative.
             * Otherwise use the configured endpoint.
             */
            if (
                !this.hasState(state) &&
                this.endpoint
            ) {
                state = await this.load();
            }

            /**
             * Render actual state only.
             *
             * Empty endpoint state does not destroy existing
             * server-rendered DOM.
             */
            if (this.hasState(state)) {
                this.renderState(state);
            }

            this.prepareExistingItems();
            this.prepareExistingGroups();

            this.initRootItemsSortable();
            this.initGroupsSortable();
            this.initGroupItemSortables();

            this.bindEvents();
            this.updateEmptyStates();

            /**
             * Synchronize the hidden input silently.
             */
            this.sync(false);

            this.initialized = true;
        } catch (error) {
            this.dispatch(
                'sortable:error',
                {
                    error,
                    phase: 'init',
                }
            );

            throw error;
        } finally {
            this.initializing = false;
        }

        return this;
    }

    /**
     * Determine whether a normalized state contains
     * meaningful data for this component.
     *
     * When grouped is true, only groups are relevant.
     *
     * @param {{groups: Array, items: Array}|null} state
     * @returns {boolean}
     */
    hasState(state) {
        if (!state) {
            return false;
        }

        if (this.grouped) {
            return (
                Array.isArray(state.groups) &&
                state.groups.length > 0
            );
        }

        return Boolean(
            (
                Array.isArray(state.groups) &&
                state.groups.length > 0
            ) ||
            (
                Array.isArray(state.items) &&
                state.items.length > 0
            )
        );
    }

    /**
     * Read the initial state from the hidden input.
     *
     * @returns {{groups: Array, items: Array}|null}
     */
    readInitialValue() {
        if (!this.input?.value) {
            return null;
        }

        try {
            const parsed =
                JSON.parse(
                    this.input.value
                );

            return this.normalizeState(
                parsed
            );
        } catch (error) {
            this.dispatch(
                'sortable:error',
                {
                    error,
                    phase:
                        'parse-initial-value',
                }
            );

            return null;
        }
    }

    /**
     * Fetch initial state from the configured endpoint.
     *
     * @returns {Promise<{groups: Array, items: Array}|null>}
     */
    async load() {
        if (!this.endpoint) {
            return null;
        }

        try {
            const response =
                await api.get(
                    this.endpoint
                );

            return this.normalizeState(
                response.data
            );
        } catch (error) {
            this.dispatch(
                'sortable:error',
                {
                    error,
                    phase: 'load',
                }
            );

            return null;
        }
    }

    /**
     * Normalize supported API payload formats.
     *
     * @param {unknown} payload
     * @returns {{groups: Array, items: Array}}
     */
    normalizeState(payload) {
        let state = payload;

        /**
         * Supports:
         *
         * {
         *     data: {
         *         groups: [],
         *         items: [],
         *     }
         * }
         */
        if (
            state &&
            typeof state === 'object' &&
            'data' in state
        ) {
            state = state.data;
        }

        /**
         * Supports a plain array as a root-item collection.
         *
         * In grouped mode the root collection is discarded.
         */
        if (Array.isArray(state)) {
            return {
                groups: [],
                items: this.grouped
                    ? []
                    : state,
            };
        }

        if (
            !state ||
            typeof state !== 'object'
        ) {
            return {
                groups: [],
                items: [],
            };
        }

        return {
            groups:
                Array.isArray(state.groups)
                    ? state.groups
                    : [],

            items:
                this.grouped
                    ? []
                    : (
                        Array.isArray(
                            state.items
                        )
                            ? state.items
                            : []
                    ),
        };
    }

    /**
     * Render a generic state into the DOM.
     *
     * Root items are rendered only when the component
     * allows root items.
     *
     * @param {{groups: Array, items: Array}} state
     * @returns {void}
     */
    renderState(state) {
        if (this.groupsRoot) {
            this.groupsRoot.innerHTML = '';

            for (
                const group of state.groups
            ) {
                this.groupsRoot.appendChild(
                    this.createGroupElement(
                        group
                    )
                );
            }
        }

        if (
            !this.grouped &&
            this.rootItemsRoot
        ) {
            this.rootItemsRoot.innerHTML = '';

            for (
                const item of state.items
            ) {
                this.rootItemsRoot.appendChild(
                    this.createItemElement(
                        item
                    )
                );
            }
        }
    }

    /**
     * Create a generic group element.
     *
     * @param {Object} group
     * @returns {HTMLElement}
     */
    createGroupElement(group) {
        const element =
            document.createElement(
                'div'
            );

        element.id =
            group.id ||
            this.generateId(
                'sortable-group'
            );

        element.className =
            'ui-form-sortable__group';

        element.dataset.sortableGroup = '';

        element.dataset.sortableGroupId =
            String(
                group.id ??
                this.generateId(
                    'group'
                )
            );

        const header =
            document.createElement(
                'div'
            );

        header.className =
            'ui-form-sortable__group-header';

        const handle =
            document.createElement(
                'span'
            );

        handle.className =
            'ui-form-sortable__group-handle';

        handle.dataset
            .sortableGroupHandle = '';

        handle.setAttribute(
            'aria-hidden',
            'true'
        );

        handle.innerHTML =
            '<i class="bi bi-grip-vertical"></i>';

        const title =
            document.createElement(
                'span'
            );

        title.className =
            'ui-form-sortable__group-title';

        title.dataset
            .sortableGroupTitle = '';

        title.textContent =
            group.label ??
            'Новая группа';

        const actions =
            document.createElement(
                'div'
            );

        actions.className =
            'ui-form-sortable__group-actions';

        actions.innerHTML = `
            <button
                type="button"
                class="btn btn-link"
                data-sortable-group-edit
                aria-label="Изменить название группы"
            >
                <i class="bi bi-pencil"></i>
            </button>

            <button
                type="button"
                class="btn btn-link"
                data-sortable-group-add-item
                aria-label="Добавить элемент"
            >
                <i class="bi bi-plus-lg"></i>
            </button>

            <button
                type="button"
                class="btn btn-link"
                data-sortable-group-remove
                aria-label="Удалить группу"
            >
                <i class="bi bi-trash"></i>
            </button>
        `;

        header.append(
            handle,
            title,
            actions
        );

        const items =
            document.createElement(
                'div'
            );

        items.className =
            'ui-form-sortable__group-items';

        items.dataset.sortableItems = '';

        const groupItems =
            Array.isArray(
                group.items
            )
                ? group.items
                : [];

        for (
            const item of groupItems
        ) {
            items.appendChild(
                this.createItemElement(
                    item
                )
            );
        }

        element.append(
            header,
            items
        );

        return element;
    }

    /**
     * Create a generic item element.
     *
     * Domain-specific code may enrich the generated element
     * after sortable:item-add or replace its internal content.
     *
     * @param {Object} item
     * @returns {HTMLElement}
     */
    createItemElement(item = {}) {
        const element =
            document.createElement(
                'div'
            );

        element.id =
            item.id ||
            this.generateId(
                'sortable-item'
            );

        element.className =
            'ui-form-sortable__item';

        element.dataset.sortableItem = '';

        element.dataset
            .sortableItemValue =
            item.value ?? '';

        if (
            item.id !== undefined &&
            item.id !== null
        ) {
            element.dataset
                .sortableItemId =
                String(item.id);
        }

        const content =
            document.createElement(
                'div'
            );

        content.className =
            'ui-form-sortable__item-content';

        const handle =
            document.createElement(
                'span'
            );

        handle.className =
            'ui-form-sortable__item-handle';

        handle.dataset
            .sortableItemHandle = '';

        handle.setAttribute(
            'aria-hidden',
            'true'
        );

        handle.innerHTML =
            '<i class="bi bi-grip-vertical"></i>';

        const label =
            document.createElement(
                'div'
            );

        label.className =
            'ui-form-sortable__item-label';

        label.dataset
            .sortableItemLabel = '';

        label.dataset
            .sortableItemLabelValue =
            item.label ?? '';

        label.textContent =
            item.label ??
            'Новый элемент';

        content.append(
            handle,
            label
        );

        const remove =
            document.createElement(
                'button'
            );

        remove.type = 'button';

        remove.className =
            'btn btn-link';

        remove.dataset
            .sortableItemRemove = '';

        remove.setAttribute(
            'aria-label',
            'Удалить элемент'
        );

        remove.innerHTML =
            '<i class="bi bi-trash"></i>';

        element.append(
            content,
            remove
        );

        this.writeItemData(
            element,
            item.data ?? {}
        );

        this.prepareItem(
            element
        );

        return element;
    }

    /**
     * Prepare all existing items.
     *
     * @returns {void}
     */
    prepareExistingItems() {
        this.getAllItems().forEach(
            (item) => {
                this.prepareItem(
                    item
                );
            }
        );
    }

    /**
     * Prepare all existing groups.
     *
     * @returns {void}
     */
    prepareExistingGroups() {
        if (!this.groupsRoot) {
            return;
        }

        this.groupsRoot
            .querySelectorAll(
                ':scope > [data-sortable-group]'
            )
            .forEach(
                (group) => {
                    if (
                        !group.dataset
                            .sortableGroupId
                    ) {
                        group.dataset
                            .sortableGroupId =
                            this.generateId(
                                'group'
                            );
                    }
                }
            );
    }

    /**
     * Initialize root item SortableJS.
     *
     * Root items do not exist in grouped mode.
     *
     * @returns {void}
     */
    initRootItemsSortable() {
        if (
            this.grouped ||
            !this.rootItemsRoot ||
            this.rootItemsSortable
        ) {
            return;
        }

        this.rootItemsSortable =
            this.createItemSortable(
                this.rootItemsRoot
            );
    }

    /**
     * Initialize group SortableJS.
     *
     * Groups are sortable only inside the groups container.
     *
     * @returns {void}
     */
    initGroupsSortable() {
        if (
            !this.groupsRoot ||
            this.groupsSortable
        ) {
            return;
        }

        this.groupsSortable =
            new SortableJS(
                this.groupsRoot,
                {
                    animation: 150,

                    draggable:
                        '[data-sortable-group]',

                    handle:
                        '[data-sortable-group-handle]',

                    fallbackOnBody: true,

                    swapThreshold: 0.65,

                    group: {
                        name:
                            `ui-sortable-groups-${
                                this.root.id ||
                                this.generateId()
                            }`,

                        pull: false,

                        put: false,
                    },

                    onEnd: (event) => {
                        if (
                            event.oldIndex ===
                            event.newIndex
                        ) {
                            return;
                        }

                        this.dispatch(
                            'sortable:group-sort',
                            {
                                group:
                                    event.item,

                                oldIndex:
                                    event.oldIndex,

                                newIndex:
                                    event.newIndex,
                            }
                        );

                        this.sync();
                    },
                }
            );
    }

    /**
     * Initialize item SortableJS instances for all groups.
     *
     * @returns {void}
     */
    initGroupItemSortables() {
        if (!this.groupsRoot) {
            return;
        }

        this.groupsRoot
            .querySelectorAll(
                ':scope > [data-sortable-group]'
            )
            .forEach(
                (group) => {
                    this.initGroupItemSortable(
                        group
                    );
                }
            );
    }

    /**
     * Initialize one group's item SortableJS.
     *
     * @param {HTMLElement} group
     * @returns {Sortable|null}
     */
    initGroupItemSortable(group) {
        const itemsRoot =
            group.querySelector(
                '[data-sortable-items]'
            );

        if (!itemsRoot) {
            return null;
        }

        if (
            this.itemSortables.has(
                itemsRoot
            )
        ) {
            return this.itemSortables.get(
                itemsRoot
            );
        }

        const sortable =
            this.createItemSortable(
                itemsRoot,
                group
            );

        this.itemSortables.set(
            itemsRoot,
            sortable
        );

        return sortable;
    }

    /**
     * Create an item SortableJS instance.
     *
     * All item containers belonging to this component share
     * one SortableJS group.
     *
     * This permits:
     *
     * root ↔ group
     * group ↔ group
     *
     * while groups themselves remain a separate hierarchy.
     *
     * @param {HTMLElement} container
     * @param {HTMLElement|null} group
     * @returns {Sortable}
     */
    createItemSortable(
        container,
        group = null
    ) {
        return new SortableJS(
            container,
            {
                animation: 150,

                draggable:
                    '[data-sortable-item]',

                handle:
                    '[data-sortable-item-handle]',

                fallbackOnBody: true,

                swapThreshold: 0.65,

                group: {
                    name:
                        this.itemGroupName,

                    pull:
                        true,

                    put:
                        true,
                },

                onAdd: (event) => {
                    const item =
                        event.item;

                    this.prepareItem(
                        item
                    );

                    this.dispatch(
                        'sortable:item-add',
                        {
                            item,

                            group:
                                this.getItemGroup(
                                    item
                                ),

                            oldContainer:
                                event.from,

                            newContainer:
                                event.to,

                            oldIndex:
                                event.oldIndex,

                            newIndex:
                                event.newIndex,
                        }
                    );
                },

                onRemove: (event) => {
                    this.dispatch(
                        'sortable:item-remove',
                        {
                            item:
                                event.item,

                            group:
                                event.from
                                    ?.closest(
                                        '[data-sortable-group]'
                                    ) ??
                                null,

                            oldContainer:
                                event.from,

                            newContainer:
                                event.to,

                            oldIndex:
                                event.oldIndex,

                            newIndex:
                                event.newIndex,
                        }
                    );
                },

                onEnd: (event) => {
                    const sourceContainer =
                        event.from;

                    const targetContainer =
                        event.to;

                    const moved =
                        sourceContainer !==
                            targetContainer ||
                        event.oldIndex !==
                            event.newIndex;

                    if (moved) {
                        this.dispatch(
                            'sortable:item-sort',
                            {
                                item:
                                    event.item,

                                group:
                                    this.getItemGroup(
                                        event.item
                                    ),

                                oldIndex:
                                    event.oldIndex,

                                newIndex:
                                    event.newIndex,

                                oldContainer:
                                    sourceContainer,

                                newContainer:
                                    targetContainer,
                            }
                        );
                    }

                    this.updateEmptyStates();

                    this.sync();
                },
            }
        );
    }

    /**
     * Bind delegated component events.
     *
     * @returns {void}
     */
    bindEvents() {
        if (
            this.boundClickHandler
        ) {
            return;
        }

        this.boundClickHandler =
            (event) => {
                const target =
                    event.target instanceof Element
                        ? event.target
                        : null;

                if (!target) {
                    return;
                }

                const groupAdd =
                    target.closest(
                        '[data-sortable-group-add]'
                    );

                if (groupAdd) {
                    event.preventDefault();

                    this.addGroup();

                    return;
                }

                const groupEdit =
                    target.closest(
                        '[data-sortable-group-edit]'
                    );

                if (groupEdit) {
                    event.preventDefault();

                    const group =
                        groupEdit.closest(
                            '[data-sortable-group]'
                        );

                    if (group) {
                        this.editGroupTitle(
                            group
                        );
                    }

                    return;
                }

                const groupAddItem =
                    target.closest(
                        '[data-sortable-group-add-item]'
                    );

                if (groupAddItem) {
                    event.preventDefault();

                    const group =
                        groupAddItem.closest(
                            '[data-sortable-group]'
                        );

                    if (group) {
                        this.addItem(
                            group
                        );
                    }

                    return;
                }

                const groupRemove =
                    target.closest(
                        '[data-sortable-group-remove]'
                    );

                if (groupRemove) {
                    event.preventDefault();

                    const group =
                        groupRemove.closest(
                            '[data-sortable-group]'
                        );

                    if (group) {
                        this.removeGroup(
                            group
                        );
                    }

                    return;
                }

                const itemRemove =
                    target.closest(
                        '[data-sortable-item-remove]'
                    );

                if (itemRemove) {
                    event.preventDefault();

                    const item =
                        itemRemove.closest(
                            '[data-sortable-item]'
                        );

                    if (item) {
                        this.removeItem(
                            item
                        );
                    }
                }
            };

        this.boundInputHandler =
            (event) => {
                this.handleItemFieldEvent(
                    event
                );
            };

        this.boundChangeHandler =
            (event) => {
                this.handleItemFieldEvent(
                    event
                );
            };

        this.root.addEventListener(
            'click',
            this.boundClickHandler
        );

        this.root.addEventListener(
            'input',
            this.boundInputHandler
        );

        this.root.addEventListener(
            'change',
            this.boundChangeHandler
        );
    }

    /**
     * Handle changes from native controls mapped to item fields.
     *
     * Example:
     *
     * data-sortable-item-field="data.amount"
     *
     * File inputs are intentionally ignored.
     *
     * @param {Event} event
     * @returns {void}
     */
    handleItemFieldEvent(event) {
        const field =
            event.target;

        if (
            !(field instanceof HTMLElement)
        ) {
            return;
        }

        /**
         * Files are handled by FileUploader / ChangeTracker.
         */
        if (field.type === 'file') {
            return;
        }

        const fieldPath =
            field.dataset
                .sortableItemField;

        if (!fieldPath) {
            return;
        }

        const item =
            field.closest(
                '[data-sortable-item]'
            );

        if (
            !item ||
            !this.root.contains(item)
        ) {
            return;
        }

        if (
            !this.shouldHandleItemFieldEvent(
                field,
                event.type
            )
        ) {
            return;
        }

        const value =
            this.extractSortableFieldValue(
                field
            );

        if (value === undefined) {
            return;
        }

        this.setItemField(
            item,
            fieldPath,
            value
        );
    }

    /**
     * Determine which native event should synchronize a field.
     *
     * Text-like controls use input.
     * Selects, checkbox, radio and similar controls use change.
     *
     * @param {HTMLElement} field
     * @param {string} eventType
     * @returns {boolean}
     */
    shouldHandleItemFieldEvent(
        field,
        eventType
    ) {
        const tagName =
            field.tagName.toLowerCase();

        const type =
            field instanceof HTMLInputElement
                ? field.type
                : null;

        const textLike =
            tagName === 'textarea' ||
            (
                tagName === 'input' &&
                [
                    'text',
                    'search',
                    'email',
                    'url',
                    'tel',
                    'password',
                    'number',
                    'range',
                ].includes(type)
            );

        if (textLike) {
            return eventType === 'input';
        }

        return eventType === 'change';
    }

    /**
     * Extract a normalized value from a native form control.
     *
     * File inputs are intentionally unsupported here.
     *
     * @param {HTMLElement} field
     * @returns {*|undefined}
     */
    extractSortableFieldValue(field) {
        if (
            field instanceof HTMLInputElement
        ) {
            if (
                field.type === 'file'
            ) {
                return undefined;
            }

            if (
                field.type === 'checkbox'
            ) {
                return field.checked;
            }

            if (
                field.type === 'radio'
            ) {
                return field.checked
                    ? field.value
                    : undefined;
            }

            if (
                field.type === 'number' ||
                field.type === 'range'
            ) {
                return field.value === ''
                    ? ''
                    : (
                        Number.isNaN(
                            field.valueAsNumber
                        )
                            ? field.value
                            : field.valueAsNumber
                    );
            }

            return field.value;
        }

        if (
            field instanceof HTMLSelectElement
        ) {
            if (field.multiple) {
                return Array.from(
                    field.selectedOptions
                ).map(
                    (option) =>
                        option.value
                );
            }

            return field.value;
        }

        if (
            field instanceof HTMLTextAreaElement
        ) {
            return field.value;
        }

        return undefined;
    }

    /**
     * Add a new group at the beginning of the group collection.
     *
     * @returns {HTMLElement|null}
     */
    addGroup() {
        if (!this.groupsRoot) {
            return null;
        }

        const group =
            this.createGroupElement({
                id:
                    this.generateId(
                        'group'
                    ),

                label:
                    'Новая группа',

                items: [],
            });

        this.groupsRoot.prepend(
            group
        );

        this.initGroupItemSortable(
            group
        );

        this.updateEmptyStates();

        this.editGroupTitle(
            group
        );

        this.dispatch(
            'sortable:group-add',
            {
                group,
            }
        );

        this.sync();

        return group;
    }

    /**
     * Edit a group title inline.
     *
     * Enter and blur commit.
     * Escape restores the previous value.
     *
     * @param {HTMLElement} group
     * @returns {void}
     */
    editGroupTitle(group) {
        const title =
            group.querySelector(
                '[data-sortable-group-title]'
            );

        if (
            !title ||
            title.querySelector('input')
        ) {
            return;
        }

        const previousValue =
            title.textContent.trim();

        const input =
            document.createElement(
                'input'
            );

        input.type = 'text';

        input.className =
            'form-control form-control-sm';

        input.value =
            previousValue;

        title.replaceChildren(
            input
        );

        input.focus();
        input.select();

        let cancelled = false;
        let committed = false;

        const restore = () => {
            title.textContent =
                previousValue;
        };

        const commit = () => {
            if (
                cancelled ||
                committed
            ) {
                return;
            }

            committed = true;

            const value =
                input.value.trim() ||
                previousValue;

            title.textContent =
                value;

            if (
                value !==
                previousValue
            ) {
                this.dispatch(
                    'sortable:group-change',
                    {
                        group,

                        field:
                            'label',

                        value,

                        previousValue,
                    }
                );

                this.sync();
            }
        };

        input.addEventListener(
            'blur',
            commit,
            {
                once: true,
            }
        );

        input.addEventListener(
            'keydown',
            (event) => {
                if (
                    event.key === 'Enter'
                ) {
                    event.preventDefault();

                    input.blur();

                    return;
                }

                if (
                    event.key === 'Escape'
                ) {
                    event.preventDefault();

                    cancelled = true;

                    input.removeEventListener(
                        'blur',
                        commit
                    );

                    restore();
                }
            }
        );
    }

    /**
     * Add a generic item to a group.
     *
     * Domain-specific code can listen to sortable:item-add
     * and enrich the generated item.
     *
     * @param {HTMLElement} group
     * @returns {HTMLElement|null}
     */
    addItem(group) {
        const itemsRoot =
            group.querySelector(
                '[data-sortable-items]'
            );

        if (!itemsRoot) {
            return null;
        }

        const item =
            this.createItemElement({
                id:
                    this.generateId(
                        'item'
                    ),

                value:
                    '',

                label:
                    'Новый элемент',

                data:
                    {},
            });

        itemsRoot.appendChild(
            item
        );

        this.prepareItem(
            item
        );

        this.updateEmptyStates();

        this.dispatch(
            'sortable:item-add',
            {
                item,

                group,

                source:
                    'ui',
            }
        );

        this.sync();

        return item;
    }

    /**
     * Remove an item.
     *
     * @param {HTMLElement} item
     * @returns {void}
     */
    removeItem(item) {
        const group =
            this.getItemGroup(
                item
            );

        this.dispatch(
            'sortable:item-remove',
            {
                item,

                group,

                source:
                    'ui',
            }
        );

        item.remove();

        this.updateEmptyStates();

        this.sync();
    }

    /**
     * Remove a group and its contained items.
     *
     * @param {HTMLElement} group
     * @returns {void}
     */
    removeGroup(group) {
        this.dispatch(
            'sortable:group-remove',
            {
                group,
            }
        );

        const itemsRoot =
            group.querySelector(
                '[data-sortable-items]'
            );

        if (itemsRoot) {
            const sortable =
                this.itemSortables.get(
                    itemsRoot
                );

            if (sortable) {
                sortable.destroy();

                this.itemSortables.delete(
                    itemsRoot
                );
            }
        }

        group.remove();

        this.updateEmptyStates();

        this.sync();
    }

    /**
     * Set an item field value.
     *
     * Supported fields:
     *
     * value
     * label
     * data.amount
     * data.unit
     * data.enabled
     *
     * @param {HTMLElement} item
     * @param {string} field
     * @param {*} value
     * @returns {void}
     */
    setItemField(
        item,
        field,
        value
    ) {
        const previousValue =
            this.getItemField(
                item,
                field
            );

        if (field === 'value') {
            item.dataset
                .sortableItemValue =
                value ?? '';
        } else if (
            field === 'label'
        ) {
            const label =
                item.querySelector(
                    '[data-sortable-item-label]'
                );

            if (label) {
                label.dataset
                    .sortableItemLabelValue =
                    value ?? '';

                label.textContent =
                    value ?? '';
            }
        } else if (
            field.startsWith('data.')
        ) {
            const dataField =
                field.slice(5);

            this.writeItemDataField(
                item,
                dataField,
                value
            );
        }

        this.dispatch(
            'sortable:item-change',
            {
                item,

                group:
                    this.getItemGroup(
                        item
                    ),

                field,

                value,

                previousValue,
            }
        );

        this.sync();
    }

    /**
     * Get an item field value.
     *
     * @param {HTMLElement} item
     * @param {string} field
     * @returns {*}
     */
    getItemField(
        item,
        field
    ) {
        if (field === 'value') {
            return (
                item.dataset
                    .sortableItemValue ??
                ''
            );
        }

        if (field === 'label') {
            const label =
                item.querySelector(
                    '[data-sortable-item-label]'
                );

            if (!label) {
                return '';
            }

            return (
                label.dataset
                    .sortableItemLabelValue ??
                label.textContent.trim()
            );
        }

        if (
            field.startsWith('data.')
        ) {
            return this.readItemDataField(
                item,
                field.slice(5)
            );
        }

        return undefined;
    }

    /**
     * Read all data-item-data-* values from an item.
     *
     * @param {HTMLElement} item
     * @returns {Object<string, *>}
     */
    readItemData(item) {
        const data = {};

        for (
            const [key, value]
            of Object.entries(
                item.dataset
            )
        ) {
            if (
                !key.startsWith(
                    'itemData'
                )
            ) {
                continue;
            }

            const field =
                this.datasetKeyToField(
                    key.slice(
                        'itemData'.length
                    )
                );

            data[field] =
                this.parseDatasetValue(
                    value
                );
        }

        return data;
    }

    /**
     * Write item data fields.
     *
     * @param {HTMLElement} item
     * @param {Object<string, *>} data
     * @returns {void}
     */
    writeItemData(
        item,
        data
    ) {
        if (
            !data ||
            typeof data !== 'object'
        ) {
            return;
        }

        for (
            const [field, value]
            of Object.entries(
                data
            )
        ) {
            this.writeItemDataField(
                item,
                field,
                value
            );
        }
    }

    /**
     * Read one item data field.
     *
     * @param {HTMLElement} item
     * @param {string} field
     * @returns {*}
     */
    readItemDataField(
        item,
        field
    ) {
        const attribute =
            this.fieldToDataAttribute(
                field
            );

        const value =
            item.dataset[
                attribute
            ];

        return value === undefined
            ? undefined
            : this.parseDatasetValue(
                value
            );
    }

    /**
     * Write one item data field.
     *
     * @param {HTMLElement} item
     * @param {string} field
     * @param {*} value
     * @returns {void}
     */
    writeItemDataField(
        item,
        field,
        value
    ) {
        const attribute =
            this.fieldToDataAttribute(
                field
            );

        if (
            value === null ||
            value === undefined
        ) {
            delete item.dataset[
                attribute
            ];

            return;
        }

        item.dataset[
            attribute
        ] =
            this.stringifyDatasetValue(
                value
            );
    }

    /**
     * Convert a logical data field name to a dataset key.
     *
     * Example:
     *
     * amount -> itemDataAmount
     *
     * @param {string} field
     * @returns {string}
     */
    fieldToDataAttribute(field) {
        return (
            `itemData${field
                .replace(
                    /[-_]+(.)/g,
                    (_, char) =>
                        char.toUpperCase()
                )
                .replace(
                    /^(.)/,
                    (char) =>
                        char.toUpperCase()
                )}`
        );
    }

    /**
     * Convert a dataset key suffix back to a logical field name.
     *
     * @param {string} value
     * @returns {string}
     */
    datasetKeyToField(value) {
        return value.replace(
            /^(.)/,
            (char) =>
                char.toLowerCase()
        );
    }

    /**
     * Parse a data-* value.
     *
     * @param {string} value
     * @returns {*}
     */
    parseDatasetValue(value) {
        if (value === 'true') {
            return true;
        }

        if (value === 'false') {
            return false;
        }

        if (value === 'null') {
            return null;
        }

        if (
            value !== '' &&
            Number.isFinite(
                Number(value)
            )
        ) {
            return Number(value);
        }

        if (
            (
                value.startsWith('{') &&
                value.endsWith('}')
            ) ||
            (
                value.startsWith('[') &&
                value.endsWith(']')
            )
        ) {
            try {
                return JSON.parse(
                    value
                );
            } catch {
                /**
                 * Invalid JSON remains a string.
                 */
            }
        }

        return value;
    }

    /**
     * Serialize a data-* value.
     *
     * @param {*} value
     * @returns {string}
     */
    stringifyDatasetValue(value) {
        if (
            value !== null &&
            typeof value === 'object'
        ) {
            return JSON.stringify(
                value
            );
        }

        return String(value);
    }

    /**
     * Get all items inside this Sortable.
     *
     * @returns {HTMLElement[]}
     */
    getAllItems() {
        if (!this.root) {
            return [];
        }

        return [
            ...this.root.querySelectorAll(
                '[data-sortable-item]'
            ),
        ];
    }

    /**
     * Determine the group containing an item.
     *
     * Root items return null.
     *
     * @param {HTMLElement} item
     * @returns {HTMLElement|null}
     */
    getItemGroup(item) {
        return item.closest(
            '[data-sortable-group]'
        );
    }

    /**
     * Prepare one item for state management.
     *
     * @param {HTMLElement} item
     * @returns {void}
     */
    prepareItem(item) {
        if (
            !this.itemFieldValues.has(
                item
            )
        ) {
            this.itemFieldValues.set(
                item,
                new Map()
            );
        }
    }

    /**
     * Serialize items from one container.
     *
     * Only direct child items are included.
     *
     * @param {HTMLElement|null} container
     * @returns {Array<Object>}
     */
    serializeItems(container) {
        if (!container) {
            return [];
        }

        return [
            ...container.querySelectorAll(
                ':scope > [data-sortable-item]'
            ),
        ].map(
            (item, position) => ({
                id:
                    item.dataset
                        .sortableItemId ??
                    item.id ??
                    null,

                value:
                    item.dataset
                        .sortableItemValue ??
                    '',

                label:
                    this.getItemLabel(
                        item
                    ),

                data:
                    this.readItemData(
                        item
                    ),

                position,
            })
        );
    }

    /**
     * Obtain the logical item label.
     *
     * data-sortable-item-label-value has priority,
     * so complex item markup does not affect serialization.
     *
     * @param {HTMLElement} item
     * @returns {string}
     */
    getItemLabel(item) {
        const label =
            item.querySelector(
                '[data-sortable-item-label]'
            );

        if (!label) {
            return '';
        }

        return (
            label.dataset
                .sortableItemLabelValue ??
            label.textContent.trim()
        );
    }

    /**
     * Serialize the complete current component state.
     *
     * @returns {{groups: Array, items: Array}}
     */
    serialize() {
        const groups =
            this.groupsRoot
                ? [
                    ...this.groupsRoot
                        .querySelectorAll(
                            ':scope > [data-sortable-group]'
                        ),
                ].map(
                    (group, position) => ({
                        id:
                            group.dataset
                                .sortableGroupId ??
                            group.id ??
                            null,

                        label:
                            group.querySelector(
                                '[data-sortable-group-title]'
                            )?.textContent.trim() ??
                            '',

                        position,

                        items:
                            this.serializeItems(
                                group.querySelector(
                                    '[data-sortable-items]'
                                )
                            ),
                    })
                )
                : [];

        return {
            groups,

            items:
                this.grouped
                    ? []
                    : this.serializeItems(
                        this.rootItemsRoot
                    ),
        };
    }

    /**
     * Synchronize current state with the hidden input.
     *
     * @param {boolean} dispatchChange
     * @returns {{groups: Array, items: Array}}
     */
    sync(
        dispatchChange = true
    ) {
        const state =
            this.serialize();

        if (this.input) {
            this.input.value =
                JSON.stringify(
                    state
                );
        }

        if (dispatchChange) {
            this.dispatchNativeChange();
        }

        return state;
    }

    /**
     * Dispatch a native change event from the hidden input.
     *
     * This keeps Sortable independent from ChangeTracker.
     *
     * @returns {void}
     */
    dispatchNativeChange() {
        if (!this.input) {
            return;
        }

        this.input.dispatchEvent(
            new Event(
                'change',
                {
                    bubbles: true,
                }
            )
        );
    }

    /**
     * Dispatch a component-specific CustomEvent.
     *
     * @param {string} name
     * @param {Object} detail
     * @returns {void}
     */
    dispatch(
        name,
        detail = {}
    ) {
        this.root.dispatchEvent(
            new CustomEvent(
                name,
                {
                    bubbles: true,

                    detail: {
                        ...detail,

                        sortable:
                            this,
                    },
                }
            )
        );
    }

    /**
     * Refresh empty-state classes.
     *
     * Root items are skipped in grouped mode because
     * the root container does not exist there.
     *
     * @returns {void}
     */
    updateEmptyStates() {
        if (
            !this.grouped &&
            this.rootItemsRoot
        ) {
            this.updateEmptyState(
                this.rootItemsRoot
            );
        }

        if (
            this.groupsRoot
        ) {
            this.groupsRoot
                .querySelectorAll(
                    ':scope > [data-sortable-group] [data-sortable-items]'
                )
                .forEach(
                    (container) => {
                        this.updateEmptyState(
                            container
                        );
                    }
                );
        }
    }

    /**
     * Update the empty state of one item container.
     *
     * CSS uses the JS-managed `.is-empty` class instead of :empty.
     *
     * @param {HTMLElement} container
     * @returns {void}
     */
    updateEmptyState(container) {
        const empty =
            !container.querySelector(
                ':scope > [data-sortable-item]'
            );

        container.classList.toggle(
            'is-empty',
            empty
        );
    }

    /**
     * Generate a client-side identifier.
     *
     * This identifies a DOM node and is not necessarily
     * a persisted database identifier.
     *
     * @param {string} prefix
     * @returns {string}
     */
    generateId(
        prefix = 'sortable'
    ) {
        const random =
            Math.random()
                .toString(36)
                .slice(2, 10);

        return `${prefix}-${random}`;
    }

    /**
     * Destroy all SortableJS instances and delegated listeners.
     *
     * @returns {void}
     */
    destroy() {
        this.groupsSortable?.destroy();

        this.rootItemsSortable?.destroy();

        for (
            const sortable
            of this.itemSortables.values()
        ) {
            sortable.destroy();
        }

        this.itemSortables.clear();

        this.groupsSortable = null;
        this.rootItemsSortable = null;

        if (this.boundClickHandler) {
            this.root.removeEventListener(
                'click',
                this.boundClickHandler
            );
        }

        if (this.boundInputHandler) {
            this.root.removeEventListener(
                'input',
                this.boundInputHandler
            );
        }

        if (this.boundChangeHandler) {
            this.root.removeEventListener(
                'change',
                this.boundChangeHandler
            );
        }

        this.boundClickHandler = null;
        this.boundInputHandler = null;
        this.boundChangeHandler = null;

        this.initialized = false;
    }
}

/**
 * WeakMap containing initialized SortableField instances.
 *
 * @type {WeakMap<HTMLElement, SortableField>}
 */
const instances = new WeakMap();

/**
 * Initialize one SortableField.
 *
 * @param {HTMLElement} root
 * @returns {Promise<SortableField>}
 */
export async function initSortable(root) {
    if (
        !(root instanceof HTMLElement)
    ) {
        throw new TypeError(
            'Sortable root must be an HTMLElement.'
        );
    }

    let sortable =
        instances.get(root);

    if (!sortable) {
        sortable =
            new SortableField(root);

        instances.set(
            root,
            sortable
        );
    }

    await sortable.init();

    return sortable;
}

/**
 * Return the initialized SortableField instance for an element.
 *
 * @param {HTMLElement} root
 * @returns {SortableField|undefined}
 */
export function getSortable(root) {
    return instances.get(root);
}

/**
 * Initialize all Sortable fields on the current page.
 *
 * @returns {Promise<void>}
 */
export async function initSortables() {
    const roots =
        document.querySelectorAll(
            '[data-sortable]'
        );

    await Promise.all(
        [...roots].map(
            (root) =>
                initSortable(root)
        )
    );
}

if (
    document.readyState === 'loading'
) {
    document.addEventListener(
        'DOMContentLoaded',
        () => {
            initSortables();
        },
        {
            once: true,
        }
    );
} else {
    initSortables();
}
