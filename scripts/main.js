(function ($) {
    $.fn.HideLabel = function () {
        this.each(function () {
            var Label = $(this).find('.placeholder'),
                Inp = $(this).find('textarea, input');
            if ((Inp.val() != '')) {
                Label.hide();
            }
            Inp.bind({
                focusin: function () {
                    Label.hide();
                },
                focusout: function () {
                    if ((Inp.val() == '')) {
                        Label.show();
                    } else {
                        Label.hide();
                    }
                }
            });
        });
    };
})(jQuery);

function changeAmountInput() {
    /*plus/minus*/
    $('.js_minus').on('click', function () {
        var $input = $(this).parent().find('input');
        var count = parseInt($input.val()) - 1;
        var _this = $(this);
        $.ajax({
            type: "POST",
            url: window.location.href,
            data: {
                'key'     : $input.parents('.mm-cart-item').index(),
                'amount'  : count,
                'action'  : 'cart_amount'
            },
            cache: false,
            success: function(html){
                data = jQuery.parseJSON(html);
                $input.val(data.amount);
                $input.change();
                rebuildCart();
                $('.block-cart').html(data.headCart);
                $('.bxslider_order').html(data.orderCart);
            }
        });
        return false;
    });

    $('.js_plus').on('click', function () {
        var $input = $(this).parent().find('input');
        var count = parseInt($input.val()) + 1;
        var _this = $(this);
        $.ajax({
            type: "POST",
            url: window.location.href,
            data: {
                'key'     : $input.parents('.mm-cart-item').index(),
                'amount'  : count,
                'action'  : 'cart_amount'
            },
            cache: false,
            success: function(html){
                data = jQuery.parseJSON(html);
                $input.val(data.amount);
                $input.change();
                rebuildCart();
                $('.block-cart').html(data.headCart);
                $('.bxslider_order').html(data.orderCart);
            }
        });
        return false;
    });
}

