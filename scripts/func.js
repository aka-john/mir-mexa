function buildFieldArray(_this) {
    var check_data = {};
    var count = 0;
    jQuery.each(_this.parents("form").find("input,select,textarea"), function(i, val) {
        if ($(this).attr("check") != null) {
            check_data[i] = {};
            check_data[i]["name"]  = $(this).attr("name");
            check_data[i]["value"] = $(this).val();
            check_data[i]["check"] = $(this).attr("check");
        } else {
            check_data[i] = {};
            check_data[i]["name"]  = $(this).attr("name");
            check_data[i]["value"] = $(this).val();
            check_data[i]["check"] = "null";
        }
        count = i;
    });
    check_data['email']    = _this.parents("form").find('input[name=email]').val();
    check_data['password'] = _this.parents("form").find('input[name=password]').val();
    check_data['recaptcha_challenge_field'] = _this.parents("form").find('input[name=recaptcha_challenge_field]').val();
    check_data['recaptcha_response_field']  = _this.parents("form").find('input[name=recaptcha_response_field]').val();
    return check_data;
};

function buildErrorList(data, _this) {
    if (data.alert != undefined) {
        _this.parent('form').find(".error_text").remove();
        _this.parents("form").prepend("<label class=\"error_text\" style=\"display:block;\">"+data.alert+"</label>");
    }
    
    if (data.error == undefined) {
        return true;
    }
    
    $.each(_this.parents("form").find("input,select,textarea"), function(i, val) {
        if (data.error[i] != null && data.error[i]["name"] != 'vericode') {
            if ($(this).attr("name") == data.error[i]["name"]) {
                $(this).parent('div').find(".error_text").remove();
                $(this).parent('div').removeClass("pass");
                $(this).parent('div').addClass("error");
                $(this).parent('div').append("<label class=\"error_text\" style=\"display:block;\">"+data.error[i]["message"]+"</label>");
            }
        } else {
            if ($(this).attr("name") != 'recaptcha_response_field' && $(this).attr("nocheck") != "true") {
                $(this).parent('.js_wrap_input').removeClass("error");
                $(this).parent('.js_wrap_input').addClass("pass");
                $(this).parent('.js_wrap_input').find(".error_text").remove();
            }
        }
    });
    
    if (data.error["vericode"] != null) {
        Recaptcha.reload();
        _this.parents("form").find('.captcha').find('.error_text').remove();
        _this.parents("form").find('.captcha').append("<label class=\"error_text\" style=\"display:block;\">"+data.error["vericode"]["message"]+"</label>");
    }

    return true;
};

function clearFormErrors(_this) {
    $.each(_this.parents("form").find("input[type='text'],input[type='password'],select,textarea"), function(i, val) {
        $(this).val('');
        $(this).parent('div').removeClass("error");
        $(this).parent('div').removeClass("pass");
        $(this).parent('div').find(".error_text").remove();
    });
}

function getFilters() {
    var filter_block = $('#filterBlock'),
        sort_block   = $('#sortBlock'),
        price_block  = $('#slider_price'),
        count = 0,
        filter_array = [];

    filter_block.find('input[type=checkbox]').each(function(i, val){
        if ($(this).attr("checked") == 'checked') {
            filter_array[count] = $(this).attr('id'); 
            count++;
        } 
    });

    sort_block.find('select').each(function(i, val){
        filter_array[count] = $(this).attr('name')+"="+$(this).val(); 
        count++;
    });

    price_block.find('input[type=text]').each(function(i, val){
        filter_array[count] = $(this).attr('name')+"="+$(this).val(); 
        count++;
    });

    if (sort_block.find('input[name=search]').val() != undefined) {
        filter_array[count] = 'search='+sort_block.find('input[name=search]').val(); 
    }
    
    return filter_array;
}

function buildFilterUrl() {
    var filter_block = $('#filterBlock'),
        sort_block   = $('#sortBlock'),
        filter_line  = getFilters().join('&');
    sort_block.attr("action", sort_block.attr("page")+(filter_line != "" ? "?" : "")+filter_line);
    filter_block.attr("action", sort_block.attr("page")+(filter_line != "" ? "?" : "")+filter_line);
}

