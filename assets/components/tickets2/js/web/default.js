var Tickets2 = {
    initialize: function () {
        if (typeof window['prettyPrint'] != 'function') {
            $.getScript(Tickets2Config.jsUrl + 'lib/prettify/prettify.js', function () {
                prettyPrint();
            });
            $('<link/>', {
                rel: 'stylesheet',
                type: 'text/css',
                href: Tickets2Config.jsUrl + 'lib/prettify/prettify.css'
            }).appendTo('head');
        }
        if (typeof window['Sortable'] != 'function') {
            document.write('<script src="' + Tickets2Config.jsUrl + 'lib/sortable/Sortable.min.js"><\/script>');
            document.write('<script src="' + Tickets2Config.jsUrl + 'lib/sortable/jquery.binding.js"><\/script>');
        }
        if (!jQuery().ajaxForm) {
            document.write('<script src="' + Tickets2Config.jsUrl + 'lib/jquery.form.min.js"><\/script>');
        }
        if (!jQuery().jGrowl) {
            document.write('<script src="' + Tickets2Config.jsUrl + 'lib/jquery.jgrowl.min.js"><\/script>');
        }
        if (!jQuery().sisyphus) {
            document.write('<script src="' + Tickets2Config.jsUrl + 'lib/jquery.sisyphus.min.js"><\/script>');
        }

        // Forms listeners
        $(document).on('click', '#comment-preview-placeholder a', function () {
            return false;
        });
        $(document).on('change', '#comments-subscribe', function () {
            Tickets2.comment.subscribe($('[name="thread"]', $('#comment-form')));
        });
        $(document).on('change', '#tickets2-subscribe', function () {
            Tickets2.ticket.subscribe($(this).data('id'));
        });
        $(document).on('change', '#tickets2-author-subscribe', function () {
            Tickets2.author.subscribe($(this).data('id'));
        });
        $(document).on('submit', '#ticketForm', function (e) {
            Tickets2.ticket.save(this, $(this).find('[type="submit"]')[0]);
            e.preventDefault();
            return false;
        });
        $(document).on('submit', '#comment-form', function (e) {
            Tickets2.comment.save(this, $(this).find('[type="submit"]')[0]);
            e.preventDefault();
            return false;
        });
        // Preview and submit
        $(document).on('click touchend', '#ticketForm .preview, #ticketForm .save, #ticketForm .draft, #ticketForm .publish', function (e) {
            if ($(this).hasClass('preview')) {
                Tickets2.ticket.preview(this.form, this);
            }
            else {
                Tickets2.ticket.save(this.form, this);
            }
            e.preventDefault();
            return false;
        });
        // Delete
        $(document).on('click touchend', '#ticketForm .delete, #ticketForm .undelete', function (e) {
            var confirm_text = $(this).attr('data-confirm');
            var param = $(this).hasClass('delete')?'delete':'undelete';
            if (confirm(confirm_text)) {
                Tickets2.ticket.delete(this.form, this, param);
            }
            e.preventDefault();
            return false;
        });
        $(document).on('click touchend', '#comment-form .preview, #comment-form .submit', function (e) {
            if ($(this).hasClass('preview')) {
                Tickets2.comment.preview(this.form, this);
            }
            else {
                Tickets2.comment.save(this.form, this);
            }
            e.preventDefault();
            return false;
        });
        // Hotkeys
        $(document).on('keydown', '#ticketForm, #comment-form', function (e) {
            if (e.keyCode == 13) {
                if (e.shiftKey && (e.ctrlKey || e.metaKey)) {
                    $(this).submit();
                }
                else if ((e.ctrlKey || e.metaKey)) {
                    $(this).find('input[type="button"].preview').click();
                }
            }
        });
        // Show and hide forms
        $(document).on('click touchend', '#comment-new-link a', function (e) {
            Tickets2.forms.comment();
            e.preventDefault();
            return false;
        });
        $(document).on('click touchend', '.comment-reply a', function (e) {
            var id = $(this).parents('.ticket-comment').data('id');
            if ($(this).hasClass('reply')) {
                Tickets2.forms.reply(id);
            }
            else if ($(this).hasClass('edit')) {
                Tickets2.forms.edit(id);
            }
            e.preventDefault();
            return false;
        });
        // Votes and rating
        $(document).on('click touchend', '.ticket-comment-rating.active > .vote', function (e) {
            var id = $(this).parents('.ticket-comment').data('id');
            if ($(this).hasClass('plus')) {
                Tickets2.Vote.comment.vote(this, id, 1);
            }
            else if ($(this).hasClass('minus')) {
                Tickets2.Vote.comment.vote(this, id, -1);
            }
            e.preventDefault();
            return false;
        });
        $(document).on('click touchend', '.ticket-rating.active > .vote', function (e) {
            var id = $(this).parents('.ticket-meta').data('id');
            if ($(this).hasClass('plus')) {
                Tickets2.Vote.ticket.vote(this, id, 1);
            }
            else if ($(this).hasClass('minus')) {
                Tickets2.Vote.ticket.vote(this, id, -1);
            }
            else {
                Tickets2.Vote.ticket.vote(this, id, 0);
            }
            e.preventDefault();
            return false;
        });
        // --
        // Stars
        $(document).on('click touchend', '.ticket-comment-star.active > .star', function (e) {
            var id = $(this).parents('.ticket-comment').data('id');
            Tickets2.Star.comment.star(this, id, 0);
            e.preventDefault();
            return false;
        });
        $(document).on('click touchend', '.ticket-star.active > .star', function (e) {
            var id = $(this).parents('.ticket-meta').data('id');
            Tickets2.Star.ticket.star(this, id, 0);
            e.preventDefault();
            return false;
        });

        $(document).ready(function () {
            if (Tickets2Config.enable_editor == true) {
                $('#ticket-editor').markItUp(Tickets2Config.editor.ticket);
            }
            if (Tickets2Config.enable_editor == true) {
                $('#comment-editor').markItUp(Tickets2Config.editor.comment);
            }

            $.jGrowl.defaults.closerTemplate = '<div>[ ' + Tickets2Config.close_all_message + ' ]</div>';

            var count = $('.ticket-comment').length;
            $('#comment-total, .ticket-comments-count').text(count);

            $("#ticketForm.create").sisyphus({
                excludeFields: $('#ticketForm .disable-sisyphus')
            });

            // Auto hide new comment button
            if ($('#comment-form').is(':visible')) {
                $('#comment-new-link').hide();
            }
        });

        // Link to parent comment
        $('#comments').on('click touchend', '.ticket-comment-up a', function () {
            var id = $(this).data('id');
            var parent = $(this).data('parent');
            if (parent && id) {
                Tickets2.utils.goto('comment-' + parent);
                $('#comment-' + parent + ' .ticket-comment-down:lt(1)').show().find('a').attr('data-child', id);
            }
            return false;
        });

        // Link to child comment
        $('#comments').on('click touchend', '.ticket-comment-down a', function () {
            var child = $(this).data('child');
            if (child) {
                Tickets2.utils.goto('comment-' + child);
            }
            $(this).attr('data-child', '').parent().hide();
            return false;
        });
    },

    ticket: {
        preview: function (form, button) {
            $(form).ajaxSubmit({
                data: {action: 'ticket/preview'},
                url: Tickets2Config.actionUrl,
                form: form,
                button: button,
                dataType: 'json',
                beforeSubmit: function () {
                    $(button).attr('disabled', 'disabled');
                    return true;
                },
                success: function (response) {
                    $(document).trigger('tickets2_ticket_preview', response);
                    var element = $('#ticket-preview-placeholder');
                    if (response.success) {
                        element.html(response.data.preview).show();
                        prettyPrint();
                    }
                    else {
                        element.html('').hide();
                        Tickets2.Message.error(response.message);
                    }
                    $(button).removeAttr('disabled');
                }
            });
        },

        delete: function (form, button, action) {
            $(form).ajaxSubmit({
                data: {action: 'ticket/'+action},
                url: Tickets2Config.actionUrl,
                form: form,
                button: button,
                dataType: 'json',
                beforeSubmit: function () {
                    $(button).attr('disabled', 'disabled');
                    return true;
                },
                success: function (response) {
                    $(document).trigger('tickets2_ticket_'+action, response);
                    if (response.success) {
                        if (response.message) {
                            Tickets2.Message.success(response.message);
                        }
                        if (response.data.redirect) {
                            document.location.href = response.data.redirect;
                        }
                    }
                    else {
                        Tickets2.Message.error(response.message);
                    }
                    $(button).removeAttr('disabled');
                }
            });
        },

        save: function (form, button) {
            var action = 'ticket/';
            switch ($(button).prop('name')) {
                case 'draft':
                    action += 'draft';
                    break;
                case 'save':
                    action += 'save';
                    break;
                default:
                    action += 'publish';
                    break;
            }

            $(form).ajaxSubmit({
                data: {action: action},
                url: Tickets2Config.actionUrl,
                form: form,
                button: button,
                dataType: 'json',
                beforeSubmit: function () {
                    $(form).find('input[type="submit"], input[type="button"]').attr('disabled', 'disabled');
                    $('.error', form).text('');
                    return true;
                },
                success: function (response) {
                    $(document).trigger('tickets2_ticket_save', response);
                    $('#ticketForm.create').sisyphus().manuallyReleaseData();

                    if (response.success) {
                        if (response.message) {
                            Tickets2.Message.success(response.message);
                        }
                        if (action == 'ticket/save') {
                            $(form).find('input[type="submit"], input[type="button"]').removeAttr('disabled');
                            if (response.data['content']) {
                                $('#ticket-editor').val(response.data['content']);
                            }
                            $('#ticket-files-list').find('.deleted').each(function() {
                                $(this).remove();
                            })
                        }
                        else if (response.data.redirect) {
                            document.location.href = response.data.redirect;
                        }
                    }
                    else {
                        $(form).find('input[type="submit"], input[type="button"]').removeAttr('disabled');
                        Tickets2.Message.error(response.message);
                        if (response.data) {
                            var i, field;
                            for (i in response.data) {
                                field = response.data[i];
                                $(form).find('[name="' + field.field + '"]').parent().find('.error').text(field.message)
                                var elem = $(form).find('[name="' + field.field + '"]').parent().find('.error');
                                if (!elem.length) {
                                    elem = $(form).find('#' + field.field + '-error');
                                }
                                if (elem.length) {
                                    elem.text(field.message)
                                }
                            }
                        }
                    }
                }
            });
        },

        subscribe: function (section) {
            if (section) {
                $.post(Tickets2Config.actionUrl, {action: "section/subscribe", section: section}, function (response) {
                    if (response.success) {
                        Tickets2.Message.success(response.message);
                    }
                    else {
                        Tickets2.Message.error(response.message);
                    }
                }, 'json');
            }
        }
    },

    comment: {
        preview: function (form, button) {
            $(form).ajaxSubmit({
                data: {action: 'comment/preview'},
                url: Tickets2Config.actionUrl,
                form: form,
                button: button,
                dataType: 'json',
                beforeSubmit: function () {
                    $(button).attr('disabled', 'disabled');
                    return true;
                },
                success: function (response) {
                    $(document).trigger('tickets2_comment_preview', response);
                    $(button).removeAttr('disabled');
                    if (response.success) {
                        $('#comment-preview-placeholder').html(response.data.preview).show();
                        prettyPrint();
                    }
                    else {
                        Tickets2.Message.error(response.message);
                    }
                }
            });
            return false;
        },

        save: function (form, button) {
            $(form).ajaxSubmit({
                data: {action: 'comment/save'},
                url: Tickets2Config.actionUrl,
                form: form,
                button: button,
                dataType: 'json',
                beforeSubmit: function () {
                    clearInterval(window.timer);
                    $('.error', form).text('');
                    $(button).attr('disabled', 'disabled');
                    return true;
                },
                success: function (response) {
                    $(button).removeAttr('disabled');
                    $(document).trigger('tickets2_comment_save', response);
                    if (response.success) {
                        Tickets2.forms.comment(false);
                        $('#comment-preview-placeholder').html('').hide();
                        $('#comment-editor', form).val('');
                        $('.ticket-comment .comment-reply a').show();

                        // autoPublish = 0
                        if (!response.data.length && response.message) {
                            Tickets2.Message.info(response.message);
                        }
                        else {
                            Tickets2.comment.insert(response.data.comment);
                            Tickets2.utils.goto($(response.data.comment).attr('id'));
                        }

                        Tickets2.comment.getlist();
                        prettyPrint();
                    }
                    else {
                        Tickets2.Message.error(response.message);
                        if (response.data) {
                            var errors = [];
                            var i, field;
                            for (i in response.data) {
                                field = response.data[i];
                                var elem = $(form).find('[name="' + field.field + '"]').parent().find('.error');
                                if (!elem.length) {
                                    elem = $(form).find('#' + field.field + '-error');
                                }
                                if (elem.length) {
                                    elem.text(field.message)
                                }
                                else if (field.field && field.message) {
                                    errors.push(field.field + ': ' + field.message);
                                }
                            }
                            if (errors.length) {
                                Tickets2.Message.error(errors.join('<br/>'));
                            }
                        }
                    }
                    if (response.data.captcha) {
                        $('input[name="captcha"]', form).val('').focus();
                        $('#comment-captcha', form).text(response.data.captcha);
                    }
                }
            });
            return false;
        },

        getlist: function () {
            var form = $('#comment-form');
            var thread = $('[name="thread"]', form);
            if (!thread) {
                return false;
            }
            Tickets2.tpanel.start();
            $.post(Tickets2Config.actionUrl, {action: 'comment/getlist', thread: thread.val()}, function (response) {
                for (var k in response.data.comments) {
                    if (response.data.comments.hasOwnProperty(k)) {
                        Tickets2.comment.insert(response.data.comments[k], true);
                    }
                }
                var count = $('.ticket-comment').length;
                $('#comment-total, .ticket-comments-count').text(count);

                Tickets2.tpanel.stop();
            }, 'json');
            return true;
        },

        insert: function (data, remove) {
            var comment = $(data);
            var parent = $(comment).attr('data-parent');
            var id = $(comment).attr('id');
            var exists = $('#' + id);
            var children = '';

            if (exists.length > 0) {
                var np = exists.data('newparent');
                comment.attr('data-newparent', np);
                data = comment[0].outerHTML;
                if (remove) {
                    children = exists.find('.comments-list').html();
                    exists.remove();
                }
                else {
                    exists.replaceWith(data);
                    return;
                }
            }

            if (parent == 0 && Tickets2Config.formBefore) {
                $('#comments').prepend(data)
            }
            else if (parent == 0) {
                $('#comments').append(data)
            }
            else {
                var pcomm = $('#comment-' + parent);
                if (pcomm.data('parent') != pcomm.data('newparent')) {
                    parent = pcomm.data('newparent');
                    comment.attr('data-newparent', parent);
                    data = comment[0].outerHTML;
                }
                else if (Tickets2Config.thread_depth) {
                    var level = pcomm.parents('.ticket-comment').length;
                    if (level > 0 && level >= (Tickets2Config.thread_depth - 1)) {
                        parent = pcomm.data('parent');
                        comment.attr('data-newparent', parent);
                        data = comment[0].outerHTML;
                    }
                }
                $('#comment-' + parent + ' > .comments-list').append(data);
            }

            if (children.length > 0) {
                $('#' + id).find('.comments-list').html(children);
            }
        },

        subscribe: function (thread) {
            if (thread.length) {
                $.post(Tickets2Config.actionUrl, {
                    action: "comment/subscribe",
                    thread: thread.val()
                }, function (response) {
                    if (response.success) {
                        Tickets2.Message.success(response.message);
                    }
                    else {
                        Tickets2.Message.error(response.message);
                    }
                }, 'json');
            }
        }
    },

    author: {
        subscribe: function (author) {
            if (author) {
                $.post(Tickets2Config.actionUrl, {action: "author/subscribe", author: author}, function (response) {
                    if (response.success) {
                        Tickets2.Message.success(response.message);
                    }
                    else {
                        Tickets2.Message.error(response.message);
                    }
                }, 'json');
            }
        }
    },

    forms: {
        reply: function (comment_id) {
            $('#comment-new-link').show();

            clearInterval(window.timer);
            var form = $('#comment-form');
            $('.time', form).text('');
            $('.ticket-comment .comment-reply a').show();

            $('#comment-preview-placeholder').hide();
            $('input[name="parent"]', form).val(comment_id);
            $('input[name="id"]', form).val(0);
            if (typeof Tickets2.StartPlupload != 'undefined') {
                $('#ticket-files-list .ticket-file').remove();
                Tickets2.Uploader.destroy();
                Tickets2.StartPlupload();
            }

            var reply = $('#comment-' + comment_id + ' > .comment-reply');
            form.insertAfter(reply).show();
            $('a', reply).hide();
            reply.parents('.ticket-comment').removeClass('ticket-comment-new');

            $('#comment-editor', form).val('').focus();
            return false;
        },

        comment: function (focus) {
            if (focus !== false) {
                focus = true;
            }
            clearInterval(window.timer);

            $('#comment-new-link').hide();

            var form = $('#comment-form');
            $('.time', form).text('');
            $('.ticket-comment .comment-reply a:hidden').show();

            $('#comment-preview-placeholder').hide();
            $('input[name="parent"]', form).val(0);
            $('input[name="id"]', form).val(0);
            if (typeof Tickets2.StartPlupload != 'undefined') {
                $('#ticket-files-list .ticket-file').remove();
                Tickets2.Uploader.destroy();
                Tickets2.StartPlupload();
            }
            $(form).insertAfter('#comment-form-placeholder').show();

            $('#comment-editor', form).val('');
            if (focus) {
                $('#comment-editor', form).focus();
            }
            return false;
        },

        edit: function (comment_id) {
            $('#comment-new-link').show();

            var thread = $('#comment-form [name="thread"]').val();
            var form_key = $('#comment-form [name="form_key"]').val();
            $.post(Tickets2Config.actionUrl, {
                action: "comment/get",
                id: comment_id,
                thread: thread,
                form_key: form_key
            }, function (response) {
                if (!response.success) {
                    Tickets2.Message.error(response.message);
                }
                else {
                    clearInterval(window.timer);
                    $('.ticket-comment .comment-reply a:hidden').show();
                    var form = $('#comment-form');
                    $('#comment-preview-placeholder').hide();
                    $('input[name="parent"]', form).val(0);
                    $('input[name="id"]', form).val(comment_id);
                    if (typeof Tickets2.StartPlupload != 'undefined') {
                        if ($('.comment-form-files').length && response.data.files) {
                            $('.comment-form-files').html(response.data.files);
                        }
                        Tickets2.Uploader.destroy();
                        Tickets2.StartPlupload(comment_id);
                    }

                    var reply = $('#comment-' + comment_id + ' > .comment-reply');
                    var time_left = $('.time', form);

                    time_left.text('');
                    form.insertAfter(reply).show();
                    $('a', reply).hide();

                    $('#comment-editor', form).val(response.data.raw).focus();
                    if (response.data.name) {
                        $('[name="name"]', form).val(response.data.name);
                    }
                    if (response.data.email) {
                        $('[name="email"]', form).val(response.data.email);
                    }

                    var time = response.data.time;
                    window.timer = setInterval(function () {
                        if (time > 0) {
                            time -= 1;
                            time_left.text(Tickets2.utils.timer(time));
                        }
                        else {
                            clearInterval(window.timer);
                            time_left.text('');
                            //Tickets2.forms.comment();
                        }
                    }, 1000);
                }
            }, 'json');

            return false;
        }
    },

    utils: {
        timer: function (diff) {
            days = Math.floor(diff / (60 * 60 * 24));
            hours = Math.floor(diff / (60 * 60));
            mins = Math.floor(diff / (60));
            secs = Math.floor(diff);

            dd = days;
            hh = hours - days * 24;
            mm = mins - hours * 60;
            ss = secs - mins * 60;

            var result = [];

            if (hh > 0) result.push(hh ? this.addzero(hh) : '00');
            result.push(mm ? this.addzero(mm) : '00');
            result.push(ss ? this.addzero(ss) : '00');

            return result.join(':');
        },

        addzero: function (n) {
            return (n < 10) ? '0' + n : n;
        },

        goto: function (id) {
            $('html, body').animate({
                scrollTop: $('#' + id).offset().top
            }, 1000);
        }
    }
};


