const user = {

    init: function () {
        const subscribeButtons = document.querySelectorAll('.subscribeButton');

        for (let index = 0; index < subscribeButtons.length; index++) {
            subscribeButtons[index].addEventListener('click', user.handleSubscription);

        }

    },

    handleSubscription: function (e) {
        e.preventDefault()
        if (e.currentTarget.classList.contains('subscribed')) {
            e.currentTarget.classList.remove('subscribed');
            e.currentTarget.classList.add('notSubscribed');
            e.currentTarget.innerHTML = "<i class='bi bi-person-plus mr-3'></i>Suivre";
        } else {
            e.currentTarget.classList.remove('notSubscribed');
            e.currentTarget.classList.add('subscribed');
            e.currentTarget.innerHTML = "<i class='bi bi-person-plus-fill mr-3'></i>Suivi";
        }
    }
}

document.addEventListener('DOMContentLoaded', user.init);