function rebuildCart() {
    var cart_block = $('.mm-cart-block'),
        cart_item  = $('.mm-cart-item'),
        cart_total_summ  = $('.mm-total-summ'),
        cart_total_amount = $('.mm-total-amount'),
        cart_empty = $('.mm-cart-empty'),
        total_summ = 0,
        total_amount = 0,
        cart_empty_alert = '';

    cart_block.find('.mm-cart-item').each(function() {
        amount = $(this).find('.mm-product-amount').val();
        amount = (amount < 1 || amount == '' ? 1 : amount);
        price  = $(this).find('.mm-product-summ').attr("data");
        summ   = parseInt(amount) * parseInt(price);
        $(this).find('.mm-product-summ').text(summ);
        total_summ = total_summ + summ;
        total_amount = total_amount + parseInt(amount);
    });
    
    cart_total_summ.text((total_summ == 0 ? '' : total_summ));
    cart_total_amount.text((total_amount == 0 ? '' : total_amount));
    cart_empty.text((total_summ == 0 ? '' : ''));

    if (total_summ == 0) {
        $('.zakaz_table').parent('div').prepend(cart_empty_alert);
        $('.zakaz_table').parent('div').find('.total_line').hide(); 
        $('.zakaz_table').hide();
    } else {
        $('.zakaz_table').parent('div').find('.message').remove();
        $('.zakaz_table').parent('div').find('.btn-cart').show(); 
        $('.zakaz_table').show(); 
    }
}

