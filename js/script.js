// JavaScript Document


$(document).ready(function () {
    main_button("home");
    window.parent.postMessage('1', '*');
    window.top.postMessage('1', '*');
    window.frames.postMessage('1', '*');
    console.log(user_location);
    mymap = L.map('map').setView([35.699695, 51.338349], 11);
    L.tileLayer(
        'https://api.tiles.mapbox.com/v4/{id}/{z}/{x}/{y}.png?access_token=pk.eyJ1Ijoic2hhamFyaWFudGFoYSIsImEiOiJjamo5M2lrbWoxN25jM2ttbTBjMjBtbTFkIn0.g60dHXBYeKkRhhrvTOSuxg', {
            maxZoom: 18,
            id: 'mapbox.streets',
            accessToken: 'pk.eyJ1Ijoic2hhamFyaWFudGFoYSIsImEiOiJjamo5M2lrbWoxN25jM2ttbTBjMjBtbTFkIn0.g60dHXBYeKkRhhrvTOSuxg'
        }).addTo(mymap);

    L.easyButton('<i onclick="user_get_location()" class="fa fa-crosshairs"></i>', function () {
    }).addTo(mymap);
});

// define variable
let user_id = 3590;
let shop_id = 110;
let titles_home = [];
let titles_search = [];
let titles_locations = [];
let titles_bascket = [];
let titles_profile = [];
let titles_wallet = [];
let titles_history = [];
let titles_messages = [];
let titles_doctor = [];
let loading = 0;
let home = 0;
let history_order = 0;
let search = 0;
let locations = 0;
let bascket = 0;
let data = {};
let order_list = [], active_article, is_open, scrollRight, scrollLeft;
let user = null;
let is_adding = 0;
let user_location;
let mymap;
let marker_location;
let pharms_marker = [];
let order;

const main_button = target => {
    if (!loading) {
        $("#back").css('color', '#1A7395');
        loading = 1;
        $(".main_page").fadeOut().promise().done(function () {
            $("#" + target).fadeIn().promise().done(function () {
                loading = 0;
                $("#" + target).css("display", "-webkit-inline-box");
                active_article = target;
                toggle_slide_button_in(); //optional
            });
        });
        $(".main_buttons").removeClass("footer_active");
        $("li[target=" + target + "]").addClass("footer_active");
        switch (target) {
            case "home":
                //$("#home_page_1").fadeIn();
                if (titles_home.length === 0) titles_home.push("دارسی");
                if (titles_home.length <= 1) $("#back").css('color', '#8080808c');
                $("#title_main").text(titles_home[titles_home.length - 1]);
                break;
            case "search":
                if (titles_search.length === 0) {
                    titles_search.push("جستجو");
                }
                if (titles_search.length <= 1) $("#back").css('color', '#8080808c');
                $("#title_main").text(titles_search[titles_search.length - 1]);
                break;
            case "bascket":
                if (titles_bascket.length === 0) {
                    titles_bascket.push("سبد خرید");
                }
                if (titles_bascket.length <= 1) $("#back").css('color', '#8080808c');
                $("#title_main").text(titles_bascket[titles_bascket.length - 1]);
                bascket_get(user_id, shop_id);
                break;
            case "locations":
                if (titles_locations.length === 0) {
                    titles_locations.push("داروخانه ها");
                }
                if (titles_locations.length <= 1) $("#back").css('color', '#8080808c');
                $("#title_main").text(titles_locations[titles_locations.length - 1]);
                location_map();
                break;
            case "profile":
                if (titles_profile.length === 0) {
                    titles_profile.push("پروفایل");
                }
                if (titles_profile.length <= 1) $("#back").css('color', '#8080808c');
                $("#title_main").text(titles_profile[titles_profile.length - 1]);
                if (!user) user_get("#profile_page_1"); else profile_fill(user, "#profile_page_1");
                break;
            case "wallet":
                if (titles_wallet.length === 0) {
                    titles_wallet.push("کیف پول");
                }
                if (titles_wallet.length <= 1) $("#back").css('color', '#8080808c');
                $("#title_main").text(titles_wallet[titles_wallet.length - 1]);
                wallet_get();
                break;
            case "history_order":
                if (titles_history.length === 0) {
                    titles_history.push("تاریخچه سفارشات");
                }
                if (titles_history.length <= 1) $("#back").css('color', '#8080808c');
                $("#title_main").text(titles_history[titles_history.length - 1]);
                history_orders_get(user_id);
                break;
            case "messages":
                if (titles_messages.length === 0) {
                    titles_messages.push("پیام رسانی");
                }
                if (titles_messages.length <= 1) $("#back").css('color', '#8080808c');
                $("#title_main").text(titles_messages[titles_messages.length - 1]);
                break;
            case "doctor":
                if (titles_doctor.length === 0) {
                    titles_doctor.push("مشاوره");
                }
                if (titles_doctor.length <= 1) $("#back").css('color', '#8080808c');
                $("#title_main").text(titles_doctor[titles_doctor.length - 1]);
                break;
            default:
                $("#title_main").text("دارسی");
                break;
        }
    } else {
        console.log("Please wait...");
    }
};

const toggle_slide_button_in = () => {
    $(".toggle_slide_button").css("transition-timing-function", "cubic-bezier(0.6, -0.28, 0.74, 0.05)");
    $.each($(".toggle_slide_button"), function (index, value) {
        $(value).css("top", 0);
    });
    $(".toggle_slide_buttons").attr("is_open", "0");
};

