post = {

    init: function () {

        const posts = document.querySelectorAll('.postBody');
        const commentButtons = document.querySelectorAll('.commentButton');
        const commentSection = document.querySelectorAll('.numberOfComments');

        for (let index = 0; index < posts.length; index++) {
            posts[index].addEventListener('click', post.handlePostDisplay);
        }

        for (let index = 0; index < commentButtons.length; index++) {
            commentButtons[index].addEventListener('click', post.handleCommentDisplay);
        }

        for (let index = 0; index < commentSection.length; index++) {
            commentSection[index].addEventListener('click', post.handleCommentDisplay);
        }
    },
    handlePostDisplay: function (e) {
        e.preventDefault();
        const postToDisplay = e.target.closest('.postBody');
        const moreLink = postToDisplay.querySelector('.moreLink');
       
        moreLink.style.display = 'none';
        postToDisplay.querySelector('p').removeAttribute('style');

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


document.addEventListener('DOMContentLoaded', post.init)