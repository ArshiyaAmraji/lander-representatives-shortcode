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

add_action('init', function () {
  register_post_type('lander_agency', [
    'labels' => [
      'name'          => 'نمایندگی‌ها',
      'singular_name' => 'نمایندگی',
      'add_new'       => 'افزودن نمایندگی',
      'add_new_item'  => 'افزودن نمایندگی جدید',
      'edit_item'     => 'ویرایش نمایندگی',
    ],
    'public'       => true,
    'show_in_menu' => true,
    'menu_icon'    => 'dashicons-location',
    'supports'     => ['title'],
  ]);
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
  
 #$wrap_id{
  --primary: #FF4A00;
  --primary-soft: rgba(255,164,0,.15);
  --dark: #140E4A;
  --dark-soft: rgba(20,14,74,.12);
}

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
  margin: 16px;
  box-shadow: 0 0 0 6px #fff, 0 0 0 10px rgba(255,164,0,.15), 0 25px 70px rgba(255,164,0,.15);
  background:#fff;
  position:relative;
  z-index:1;
}

#$wrap_id .container{
  width:100%;
  max-width:480px;
  padding:20px 16px;
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
  text-align:center;
  font-size:28px;
  margin:0 0 20px;
}

#$wrap_id .top-filters{
  display:flex;
  align-items:center;
  height:42px;
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

#$wrap_id select[id^="lander-citySelect"]{
  margin-top: 8px;
  margin-bottom: 0px;
}

#$wrap_id #$search_id{
  width:100%;
  height: 2rem;
  padding:16px 20px;
  margin:10px 0 12px;
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
  min-width:42px;
  border-radius:12px;
  color:#fff;
  display:flex;
  align-items:center;
  justify-content:center;
  cursor:pointer;
  transition:all .2s ease;
  padding:5px;
  margin-right: 5px;
  vertical-align:middle; 
  line-height:1;
  background-color: var(--dark);}

#<?php echo $wrap_id; ?> .filter-mini-btn svg{
  width:20px;
  height:20px;
  display:block;
}

#<?php echo $wrap_id; ?> .filter-mini-btn:hover{
  transform:translateY(-1px);
  box-shadow:0 10px 26px rgba(30,64,175,.45);
  background-color: var(--dark);
}

#<?php echo $wrap_id; ?> .filter-mini-btn:active{
  transform:scale(.95);
}

#$wrap_id .filter-mini-btn:hover{ background:#1e3a8a; }

#$wrap_id .nearest-inline-btn{
  width:100%;
  margin:0 0 7px 0px;
  padding:6px;
  border-radius:16px;
  border:none;
  font-weight:500;
  font-size: 80%;
  cursor:pointer;
  box-shadow:0 10px 30px;
  transition:.18s;
  height:30px;
}
#$wrap_id .nearest-inline-btn:hover{
  transform:translateY(-2px);
  box-shadow:0 14px 25px;
  background-color: #140E4A;
  color: #fff;

}
#$wrap_id .nearest-inline-btn:disabled{ opacity:.7; cursor:not-allowed; }

#$wrap_id .desktop-nearest{ display:block; }
#$wrap_id .mobile-actions{ display:none; gap:10px; }
#$wrap_id .go-to-map-btn{
  height:30px;
  padding:0 12px;
  border-radius:16px;
  border:1px solid var(--primary);
  background: var(--primary);
  color: var(--dark);
  font-size:80%;
  font-weight:700;
  cursor:pointer;
  font-family:'Vazirmatn', sans-serif;

  display:flex;
  align-items:center;
  justify-content:center;

  width:auto;        /* ← خیلی مهم */
  min-width:unset;  /* ← اطمینان */
}

#$wrap_id .agency-list{
  flex:1;
  overflow-y:auto;
  padding-bottom:20px;
  min-height:0;
}

#$wrap_id .agency-item{
  background:#f1f5f9;
  border-right:6px solid var(--dark);
  padding:10px;
  margin:18px 0;
  border-radius:16px;
  cursor:pointer;
  transition:.25s;
  box-shadow:0 4px 15px rgba(0,0,0,.05);
}
#$wrap_id .agency-item:hover{
  transform:translateY(-3px);
  background: var(--primary-soft);
  box-shadow:0 12px 30px rgba(255,164,0,.35);
}
#$wrap_id .agency-item strong{
  font-size:19px;
  display:block;
  margin:0 0 6px;
}

