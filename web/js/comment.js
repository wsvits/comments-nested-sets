/**
 * Get comments by parent id
 * @param id
 */
function getComments(id) {
    loaderShow(id, 1);
    $.ajax({
        method: 'GET',
        url: '/comments/' + id,
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        success: function(data) {
            loaderShow(id, 0);
            buildTree(data.data);
        },
        error: function(data) {
            loaderShow(id, 0);
            alert('Error while request comments');
        }
    });
}

/**
 * Create comment request
 * @param id
 * @param text
 */
function createComment(id, text) {
    loaderShow(id, 1);
    $.ajax({
        method: 'POST',
        url: '/comments/' + id,
        data: JSON.stringify({"text": text}),
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        success: function(data) {
            loaderShow(id, 0);
            if (data.success) {
                addComment(id, data.data);
            } else {
                alert(data.error)
            }
        },
        error: function(data) {
            loaderShow(id, 0);
            alert('Error while create comment');
        }
    });
}

/**
 * Update comment request
 * @param id
 * @param text
 */
function updateComment(id, text) {
    loaderShow(id, 1);
    $.ajax({
        method: 'PUT',
        url: '/comments/' + id,
        data: JSON.stringify({"text": text}),
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        success: function(data) {
            if (data && data.success) {
                loaderShow(id, 0);
            }
        },
        error: function(data) {
            loaderShow(id, 0);
            alert('Error while update comment');
        }
    });
}

/**
 * Delete comment request
 * @param id
 */
function deleteComment(id) {
    loaderShow(id, 1);
    $.ajax({
        method: 'DELETE',
        url: '/comments/' + id,
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        success: function(data) {
            if (data && data.success) {
                loaderShow(id, 0);
                $('#comment_' + id).remove();
            }
        },
        error: function(data) {
            loaderShow(id, 0);
            alert('Error while delete comment');
        }
    });
}

/**
 * Build comments tree from server response
 * @param list
 */
function buildTree(list) {
    for (var i = 0; i < list.length; i++) {
        addComment(list[i].parent_id, list[i]);
    }
}

function expandComments(e) {
    var id = $(this).attr('id').replace(/comment_btn_/, '');
    $(this).toggleClass('dec');
    $(this).parent().toggleClass('dec');

    var needLoad = $(this).hasClass('dec');

    if (needLoad) {
        if ($('#comment_ul_' + id)) {
            $('#comment_ul_' + id).remove();
        }
        getComments(id);
    }
}

/**
 * Show comment form
 * @param e
 */
function showCommentForm(e) {
    e.preventDefault();
    var commentId = $(this).parent().parent().attr('id').replace(/comment_/, '');
    var formExist = $('#comment_form' + commentId).length;

    if ($('#comment_btn_' + commentId).length != 0 && !$('#comment_btn_' + commentId).hasClass('dec')) {
        $('#comment_btn_' + commentId).click();
    }

    // if form exist
    if (formExist) {
        return;
    }

    var form = '<div id="reply_block' + commentId + '">' +
        '<form action="/comments/' + commentId + '" method="post" id="comment_form' + commentId + '" class="comment_form">' +
        '<textarea name="text" class="comment_text" rows="4" onfocus="if(this.value==this.defaultValue)this.value=\'\';" onblur="if(this.value==\'\')this.value=this.defaultValue;">Your comment</textarea>' +
        '<input type="submit" class="comment_submit_btn comment_button" value="Submit" />' +
        '<input type="button" class="comment_cancel_btn comment_button" value="Cancel" />' +
        '</form></div>';

    $('#comment_' + commentId).append(form);
    $('#comment_form' + commentId).submit(sendComment);
    $('#reply_block' + commentId + ' .comment_cancel_btn').click(cancelComment);
}

/**
 * Cancel new comment
 * @param e
 */
function cancelComment(e) {
    e.preventDefault();
    var commentId = $(this).parent().attr('id').replace(/comment_form/, '');
    $('#reply_block' + commentId).remove();
}

/**
 * Remove comment
 * @param e
 */
function removeComment(e) {
    e.preventDefault();
    var commentId = $(this).parent().parent().attr('id').replace(/comment_/, '');

    deleteComment(commentId);
}

/**
 * Show/hide wait-loader when request server
 * @param commentId
 * @param show
 */
