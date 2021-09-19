
const post = {

    init: function () {

        const posts = document.querySelectorAll('.postBody');
        const commentButtons = document.querySelectorAll('.commentButton');
        const commentSection = document.querySelectorAll('.numberOfComments');
        const likeButtons = document.querySelectorAll('.likeButton');
        const dislikeButtons = document.querySelectorAll('.dislikeButton');
        const flashMessageButtons = document.querySelectorAll('.flashMessageCloseButton');
        const loadMoreCommentsButton = document.querySelectorAll('.loadMoreComments');
        const displaySubCommentForm = document.querySelectorAll('.subCommentFormButton');
        const generateHomePostButton = document.querySelector('#generateHome a');
        const commentForm = document.querySelectorAll('.commentForm form');
        const subCommentForm = document.querySelectorAll('.subCommentForm form');
        const generateAnswerButton = document.querySelector('#generateAnswers a');
        const answerButton = document.querySelector('.answerButton');
        const multipleAnswerButton = document.querySelectorAll('.multipleAnswerButton');
        const postAnswerButton = document.querySelector('#answerPostButton');
        const multiplePostAnswerButton = document.querySelectorAll('.multiplePostAnswerButton')
        const generateFollowingPostButton = document.querySelector('#generateFollowing a');

        for (let index = 0; index < posts.length; index++) {
            posts[index].addEventListener('click', post.handlePostDisplay);
        }

        for (let index = 0; index < loadMoreCommentsButton.length; index++) {
            loadMoreCommentsButton[index].addEventListener('click', post.showMoreComments);
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

        for (let index = 0; index < displaySubCommentForm.length; index++) {
            displaySubCommentForm[index].addEventListener('click', post.handleSubCommentFormDisplay);
        }

        for (let index = 0; index < commentForm.length; index++) {
            commentForm[index].addEventListener('submit', post.handleCommentForm);
        }

        for (let index = 0; index < subCommentForm.length; index++) {
            subCommentForm[index].addEventListener('submit', post.handleSubCommentForm);
        }

        for (let index = 0; index < multipleAnswerButton.length; index++) {
            multipleAnswerButton[index].addEventListener('click', post.showEditor);
        }

        for (let index = 0; index < multiplePostAnswerButton.length; index++) {
            multiplePostAnswerButton[index].addEventListener('click', post.postAnswer);
        }

        for (let index = 0; index < multiplePostAnswerButton.length; index++) {
            multiplePostAnswerButton[index].addEventListener('click', post.postAnswer);
        }


        if (generateHomePostButton) {
            generateHomePostButton.addEventListener('click', post.handleMoreHomePostButton)
        }

        if (generateAnswerButton) {
            generateAnswerButton.addEventListener('click', post.generateAnswers);
        }

        if (answerButton) {
            answerButton.addEventListener('click', post.showEditor);
        }

        if (postAnswerButton) {
            postAnswerButton.addEventListener('click', post.postAnswer);
        }
        if (generateFollowingPostButton) {
            generateFollowingPostButton.addEventListener('click', post.handleMorePostButton);
        }

    },

    handlePostDisplay: function (e) {
        e.preventDefault();
        const postToDisplay = e.target.closest('.postBody');
        const moreLink = postToDisplay.querySelector('.moreLink');


        moreLink.style.display = 'none';
        postToDisplay.querySelector('.answer').removeAttribute('style');

    },

    showMoreComments: function (e) {
        e.preventDefault();
        const clickedElement = e.currentTarget;
        const commentContainer = clickedElement.closest('.comments').querySelector('.commentList');
        const commentLoaderSpinner = clickedElement.closest('.comments').querySelector('.loadingMoreCommentsSpinner');
        const postId = clickedElement.closest('.questionAnswer').dataset.answerId;
        const comments = commentContainer.querySelectorAll('.commentheader');
        const lastCommentDate = comments[comments.length - 1].dataset.commentDate;
        clickedElement.classList.add('hidden');
        commentLoaderSpinner.classList.remove('hidden');
        fetch('/answer/comments/' + postId + '/' + lastCommentDate)
            .then(function (response) {
                if (response.status === 200) {
                    return response.json();
                }
            })

            .then(function (responseJson) {
                if (responseJson.content !== '') {
                    clickedElement.classList.remove('hidden');
                    commentContainer.innerHTML += responseJson.content;
                    post.init();
                }
                commentLoaderSpinner.classList.add('hidden');
            })
    },

    handleCommentDisplay: function (e) {
        e.preventDefault();
        const clickedPost = e.currentTarget.closest('.questionAnswer')
        const postId = clickedPost.dataset.answerId;
        const postFooter = clickedPost.querySelector('.postFooter');
        const commentsNumber = postFooter.querySelector('.numberOfComments');
        const commentSection = postFooter.querySelector('.comments');
        const commentContainer = postFooter.querySelector('.commentList');

        if (commentsNumber.style.display == 'block') {

            commentSection.style.display = 'block';
            commentsNumber.style.display = 'none';
            const loading = postFooter.querySelector('.loadingMoreCommentsSpinner');
            loading.classList.remove('hidden');
            fetch('/answer/comments/' + postId)
                .then(function (response) {
                    if (response.status === 200) {
                        return response.json();
                    }
                })
                .then(function (responseJson) {
                    loading.classList.add('hidden');
                    commentContainer.innerHTML = responseJson.content;
                    post.init();
                })

        } else {
            commentsNumber.style.display = 'block';
            commentSection.style.display = 'none';
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
        fetch('/api/answers/' + answerId + '/' + action)
            .then(function (response) {
                if (response.status === 200) {
                    return response.json();

                }
            })
            .then(function (responseJson) {
                const likeNumber = document.querySelector('#answer_' + responseJson.answerId + '_likeNumber');
                const dislikeNumber = document.querySelector('#answer_' + responseJson.answerId + '_dislikeNumber');
                likeNumber.textContent = responseJson.likeNumber;
                dislikeNumber.textContent = responseJson.dislikeNumber;
            })


    },

    handleFlashMessageButton: function (e) {
        e.preventDefault();
        const flashMessage = e.currentTarget.closest('.flashMessage');
        flashMessage.animate({ opacity: ['1', '0'] }, 500).onfinish = function () {
            flashMessage.style.opacity = "0";
            flashMessage.style.display = "none";
        }
    },

    handleMoreHomePostButton: function (e) {
        e.preventDefault();
        const clickedElement = e.currentTarget;
        const lastElement = document.querySelector('#content').lastElementChild;
        const lastDate = lastElement.dataset.questionsDate;
        clickedElement.closest('#generateHome').classList.add('hidden');

        document.querySelector('.loadingMorePostsSpinner').classList.remove('hidden');

        fetch('/questions/generate/' + lastDate)
            .then(function (response) {
                if (response.status === 200) {
                    return response.json();
                }
            })
            .then(function (responseJson) {
                if (responseJson.content !== '') {
                    document.querySelector('#content').innerHTML += responseJson.content;
                    post.init();
                    clickedElement.closest('#generateHome').classList.remove('hidden');
                }
                document.querySelector('.loadingMorePostsSpinner').classList.add('hidden');

            });
    },
    handleSubCommentFormDisplay: function (e) {
        e.preventDefault();
        const subCommentForm = e.currentTarget.closest('.commentFooter').querySelector('.subCommentForm');
        if (subCommentForm.classList.contains('hidden')) {
            subCommentForm.classList.remove('hidden');
        } else {
            subCommentForm.classList.add('hidden');
        }

    },

    handleCommentForm: function (e) {
        e.preventDefault();
        const currentTarget = e.currentTarget;
        const comment = currentTarget.querySelector('.commentSpace');
        const answerId = currentTarget.closest('.questionAnswer').dataset.answerId;
        const data = { 'comment': comment.value, 'answerId': answerId };
        const config = {
            method: 'POST',
            body: JSON.stringify(data),
            headers: {
                'Content-Type': 'application/json'
            }
        }
        fetch('/comments/create', config).then(function (response) {
            if (response.status === 201) {
                return response.json();
            } else {
                const error = response.json();
                throw error;
            }
        })

            .then(function (response) {
                post.showMessage(response.message)
                const template = document.querySelector('#commentTemplate').content.cloneNode(true);
                template.querySelector('.name').textContent = response.user;
                template.querySelector('.date').textContent = response.date;
                const formattedDate = response.date.replaceAll('/', '-');
                template.querySelector('.commentheader').dataset.commentDate = formattedDate;
                template.querySelector('.comment').textContent = response.comment;
                currentTarget.closest('.comments').querySelector('.commentList').prepend(template);
                post.init();
                comment.value = '';
            })
            .catch(function (error) {
                return error;
            })
            .then(function (errorJson) {
                for (const error of errorJson.message) {
                    post.showMessage(error);
                }
            })
    },

    handleSubCommentForm: function (e) {
        e.preventDefault();
        const currentTarget = e.currentTarget;
        const comment = currentTarget.querySelector('.commentSpace');
        const commentId = currentTarget.closest('.comment').dataset.commentId;
        const data = { 'subComment': comment.value, 'commentId': commentId };
        const config = {
            method: 'POST',
            body: JSON.stringify(data),
            headers: {
                'Content-Type': 'application/json'
            }
        }
        fetch('/subComments/create', config)
            .then(function (response) {
                if (response.status === 201) {
                    return response.json();
                } else {
                    const error = response.json();
                    throw error;
                }
            })
            .then(function (response) {
                post.showMessage(response.message);
                const template = document.querySelector('#subCommentTemplate').content.cloneNode(true);
                template.querySelector('.subCommentName').textContent = response.user;
                template.querySelector('.subCommentDate').textContent = response.date;
                template.querySelector('.subCommentContent').textContent = response.comment;
                currentTarget.closest('.comment').querySelector('.subComments').prepend(template);
                currentTarget.classList.add('hidden');
                comment.value = '';
            })
            .catch(function (error) {
                return error;
            })
            .then(function (errorJson) {
                for (const error of errorJson.message) {
                    post.showMessage(error);
                }
            })
    },
    generateAnswers: function (e) {
        e.preventDefault();
        const clickedElement = e.currentTarget;
        const lastAnswer = document.querySelector('.content').lastElementChild;
        const id = lastAnswer.dataset.questionId;
        const date = lastAnswer.dataset.answerDate;
        clickedElement.closest('#generateAnswers').classList.add('hidden');
        document.querySelector('.loadingMoreAnswersSpinner').classList.remove('hidden');
        fetch('/questions/' + id + '/generate/' + date)

            .then(function (response) {
                if (response.status === 200) {
                    return response.json();
                }
            })

            .then(function (responseJson) {
                if (responseJson.content !== '') {
                    document.querySelector('#content').innerHTML += responseJson.content;
                    post.init();
                    clickedElement.closest('#generateHome').classList.remove('hidden');
                }
                document.querySelector('.loadingMoreAnswersSpinner').classList.add('hidden');
            })

    },
    showEditor: function (e) {
        e.preventDefault();
        e.currentTarget.classList.add('hidden');
        const question = e.currentTarget.closest('.answerHeader');
        const editorSpace = question.querySelector('#editor');
        ClassicEditor
            .create(editorSpace, {
                ckfinder: {
                    uploadUrl: '/answer/picture/add',
                },
                removePlugins: ['Heading'],
                toolbar: ['imageUpload', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote'],
            })
            .catch(error => {
                console.log(error);
            });
        if (question.querySelector('#answerPostButton')) {
            question.querySelector('#answerPostButton').classList.remove('hidden');

        } else {
            question.querySelector('.multiplePostAnswerButton').classList.remove('hidden');
        }

    },

    postAnswer: function (e) {
        e.preventDefault();
        const currentTarget = e.currentTarget
        const editorSpace = currentTarget.closest('.answerHeader').querySelector('.ck-editor__editable');
        const questionid = currentTarget.closest('.answerHeader').dataset.questionId;
        const data = { 'answer': editorSpace.innerHTML, 'questionId': questionid, 'user': user };
        const config = {
            method: 'POST',
            body: JSON.stringify(data),
            headers: {
                'Content-Type': 'application/json'
            }
        }
        if (editorSpace.textContent.length > 0) {
            fetch('/answers/create', config)
                .then(function (response) {
                    if (response.status === 201) {
                        return response.json();
                    } else {
                        const error = response.json();
                        throw error.json();
                    }
                })
                .then(function (responseJson) {
                    post.showMessage(responseJson.message);
                    if (document.querySelector('.content')) {
                        document.querySelector('.content').innerHTML += responseJson.content;
                        document.querySelector('#answerNumber').textContent++;
                        document.querySelector('.answerButton').classList.remove('hidden');
                        editorSpace.closest('.ck-editor').remove();
                        document.querySelector('#answerPostButton').classList.add('hidden');
                        post.init();
                    } else {
                        window.location.href = ('/questions/' + questionid);
                    }
                    return { then: function () { } };
                })
                .catch(function (error) {
                    return error;
                })
                .then(function (errorJson) {
                    for (const error of errorJson.message) {
                        post.showMessage(error);
                    }
                })
        }
    },

    handleMorePostButton: function (e) {
        e.preventDefault();
        const currentTarget = e.currentTarget;
        const lastElement = document.querySelector('#content').lastElementChild;
        const lastDate = lastElement.dataset.questionsDate;
        currentTarget.closest('#generateFollowing').classList.add('hidden');

        document.querySelector('.loadingMoreFollowingPostsSpinner').classList.remove('hidden');

        fetch('/following/generate/' + lastDate)
            .then(function (response) {
                if (response.status === 200) {
                    return response.json();
                }
            })
            .then(function (responseJson) {
                if (responseJson.content !== '') {
                    document.querySelector('#content').innerHTML += responseJson.content;
                    post.init();
                    currentTarget.closest('#generateFollowing').classList.remove('hidden');
                }
                document.querySelector('.loadingMoreFollowingPostsSpinner').classList.add('hidden');
            })
    },

    showMessage: function (message) {
        const messageSpace = document.querySelector('#messagesSpace');
        messageSpace.innerHTML = message;
        const flashMmessage = document.querySelector('.flashMessage');
        flashMmessage.animate({ opacity: ['0', '1'] }, 500).onfinish = function () {
            flashMmessage.style.opacity = "1";
        }
        post.init();
    }
}



document.addEventListener('DOMContentLoaded', post.init)