$(document).ready(function() {
    $('.js_submit_btn').on("click",function(e) {
        _this = $(this);
		
        $.ajax({
            type: "POST",
            url: window.location.href,
            data: {
                'fields': buildFieldArray(_this),
                'data': _this.parents("form").serialize(),
                'action': $(this).attr("data")
            },
            cache: false,
            success: function(html){
                data = jQuery.parseJSON(html);
                if (data.error_status != false) {
                    switch (data.key) {
                        case 'form_auth_order':
                        case 'form_auth':
                            document.location = data.value;
                            break;
                        case 'form_reg':
                            _this.parents('form').hide();
                            _this.parents('form').before(data.value);
                            setTimeout(function() {
                                _this.parents('.popup').find('.close').click();
                            }, 3000);
                            break;
                        case 'form_forgot':
                            _this.parents('form').hide();
                            _this.parents('.form').before(data.value);
                            setTimeout(function() {
                                _this.parents('.popup').find('.close').click();
                            }, 3000);
                            break;
                        case 'form_callback':
                            _this.parents('form').hide();
                            _this.parents('form').before(data.value);
                            setTimeout(function() {
                                _this.parents('.popup').find('.close').click();
                            }, 3000);
                            break;
                        case 'form_subscribe':
                            _this.parents('form').hide();
                            _this.parents('.form').find('.message').remove();
                            _this.parents('.form').prepend(data.value);
                            _this.parents('form').find('.error').removeClass("error");
                            _this.parents('form').find('.error_text').remove();
                            break;
                        case 'profile_save':
                            _this.parents('.tab_container').find('.message').remove();
                            $('#alert').find('.message').remove();
                            $('#alert').append(data.value);
                            _this.parents('form').find('.error').removeClass("error");
                            _this.parents('form').find('.error_text').remove();
                            $('#alert').fadeIn();
                            setTimeout(function() {
                                $('#alert').fadeOut();
                            }, 3000);
                            break;
                        case 'password_change':
                            _this.parents('.tab_container').find('.message').remove();
                            _this.parents('.tab_container').find('input[name=old_password]').val('');
                            _this.parents('.tab_container').find('input[name=password]').val('');
                            $('#alert').find('.message').remove();
                            $('#alert').append(data.value);
                            _this.parents('form').find('.error').removeClass("error");
                            _this.parents('form').find('.error_text').remove();
                            $('#alert').fadeIn();
                            setTimeout(function() {
                                $('#alert').fadeOut();
                            }, 3000);
                            break;
                        case 'profile_address_save':
                            _this.parents('.tab_container').find('.message').remove();
                            $('#alert').find('.message').remove();
                            $('#alert').append(data.value);
                            _this.parents('form').find('.error').removeClass("error");
                            _this.parents('form').find('.error_text').remove();
                            $('#alert').fadeIn();
                            setTimeout(function() {
                                $('#alert').fadeOut();
                            }, 3000);
                            break;
                        case 'form_review':
                            _this.parents('.wrap_form_review').find('form').hide();
                            _this.parents('.wrap_form_review').find('.message').remove();
                            _this.parents('.wrap_form_review').prepend(data.value);
                            _this.parents('form').find('.error').removeClass("error");
                            _this.parents('form').find('.error_text').remove();
                            break;
                        case 'form_feedback':
                            _this.parents('.form').find('form').hide();
                            _this.parents('.form').find('.message').remove();
                            _this.parents('.form').prepend(data.value);
                            _this.parents('form').find('.error').removeClass("error");
                            _this.parents('form').find('.error_text').remove();
                            break;
                        case 'form_report':
                            _this.parents('form').hide();
                            _this.parents('form').before(data.value);
                            _this.parents('.form').find('.message-introtext').remove();
                            setTimeout(function() {
                                _this.parents('.popup').find('.close').click();
                            }, 3000);
                            break;
                        case 'form_fitting':
                            _this.parents('form').hide();
                            _this.parents('form').before(data.value);
                            setTimeout(function() {
                                _this.parents('form').show();
                                $.each(_this.parents("form").find("input,select,textarea"), function(i, val) {
                                    $(this).val('');
                                    $(this).parent('div').removeClass("error");
                                    $(this).parent('div').removeClass("pass");
                                    $(this).parent('div').find(".placeholder").show();
                                    $(this).parent('div').find(".error_text").remove();
                                });
                                $('.recording_fitting').find('.message').remove();
                                $('.js_ico_recording').click();
                            }, 3000);
                            break;
                        case 'form_order':
                            switch (data.action) {
                                case 'liqpay':
                                    $('body').append(data.html);
                                    $('#liqpay').submit();
                                    break;
                                default:
                                    document.location = data.value;
                                    break;  
                            }
                            break;
                    }

                } else {
                    message = buildErrorList(data, _this);
                    /*$('html, body').animate({
                      scrollTop: 0
                    }, 1200);*/
                }
            }
        });
        return false;
    });
    
    var counter = $('#addressForm .address_container').find('.form_collection').length;
    $(document).on("click", ".js_addProfileAddress", function(){
        _this = $(this);
        $.ajax({
            type: "POST",
            url: document.location.href,
            data: {
                'counter': counter,
                'action' : 'add_profile_address'
            },
            cache: false,
            success: function(html){
                $('#addressForm .address_container').before(html);
				$('.js_select').selectbox();
                counter = counter + 1;
                _this.parents('.form_collection').find('select').selectbox("detach");
                _this.parents('.form_collection').find('select').selectbox("attach");
            }
        });
        return false;
    });
    
    $(document).on("change", ".js_changeProductSize", function(){
        var _this = $(this),
            size = _this.val();

        _this.find('option').attr("selected", false)
        _this.find('option[value='+size+']').attr("selected", true);
        _this.selectbox("detach");
        _this.selectbox("attach");

        $.ajax({
            type: "POST",
            url: document.location.href,
            data: {
                'size': size,
                'action' : 'check_size_amount'
            },
            cache: false,
            success: function(html){
                data = jQuery.parseJSON(html);
                data.price = data.price == undefined ? 0 : data.price;

                $('input[name=size_id]').val(size);

                if (data.amount == 0 || data.price < 1) {
                    $('.wrap_info_tovar').find('.js_addToCart').hide();
                    $('.wrap_info_tovar').find('.old_price').hide();
                    $('.wrap_info_tovar').find('.mm-product-price').hide();
                    $('.wrap_info_tovar').find('.avail_n').show();
                } else {
                    $('.wrap_info_tovar').find('.js_addToCart').show();
                    $('.wrap_info_tovar').find('.old_price').show();
                    $('.wrap_info_tovar').find('.mm-product-price').show();
                    $('.wrap_info_tovar').find('.avail_n').hide();
                }

                _this.parents('div').find('.mm-product-price span').text(parseInt(data.price));

                if (data.sale_price == undefined) {
                    _this.parents('div').find('.old_price').hide();
                } else {
                    _this.parents('div').find('.old_price span').text(parseInt(data.sale_price));
                }
               
                $('.js_presence').text(data.presence);
            }
        });
    });
    
    $(document).on("click", ".js_addToCart", function(){
        var _this = $(this);

        $.ajax({
            type: "POST",
            url: document.location.href,
            data: {
                'product_id': _this.attr('data'),
                'size': _this.parents('.wrap_info_tovar').find('select[name=sizes]').val(),
                'action' : 'cart_add'
            },
            cache: false,
            success: function(html){
                data = jQuery.parseJSON(html);
                $('.block-cart').html(data.headCart);
                $('#cart').html(data.popupCart);
                changeAmountInput();
                $('html, body').animate({scrollTop:0},'slow');

                $('#alert').find('.message').remove();
                $('#alert').append(data.message);
                $('#alert').fadeIn();
                setTimeout(function() {
                    $('#alert').fadeOut();
                }, 3000);
            }
        });
        return false;
    });
    
    $(document).on("click", ".js_clearCart", function(){
        _this = $(this);
        $.ajax({
            type: "POST",
            url: document.location.href,
            data: {
                'action' : 'cart_clear'
            },
            cache: false,
            success: function(html){
                data = jQuery.parseJSON(html);
                _this.parent('.total_line').remove();
                rebuildCart();
                if ($('.block-cart').find('.mm-cart-item').length < 1) {
                    $('#cart').find('.total_line').hide();
                    $('#cart').find('.btn_line').hide();
                    $('.table').html(data.cart);
                    setTimeout(function() {
                        $('.close').click();
                    }, 2000);
                } 
                $('.block-cart').html(data.headCart);
                $('.bxslider_order').html(data.orderCart);
            }
        });
        return false;
    });
    
    $(document).on("click", ".js_removeCartProduct", function(){
        _this = $(this);
        $.ajax({
            type: "POST",
            url: document.location.href,
            data: {
                'key'    : _this.parents('.mm-cart-item').index(),
                'action' : 'cart_remove'
            },
            cache: false,
            success: function(html){
                data = jQuery.parseJSON(html);
                _this.parents('.mm-cart-item').remove();
                rebuildCart();
                if ($('#cart').find('.mm-cart-item').length < 1) {
                    $('#cart').find('.total_line').hide();
                    $('#cart').find('.btn_line').hide();
                    $('#cart').find('.table').html(data.cart);
                    setTimeout(function() {
                        $('.close').click();
                    }, 2000);
                } 
                $('.block-cart').html(data.headCart);
                $('.bxslider_order').html(data.orderCart);
            }
        });
        return false;
    });
    
    $(document).on("change", ".js_changeDelivery", function(){
        var _this = $(this),
            total_price = $('.total_sum_order .mm-total-summ').attr('price'),
            delivery_price = _this.attr('price');

        if (parseInt(total_price) < parseInt($('.total_sum_order .mm-delivery-summ').attr('free'))) {
            $('.total_sum_order .mm-total-summ').text(Math.round(parseInt(total_price) + parseInt(delivery_price)));
            $('.total_sum_order .mm-delivery-summ').text(_this.attr('price'));
        }
    });
    
    $(document).on("change", ".js_orderShopCity", function(){
        var _this = $(this);

        $.ajax({
            type: "POST",
            url: document.location.href,
            data: {
                'city': _this.parents('form').find('select[name=fitting_city]').val(),
                'action' : 'order_shops_view'
            },
            cache: false,
            success: function(html){
                _this.parents('form').find('select[name=fitting_shop]').html(html);
                _this.parents('form').find('select[name=fitting_shop]').selectbox("detach");
                _this.parents('form').find('select[name=fitting_shop]').selectbox("attach");
            }
        });
        return false;
    });
    
    $(document).on("change", ".js_orderStockCity", function(){
        var _this = $(this);

        $.ajax({
            type: "POST",
            url: document.location.href,
            data: {
                'city': _this.parents('form').find('select[name=np_stock]').find('option:selected').attr('data'),
                'action' : 'order_stock_view'
            },
            cache: false,
            success: function(html){
                _this.parents('form').find('select[name=np_address]').html(html);
                _this.parents('form').find('select[name=np_address]').selectbox("detach");
                _this.parents('form').find('select[name=np_address]').selectbox("attach");
            }
        });
    });

    $(document).on("change", ".js_changeUserCity", function(){
        var _this = $(this);
        _this.parents('form').submit();
    });

    $(document).on("change", ".js_changeUserCityInOrder", function(){
        var _this = $(this);

        $.ajax({
            type: "POST",
            url: document.location.href,
            data: {
                'city_id': _this.val(),
                'action' : 'change_user_city'
            },
            cache: false,
            success: function(html){
                data = jQuery.parseJSON(html);
                $('#cart').html(data.popupCart);
                $('.block-cart').html(data.headCart);
                $('.bxslider_order').html(data.orderCart);

                delivery_price = $('.total_sum_order .mm-delivery-summ').text();
                $('.total_sum_order .mm-total-summ').text(Math.round(parseInt(data.total_price) + parseInt(delivery_price)));

                if (data.total_price == 0) {
                    $('button[data=form_order]').hide();
                } else {
                    $('button[data=form_order]').show();
                }

                var slider = $('.bxslider_order').bxSlider();
                slider.reloadSlider();
            }
        });
    });
    
    $(document).on("click", ".js_BackToBuy", function(){
        $('#cart').find('.close').click();
        return false;
    });

    $(document).on("click", ".js_changeDeliveryAddress", function(){
        switch($(this).val()) {
            case 'new':
                $('js_delivery_info').show();
                break;
            default:
                $('js_delivery_info').hide();
                break;
        }
    });

    $(document).on("click", ".js_removeProfileAddress", function(){
        $(this).parents('.form_collection').remove();
        return false;
    });
    
    $(".js_filter_select").on("change", function () {
        buildFilterUrl();
        window.location = 'http://'+window.location.hostname+'/'+$('#sortBlock').attr("action");
        return false;
    });
    
    $(".js_filter_checkbox").on("change", function () {
        buildFilterUrl();
        window.location = 'http://'+window.location.hostname+'/'+$('#sortBlock').attr("action");
        return false;
    });

    if (window.location.hash == "#login") {
        $('.js_popup[href=#sign]').click();
    }
    
    if (window.location.hash == "#forgot") {
        $('.js_popup[href=#forgot]').click();
    }

    // $("a:not([href^='"+window.location.origin+"']):not([href^='#'])").attr("rel", "nofollow");
});


$(function(){
	
var homeUrl = 'http://mir-mexa.com/';
if ( document.URL == homeUrl  ){
	if($('.mobile-menu-catalog').css('display') == 'block'){
		$('.nav ul').show(); 
	}
    
}	
	

	
		$('.mobile-menu-catalog').on('click', function(){
							
									$('.nav ul').toggle();
	
						   			});

	
		$('.mobile-menu-small').click(function(){
							
									$('header .header_bottom  .new_header_bottom_center .header_menu').toggle();
									
						   			});
	$('.mobile-menu-filters').click(function(){
							
									$('.wrap_catalog .left .wrap_filters').toggle();
									
						   			});
	
	

	
	
});