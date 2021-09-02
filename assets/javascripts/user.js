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

        const subscriptionFilter = document.querySelector('#subscriptionFilter');
        if (subscriptionFilter) {
            subscriptionFilter.addEventListener('change', user.filterSubscriptionPage);
        }

        const generateUser = document.querySelector('#generateUser');
        if (generateUser) {
            generateUser.addEventListener('click', user.handleGenerateUser);
        }

    },

    handleSubscription: function (target, classIfAdded, classIfRemoved) {
        if (target.classList.contains(classIfAdded)) {
            user.AJAXSubscriptionHandler(target.dataset.id, "remove");
            target.classList.add(classIfRemoved);
            target.classList.remove(classIfAdded);
        } else {
            user.AJAXSubscriptionHandler(target.dataset.id, "add");
            target.classList.remove(classIfRemoved);
            target.classList.add(classIfAdded);
        }

    },

    handleSubscriptionLinks: function (e) {
        e.preventDefault();
        const currentLink = e.currentTarget;

        user.handleSubscription(currentLink, 'link-subscribed', 'link-unsubscribed')

        if (currentLink.classList.contains('link-subscribed')) {
            currentLink.textContent = "Suivi";
        } else {
            currentLink.textContent = "Suivre";
        }

    },

    handleSubscriptionButton: function (e) {
        e.preventDefault();
        const idOfUserToHandle = e.currentTarget;

        user.handleSubscription(idOfUserToHandle, 'subscribed', 'notSubscribed')

        if (idOfUserToHandle.classList.contains('subscribed')) {
            idOfUserToHandle.innerHTML = "<i class='bi bi-person-plus-fill mr-3'></i>Suivi";

        } else {
            idOfUserToHandle.innerHTML = "<i class='bi bi-person-plus mr-3'></i>Suivre";
        }
    },

    AJAXSubscriptionHandler: function (id, action) {

        fetch('/profile/' + id + '/subscribers/' + action).then(response => response.json())
    },

    filterSubscriptionPage: function (e) {
        const elementToFilter = e.currentTarget.value;
        const subscriptionContainer = document.querySelector('.subscriptions')
        const subscriptions = subscriptionContainer.querySelectorAll('.subscription');

        for (let index = 0; index < subscriptions.length; index++) {
            if (!subscriptions[index].classList.contains(elementToFilter)) {
                subscriptions[index].classList.add('hidden');
            } else {
                subscriptions[index].classList.remove('hidden');
            }
        }
    },
    handleGenerateUser: function (e) {
        e.preventDefault();
        const email = document.querySelector('#inputEmail');
        const password = document.querySelector('#inputPassword');

        fetch('/login/user/generate').then(function (response) { return response.json() }).then(function (responseJson) {

            email.value = responseJson.email;
            password.value = 'demo';

        })

    }
}

document.addEventListener('DOMContentLoaded', user.init);