#$wrap_id .activity-badge{
  display:inline-flex;            /* به‌جای inline-block */
  align-items:center;
  max-width:100%;
  min-width: 0px;

  font-size:10px;
  font-weight:700;
  color:#fff;
  background-color: var(--dark);
  border-radius:14px;
  padding:2px 10px;
  margin-bottom:5px;
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
  transform:translateY(-4px) scale(1.01);
  position:relative;
  overflow:hidden;
  background: linear-gradient(135deg, var(--primary), #ffb733) !important;
  color: var(--dark) !important;
  border-right:8px solid var(--dark) !important;
}
#$wrap_id .agency-item.nearest-one strong,
#$wrap_id .agency-item.nearest-one .agency-address,
#$wrap_id .agency-item.nearest-one .phone-link{ color: var(--dark) !important; }

/* Map hint */
#$wrap_id .map-hint{
  position:absolute;
  top:16px;
  right:16px;
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
  background-color: #140E4A;
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
  margin:0 0 14px;
  font-size:19px;
  font-weight:900;
  color: var(--dark);
}
#$wrap_id .neshan-btn{
  display:block;
  margin:18px auto 0;
  padding:10px 30px;
  text-decoration:none;
  border-radius:14px;
  font-weight:700;
  font-size:15px;
  transition:.18s;
  background-color: var(--primary);
  color: #fff !important;
}
#$wrap_id .neshan-btn:hover{ background: var(--primary); transform:translateY(-2px); }

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
  font-weight:900;
}
#$wrap_id .close-service{
  width:36px;
  height:36px;
  background:var(--dark);
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
  border:2px solid var(dark);
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
  background:var(--dark);
  color:#00000ff;
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
    padding:0px;
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
    height:65vh;
    min-height:65vh;
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
  #$wrap_id .phone-link{ font-size:10px !important; font-weight:400 !important; margin-top:0px; padding-right: -2px;}
  .phone-icon{ width:14px !important; height:14px !important; stroke:currentColor; }
  #$wrap_id .activity-badge{ font-size:6.5px !important; .padding: 0px; margin-top: 5px; }
}

@media (max-width: 360px){
  #$wrap_id .leaflet-popup-content-wrapper,
  #$wrap_id .leaflet-popup-content{ max-width:220px !important; }
  #$wrap_id .popup-content h4{ font-size:14px !important; }
}


/* اسکرول لیست موبایل */
@media (max-width: 992px){
  #$wrap_id .list-box{
    max-height:90vh;
    overflow:hidden;
  }
  #$wrap_id .agency-list{
    max-height:55vh;
    overflow-y:auto;
    -webkit-overflow-scrolling:touch;
  }
}

@media (max-width: 480px){
  #$wrap_id .container{
    padding:10px 8px 0;
  }

  #$wrap_id .list-box{
    margin:0 6px;
    padding:18px 14px;
  }

  #$wrap_id .lander-map{
    margin:0 6px 10px;
    border-radius:22px;
	padding-right: 0px;
  }
}

#$wrap_id .top-filters{
  align-items:stretch;
}
@media (max-width: 480px){
  #$wrap_id .filter-mini-btn{
  height:100%;
  aspect-ratio:1/1;
  margin-right:5px;
  padding:0;
  position:static;  
}
}
/* === BeTheme Leaflet Popup FIX (FINAL) === */
#$wrap_id .leaflet-popup-content,
#$wrap_id .leaflet-popup-content *{
  height: auto !important;
  max-height: none !important;
  overflow: visible !important;
  display: block !important;
  opacity: 1 !important;
  visibility: visible !important;
}

#$wrap_id .leaflet-popup{
  overflow: visible !important;
}

