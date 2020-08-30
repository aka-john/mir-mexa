$.fn.editable.defaults.mode = 'popup';

function alertMessage(message,status) {
    $("body").prepend("<div class=\"art_alert-popup\"><p class=\"text-danger\">"+message+"</p></div>");
     window.setTimeout(function() {
        $(".art_alert-popup").fadeOut("slow", function() {
             $(".art_alert-popup").remove();
         });
     }, 2000);
};

function generatePassword() {
    var length = 10,
        charset = "abcdefghijklnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789",
        retVal = "";
    for (var i = 0, n = charset.length; i < length; ++i) {
        retVal += charset.charAt(Math.floor(Math.random() * n));
    }
    return retVal;
}

$ = jQuery.noConflict();
String.prototype.translit = (function(){
    var L = {
            "А" : "A","а" : "a","Б" : "B","б" : "b","В" : "V","в" : "v","Г" : "G","г" : "g","Д" : "D","д" : "d",
            "Е" : "E","е" : "e","Ё" : "Yo","ё" : "yo","Ж" : "Zh","ж" : "zh","З" : "Z","з" : "z","И" : "I","и" : "i",
            "Й" : "Y","й" : "y","К" : "K","к" : "k","Л" : "L","л" : "l","М" : "M","м" : "m","Н" : "N","н" : "n",
            "О" : "O","о" : "o","П" : "P","п" : "p","Р" : "R","р" : "r","С" : "S","с" : "s","Т" : "T","т" : "t",
            "У" : "U","у" : "u","Ф" : "F","ф" : "f","Х" : "Kh","х" : "kh","Ц" : "Ts","ц" : "ts","Ч" : "Ch",
            "ч" : "ch","Ш" : "Sh","ш" : "sh","Щ" : "Sch","щ" : "sch","Ъ" : "","ъ" : "","Ы" : "Y","ы" : "y","Ь" : "",
            "ь" : "","Э" : "E","э" : "e","Ю" : "Yu","ю" : "yu","Я" : "Ya","я" : "ya"," " : "-","&" : "",
            "\'" : "","/" : "-","%" : "","," : "","." : "","!" : "","І" : "I","і" : "i","Є" : "E","є" : "e","Ґ" : "G","ґ" : "g",
            "Ї" : "i","ї" : "i","~" : "","`" : "",";" : "",":" : "",")" : "","(" : "","*" : "","@" : "","#" : "",
            "$" : "","^" : "","+" : "","=" : "","?" : "", "_":""
        },
        r = "",
        k;
    for (k in L) r = r + k;
    r = new RegExp("[" + r + "]", "g");
    k = function(a){
        return a in L ? L[a] : "";
    };
    return function(){
        return this.replace(r, k);
    };
})();
String.prototype.strip_tags = (function(){
  return function (){ return this.replace(/<\/?[^>]+>/gi, "");}
})();

