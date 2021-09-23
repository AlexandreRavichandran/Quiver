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

        const qualification = document.querySelector('#userQualification');
        if (qualification) {
            qualification.addEventListener('click', user.allowQualificationSetting);
        }

        const setQualification = document.querySelector('.userQualificationSetting');
        if (setQualification) {
            setQualification.addEventListener('keyup', user.updateQualification);
        }

        const description = document.querySelector('#userDescription');
        if (description) {
            description.addEventListener('click', user.allowDescriptionSetting);
        }

        const setDescription = document.querySelector('#updateDescription')
        if (setDescription) {
            setDescription.addEventListener('click', user.updateDescription);
        }

        const allowProfilePictureUpdate = document.querySelector('#changePicture');
        if (setDescription) {
            allowProfilePictureUpdate.addEventListener('click', user.showProfilePictureForm);
        }
        const profilePictureInput = document.querySelector('#user_picture_imageFile_file');
        if (profilePictureInput) {
            profilePictureInput.addEventListener('change', user.handleProfilePictureUpdate);
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

        fetch('/profile/' + id + '/subscribers/' + action)
            .then(function (response) {
                if (response.status === 200) {
                    response.json();
                }
            })
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

        fetch('/login/user/generate')
            .then(function (response) {
                if (response.status === 200) {
                    return response.json()
                }
            })
            .then(function (responseJson) {

                email.value = responseJson.email;
                password.value = 'demo';

            })

    },
    allowQualificationSetting: function (e) {
        e.preventDefault()
        e.currentTarget.classList.add('hidden');
        const qualificationInput = document.querySelector('.userQualificationSetting');
        qualificationInput.classList.remove('hidden');
        qualificationInput.value = e.currentTarget.textContent;

    },

    updateQualification: function (e) {
        if (e.keyCode === 13) {
            const newQualification = e.currentTarget;

            const data = { 'newQualification': newQualification.value };
            const config = {
                method: 'POST',
                body: JSON.stringify(data),
                headers: {
                    'Content-Type': 'application/json'
                }
            }
            fetch('/profile/qualification/update', config)
                .then(function (response) {
                    if (response.status === 201) {
                        return response.json()
                    } else {
                        const error = response.json();
                        throw error;
                    }
                })
                .then(function (responseJson) {
                    const userQualification = document.querySelector('#userQualification');
                    userQualification.classList.remove('hidden');
                    userQualification.textContent = newQualification.value;
                    newQualification.classList.add('hidden');
                })
                .catch(function (error) {
                    console.log(error);
                })
        }
    },
    allowDescriptionSetting: function (e) {
        e.preventDefault();

        e.currentTarget.classList.add('hidden');
        const descriptionInput = document.querySelector('.userDescriptionSetting');
        const updateDescriptionButton = document.querySelector('#updateDescription');
        descriptionInput.classList.remove('hidden');
        updateDescriptionButton.classList.remove('hidden');

        descriptionInput.value = e.currentTarget.textContent;

    },
    updateDescription: function (e) {

        const newDescription = document.querySelector('.userDescriptionSetting');

        const data = { 'newDescription': newDescription.value };
        const config = {
            method: 'POST',
            body: JSON.stringify(data),
            headers: {
                'Content-Type': 'application/json'
            }
        }
        fetch('/profile/description/update', config)
            .then(function (response) {
                if (response.status === 201) {
                    return response.json();
                } else {
                    const error = response.json();
                    throw error;
                }
            })
            .then(function (responseJson) {
                const userDescription = document.querySelector('#userDescription');
                const updateDescriptionButton = document.querySelector('#updateDescription')
                userDescription.textContent = newDescription.value;
                newDescription.classList.add('hidden');
                updateDescriptionButton.classList.add('hidden');
                userDescription.classList.remove('hidden');
            })
            .catch(function (error) {
                console.log(error);
            })

    },

    showMessage: function (message) {
        const messageSpace = document.querySelector('#messagesSpace');
        messageSpace.innerHTML = message;
        const flashMmessage = document.querySelector('.flashMessage');
        flashMmessage.animate({ opacity: ['0', '1'] }, 500).onfinish = function () {
            flashMmessage.style.opacity = "1";
        }
    },

    handleFlashMessageButton: function (e) {
        e.preventDefault();
        const flashMessage = e.currentTarget.closest('.flashMessage');
        flashMessage.animate({ opacity: ['1', '0'] }, 500).onfinish = function () {
            flashMessage.style.opacity = "0";
            flashMessage.style.display = "none";
        }
    },

    showProfilePictureForm: function (e) {
        e.preventDefault();
        document.querySelector('#user_picture_imageFile_file').click();
    },
    handleProfilePictureUpdate: function (e) {
        e.currentTarget.closest('form').submit()
    }
}

document.addEventListener('DOMContentLoaded', user.init);