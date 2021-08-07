post = {

    init: function () {

        const posts = document.querySelectorAll('.postBody');

        document.querySelector('.commentButton').addEventListener('click', app.handleCommentDisplay);
        document.querySelector('.numberOfComments').addEventListener('click', app.handleCommentDisplay);

        const commentButtons = document.querySelectorAll('.commentButton');
        const commentSection = document.querySelectorAll('.numberOfComments');

        for (let index = 0; index < posts.length; index++) {
            posts[index].addEventListener('click', app.handlePostDisplay);
        }

        for (let index = 0; index < posts.length; index++) {
            commentButtons[index].addEventListener('click', app.handleCommentDisplay);
        }

        for (let index = 0; index < posts.length; index++) {
            commentSection[index].addEventListener('click', app.handleCommentDisplay);
        }
    },
    handlePostDisplay: function (e) {
        e.preventDefault();
        const postToDisplay = e.target;
        const moreLink = postToDisplay.parentNode.nextSibling;
        moreLink.style.display = 'none';

        postToDisplay.removeAttribute('style');

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

    }
}


document.addEventListener('DOMContentLoaded', app.init)