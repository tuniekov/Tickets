Tickets2.StartPlupload = function(comment_id = 0) {
    var form = $('#comment-form');
    if (!form.length) {
        form = $('#ticketForm');
    }
    Tickets2.Uploader = new plupload.Uploader({
        runtimes: 'html5,flash,silverlight,html4',

        browse_button: 'ticket-files-select',
        //upload_button: document.getElementById('ticket-files-upload'),
        container: 'ticket-files-container',
        filelist: 'ticket-files-list',
        progress: 'ticket-files-progress',
        progress_bar: 'ticket-files-progress-bar',
        progress_count: 'ticket-files-progress-count',
        progress_percent: 'ticket-files-progress-percent',
        form: form,

        multipart_params: {
            action: $('#ticket-files-container').data('action') || 'ticket/file/upload',
            tid: form.find('[name="tid"]').val(),
            form_key: form.find('[name="form_key"]').val(),
            ctx: Tickets2Config.ctx || 'web'
        },
        drop_element: 'ticket-files-list',

        url: Tickets2Config.actionUrl,

        filters: {
            max_file_size: Tickets2Config.source.size,
            mime_types: [{
                title: 'Files',
                extensions: Tickets2Config.source.extensions
            }]
        },

        resize: {
            width: Tickets2Config.source.width || 1920,
            height: Tickets2Config.source.height || 1080
        },

        flash_swf_url: Tickets2Config.jsUrl + 'web/lib/plupload/js/Moxie.swf',
        silverlight_xap_url: Tickets2Config.jsUrl + 'web/lib/plupload/js/Moxie.xap',

        init: {
            Init: function () {
                if (this.runtime == 'html5') {
                    var element = $(this.settings.drop_element);
                    element.addClass('droppable');
                    element.on('dragover', function () {
                        if (!element.hasClass('dragover')) {
                            element.addClass('dragover');
                        }
                    });
                    element.on('dragleave drop', function () {
                        element.removeClass('dragover');
                    });
                }
            },

            PostInit: function (up) {
                var element = $('#'+this.settings.filelist);
                var actionUrl = this.settings.url;
                element.sortable({
                sort: true,
                draggable: ".ticket-file",
                ghostClass: "ticket-ghost-state-highlight",
                animation: 150,
                onUpdate: function( event ) {
                    var rank = {};
                    element.find('.ticket-file').each(function(i){
                        rank[i] = $(this).data('id');
                    });

                    var data = {
                        action: 'ticket/file/sort'
                        , rank: rank
                    };

                    $.post(actionUrl, data, function(response) {
                        if (!response.success) {
                            Tickets2.Message.error(response.message);
                        }
                    }, 'json');
                }
                });
            },

            FilesAdded: function (up) {
                this.settings.form.find('[type="submit"]').attr('disabled', true);
                up.start();
            },

            UploadProgress: function (up) {
                $(up.settings.browse_button).hide();
                $('#' + up.settings.progress).show();
                $('#' + up.settings.progress_count).text((up.total.uploaded + 1) + ' / ' + up.files.length);
                $('#' + up.settings.progress_percent).text(up.total.percent + '%');
                $('#' + up.settings.progress_bar).css('width', up.total.percent + '%');
            },

            FileUploaded: function (up, file, response) {
                response = $.parseJSON(response.response);
                if (response.success) {
                    // Successful action
                    var files = $('#' + up.settings.filelist);
                    var clearfix = files.find('.clearfix');
                    if (clearfix.length != 0) {
                        $(response.data).insertBefore(clearfix);
                    }
                    else {
                        files.append(response.data);
                    }

                }
                else {
                    Tickets2.Message.error(response.message);
                }
            },

            UploadComplete: function (up) {
                $(up.settings.browse_button).show();
                $('#' + up.settings.progress).hide();
                up.total.reset();
                up.splice();
                this.settings.form.find('[type="submit"]').attr('disabled', false);
            },

            Error: function (up, err) {
                Tickets2.Message.error(err.message);
            }
        }
    });
    if (comment_id > 0) {
        Tickets2.Uploader.settings.multipart_params.tid = comment_id;
    }
    Tickets2.Uploader.init();
}

Tickets2.StartPlupload();

$(document).on('click', '.ticket-file-delete, .ticket-file-restore', function () {
    var deleted = 'deleted';
    var $this = $(this);
    var $form = $this.parents('form');
    var $parent = $this.parents('.ticket-file');
    var id = $parent.data('id');
    var form_key = $form.find('[name="form_key"]').val();

    $.post(Tickets2Config.actionUrl, {action: 'ticket/file/delete', id: id, form_key: form_key}, function (response) {
        if (response.success) {
            if ($parent.hasClass(deleted)) {
                $parent.removeClass(deleted)
            }
            else {
                $parent.addClass(deleted)
            }
        }
        else {
            Tickets2.Message.error(response.message);
        }
    }, 'json');
    return false;
});

$.fn.insertAtCaret = function (text) {
    return this.each(function () {
        if (document.selection && this.tagName == 'TEXTAREA') {
            //IE textarea support
            this.focus();
            sel = document.selection.createRange();
            sel.text = text;
            this.focus();
        } else if (this.selectionStart || this.selectionStart == '0') {
            //MOZILLA/NETSCAPE support
            startPos = this.selectionStart;
            endPos = this.selectionEnd;
            scrollTop = this.scrollTop;
            this.value = this.value.substring(0, startPos) + text + this.value.substring(endPos, this.value.length);
            this.focus();
            this.selectionStart = startPos + text.length;
            this.selectionEnd = startPos + text.length;
            this.scrollTop = scrollTop;
        } else {
            // IE input[type=text] and other browsers
            this.value += text;
            this.focus();
            this.value = this.value; // forces cursor to end
        }
    });
};

$(document).on('click', '.ticket-file-insert', function () {
    var $this = $(this);
    var $parent = $this.parents('.ticket-file');
    var $text = $('[name="content"]');
    var template = $parent.find('.ticket-file-template').html();
    template = $.trim(template.replace(/^\n/g, '').replace(/\t{2}/g, '').replace(/\t$/g, ''));

    $text.focus();
    if (Tickets2Config.enable_editor > 0) {
        $.markItUp({replaceWith: template});
    } else {
        var form = $('#comment-form');
        if (form.length) {
            var $text = $('[name="text"]');
        }
        $text.insertAtCaret(template);
    }
    return false;
});