function loaderShow(commentId, show) {
    if (show) {
        $('#comment_' + commentId).append('<div id="wait_block' + commentId + '" class="wait_block"><img src="/images/ajax-loader.gif"/></div>');
    } else {
        $('#wait_block' + commentId).remove();
    }
}

/**
 * Validate and send comment from form via AJAX
 * @param e
 */
function sendComment(e){
    e.preventDefault();

    var commentId = Number($(this).attr("id").replace(/comment_form/, ''));
    var text = $(this).find('.comment_text').val();

    if (text == '') {
        alert('Please fill the required fields (comment)');
        return false;
    }

    $('#reply_block' + commentId).remove();
    createComment(commentId, text);
}

/**
 * Create html-block for comment dynamically
 * @param parentCommentId
 * @param comment
 */
function addComment(parentCommentId, comment) {
    var commentHtml = '<li id="comment_' + comment.id + '">';

    if (parentCommentId == 0) {
        commentHtml += '<div class="button" id="comment_btn_' + comment.id + '">+</div>';
    }

    commentHtml += '<span class="node"><span class="text" id="comment_text_' + comment.id + '">' + comment.text + '</span>' +
    '<a href="#" class="comment_add" id="comment_add_' + comment.id + '">Add comment</a>' +
    '<a href="#" class="comment_del" id="comment_del_' + comment.id + '">Delete</a>' +
    '<a href="#" class="comment_edit" id="comment_edit_' + comment.id + '">Edit</a>' +
    '</span>';
    commentHtml += '</li>';

    if (!$('#comment_ul_' + parentCommentId).length) {
        if (!$('#reply_block' + parentCommentId).length) {
            $('#comment_' + parentCommentId).append('<ul id="comment_ul_' + parentCommentId + '"></ul>');
        } else {
            $('<ul id="comment_ul_' + parentCommentId + '"></ul>').insertBefore($('#reply_block' + parentCommentId));
        }
    }

    $('#comment_ul_' + parentCommentId).append(commentHtml);
    $('#comment_btn_' + comment.id).click(expandComments);
    $('#comment_add_' + comment.id).click(showCommentForm);
    $('#comment_del_' + comment.id).click(removeComment);
    $('#comment_edit_' + comment.id).click(editComment);
}

/**
 * Validate and send comment from form via AJAX
 * @param e
 */
function sendCommentUpdate(e){
    e.preventDefault();

    var commentId = Number($(this).attr("id").replace(/comment_edit_form/, ''));
    var text = $(this).find('.comment_text').val();

    if (text == '') {
        alert('Please fill the required fields (comment)');
        return false;
    }

    $('#edit_block' + commentId).replaceWith('<span class="text" id="comment_text_' + commentId + '">' + text + '</span>');
    updateComment(commentId, text);
}

/**
 * Edit comment
 * @param e
 */
function editComment(e) {
    e.preventDefault();
    var commentId = $(this).parent().parent().attr('id').replace(/comment_/, '');
    var formExist = $('#comment_edit_form' + commentId).length;

    // if form exist
    if (formExist) {
        return;
    }

    var text = $('#comment_text_' + commentId).html();

    var form = '<div id="edit_block' + commentId + '">' +
        '<form action="/comments/' + commentId + '" method="post" id="comment_edit_form' + commentId + '" class="comment_edit_form">' +
        '<textarea name="text" class="comment_text" rows="4">' + text + '</textarea>' +
        '<input type="hidden" class="old_value" value="' + text + '" />' +
        '<input type="submit" class="comment_submit_btn comment_button" value="Submit" />' +
        '<input type="button" class="comment_cancel_btn comment_button" value="Cancel" />' +
        '</form></div>';

    $('#comment_text_' + commentId).replaceWith(form);
    $('#comment_edit_form' + commentId).submit(sendCommentUpdate);
    $('#edit_block' + commentId + ' .comment_cancel_btn').click(cancelEditComment);
}

/**
 * Cancel edit comment
 * @param e
 */
function cancelEditComment(e) {
    e.preventDefault();
    var commentId = $(this).parent().attr('id').replace(/comment_edit_form/, '');
    $('#edit_block' + commentId).replaceWith(
        '<span class="text" id="comment_text_' + commentId + '">' +
        $('#edit_block' + commentId + ' .old_value').val() +
        '</span>'
    );
}


$('.comment_layout .button').click(expandComments);
$('.comment_add').click(showCommentForm);
$('.comment_del').click(removeComment);
$('.comment_edit').click(editComment);