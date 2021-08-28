const post = {

    init: function () {

        const posts = document.querySelectorAll('.postBody');
        const commentButtons = document.querySelectorAll('.commentButton');
        const commentSection = document.querySelectorAll('.numberOfComments');
        const likeButtons = document.querySelectorAll('.likeButton');
        const dislikeButtons = document.querySelectorAll('.dislikeButton');
        const flashMessageButtons = document.querySelectorAll('.flashMessageCloseButton');


        for (let index = 0; index < posts.length; index++) {
            posts[index].addEventListener('click', post.handlePostDisplay);
        }

        for (let index = 0; index < commentButtons.length; index++) {
            commentButtons[index].addEventListener('click', post.handleCommentDisplay);
        }

        for (let index = 0; index < commentSection.length; index++) {
            commentSection[index].addEventListener('click', post.handleCommentDisplay);
        }

        for (let index = 0; index < likeButtons.length; index++) {
            likeButtons[index].addEventListener('click', post.handleLikeButton);
        }

        for (let index = 0; index < dislikeButtons.length; index++) {
            dislikeButtons[index].addEventListener('click', post.handleDislikeButton);
        }

        for (let index = 0; index < flashMessageButtons.length; index++) {
            flashMessageButtons[index].addEventListener('click', post.handleFlashMessageButton);
        }

        const generatePostButton = document.querySelector('#generateHome a');
        if (generatePostButton) {
            generatePostButton.addEventListener('click', post.handleMorePostButton)
        }
    },

    handlePostDisplay: function (e) {
        e.preventDefault();
        const postToDisplay = e.target.closest('.postBody');
        const moreLink = postToDisplay.querySelector('.moreLink');


        moreLink.style.display = 'none';
        postToDisplay.querySelector('.answer').removeAttribute('style');

    },

    handleCommentDisplay: function (e) {
        e.preventDefault();
        const postToDisplay = e.target;
        const postId = e.currentTarget.closest('.questionAnswer').dataset.answerId;

        const comments = postToDisplay.closest('.postFooter').querySelector('.numberOfComments');
        const commentList = postToDisplay.closest('.postFooter').querySelector('.comments');
        if (comments.style.display == 'block') {
            commentList.style.display = 'block';
            comments.style.display = 'none';
            const loading = postToDisplay.closest('.postFooter').querySelector('.loadingMoreommentsSpinner')
            loading.classList.remove('hidden');
            fetch('/answer/comments/' + postId).then(function (response) { return response.json() }).then(function (jsonResponse) {
                loading.classList.add('hidden');
                comments.style.display = 'none';
                commentList.style.display = 'block';
                const commentListe = postToDisplay.closest('.postFooter').querySelector('.commentList');
                commentListe.innerHTML = jsonResponse.content;
            })
        } else {
            comments.style.display = 'block';
            commentList.style.display = 'none';
        };

    },

    handleLikeButton: function (e) {
        e.preventDefault();
        const dislikeButton = e.currentTarget.closest('.questionAnswer').querySelector('.dislikeButton');
        const likeButton = e.currentTarget;
        const answerId = e.currentTarget.closest('.questionAnswer').dataset.answerId;
        post.handleLikeAction(answerId, 'liked');
        if (likeButton.classList.contains('liked')) {
            likeButton.classList.remove('liked');
        } else {
            dislikeButton.classList.remove('disliked');
            likeButton.classList.add('liked');
        }
    },

    handleDislikeButton: function (e) {
        e.preventDefault();
        const likeButton = e.currentTarget.closest('.questionAnswer').querySelector('.likeButton');
        const dislikeButton = e.currentTarget;

        const answerId = e.currentTarget.closest('.questionAnswer').dataset.answerId;
        post.handleLikeAction(answerId, 'disliked');

        if (dislikeButton.classList.contains('disliked')) {
            dislikeButton.classList.remove('disliked');
        } else {
            dislikeButton.classList.add('disliked');
            likeButton.classList.remove('liked');

        }

    },
    handleMorePostButton: function (e) {
        e.preventDefault();
        post.addMorePosts(e.currentTarget, 'questions/generate');
    },
    handleLikeAction: function (answerId, action) {
        fetch('/answers/' + answerId + '/' + action).then(response => response.json()).then(datas => {
            const likeNumber = document.querySelector('#answer_' + datas.answerId + '_likeNumber');
            const dislikeNumber = document.querySelector('#answer_' + datas.answerId + '_dislikeNumber');
            likeNumber.textContent = datas.likeNumber;
            dislikeNumber.textContent = datas.dislikeNumber;
        });


    },

    handleFlashMessageButton: function (e) {
        e.preventDefault();

        const flashMessage = e.currentTarget.closest('.flashMessage');
        flashMessage.style.display = 'none';
    },

    addMorePosts: function (clickedElement, controllerRoute) {
        const lastElement = document.querySelector('#content').lastElementChild;
        const lastDate = lastElement.dataset.questionsDate;
        clickedElement.closest('#generateHome').classList.add('hidden');

        document.querySelector('.loadingMorePostsSpinner').classList.remove('hidden');

        fetch('/' + controllerRoute + '/' + lastDate).then(response => response.json()).then(datas => {
            if (datas.content !== '') {
                document.querySelector('#content').innerHTML += datas.content;
                post.init();
                clickedElement.closest('#generateHome').classList.remove('hidden');
            } else {

            }
            document.querySelector('.loadingMorePostsSpinner').classList.add('hidden');

        });
    }
}


document.addEventListener('DOMContentLoaded', post.init)