const toggle_slide_button_out = () => {
    $(".toggle_slide_button").css("transition-timing-function", "cubic-bezier(0.66, 0.65, 0.54, 1.33)");
    let top_buttons = -10;
    $.each($(".toggle_slide_button"), function (index, value) {
        let height_button = parseInt($(value).css("height")) + 10;
        top_buttons -= height_button;
        $(value).css("top", top_buttons + "px");
    });
    $(".toggle_slide_buttons").attr("is_open", "1");
};

const user_login = () => {
    let header = `شماره تلفن همراه خود را وارد کنید`;
    let content = `<input class='cinput cinput-login' type="number" onkeydown="if(this.value.length===11 && event.keyCode!==8) return false;" id='login_input' placeholder='مثال: 09123456789'>`;
    let footer = `<button onclick='user_login_send_phone()' class='cbtn cbtn-login-submit'>ارسال</button>`;
    let close_able = 1;
    let delay = 0;
    modal_show(header, content, footer, close_able, delay);
    setTimeout(function () {
        $("#login_input").focus();
    }, 500);
};

const user_login_send_phone = () => {
    let phone = $("#login_input").val();
    if (!phone) {
        snackbar("شماره تلفن خود را وارد کنید", "red");
        $("#login_input").focus();
    } else {
        if (phone.length < 10) {
            snackbar("شماره تلفن خود را به طور صحیح وارد کنید", "red");
            $("#login_input").focus();
        } else {
            let send_code = user_login_send_code_to_user(phone);
            if (send_code.status === "ok") {
                snackbar(send_code.message, "green");
                modal_hide();
                let header = `کد تایید را وارد کنید`;
                let content = `<input class='cinput cinput-login' id="login_user_code" type="number" onkeydown="if(this.value.length===4 && event.keyCode!==8) return false;" placeholder="کد تایید چهار رقمی"/>`;
                let footer = `<button onclick='user_login_send_code()' class='cbtn cbtn-login-submit'>ارسال</button>`;
                let close_able = 1;
                let delay = 1000;
                modal_show(header, content, footer, close_able, delay);
                setTimeout(function () {
                    $("#login_user_code").focus();
                }, 500);
            } else {
                snackbar(send_code.message, "red");
                $("#login_input").focus();
            }
        }
    }
};

const user_login_send_code = () => {
    let code = $("#login_user_code").val();
    if (!code) {
        snackbar(" کد دریافتی خود را وارد کنید", "red");
        $("#login_user_code").focus();
    } else {
        if (code.length < 4) {
            snackbar(" کد صحیح نیست", "red");
            $("#login_user_code").focus();
        } else {
            let validate_code = user_login_verify_code(code);
            if (validate_code.status === "ok") {
                modal_hide();
                user = validate_code.data;
                let name_user = user.name;
                snackbar(`${name_user} گرامی خوش آمدید `, "green");
                profile_fill(user, "#profile_page_1");
            } else {
                snackbar(" کد صحیح نیست", "red");
                $("#login_user_code").focus();
            }
        }
    }
};

const next_page = title => {
    if (title.length > 40) {
        title = title.substring(0, 40);
        title += '...';
    }
    $("#title_main").text(title);
    if (!loading) {
        loading = 1;
        switch (active_article) {
            case "home":
                titles_home.push(title);
                scrollRight = $("#home").scrollLeft() + $(".home_page").width();
                $('#home').animate({scrollLeft: scrollRight + 'px'}, 600).promise().done(function () {
                    loading = 0;
                });
                home++;
                $("#back").css('color', '#1A7395');
                break;
            case "search":
                titles_search.push(title);
                scrollRight = $("#search").scrollLeft() + $(".search_page").width();
                $('#search').animate({scrollLeft: scrollRight + 'px'}, 600).promise().done(function () {
                    loading = 0;
                });
                search++;
                $("#back").css('color', '#1A7395');
                break;
            case "locations":
                titles_locations.push(title);
                scrollRight = $("#locations").scrollLeft() + $(".locations_page").width();
                $('#locations').animate({scrollLeft: scrollRight + 'px'}, 600).promise().done(function () {
                    loading = 0;
                });
                locations++;
                $("#back").css('color', '#1A7395');
                break;
            case "bascket":
                titles_bascket.push(title);
                scrollRight = $("#bascket").scrollLeft() + $(".bascket_page").width();
                $('#bascket').animate({scrollLeft: scrollRight + 'px'}, 600).promise().done(function () {
                    loading = 0;
                });
                bascket++;
                $("#back").css('color', '#1A7395');
                break;
            case "history_order":
                titles_history.push(title);
                scrollRight = $("#history_order").scrollLeft() + $(".history_page").width();
                $('#history_order').animate({scrollLeft: scrollRight + 'px'}, 600).promise().done(function () {
                    loading = 0;
                });
                history_order++;
                $("#back").css('color', '#1A7395');
                break;
            default:
                console.log("active_page ", active_article, " not definded");
                break;
        }
    } else {
        console.log("Please wait...");
    }
};

