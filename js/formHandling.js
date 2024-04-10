const selectors = {
    form: '#formTask',
    fileLabel: "label[for='file']:first",
    image: '#image',
    modal: '#cart',
    modalBody: '#cart .modal-body',
    modalImage: '#cart #img_modal',
};

let prevFormValues; // Объект для хранения предыдущих значений полей

$(document).ready(function () {
    $(document.body).on('click', '[data-cs-focusable]', function () {
        // Получаем экземпляр CKEditor по его ID (в данном случае, "content")
        let ckeditorInstance = CKEDITOR.instances['content'];
        // Проверяем, существует ли экземпляр CKEditor
        if (ckeditorInstance) {
            // Устанавливаем фокус в текстовую область CKEditor
            ckeditorInstance.focus();
        }
    });

    initCkEditor();
    configureValidator();
    initFormValidation($(selectors.form));

    // обработчики событий
    $(document.body).on('change', '#file', onFileFieldChange);
    $(document.body).on('click', '#buttonPreview', onPreviewButtonClick);
});

// инициализация CK Editor
function initCkEditor() {
    CKEDITOR.replace('content', {
        toolbar: [{name: 'basicstyles', items: ['Bold', 'Italic', 'Underline']}],
        removeButtons: '',
        stylesSet: [{name: 'Underline', element: 'u'}],
    });
}

// конфигурация валидатора
function configureValidator() {
    // валидация полей
    $.validator.addMethod('nameRule', function (value, element) {
        return this.optional(element) || /^[a-zA-ZА-я0-9\s]{3,20}$/.test(value);
    });

    $.validator.addMethod('fileSize', function (value, element, param) {
        return this.optional(element) || (element.files[0].size <= param);
    });
}

// инициализация валидации на форме
function initFormValidation($form) {
    $form.validate({
        ignore: [],
        debug: false,
        rules: {
            author: {
                required: true,
                nameRule: true,
            },
            email: {
                required: true,
                email: true,
            },
            content: {
                required: function () {
                    CKEDITOR.instances.content.updateElement();
                },
                minlength: 20,
                maxlength: 300,
            },
            file: {
                required: false,
                extension: 'jpg|jpeg|png|gif',
                fileSize: 5242880, // 5 MB
            },
        },
        messages: {
            author: {
                required: 'Пожалуйста, заполните ваше имя как автора задачи.',
                nameRule:
                    'Ваше имя как автора задачи должно содержать буквы или цифры и быть длиной от 3 до 20 символов.',
            },
            email: 'Пожалуйста, введите ваш корректный email адрес.',
            content: {
                required: 'Пожалуйста, заполните текст вашей задачи.',
                minlength: 'Текст вашей задачи должен содержать не менее 20 символов.',
                maxlength: 'Текст вашей задачи не должен превышать 300 символов.',
            },
            file: {
                extension:
                    'Пожалуйста, загрузите файл в одном из следующих форматов: JPG, JPEG, PNG, GIF.',
                fileSize: 'Размер загружаемого файла не должен превышать 5 Мб.',
            },
        },
        errorPlacement: function (error, element) {
            // Кастомизация вывода ошибки для поля "content"
            if (element.attr('name') === 'content') {
                error.appendTo('#content-error-container');
                return;
            }

            // Кастомизация вывода ошибки для поля "file"
            if (element.attr('name') === 'file') {
                error.addClass('invalid-label');
                $('label[for="file"]').after(error);
                return;
            }

            // Вывод ошибок для других полей по умолчанию
            error.insertAfter(element);
        },
        // Переопределение стандартного highlight для поля "content"
        highlight: function (element, errorClass, validClass) {
            let elem = $(element).attr('id');
            if (elem === 'content' || elem === 'file') {
                $(element).removeClass(errorClass).addClass(validClass);
            } else {
                $(element).addClass(errorClass).removeClass(validClass);
            }
        },
        submitHandler: function () {
            return true;
        }
    });
}

// обработчик изменений файла
function onFileFieldChange() {
    const $form = $(selectors.form);
    const {fileName, file} = getFormValues($form);

    $(selectors.image).empty();
    $(selectors.fileLabel).text(fileName.replace(/^.*[\\\/]/, ''));

    if (!$form.valid()) {
        return;
    }
    // вставка изображения в верхний блок
    getImageSrc(file).then(function onReady(src) {
        $('<img />', {src}).appendTo(selectors.image);
    });
}

// обработчик нажатия на превью
function onPreviewButtonClick() {
    const $form = $(selectors.form);

    // Если форма не валидная - выход
    if (!$form.valid()) {
        return;
    }

    const currentFormValues = getFormValues($form);

    // Если в форме ввели новые данные - отправляем их на сервер => пишем в кэш ответ,
    if (areFormValuesEqual(currentFormValues, prevFormValues)) {
        $(selectors.modal).modal();
        return;
    }

    sendRequest(currentFormValues)
        .then(onPreviewRequestSuccess, onPreviewRequestError)
        .then(function saveCurrentValues() {
            prevFormValues = currentFormValues;
        });
}

// получить поля из формы
// также отдает объект `file` если он есть, если нет NULL
function getFormValues($form) {
    const $fileField = $form.find('#file');
    const fileName = $fileField.val();
    const file = fileName ? $fileField[0].files[0] : null;

    return {
        author: $form.find('#author').val(),
        email: $form.find('#email').val(),
        content: CKEDITOR.instances.content.getData(),
        fileName,
        file,
    };
}

// конвертирует файл в data-url строку
function getImageSrc(file) {
    return new Promise(function (resolve) {
        let reader = new FileReader();
        reader.onload = function (e) {
            resolve(e.target.result);
        };
        reader.readAsDataURL(file);
    });
}

// Функция для определения изменений в форме
function areFormValuesEqual(current, prev) {
    // console.log(prev, current);
    if (!prev) {
        // console.log(123);
        return false;
    }

    return (
        current.author === prev.author &&
        current.email === prev.email &&
        current.content === prev.content &&
        current.fileName === prev.fileName
    );
}

function sendRequest(formValues) {
    const formData = new FormData();

    formData.append('author', formValues.author);
    formData.append('email', formValues.email);
    formData.append('content', formValues.content);

    if (formValues.file) {
        formData.append('file', formValues.file);
    }

    return $.ajax({
        type: 'POST',
        url: 'preview',
        data: formData,
        cache: false, // кэш и прочие настройки писать именно так (для файлов)
        contentType: false, // нужно указать тип контента false для картинки(файла)
        processData: false, // для передачи картинки(файла) нужно false
    });
}

// обработчик успешного запроса на превью
function onPreviewRequestSuccess(res) {
    if (!res) {
        alert('Ошибка');
        return;
    }

    $(selectors.modalBody).html(res);
    $(selectors.modalImage).attr('src', $(selectors.image).attr('src'));
    $(selectors.modal).modal();
}

// обработчик нeудaчнoгo зaвeршeния зaпрoсa к сeрвeру
function onPreviewRequestError(err) {
    alert(err); // и тeкст oшибки
}