Tickets2.Message = {
    success: function (message) {
        if (message) {
            $.jGrowl(message, {theme: 'tickets2-message-success'});
        }
    },
    error: function (message) {
        if (message) {
            $.jGrowl(message, {theme: 'tickets2-message-error'/*, sticky: true*/});
        }
    },
    info: function (message) {
        if (message) {
            $.jGrowl(message, {theme: 'tickets2-message-info'});
        }
    },
    close: function () {
        $.jGrowl('close');
    }
};


Tickets2.Vote = {

    comment: {
        options: {
            active: 'active',
            inactive: 'inactive',
            voted: 'voted',
            vote: 'vote',
            rating: 'rating',
            positive: 'positive',
            negative: 'negative'
        },
        vote: function (link, id, value) {
            link = $(link);
            var parent = link.parent();
            var options = this.options;
            var rating = parent.find('.' + options.rating);
            if (parent.hasClass(options.inactive)) {
                return false;
            }

            $.post(Tickets2Config.actionUrl, {action: 'comment/vote', id: id, value: value}, function (response) {
                if (response.success) {
                    link.addClass(options.voted);
                    parent.removeClass(options.active).addClass(options.inactive);
                    parent.find('.' + options.vote);
                    rating.text(response.data.rating).attr('title', response.data.title);

                    rating.removeClass(options.positive + ' ' + options.negative);
                    if (response.data.status == 1) {
                        rating.addClass(options.positive);
                    }
                    else if (response.data.status == -1) {
                        rating.addClass(options.negative);
                    }
                }
                else {
                    Tickets2.Message.error(response.message);
                }
            }, 'json');

            return true;
        }
    },
    ticket: {
        options: {
            active: 'active',
            inactive: 'inactive',
            voted: 'voted',
            vote: 'vote',
            rating: 'rating',
            positive: 'positive',
            negative: 'negative'
        },
        vote: function (link, id, value) {
            link = $(link);
            var parent = link.parent();
            var options = this.options;
            var rating = parent.find('.' + options.rating);
            if (parent.hasClass(options.inactive)) {
                return false;
            }

            $.post(Tickets2Config.actionUrl, {action: 'ticket/vote', id: id, value: value}, function (response) {
                if (response.success) {
                    link.addClass(options.voted);
                    parent.removeClass(options.active).addClass(options.inactive);
                    parent.find('.' + options.vote);
                    rating.text(response.data.rating).attr('title', response.data.title).removeClass(options.vote);

                    rating.removeClass(options.positive + ' ' + options.negative);
                    if (response.data.status == 1) {
                        rating.addClass(options.positive);
                    }
                    else if (response.data.status == -1) {
                        rating.addClass(options.negative);
                    }
                }
                else {
                    Tickets2.Message.error(response.message);
                }
            }, 'json');

            return true;
        }
    }
};