const prev_page = () => {
    if (!loading) {
        loading = 1;
        switch (active_article) {
            case "home":
                titles_home.pop();
                scrollLeft = $("#home").scrollLeft() - $(".home_page").width();
                $('#home').animate({scrollLeft: scrollLeft + 'px'}, 600).promise().done(function () {
                    loading = 0;
                });
                home--;

                $("#title_main").text(titles_home[titles_home.length - 1]);
                if (home < 0) {
                    home = 0;
                    titles_home = ["دارسی"];
                }
                if (home === 0) {
                    $("#back").css('color', '#8080808c');
                }
                break;
            case "search":
                titles_search.pop();
                scrollLeft = $("#search").scrollLeft() - $(".search_page").width();
                $('#search').animate({scrollLeft: scrollLeft + 'px'}, 600).promise().done(function () {
                    loading = 0;
                });
                search--;

                $("#title_main").text(titles_search[titles_search.length - 1]);
                if (search < 0) {
                    search = 0;
                    titles_search = ["جستجو"];
                }
                if (search === 0) {
                    $("#back").css('color', '#8080808c');
                }
                break;
            case "locations":
                titles_locations.pop();
                scrollLeft = $("#locations").scrollLeft() - $(".locations_page").width();
                $('#locations').animate({scrollLeft: scrollLeft + 'px'}, 600).promise().done(function () {
                    loading = 0;
                });
                locations--;

                $("#title_main").text(titles_locations[titles_locations.length - 1]);
                if (locations < 0) {
                    locations = 0;
                    titles_locations = ["داروخانه ها"];
                }
                if (locations === 0) {
                    $("#back").css('color', '#8080808c');
                }
                break;
            case "bascket":
                titles_bascket.pop();
                scrollLeft = $("#bascket").scrollLeft() - $(".bascket_page").width();
                $('#bascket').animate({scrollLeft: scrollLeft + 'px'}, 600).promise().done(function () {
                    loading = 0;
                });
                bascket--;

                $("#title_main").text(titles_bascket[titles_bascket.length - 1]);
                if (bascket < 0) {
                    bascket = 0;
                    titles_bascket = ["سبد خرید"];
                }
                if (bascket === 0) {
                    $("#back").css('color', '#8080808c');
                }
                break;
            case "history_order":
                titles_history.pop();
                scrollLeft = $("#history_order").scrollLeft() - $(".history_page").width();
                $('#history_order').animate({scrollLeft: scrollLeft + 'px'}, 600).promise().done(function () {
                    loading = 0;
                });
                history_order--;

                $("#title_main").text(titles_history[titles_history.length - 1]);
                if (history_order <= 0) {
                    history_order = 0;
                    titles_history = ["تاریخچه سفارشات"];
                    $("#back").css('color', '#8080808c');
                }
                break;
            default:
                console.log("active_page ", active_article, " not definded");
                break;
        }
    } else {
        console.log("Please wait...");
    }

};

const fill_batch_list = (data, target, image) => {

    $(target).html("");
    let target_end;
    if (target === '#locations_page_3 ul') {
        target_end = '#locations_page_4 ul';
    } else if (target === '#home_page_2 ul') {
        target_end = '#home_page_3 ul';
    }
    $.each(data, function (index, value) {
        $(target).append(
            `<li onclick="get_list_item(1, '${target_end}', '${value.name}')" class="list_batch" title="${value.name}" target=${value.id}>
                <p>${value.name}</p>
                <img alt="image" src=${image}>
            </li>`
        );
    });
};

const show_more_detils = (id) => {
    
    // TODO: have this code we will use it later
    // let target_end;
    // if (target === '#locations_page_5') {
    //     target_end = '#locations_page_6';
    // } else if (target === '#home_page_4') {
    //     target_end = '#home_page_5';
    // } else if (target === '#search_page_3') {
    //     target_end = '#search_page_4';
    // }
    // $(target).html("");
    // $(target).html(
    //     `<div class="img">
    //         <img alt="item" class="item" src="/pictures/${value.image}">
    //     </div>
    //     <div class="names">
    //         <div class="name">${name}</div>
    //         <div class="description">${description}</div>
    //         <div class="functions">
    //             <div class="price"> ${cama_for_digit(value.price)} تومان</div>
    //             <div class="plus_mines">
    //                 <span class="oprator plus">+</span>
    //                 <span class="number">0</span>
    //                 <span class="oprator mines">-</span>
    //             </div>
    //         </div>
    //     </div>`
    // );
    // next_page(name);

    if ($("#"+id).hasClass("hide_detail")) {
        // چون با انیمیشن نمیشه ارتفاع را اوتو کرد من اول ارتفاع رو اوتو میکنم و اندازشو میگیرم و بعد بر میگردونم سر جاش و با انیمیت میبرم به اون ارتفاع
        let = autoHeight = $("#"+id).css('height', 'auto').height(); $("#"+id).css('height', '125px');
        //
        $("#"+id).animate({
            "height": autoHeight
        }, 500).removeClass("hide_detail"); 
        console.log($("#"+id).attr("class"));
        console.log("#"+id)
    } else {
        $("#"+id).animate({
            "height": "125px"
        }, 500).addClass("hide_detail");
        console.log($("#"+id).attr("class")) 
        console.log("hide","#"+id)
    }
    // if ($("#"+id).hasClass("hide_detail")) {
    //     $("#"+id).css({
    //         "height": "auto"
    //     }).removeClass("hide_detail"); 
    //     console.log($("#"+id).attr("class"));
    //     console.log("#"+id)
    // } else {
    //     $("#"+id).css({
    //         "height": "120px"
    //     }).addClass("hide_detail");
    //     console.log($("#"+id).attr("class")) 
    //     console.log("hide","#"+id)
    // }

};

const cama_for_digit = (x, symbole = "/") => x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, `<sub>${symbole}</sub>`);

const search_function = () => {
    let title = $("#search_product_input").val();
    let data = search_item(title);
    console.log(data);
    next_page(title);
};

