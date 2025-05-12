function autoResizeTextarea(el) {
    el.style.height = 'auto';
    el.style.height = (el.scrollHeight) + 'px';
}

document.addEventListener('DOMContentLoaded', () => {
    const textarea = document.querySelector('.description-input');

    if (textarea) {
        autoResizeTextarea(textarea);

        textarea.addEventListener('input', () => {
            autoResizeTextarea(textarea);
        });
    }
});
