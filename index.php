<?php
/**
 * Shortcode: [lander_map]
 * Clean + isolated (BeTheme/BeBuilder friendly)
 */

if (!defined('ABSPATH')) exit;

add_action('wp_enqueue_scripts', function () {
  // Leaflet (always safe to enqueue)
  wp_enqueue_style('leaflet-css', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css', [], '1.9.4');
  wp_enqueue_script('leaflet-js', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js', [], '1.9.4', true);

  // Google Font (same as your style.css)
  wp_enqueue_style('lander-vazirmatn', 'https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;500;700;900&display=swap', [], null);
});

add_shortcode('lander_map', function () {
  $uid = wp_rand(1000, 999999);

  // Unique IDs per shortcode instance
  $wrap_id   = "lander-wrap-$uid";
  $map_id    = "lander-map-$uid";
  $list_id   = "lander-agencyList-$uid";
  $search_id = "lander-searchBox-$uid";
  $prov_id   = "lander-provinceSelect-$uid";
  $city_id   = "lander-citySelect-$uid";
  $svc_hidden_id = "lander-serviceFilter-$uid";

  $filter_btn_id = "lander-filterBtn-$uid";
  $modal_id      = "lander-serviceModal-$uid";
  $close_id      = "lander-closeService-$uid";
  $svc_select_id = "lander-serviceSelect-$uid";
  $apply_id      = "lander-applyService-$uid";

  $nearest_btn_id       = "lander-findNearestBtn-$uid";
  $nearest_btn_m_id     = "lander-findNearestBtnMobile-$uid";

  // -------------------------
  // CSS (fixed + isolated)
  // -------------------------
  $css = <<<CSS
/* ===== Lander Map (isolated) ===== */
#$wrap_id{
  font-family: 'Vazirmatn', sans-serif;
  direction: rtl;
  color: #1e293b;
}
#$wrap_id *{ box-sizing:border-box; }

#$wrap_id .main-wrapper{
  display:flex;
  flex-direction:row-reverse;
  min-height:100vh;
}

#$wrap_id .lander-map{
  flex:1;
  height: calc(100vh - 110px);
  top: 12px;
  border-radius: 20px;
  overflow: hidden; /* ok چون autoPan/keepInView رو فعال کردیم */
  margin: 16px;
  box-shadow: 0 0 0 6px #fff, 0 0 0 10px #e0e7ff, 0 25px 70px rgba(0,0,0,.25);
  background:#fff;
  position:relative;
  z-index:1;
}

#$wrap_id .container{
  width:100%;
  max-width:480px;
  padding:20px 16px;
  background:#f8fafc;
  height: calc(100vh - 78px);
  display:flex;
  flex-direction:column;
  min-width:0; /* مهم برای flex */
}

#$wrap_id .list-box{
  background:#fff;
  border-radius:20px;
  padding:24px;
  box-shadow:0 15px 50px rgba(0,0,0,.1);
  display:flex;
  flex-direction:column;
  height: calc(100vh - 100px);
  min-height:0; /* مهم برای اسکرول داخل flex */
}

#$wrap_id .list-box h2{
  color:#1e40af;
  text-align:center;
  font-size:28px;
  margin:0 0 20px;
}

#$wrap_id .top-filters{
  display:flex;
  align-items:center;
  gap:10px;
  margin-bottom:12px;
}

#$wrap_id select, 
#$wrap_id input{
  font-family:'Vazirmatn', sans-serif;
}

#$wrap_id #$prov_id,
#$wrap_id #$city_id{
  width:100%;
  height:42px;
  border-radius:12px;
  border:2px solid #e2e8f0;
  padding:0 12px;
  background:#fff;
}

#$wrap_id #$search_id{
  width:100%;
  padding:16px 20px;
  margin:0 0 12px;
  border:2px solid #e2e8f0;
  border-radius:16px;
  font-size:13px;
  background:#f8fafc;
}

#$wrap_id #$search_id:focus{
  outline:none;
  border-color:#3b82f6;
  background:#fff;
  box-shadow:0 0 0 4px rgba(59,130,246,.15);
}

#$wrap_id .filter-mini-btn{
  width:42px;
  height:42px;
  border-radius:12px;
  background:#1e40af;
  color:#fff;
  border:none;
  cursor:pointer;
  display:flex;
  align-items:center;
  justify-content:center;
  transition:.2s;
}
#$wrap_id .filter-mini-btn:hover{ background:#1e3a8a; }

#$wrap_id .nearest-inline-btn{
  width:100%;
  margin:0 0 7px;
  padding:6px;
  border-radius:16px;
  border:none;
  background:linear-gradient(135deg,#10b981,#059669);
  color:#fff;
  font-weight:500;
  font-size:13px;
  cursor:pointer;
  box-shadow:0 10px 30px rgba(16,185,129,.25);
  transition:.18s;
  height:30px;
}
#$wrap_id .nearest-inline-btn:hover{
  transform:translateY(-2px);
  box-shadow:0 14px 35px rgba(16,185,129,.45);
}
#$wrap_id .nearest-inline-btn:disabled{ opacity:.7; cursor:not-allowed; }