Tickets2.Star = {
    comment: {
        options: {
            stared: 'stared',
            unstared: 'unstared'
            //,count: 'ticket-comment-star-count'
        },
        star: function (link, id, value) {
            link = $(link);
            var options = this.options;
            var parent = link.parent();

            $.post(Tickets2Config.actionUrl, {action: 'comment/star', id: id}, function (response) {
                if (response.success) {
                    link.toggleClass(options.stared).toggleClass(options.unstared);
                }
                else {
                    Tickets2.Message.error(response.message);
                }
            }, 'json');

            return true;
        }
    },
    ticket: {
        options: {
            stared: 'stared',
            unstared: 'unstared',
            count: 'ticket-star-count'
        },
        star: function (link, id, value) {
            link = $(link);
            var options = this.options;
            var count = link.parent().find('.' + this.options.count);

            $.post(Tickets2Config.actionUrl, {action: 'ticket/star', id: id}, function (response) {
                if (response.success) {
                    link.toggleClass(options.stared).toggleClass(options.unstared);
                    count.text(response.data.stars);
                }
                else {
                    Tickets2.Message.error(response.message);
                }
            }, 'json');

            return true;
        }
    }
};


Tickets2.tpanel = {
    wrapper: $('#comments-tpanel'),
    refresh: $('#tpanel-refresh'),
    new_comments: $('#tpanel-new'),
    class_new: 'ticket-comment-new',

    initialize: function () {
        if (Tickets2Config.tpanel) {
            this.wrapper.show();
            this.stop();
        }

        this.refresh.on('click', function () {
            $('.' + Tickets2.tpanel.class_new).removeClass(Tickets2.tpanel.class_new);
            Tickets2.comment.getlist();
        });

        this.new_comments.on('click', function () {
            var elem = $('.' + Tickets2.tpanel.class_new + ':first');
            $('html, body').animate({
                scrollTop: elem.offset().top
            }, 1000, 'linear', function () {
                elem.removeClass(Tickets2.tpanel.class_new);
            });

            var count = parseInt(Tickets2.tpanel.new_comments.text());
            if (count > 1) {
                Tickets2.tpanel.new_comments.text(count - 1);
            }
            else {
                Tickets2.tpanel.new_comments.text('').hide();
            }
        });
    },

    start: function () {
        this.refresh.addClass('loading');
    },

    stop: function () {
        var count = $('.' + this.class_new).length;
        if (count > 0) {
            this.new_comments.text(count).show();
        }
        else {
            this.new_comments.hide();
        }
        this.refresh.removeClass('loading');
    }

};
if (typeof Tickets2Config != 'undefined') {
    Tickets2.initialize();
    Tickets2.tpanel.initialize();
}
