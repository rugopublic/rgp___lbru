/**
 * Public content interactions.
 *
 * Likes, bookmarks and comments use small native-JS AJAX handlers so the
 * public surface remains responsive without introducing a second form engine.
 */

const csrfToken = () =>
    document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

const request = async (form) => {
    const response = await fetch(form.action, {
        method: (form.method || 'POST').toUpperCase(),
        headers: {
            'X-CSRF-TOKEN': csrfToken(),
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
        },
        body: new URLSearchParams(new FormData(form)),
        credentials: 'same-origin',
    });

    let payload = null;

    try {
        payload = await response.json();
    } catch {
        payload = null;
    }

    if (!response.ok) {
        const message = payload?.message ?? 'Не удалось выполнить действие.';
        throw new Error(message);
    }

    return payload;
};

const setBusy = (form, busy) => {
    form.dataset.busy = busy ? 'true' : 'false';
    form.querySelectorAll('button, input, textarea').forEach((control) => {
        control.disabled = busy;
    });
};

const updateContext = (form, payload) => {
    if (typeof payload?.active !== 'boolean') {
        return;
    }

    const button = form.querySelector('button');

    if (!button) {
        return;
    }

    const icon = button.querySelector('i');

    if (form.action.includes('/like')) {
        button.classList.toggle('is-active', payload.active);
        icon?.classList.toggle('bi-heart-fill', payload.active);
        icon?.classList.toggle('bi-heart', !payload.active);
    }

    if (form.action.includes('/bookmark')) {
        button.classList.toggle('is-active', payload.active);
        icon?.classList.toggle('bi-bookmark-fill', payload.active);
        icon?.classList.toggle('bi-bookmark', !payload.active);
        const text = button.lastChild;
        if (text?.nodeType === Node.TEXT_NODE) {
            text.textContent = payload.active ? 'Сохранено' : 'Сохранить';
        }
    }

    const likeCount = payload?.context?.likeCount;
    if (Number.isFinite(Number(likeCount))) {
        document.querySelectorAll('[data-like-count]').forEach((element) => {
            element.textContent = `♥ ${likeCount}`;
        });
    }
};

const appendComment = (form, payload) => {
    const list = form.closest('.comments')?.querySelector('.comment-list');
    if (!list || !payload?.comment) {
        return;
    }

    const empty = list.querySelector('.muted');
    empty?.remove();

    const item = document.createElement('div');
    item.className = 'comment';

    const avatar = document.createElement('div');
    avatar.className = 'comment__avatar';
    avatar.textContent = String(payload.comment.avatar ?? 'П');

    const content = document.createElement('div');
    const author = document.createElement('strong');
    author.textContent = String(payload.comment.author ?? 'Пользователь');

    const body = document.createElement('p');
    body.textContent = String(payload.comment.body ?? '');

    const date = document.createElement('small');
    date.textContent = 'только что';

    content.append(author, body, date);
    item.append(avatar, content);
    list.prepend(item);
};

document.addEventListener('DOMContentLoaded', () => {
    document.addEventListener('submit', async (event) => {
        const form = event.target;

        if (!(form instanceof HTMLFormElement)) {
            return;
        }

        if (!form.matches('[data-interaction-form], [data-comment-form]')) {
            return;
        }

        event.preventDefault();

        if (form.dataset.busy === 'true') {
            return;
        }

        setBusy(form, true);

        try {
            const payload = await request(form);

            if (form.matches('[data-interaction-form]')) {
                updateContext(form, payload);
            }

            if (form.matches('[data-comment-form]')) {
                appendComment(form, payload);
                form.reset();
                form.querySelector('textarea')?.focus();
            }
        } catch (error) {
            const status = form.matches('[data-comment-form]')
                ? form.querySelector('[data-comment-status]')
                : document.querySelector('[data-interaction-status]');

            if (status) {
                status.textContent = error?.message ?? 'Не удалось выполнить действие.';
                status.classList.remove('visually-hidden');
            }
        } finally {
            setBusy(form, false);
        }
    });
});