#$wrap_id .desktop-nearest{ display:block; }
#$wrap_id .mobile-actions{ display:none; gap:10px; }
#$wrap_id .go-to-map-btn{
  border-radius:16px;
  border:1px solid #1e40af;
  background:#fff;
  color:#1e40af;
  font-size:10px;
  font-weight:600;
  height:30px;
  cursor:pointer;
  font-family:'Vazirmatn', sans-serif;
}

#$wrap_id .agency-list{
  flex:1;
  overflow-y:auto;
  padding-bottom:20px;
  min-height:0;
}

#$wrap_id .agency-item{
  background:#f1f5f9;
  border-right:6px solid #3b82f6;
  padding:10px;
  margin:18px 0;
  border-radius:16px;
  cursor:pointer;
  transition:.25s;
  box-shadow:0 4px 15px rgba(0,0,0,.05);
}
#$wrap_id .agency-item:hover{
  transform:translateY(-3px);
  background:#e0f2fe;
  box-shadow:0 12px 30px rgba(59,130,246,.25);
}
#$wrap_id .agency-item strong{
  color:#1e40af;
  font-size:19px;
  display:block;
  margin:0 0 6px;
}

#$wrap_id .activity-badge{
  display:inline-block;
  width:auto;
  font-size:11px;
  font-weight:700;
  color:#fff;
  padding:4px 10px;
  border-radius:14px;
  margin:6px 0 10px;
  white-space:nowrap;
}

#$wrap_id .agency-address{ font-size:14px; color:#334155; }

#$wrap_id .phone-section{
  margin-top:10px;
  display:flex;
  align-items:center;
  flex-wrap:wrap;
  gap:10px;
  font-size:15px;
}
#$wrap_id .phone-link{
  color:#1d4ed8 !important;
  font-weight:500 !important;
  font-size:14px !important;
  text-decoration:none;
  display:inline-flex;
  align-items:center;
  gap:6px;
  transition:.18s;
}
#$wrap_id .phone-link:hover{
  color:#2563eb !important;
  transform:translateY(-1px);
}
#$wrap_id .phone-icon{ width:18px; height:18px; margin-bottom:5px; stroke:currentColor; }

#$wrap_id .distance-tag{
  display:inline-block;
  font-size:12px;
  font-weight:800;
  padding:3px 12px;
  border-radius:12px;
  margin:8px 6px 4px 0;
  backdrop-filter:blur(4px);
}
#$wrap_id .distance-tag.nearest{
  background:#e2e8f0;
  color:#1e40af;
  font-size:11px;
  font-weight:600;
}

#$wrap_id .agency-item.nearest-one{
  background:linear-gradient(135deg,#10b981,#059669) !important;
  color:#fff !important;
  border-right:8px solid #41806f !important;
  transform:translateY(-4px) scale(1.01);
  position:relative;
  overflow:hidden;
}
#$wrap_id .agency-item.nearest-one strong,
#$wrap_id .agency-item.nearest-one .agency-address,
#$wrap_id .agency-item.nearest-one .phone-link{ color:#fff !important; }

/* Map hint */
#$wrap_id .map-hint{
  position:absolute;
  top:16px;
  right:16px;
  background:rgba(30,64,175,.95);
  color:#fff;
  padding:5px 10px;
  border-radius:16px;
  font-size:10px;
  font-weight:600;
  z-index:600;
  white-space:nowrap;
  user-select:none;
  pointer-events:none;
  border:1px solid rgba(255,255,255,.2);
}

/* Leaflet popup tweaks */
#$wrap_id .popup-content{
  text-align:center;
  padding:20px 22px;
  direction:rtl;
  min-width:280px;
  max-width:340px;
  font-family:'Vazirmatn', sans-serif;
}
#$wrap_id .popup-content h4{
  color:#1e40af;
  margin:0 0 14px;
  font-size:19px;
  font-weight:900;
}
#$wrap_id .neshan-btn{
  display:block;
  margin:18px auto 0;
  padding:10px 30px;
  background:#10b981;
  color:#fff !important;
  text-decoration:none;
  border-radius:14px;
  font-weight:700;
  font-size:15px;
  transition:.18s;
}
#$wrap_id .neshan-btn:hover{ background:#0d9a6e; transform:translateY(-2px); }

#$wrap_id .leaflet-popup-content-wrapper{
  background:#fff !important;
  border-radius:18px !important;
  box-shadow:0 15px 45px rgba(0,0,0,.3) !important;
  padding:8px !important;
  overflow:visible !important;
}
#$wrap_id .leaflet-popup-content{ margin:0 !important; overflow:visible !important; }
#$wrap_id .leaflet-popup-pane{ z-index: 700 !important; }
#$wrap_id .leaflet-control-attribution{
  font-size:10px; /* قبلاً 2px بود که خیلی ریز می‌شد */
}

