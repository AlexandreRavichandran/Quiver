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

    },
    
    handlePostDisplay: function (e) {
        e.preventDefault();
        const postToDisplay = e.target.closest('.postBody');
        const moreLink = postToDisplay.querySelector('.moreLink');

        console.log(postToDisplay);
        moreLink.style.display = 'none';
        postToDisplay.querySelector('.answer').removeAttribute('style');

    },

    handleCommentDisplay: function (e) {
        e.preventDefault();
        const postToDisplay = e.target;
        const comments = postToDisplay.closest('.postFooter').querySelector('.numberOfComments');
        const commentList = postToDisplay.closest('.postFooter').querySelector('.comments');

        if (comments.style.display == 'block') {
            comments.style.display = 'none';
            commentList.style.display = 'block';
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

    handleLikeAction: function (answerId, action) {
        const xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function () {
            if (this.readyState === 4 && this.status === 200) {
                const datas = JSON.parse(this.responseText);
                const likeNumber = document.querySelector('#answer_' + datas.answerId + '_likeNumber');
                const dislikeNumber = document.querySelector('#answer_' + datas.answerId + '_dislikeNumber');
                likeNumber.textContent = datas.likeNumber;
                dislikeNumber.textContent = datas.dislikeNumber;
            }
        };

        xhttp.open('GET', '/answers/' + answerId + '/' + action, true);
        xhttp.setRequestHeader("X-Requested-With", "XMLHttpRequest");
        xhttp.send();
    },

    handleFlashMessageButton: function (e) {
        e.preventDefault();
        console.log(e.currentTarget);
        const flashMessage = e.currentTarget.closest('.flashMessage');
        flashMessage.style.display = 'none';
    }

}


document.addEventListener('DOMContentLoaded', post.init)