#$wrap_id .leaflet-container{
  font-family: 'Vazirmatn', sans-serif !important;
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
	var currentProvinceKey = "";
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
    var currentCity = "";
    var currentService = "";
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
      minZoom: window.innerWidth <= 992 ? 4.4 : 5.1,
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
    var __lastWidth = window.innerWidth;
	var __rt = null;

	window.addEventListener("resize", function () {
	  clearTimeout(__rt);
	  __rt = setTimeout(function () {
		var w = window.innerWidth;

		// فقط وقتی عرض تغییر کرد (چرخش گوشی / تغییر واقعی سایز)
		if (w !== __lastWidth) {
		  __lastWidth = w;
		  updateMapView(map);
		}
	  }, 150);
	});


    var bluePin = L.icon({
      iconUrl: "https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-blue.png",
      shadowUrl: "https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png",
      iconSize: [25,41],
      iconAnchor: [12,41],
      popupAnchor: [1,-34],
      shadowSize: [41,41]
    });

    // Data (same as your main.js)

	

    function getTypeColor(){ return "#2563eb"; }

    function getPhoneHtml(phone){
      var phones = (phone || "").split("/").map(function(p){ return p.trim(); }).filter(Boolean);
      var html = "";
		phones.forEach(function(p){
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
	  closeButton: true,
	  sanitize: false   // ← مهم‌ترین خط
	};

	var popup = L.popup(popupOptions).setContent(popupHtml);

	var marker = L.marker([a.lat, a.lng], { icon: bluePin });
	marker.bindPopup(popup);

      marker.addTo(markersLayer);

      var item = document.createElement("div");
      item.className = "agency-item";
      item.innerHTML =
        "<strong>"+ title +"</strong>" +
        '<div class="activity-badge">' + (a.type||"") + "</div>" +
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
	  
	  var agencyPin = L.icon({
		  iconUrl: "https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-orange.png",
		  shadowUrl: "https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png",
		  iconSize:[25,41],
		  iconAnchor:[12,41],
		  popupAnchor:[1,-34],
		  shadowSize:[41,41]
		});


      agencyMarkers.push({
        marker: marker,
        element: item,
        text: (a.city+" "+(a.name||"")+" "+(a.addr||"")+" "+(a.phone||"")+" "+(a.type||"")).toLowerCase(),
        type: a.type || "",
		province: a.province || "",
		city: a.city || ""
      });

      listContainer.appendChild(item);
    });

    var provinceMap = {
      tehran: "تهران",
      alborz: "البرز",
      khorasanerazavi: "خراسانرضوی",
      esfehan: "اصفهان",
      fars: "فارس",
      azerbaijan: "تبریز",
      gilan: "رشت",
      qom: "قم",
	  kerman: "کرمان",
	  hormozgan: "هرمزگان",
	  yazd: "یزد",
	  hamedan: "همدان",
	  mazandaran: "مازندران",
    };

    var citiesByProvince = {
      tehran: [
        { id:"tehran", label:"تهران" }, { id:"rey", label:"ری" }, { id:"eslamshahr", label:"اسلامشهر" },
        { id:"shahrqods", label:"شهرقدس" }, { id:"shahriar", label:"شهریار" }, { id:"malard", label:"ملارد" },
        { id:"robatkarim", label:"رباط کریم" }, { id:"varamin", label:"ورامین" }, {id:"parand", label:"پرند"},{id:"andisheh", label: "اندیشه"},
      ],
      alborz: [
        { id:"karaj", label:"کرج" }, { id:"fardis", label:"فردیس" }, { id:"nazarabad", 		  label:"نظرآباد" }, { id:"hashtgerd", label:"هشتگرد" }, 
      ],
	  kerman: [
	  {id:"bam", label:"بم"},{id:"sirjan", label:"سیرجان"},{id:"jiroft", label:"جیرفت"},
	  ],
	  hormozgan: [
	  {id:"bandarabbas", label:"بندرعباس"},
	  ],
	  yazd:[{id:"yazd", label:"یزد"}],
	  khorasanerazavi:[{id:"mashhad", label:"مشهد"}],
	  fars:[
	  {id:"shiraz", label:"شیراز"},
	  ],
	  hamedan:[
	  {id:"hamedan", label:"همدان"}, {id:"toyserkan", label:"تویسرکان"},
	  ],
	  mazandaran: [
	  {id:"amol", label:"آمل"},
	  ],
	  gilan: [
	  {id:"rasht", label:"رشت"},
	  ],
	  esfehan: [
	  {id:"esfehan", label:"اصفهان"},
	  ],
    };

    var configZoom = {
      tehran:{ center:[35.7210,51.3890], zoom:9 },
      alborz:{ center:[35.864412,50.869161], zoom:11 },
      khorasanerazavi:{ center:[36.2970,59.6062], zoom:12 },
      esfehan:{ center:[33.29489106435352,52.511744367139414], zoom:10 },
      fars:{ center:[29.330184263972598,53.22394905665354], zoom:8 },
      azerbaijan:{ center:[38.0667,46.2833], zoom:12 },
      gilan:{ center:[37.36570654382224,49.48652442123952], zoom:10 },
      qom:{ center:[34.6399,50.8759], zoom:12 },
	  kerman: {center: [30.29069965139118, 57.05827952244834], zoom:12},
	  hormozgan: { center:[27.1832, 56.2666], zoom:10 },
	  yazd: {center:[31.958669094791038, 54.35245293609361], zoom:10},
	  hamedan: {center: [35.000323761857686,48.65557789322659], zoom:10},
	  mazandaran: {center: [36.36901211005802,51.89136642871176], zoom:9},
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
	  parand:{center:[35.4848712249257,50.948292183922206], zoom:12},
	  andisheh: {center:[35.700622325288265,51.027298930509176], zoom:12},
	  
      karaj:{ center:[35.8354,50.9604], zoom:12 },
      fardis:{ center:[35.7216,50.9759], zoom:13 },
      nazarabad:{ center:[35.9560,50.6095], zoom:13 },
      hashtgerd:{ center:[35.9614,50.6786], zoom:13 },
	  bam: {center:[29.095521522037057, 58.35615102765471], zoom:13},
	  sirjan: {center:[29.446786662474537,55.67094176949499], zoom:13},
	  jiroft: {center:[28.670601320665284,57.737524337292], zoom:13},
	  bandarabbas: {center:[27.194233047023797, 56.28802481184466], zoom:13},
	  yazd: {center:[31.896371777958386, 54.35682558893884], zoom:13},
	  mashhad:{ center:[36.2970,59.6062], zoom:13 },
	  shiraz:{ center: [29.60607600834959,52.53770627727468], zoom: 13},
	  hamedan:{ center: [34.802812766244756,48.51379815729629], zoom: 13},
	  toyserkan: {center: [34.548274515511736,48.44794713928573], zoom: 13},
	  amol: {center: [36.472999300347965,52.35016788246736], zoom: 12},
	  rasht: {center: [37.28164770555185,49.58442908435683], zoom:12},
	  esfehan: {center: [32.651244495962274,51.66711584730038], zoom:11},
    };

    function filterList(){
      var term = (searchBox.value || "").trim().toLowerCase();
      markersLayer.clearLayers();

      agencyMarkers.forEach(function(obj){
        var fullText = obj.text || (obj.element.textContent || "").toLowerCase();
        var matchesSearch = fullText.indexOf(term) !== -1;
		var matchesProvince =
		  !currentProvinceKey || obj.province === currentProvinceKey;
		var matchesCity =
		  !currentCity || obj.city === currentCity;

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
		currentCity = "";
        updateMapView(map);
        citySelect.innerHTML = '<option value="">همه شهرستان‌ها</option>';
        citySelect.style.display = "none";
        filterList();
        return;
      }

      currentProvinceKey = key;
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
              <option value="khorasanerazavi">خراسان رضوی</option>
              <option value="esfehan">اصفهان</option>
              <option value="fars">فارس</option>
              <option value="azerbaijan">تبریز</option>
              <option value="gilan">گیلان</option>
              <option value="qom">قم</option>
			  <option value="kerman">کرمان</option>
			  <option value="hormozgan">هرمزگان</option>
			  <option value="yazd">یزد</option>
			  <option value="hamedan">همدان</option>
		      <option value="mazandaran">مازندران</option>

            </select>

            <button id="<?php echo esc_attr($filter_btn_id); ?>" class="filter-mini-btn" aria-label="فیلتر خدمات">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <line x1="4" y1="21" x2="4" y2="14"></line>
                    <line x1="4" y1="10" x2="4" y2="3"></line>
                    <line x1="12" y1="21" x2="12" y2="12"></line>
                    <line x1="12" y1="8" x2="12" y2="3"></line>
                    <line x1="20" y1="21" x2="20" y2="16"></line>
                    <line x1="20" y1="12" x2="20" y2="3"></line>
                    <circle cx="4" cy="12" r="2"></circle>
                    <circle cx="12" cy="10" r="2"></circle>
                    <circle cx="20" cy="14" r="2"></circle>
                </svg>
            </button>
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

    <?php
    $agencies = [];

	$q = new WP_Query([
	  'post_type'      => 'lander_agency',
	  'posts_per_page' => -1,
	  'meta_key'       => 'priority',
	  'orderby'        => 'meta_value_num',
	  'order'          => 'ASC'
	]);


    while ($q->have_posts()) {
      $q->the_post();
		$agencies[] = [
		  'name'     => get_the_title(),
		  'province' => get_post_meta(get_the_ID(),'province', true), // 👈 اضافه شد
		  'city'     => get_post_meta(get_the_ID(),'city', true),
		  'lat'      => (float) get_post_meta(get_the_ID(),'lat', true),
		  'lng'      => (float) get_post_meta(get_the_ID(),'lng', true),
		  'addr'     => get_post_meta(get_the_ID(),'address', true),
		  'phone'    => get_post_meta(get_the_ID(),'phone', true),
		  'type'     => get_post_meta(get_the_ID(),'services', true),
		];
    }
    wp_reset_postdata();
    ?>

    <script>
      var agencies = <?php echo json_encode($agencies, JSON_UNESCAPED_UNICODE); ?>;
    </script>

    <script><?php echo $js; ?></script>
  </div>

<?php
return ob_get_clean();
});

