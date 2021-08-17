const user = {

    init: function () {
        const subscribeButtons = document.querySelectorAll('.subscribeButton');

        for (let index = 0; index < subscribeButtons.length; index++) {
            subscribeButtons[index].addEventListener('click', user.handleSubscription);

        }

    },

    handleSubscription: function (e) {
        e.preventDefault();
        const idOfUserToHandle = e.currentTarget.dataset.id;

        if (e.currentTarget.classList.contains('subscribed')) {

            user.AJAXSubscriptionHandler(idOfUserToHandle, "remove");
            e.currentTarget.classList.add('notSubscribed');
            e.currentTarget.classList.remove('subscribed');
            e.currentTarget.innerHTML = "<i class='bi bi-person-plus mr-3'></i>Suivre";

        } else {
            user.AJAXSubscriptionHandler(idOfUserToHandle, "add");
            e.currentTarget.classList.remove('notSubscribed');
            e.currentTarget.classList.add('subscribed');
            e.currentTarget.innerHTML = "<i class='bi bi-person-plus-fill mr-3'></i>Suivi";
        }
    },
    AJAXSubscriptionHandler: function (id, action) {
        const xhttp = new XMLHttpRequest();

        xhttp.onreadystatechange = function () {
            if (this.readyState === 4 && this.status === 200) {
                return true;
            }
        };

        xhttp.open('GET', '/profile/' + id + '/subscribers/' + action, true);
        xhttp.setRequestHeader("X-Requested-With", "XMLHttpRequest");
        xhttp.send();
    }
}

document.addEventListener('DOMContentLoaded', user.init);