/* Service modal */
#$wrap_id .service-modal{
  position:fixed;
  inset:0;
  background:rgba(0,0,0,.45);
  backdrop-filter:blur(6px);
  display:flex;
  align-items:center;
  justify-content:center;
  z-index:2000;
  opacity:0;
  pointer-events:none;
  transition:.25s ease;
}
#$wrap_id .service-modal.active{
  opacity:1;
  pointer-events:auto;
}
#$wrap_id .service-box{
  background:rgba(255,255,255,.88);
  backdrop-filter:blur(18px);
  width:92%;
  max-width:380px;
  padding:20px;
  border-radius:20px;
  box-shadow:0 20px 50px rgba(11,37,71,.25);
  text-align:right;
  border:1px solid rgba(30,64,175,.08);
}
#$wrap_id .service-header{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:10px;
  margin-bottom:12px;
}
#$wrap_id .service-box h3{
  font-size:18px;
  margin:0;
  color:#1e3a8a;
  font-weight:900;
}
#$wrap_id .close-service{
  width:36px;
  height:36px;
  background:linear-gradient(90deg,#3b82f6,#1d4ed8);
  color:#fff;
  border-radius:10px;
  border:none;
  cursor:pointer;
  display:flex;
  align-items:center;
  justify-content:center;
  font-weight:800;
  transition:.18s;
}
#$wrap_id .close-service:hover{ transform:translateY(-2px); background:#dc2626; }
#$wrap_id .service-select{
  width:100%;
  padding:12px 14px;
  border-radius:12px;
  border:2px solid #e2e8f0;
  background:#fff;
  font-size:15px;
  box-shadow:0 8px 20px rgba(30,64,175,.03);
}
#$wrap_id .apply-btn{
  display:block;
  width:100%;
  margin-top:14px;
  padding:12px 16px;
  border-radius:12px;
  border:none;
  background:linear-gradient(90deg,#3b82f6,#1d4ed8);
  color:#fff;
  font-weight:800;
  cursor:pointer;
  box-shadow:0 12px 30px rgba(29,78,216,.18);
  transition:.18s;
}
#$wrap_id .apply-btn:hover{ transform:translateY(-3px); }

#$wrap_id #$svc_hidden_id{
  display:none !important;
  visibility:hidden !important;
  opacity:0 !important;
  height:0 !important;
  pointer-events:none !important;
}

/* ===== Responsive (FIXED MEDIA BRACES) ===== */
@media (max-width: 992px){
  #$wrap_id .main-wrapper{ flex-direction:column; }
  #$wrap_id .container{
    order:1;
    max-width:none;
    padding:16px 12px 0;
    height:auto;
  }
  #$wrap_id .list-box{
    border-radius:28px;
    margin:0 15px;
    padding:24px 20px;
    height:auto;
  }
  #$wrap_id .lander-map{
    order:2;
    height:75vh;
    min-height:75vh;
    margin:0 31px 12px;
    border-radius:28px;
  }
  #$wrap_id .desktop-nearest{ display:none; }
  #$wrap_id .mobile-actions{ display:flex; }
  #$wrap_id .map-hint{ top:12px; right:12px; font-size:9px; padding:7px 10px; }
}

@media (max-width: 480px){
  #$wrap_id .popup-content{ padding:14px 12px; min-width:unset; max-width:100%; }
  #$wrap_id .leaflet-popup-content-wrapper{ max-width:260px !important; padding:0 !important; margin:0 auto !important; }
  #$wrap_id .leaflet-popup-content{ max-width:260px !important; text-align:center !important; }
  #$wrap_id .neshan-btn{
    margin:12px auto 0 !important; /* FIX: قبلاً "12px auto px" بود */
    padding:9px 18px !important;
    font-size:13px !important;
    width:fit-content !important;
    min-width:140px;
  }
  #$wrap_id .phone-link{ font-size:11px !important; font-weight:400 !important; margin-top:5px; }
  #$wrap_id .activity-badge{ font-size:10px !important; }
}

@media (max-width: 360px){
  #$wrap_id .leaflet-popup-content-wrapper,
  #$wrap_id .leaflet-popup-content{ max-width:220px !important; }
  #$wrap_id .popup-content h4{ font-size:14px !important; }
}

body.page-id-1301 .section,
body.page-id-1301 .section_wrapper,
body.page-id-1301 .wrap,
body.page-id-1301 .column,
body.page-id-1301 .column_attr {
    background: transparent !important;
}

CSS;

  // -------------------------
  // JS (scoped + fixed popup)
  // -------------------------
  $js = <<<JS
