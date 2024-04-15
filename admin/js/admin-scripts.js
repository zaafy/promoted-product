// manipulateTimePicker handles showing and hiding of the time picker in custom metabox fields on product single edit page.
function manipulateTimePicker(chekcbox, ttlBox) {
    const checkboxElement = document.querySelector(`input[name="${chekcbox}"]`);
    const timeToLiveBox = document.querySelector(`.${ttlBox}`);
    if (checkboxElement.checked) {
        timeToLiveBox.classList.remove('hidden');
        timeToLiveBox.classList.add('visible');
    } else {
        timeToLiveBox.classList.add('hidden');
        timeToLiveBox.classList.remove('visible');
    }
}