$(document).ready(function() {
    $.ajaxSetup({
            type: "POST",
            cache: false,
            error: function(XMLHttpRequest, textStatus, errorThrown){
        console.log('Error : ' + XMLHttpRequest);
        console.log('Error : ' + textStatus);
        console.log('Error : ' + errorThrown);
    }
    }); 

    $('.js_submitForm').on('click', function(){
        $(this).parent('#art_content').find('form').submit();
    }); 

    $('#art_actionbar .js_submitForm').on('click', function(){
        $(this).parents().find('form').submit();
    }); 
    
    $('.tooltip-top').tooltip({'placement':'top'});
    $('.tooltip-left').tooltip({'placement':'left'});
    $('.tooltip-right').tooltip({'placement':'right'});
    $('.tooltip-bottom').tooltip({'placement':'bottom'});
    
    $(".bootstrap_selectpicker").selectpicker();
    
    $(".bootstrap_switch").bootstrapSwitch();
    
    $('.bootstrap_datetimepicker').datetimepicker({
        language: 'ru'
    });
    
    $('.popover-dismiss').popover({
        trigger: 'focus',
        html: 'true'
    });
    
    $('.bootstrap_popover').popover({
        html: 'true'
    });
    
    $(".bootstrap_spin").TouchSpin({
        min: 0,
        max: 9999999
    });
    
    $('.summernote').summernote({
        height: 200,
        minHeight: null,
        maxHeight: null,
        focus: true,
        toolbar: [
            ['style', ['bold', 'italic', 'underline', 'clear']],
            ['font', ['strikethrough']],
            ['fontsize', ['fontsize']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['height', ['height']],
            ['codeview', ['codeview']]
        ]
    });

    $(window).scroll(function(){
        var y =  $(window).scrollTop();
        var top = 100;
        if(y > top){
            $("#art_actionbar").addClass('art_actionbar-scrollable');
            $('#art_actionbar .art_actionbar-scroll-top').fadeIn('200');
        }else{
            $("#art_actionbar").removeClass('art_actionbar-scrollable');
            $('#art_actionbar .art_actionbar-scroll-top').fadeOut('200');
        }
    });
    
    $("#alias_generate").on("keyup", function (){
        value = $.trim($(this).val());
        $("input[name=alias]").val(value.translit().strip_tags());
    });
    
    $('.art_notice-warning-btn').click(function (e) {
        $('.art_notice-warning-block').show('slow');
    });
   
    $('#art_content-tab-navigation a').click(function (e) {
        e.preventDefault()
        $(this).tab('show')
    });

    $(".art_actionbar-scroll-top").click(function(){ 
        $('html, body').animate({scrollTop: "0px"}); 
         return false;
    });
    
    if (window.location.hash != '') {
        $('.nav-tabs').find('a[href='+window.location.hash+']').click();
    }
    
    $("input[type=checkbox]").on("change", function() {
        if ($(this).prop("checked") == true) {
            $(this).val(1);
        } else { 
            $(this).val(0);
        }
    });
    
    $('.bootstrap_switch').on('switchChange.bootstrapSwitch', function(event, state) {
        if ($(this).prop("checked") == true) {
            $(this).val(1);
        } else { 
            $(this).val(0);
        }
    });

    $(".js_art-genpassword").click(function(){ 
        var new_password = generatePassword(10);
        $(this).parent().find('input[name=password]').val(new_password);
    });
                
    $(".js_hide-left-toolbar").click(function(){ 
        if ($('#art_left-toolbar').hasClass('art_hide-toolbar')) {
            $('#art_left-toolbar').removeClass('art_hide-toolbar');
            $('#art_left-toolbar').animate({
                width: "135px"
            }, 200);
            $('#art_left-actions').animate({
                width: "200px"
            }, 200);
            window.setTimeout(function() {
                $('#art_left-toolbar .toolbar-element .art_big-glyphicon-label').fadeIn("slow");
            }, 200);
            $(this).find('.glyphicon').attr('class', 'glyphicon glyphicon-chevron-left');
        } else {
            $('#art_left-toolbar').addClass('art_hide-toolbar');
            $('#art_left-toolbar .toolbar-element .art_big-glyphicon-label').hide();
            $('#art_left-toolbar').animate({
                width: "30px"
            }, 200);
            $('#art_left-actions').animate({
                width: "0px"
            }, 200);
            $(this).find('.glyphicon').attr('class', 'glyphicon glyphicon-chevron-right');
        }
    });
    
    $(".js_data-grid-check-all-checkbox").click(function(){ 
        var elements = $(this).parents('form').find('.table tbody tr').find('td:first input[type=checkbox]'),
            data = [];
        $.each(elements, function(key, value) {
            if($(this).is(':checked')){
                $(this).prop('checked', false);
            }else{
                $(this).prop('checked', true);
                data[key] = $(this).parents('tr').attr('id');
            }
        });
        $(this).parents('form').find('input[name=checked_list]').val(data.join(','));
    });
    
    $(".js_grid-sort-table").click(function(){ 
        var _this = $(this),
            sort_dir = _this.parents('th').attr('name'),
            sort_by = _this.attr('data');
        _this.parents('form').find('input[name=grid_sort_dir]').val(sort_dir);
        _this.parents('form').find('input[name=grid_sort_by]').val(sort_by);
        _this.parents('form').submit();
    });
    
    $(".js_art_add-collection").click(function(){ 
        counter = $(this).parent('div').find('.art_collection-group').length;
        data = $(this).attr('data-content');
        data = data.replace(/__counter__/g, 'new_'+(counter+1));
        $(this).parent('div').append(data);
        $(this).parent('div').find('input[name=counter_id]').val(data);
        $(this).parent('div').find('.bootstrap_selectpicker').selectpicker();
        $(this).parent('div').find('.bootstrap_switch').bootstrapSwitch();
        $('.bootstrap_switch').on('switchChange.bootstrapSwitch', function(event, state) {
            if ($(this).prop("checked") == true) {
                $(this).val(1);
            } else { 
                $(this).val(0);
            }
        });
    });
    
    $(document).on("click", ".js_art_remove-collection", function(){
        _this = $(this);
        $.ajax({
            url : _this.attr('href'),
            type : "POST",
            data : {
                "id" :_this.attr('data')
            },
            cache : false,
            success : function(html) {
                console.log(html);
                _this.parents('.art_collection-group').remove();
            }
        });
        return false;
    });
    
    $(document).on("click", ".js_art_remove-image", function(){
        _this = $(this);
        $.ajax({
            url : _this.attr('href'),
            type : "POST",
            data : {
                "id" :_this.attr('data')
            },
            cache : false,
            success : function(html) {
                console.log(html);
                _this.parent('div').find('.js_file-name').remove();
            }
        });
        return false;
    });
    
    $(document).on("click", ".ajax_art-grid-dropdown-action", function(){
        _this = $(this);
        $.ajax({
            url : _this.attr('href'),
            type : "POST",
            data : {
                "ids" :_this.parents('form').find('.js_grid-checkbox-check:checked').map(function () {return $(this).parents('tr').attr('id');}).get().join(",")
            },
            cache : false,
            success : function(html) {
                console.log(html);
                _this.parents('form').submit();
            }
        });
        return false;
    });
    
    $(".js_grid-change-display").change(function(){ 
        var _this = $(this);
        _this.parents('form').submit();
    });

    if ($('.js_grid-count-th').html() != undefined) {
        $('.js_grid-count-th').attr('colspan', $('.js_grid-count-th').parents('table').find('th').length);
    }
    
    if ($('.art_flash-alert').html() != undefined) {
        window.setTimeout(function() {
          /*  $(".art_flash-alert").fadeOut("slow", function() {
                $(".art_flash_alert").remove();
            });*/
        }, 4000);
    }
    
});