(function(){
  function initLanderMap(){
    var wrap = document.getElementById("$wrap_id");
    if(!wrap) return;
    if(typeof L === "undefined") return;

    var mapEl = document.getElementById("$map_id");
    if(!mapEl) return;

    // Prevent double init
    if(mapEl.dataset.inited === "1") return;
    mapEl.dataset.inited = "1";

    var listContainer = document.getElementById("$list_id");
    var provinceSelect = document.getElementById("$prov_id");
    var citySelect = document.getElementById("$city_id");
    var searchBox = document.getElementById("$search_id");

    var filterBtn = document.getElementById("$filter_btn_id");
    var modal = document.getElementById("$modal_id");
    var closeService = document.getElementById("$close_id");
    var serviceSelect = document.getElementById("$svc_select_id");
    var applyBtn = document.getElementById("$apply_id");
    var hiddenService = document.getElementById("$svc_hidden_id");

    var nearestBtn = document.getElementById("$nearest_btn_id");
    var nearestBtnMobile = document.getElementById("$nearest_btn_m_id");

    var agencyMarkers = [];
    var userMarker = null;
    var currentProvince = "";
    var currentCity = "";
    var currentService = "";
    var currentProvinceKey = "";
    var markersLayer;

    var IRAN_BOUNDS = [[20,38],[44,70]];

    function updateMapView(map){
      if(window.innerWidth <= 992){
        map.setView([33.5, 52.5], 4.6);
      }else{
        map.fitBounds([[25,44],[39.8,63.4]], { padding: [50,50] });
      }
    }

    var map = L.map("$map_id", {
      center: [32.4279, 53.6880],
      zoom: window.innerWidth <= 992 ? 5 : 6,
      minZoom: window.innerWidth <= 992 ? 4.8 : 5.1,
      maxZoom: 18,
      maxBounds: IRAN_BOUNDS,
      maxBoundsViscosity: 0.75,
      zoomSnap: 0.1,
      zoomDelta: 1,
      zoomControl: false
    });

    function openPopupCentered(marker){
      var latLng = marker.getLatLng();
      var isMobile = window.innerWidth <= 992;
      var zoomLevel = isMobile ? 16 : 15.5;

      map.closePopup();
      map.flyTo(latLng, zoomLevel, { animate: true, duration: 0.9 });

      map.once("moveend", function(){
        marker.openPopup();
      });
    }

    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
      attribution: "&copy; OpenStreetMap contributors",
      maxZoom: 19
    }).addTo(map);

    L.control.zoom({ position: "topleft", zoomInTitle: "بزرگ‌نمایی", zoomOutTitle: "کوچک‌نمایی" }).addTo(map);
    L.control.attribution({ position: "bottomleft", prefix: "" }).addTo(map);

    markersLayer = L.layerGroup().addTo(map);

    updateMapView(map);
    window.addEventListener("resize", function(){ updateMapView(map); });

    var bluePin = L.icon({
      iconUrl: "https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-blue.png",
      shadowUrl: "https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png",
      iconSize: [25,41],
      iconAnchor: [12,41],
      popupAnchor: [1,-34],
      shadowSize: [41,41]
    });

    // Data (same as your main.js)
    var agencies = [
      { city:"تهران", name:"دفتر مرکزی", lat:35.736942070098976, lng:51.432493071143035, addr:"تهران، خیابان سهروردی، خیابان خرمشهر، خیابان عشقیار (نیلوفر)، کوچه چهارم (حورسی)، پلاک ۱", phone:"09127146489 / 02136483529", type:"ردیاب جی‌پی‌اس خودرو / ردیاب جی‌پی‌اس موتورسیکلت" },
      { city:"تهران", name:"دیجی سام (سامان آذرخوش)", lat:35.68696559794489, lng:51.42165512396892, addr:"تهران، میدان امام خمینی، اول فردوسی، پشت شهرداری، پاساژ لباف، طبقه 1", phone:"09127146489 / 02136483529", type:"ردیاب جی‌پی‌اس خودرو / ردیاب جی‌پی‌اس موتورسیکلت" },
      { city:"تهران", name:"فروشگاه موتوتیونینگ محسن (آقای شاملو)", lat:35.654444524066555, lng:51.49072091700788, addr:"تهران، اتوبان بسیج، ۲۰ متری افسریه، ۱۵ متری اول، نبش کوچه کنگاوری (۲۹)", phone:"02133145521 / 02138333099", type:"ردیاب جی‌پی‌اس موتورسیکلت" },
      { city:"تهران", name:"فروشگاه رحمانی (آقای مهران رحمانی)", lat:35.7012, lng:51.3456, addr:"تهران، خیابان عباسی، نبش دومین کوچه سمت چپ، پلاک ۲۹۴", phone:"09128404537 / 02155418982", type:"ردیاب جی‌پی‌اس خودرو / ردیاب جی‌پی‌اس موتورسیکلت" },
      { city:"تهران", name:"فروشگاه جام جم (آقای فرید نظری)", lat:35.74112645085245, lng:51.549589049711265, addr:"تهرانپارس، خیابان ۱۹۶ شرقی، پلاک ۲۲۹", phone:"09128300310 / 0217786751", type:"ردیاب جی‌پی‌اس خودرو / ردیاب جی‌پی‌اس موتورسیکلت" },
      { city:"تهران", name:"لندرشاپ (آقای رسولی)", lat:35.71284445034936, lng:51.36932671244907, addr:"تهران، ستارخان، بین شادمان و بهبودی، بعد از کوچه علی نجاری، پلاک ۲۴۴، طبقه اول", phone:"09122151330 / 02166559575", type:"ردیاب جی‌پی‌اس خودرو / ردیاب جی‌پی‌اس موتورسیکلت" },
      { city:"تهران", name:"آیبینو (محمدرضا عاشق)", lat:35.69490268819197, lng:51.40676256522828, addr:"تهران، خیابان جمهوری، پاساژ علاالدین ۲، طبقه همکف، واحد ۲۰", phone:"09129259105 / 02166170821", type:"ردیاب جی‌پی‌اس خودرو / ردیاب جی‌پی‌اس موتورسیکلت" },
      { city:"تهران", name:"زنگوله (مهرداد گرجی)", lat:35.735, lng:51.3234, addr:"تهرانپارس، میدان شاهد، خیابان ۱۹۶ شرقی، بین خیابان ۱۳۱ و ۱۳۳، پلاک ۳۷۳", phone:"09354223037", type:"ردیاب جی‌پی‌اس خودرو / ردیاب جی‌پی‌اس موتورسیکلت" },
      { city:"تهران", name:"علیرضا جابری (رضا اسپرت)", lat:35.68507926936174, lng:51.490814939322775, addr:"تهران، پیروزی، بلوار ابوذر، بین پل اول و دوم، کوچه جواهری، ششم غربی پلاک 1010", phone:"۰۹۱۲۳۳۸۶۴۵۴", type:"ردیاب جی‌پی‌اس خودرو / ردیاب جی‌پی‌اس موتورسیکلت" },
      { city:"ری", name:"جواد سیستم (آقای جواد پرتوی)", lat:35.36543079764921, lng:51.23044967671497, addr:"تهران، حسن آباد فشافویه، بلوار امام، جنب حوزه بسیج، پلاک ۹۱۵", phone:"۰۹۱۲۸۹۸۷۵۳۳", type:"ردیاب جی‌پی‌اس خودرو / ردیاب جی‌پی‌اس موتورسیکلت" },
      { city:"تهران", name:"فروشگاه بهار سیستم (آقای اکبری)", lat:35.68775732228994, lng:51.42020201840585, addr:"تهران خیابان فردوسی جنوبی بالاتر از میدان امام خمینی پاساژ 26 طبقه همکف پلاک 11", phone:"۰۹۱۲۲۴۸۹۴۲۵ / ۰۲۱۶۶۷۶۹۳۵۲", type:"ردیاب جی‌پی‌اس خودرو / ردیاب جی‌پی‌اس موتورسیکلت" },
      { city:"تهران", name:"فروشگاه بهار سیستم (آقای اکبری)", lat:35.6703036911901, lng:51.38341069224023, addr:"تهران، خیابان قزوین، خیابان شهید ابراهیمی (عباسی)، پلاک ۳۶۹", phone:"۰۲۱۵۵۴۲۶۷۱۲ / ۰۹۱۲۱۰۵۹۷۳۱", type:"دزدگیر اماکن و منازل / ردیاب جی‌پی‌اس خودرو" },
      { city:"تهران", name:"تعمیرگاه موتور زد (آقای امانی)", lat:35.71967118671216, lng:51.430824131481415, addr:"تهران، میدان هفت تیر، خیابان بهارشیراز، خیابان سلیمان خاطر، سمت چپ بلوار، پلاک ۳۴", phone:"۰۹۱۹۷۷۳۳۴۱۷", type:"دزدگیر اماکن و منازل / ردیاب جی‌پی‌اس خودرو" },
      { city:"تهران", name:"تعمیرگاه موتور زد (آقای امانی)", lat:35.72428530254722, lng:51.41616941976933, addr:"تهران، خیابان مطهری، نبش میرزای شیرازی، ضلع شمال غربی، پلاک ۲۸۵", phone:"۰۹۱۹۷۷۳۳۴۱۷", type:"دزدگیر اماکن و منازل / ردیاب جی‌پی‌اس خودرو" },
      { city:"تهران", name:"فروشگاه نگین غرب (آقای مرجانی)", lat:35.735, lng:51.3234, addr:"تهران، خیابان جلال آل احمد، نرسیده به اشرفی اصفهانی، خیابان شایق شمالی", phone:"۰۹۱۲۲۳۸۰۳۷۸", type:"ردیاب جی‌پی‌اس خودرو" },
      { city:"تهران", name:"فروشگاه آپشن سیتی (آقای مصطفی پوربخش)", lat:35.735, lng:51.3234, addr:"سهروردی شمالی، خیابان خلیل حسینی، سمت چپ، پلاک ۷۴", phone:"۰۲۱۸۸۷۴۶۹۶۳ / ۰۹۱۲۲۸۰۳۱۱۹", type:"ردیاب جی‌پی‌اس خودرو / ردیاب جی‌پی‌اس موتورسیکلت" },

      { city:"کرج", name:"نمایندگی کرج", lat:35.8321, lng:50.9654, addr:"جهانشهر، بلوار جمهوری", phone:"026-32511223", type:"فروش و نصب" },
      { city:"مشهد", name:"نمایندگی مشهد", lat:36.2970, lng:59.6062, addr:"وکیل آباد، نبش وکیل آباد ۲۵", phone:"051-36081234", type:"فروش و خدمات پس از فروش" },
      { city:"اصفهان", name:"نمایندگی اصفهان", lat:32.6539, lng:51.6660, addr:"چهارباغ بالا، نزدیک سی و سه پل", phone:"031-36654321", type:"فروش و نصب تخصصی" },
      { city:"شیراز", name:"فول آپشن", lat:29.5918, lng:52.5833, addr:"چمران، نرسیده به پل چمران", phone:"071-36281900", type:"فروش و نصب + تیونینگ" },
      { city:"تبریز", name:"نمایندگی تبریز", lat:38.0667, lng:46.2833, addr:"امام خمینی، نزدیک میدان ساعت", phone:"041-33345678", type:"فروش و خدمات" },
      { city:"رشت", name:"نمایندگی رشت", lat:37.2808, lng:49.5832, addr:"میدان شهرداری، سبزه میدان", phone:"013-33398765", type:"فروش و نصب" },
      { city:"قم", name:"نمایندگی قم", lat:34.6399, lng:50.8759, addr:"بلوار امین، نزدیک حرم", phone:"025-37754321", type:"فروش و خدمات پس از فروش" }
    ];

    function getTypeColor(){ return "#2563eb"; }

    function getPhoneHtml(phone){
      var phones = (phone || "").split("/").map(function(p){ return p.trim(); }).filter(Boolean);
      var html = "";
      phones.forEach(function(p, idx){
        if(idx>0) html += ' <span class="phone-separator">•</span> ';
        html += '<a href="tel:'+p+'" class="phone-link">' +
                  '<svg class="phone-icon" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">' +
                    '<path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>' +
                  '</svg>' + p + '</a>';
      });
      return html;
    }

    agencies.forEach(function(a){
      var title = a.name ? a.name : "نمایندگی لندر";
      var gmapUrl = "https://www.google.com/maps/search/?api=1&query=" + a.lat + "," + a.lng;

      var popupHtml =
        '<div class="popup-content">' +
          '<h4>' + title + '</h4>' +
          '<a href="'+ gmapUrl +'" target="_blank" class="neshan-btn" rel="noopener">مسیریابی با گوگل مپ</a>' +
        '</div>';

      // FIX: autoPan/keepInView to prevent clipped popup under overflow:hidden
      var popupOptions = {
        maxWidth: 340,
        minWidth: 280,
        autoPan: true,
        keepInView: true,
        closeButton: true
      };

      var marker = L.marker([a.lat, a.lng], { icon: bluePin }).bindPopup(popupHtml, popupOptions);
      marker.addTo(markersLayer);

      var item = document.createElement("div");
      item.className = "agency-item";
      item.innerHTML =
        "<strong>"+ title +"</strong>" +
        '<div class="activity-badge" style="background:'+ getTypeColor(a.type) +'">' + (a.type||"") + "</div>" +
        '<div class="agency-address">' + (a.addr||"") + "</div>" +
        '<div class="phone-section">' + getPhoneHtml(a.phone) + "</div>";

      item.addEventListener("click", function(){
        openPopupCentered(marker);
        if(window.innerWidth <= 992){
          setTimeout(function(){
            mapEl.scrollIntoView({ behavior:"smooth", block:"center" });
          }, 500);
        }
      });

      agencyMarkers.push({
        marker: marker,
        element: item,
        text: (a.city+" "+(a.name||"")+" "+(a.addr||"")+" "+(a.phone||"")+" "+(a.type||"")).toLowerCase(),
        type: a.type || ""
      });

      listContainer.appendChild(item);
    });

    var provinceMap = {
      tehran: "تهران",
      alborz: "کرج",
      khorasan: "مشهد",
      esfahan: "اصفهان",
      fars: "شیراز",
      azerbaijan: "تبریز",
      gilan: "رشت",
      qom: "قم"
    };

    var citiesByProvince = {
      tehran: [
        { id:"tehran", label:"تهران" }, { id:"rey", label:"ری" }, { id:"eslamshahr", label:"اسلامشهر" },
        { id:"shahrqods", label:"شهرقدس" }, { id:"shahriar", label:"شهریار" }, { id:"malard", label:"ملارد" },
        { id:"robatkarim", label:"رباط کریم" }, { id:"varamin", label:"ورامین" }
      ],
      alborz: [
        { id:"karaj", label:"کرج" }, { id:"fardis", label:"فردیس" }, { id:"nazarabad", label:"نظرآباد" }, { id:"hashtgerd", label:"هشتگرد" }
      ]
    };

    var configZoom = {
      tehran:{ center:[35.7210,51.3890], zoom:9 },
      alborz:{ center:[35.864412,50.869161], zoom:11 },
      khorasan:{ center:[36.2970,59.6062], zoom:12 },
      esfahan:{ center:[32.6539,51.6660], zoom:12 },
      fars:{ center:[29.5918,52.5833], zoom:12 },
      azerbaijan:{ center:[38.0667,46.2833], zoom:12 },
      gilan:{ center:[37.2808,49.5832], zoom:12 },
      qom:{ center:[34.6399,50.8759], zoom:12 }
    };

    var cityZoom = {
      tehran:{ center:[35.698,51.436], zoom:11 },
      rey:{ center:[35.3654,51.2304], zoom:14 },
      eslamshahr:{ center:[35.5466,51.2350], zoom:13 },
      shahrqods:{ center:[35.7129,51.1130], zoom:13 },
      shahriar:{ center:[35.6598,51.0588], zoom:12 },
      malard:{ center:[35.6670,50.9789], zoom:13 },
      robatkarim:{ center:[35.4849,51.0826], zoom:12 },
      varamin:{ center:[35.3256,51.6470], zoom:12 },
      karaj:{ center:[35.8354,50.9604], zoom:12 },
      fardis:{ center:[35.7216,50.9759], zoom:13 },
      nazarabad:{ center:[35.9560,50.6095], zoom:13 },
      hashtgerd:{ center:[35.9614,50.6786], zoom:13 }
    };

    function filterList(){
      var term = (searchBox.value || "").trim().toLowerCase();
      markersLayer.clearLayers();

      agencyMarkers.forEach(function(obj){
        var fullText = obj.text || (obj.element.textContent || "").toLowerCase();
        var matchesSearch = fullText.indexOf(term) !== -1;
        var matchesProvince = !currentProvince || fullText.indexOf(currentProvince) !== -1;

        var cityLabel = "";
        var arr = citiesByProvince[currentProvinceKey] || [];
        for(var i=0;i<arr.length;i++){
          if(arr[i].id === currentCity){ cityLabel = arr[i].label; break; }
        }
        var matchesCity = !currentCity || (obj.element.textContent || "").indexOf(cityLabel) !== -1;

        var matchesService = !currentService || (obj.type || "").indexOf(currentService) !== -1;

        var shouldShow = matchesSearch && matchesProvince && matchesCity && matchesService;
        obj.element.style.display = shouldShow ? "block" : "none";
        if(shouldShow) obj.marker.addTo(markersLayer);
      });
    }

    provinceSelect.addEventListener("change", function(){
      var key = this.value;

      if(!key){
        currentProvinceKey = "";
        currentProvince = "";
        currentCity = "";
        updateMapView(map);
        citySelect.innerHTML = '<option value="">همه شهرستان‌ها</option>';
        citySelect.style.display = "none";
        filterList();
        return;
      }

      currentProvinceKey = key;
      currentProvince = provinceMap[key] || "";

      if(configZoom[key]) map.setView(configZoom[key].center, configZoom[key].zoom, { animate:true });

      var cities = citiesByProvince[key];
      if(!cities || !cities.length){
        citySelect.style.display = "none";
        filterList();
        return;
      }

      citySelect.style.display = "block";
      citySelect.innerHTML = '<option value="">همه شهرستان‌ها</option>';
      cities.forEach(function(city){
        var opt = document.createElement("option");
        opt.value = city.id;
        opt.textContent = city.label;
        citySelect.appendChild(opt);
      });

      currentCity = "";
      filterList();
    });

    citySelect.addEventListener("change", function(){
      currentCity = this.value;

      if(currentCity && cityZoom[currentCity]){
        map.setView(cityZoom[currentCity].center, cityZoom[currentCity].zoom, { animate:true });
      }else if(!currentCity && currentProvinceKey && configZoom[currentProvinceKey]){
        map.setView(configZoom[currentProvinceKey].center, configZoom[currentProvinceKey].zoom, { animate:true });
      }

      filterList();
    });

    searchBox.addEventListener("input", filterList);

    // Service modal
    function openModal(){
      if(hiddenService) serviceSelect.value = hiddenService.value || "";
      modal.classList.add("active");
      modal.setAttribute("aria-hidden","false");
      serviceSelect.focus();
    }
    function closeModal(){
      modal.classList.remove("active");
      modal.setAttribute("aria-hidden","true");
      filterBtn.focus();
    }
    filterBtn.addEventListener("click", openModal);
    closeService.addEventListener("click", closeModal);
    modal.addEventListener("click", function(e){ if(e.target === modal) closeModal(); });
    document.addEventListener("keydown", function(e){ if(e.key === "Escape" && modal.classList.contains("active")) closeModal(); });

    function applySelection(){
      if(!hiddenService){ closeModal(); return; }
      hiddenService.value = serviceSelect.value;
      currentService = (hiddenService.value || "").trim();
      filterList();
      closeModal();
    }
    applyBtn.addEventListener("click", applySelection);
    serviceSelect.addEventListener("change", applySelection);

    // Nearest
    function resetFilters(){
      currentProvince = "";
      currentCity = "";
      currentProvinceKey = "";
      currentService = "";
      provinceSelect.value = "";
      citySelect.style.display = "none";
      citySelect.innerHTML = '<option value="">همه شهرستان‌ها</option>';
      searchBox.value = "";
      if(hiddenService) hiddenService.value = "";
      filterList();
    }

    function sortAgenciesByDistance(userLatLng){
      agencyMarkers.forEach(function(obj){
        var m = obj.marker.getLatLng();
        obj.distance = map.distance(userLatLng, m) / 1000;
      });
      agencyMarkers.sort(function(a,b){ return a.distance - b.distance; });

      listContainer.innerHTML = "";
      agencyMarkers.forEach(function(obj, idx){
        obj.element.classList.remove("nearest-one");

        if(idx === 0) obj.element.classList.add("nearest-one");

        var km = (obj.distance || 0).toFixed(1);
        var distanceText = '<div class="distance-tag '+ (idx===0 ? "nearest" : "") +'">' +
          (idx===0 ? "نزدیک‌ترین • " : "") + km + " کیلومتر</div>";

        var old = obj.element.querySelector(".distance-tag");
        if(old) old.remove();

        var badge = obj.element.querySelector(".activity-badge");
        if(badge) badge.insertAdjacentHTML("afterend", distanceText);
        else obj.element.querySelector("strong").insertAdjacentHTML("afterend", distanceText);

        listContainer.appendChild(obj.element);
      });
    }

    function findNearestAgency(){
      var btn = nearestBtn;
      if(!navigator.geolocation){
        alert("مرورگر از موقعیت مکانی پشتیبانی نمی‌کند");
        return;
      }

      resetFilters();
      btn.disabled = true;
      btn.textContent = "در حال دریافت موقعیت...";

      navigator.geolocation.getCurrentPosition(function(position){
        var userLocation = { lat: position.coords.latitude, lng: position.coords.longitude };

        if(userMarker) map.removeLayer(userMarker);

        userMarker = L.marker([userLocation.lat, userLocation.lng], {
          icon: L.icon({
            iconUrl:"https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png",
            shadowUrl:"https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png",
            iconSize:[25,41],
            iconAnchor:[12,41]
          })
        }).addTo(map).bindPopup("موقعیت شما").openPopup();

        sortAgenciesByDistance([userLocation.lat, userLocation.lng]);

        markersLayer.clearLayers();
        agencyMarkers.forEach(function(obj){ obj.marker.addTo(markersLayer); });

        var nearest = agencyMarkers[0];
        if(nearest){
          map.setView(nearest.marker.getLatLng(), 16, { animate:true });
          nearest.marker.openPopup();
          nearest.element.scrollIntoView({ behavior:"smooth", block:"center" });
        }

        btn.disabled = false;
        btn.textContent = "نزدیک‌ترین نمایندگی به من";
      }, function(){
        btn.disabled = false;
        btn.textContent = "تلاش مجدد";
        alert("خطا در دریافت موقعیت مکانی");
      });
    }

    if(nearestBtn) nearestBtn.addEventListener("click", findNearestAgency);
    if(nearestBtnMobile) nearestBtnMobile.addEventListener("click", findNearestAgency);

    var goToMapBtn = wrap.querySelector(".go-to-map-btn");
    if(goToMapBtn){
      goToMapBtn.addEventListener("click", function(){
        mapEl.scrollIntoView({ behavior:"smooth", block:"start" });
      });
    }

    // initial filter
    filterList();
  }

  // Run after DOM ready
  if(document.readyState === "loading"){
    document.addEventListener("DOMContentLoaded", initLanderMap);
  }else{
    initLanderMap();
  }
})();
JS;

  ob_start(); ?>

  <div id="<?php echo esc_attr($wrap_id); ?>" class="lander-map-wrapper">
    <style><?php echo $css; ?></style>

    <div class="main-wrapper">
      <div id="<?php echo esc_attr($map_id); ?>" class="lander-map">
        <div class="map-hint">برای مشاهده اطلاعات نمایندگی، روی نشانگر آبی کلیک کنید</div>
      </div>

      <div class="container">
        <div class="list-box">
          <h2>لیست نمایندگی‌ها</h2>

          <div class="top-filters">
            <select id="<?php echo esc_attr($prov_id); ?>">
              <option value="">همه استان‌ها</option>
              <option value="tehran">تهران</option>
              <option value="alborz">البرز</option>
              <option value="khorasan">مشهد</option>
              <option value="esfahan">اصفهان</option>
              <option value="fars">شیراز</option>
              <option value="azerbaijan">تبریز</option>
              <option value="gilan">رشت</option>
              <option value="qom">قم</option>
            </select>

            <button id="<?php echo esc_attr($filter_btn_id); ?>" class="filter-mini-btn" aria-label="فیلتر خدمات">☰</button>
          </div>

          <select id="<?php echo esc_attr($city_id); ?>" style="display:none;">
            <option value="">همه شهرستان‌ها</option>
          </select>

          <select id="<?php echo esc_attr($svc_hidden_id); ?>" style="display:none">
            <option value="">همه خدمات</option>
            <option value="ردیاب جی‌پی‌اس خودرو">ردیاب جی‌پی‌اس خودرو</option>
            <option value="ردیاب جی‌پی‌اس موتورسیکلت">ردیاب جی‌پی‌اس موتورسیکلت</option>
            <option value="دزدگیر اماکن و منازل">دزدگیر اماکن و منازل</option>
          </select>

          <input type="text" id="<?php echo esc_attr($search_id); ?>" placeholder="جستجوی نشانی یا نمایندگی...">

          <button id="<?php echo esc_attr($nearest_btn_id); ?>" class="nearest-inline-btn desktop-nearest">نزدیک‌ترین نمایندگی به من</button>

          <div class="action-row mobile-actions">
            <button id="<?php echo esc_attr($nearest_btn_m_id); ?>" class="nearest-inline-btn">نزدیک‌ترین نمایندگی به من</button>
            <button type="button" class="go-to-map-btn">مشاهده نقشه</button>
          </div>

          <div id="<?php echo esc_attr($list_id); ?>" class="agency-list"></div>
        </div>
      </div>
    </div>

    <div class="service-modal" id="<?php echo esc_attr($modal_id); ?>" aria-hidden="true">
      <div class="service-box" role="dialog" aria-modal="true">
        <div class="service-header">
          <h3>انتخاب نوع خدمات</h3>
          <button class="close-service" id="<?php echo esc_attr($close_id); ?>" aria-label="بستن">✕</button>
        </div>

        <select id="<?php echo esc_attr($svc_select_id); ?>" class="service-select" aria-label="انتخاب خدمات">
          <option value="">همه خدمات</option>
          <option value="ردیاب جی‌پی‌اس خودرو">ردیاب جی‌پی‌اس خودرو</option>
          <option value="ردیاب جی‌پی‌اس موتورسیکلت">ردیاب جی‌پی‌اس موتورسیکلت</option>
          <option value="دزدگیر اماکن و منازل">دزدگیر اماکن و منازل</option>
        </select>

        <button class="apply-btn" id="<?php echo esc_attr($apply_id); ?>">اعمال فیلتر</button>
      </div>
    </div>

    <script><?php echo $js; ?></script>
  </div>

  <?php
  return ob_get_clean();
});
