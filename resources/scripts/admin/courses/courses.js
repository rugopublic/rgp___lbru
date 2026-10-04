/**
 * @file resources/scripts/admin/courses/courses.js
 * @description Admin shared module for resources/scripts/admin/courses/courses.js; provides reusable or page-level administration behavior.
 * @exports init
 */

export function init() {


        /* =========================================================
           REFERENCES
           ========================================================= */

        const form = document.getElementById('courseEditForm');

        if (!form) {
            return;
        }

        const titleInput = document.getElementById('course_title');
        const shortDescriptionInput = document.getElementById('short_description');

        const promiseInput = document.getElementById('promise');
        const whoForInput = document.getElementById('who_for');
        const resultInput = document.getElementById('result');
        const uniqueValueInput = document.getElementById('unique_value');

        const objectiveInput = document.getElementById('objective');
        const requirementsInput = document.getElementById('requirements');
        const faqInput = document.getElementById('faq');

        const teacherNameInput = document.getElementById('teacher_name');
        const teacherBioInput = document.getElementById('teacher_bio');

        const priceInput = document.getElementById('price');
        const taxRateInput = document.getElementById('tax_rate');

        const comparableMinInput =
            document.getElementById('price_comparable_min');

        const comparableMaxInput =
            document.getElementById('price_comparable_max');

        const learnerValueInput =
            document.getElementById('price_learner_value');

        const accessTypeInput =
            document.getElementById('access_type');

        const accessDurationInput =
            document.getElementById('access_duration');

        const startDateInput =
            document.getElementById('start_date');

        const subscriptionBox =
            document.getElementById('subscriptionBox');

        const subscriptionPriceInput =
            document.getElementById('subscription_price');

        const subscriptionMonthsInput =
            document.getElementById('subscription_months');

        const subscriptionStudentsInput =
            document.getElementById('subscription_students');

        const subscriptionRetentionInput =
            document.getElementById('subscription_retention');

        const statusInput =
            document.getElementById('status');

        const publishDateInput =
            document.getElementById('publish_date');

        const metaTitleInput =
            document.getElementById('meta_title');

        const metaDescriptionInput =
            document.getElementById('meta_description');

        const metaKeywordsInput =
            document.getElementById('meta_keywords');

        const promoPlanInput =
            document.getElementById('promo_plan');

        const editor =
            document.getElementById('full-description-editor');

        const hiddenDescriptionInput =
            document.getElementById('full-description-input');

        const unsavedState =
            document.getElementById('unsavedState');

        const unsavedStateText =
            document.getElementById('unsavedStateText');

        const pageStatusBadge =
            document.getElementById('pageStatusBadge');

        const pageStatusText =
            document.getElementById('pageStatusText');

        const confirmDeleteCheck =
            document.getElementById('confirmDeleteCourse');

        const confirmDeleteButton =
            document.getElementById('confirmDeleteCourseBtn');

        /* =========================================================
           HELPERS
           ========================================================= */

        const moneyFormatter = new Intl.NumberFormat('ru-RU', {
            maximumFractionDigits: 0
        });

        function money(value) {
            const number = Number(value) || 0;

            return moneyFormatter.format(
                Math.round(number)
            ) + ' ₽';
        }

        function clamp(value, min, max) {
            return Math.min(
                Math.max(value, min),
                max
            );
        }

        function textLength(value) {
            return value
                .replace(/\s+/g, ' ')
                .trim()
                .length;
        }

        function wordCount(value) {

            const cleaned =
                String(value || '').trim();

            if (!cleaned) {
                return 0;
            }

            return cleaned.split(/\s+/).length;
        }

        function markDirty() {

            if (!unsavedState || !unsavedStateText) {
                return;
            }

            unsavedState.classList.add('is-dirty');

            unsavedStateText.textContent =
                'Есть несохранённые изменения';
        }

        function setAdvisor(
            element,
            type,
            icon,
            text
        ) {

            if (!element) {
                return;
            }

            const iconElement =
                element.querySelector('i');

            const textElement =
                element.querySelector('span');

            element.className =
                'smart-advisor is-visible ' + type;

            if (iconElement) {
                iconElement.className =
                    'bi ' + icon;
            }

            if (textElement) {
                textElement.textContent = text;
            }
        }

        function hideAdvisor(element) {

            if (element) {
                element.className =
                    'smart-advisor';
            }
        }

        function updateMeter(
            input,
            bar,
            label,
            max
        ) {

            if (!input || !bar || !label) {
                return;
            }

            const length =
                input.value.length;

            const percent =
                clamp(
                    (length / max) * 100,
                    0,
                    100
                );

            bar.style.width =
                percent + '%';

            if (percent < 35) {

                bar.style.backgroundColor =
                    '#b9b9b5';

            } else if (percent < 70) {

                bar.style.backgroundColor =
                    '#d0a62b';

            } else {

                bar.style.backgroundColor =
                    '#258452';
            }

            label.textContent =
                length + ' / ' + max;
        }

        function slugify(value) {

            if (!value) {
                return 'course';
            }

            return value
                .toString()
                .trim()
                .toLowerCase()
                .replace(/ё/g, 'е')
                .replace(/[^\p{L}\p{N}\s-]/gu, '')
                .replace(/[\s_-]+/g, '-')
                .replace(/^-+|-+$/g, '')
                || 'course';
        }

        /* =========================================================
           SEARCH PREVIEW
           ========================================================= */

        const yandexTitle =
            document.getElementById('yandexPreviewTitle');

        const yandexDescription =
            document.getElementById('yandexPreviewDescription');

        const yandexUrl =
            document.getElementById('yandexPreviewUrl');

        const googleTitle =
            document.getElementById('googlePreviewTitle');

        const googleDescription =
            document.getElementById('googlePreviewDescription');

        const googleUrl =
            document.getElementById('googlePreviewUrl');

        const baseUrl = window.baseUrl || '';

        function updateSearchPreview() {

            const title =
                titleInput?.value.trim()
                || 'Название курса';

            const description =
                shortDescriptionInput?.value.trim()
                || 'Краткое описание курса появится здесь.';

            const slug =
                slugify(title);

            const url =
                `${baseUrl}/courses/${slug}`;

            if (yandexTitle) {
                yandexTitle.textContent =
                    title;
            }

            if (googleTitle) {
                googleTitle.textContent =
                    title;
            }

            if (yandexDescription) {
                yandexDescription.textContent =
                    description;
            }

            if (googleDescription) {
                googleDescription.textContent =
                    description;
            }

            if (yandexUrl) {
                yandexUrl.textContent =
                    url;
            }

            if (googleUrl) {
                googleUrl.textContent =
                    url;
            }
        }

        /* =========================================================
           TEXT QUALITY BOT
           ========================================================= */

        function inspectTitle() {

            if (!titleInput) {
                return;
            }

            const value =
                titleInput.value.trim();

            const length =
                textLength(value);

            updateMeter(
                titleInput,
                document.getElementById('titleMeter'),
                document.getElementById('titleMeterLabel'),
                255
            );

            const advisor =
                document.getElementById('titleAdvisor');

            if (!length) {

                setAdvisor(
                    advisor,
                    'is-danger',
                    'bi-exclamation-circle',
                    'Название обязательно. Сейчас поисковику и покупателю буквально нечего показать.'
                );

                return;
            }

            if (length < 10) {

                setAdvisor(
                    advisor,
                    'is-warning',
                    'bi-pencil',
                    'Название пока слишком короткое. Добавьте тему или конкретный результат, чтобы человеку было понятно, что он ищет.'
                );

                return;
            }

            const genericPatterns = [
                'уникальный курс',
                'лучший курс',
                'интересный курс',
                'для всех',
                'погружение',
                'секреты успеха'
            ];

            const lower =
                value.toLowerCase();

            if (
                genericPatterns.some(
                    pattern => lower.includes(pattern)
                )
            ) {

                setAdvisor(
                    advisor,
                    'is-warning',
                    'bi-search',
                    'В названии есть рекламный штамп. Поиск и покупатель лучше реагируют на конкретную тему и результат.'
                );

                return;
            }

            setAdvisor(
                advisor,
                'is-good',
                'bi-check-circle',
                'Название выглядит содержательно. Теперь проверьте, совпадает ли оно с тем, что реально получает ученик.'
            );
        }

        function inspectShortDescription() {

            if (!shortDescriptionInput) {
                return;
            }

            const value =
                shortDescriptionInput.value.trim();

            const words =
                wordCount(value);

            updateMeter(
                shortDescriptionInput,
                document.getElementById('shortDescriptionMeter'),
                document.getElementById('shortDescriptionMeterLabel'),
                1000
            );

            const advisor =
                document.getElementById('shortDescriptionAdvisor');

            if (!value) {

                setAdvisor(
                    advisor,
                    'is-danger',
                    'bi-exclamation-circle',
                    'Без краткого описания карточке курса будет трудно объяснить ценность предложения.'
                );

                return;
            }

            const genericPatterns = [
                'уникальный курс',
                'много полезной информации',
                'для всех',
                'интересный курс',
                'погрузитесь в мир',
                'научитесь всему'
            ];

            const lower =
                value.toLowerCase();

            if (
                genericPatterns.some(
                    pattern => lower.includes(pattern)
                )
            ) {

                setAdvisor(
                    advisor,
                    'is-warning',
                    'bi-chat-square-text',
                    'Здесь обнаружены рекламные штампы. Замените их конкретикой: что человек сделает, получит или изменит.'
                );

                return;
            }

            if (words < 15) {

                setAdvisor(
                    advisor,
                    'is-warning',
                    'bi-arrow-right-circle',
                    'Пока мало фактов. Добавьте кому подходит курс, результат и хотя бы одну причину выбрать именно его.'
                );

                return;
            }

            if (words >= 25) {

                setAdvisor(
                    advisor,
                    'is-good',
                    'bi-check-circle',
                    'Описание уже похоже на полноценное ценностное предложение. Следите, чтобы каждое предложение добавляло смысл.'
                );

                return;
            }

            setAdvisor(
                advisor,
                'is-good',
                'bi-check-circle',
                'Хорошее начало. Проверьте, есть ли здесь конкретный результат, а не только описание содержания.'
            );
        }

        function inspectPromise() {

            const value =
                promiseInput?.value.trim() || '';

            const advisor =
                document.getElementById('promiseAdvisor');

            if (!value) {

                setAdvisor(
                    advisor,
                    'is-warning',
                    'bi-lightbulb',
                    'Сформулируйте главное обещание. Оно поможет удержать весь курс в фокусе.'
                );

                return;
            }

            if (wordCount(value) < 8) {

                setAdvisor(
                    advisor,
                    'is-warning',
                    'bi-pencil',
                    'Обещание слишком короткое. Добавьте измеримый или наблюдаемый результат.'
                );

                return;
            }

            const weakWords = [
                'получите знания',
                'получите информацию',
                'познакомитесь',
                'разберётесь',
                'узнаете всё'
            ];

            const lower =
                value.toLowerCase();

            if (
                weakWords.some(
                    word => lower.includes(word)
                )
            ) {

                setAdvisor(
                    advisor,
                    'is-warning',
                    'bi-bullseye',
                    'Обещание звучит абстрактно. Опишите действие, продукт или изменение, которое можно увидеть.'
                );

                return;
            }

            setAdvisor(
                advisor,
                'is-good',
                'bi-check-circle',
                'Обещание сформировано. Теперь оно должно совпадать с реальным содержанием курса.'
            );
        }

        function inspectWhoFor() {

            const value =
                whoForInput?.value.trim() || '';

            const advisor =
                document.getElementById('whoForAdvisor');

            if (!value) {

                setAdvisor(
                    advisor,
                    'is-warning',
                    'bi-person',
                    'Опишите конкретного ученика. Чем точнее адресат, тем меньше курс выглядит «для всех и ни для кого».'
                );

                return;
            }

            if (wordCount(value) < 10) {

                setAdvisor(
                    advisor,
                    'is-warning',
                    'bi-people',
                    'Добавьте контекст: уровень, ситуация, цель или проблему будущего ученика.'
                );

                return;
            }

            setAdvisor(
                advisor,
                'is-good',
                'bi-check-circle',
                'Хорошо. Чёткая аудитория помогает одновременно продажам, рекламе и содержанию.'
            );
        }

        function inspectResult() {

            const value =
                resultInput?.value.trim() || '';

            const advisor =
                document.getElementById('resultAdvisor');

            if (!value) {

                setAdvisor(
                    advisor,
                    'is-danger',
                    'bi-bullseye',
                    'Опишите конечный результат. Именно он должен оправдывать стоимость курса.'
                );

                return;
            }

            const weakWords = [
                'знания',
                'понимание',
                'познакомитесь',
                'узнаете',
                'получите информацию',
                'разберётесь'
            ];

            const lower =
                value.toLowerCase();

            if (
                weakWords.some(
                    word => lower.includes(word)
                )
            ) {

                setAdvisor(
                    advisor,
                    'is-warning',
                    'bi-bullseye',
                    'В результате есть абстрактные формулировки. Попробуйте описать действие, продукт или изменение, которое можно увидеть.'
                );

                return;
            }

            setAdvisor(
                advisor,
                'is-good',
                'bi-check-circle',
                'Результат сформулирован конкретнее. Убедитесь, что программа действительно приводит к нему.'
            );
        }

        function inspectUniqueValue() {

            const value =
                uniqueValueInput?.value.trim() || '';

            const advisor =
                document.getElementById('uniqueValueAdvisor');

            if (!value) {

                setAdvisor(
                    advisor,
                    'is-warning',
                    'bi-stars',
                    'Пока не объяснено, почему ученик должен выбрать этот курс среди альтернатив.'
                );

                return;
            }

            const genericPatterns = [
                'уникальный',
                'лучший',
                'самый',
                'профессиональный',
                'качественный'
            ];

            const lower =
                value.toLowerCase();

            if (
                genericPatterns.some(
                    word => lower.includes(word)
                ) &&
                wordCount(value) < 15
            ) {

                setAdvisor(
                    advisor,
                    'is-warning',
                    'bi-search',
                    'Слова «лучший» и «уникальный» ничего не доказывают. Покажите конкретное отличие.'
                );

                return;
            }

            setAdvisor(
                advisor,
                'is-good',
                'bi-check-circle',
                'Отличие заявлено. Хорошо бы подкрепить его структурой курса, методикой или реальным опытом автора.'
            );
        }

        function inspectObjective() {

            const value =
                objectiveInput?.value.trim() || '';

            const advisor =
                document.getElementById('objectiveAdvisor');

            if (!value) {

                setAdvisor(
                    advisor,
                    'is-danger',
                    'bi-exclamation-circle',
                    'Список результатов курса пуст. Ученику будет трудно понять, за что он платит.'
                );

                return;
            }

            const lines =
                value
                    .split('\n')
                    .map(
                        line =>
                            line
                                .replace(/^[•\-–—\s]+/, '')
                                .trim()
                    )
                    .filter(Boolean);

            if (lines.length < 3) {

                setAdvisor(
                    advisor,
                    'is-warning',
                    'bi-list-check',
                    'Добавьте ещё несколько конкретных результатов. Три пункта — минимальная ориентировочная планка для понятной картины курса.'
                );

                return;
            }

            const abstractWords = [
                'узнает',
                'познакомится',
                'получит знания',
                'поймёт',
                'поймет'
            ];

            const abstractFound =
                lines.some(
                    line =>
                        abstractWords.some(
                            word =>
                                line
                                    .toLowerCase()
                                    .includes(word)
                        )
                );

            if (abstractFound) {

                setAdvisor(
                    advisor,
                    'is-warning',
                    'bi-pencil',
                    'Некоторые результаты звучат абстрактно. Попробуйте описывать конкретное действие ученика.'
                );

                return;
            }

            setAdvisor(
                advisor,
                'is-good',
                'bi-check-circle',
                `Сформировано ${lines.length} конкретных пунктов. Это уже похоже на честное обещание результата.`
            );
        }

        function inspectRequirements() {

            const value =
                requirementsInput?.value.trim() || '';

            const advisor =
                document.getElementById('requirementsAdvisor');

            if (!value) {

                setAdvisor(
                    advisor,
                    'is-warning',
                    'bi-person-check',
                    'Пустое поле не всегда плохо, но стоит честно ответить: действительно ли для курса ничего не требуется?'
                );

                return;
            }

            const lines =
                value
                    .split('\n')
                    .map(
                        line =>
                            line
                                .replace(/^[•\-–—\s]+/, '')
                                .trim()
                    )
                    .filter(Boolean);

            if (lines.length < 2) {

                setAdvisor(
                    advisor,
                    'is-warning',
                    'bi-list-check',
                    'Добавьте конкретику: уровень подготовки, материалы, оборудование или честно укажите, что ничего не требуется.'
                );

                return;
            }

            setAdvisor(
                advisor,
                'is-good',
                'bi-check-circle',
                'Точка старта описана. Это снижает риск разочарования после покупки.'
            );
        }

        function inspectTeacherName() {

            const value =
                teacherNameInput?.value.trim() || '';

            const advisor =
                document.getElementById('teacherNameAdvisor');

            if (!value) {

                setAdvisor(
                    advisor,
                    'is-warning',
                    'bi-person',
                    'Имя преподавателя лучше заполнить: человек должен понимать, у кого он покупает обучение.'
                );

                return;
            }

            setAdvisor(
                advisor,
                'is-good',
                'bi-check-circle',
                'Имя преподавателя указано.'
            );
        }

        function inspectTeacherBio() {

            if (!teacherBioInput) {
                return;
            }

            const value =
                teacherBioInput.value.trim();

            const words =
                wordCount(value);

            updateMeter(
                teacherBioInput,
                document.getElementById('teacherBioMeter'),
                document.getElementById('teacherBioMeterLabel'),
                3000
            );

            const advisor =
                document.getElementById('teacherBioAdvisor');

            if (!value) {

                setAdvisor(
                    advisor,
                    'is-danger',
                    'bi-person-x',
                    'Описание преподавателя пустое. Для платного курса это серьёзный провал доверия.'
                );

                return;
            }

            const weakPatterns = [
                'большой опыт',
                'огромный опыт',
                'профессионал',
                'эксперт',
                'люблю своё дело',
                'знаю всё',
                'много лет опыта'
            ];

            const lower =
                value.toLowerCase();

            if (
                weakPatterns.some(
                    pattern => lower.includes(pattern)
                ) &&
                words < 45
            ) {

                setAdvisor(
                    advisor,
                    'is-warning',
                    'bi-person-check',
                    'Здесь есть общие заявления об экспертизе. Добавьте факты: опыт, проекты, результаты, специализацию или путь преподавателя.'
                );

                return;
            }

            if (words < 25) {

                setAdvisor(
                    advisor,
                    'is-warning',
                    'bi-person-lines-fill',
                    'Биография пока короткая. Добавьте конкретный опыт и объясните связь этого опыта с курсом.'
                );

                return;
            }

            if (words >= 50) {

                setAdvisor(
                    advisor,
                    'is-good',
                    'bi-shield-check',
                    'Биография уже создаёт содержательную основу доверия. Проверьте, что каждый факт релевантен курсу.'
                );

                return;
            }

            setAdvisor(
                advisor,
                'is-good',
                'bi-check-circle',
                'Хорошая база. Добавьте ещё один конкретный факт, если хотите усилить доверие.'
            );
        }

        function inspectFullDescription() {

            if (!editor) {
                return;
            }

            const text =
                editor.innerText.trim();

            const advisor =
                document.getElementById('fullDescriptionAdvisor');

            if (!text) {

                setAdvisor(
                    advisor,
                    'is-warning',
                    'bi-file-text',
                    'Полное описание пока пустое. Здесь стоит раскрыть программу, результат, формат и преимущества курса.'
                );

                return;
            }

            const words =
                wordCount(text);

            if (words < 80) {

                setAdvisor(
                    advisor,
                    'is-warning',
                    'bi-file-text',
                    'Описание пока очень короткое. Покупателю может не хватить контекста для уверенного решения.'
                );

                return;
            }

            const lower =
                text.toLowerCase();

            const hasResult =
                lower.includes('результат') ||
                lower.includes('сможете') ||
                lower.includes('научитесь');

            const hasProgram =
                lower.includes('урок') ||
                lower.includes('модул') ||
                lower.includes('программ');

            if (!hasResult || !hasProgram) {

                setAdvisor(
                    advisor,
                    'is-warning',
                    'bi-search',
                    'Описание уже достаточно объёмное, но проверьте наличие двух вещей: явного результата и понятной структуры программы.'
                );

                return;
            }

            setAdvisor(
                advisor,
                'is-good',
                'bi-check-circle',
                'Описание содержит и результат, и признаки программы. Теперь проверьте его глазами человека, который впервые видит курс.'
            );
        }

        function inspectFaq() {

            const value =
                faqInput?.value.trim() || '';

            const advisor =
                document.getElementById('faqAdvisor');

            if (!value) {

                setAdvisor(
                    advisor,
                    'is-warning',
                    'bi-question-circle',
                    'FAQ пока пуст. Это не обязательно ошибка, но здесь можно снять самые дорогие возражения до оплаты.'
                );

                return;
            }

            if (wordCount(value) < 10) {

                setAdvisor(
                    advisor,
                    'is-warning',
                    'bi-chat-square-text',
                    'FAQ пока слишком короткий. Добавьте реальные вопросы, которые ученик может задать перед оплатой.'
                );

                return;
            }

            setAdvisor(
                advisor,
                'is-good',
                'bi-check-circle',
                'FAQ уже помогает снять часть неопределённости. Используйте реальные вопросы аудитории, а не придуманные рекламные формулировки.'
            );
        }

        function inspectMetaTitle() {

            const value =
                metaTitleInput?.value.trim() || '';

            const advisor =
                document.getElementById('metaTitleAdvisor');

            if (!value) {

                setAdvisor(
                    advisor,
                    'is-warning',
                    'bi-search',
                    'SEO Title пуст. Если курс должен находиться в поиске, это поле лучше заполнить.'
                );

                return;
            }

            if (value.length > 65) {

                setAdvisor(
                    advisor,
                    'is-warning',
                    'bi-textarea-t',
                    'Title довольно длинный. Проверьте, не потеряется ли главное слово или тема курса.'
                );

                return;
            }

            setAdvisor(
                advisor,
                'is-good',
                'bi-check-circle',
                'SEO Title заполнен. Проверьте, что он действительно соответствует запросу, по которому вы хотите быть найдены.'
            );
        }

        function inspectMetaDescription() {

            const value =
                metaDescriptionInput?.value.trim() || '';

            const advisor =
                document.getElementById('metaDescriptionAdvisor');

            if (!value) {

                setAdvisor(
                    advisor,
                    'is-warning',
                    'bi-search',
                    'SEO Description пуст. Заполните его конкретным описанием ценности курса.'
                );

                return;
            }

            if (value.length < 80) {

                setAdvisor(
                    advisor,
                    'is-warning',
                    'bi-pencil',
                    'Описание короткое. Добавьте результат и причину открыть страницу курса.'
                );

                return;
            }

            setAdvisor(
                advisor,
                'is-good',
                'bi-check-circle',
                'SEO Description выглядит содержательно.'
            );
        }

        function inspectFaqAndPromo() {
            inspectFaq();
            inspectPromoPlan();
        }

        function inspectPromoPlan() {

            const value =
                promoPlanInput?.value.trim() || '';

            const advisor =
                document.getElementById('promoPlanAdvisor');

            if (!value) {

                setAdvisor(
                    advisor,
                    'is-warning',
                    'bi-megaphone',
                    'План продвижения пуст. Не публикуйте курс с мыслью «рекламу придумаем потом».'
                );

                return;
            }

            if (wordCount(value) < 15) {

                setAdvisor(
                    advisor,
                    'is-warning',
                    'bi-calendar2-week',
                    'План пока слишком общий. Добавьте каналы, контент, даты, партнёров или конкретные действия.'
                );

                return;
            }

            setAdvisor(
                advisor,
                'is-good',
                'bi-check-circle',
                'Есть рабочая основа продвижения. Теперь привяжите её к дате запуска и не откладывайте подготовку рекламы на последний день.'
            );
        }

        /* =========================================================
           ADVANTAGES
           ========================================================= */

        const advantagesList =
            document.getElementById('advantagesList');

        const addAdvantageBtn =
            document.getElementById('addAdvantageBtn');

        function renumberAdvantages() {

            if (!advantagesList) {
                return;
            }

            const rows =
                advantagesList.querySelectorAll(
                    '[data-advantage-row]'
                );

            rows.forEach((row, index) => {

                const number =
                    row.querySelector('.advantage-number');

                if (number) {
                    number.textContent =
                        index + 1;
                }
            });

            updateAdvantagesAdvisor();
        }

        function updateAdvantagesAdvisor() {

            const advisor =
                document.getElementById('advantagesAdvisor');

            if (!advisor || !advantagesList) {
                return;
            }

            const values =
                Array.from(
                    advantagesList.querySelectorAll(
                        '.advantage-input'
                    )
                )
                    .map(input => input.value?.trim())
                    .filter(Boolean);

            if (!values.length) {

                advisor.innerHTML =
                    'Пока нет преимуществ. Добавьте хотя бы 3 конкретные причины выбрать этот курс.';

                return;
            }

            if (values.length < 3) {

                advisor.innerHTML =
                    `Сейчас ${values.length} пункт(а). Добавьте ещё: три конкретных преимущества — хорошая минимальная база.`;

                return;
            }

            const generic =
                values.filter(value => {

                    const lower =
                        value.toLowerCase();

                    return [
                        'качественный',
                        'уникальный',
                        'интересный',
                        'лучший',
                        'полезный',
                        'профессиональный'
                    ].some(
                        word => lower.includes(word)
                    );
                });

            if (generic.length) {

                advisor.innerHTML =
                    'Некоторые преимущества звучат как рекламные прилагательные. Замените их конкретными свойствами, результатами или доказательствами.';

                return;
            }

            advisor.innerHTML =
                `<strong>${values.length}</strong> конкретных преимуществ. Отлично. Теперь проверьте, что каждый пункт действительно важен ученику.`;
        }

        function createAdvantageRow(value = '') {

            if (!advantagesList) {
                return;
            }

            const row =
                document.createElement('div');

            row.className =
                'advantage-row';

            row.setAttribute(
                'data-advantage-row',
                ''
            );

            row.innerHTML = `
            <span class="advantage-number"></span>

            <input
                type="text"
                name="advantages[]"
                class="form-control advantage-input"
                value=""
                maxlength="255"
                placeholder="Например: 12 практических рецептов с пошаговым разбором"
            >

            <button
                type="button"
                class="advantage-remove"
                data-remove-advantage
                title="Удалить преимущество"
                aria-label="Удалить преимущество"
            >
                <i class="bi bi-x-lg"></i>
            </button>
        `;

            const input =
                row.querySelector('.advantage-input');

            if (input) {
                input.value =
                    value;
            }

            advantagesList.appendChild(row);

            bindAdvantageRow(row);

            renumberAdvantages();

            if (input) {
                input.focus();
            }

            markDirty();
        }

        function bindAdvantageRow(row) {

            const input =
                row.querySelector('.advantage-input');

            const removeButton =
                row.querySelector('[data-remove-advantage]');

            if (input) {

                input.addEventListener(
                    'input',
                    function () {

                        updateAdvantagesAdvisor();
                        markDirty();
                    }
                );
            }

            if (removeButton) {

                removeButton.addEventListener(
                    'click',
                    function () {

                        const rows =
                            advantagesList.querySelectorAll(
                                '[data-advantage-row]'
                            );

                        if (rows.length <= 1) {

                            if (input) {
                                input.value = '';
                            }

                        } else {

                            row.remove();
                        }

                        renumberAdvantages();
                        markDirty();
                    }
                );
            }
        }

        if (addAdvantageBtn) {

            addAdvantageBtn.addEventListener(
                'click',
                function () {

                    createAdvantageRow('');
                }
            );
        }

        if (advantagesList) {

            advantagesList
                .querySelectorAll('[data-advantage-row]')
                .forEach(bindAdvantageRow);

            renumberAdvantages();
        }

        /* =========================================================
           QUALITY SCORE
           ========================================================= */

        function calculateQualityScore() {

            const advantageValues =
                Array.from(
                    document.querySelectorAll(
                        '.advantage-input'
                    )
                )
                    .map(
                        input => input?.value?.trim()
                    )
                    .filter(Boolean);

            const checks = [

                textLength(titleInput?.value || '') >= 10,

                wordCount(
                    shortDescriptionInput?.value || ''
                ) >= 15,

                wordCount(
                    promiseInput?.value || ''
                ) >= 8,

                wordCount(
                    whoForInput?.value || ''
                ) >= 10,

                wordCount(
                    resultInput?.value || ''
                ) >= 8,

                wordCount(
                    uniqueValueInput?.value || ''
                ) >= 8,

                wordCount(
                    objectiveInput?.value || ''
                ) >= 15,

                wordCount(
                    requirementsInput?.value || ''
                ) >= 5,

                wordCount(
                    teacherBioInput?.value || ''
                ) >= 25,

                wordCount(
                    editor?.innerText || ''
                ) >= 80,

                wordCount(
                    faqInput?.value || ''
                ) >= 10,

                Number(
                    priceInput?.value || 0
                ) > 0,

                Boolean(
                    metaTitleInput?.value.trim()
                ),

                Boolean(
                    metaDescriptionInput?.value.trim()
                ),

                advantageValues.length >= 3,

                Boolean(
                    promoPlanInput?.value.trim()
                )
            ];

            const score =
                Math.round(
                    checks.filter(Boolean).length /
                    checks.length *
                    100
                );

            const bar =
                document.getElementById('qualityBar');

            const scoreLabel =
                document.getElementById('qualityScore');

            const message =
                document.getElementById('qualityMessage');

            if (bar) {

                bar.style.width =
                    score + '%';

                if (score < 50) {

                    bar.style.backgroundColor =
                        '#b34b56';

                } else if (score < 75) {

                    bar.style.backgroundColor =
                        '#d0a62b';

                } else {

                    bar.style.backgroundColor =
                        '#258452';
                }
            }

            if (scoreLabel) {

                scoreLabel.textContent =
                    score + '%';
            }

            if (message) {

                if (score < 50) {

                    message.textContent =
                        'Курс пока трудно продавать: слишком много ключевых аргументов ещё не сформировано.';

                } else if (score < 75) {

                    message.textContent =
                        'Основа есть. Сейчас важнее всего убрать слабые места, которые мешают покупателю принять решение.';

                } else if (score < 90) {

                    message.textContent =
                        'Сильная коммерческая упаковка. Несколько точечных улучшений могут заметно повысить убедительность.';

                } else {

                    message.textContent =
                        'Очень хорошая заполненность. Теперь задача — не допустить разрыва между обещанием и реальным содержанием курса.';
                }
            }

            return score;
        }

        /* =========================================================
           REVENUE CALCULATOR
           ========================================================= */

        function calculateRevenue() {

            const price =
                Math.max(
                    Number(priceInput?.value) || 0,
                    0
                );

            const taxRate =
                Math.max(
                    Number(taxRateInput?.value) || 0,
                    0
                );

            const tax =
                price * taxRate / 100;

            const net =
                price - tax;

            const grossPrice =
                document.getElementById('grossPrice');

            const taxAmount =
                document.getElementById('taxAmount');

            const netPrice =
                document.getElementById('netPrice');

            if (grossPrice) {
                grossPrice.textContent =
                    money(price);
            }

            if (taxAmount) {
                taxAmount.textContent =
                    money(tax);
            }

            if (netPrice) {
                netPrice.textContent =
                    money(net);
            }

            const counts =
                [5, 10, 15, 25, 50, 100];

            const maximum =
                Math.max(
                    net * Math.max(...counts),
                    1
                );

            counts.forEach(count => {

                const value =
                    net * count;

                const valueElement =
                    document.getElementById(
                        'revenue' + count
                    );

                const fillElement =
                    document.querySelector(
                        `[data-revenue-fill="${count}"]`
                    );

                if (valueElement) {

                    valueElement.textContent =
                        money(value);
                }

                if (fillElement) {

                    fillElement.style.width =
                        Math.max(
                            (value / maximum) * 100,
                            4
                        ) + '%';
                }
            });

            calculateWealthComparison(net);
            calculateSubscriptionRevenue();
            calculatePriceFairness();
        }

        function calculateWealthComparison(netPerSale) {

            const comparison =
                document.getElementById(
                    'wealthComparison'
                );

            if (!comparison) {
                return;
            }

            const values = {

                5: netPerSale * 5,
                10: netPerSale * 10,
                15: netPerSale * 15,
                25: netPerSale * 25,
                50: netPerSale * 50,
                100: netPerSale * 100

            };

            if (values[100] >= 10000000) {

                comparison.innerHTML =
                    'При 100 продажах модельный доход уже превышает <strong>10 млн ₽</strong>. Это не обещание результата, а демонстрация силы повторяемых продаж.';

                return;
            }

            if (values[100] >= 5000000) {

                comparison.innerHTML =
                    'При 100 продажах получается более <strong>5 млн ₽</strong> после выбранного налога — сумма уже сравнима со стоимостью заметной части московской недвижимости.';

                return;
            }

            if (values[50] >= 3000000) {

                comparison.innerHTML =
                    'При 50 продажах модельный доход уже измеряется <strong>миллионами рублей</strong>. Именно повторяемость продаж превращает курс в актив, а не разовую публикацию.';

                return;
            }

            if (values[25] >= 1000000) {

                comparison.innerHTML =
                    'При 25 продажах получается более <strong>1 млн ₽</strong> после выбранного налога. Это хороший повод думать не только о создании курса, но и о системе его продвижения.';

                return;
            }

            if (values[10] >= 500000) {

                comparison.innerHTML =
                    'При 10 продажах это уже <strong>сотни тысяч рублей</strong>. Следующий вопрос — как сделать так, чтобы десять продаж не были потолком.';

                return;
            }

            comparison.innerHTML =
                'Даже небольшая серия продаж создаёт экономический эффект. Важнее всего не одна удачная продажа, а способность повторять результат.';
        }

        /* =========================================================
           SUBSCRIPTION
           ========================================================= */

        function calculateSubscriptionRevenue() {

            const price =
                Math.max(
                    Number(
                        subscriptionPriceInput?.value
                    ) || 0,
                    0
                );

            const months =
                Math.max(
                    Number(
                        subscriptionMonthsInput?.value
                    ) || 1,
                    1
                );

            const retention =
                clamp(
                    Number(
                        subscriptionRetentionInput?.value
                    ) || 0,
                    0,
                    1
                );

            const taxRate =
                Math.max(
                    Number(
                        taxRateInput?.value
                    ) || 0,
                    0
                );

            const students =
                Math.max(
                    Number(
                        subscriptionStudentsInput?.value
                    ) || 1,
                    1
                );

            let expectedMonths = 0;

            for (
                let month = 0;
                month < months;
                month++
            ) {

                expectedMonths +=
                    Math.pow(
                        retention,
                        month
                    );
            }

            const grossCustomerRevenue =
                price * expectedMonths;

            const netCustomerRevenue =
                grossCustomerRevenue *
                (1 - taxRate / 100);

            const totalRevenue =
                netCustomerRevenue *
                students;

            const customerRevenueElement =
                document.getElementById(
                    'subscriptionCustomerRevenue'
                );

            const totalRevenueElement =
                document.getElementById(
                    'subscriptionFiftyRevenue'
                );

            if (customerRevenueElement) {

                customerRevenueElement.textContent =
                    money(netCustomerRevenue);
            }

            if (totalRevenueElement) {

                totalRevenueElement.textContent =
                    money(totalRevenue);
            }
        }

        function updateSubscriptionVisibility() {

            const isSubscription =
                accessTypeInput?.value === 'subscription';

            if (subscriptionBox) {

                subscriptionBox.classList.toggle(
                    'is-visible',
                    isSubscription
                );
            }

            calculateSubscriptionRevenue();
        }

        /* =========================================================
           PRICE FAIRNESS
           ========================================================= */

        function calculatePriceFairness() {

            const result =
                document.getElementById(
                    'priceFairnessResult'
                );

            const note =
                document.getElementById(
                    'priceFairnessNote'
                );

            if (!result || !note) {
                return;
            }

            const price =
                Math.max(
                    Number(priceInput?.value) || 0,
                    0
                );

            const min =
                Math.max(
                    Number(
                        comparableMinInput?.value
                    ) || 0,
                    0
                );

            const max =
                Math.max(
                    Number(
                        comparableMaxInput?.value
                    ) || 0,
                    0
                );

            const learnerValue =
                Math.max(
                    Number(
                        learnerValueInput?.value
                    ) || 0,
                    0
                );

            const quality =
                calculateQualityScore();

            if (!price) {

                result.textContent =
                    'Сначала укажите цену курса.';

                note.textContent =
                    'Затем можно проверить её относительно аналогов и ценности результата.';

                return;
            }

            if (
                min > 0 &&
                max > 0 &&
                max >= min
            ) {

                const midpoint =
                    (min + max) / 2;

                const lowerBound =
                    min * .85;

                const upperBound =
                    max * 1.15;

                if (price < lowerBound) {

                    result.innerHTML =
                        `Цена <strong>${money(price)}</strong> заметно ниже диапазона аналогов.`;

                    note.textContent =
                        'Это не значит, что цена неправильная: возможно, вы сознательно выбрали стратегию входа на рынок. Но проверьте, не недооцениваете ли вы продукт.';

                } else if (price > upperBound) {

                    result.innerHTML =
                        `Цена <strong>${money(price)}</strong> выше большинства заданных аналогов.`;

                    note.textContent =
                        'Такую цену можно защищать, если курс даёт заметно больше ценности, доверия, сопровождения или доказательств результата.';

                } else {

                    result.innerHTML =
                        `Цена <strong>${money(price)}</strong> находится внутри разумного диапазона аналогов.`;

                    note.textContent =
                        `Ориентир середины диапазона — ${money(midpoint)}. Теперь решает не арифметика, а сила вашего предложения.`;
                }

                if (
                    learnerValue > 0 &&
                    price > learnerValue
                ) {

                    note.textContent +=
                        ' При этом цена выше указанной вами денежной оценки результата — проверьте, насколько убедительно вы объясняете ценность.';

                } else if (
                    learnerValue > 0 &&
                    price <= learnerValue * .2
                ) {

                    note.textContent +=
                        ' Цена составляет небольшую долю заявленной ценности результата — это может быть сильным аргументом, если сама оценка ценности реалистична.';
                }

                return;
            }

            if (learnerValue > 0) {

                const ratio =
                    price / learnerValue;

                if (ratio <= .2) {

                    result.innerHTML =
                        `Цена — около <strong>${Math.round(ratio * 100)}%</strong> от заявленной ценности результата.`;

                    note.textContent =
                        'Математически предложение выглядит убедительно. Теперь важно доказать, что эта ценность реальна.';

                } else if (ratio <= .5) {

                    result.innerHTML =
                        `Цена — около <strong>${Math.round(ratio * 100)}%</strong> от заявленной ценности результата.`;

                    note.textContent =
                        'Проверьте, достаточно ли сильны результат, преподаватель и доказательства, чтобы цена ощущалась логичной.';

                } else {

                    result.innerHTML =
                        `Цена составляет около <strong>${Math.round(ratio * 100)}%</strong> от заявленной ценности результата.`;

                    note.textContent =
                        'Это повод особенно тщательно проработать результат, доверие, программу и преимущества курса.';
                }

                return;
            }

            result.innerHTML =
                `Текущая цена: <strong>${money(price)}</strong>.`;

            note.textContent =
                `Чтобы действительно проверить её, задайте диапазон цен аналогов или денежную оценку результата. Качество упаковки сейчас ${quality}%.`;
        }

        /* =========================================================
           ACCESS CONSEQUENCES
           ========================================================= */

        function updateAccessConsequences() {

            const duration =
                Math.max(
                    Number(
                        accessDurationInput?.value
                    ) || 0,
                    0
                );

            const type =
                accessTypeInput?.value || 'free';

            const title =
                document.getElementById(
                    'accessConsequenceTitle'
                );

            const text =
                document.getElementById(
                    'accessConsequenceText'
                );

            if (!title || !text) {
                return;
            }

            if (type === 'subscription') {

                title.textContent =
                    'Подписка: доступ связан с продолжением оплаты';

                text.textContent =
                    'Это модель долгосрочного отношения. Каждый период должен давать ученику новую причину остаться: новые материалы, практика, сопровождение, разборы или обновления.';

            } else if (type === 'free') {

                title.textContent =
                    'Бесплатный доступ';

                text.textContent =
                    'Здесь главный вопрос — какую задачу решает бесплатный продукт и почему после него человеку захочется перейти к следующему шагу.';

            } else if (type === 'lifetime') {

                title.textContent =
                    'Бессрочный доступ';

                text.textContent =
                    'Большая свобода для ученика повышает ощущение безопасности, но снижает срочность. Особенно важны сильная структура и понятный результат.';

            } else if (duration <= 30) {

                title.textContent =
                    `Короткий доступ — ${duration || 0} дней`;

                text.textContent =
                    'Короткий срок создаёт дедлайн и может мотивировать пройти материал быстрее. Но он требует особенно понятного темпа обучения и предупреждения ученика о сроке окончания.';

            } else if (duration <= 180) {

                title.textContent =
                    `Доступ на ${duration} дней`;

                text.textContent =
                    'Это компромисс между свободой и дедлайном. Подходит, когда ученику нужно время на практику, но курс не должен превращаться в бесконечный архив.';

            } else {

                title.textContent =
                    `Долгий доступ — ${duration} дней`;

                text.textContent =
                    'Ученик получает большую свободу, но чувство срочности снижается. Для такой модели особенно важны регулярная мотивация, понятная структура и ценность самого материала.';
            }
        }

        function updateStartDateWarning() {

            const warning =
                document.getElementById(
                    'accessDateWarning'
                );

            if (!warning) {
                return;
            }

            if (!startDateInput?.value) {

                warning.classList.remove(
                    'is-visible'
                );

                return;
            }

            const selected =
                new Date(
                    startDateInput.value +
                    'T00:00:00'
                );

            const today =
                new Date();

            today.setHours(
                0, 0, 0, 0
            );

            const diff =
                Math.round(
                    (
                        selected - today
                    ) / 86400000
                );

            warning.classList.add(
                'is-visible'
            );

            if (diff > 30) {

                warning.innerHTML =
                    '<i class="bi bi-calendar-check me-1"></i>' +
                    '<strong>До старта достаточно времени.</strong> ' +
                    'Используйте его для рекламы, контента, партнёрств, рассылки, теста лендинга и подготовки первых продаж.';

            } else if (diff > 14) {

                warning.innerHTML =
                    '<i class="bi bi-megaphone me-1"></i>' +
                    '<strong>Хорошее окно для подготовки.</strong> ' +
                    'Реклама и контент уже должны быть в работе. Не оставляйте раскрутку на неделю запуска.';

            } else if (diff > 0) {

                warning.innerHTML =
                    '<i class="bi bi-exclamation-circle me-1"></i>' +
                    '<strong>До старта меньше двух недель.</strong> ' +
                    'Проверьте, готовы ли рекламные материалы, партнёрские размещения, рассылка и ответы на возражения.';

            } else if (diff === 0) {

                warning.innerHTML =
                    '<i class="bi bi-broadcast me-1"></i>' +
                    '<strong>Старт сегодня.</strong> ' +
                    'Убедитесь, что реклама, лендинг и коммуникация с потенциальными учениками уже готовы.';

            } else {

                warning.innerHTML =
                    '<i class="bi bi-clock-history me-1"></i>' +
                    '<strong>Дата уже прошла.</strong> ' +
                    'Если это не намеренный повторный запуск, проверьте дату публикации.';
            }
        }

        /* =========================================================
           PUBLICATION STATUS
           ========================================================= */

        function updateStatusUI() {

            const status =
                statusInput?.value || 'draft';

            if (!pageStatusBadge || !pageStatusText) {
                return;
            }

            pageStatusBadge.classList.remove(
                'is-draft',
                'is-archived'
            );

            if (status === 'published') {

                pageStatusText.textContent =
                    'Опубликован';

            } else if (status === 'archived') {

                pageStatusBadge.classList.add(
                    'is-archived'
                );

                pageStatusText.textContent =
                    'Архив';

            } else {

                pageStatusBadge.classList.add(
                    'is-draft'
                );

                pageStatusText.textContent =
                    'Черновик';
            }

            updateLaunchFlow();
        }

        function updateLaunchFlow() {

            const status =
                statusInput?.value || 'draft';

            const publishDate =
                publishDateInput?.value;

            document
                .querySelectorAll('[data-launch-step]')
                .forEach(
                    step => step.classList.remove(
                        'is-current'
                    )
                );

            let currentStep = 1;

            if (status === 'draft') {
                currentStep = 1;
            }

            if (
                status === 'draft' &&
                publishDate
            ) {
                currentStep = 2;
            }

            if (status === 'published') {
                currentStep = 3;
            }

            const current =
                document.querySelector(
                    `[data-launch-step="${currentStep}"]`
                );

            if (current) {
                current.classList.add(
                    'is-current'
                );
            }
        }

        /* =========================================================
           LANDING PREVIEW
           ========================================================= */

        function updateLandingPreview() {

            const title =
                titleInput?.value.trim()
                || 'Название курса';

            const description =
                shortDescriptionInput?.value.trim()
                || 'Краткое описание курса появится здесь.';

            const teacher =
                teacherNameInput?.value.trim()
                || 'Имя преподавателя';

            const teacherBio =
                teacherBioInput?.value.trim()
                || 'Описание преподавателя появится здесь.';

            const result =
                resultInput?.value.trim()
                || 'Результат курса будет показан здесь.';

            const whoFor =
                whoForInput?.value.trim()
                || 'Здесь появится описание целевой аудитории.';

            const uniqueValue =
                uniqueValueInput?.value.trim()
                || 'Здесь появится главное отличие курса.';

            const price =
                Math.max(
                    Number(priceInput?.value) || 0,
                    0
                );

            const titleElement =
                document.getElementById(
                    'landingPreviewTitle'
                );

            const leadElement =
                document.getElementById(
                    'landingPreviewLead'
                );

            const priceElement =
                document.getElementById(
                    'landingPreviewPrice'
                );

            const teacherElement =
                document.getElementById(
                    'landingPreviewTeacher'
                );

            const teacherBioElement =
                document.getElementById(
                    'landingPreviewTeacherBio'
                );

            const resultElement =
                document.getElementById(
                    'landingPreviewResult'
                );

            const whoForElement =
                document.getElementById(
                    'landingPreviewWhoFor'
                );

            const uniqueElement =
                document.getElementById(
                    'landingPreviewUniqueValue'
                );

            const avatarElement =
                document.getElementById(
                    'landingPreviewAvatar'
                );

            const accessElement =
                document.getElementById(
                    'landingPreviewAccess'
                );

            const startElement =
                document.getElementById(
                    'landingPreviewStart'
                );

            if (titleElement) {
                titleElement.textContent =
                    title;
            }

            if (leadElement) {
                leadElement.textContent =
                    description;
            }

            if (priceElement) {

                priceElement.innerHTML =
                    `${money(price)} <small>за курс</small>`;
            }

            if (teacherElement) {
                teacherElement.textContent =
                    teacher;
            }

            if (teacherBioElement) {
                teacherBioElement.textContent =
                    teacherBio;
            }

            if (resultElement) {
                resultElement.textContent =
                    result;
            }

            if (whoForElement) {
                whoForElement.textContent =
                    whoFor;
            }

            if (uniqueElement) {
                uniqueElement.textContent =
                    uniqueValue;
            }

            if (avatarElement) {

                avatarElement.textContent =
                    teacher
                        .trim()
                        .charAt(0)
                        .toUpperCase() || 'П';
            }

            if (accessElement) {

                const type =
                    accessTypeInput?.value || 'free';

                const duration =
                    Number(
                        accessDurationInput?.value
                    ) || 0;

                let accessText =
                    'Бесплатный доступ';

                if (type === 'subscription') {
                    accessText =
                        'Доступ по подписке';
                } else if (type === 'lifetime') {
                    accessText =
                        'Бессрочный доступ';
                } else if (type === 'fixed') {
                    accessText =
                        `Доступ на ${duration} дней`;
                }

                accessElement.textContent =
                    accessText;
            }

            if (startElement) {

                if (startDateInput?.value) {

                    startElement.textContent =
                        `Старт: ${startDateInput.value}`;

                } else {

                    startElement.textContent =
                        '';
                }
            }

            updateLandingAdvantages();
        }

        function updateLandingAdvantages() {

            const container =
                document.getElementById(
                    'landingPreviewAdvantages'
                );

            if (!container) {
                return;
            }

            const values =
                Array.from(
                    document.querySelectorAll(
                        '.advantage-input'
                    )
                )
                    .map(
                        input => input.value.trim()
                    )
                    .filter(Boolean);

            container.innerHTML = '';

            if (!values.length) {

                const li =
                    document.createElement('li');

                li.innerHTML =
                    '<i class="bi bi-info-circle"></i>' +
                    '<span>Преимущества курса пока не заполнены.</span>';

                container.appendChild(li);

                return;
            }

            values.forEach(value => {

                const li =
                    document.createElement('li');

                li.innerHTML =
                    '<i class="bi bi-check-circle-fill"></i>';

                const span =
                    document.createElement('span');

                span.textContent =
                    value;

                li.appendChild(span);

                container.appendChild(li);
            });
        }

        /* =========================================================
           EDITOR
           ========================================================= */

        document
            .querySelectorAll(
                '.editor-toolbar button[data-command]'
            )
            .forEach(button => {

                button.addEventListener(
                    'click',
                    function () {

                        const command =
                            this.dataset.command;

                        const value =
                            this.dataset.value || null;

                        if (!editor) {
                            return;
                        }

                        editor.focus();

                        if (
                            command === 'createLink'
                        ) {

                            const url =
                                window.prompt(
                                    'Введите URL ссылки:'
                                );

                            if (!url) {
                                return;
                            }

                            document.execCommand(
                                command,
                                false,
                                url
                            );

                        } else {

                            document.execCommand(
                                command,
                                false,
                                value
                            );
                        }

                        syncDescription(true);
                        inspectFullDescription();
                        markDirty();
                    }
                );
            });

        function syncDescription(notify = false) {

            if (
                !editor ||
                !hiddenDescriptionInput
            ) {
                return;
            }

            hiddenDescriptionInput.value =
                editor.innerHTML;

            if (notify) {
                hiddenDescriptionInput.dispatchEvent(
                    new Event('input', {
                        bubbles: true
                    })
                );
            }
        }

        if (editor) {

            editor.addEventListener(
                'input',
                function () {

                    syncDescription(true);
                    inspectFullDescription();
                    calculateQualityScore();
                    markDirty();
                }
            );
        }

        /* =========================================================
           GENERIC INPUT BINDINGS
           ========================================================= */

        const inspections = [

            [titleInput, inspectTitle],
            [shortDescriptionInput, inspectShortDescription],
            [promiseInput, inspectPromise],
            [whoForInput, inspectWhoFor],
            [resultInput, inspectResult],
            [uniqueValueInput, inspectUniqueValue],
            [objectiveInput, inspectObjective],
            [requirementsInput, inspectRequirements],
            [teacherNameInput, inspectTeacherName],
            [teacherBioInput, inspectTeacherBio],
            [faqInput, inspectFaq],
            [metaTitleInput, inspectMetaTitle],
            [metaDescriptionInput, inspectMetaDescription],
            [promoPlanInput, inspectPromoPlan]

        ];

        inspections.forEach(
            ([input, handler]) => {

                if (!input) {
                    return;
                }

                input.addEventListener(
                    'input',
                    function () {

                        handler();
                        calculateQualityScore();
                        markDirty();
                        updateSearchPreview();
                        updateLandingPreview();
                    }
                );
            }
        );

        [
            priceInput,
            taxRateInput,
            comparableMinInput,
            comparableMaxInput,
            learnerValueInput,
            subscriptionPriceInput,
            subscriptionMonthsInput,
            subscriptionStudentsInput,
            subscriptionRetentionInput
        ].forEach(input => {

            if (!input) {
                return;
            }

            input.addEventListener(
                'input',
                function () {

                    calculateRevenue();
                    markDirty();
                    updateLandingPreview();
                }
            );

            input.addEventListener(
                'change',
                function () {

                    calculateRevenue();
                    markDirty();
                    updateLandingPreview();
                }
            );
        });

        if (accessTypeInput) {

            accessTypeInput.addEventListener(
                'change',
                function () {

                    updateSubscriptionVisibility();
                    updateAccessConsequences();
                    updateLandingPreview();
                    markDirty();
                }
            );
        }

        if (accessDurationInput) {

            accessDurationInput.addEventListener(
                'input',
                function () {

                    updateAccessConsequences();
                    updateLandingPreview();
                    markDirty();
                }
            );
        }

        if (startDateInput) {

            startDateInput.addEventListener(
                'change',
                function () {

                    updateStartDateWarning();
                    updateLandingPreview();
                    markDirty();
                }
            );
        }

        if (statusInput) {

            statusInput.addEventListener(
                'change',
                function () {

                    updateStatusUI();
                    markDirty();
                }
            );
        }

        if (publishDateInput) {

            publishDateInput.addEventListener(
                'change',
                function () {

                    updateLaunchFlow();
                    markDirty();
                }
            );
        }

        [
            document.getElementById('language'),
            document.getElementById('level'),
            document.getElementById('format'),
            document.getElementById('certificate')
        ].forEach(input => {

            if (!input) {
                return;
            }

            input.addEventListener(
                'change',
                function () {

                    markDirty();
                    calculateQualityScore();
                }
            );
        });

        /* =========================================================
           DELETE CONFIRMATION
           ========================================================= */

        if (
            confirmDeleteCheck &&
            confirmDeleteButton
        ) {

            confirmDeleteCheck.addEventListener(
                'change',
                function () {

                    confirmDeleteButton.disabled =
                        !this.checked;
                }
            );
        }

        /* =========================================================
           SUBMIT
           ========================================================= */

        let formWasSubmitted =
            false;

        form.addEventListener(
            'submit',
            function () {

                syncDescription();

                formWasSubmitted =
                    true;
            }
        );

        window.addEventListener(
            'beforeunload',
            function (event) {

                if (
                    !formWasSubmitted &&
                    unsavedState &&
                    unsavedState.classList.contains(
                        'is-dirty'
                    )
                ) {

                    event.preventDefault();
                    event.returnValue = '';
                }
            }
        );

        /* =========================================================
           INITIAL STATE
           ========================================================= */

        syncDescription();

        inspectTitle();
        inspectShortDescription();
        inspectPromise();
        inspectWhoFor();
        inspectResult();
        inspectUniqueValue();
        inspectObjective();
        inspectRequirements();
        inspectTeacherName();
        inspectTeacherBio();
        inspectFullDescription();
        inspectFaq();
        inspectMetaTitle();
        inspectMetaDescription();
        inspectPromoPlan();

        updateSearchPreview();

        calculateQualityScore();

        calculateRevenue();

        updateSubscriptionVisibility();

        updateAccessConsequences();

        updateStartDateWarning();

        updateStatusUI();

        updateLandingPreview();

        updateLandingAdvantages();
}