const fill_list_items = (data, target) => {
    let target_end;
    if (target === '#home_page_3 ul') {
        target_end = '#home_page_4';
    } else if (target === '#locations_page_4 ul') {
        target_end = '#locations_page_5';
    } else if (target === '#search_page_2 ul') {
        target_end = '#search_page_3';
    }
    $(target).html("");
    $.each(data, function (index, value) {
        let name = value.name;
        let description = value.description_short.replace("\\r\\n", "", "g").replace("\r\n", "<br>", "g");
        let description_normal = value.description_normal.replace("\\r\\n", "", "g").replace("\r\n", "<br>", "g");
        name = name.split("*")[0];
        description = description.substring(0, 100);
        description += '...';
        let li_id = target.replace(" ", "_").replace("#", "")+index;
        $(target).append( /*html*/
            `<li id="${li_id}" class="list_item_li hide_detail" title="${name}" target=${value.id}>
                <div class="list_item_li_image_names">
                    <div class="img"> 
                        <img alt="item" class="item" src="/pictures/${value.image}">
                    </div>
                    <div class="names">
                        <div class="name">${name}</div>
                        <div class="description">${description}</div>
                        <div class="functions">
                            <div class="price"> ${cama_for_digit(value.price)} تومان </div>
                            <div onclick="show_more_detils('${li_id}')" class="button_description">توضیحات</div>
                            <div class="plus_mines">
                                <span class="oprator plus">+</span>
                                <span class="number">0</span>
                                <span class="oprator mines">-</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="more_details">
                    <hr class="more_details_hr">
                    <div class="description_normal">
                        ${description_normal} 
                    </div>
                    <div class="pdf_function">
                        <a href="/pictures/${value.description_pdf}" class="cbtn pdf_function_btn" download ><span>دانلود اطلاعات تکمیلی</span><i class="fa fa-download"></i></a>
                        <a class="cbtn pdf_function_btn" onclick="view_pdf_file('${value.description_pdf}', '${target_end}')"><span>نمایش اطلاعات تکمیلی</span><i class="fa fa-eye"></i></a>
                    </div>
                </div>
			</li>`
        );
    });
};

const fill_root_item = (data, target) => {
    $(target).html("");
    $.each(data, function (index, value) {
        $(target).append( 
            `<li onclick="get_batch_list(${value.id}, '#locations_page_3 ul', '${value.name}', '${value.image}')" class="locations_page_2_buttons" title=${value.name} target=${value.id}>
				<p>${value.name}</p>
				<img alt="img" src=${value.image}>
			</li>`
        );
    });
};

const select_address = (data, title, target) => {
    let main_price = 0;
    let items = '';
    function_div = /*html*/
        `<div class="div_functions">
            <button class="cbtn button_function button_go_to_payment" onclick="payment_function(' پرداخت ', '#bascket_page_3')">
                <span> پرداخت و ثبت سفارش </span><i class="fa fa-2x fa-caret-right"></i>
            </button>
        </div>`

        items = "";
        $.each(data.items, function (index, value) { 
            items += /*html*/
                `<tr>
					<td>${value.name}</td>
					<td>${value.price.replace("تومان","")}</td>
					<td id="${value.id}">
					    <div class="plus_mines plus_mines_bascket"><span onclick="inc_item(${value.id})" class="oprator plus">+</span><span class="number">${value.count}</span><span onclick="dec_item(${value.id})" class="oprator mines">-</span></div>
					</td>
					<td>${cama_for_digit(value.count * parseInt((value.price).replace("/", "")))}</td>
				</tr>`;
            main_price += value.count * parseInt((value.price).replace("/", ""));
        });
    
    $(target).html( /*html*/
        `<div class="div_view">
			<div class="bascket_header">جمع کل: <span class="bascket_header_main_price">${cama_for_digit(main_price)}</span> تومان </div>
			<table class="bascket_table">
				<colgroup>
					<col width="40%">
					<col width="20%">
					<col width="20%">
					<col width="20%">
				</colgroup>
				<thead>
					<tr>
						<th>نام محصول</th>
						<th> فی (تومان)  </th>
						<th>تعداد</th>
						<th>جمع (تومان)</th>
					</tr>
				</thead>
				<tbody>
					${items}
				</tbody>
			</table>
            <hr class="hr_select_address">
            <div class="order_details">
                <div><span>مالیات:</span><span> &nbsp;${data.vat}&nbsp; تومان</span></div>
                <div><span>هزینه حمل:</span><span> &nbsp;${data.transport}&nbsp; تومان</span></div>
                <div><span>تخفیف:</span><span>&nbsp;${data.discount}&nbsp; تومان</span></div>
                <div><span>استفاده از کیف پول:</span><span>&nbsp;${user.amount_wallet}&nbsp; تومان</span>    <div onclick="turn_on_toggle_button(this)" class="toggle_button off"><span></span></div></div>
            </div>
            <div class="bascket_inputs_select_address"> 
                <div class="input_select_address">
                    <input class="cinput" placeholder="آدرس خود را وارد کنید">
                </div>
                <div class="discount_code_div">
                    <input class="cinput" placeholder="کد تخفیف خود را وارد کنید">
                    <button onclick="check_discount_code(this)" class="cbtn"> اعمال کد </button>
                </div>
            </div>
        </div>
        ${function_div}
        `
    );
    next_page(title);
};

const apply_use_wallet = (data) => {
    if (data.status === "ok") {
        $(".bascket_header_main_price").text(parseInt($(".bascket_header_main_price").text().replace("/",""))-user.amount_wallet);
        //$(".bascket.header").html(`جمع کل: ${cama_for_digit(main_price-user.amount_wallet)} تومان`);
    }
}

const apply_dont_use_wallet = (data) => {
    if (data.status === "ok") {
        $(".bascket_header_main_price").text(parseInt($(".bascket_header_main_price").text().replace("/",""))+parseInt(user.amount_wallet));
        //$(".bascket.header").html(`جمع کل: ${cama_for_digit(main_price+user.amount_wallet)} تومان`);
    }
}

