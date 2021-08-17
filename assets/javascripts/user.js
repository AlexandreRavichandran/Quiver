const user = {

    init: function () {
        const subscribeButtons = document.querySelectorAll('.subscribeButton');

        for (let index = 0; index < subscribeButtons.length; index++) {
            subscribeButtons[index].addEventListener('click', user.handleSubscriptionButton);

        }

        const subscribeLinks = document.querySelectorAll('.subscribeLink');

        for (let index = 0; index < subscribeLinks.length; index++) {
            subscribeLinks[index].addEventListener('click', user.handleSubscriptionLinks);
        }

    },

    handleSubscriptionLinks: function (e) {
        e.preventDefault();
        const currentLink = e.currentTarget;

        if (currentLink.classList.contains('link-subscribed')) {

            user.AJAXSubscriptionHandler(currentLink.dataset.id, "remove");
            currentLink.classList.add('link-unsubscribed');
            currentLink.classList.remove('link-subscribed');
            currentLink.textContent = "Suivre";

        } else {
            user.AJAXSubscriptionHandler(currentLink.dataset.id, "add");
            currentLink.classList.remove('link-unsubscribed');
            currentLink.classList.add('link-subscribed');
            currentLink.textContent = "Suivi";
        }

    },

    handleSubscriptionButton: function (e) {
        e.preventDefault();
        const idOfUserToHandle = e.currentTarget;

        if (idOfUserToHandle.classList.contains('subscribed')) {

            user.AJAXSubscriptionHandler(idOfUserToHandle.dataset.id, "remove");
            idOfUserToHandle.classList.add('notSubscribed');
            idOfUserToHandle.classList.remove('subscribed');
            idOfUserToHandle.innerHTML = "<i class='bi bi-person-plus mr-3'></i>Suivre";

        } else {
            user.AJAXSubscriptionHandler(idOfUserToHandle.dataset.id, "add");
            idOfUserToHandle.classList.remove('notSubscribed');
            idOfUserToHandle.classList.add('subscribed');
            idOfUserToHandle.innerHTML = "<i class='bi bi-person-plus-fill mr-3'></i>Suivi";
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