<?php
// Evitar acceso directo
if ( ! defined( 'ABSPATH' ) ) exit;

add_shortcode( 'giftia_app', 'gf_render_final_logic' );

function gf_render_final_logic( $atts ) {
    
    // PERFILES
    $profiles = [
        ['slug'=>'pareja', 'name'=>'Pareja', 'icon'=>'fa-heart'],
        ['slug'=>'familia', 'name'=>'Familia', 'icon'=>'fa-house-user'],
        ['slug'=>'amigos', 'name'=>'Amigos', 'icon'=>'fa-users'],
        ['slug'=>'peques', 'name'=>'Peques', 'icon'=>'fa-child-reaching'],
        ['slug'=>'compromiso', 'name'=>'Compromiso', 'icon'=>'fa-handshake'],
        ['slug'=>'friki', 'name'=>'Para Mí', 'icon'=>'fa-user-astronaut']
    ];

    // VIBES (Deben coincidir con los intereses)
    $vibes = [
        ['slug'=>'tech', 'name'=>'Tech', 'icon'=>'fa-microchip'],
        ['slug'=>'cocina', 'name'=>'Gourmet', 'icon'=>'fa-utensils'],
        ['slug'=>'viajero', 'name'=>'Viajes', 'icon'=>'fa-plane'],
        ['slug'=>'zen', 'name'=>'Zen/Relax', 'icon'=>'fa-spa'],
        ['slug'=>'fit', 'name'=>'Deporte', 'icon'=>'fa-dumbbell'],
        ['slug'=>'fashion', 'name'=>'Moda', 'icon'=>'fa-gem'],
        ['slug'=>'friki', 'name'=>'Fandom', 'icon'=>'fa-jedi']
    ];

    ob_start();
    ?>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;500;700;900&display=swap" rel="stylesheet">
    
    <style>
        body.gf-app-active { overflow: hidden !important; }
        #giftia-os { position: fixed !important; top: 0; left: 0; width: 100vw; height: 100vh; background: #0f172a; color: white; z-index: 2147483647; font-family: 'Outfit', sans-serif; display: flex; flex-direction: column; }
        .neural-bg { position: absolute; width: 100%; height: 100%; z-index: 0; background: radial-gradient(circle at 50% 50%, #1e293b 0%, #000 100%); pointer-events: none; }
        .os-nav { position: relative; z-index: 10; padding: 20px 30px; display: flex; justify-content: space-between; align-items: center; height: 60px; }
        .btn-back { background: rgba(255,255,255,0.1); border: none; color: white; width: 40px; height: 40px; border-radius: 50%; cursor: pointer; opacity: 0; transition: 0.3s; pointer-events: none; display: flex; align-items: center; justify-content: center;}
        .btn-back.show { opacity: 1; pointer-events: all; }
        .os-stage { flex: 1; position: relative; z-index: 5; display: flex; justify-content: center; align-items: center; padding: 20px; }
        .step-panel { position: absolute; width: 100%; max-width: 800px; display: flex; flex-direction: column; align-items: center; text-align: center; opacity: 0; transform: translateY(30px); pointer-events: none; transition: 0.5s cubic-bezier(0.19, 1, 0.22, 1); }
        .step-panel.active { opacity: 1; transform: translateY(0); pointer-events: all; }
        .step-panel.prev { opacity: 0; transform: translateY(-30px); }
        h1 { font-size: 32px; font-weight: 800; margin: 0 0 10px 0; line-height: 1.1; }
        p.sub { font-size: 16px; color: #94a3b8; margin-bottom: 25px; font-weight: 300; }
        .os-grid { display: grid; gap: 12px; width: 100%; grid-template-columns: repeat(3, 1fr); }
        .os-card { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; padding: 20px; cursor: pointer; transition: 0.2s; display: flex; flex-direction: column; align-items: center; justify-content: center; aspect-ratio: 1.3/1; }
        .os-card:hover { background: rgba(99, 102, 241, 0.2); border-color: #818cf8; transform: translateY(-5px); }
        .os-card i { font-size: 26px; margin-bottom: 10px; color: #cbd5e1; }
        /* SLIDER DOBLE */
        .range-wrapper { width: 100%; padding: 0 10px; box-sizing: border-box; position: relative; height: 50px; margin-bottom: 20px; }
        .slider-track { position: absolute; top: 50%; transform: translateY(-50%); height: 6px; width: 100%; background: #334155; border-radius: 5px; z-index: 1; }
        .slider-fill { position: absolute; height: 6px; top: 50%; transform: translateY(-50%); background: #818cf8; z-index: 2; border-radius: 5px; }
        .range-input { position: absolute; width: 100%; top: 50%; transform: translateY(-50%); pointer-events: none; -webkit-appearance: none; background: none; z-index: 3; margin: 0; }
        .range-input::-webkit-slider-thumb { -webkit-appearance: none; pointer-events: all; width: 24px; height: 24px; border-radius: 50%; background: white; cursor: pointer; box-shadow: 0 0 10px rgba(0,0,0,0.5); border: 2px solid #818cf8; }
        .price-labels { display: flex; justify-content: space-between; font-weight: 700; color: #818cf8; font-size: 18px; margin-top: -10px;}
        /* TIEMPO */
        .time-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; width: 100%; }
        .time-opt { background: transparent; border: 2px solid rgba(255,255,255,0.1); color: #94a3b8; padding: 12px 5px; border-radius: 12px; cursor: pointer; font-size: 12px; font-weight: 600; display: flex; flex-direction: column; align-items: center; gap: 6px; transition: 0.2s; }
        .time-opt.selected { background: white; color: black; border-color: white; transform: scale(1.05); }
        .btn-next { margin-top: 30px; width: 100%; padding: 16px; background: linear-gradient(90deg, #6366f1, #818cf8); color: white; border: none; border-radius: 50px; font-weight: 700; cursor: pointer; font-size: 16px; }
        /* FEED */
        #feed-overlay { position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: #000; z-index: 2147483650; overflow-y: scroll; scroll-snap-type: y mandatory; display: none; }
        .feed-item { width: 100%; height: 100vh; scroll-snap-align: start; position: relative; display: flex; justify-content: center; align-items: center; }
        .feed-bg { position: absolute; width: 100%; height: 100%; object-fit: cover; opacity: 0.5; }
        .feed-info { position: relative; z-index: 5; text-align: left; width: 90%; max-width: 500px; color: white; margin-top: 40vh; }
        .feed-price { font-size: 40px; font-weight: 900; color: #fbbf24; }
        .feed-title { font-size: 24px; font-weight: 700; margin: 10px 0 20px 0; line-height: 1.2; text-shadow: 0 2px 5px black; }
        .feed-cta { background: white; color: black; padding: 15px 30px; border-radius: 50px; text-decoration: none; font-weight: 800; display: inline-block; }
        .fallback-notice { background: rgba(255,255,255,0.2); backdrop-filter:blur(5px); padding: 5px 10px; border-radius: 5px; font-size: 11px; display: inline-block; margin-bottom: 10px; }
        @media (max-width: 768px) { .os-grid { grid-template-columns: repeat(2, 1fr); } .time-grid { grid-template-columns: repeat(2, 1fr); } }
    </style>

    <div id="giftia-os">
        <div class="neural-bg"><div class="orb orb-1"></div><div class="orb orb-2"></div></div>
        
        <div class="os-nav">
            <button class="btn-back" id="back-btn" onclick="goBack()"><i class="fa-solid fa-arrow-left"></i></button>
            <div style="font-weight:700; letter-spacing:1px;">GIFTIA AI</div>
            <div style="width:40px"></div>
        </div>

        <div class="os-stage">
            <div class="step-panel active" id="step-1">
                <h1>Objetivo</h1>
                <p class="sub">Elige perfil para calibrar.</p>
                <div class="os-grid">
                    <?php foreach($profiles as $p): ?>
                    <div class="os-card" onclick="setRec('<?php echo $p['slug']; ?>')">
                        <i class="fa-solid <?php echo $p['icon']; ?>"></i><span><?php echo $p['name']; ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="step-panel" id="step-2">
                <h1>Logística</h1>
                <p class="sub">Rango de precio y urgencia.</p>
                <div style="background:rgba(255,255,255,0.05); padding:20px; border-radius:15px; width:100%; margin-bottom:20px;">
                    <div class="price-labels"><span id="lbl-min">10€</span><span id="lbl-max">150€</span></div>
                    <div class="range-wrapper">
                        <div class="slider-track"></div><div class="slider-fill" id="fill-bar"></div>
                        <input type="range" min="0" max="300" value="10" step="5" class="range-input" id="range-min" oninput="slideMin()">
                        <input type="range" min="0" max="300" value="150" step="5" class="range-input" id="range-max" oninput="slideMax()">
                    </div>
                </div>
                <div style="width:100%;">
                    <div class="time-grid">
                        <div class="time-opt" onclick="setTime('immed', this)"><i class="fa-solid fa-bolt" style="color:#fbbf24"></i> Digital</div>
                        <div class="time-opt" onclick="setTime('fast', this)"><i class="fa-solid fa-truck-fast"></i> 24h - 48h</div>
                        <div class="time-opt" onclick="setTime('standard', this)"><i class="fa-solid fa-box"></i> 4-7 Días</div>
                        <div class="time-opt" onclick="setTime('any', this)"><i class="fa-solid fa-infinity"></i> Sin Prisa</div>
                    </div>
                </div>
                <button class="btn-next" onclick="goToStep3()">CONTINUAR ➔</button>
            </div>

            <div class="step-panel" id="step-3">
                <h1>Vibe</h1>
                <p class="sub">¿Qué le mueve por dentro?</p>
                <div class="os-grid" style="grid-template-columns: repeat(4, 1fr);">
                    <?php foreach($vibes as $v): ?>
                    <div class="os-card" onclick="finish('<?php echo $v['slug']; ?>')">
                        <i class="fa-solid <?php echo $v['icon']; ?>"></i><span style="font-size:12px;"><?php echo $v['name']; ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="step-panel" id="step-load">
                <i class="fa-solid fa-circle-notch fa-spin" style="font-size:50px; color:#818cf8; margin-bottom:20px;"></i>
                <h2>Triangulando opciones...</h2>
            </div>
        </div>
    </div>

    <div id="feed-overlay"></div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const app = document.getElementById('giftia-os');
            const feed = document.getElementById('feed-overlay');
            if(app) { document.body.appendChild(app); document.body.classList.add('gf-app-active'); }
            if(feed) document.body.appendChild(feed);
            
            const p = new URLSearchParams(window.location.search);
            if(p.get('mode') === 'feed') {
                if(app) app.style.display = 'none';
                if(feed) feed.style.display = 'block';
            }
            slideMin(); slideMax();
        });

        let state = { rec: '', minP: 10, maxP: 150, time: '', vibe: '', step: 1 };
        function slideMin() {
            let minRange = document.getElementById("range-min"); let maxRange = document.getElementById("range-max");
            let minVal = parseInt(minRange.value); let maxVal = parseInt(maxRange.value);
            if(maxVal - minVal < 10) { minRange.value = maxVal - 10; minVal = minRange.value; }
            state.minP = minVal; document.getElementById("lbl-min").innerText = minVal + "€"; updateFill();
        }
        function slideMax() {
            let minRange = document.getElementById("range-min"); let maxRange = document.getElementById("range-max");
            let minVal = parseInt(minRange.value); let maxVal = parseInt(maxRange.value);
            if(maxVal - minVal < 10) { maxRange.value = minVal + 10; maxVal = maxRange.value; }
            state.maxP = maxVal; document.getElementById("lbl-max").innerText = (maxVal >= 300 ? "+300€" : maxVal + "€"); updateFill();
        }
        function updateFill() {
            let minVal = document.getElementById("range-min").value; let maxVal = document.getElementById("range-max").value;
            let range = 300; let fill = document.getElementById("fill-bar");
            fill.style.left = (minVal / range) * 100 + "%"; fill.style.width = ((maxVal - minVal) / range) * 100 + "%";
        }
        function setRec(val) { state.rec = val; move(1); }
        function setTime(val, el) {
            state.time = val;
            document.querySelectorAll('.time-opt').forEach(e => e.classList.remove('selected'));
            el.classList.add('selected');
        }
        function goToStep3() { if(!state.time) { alert("Elige cuándo lo necesitas."); return; } move(1); }
        function finish(val) {
            state.vibe = val; move(1);
            setTimeout(() => {
                const q = `?rec=${state.rec}&min=${state.minP}&max=${state.maxP}&time=${state.time}&vibe=${state.vibe}&mode=feed`;
                window.location.search = q;
            }, 1000);
        }
        function move(dir) {
            let curr = document.getElementById('step-' + state.step); curr.classList.remove('active');
            if(dir > 0) curr.classList.add('prev');
            state.step += dir;
            let nextId = state.step === 4 ? 'step-load' : 'step-' + state.step;
            let next = document.getElementById(nextId);
            setTimeout(() => { next.classList.remove('prev'); next.classList.add('active'); }, 100);
            let btn = document.getElementById('back-btn');
            if(state.step > 1 && state.step < 4) btn.classList.add('show'); else btn.classList.remove('show');
        }
        function goBack() { move(-1); }
    </script>

    <?php
    // --- LÓGICA PHP: CASCADA DE RESCATE (Sincronizada con ingestor) ---
    if(isset($_GET['mode']) && $_GET['mode'] == 'feed') {
        
        $rec_slug = sanitize_text_field($_GET['rec']);
        $time_mode = sanitize_text_field($_GET['time']);
        $vibe_slug = sanitize_text_field($_GET['vibe']);
        $min_price = (int)$_GET['min'];
        $max_price = (int)$_GET['max'];
        if($max_price >= 300) $max_price = 99999;

        // MAPEO DE SLUGS A TAGS (¡Esto debe coincidir con api-ingest!)
        $tags = [$vibe_slug];
        if($vibe_slug == 'tech') $tags = ['Tech']; // En ingestor se guarda como 'Tech'
        if($vibe_slug == 'cocina') $tags = ['Gourmet']; // En ingestor 'Gourmet'
        if($vibe_slug == 'friki') $tags = ['Friki']; 
        if($vibe_slug == 'fit') $tags = ['Deporte']; 
        if($vibe_slug == 'zen') $tags = ['Zen']; 
        if($vibe_slug == 'viajero') $tags = ['Viajes'];
        
        // --- FUNCIÓN DE BÚSQUEDA ---
        function gf_search($args_override = []) {
            $base_args = [ 'post_type' => 'gf_gift', 'posts_per_page' => 20, 'post_status' => ['publish', 'draft'] ];
            $args = array_merge($base_args, $args_override);
            return new WP_Query($args);
        }

        // CASCADA DE INTENTOS
        $q = null; $fallback_msg = "";
        
        // NIVEL 1: EXACTO
        $q = gf_search([
            'tax_query' => [ 'relation' => 'AND',
                ['taxonomy'=>'gf_interest','field'=>'name','terms'=>$tags,'operator'=>'IN'], // Usamos 'name' para coincidir con el tag exacto
                ['taxonomy'=>'gf_recipient','field'=>'slug','terms'=>$rec_slug]
            ]
        ]);

        // NIVEL 2: SOLO VIBE
        if(!$q->have_posts()) {
            $q = gf_search([ 'tax_query' => [['taxonomy'=>'gf_interest','field'=>'name','terms'=>$tags,'operator'=>'IN']] ]);
            $fallback_msg = "Sugerencias por Interés";
        }

        // NIVEL 3: SOLO RECIPIENT
        if(!$q->have_posts()) {
            $q = gf_search([ 'tax_query' => [['taxonomy'=>'gf_recipient','field'=>'slug','terms'=>$rec_slug]] ]);
            $fallback_msg = "Populares para " . ucfirst($rec_slug);
        }

        // NIVEL 4: TODO (Si todo falla)
        if(!$q->have_posts()) {
            $q = gf_search(['orderby' => 'date', 'order' => 'DESC']);
            $fallback_msg = "Novedades Destacadas";
        }

        $fast_vendors = ['amazon', 'corte', 'media', 'fnac', 'pc', 'carrefour', 'pccomponentes', 'game'];

        echo '<div id="php-feed" style="display:none;">';
        $count_shown = 0;
        while($q->have_posts()){
            $q->the_post();
            global $wpdb;
            $t = $wpdb->prefix . 'gf_affiliate_offers';
            $offers = $wpdb->get_results($wpdb->prepare("SELECT * FROM $t WHERE post_id=%d ORDER BY price ASC", get_the_ID()));
            
            if(empty($offers)) continue;
            
            // Lógica Vendor
            $chosen = null;
            if($time_mode == 'immed') { $chosen = $offers[0]; } 
            elseif($time_mode == 'fast') {
                foreach($offers as $o) {
                    $v = strtolower($o->vendor_name);
                    foreach($fast_vendors as $fv) { if(strpos($v, $fv) !== false) { $chosen = $o; break 2; } }
                }
                if(!$chosen && $fallback_msg != "") $chosen = $offers[0];
            } else { $chosen = $offers[0]; }
            if(!$chosen) continue;

            if($fallback_msg == "" && ($chosen->price < $min_price || $chosen->price > $max_price)) continue;

            $count_shown++;
            $img = get_the_post_thumbnail_url(get_the_ID(),'full');
            if(!$img) $img = 'https://source.unsplash.com/random/800x1200/?gift';
            
            $badge = '';
            if($time_mode=='immed') $badge='⚡ DIGITAL';
            elseif($time_mode=='fast') $badge='🚀 24H - 48H';
            ?>
            <div class="feed-item">
                <img src="<?php echo esc_url($img); ?>" class="feed-bg">
                <div class="feed-info">
                    <?php if($fallback_msg): ?><div class="fallback-notice"><i class="fa-solid fa-lightbulb"></i> <?php echo esc_html($fallback_msg); ?></div><?php endif; ?>
                    <?php if($badge): ?><span style="background:#8b5cf6; padding:5px 10px; border-radius:5px; font-weight:bold; font-size:12px; margin-bottom:10px; display:inline-block;"><?php echo esc_html($badge); ?></span><?php endif; ?>
                    <div class="feed-title"><?php the_title(); ?></div>
                    <div class="feed-price"><?php echo esc_html(number_format($chosen->price,2)); ?>€</div>
                    <div style="font-size:12px; color:#cbd5e1; margin-bottom:20px;">Vendido por <?php echo esc_html(ucfirst($chosen->vendor_name)); ?></div>
                    <a href="<?php echo esc_url($chosen->affiliate_url); ?>" target="_blank" rel="noopener noreferrer" class="feed-cta">COMPRAR AHORA</a>
                </div>
            </div>
            <?php
        }
        
        if($count_shown == 0) {
            echo '<div class="feed-item" style="background:#0f172a;"><div style="color:white;text-align:center;"><h2>🔎</h2><p>No tengo stock con ese filtro exacto.</p><a href="?" style="color:#818cf8; margin-top:20px; display:block;">Reiniciar</a></div></div>';
        }
        echo '</div>';
        echo "<script>document.getElementById('feed-overlay').innerHTML = document.getElementById('php-feed').innerHTML;</script>";
    }
    return ob_get_clean();
}