const turn_on_toggle_button = (el) => {
    if ($(el).hasClass("off")) {
        $(el).find("span").animate({
            "margin-left":"26px"
        }, 100);
        $(el).css({"background":"red", "border-color": "red"});
        $(el).removeClass("off");
        use_wallet();
    } else {
        $(el).find("span").animate({
            "margin-left":"0"
        }, 100);
        $(el).css({"background":"gray", "border-color": "gray"});
        $(el).addClass("off");
        dont_use_wallet();
    }
}

const apply_discount = (data) => {
    if (data.status === "ok") {
        snackbar(`مبلغ ${data.data} تومان تخفیف اعمال شد`, "green");
    } else {
        snackbar("کد تخفیف اشتباه است", "red");
    }
}

const payment_function = (title, target) => {
    $(target).html("<div class='payment_page'> پرداخت </div>");
    next_page(title);
}

const profile_fill = (user_data, target) => {
    $(target).html("");
    console.log("profile_fill", user_data);
    if (user_data) {
        $(target).html( /*html*/
            `<div class="div_view">
                <div class='profile' class='profile_photo'>
                    <img alt="img" src='${user_data.image}'>
                </div>
                <div class='profile_phone profile_row'>
                    <span>شماره تلفن:</span>
                    <span>${user_data.phone}</span>
                </div>
                <div class='profile_name profile_row'>
                    <span>نام و نام خانوادگی:</span>
                    <span class="change_able">${user_data.name}</span>
                </div>
                <div class='profile_gender profile_row'>
                    <span>جنسیت:</span>
                    <span class="change_able">${user_data.gender}</span>
                </div>
                <div class='profile_birthday profile_row'>
                    <span>تاریخ تولد:</span>
                    <span class="change_able">${user_data.birth_day}</span>
                </div>
            </div>
            <div class='div_functions'>
                <button onclick="ready_user_update(this)" class="cbtn profile_uodate_button"><span>ثبت اطلاعات</span><i class="fa fa-2x fa-caret-right"></i></button>
            </div>`
        );
        $(target).removeClass("not_login");
    } else {
        $(target).html(`<p>برای مشاهده پروفایل وارد شوید</p><button class="cbtn cbtn-login" onclick="user_login()">ورود / ثبت نام</button>`);
        $(target).addClass("not_login");
    }
};

$(".main_page").on("click", ".change_able", function () {
    let current_value = $(this).text();
    $(this).html(`<input value='${current_value}' class='cinput edit_user_profile'>`).removeClass("change_able");
});

const ready_user_update = (element) => {
    let name                  = $(element).parent().prev().children().eq("2").find("input").val();
    let gender                = $(element).parent().prev().children().eq("3").find("input").val();
    let birth_day             = $(element).parent().prev().children().eq("4").find("input").val();
    if (!name) name           = user.name;
    if (!gender) gender       = user.gender;
    if (!birth_day) birth_day = user.birth_day;
    let data = {
        "name": name,
        "gender": gender,
        "birth_day": birth_day
    }
    update_user_data(data);
}

const wallet_fill = (data, target) => {
    // TODO: we are here 
    $(target).html( /*html*/
        `<div class='wallet'>
            <div class='wallet_amount'>
                <p>موجودی کیف پول (اعتبار)</p>
                <p class="the_amount_wallet"> ${cama_for_digit(data.amount_wallet)} تومان</p>
                <button class="cbtn">صورتحساب</button>
            </div>
            <div class='wallet_sharj'>
                <button class="cbtn">افزایش اعتبار</button>
                <div class='wallet_input_radio'>
                    <div><input type='radio' name='wallet_sharj_amount'>5000 </div>
                    <div><input type='radio' name='wallet_sharj_amount'>10000 </div>
                    <div><input type='radio' name='wallet_sharj_amount'>20000 </div>
                    <div><input type='radio' name='wallet_sharj_amount'>50000 </div>
                    <div><input type='radio' name='wallet_sharj_amount'>100000 </div>
                    <div><button class="cbtn">پرداخت</button></div>
                </div>
            </div>
        </div>`
    );
};

const history_orders_fill = (data, target) => {
    $(target).html("");
    $.each(data, function (index, value) {
        $(target).append( /*html*/
            `<div class="div_history_page_table">
                <table class="history_page_table history_page_table_order">
                    <colgroup>
                        <col width="18%">
                        <col width="18%">
                        <col width="18%">
                        <col width="33%">
                        <col width="13%">
                    </colgroup>
                    <tr onclick='history_order_get_details(${value.order_id})'>
                        <td>${value.order_id}</td>
                        <td>${value.date}</td>
                        <td>${value.shop_name}</td>
                        <td>${cama_for_digit(value.order_items)}</td>
                        <td style="border-left: none">${cama_for_digit(value.order_price)}</td>
                    </tr>
                </table>
            </div>`
        );
    });
};

