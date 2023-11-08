function flexi_show_key_pair(key){
    $('.form-access-key-pairs > div').addClass('hidden');
    const obj = $('.' + key+'-container');
    if($(obj).length){
        $('.form-access-key-pairs').removeClass('hidden');
        $(obj).removeClass('hidden');
    }
}

function sortFoldersFirst(node) {
    if (node.children) {
        node.children.sort(function(a, b) {
            if (a.icon === 'jstree-folder' && b.icon !== 'jstree-folder') {
                return -1;
            } else if (a.icon !== 'jstree-folder' && b.icon === 'jstree-folder') {
                return 1;
            }
            return 0;
        });
        node.children.forEach(function(childNode) {
            sortFoldersFirst(childNode);
        });
    }
}

$(function(){
    $('#flexibackup_include_file_in_the_backup').on('change', function(){
        if($(this).is(':checked')){
            $('.bk-now-options-wrapper').removeClass('tw-hidden');
        }else{
            $('.bk-now-options-wrapper').addClass('tw-hidden');
        }
    });

    //remove element
    $('.flexibackup-remove-file-action-preview-btn').on('click', function(){
        $(this).closest('.panel_s').remove();
        return false;
    });
    //download log file
    $('#flexibackup-log-file-modal-url').on('click', function(){
        window.location.href = $('#flexi_log_url').val();
        return false;
    });
    //view log file
    $('.flexibackup-view-log-file').on('click', function(){
        const id = $(this).data('id');
        const url = $('#base_url').val();
        const data = {
            action: 'view_log_file',
            id: id,
        }
        //make get request
        $.get(url, data, function(response) {
            if(response.success){
                //show modal
                $('#flexibackup-log-file-modal').modal('show');
                $('#flexibackup-log-file-modal .modal-body').html(response.html);
            }
        });
        return false;
    });
    //restore backup
    $('.flexibackup-restore-backup').on('click', function(){
        const id = $(this).data('id');
        const url = $('#base_url').val();
        const data = {
            action: 'restore_backup',
            id: id,
        }
        //make get request
        $.get(url, data, function(response) {
            if(response.success){
                //show modal
                $('#flexibackup-restore-backup-modal').modal('show');
                $('#flexibackup-restore-backup-modal').html(response.html);
            }
        });
        return false;
    });


    //make ajax request passing type and id
    $('.flexibackup-show-action-btn').on('click', function(){
       //make ajax request
        const result_wrapper = $('#file-actions-wrapper');
        result_wrapper.html('<div class="panel_s loader-height tw-h-[63px]">\n' +
            '                        <div class="">\n' +
            '                            <div class="dt-loader"></div>\n' +
            '                        </div>\n' +
            '                    </div>');
         const id = $(this).data('id');
         const type = $(this).data('type');
         const url = $('#base_url').val();
         const data = {
            action: 'file_action_view',
            id: id,
            type: type,
         };
         //make get request
        $.get(url, data, function(response) {
            if(response.success){
                result_wrapper.html(response.html);
                if(response.files.length > 0){
                    const jsonData = response.files;
                    // Sort the JSON data to list folders before files
                    sortFoldersFirst({ children: jsonData });
                    $('#file-tree-'+id+'-'+type).jstree({
                        'core': {
                            'data': jsonData
                        },
                        'plugins': ['types'],
                        'types': {
                            'default': {
                                'icon': 'jstree-file' // Default icon for files
                            },
                            'folder': {
                                'icon': 'jstree-folder' // Icon for folders
                            }
                        }
                    });
                }
            }
        });
    });
})