$(document).ready(function() {
    $('.js_wrap_input, .js_wrap_textarea').HideLabel();
    $(".js_wrap_input, .js_wrap_textarea").on("click", function(){
        $(this).find('.placeholder').hide();
        $(this).find('input,textarea').focus();
    });

    slider = $('.bxslider').bxSlider({
        auto:true,
        speed:1000,
        controls: false,
        autoControlsCombine:true
    });
    $('.slider .bx-pager a').on('click', function() {
        window.setTimeout(slider.startAuto, 1000);
    });

    
    

    $('.bxcarousel').bxSlider({
        slideWidth: 233,
        minSlides: 4,
        maxSlides: 4,
        slideMargin: 16,
        moveSlides: 1,
        pager: false
    }); 

    $('.bxslider_order').bxSlider({
        pagerCustom: '.bx-pager'
    });

    /*select*/
    $('.js_select').selectbox();
    
    /*datepicker*/
    $.datepicker.regional['ru'] = { 
        closeText: 'Закрыть', 
        prevText: '&#x3c;Пред', 
        nextText: 'След&#x3e;', 
        currentText: 'Сегодня', 
        monthNames: ['Январь','Февраль','Март','Апрель','Май','Июнь', 
        'Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'], 
        monthNamesShort: ['Янв','Фев','Мар','Апр','Май','Июн', 
        'Июл','Авг','Сен','Окт','Ноя','Дек'], 
        dayNames: ['воскресенье','понедельник','вторник','среда','четверг','пятница','суббота'], 
        dayNamesShort: ['вск','пнд','втр','срд','чтв','птн','сбт'], 
        dayNamesMin: ['Вс','Пн','Вт','Ср','Чт','Пт','Сб'], 
        firstDay: 1, 
        isRTL: false 
    }; 
    $.datepicker.setDefaults($.datepicker.regional['ru']);

    $( "#date_bith" ).datepicker({
        defaultDate: "+1w",
        numberOfMonths: 1,
    });

    /*галлерея в товаре*/
    $('#carousel').carouFredSel({
        responsive: true,
        circular: false,
        auto: false,
        items: {
            visible: 1,
            width: 314,
        },
        scroll: {
            fx: 'directscroll'
        }
    });

    $('#thumbs').carouFredSel({
        responsive: true,
        circular: false,
        infinite: false,
        auto: false,
        prev: '#prev',
        next: '#next',
        items: {
            visible: 3,
            width: 97,
        }
    });

    $('#thumbs a').click(function(e) {
		//console.log(this.href.split('#').pop(), $(this).index(), this.href.split('#')[1] )
        $('#carousel').trigger('slideTo', '#'+ this.href.split('#').pop()); //
		// $('#carousel').trigger('slideTo', [ '#carousel a#'+ this.href.split('#').pop() ]);
		$('#carousel').trigger('slideTo', $(this).index());
        $('#thumbs a').removeClass('selected');
        $(this).addClass('selected');
		//window.location.hash = this.href.split('#').pop();
		//e.preventDefault();
		//e.stopPropagation();
		//var scrollmem = $(window).scrollTop();
		//console.log(scrollmem)
		//$('html,body').scrollTop(scrollmem);
        return false;
		
    });

    $('.zoom').zoom();
    $('.fancy').fancybox({
        padding:0
    });
    $('.fancy_video').fancybox({
        padding:0,
        type: 'iframe'
    });

    /*галерея в городе*/
    $('#carousel_sity').carouFredSel({
        responsive: true,
        circular: false,
        auto: false,
        items: {
            visible: 1,
            width: 424,
            // height: '65%'
        },
        scroll: {
            fx: 'directscroll'
        }
    });

    $('#thumbs_sity').carouFredSel({
        responsive: true,
        circular: false,
        infinite: false,
        auto: false,
        prev: '#prev',
        next: '#next',
        items: {
            visible: 3,
            width: 111,
            // height: '44%'
        }
    });

    $('#thumbs_sity a').click(function() {
        $('#carousel_sity').trigger('slideTo', '#' + this.href.split('#').pop() );
        $('#thumbs_sity a').removeClass('selected');
        $(this).addClass('selected');
        return false;
    });

    /*tabs tovars*/
    $('.js_tabs').each(function(){
        $(this).find(".tab_content").hide();
        if(location.hash.length){
            var hash = location.hash;
            $(hash).fadeIn();
            $(this).find('a[href='+hash+']').parent('li').addClass("active").show();
        } else {
            $(this).find(".js_tab_ul li:first").addClass("active").show();
            $(this).find(".tab_content:first").show();
        }
        
    });
    $(".js_tabs .js_tab_ul li a").on("click", function() {
        location.hash = $(this).attr("href");
        var scrollmem = $(window).scrollTop();
        if ($(this).parents("li").hasClass("active")) return false;
        $(this).parents('.js_tab_ul').find("li").removeClass("active");
        $(this).parent().addClass("active"); 
        $(this).parents('.js_tabs').find(".tab_content").hide(); 
        $($(this).attr("href")).fadeIn();
        $('html,body').scrollTop(scrollmem);
        return false;
    });

    //script for popups
    $(document).on("click", "a.js_popup", function(){
        $('html, body').animate({
        scrollTop: 0
        }, 700);
        $('.popup').fadeOut(100);
        $('#overlay').remove('#overlay');
        url = $('div'+$(this).attr("href"));
        $(url).fadeIn(500);
        var popMargTop  = Math.round(($(url).height()+24)/ 2);
        $(url).css({ 
          'margin-top' : -popMargTop
        });
        $("body").append("<div id='overlay'></div>");
        $('#overlay').show().css({'filter' : 'alpha(opacity=60)'});
        return false;               
    }); 
    $('a.close, #overlay, .ok').live("click", function () {
        $('.popup').fadeOut(100);
        $('#overlay').remove('#overlay');
        return false;
    });  

    /*validation*/
    $('.js_validate button').on("click", function(){
        return validate($(this).parents(".js_validate"));
    });  

    /*Плавный переход по якорям*/
    $('a[href^="#"].js_anchor').click(function(){
        if($(this).hasClass('all_reviews')) {
            $('.wrap_tabs .tab_content').hide();
        }
        var target = $($(this).attr('href')).fadeIn();
        var target_a = $(this).attr('href');
        
        $('.js_tab_ul').find('li').removeClass('active');
        var target_li = $('.js_tab_ul').find('a[href='+target_a+']');
        
        $(target_li).parent('li').addClass('active');
        $('html, body').animate({scrollTop: $(target).offset().top}, 600);
        location.hash = target_a;
        return false; 
    }); 

    /*Доставка*/
    $('.js_form_delivery input[name=delivery_method]').on('click', function(){
        par_lab = $(this).parent('label').next('.hide_row');
        $('.js_form_delivery').find('.hide_row').slideUp();
        par_lab.slideDown();
    });

    /*personal cabinet*/
    $('.js_edit').on('click', function(){
        $(this).parents('form').find('.disabled').removeClass('disabled');
        $(this).parents('form').find('input:disabled').removeAttr('disabled');
    });

    changeAmountInput();
    
    /*запись на примерку*/
    $('.js_ico_recording').on('click', function(){
        
        if($('.js_block_recording').hasClass('open'))
            $('.js_block_recording').animate({
                left: -226
            }, 800, function(){
                $(this).removeClass('open');
                $('.overlay').hide();
            });
        else
            $('.js_block_recording').animate({
                left: 0,
                opacity: 1
            }, 800, function(){
                $(this).addClass('open');
                $('.overlay').show();
            });
    });

    /*выбор города*/
    $(document)
        .on('click', '.js_no', function(){
            $(this).parent().prev('.choose_city').show();
        })
        .on('click', '.js_yes', function(){
            $('.popup_ask').hide();
            $.ajax({
                type: "POST",
                url: document.location.href,
                data: {
                    'action' : 'set_user_city_confirm'
                },
                cache: false,
                success: function(html){
                   
                }
            });
        })
});

function validate(form){
    var error_class = "error";
    var norma_class = "pass";
    var item        = form.find("[required]");
    var e           = 0;
    var reg         = undefined;
    function mark (object, expression) {
        if (expression) {
            object.parent('div').addClass(error_class).find('.error_text').css('display','block');
            e++;
        } else
            object.parent('div').addClass(norma_class).removeClass(error_class).find('.error_text').css('display','none');
    }
    item.each(function(){
        switch($(this).attr("data-validate")) {
            case undefined:
                mark ($(this), $.trim($(this).val()).length == 0);
            break;
            case "email":
                reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
               mark ($(this), !reg.test($.trim($(this).val())));
            break;
            case "phone":
                reg = /[0-9 -()+]{5}$/;
                mark ($(this), !reg.test($.trim($(this).val())));
            break;
            case "pass":
                reg = /^[a-zA-Z0-9_-]+$/;
                mark ($(this), !reg.test($.trim($(this).val())));
            break;
            default:
                reg = new RegExp($(this).attr("data-validate"), "g");
                mark ($(this), !reg.test($.trim($(this).val())));
            break
        }
    })
    if (e == 0) {
        if (form.hasClass('js_avail_tovar')) {
            $('.popup').fadeOut(100);
            $('#overlay').remove('#overlay');
            form.parents('body').find('#request').fadeIn();
            $("body").append("<div id='overlay'></div>");
            $('#overlay').show().css({'filter' : 'alpha(opacity=60)'});
            return false; 
        }
        return true;
    }
    else {
        form.find("."+error_class+" input:first").focus();
        return false;
    }
}  