const history_orders_fill_details = (data, target) => {
    console.log(data);
    let items = '';
    $.each(data.items, function (index, value) {
        items += `<tr>
                       <td>${value.name}</td>
                       <td>${cama_for_digit(value.price)}</td>
                       <td>${value.item_count}</td>
                       <td>${cama_for_digit((value.price) * (value.item_count))}</td>
                   </tr>`;
    });
    $(target).html( /*html*/
        `<div class='history_orders_details div_view'>
			<p>قابل پرداخت: ${cama_for_digit(data.main_price)} تومان </p>
            <table class='history_details_table bascket_table'>
                <colgroup>
                <col width="50%">    
                <col width="20%">    
                <col width="10%">    
                <col width="20%">    
                </colgroup>
				<thead>
					<tr>
						<th>نام محصول</th>
						<th>فی (تومان)</th>
						<th>تعداد</th>
						<th>مبلغ(تومان)</th>
					</tr>
        		</thead>
				<tbody>
					${items}
				</tbody>
            </table>
            <hr class="hr_history">
			<table class='history_details_table_more'>
				<tr>
					<td>شماره سفارش: ${data.order_id}</td>
					<td>زمان ازسال: ${data.date}</td>
				</tr>
				<tr>
					<td> مالیات: ${cama_for_digit(data.vat)}</td>
					<td> هزینه حمل: ${cama_for_digit(data.transport)}</td>
				</tr>
				<tr>
					<td> استفاده از کیف پول: ${cama_for_digit(data.wallet)}</td>
					<td> تخفیف: ${cama_for_digit(data.discount)}</td>
				</tr>
				<tr>
					<td> وضعیت سفارش: ${data.status}</td>
					<td> وضعیت پرداخت: ${data.payment}</td>
				</tr>
		    </table>
			    <p class="history_details_more_address"><i style="color: #00899a" class="fa fa-map"></i> &nbsp;  آدرس:   ${data.address} </p>
			</div>
			<div class='div_functions'>
				<button class="cbtn pdf_function_btn history_details_button" onclick="repeat_this_order(${data.order_id})">
					تکرار سفارش
				</button>
				<button class="cbtn pdf_function_btn history_details_button" onclick="check_state_order(${data.order_id})" >
					پیگیری شفارش
				</button>
			</div>`
    );
};

const repeat_this_order= (order_id) => {
    console.log(order_id);
    order = get_order_repeat(order_id);
    order_list = order.order_list;
    order_list_count = 0;
    $.each(order_list, function(index, value) {
        order_list_count++;
    })
    $(".badget_bascket").text(order_list_count).fadeIn("fast").css('display', 'flex');
    $(".shopping-cart").effect("shake", {
        times: 2
    }, 200);
}

const check_state_order = (order_id) => {
    let phone_store = get_shop_phone(order_id);
    $("#history_page_3").html( /*html*/
        `<div class="peygrir">
            <div class="peygiri_icon_call">
                <a href="tel:${phone_store.data.tel}"><i class="fab fa-whatsapp"></i></a>
            </div>
            <div class="peygrir_textarea_div">
                <textarea></textarea>
                <button>ارسال</button>
            </div>
        </div>`
    );
    console.log(order_id);
    next_page("پیگیری سفارش");
};

const bascket_fill = (order_list) => {
    console.log(order_list);
    let main_price = 0;
    let items = '';
    let function_div = '';
    if (order_list.length < 1) {
        items = `<tr><td colspan="4"> سبد خرید خالی است </td></tr>`;
    } else {
        items = "";
        function_div = `<div class="div_functions">
                            <button class="cbtn button_function button_select_address" onclick="get_order_complete('انتخاب آدرس', '#bascket_page_2')">
                                <span>انتخاب آدرس</span><i class="fa fa-2x fa-caret-right"></i>
                            </button>
                        </div>`
        $.each(order_list, function (index, value) { 
            items += /*html*/
                `<tr>
					<td>${value.name}</td>
					<td>${value.price.replace("تومان","")}</td>
					<td id="${value.id}">
					    <div class="plus_mines plus_mines_bascket"><span onclick="inc_item(${value.id})" class="oprator plus">+</span><span class="number">${value.count}</span><span onclick="dec_item(${value.id})" class="oprator mines">-</span></div>
					</td>
					<td>${cama_for_digit(value.count * parseInt((value.price).replace("/", "")))}</td>
				</tr>`;
            main_price += value.count * parseInt((value.price).replace("/", ""));
        });
    }
    $("#bascket_page_1").html( /*html*/
        `<div class="div_view">
			<div class="bascket_header">جمع کل: ${cama_for_digit(main_price)} تومان </div>
			<table class="bascket_table">
				<colgroup>
					<col width="40%">
					<col width="20%">
					<col width="20%">
					<col width="20%">
				</colgroup>
				<thead>
					<tr>
						<th>نام محصول</th>
						<th> فی (تومان)  </th>
						<th>تعداد</th>
						<th>جمع (تومان)</th>
					</tr>
				</thead>
				<tbody>
					${items}
				</tbody>
			</table>
		</div>
        ${function_div}
        `
    );
};

const inc_item = (item_id) => {
    console.log(item_id);
};

const dec_item = (item_id) => {
    console.log(item_id);
};

const snackbar = (text, color) => {
    $("#snackbar").attr('class', 'show');
    $("#snackbar").html(text);
    $("#snackbar").css('background', color);
    setTimeout(function () {
        $("#snackbar").attr('class', '');
    }, 3000);
};

$(".toggle_slide_buttons").click(function () {
    is_open = $(".toggle_slide_buttons").attr("is_open");
    if (is_open === '1') {
        toggle_slide_button_in();
    } else {
        toggle_slide_button_out();
    }
});

$(".toggle_slide_button").click(function (e) {
    e.preventDefault();
    e.stopPropagation();
});

