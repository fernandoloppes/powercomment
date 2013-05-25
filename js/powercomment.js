/*global powercomment_params */
jQuery(document).ready(function($) {
    $("#commentform").validate({
        rules: {
            author: {
                required: true,
                minlength: 2
            },
            email: {
                required: true,
                email: true
            },
            url: {
                url: true
            },
            comment: {
                required: true,
                minlength: powercomment_params.comment_limit
            }
        },
        messages: {
            author: powercomment_params.author,
            email: powercomment_params.email,
            url: powercomment_params.url,
            comment: powercomment_params.comment
        },
        showErrors: function(errorMap, errorList) {
            this.defaultShowErrors();
            $.each(errorList, function(i, error) {
                $(error.element).next('label.error').css({
                    "color": powercomment_params.text,
                    "background": powercomment_params.background,
                    "border": "1px solid " + powercomment_params.border,
                    "display": "block",
                    "margin": "5px 0 10px",
                    "padding": "5px"
                });
            });
        }
    });
});
