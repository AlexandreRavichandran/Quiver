import * as addMorePosts from './post.js';
const space = {

    init: function () {

        const spaceSubscribeButton = document.querySelector('#spaceSubscribeButton');
        if (spaceSubscribeButton) {
            spaceSubscribeButton.addEventListener('click', space.subscribeButtonHandler);
        }


        const generateSpaceButton = document.querySelector('#generateSpace');
        if (generateSpaceButton) {
            generateSpaceButton.addEventListener('click', space.generateSpaces);
        }

        const showSpacePictureForm = document.querySelector('#changeSpacePicture');
        if (showSpacePictureForm) {
            showSpacePictureForm.addEventListener('click', space.showSpacePictureForm);
        }

        const spaceInput = document.querySelector('#space_picture_imageFile_file');
        if (spaceInput) {
            spaceInput.addEventListener('change', space.handleSpacePictureUpdate);
        }
    },

    generateSpaces: function (e) {
        e.preventDefault();
        const clickedButton = e.currentTarget;
        const spaceLoader = document.querySelector('.spaceLoader');
        spaceLoader.classList.remove('hidden');
        const lastSpaceId = document.querySelector('.spaceList').lastElementChild.dataset.id;
        fetch('/api/spaces/generate/' + lastSpaceId)
            .then(function (response) {
                if (response.status === 200) {
                    return response.json();
                }
            })

            .then(function (responseJson) {
                if (responseJson.content !== '') {
                    const spaceList = document.querySelector('.spaceList');
                    spaceLoader.classList.add('hidden');
                    spaceList.innerHTML += responseJson.content;
                } else {
                    spaceLoader.classList.add('hidden');
                    clickedButton.classList.add('hidden');
                }

            })
    },
    subscribeButtonHandler: function (e) {
        e.preventDefault();
        const subscribeButton = e.currentTarget;
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
        fetch('/api/spaces/' + id + '/subscribers/' + action)
            .then(function (response) {
                if (response.status === 200) {
                    return response.json();
                }
            })

    },

    showSpacePictureForm: function (e) {
        e.preventDefault();
        document.querySelector('#space_picture_imageFile_file').click();
    },
    handleSpacePictureUpdate: function (e) {
        e.currentTarget.closest('form').submit()
    }



}
document.addEventListener('DOMContentLoaded', space.init);