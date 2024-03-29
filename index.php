<!-- any page divided to two part: div_view and div_function div_view: 85% and divc_function 15% -->
<!doctype html>
<html lang="fa">
<head>
    <meta charset="utf-8">
    <link rel="icon" type="image/png" href="images/main_logo.png"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
    <link rel="stylesheet" href="../../css/normalize.css">
    <link href="css/style.css" rel="stylesheet" type="text/css">
    <link href="css/fontawesome-all.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" type="text/css" href="../../../leaflet/leaflet.css" />
    <link rel="stylesheet" href="../../css/easy-button.css">
</head>
<body touch-action="none">
    <header id="header">
        <div id="back" onclick="prev_page()">
            <i class="fa fa-arrow-alt-circle-left"></i>
        </div>
        <div id="title_main">دارسی</div>
        <div id="logo"><img alt="logo" src="images/main_logo.png"></div>
    </header>
    <article class="main_page" id="home">
        <div class="home_page" id="home_page_1">
            <ul class="root_item_list">
            </ul>
        </div>
        <div class="home_page" id="home_page_2">
            <ul>

            </ul>
        </div>
        <div class="home_page" id="home_page_3">
            <ul class="list_item">

            </ul>
        </div>
        <div class="home_page" id="home_page_4">
        </div>
        <div class="home_page" id="home_page_5">
        </div>
    </article>
    <article class="main_page" id="search">
        <div class="search_page" id="search_page_1">
            <div id="search_prduct">
                <input id="search_product_input" type="text" placeholder="جستجو محصول در کلیه مراکز فروش...">
                <button onclick="search_function()"></button>
            </div>
        </div>
        <div class="search_page" id="search_page_2">
            <ul class="list_item">

            </ul>
        </div>
        <div class="search_page" id="search_page_3">

        </div>
        <div class="search_page" id="search_page_4">

        </div>
    </article>
    <article class="main_page" id="locations">
        <div class="locations_page" id="locations_page_1">
            <div id="map"></div>
            <ul id="list_pharms" class="list_style list_pharmcy">
                <li onclick="get_root_item(1, '#locations_page_2 ul', 'داروخانه یک')">
                    داروخانه یک
                </li>
            </ul>
        </div>
        <div class="locations_page" id="locations_page_2">
            <ul class="root_item_list"></ul>
        </div>
        <div class="locations_page" id="locations_page_3">
            <ul class="ul_list_batch"></ul>
        </div>
        <div class="locations_page" id="locations_page_4">
            <ul class="list_item"></ul>
        </div>
        <div class="locations_page" id="locations_page_5">
            <ul class="list_item"></ul>
        </div>
        <div class="locations_page" id="locations_page_6">
            <ul class="list_item"></ul>
        </div>
    </article>
    <article class="main_page" id="bascket">
        <div class="bascket_page" id="bascket_page_1">
        </div>
        <div class="bascket_page" id="bascket_page_2">
        </div>
        <div class="bascket_page" id="bascket_page_3">
        </div>
    </article>
    <article class="main_page" id="profile">
        <div class="profile_page" id="profile_page_1">
        </div>
    </article>
    <article class="main_page" id="wallet">
        <div class="wallet_page" id="wallet_page_1">
        </div>
        <div class="wallet_page" id="wallet_page_2">
        </div>
    </article>
    <article class="main_page" id="history_order">
        <div class="history_page" id="history_page_1">
            <table class="history_page_table">
                <colgroup>
                    <col width="18%">
                    <col width="18%">
                    <col width="18%">
                    <col width="31%">
                    <col width="15%">
                </colgroup>
                <thead>
                    <tr>
                        <th>شماره سفارش</th>
                        <th>تاریخ</th>
                        <th>مرکز فروش</th>
                        <th>شرح سفارش</th>
                        <th> مبلغ(تومان)</th>
                    </tr>
                </thead>
            </table>
            <div id="history_table_tbody"></div>
        </div>
        <div class="history_page" id="history_page_2">

        </div>
        <div class="history_page" id="history_page_3">

        </div>
    </article>
    <article class="main_page" id="messages">پیام رسانی</article>
    <article class="main_page" id="doctor">doctor</article>
    <!--  Modal  -->
    <div id="modal">
        <div id="modal_body" onclick="event.stopPropagation();">
            <div id="modal_header">header</div>
            <div id="modal_content">content</div>
            <div id="modal_footer">footer</div>
        </div>
    </div>
    <!--  /Modal  -->
    <footer>
        <ul>
            <li onclick="main_button('home')" target="home" title="دارسی" class="footer_active main_buttons">
                <img alt="image" src="images/home.png"/>
            </li>
            <li onclick="main_button('search')" target="search" title="جستجو" class="main_buttons">
                <img alt="image" src="images/search.png"/>
            </li>
            <li onclick="main_button('locations')" target="locations" title="داروخانه ها" class="main_buttons">
                <img alt="image" src="images/location.png"/>
            </li>
            <li onclick="main_button('bascket')" target="bascket" title="سبد خرید" class="main_buttons bascket_li">
                <span data-badget="0" class="badget badget_bascket"> 0 </span>
                <img alt="image" class="shopping-cart" src="images/cart.png"/>
            </li>
            <li class="footer_more_button">
                <div class="toggle_slide_buttons">
                    <button class="ellipsis-v"><img alt="image" src="images/more.png"/></button>
                </div>
            </li>
            <li onclick="main_button('profile')" target="profile" title="پروفایل" class="toggle_slide_button main_buttons">
                <img alt="image" src="images/profile.png"/>
            </li>
            <li onclick="main_button('wallet')" target="wallet" title="کیف پول" class="toggle_slide_button main_buttons">
                <img alt="image" src="images/wallet.png"/>
            </li>
            <li onclick="main_button('history_order')" target="history_order" title="تاریخچه سفارشات"
                class="toggle_slide_button main_buttons">
                <img alt="image" src="images/history.png"/>
            </li>
            <li onclick="main_button('messages')" target="messages" title="پیام ها"
                class="toggle_slide_button main_buttons">
                <img alt="image" src="images/messages.png"/>
            </li>
            <li onclick="main_button('doctor')" target="doctor" title="مشاوره" class="toggle_slide_button main_buttons">
                <img alt="image" src="images/doctor.png"/>
            </li>
        </ul>
    </footer>
    <div id="snackbar"></div>
    <script language="javascript" src="js/jquery-3.3.1.min.js"></script>
    <script src='js/jquery-ui.min.js'></script>
    <script language="javascript" src="js/script.js"></script>
    <script language="javascript" src="js/backend.js"></script>
    <script type="text/javascript" src="../../../leaflet/leaflet.js"></script>
    <script type="text/javascript" src="../../js/easy-button.js"></script>
</body>
</html>