$(".main_page").on("click", ".oprator", function () {
    if ($(this).parent().hasClass("plus_mines_bascket")) {
        let item_id;
        item_id = $(this).closest("td").attr("id");
        if ($(this).hasClass("plus")) {
            let add_to_cart_result = add_to_cart(item_id, "plus");
            if (add_to_cart_result.status === "ok") {
                $(this).next().text(parseInt($(this).next().text()) + 1);
                $.each(order_list, function (index, value) {
                    if (value.id === item_id) {
                        value.count++;
                        item_exist = 1;
                    }
                });
                let cart = $('.shopping-cart');
                is_adding++;
                setTimeout(function () {
                    cart.effect("shake", {
                        times: 2
                    }, 200);
                    $(".badget_bascket").text(parseInt($(".badget_bascket").text()) + 1).fadeIn("fast").css('display', 'flex');
                    is_adding--;
                }, 100);

            } else {
                snackbar(add_to_cart_result.message, "red")
            }
        } else if ($(this).hasClass("mines")) {
            if (is_adding) {
                console.log("is adding please wait");
            } else {
                let add_to_cart_result = add_to_cart(item_id, "mines");
                if (add_to_cart_result.status === "ok") {
                    for (let i = 0; i < order_list.length; i++) {
                        if (order_list[i].id === item_id) {
                            order_list[i].count--;
                            if (order_list[i].count === 0) {
                                order_list.splice(i, 1);
                            }
                        }
                    }
                    $(this).prev().text(parseInt($(this).prev().text()) - 1);
                    if ($(this).prev().text() < 0) {
                        $(this).prev().text(0);
                    } else {
                        $(".badget_bascket").text(parseInt($(".badget_bascket").text()) - 1);
                    }
                    if (parseInt($(".badget_bascket").text()) <= 0) {
                        $(".badget_bascket").fadeOut("fast");
                        $(".badget_bascket").text(0);
                    }
                } else {
                    snackbar(add_to_cart_result.message, "red");
                }
            }
        }
    } else {
        let item_id;
        let item_name;
        let item_price;
        let item_exist = 0;
        item_id = ($(this).closest("li").attr("target"));
        item_name = ($(this).closest("li").find(".name").text());
        item_price = ($(this).closest("li").find(".price").text());

        if ($(this).hasClass("plus")) {
            let add_to_cart_result = add_to_cart(item_id, "plus");
            if (add_to_cart_result.status === "ok") {
                $(this).next().text(parseInt($(this).next().text()) + 1);
                $.each(order_list, function (index, value) {
                    if (value.id === item_id) {
                        value.count++;
                        item_exist = 1;
                    }
                });
                if (!item_exist) {
                    item = {
                        "id": item_id,
                        "name": item_name,
                        "price": item_price,
                        "count": 1
                    };
                    order_list.push(item);
                }
                let cart = $('.shopping-cart');
                let imgtodrag = $(this).parent().parent().parent().parent().find("img").eq(0);
                if (imgtodrag) {
                    is_adding++;
                    let imgclone = imgtodrag.clone()
                        .offset({
                            top: imgtodrag.offset().top,
                            left: imgtodrag.offset().left
                        })
                        .css({
                            'opacity': '0.8',
                            'position': 'absolute',
                            'height': '150px',
                            'width': '150px',
                            'z-index': '100'
                        })
                        .appendTo($('body'))
                        .animate({
                            'top': cart.offset().top + 10,
                            'left': cart.offset().left + 10,
                            'width': 75,
                            'height': 75
                        }, 1000, 'easeInOutExpo');

                    setTimeout(function () {
                        cart.effect("shake", {
                            times: 2
                        }, 200);
                        $(".badget_bascket").text(parseInt($(".badget_bascket").text()) + 1).fadeIn("fast").css('display', 'flex');
                        is_adding--;
                    }, 1500);
                    imgclone.animate({
                        'width': 0,
                        'height': 0
                    }, function () {
                        $(this).detach()
                    });
                }
            } else {
                snackbar(add_to_cart_result.message, "red")
            }
        } else if ($(this).hasClass("mines")) {
            if (is_adding) {
                console.log("is adding please wait");
            } else {
                let add_to_cart_result = add_to_cart(item_id, "mines");
                if (add_to_cart_result.status === "ok") {
                    for (let i = 0; i < order_list.length; i++) {
                        if (order_list[i].id === item_id) {
                            order_list[i].count--;
                            if (order_list[i].count === 0) {
                                order_list.splice(i, 1);
                            }
                        }
                    }
                    $(this).prev().text(parseInt($(this).prev().text()) - 1);
                    if ($(this).prev().text() < 0) {
                        $(this).prev().text(0);
                    } else {
                        $(".badget_bascket").text(parseInt($(".badget_bascket").text()) - 1);
                    }
                    if (parseInt($(".badget_bascket").text()) <= 0) {
                        $(".badget_bascket").fadeOut("fast");
                        $(".badget_bascket").text(0);
                    }
                } else {
                    snackbar(add_to_cart_result.message, "red");
                }
            }
        }
    }
});

const modal_show = (header = null, content = null, footer = null, close_able = 1, delay = 0) => {
    setTimeout(function () {
        $("#modal").fadeIn().promise().done(function () {
            $("#modal_body").animate({"top": "10%"}, 300);
        });
        $("#modal_header").html(header);
        $("#modal_content").html(content);
        $("#modal_footer").html(footer);
        if (close_able) {
            $("#modal_header").append(
                `<span id="modal_hide_X" onclick="modal_hide()"><i class="fa fa-times"></i></span>`
            );
            $("#modal").attr("onclick", "modal_hide()")
        } else {
            $("#modal").attr("onclick", "");
        }
    }, delay);
};

const modal_hide = () => {
    let start = +new Date();
    $("#modal_body").animate({"top": "-100%"}, 500).promise().done(function () {
        $("#modal").fadeOut("fast").promise().done(function () {
            let end = +new Date();
            console.log(end - start);
        });
    });
};

const location_map = () => {
    locations_update_list();
    user_get_location();
    mymap.on('click', onMapClick);
    setTimeout(function () {
        mymap.invalidateSize();
    }, 500);
};

