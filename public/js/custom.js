"use strict";

$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    },
    dataType: 'json',
    beforeSend: function () {},
    complete: function () {
        LetterAvatar.transform();
    },
    error: function (data) {
        var data = data.responseJSON;
        if (data.message) {
            toastrr(globalAjaxErrorLabel, data.message, 'error');
        } else {
            toastrr(globalAjaxErrorLabel, globalAjaxErrorMessage, 'error');
        }
    }
});


$(document).ready(function () {

    $('.custom-datatable').DataTable({
        "aaSorting": [],
        "columnDefs": [ {
            "orderable": false,  // set orderable for selected columns
            "targets": [0] // column or columns numbers
        }],
    });

    $("[data-checkboxes]").each(function() {
      var me = $(this),
        group = me.data('checkboxes'),
        role = me.data('checkbox-role');

      me.change(function() {
        var all = $('[data-checkboxes="' + group + '"]:not([data-checkbox-role="dad"])'),
          checked = $('[data-checkboxes="' + group + '"]:not([data-checkbox-role="dad"]):checked'),
          dad = $('[data-checkboxes="' + group + '"][data-checkbox-role="dad"]'),
          total = all.length,
          checked_length = checked.length;

        if(role == 'dad') {
          if(me.is(':checked')) {
            all.prop('checked', true);
          }else{
            all.prop('checked', false);
          }
        }else{
          if(checked_length >= total) {
            dad.prop('checked', true);
          }else{
            dad.prop('checked', false);
          }
        }
      });
    });

    var checkedArray = [];

    $(document).on('click', '.btn-delete-check', function (e) {

        var index = checkedArray.indexOf($(this).val());
        if(index == -1) {
            checkedArray.push($(this).val());
        } else {
            checkedArray.splice(index,1);
        }
    });

    $(document).on('click', '.btn-delete-all', function (e) {

        if (checkedArray.length <= 0) {
            alert('Please select the checkbox.');
            return false;
        } else if(!confirm('Are you sure you want to delete?')) {
            return false;
        }

        $.ajax({
            url: $(this).attr('data-url'),
            async: false,
            method: 'DELETE',
            dataType: 'json',
            data: {
                selected : checkedArray,
            },
            success: function (data) {

                $.each(data, function (key, value) {
                     $('#checkbox_checked_id' + value)
                        .children('td, th')
                        .animate({ padding: 0 })
                        .wrapInner('<div/>')
                        .children()
                        .slideUp(function() { $(this).closest('tr').remove(); });
                });
                setTimeout(function () {
                    window.location.reload();
                },2000);
            }
        });

        checkedArray = [];
    });
});


function toastrr(title, message, status) {
    toastr[status](message, title);
}

function readURL(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
            $('.profile-image').attr('src', e.target.result);
            $('.profile-image').closest('div').find('button').removeClass('d-none');
        }
        reader.readAsDataURL(input.files[0]);
    }
}

$(document).on('change', 'input[type="file"]', function (e) {
    var _URL = window.URL || window.webkitURL;
    var file, img;
    readURL(this);
    $('label[for="' + this.id + '"]').contents().first()[0].textContent = e.target.files[0].name;
});

$(document).on('click', 'a[data-ajax-popup="true"], button[data-ajax-popup="true"]', function (e) {
    e.preventDefault();

    var data = {};
    var title = $(this).data('title');
    var size = (($(this).data('size') == '') && (typeof $(this).data('size') === "undefined")) ? 'md' : $(this).data('size');
    var url = $(this).data('url');
    var align = $(this).data('align');

    $("#commonModal .modal-title").html(title);
    $("#commonModal .modal-dialog").addClass('modal-' + size + ' modal-dialog-' + align);

    $.ajax({
        url: url,
        data: data,
        dataType: 'html',
        success: function (data) {
            $('#commonModal .modal-body').html(data);
            $('#commonModal').modal();
        }
    });
});
