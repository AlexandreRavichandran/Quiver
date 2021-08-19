const space = {

    init: function () {
        const spaceSubscribeButton = document.querySelector('#spaceSubscribeButton');

        spaceSubscribeButton.addEventListener('click', space.subscribeButtonHandler);
    },

    subscribeButtonHandler: function (e) {
        e.preventDefault();
        subscribeButton = e.currentTarget;
        const isSubscribed = subscribeButton.classList.contains('subscribedSpace');
        const spaceId = e.currentTarget.dataset.id;

        if (isSubscribed) {
            space.AJAXRequestHandler(spaceId, 'remove');
            subscribeButton.classList.remove('subscribedSpace');
            subscribeButton.classList.add('notSubscribedSpace');
            subscribeButton.innerHTML = '<i class="bi bi-person-plus text-2xl mr-2"></i>Suivre';
        } else {
            space.AJAXRequestHandler(spaceId, 'add');
            subscribeButton.classList.remove('notSubscribedSpace');
            subscribeButton.classList.add('subscribedSpace');
            subscribeButton.innerHTML = '<i class="bi bi-person-check-fill text-2xl mr-2"></i>Suivi';
        }
    },

    AJAXRequestHandler: function (id, action) {
        const xhttp = new XMLHttpRequest;

        xhttp.onreadyStateChange = function () {
            return true;
        }

        xhttp.open('GET', '/spaces/' + id + '/subscribers/' + action, true);
        xhttp.send();
    }
}

document.addEventListener('DOMContentLoaded', space.init);