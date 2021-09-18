const modal = {

    init: function () {

        const openmodal = document.querySelectorAll('.modal-open');

        for (var i = 0; i < openmodal.length; i++) {
            openmodal[i].addEventListener('click', modal.openModal);
        }

        const closemodal = document.querySelectorAll('.modal-close');
        for (var i = 0; i < closemodal.length; i++) {
            closemodal[i].addEventListener('click', modal.closeModal)
        }

        const addSpacesButton = document.querySelector('.addSpaces');
        addSpacesButton.addEventListener('click', modal.showSpaces);


    },
    openModal: function (e) {

        const body = document.querySelector('body')
        const modal = e.currentTarget.parentNode.querySelector('.modal');

        modal.classList.remove('opacity-0')
        modal.classList.remove('pointer-events-none')
        body.classList.add('modal-active')

    },
    closeModal: function (e) {
        const modal = e.currentTarget.closest('.modal');
        const body = document.querySelector('body')

        modal.classList.add('opacity-0')
        modal.classList.add('pointer-events-none')
        body.classList.remove('modal-active')
    },
    showSpaces: function (e) {
        const questionId = e.currentTarget.closest('.answerHeader').dataset.questionId;

        fetch('/spaces/questions/' + questionId)
            .then(function (response) {
                if (response.status === 200) {
                    return response.json();
                }
            })
            .then(function (responseJson) {
                const spaceModal = document.querySelector('#modalSpaceList');
                spaceModal.innerHTML = '';
                for (const spaceObject of responseJson) {
                    const spaceCheckbox = document.createElement('input');
                    spaceCheckbox.setAttribute('type', 'checkbox');
                    spaceCheckbox.setAttribute('name', "spaces[]");
                    spaceCheckbox.setAttribute('id', spaceObject.id);
                    spaceCheckbox.setAttribute('value', spaceObject.id);
                    spaceCheckbox.classList.add('self-center');
                    const spaceLabel = document.createElement('label')
                    spaceLabel.setAttribute('for', spaceObject.id);
                    spaceLabel.textContent = spaceObject.name;
                    spaceLabel.classList.add('ml-2', 'mb-1');
                    const spaceBox = document.createElement('div');
                    spaceBox.classList.add('flex');
                    spaceBox.append(spaceCheckbox, spaceLabel);
                    spaceModal.appendChild(spaceBox);
                }
            })
    }

}
document.addEventListener('DOMContentLoaded', modal.init)