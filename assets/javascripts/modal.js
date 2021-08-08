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
    }

}
document.addEventListener('DOMContentLoaded', modal.init)