const user_get_location = () => {
    navigator.geolocation.getCurrentPosition(showPosition, errorHandler, {
        enableHighAccuracy: true,
        maximumAge: 10000,
        timeout: 10000
    });
};

const showPosition = position => {
    let lat = position.coords.latitude;
    let long = position.coords.longitude;
    let accuracy = position.coords.accuracy;

    user_location = {
        "lat": lat,
        "long": long,
        "acc": accuracy
    };
    locations_update_list(lat, long);
};

const locations_update_list = (lat = null, long = null) => {
    if (lat && long) {
        let Map_icon = L.icon({
            iconUrl: '../../../../leaflet/marker-icon.png',
            shadowUrl: '../../../../leaflet/marker-shadow.png',
            iconAnchor: [10, 40], // point of the icon which will correspond to marker's location
            shadowAnchor: [10, 40], // the same for the shadow
            popupAnchor: [2, -38] // point from which the popup should open relative to the iconAnchor
        });
        let pharm_icon = L.icon({
            iconUrl: 'images/location_map.png',
            // shadowUrl: '../../../../leaflet/marker-shadow.png',
            iconAnchor: [10, 40], // point of the icon which will correspond to marker's location
            shadowAnchor: [10, 40], // the same for the shadow
            popupAnchor: [2, -38] // point from which the popup should open relative to the iconAnchor
        });
        if (marker_location) mymap.removeLayer(marker_location);
        marker_location = L.marker([lat, long], {
            icon: Map_icon
        }).addTo(mymap);
        mymap.panTo(new L.LatLng(lat, long));
        let pharms = get_around_pharm(lat, long);
        for (let i = 0; i < pharms.length; i++) {
            let pharm_lat  = pharms[i].location.split(',')[0];
            let pharm_lang = pharms[i].location.split(',')[1];
            let distance   = Math.round(getDistanceFromLatLonInKm(lat, long, pharm_lat, pharm_lang) * 1000);
            let transport  = Math.round(parseInt(pharms[i].transport_rate * distance) + parseInt(pharms[i].transport_fixed));
            pharms[i]['distance'] = distance;
            pharms[i]['transport'] = transport;
            pharms_marker[i] = L.marker([pharm_lat, pharm_lang], {
                icon: pharm_icon
            }).addTo(mymap).bindPopup('<span style="font-size:10pt;">' + pharms[i]['name'] + '</span>').on('click', onClick);
        }
        let group = new L.featureGroup(pharms_marker);
        mymap.fitBounds(group.getBounds());
        pharms = pharms.sort(compare);
        fill_list_pharm(pharms);
    } else {
        $("#list_pharms").html("");
        $("#list_pharms").append(`<div class="error_get_location">لطفا موقعیت خود را روی نقشه مشخص کنید</div>`)
    }
};

const errorHandler = () => {
    snackbar("لطفا موقعیت خود را روی نقشه مشخص کنید <br> ،دریافت موقعیت  شما با مشکل مواجه شده است", "red");
};

const onMapClick = e => {
    lat = e.latlng.lat;
    long = e.latlng.lng;
    locations_update_list(lat, long);
};

const getDistanceFromLatLonInKm = (lat1, lon1, lat2, lon2) => {

    let R = 6371; // Radius of the earth in km
    let dLat = deg2rad(lat2 - lat1); // deg2rad below
    let dLon = deg2rad(lon2 - lon1);
    let a =
        Math.sin(dLat / 2) * Math.sin(dLat / 2) +
        Math.cos(deg2rad(lat1)) * Math.cos(deg2rad(lat2)) *
        Math.sin(dLon / 2) * Math.sin(dLon / 2);
    let c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
     // Distance in km
    return R * c;
};

const deg2rad = deg => deg * (Math.PI / 180);

const compare = (a, b) => {
    if (a.distance < b.distance)
        return -1;
    if (a.distance > b.distance)
        return 1;
    return 0;
};

const fill_list_pharm = (pharms) => {
    $("#list_pharms").html("");
    $.each(pharms, function (index, value) {
        $("#list_pharms").append( /*html*/
            `<div class="pharm_listed" onClick="get_root_item(${value.id}, '#locations_page_2 ul', '${value.name}')">
                 <div class="pharm_name_distance">
                    <span>${value.name}</span>
                    <span> ${value.distance} <span>متر</span>  </span>
                 </div>
                 <div class="pharm_transport">
                    <span>هزینه حمل:</span>
                    <span> ${cama_for_digit(value.transport)}  <span>تومان</span>  </span>
                 </div>
            </div>`
        );
    });
};

const onClick = function () {
    this.openPopup();
};

const view_pdf_file = (file, target) => {
    if (target === '#locations_page_5') {
        target_end = '#locations_page_6';
    } else if (target === '#home_page_4') {
        target_end = '#home_page_5';
    } else if (target === '#search_page_3') {
        target_end = '#search_page_4';
    }
    console.log(target , " => ", target_end);
    let file_pdf = "https://shop.partapp.ir/pictures/"+file;
    // $(target).html( 
    //     `<div class="preview_pdf">
    //         <object data="${file_pdf}" type="application/pdf" width="100%" height="auto"><span class="not_login">امکان پیش نمایش وجود ندارد</span></object>
    //     </div>`
    // );
    $(target).html( /*html*/
        `<div class="preview_pdf">
            <iframe src="https://docs.google.com/gview?url=${file_pdf}&embedded=true" style="width:100%; height:82vh;" frameborder="0"></iframe>
        </div>`
    );
    next_page("توضیحات تکمیلی");
}

const download_pdf_file = (file) => {
    window.location = "/pictures/"+file;
}





























































