import * as addMorePosts from './post.js';
const space = {

    init: function () {

        const spaceSubscribeButton = document.querySelector('.spaceSubscribeButton');
        if (spaceSubscribeButton) {
            spaceSubscribeButton.addEventListener('click', space.subscribeButtonHandler);
        }
        const generatePostButton = document.querySelector('#generateFollowing a');
        if (generatePostButton) {
            generatePostButton.addEventListener('click', space.handleMorePostButton)
        }
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
        fetch('/spaces/' + id + '/subscribers/' + action).then(response => response.json());
    },

    handleMorePostButton: function (e) {
        e.preventDefault();
        addMorePosts(e.currentTarget, 'following/generate');
    }
}
document.addEventListener('DOMContentLoaded', space.init);