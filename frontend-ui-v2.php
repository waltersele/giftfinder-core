<?php
/**
 * GIFTIA FRONTEND UI v2.0
 * 5 Preguntas para el regalo perfecto:
 * 1. ¿Para quién? (destinatario)
 * 2. ¿Qué edad tiene? (rango de edad)
 * 3. ¿Cuál es tu presupuesto? (slider de precio)
 * 4. ¿Qué le interesa? (múltiples intereses)
 * 5. ¿Cuál es la ocasión? (evento)
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_shortcode( 'giftia_app', 'gf_render_gift_finder' );

function gf_render_gift_finder( $atts ) {
    
    // ========================================================================
    // PASO 1: ¿Para quién es el regalo?
    // ========================================================================
    $destinatarios = [
        ['slug'=>'pareja',    'name'=>'Mi pareja',     'icon'=>'fa-heart',          'emoji'=>'💕'],
        ['slug'=>'padre',     'name'=>'Padre/Madre',   'icon'=>'fa-house-user',     'emoji'=>'👨‍👩‍👧'],
        ['slug'=>'amigo',     'name'=>'Amig@',         'icon'=>'fa-user-group',     'emoji'=>'🤝'],
        ['slug'=>'hermano',   'name'=>'Herman@',       'icon'=>'fa-people-arrows',  'emoji'=>'👫'],
        ['slug'=>'abuelo',    'name'=>'Abuel@',        'icon'=>'fa-person-cane',    'emoji'=>'👴'],
        ['slug'=>'jefe',      'name'=>'Jefe/Colega',   'icon'=>'fa-briefcase',      'emoji'=>'💼'],
        ['slug'=>'yo',        'name'=>'Para mí',       'icon'=>'fa-face-grin-stars','emoji'=>'🎁'],
    ];

    // ========================================================================
    // PASO 2: ¿Qué edad tiene?
    // ========================================================================
    $edades = [
        ['slug'=>'nino',      'name'=>'Niñ@ (0-12)',      'icon'=>'fa-child',       'range'=>'0-12'],
        ['slug'=>'teen',      'name'=>'Teen (13-17)',     'icon'=>'fa-graduation-cap','range'=>'13-17'],
        ['slug'=>'joven',     'name'=>'Joven (18-30)',    'icon'=>'fa-user',        'range'=>'18-30'],
        ['slug'=>'adulto',    'name'=>'Adulto (31-50)',   'icon'=>'fa-user-tie',    'range'=>'31-50'],
        ['slug'=>'senior',    'name'=>'Senior (51-70)',   'icon'=>'fa-user-clock',  'range'=>'51-70'],
        ['slug'=>'mayor',     'name'=>'Mayor (70+)',      'icon'=>'fa-person-cane', 'range'=>'70+'],
    ];

    // ========================================================================
    // PASO 4: ¿Qué le interesa? (puede elegir varios)
    // ========================================================================
    $intereses = [
        ['slug'=>'tech',      'name'=>'Tecnología',    'icon'=>'fa-microchip',       'keywords'=>'tech,gaming,gadget'],
        ['slug'=>'gaming',    'name'=>'Videojuegos',   'icon'=>'fa-gamepad',         'keywords'=>'gaming,consola,gamer'],
        ['slug'=>'cocina',    'name'=>'Cocina',        'icon'=>'fa-utensils',        'keywords'=>'gourmet,cocina,chef'],
        ['slug'=>'vino',      'name'=>'Vinos/Licores', 'icon'=>'fa-wine-glass',      'keywords'=>'vino,whisky,gin'],
        ['slug'=>'deporte',   'name'=>'Deporte',       'icon'=>'fa-dumbbell',        'keywords'=>'fitness,running,gym'],
        ['slug'=>'outdoor',   'name'=>'Aire libre',    'icon'=>'fa-mountain-sun',    'keywords'=>'camping,viajes,aventura'],
        ['slug'=>'moda',      'name'=>'Moda',          'icon'=>'fa-shirt',           'keywords'=>'ropa,bolso,zapatos'],
        ['slug'=>'belleza',   'name'=>'Belleza',       'icon'=>'fa-spa',             'keywords'=>'skincare,maquillaje,perfume'],
        ['slug'=>'hogar',     'name'=>'Hogar/Deco',    'icon'=>'fa-couch',           'keywords'=>'decoracion,casa,jardin'],
        ['slug'=>'libros',    'name'=>'Lectura',       'icon'=>'fa-book',            'keywords'=>'libro,kindle,literatura'],
        ['slug'=>'musica',    'name'=>'Música',        'icon'=>'fa-music',           'keywords'=>'auriculares,vinilo,instrumento'],
        ['slug'=>'arte',      'name'=>'Arte/Craft',    'icon'=>'fa-palette',         'keywords'=>'pintura,manualidades,diy'],
        ['slug'=>'fandom',    'name'=>'Fandom/Friki',  'icon'=>'fa-jedi',            'keywords'=>'marvel,starwars,anime,lego'],
        ['slug'=>'mascotas',  'name'=>'Mascotas',      'icon'=>'fa-paw',             'keywords'=>'perro,gato,mascota'],
        ['slug'=>'bienestar', 'name'=>'Bienestar',     'icon'=>'fa-heart-pulse',     'keywords'=>'yoga,meditacion,relax'],
        ['slug'=>'foto',      'name'=>'Fotografía',    'icon'=>'fa-camera',          'keywords'=>'camara,foto,instax'],
    ];

    // ========================================================================
    // PASO 5: ¿Cuál es la ocasión?
    // ========================================================================
    $ocasiones = [
        ['slug'=>'cumple',     'name'=>'Cumpleaños',       'icon'=>'fa-cake-candles',   'emoji'=>'🎂'],
        ['slug'=>'navidad',    'name'=>'Navidad',          'icon'=>'fa-tree',           'emoji'=>'🎄'],
        ['slug'=>'sanvalentin','name'=>'San Valentín',     'icon'=>'fa-heart',          'emoji'=>'💝'],
        ['slug'=>'aniversario','name'=>'Aniversario',      'icon'=>'fa-ring',           'emoji'=>'💍'],
        ['slug'=>'diaMadre',   'name'=>'Día Madre/Padre',  'icon'=>'fa-hand-holding-heart','emoji'=>'💐'],
        ['slug'=>'graduacion', 'name'=>'Graduación',       'icon'=>'fa-graduation-cap', 'emoji'=>'🎓'],
        ['slug'=>'boda',       'name'=>'Boda',             'icon'=>'fa-champagne-glasses','emoji'=>'🥂'],
        ['slug'=>'gracias',    'name'=>'Agradecimiento',   'icon'=>'fa-hands-clapping', 'emoji'=>'🙏'],
        ['slug'=>'random',     'name'=>'Sin motivo',       'icon'=>'fa-gift',           'emoji'=>'🎁'],
    ];

    ob_start();
    ?>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;500;700;900&display=swap" rel="stylesheet">
    
    <style>
        * { box-sizing: border-box; }
        body.gf-active { overflow: hidden !important; }
        
        #giftia-app {
            position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #0f172a 100%);
            color: white; z-index: 2147483647; font-family: 'Outfit', sans-serif;
            display: flex; flex-direction: column; overflow: hidden;
        }
        
        /* HEADER */
        .gf-header {
            padding: 15px 20px; display: flex; justify-content: space-between; align-items: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .gf-logo { font-weight: 900; font-size: 18px; letter-spacing: 2px; }
        .gf-progress { display: flex; gap: 6px; }
        .gf-dot { width: 10px; height: 10px; border-radius: 50%; background: rgba(255,255,255,0.2); transition: 0.3s; }
        .gf-dot.active { background: #818cf8; transform: scale(1.2); }
        .gf-dot.done { background: #22c55e; }
        .btn-back { background: rgba(255,255,255,0.1); border: none; color: white; width: 36px; height: 36px; border-radius: 50%; cursor: pointer; display: none; }
        .btn-back.show { display: flex; align-items: center; justify-content: center; }
        
        /* MAIN CONTENT */
        .gf-main { flex: 1; overflow-y: auto; padding: 30px 20px; display: flex; flex-direction: column; align-items: center; }
        
        /* STEP CONTAINER */
        .gf-step { display: none; width: 100%; max-width: 700px; animation: fadeIn 0.4s ease; }
        .gf-step.active { display: block; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        
        /* TITLES */
        .gf-step h2 { font-size: 28px; font-weight: 800; margin: 0 0 8px 0; text-align: center; }
        .gf-step p.sub { color: #94a3b8; font-size: 15px; text-align: center; margin-bottom: 30px; }
        
        /* GRID DE OPCIONES */
        .gf-options { display: grid; gap: 12px; }
        .gf-options.cols-3 { grid-template-columns: repeat(3, 1fr); }
        .gf-options.cols-4 { grid-template-columns: repeat(4, 1fr); }
        .gf-options.cols-2 { grid-template-columns: repeat(2, 1fr); }
        
        /* CARDS */
        .gf-card {
            background: rgba(255,255,255,0.05); border: 2px solid rgba(255,255,255,0.1);
            border-radius: 16px; padding: 20px 15px; cursor: pointer; transition: all 0.2s;
            display: flex; flex-direction: column; align-items: center; text-align: center;
        }
        .gf-card:hover { background: rgba(99, 102, 241, 0.15); border-color: rgba(129, 140, 248, 0.5); transform: translateY(-3px); }
        .gf-card.selected { background: rgba(99, 102, 241, 0.3); border-color: #818cf8; box-shadow: 0 0 20px rgba(99, 102, 241, 0.3); }
        .gf-card i { font-size: 28px; margin-bottom: 10px; color: #a5b4fc; }
        .gf-card span { font-size: 13px; font-weight: 600; }
        .gf-card .emoji { font-size: 32px; margin-bottom: 8px; }
        
        /* MULTI-SELECT (intereses) */
        .gf-card.multi { padding: 15px 10px; }
        .gf-card.multi i { font-size: 22px; margin-bottom: 6px; }
        .gf-card.multi span { font-size: 11px; }
        .gf-card.multi.selected::after { content: '✓'; position: absolute; top: 8px; right: 8px; background: #22c55e; color: white; width: 20px; height: 20px; border-radius: 50%; font-size: 12px; display: flex; align-items: center; justify-content: center; }
        .gf-card.multi { position: relative; }
        
        /* ====== SLIDER DE PRECIO - CORREGIDO ====== */
        .gf-price-box {
            background: rgba(255,255,255,0.05); border-radius: 20px; padding: 30px;
            margin-bottom: 30px;
        }
        .gf-price-display {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 20px;
        }
        .gf-price-value {
            font-size: 32px; font-weight: 900; color: #fbbf24;
        }
        .gf-price-label { font-size: 12px; color: #94a3b8; }
        
        /* SLIDER CONTAINER */
        .gf-slider-container {
            position: relative; height: 50px; width: 100%;
        }
        .gf-slider-track {
            position: absolute; top: 50%; left: 0; right: 0; height: 8px;
            background: #334155; border-radius: 4px; transform: translateY(-50%);
        }
        .gf-slider-fill {
            position: absolute; top: 50%; height: 8px; border-radius: 4px;
            background: linear-gradient(90deg, #6366f1, #a855f7);
            transform: translateY(-50%); pointer-events: none;
        }
        /* INPUTS - Clave: pointer-events en el thumb */
        .gf-slider-input {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            -webkit-appearance: none; appearance: none;
            background: transparent; pointer-events: none; margin: 0;
        }
        .gf-slider-input::-webkit-slider-thumb {
            -webkit-appearance: none; appearance: none;
            width: 32px; height: 32px; border-radius: 50%;
            background: white; cursor: grab;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            border: 4px solid #818cf8;
            pointer-events: auto; /* Solo el thumb es interactivo */
            position: relative; z-index: 10;
            margin-top: -12px;
        }
        .gf-slider-input::-webkit-slider-thumb:active { cursor: grabbing; }
        .gf-slider-input::-moz-range-thumb {
            width: 28px; height: 28px; border-radius: 50%;
            background: white; cursor: grab;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            border: 4px solid #818cf8;
            pointer-events: auto;
        }
        .gf-slider-input::-webkit-slider-runnable-track { height: 8px; background: transparent; }
        .gf-slider-input::-moz-range-track { height: 8px; background: transparent; }
        #slider-min { z-index: 3; }
        #slider-max { z-index: 4; }
        
        /* PRESETS DE PRECIO */
        .gf-price-presets { display: flex; gap: 10px; margin-top: 20px; flex-wrap: wrap; justify-content: center; }
        .gf-preset {
            background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2);
            padding: 8px 16px; border-radius: 20px; cursor: pointer; font-size: 13px;
            transition: 0.2s;
        }
        .gf-preset:hover { background: #818cf8; border-color: #818cf8; }
        
        /* BOTÓN SIGUIENTE */
        .gf-btn-next {
            width: 100%; max-width: 400px; margin: 30px auto 0; padding: 18px;
            background: linear-gradient(90deg, #6366f1, #a855f7);
            color: white; border: none; border-radius: 50px;
            font-size: 16px; font-weight: 700; cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            display: block;
        }
        .gf-btn-next:hover { transform: scale(1.02); box-shadow: 0 10px 30px rgba(99,102,241,0.4); }
        .gf-btn-next:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }
        
        /* CONTADOR INTERESES */
        .gf-counter { text-align: center; margin-top: 15px; color: #94a3b8; font-size: 14px; }
        .gf-counter strong { color: #818cf8; }
        
        /* FEED RESULTADOS */
        #gf-feed {
            position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;
            background: #000; z-index: 2147483650; overflow-y: scroll;
            scroll-snap-type: y mandatory; display: none;
        }
        .feed-item {
            width: 100%; height: 100vh; scroll-snap-align: start;
            position: relative; display: flex; justify-content: center; align-items: center;
        }
        .feed-bg { position: absolute; width: 100%; height: 100%; object-fit: cover; opacity: 0.6; }
        .feed-gradient { position: absolute; bottom: 0; left: 0; right: 0; height: 70%; background: linear-gradient(transparent, rgba(0,0,0,0.9)); }
        .feed-content {
            position: relative; z-index: 5; width: 90%; max-width: 500px;
            margin-top: auto; padding-bottom: 60px; color: white;
        }
        .feed-tags { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 15px; }
        .feed-tag { background: rgba(255,255,255,0.2); padding: 5px 12px; border-radius: 20px; font-size: 11px; backdrop-filter: blur(5px); }
        .feed-price { font-size: 48px; font-weight: 900; color: #fbbf24; text-shadow: 0 2px 20px rgba(0,0,0,0.5); }
        .feed-title { font-size: 22px; font-weight: 700; margin: 10px 0 20px; line-height: 1.3; }
        .feed-cta {
            display: inline-block; background: white; color: black;
            padding: 16px 40px; border-radius: 50px; font-weight: 800;
            text-decoration: none; transition: transform 0.2s;
        }
        .feed-cta:hover { transform: scale(1.05); }
        .feed-vendor { margin-top: 15px; font-size: 12px; color: #94a3b8; }
        
        /* RESPONSIVE */
        @media (max-width: 600px) {
            .gf-options.cols-3 { grid-template-columns: repeat(2, 1fr); }
            .gf-options.cols-4 { grid-template-columns: repeat(3, 1fr); }
            .gf-card { padding: 15px 10px; }
            .gf-card i { font-size: 22px; }
            .gf-step h2 { font-size: 24px; }
            .gf-price-value { font-size: 26px; }
        }
    </style>

    <div id="giftia-app">
        <!-- HEADER -->
        <div class="gf-header">
            <button class="btn-back" id="btnBack" onclick="gfBack()"><i class="fa-solid fa-arrow-left"></i></button>
            <div class="gf-logo">GIFTIA</div>
            <div class="gf-progress">
                <div class="gf-dot active" id="dot-1"></div>
                <div class="gf-dot" id="dot-2"></div>
                <div class="gf-dot" id="dot-3"></div>
                <div class="gf-dot" id="dot-4"></div>
                <div class="gf-dot" id="dot-5"></div>
            </div>
        </div>

        <div class="gf-main">
            <!-- ============ PASO 1: ¿Para quién? ============ -->
            <div class="gf-step active" id="step-1">
                <h2>¿Para quién es el regalo?</h2>
                <p class="sub">Selecciona el perfil del destinatario</p>
                <div class="gf-options cols-3">
                    <?php foreach($destinatarios as $d): ?>
                    <div class="gf-card" onclick="gfSelect('destinatario', '<?php echo $d['slug']; ?>', this)">
                        <span class="emoji"><?php echo $d['emoji']; ?></span>
                        <span><?php echo $d['name']; ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- ============ PASO 2: ¿Qué edad? ============ -->
            <div class="gf-step" id="step-2">
                <h2>¿Qué edad tiene?</h2>
                <p class="sub">Nos ayuda a afinar las sugerencias</p>
                <div class="gf-options cols-3">
                    <?php foreach($edades as $e): ?>
                    <div class="gf-card" onclick="gfSelect('edad', '<?php echo $e['slug']; ?>', this)">
                        <i class="fa-solid <?php echo $e['icon']; ?>"></i>
                        <span><?php echo $e['name']; ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- ============ PASO 3: Presupuesto ============ -->
            <div class="gf-step" id="step-3">
                <h2>¿Cuál es tu presupuesto?</h2>
                <p class="sub">Arrastra los controles para ajustar el rango</p>
                
                <div class="gf-price-box">
                    <div class="gf-price-display">
                        <div>
                            <div class="gf-price-label">DESDE</div>
                            <div class="gf-price-value" id="price-min-label">20€</div>
                        </div>
                        <div style="color:#64748b;">—</div>
                        <div style="text-align:right;">
                            <div class="gf-price-label">HASTA</div>
                            <div class="gf-price-value" id="price-max-label">100€</div>
                        </div>
                    </div>
                    
                    <div class="gf-slider-container">
                        <div class="gf-slider-track"></div>
                        <div class="gf-slider-fill" id="slider-fill"></div>
                        <input type="range" class="gf-slider-input" id="slider-min" min="0" max="500" value="20" step="5">
                        <input type="range" class="gf-slider-input" id="slider-max" min="0" max="500" value="100" step="5">
                    </div>
                    
                    <div class="gf-price-presets">
                        <span class="gf-preset" onclick="gfSetPrice(0, 30)">Hasta 30€</span>
                        <span class="gf-preset" onclick="gfSetPrice(30, 75)">30€ - 75€</span>
                        <span class="gf-preset" onclick="gfSetPrice(75, 150)">75€ - 150€</span>
                        <span class="gf-preset" onclick="gfSetPrice(150, 500)">+150€ Premium</span>
                    </div>
                </div>
                
                <button class="gf-btn-next" onclick="gfNextStep()">CONTINUAR →</button>
            </div>

            <!-- ============ PASO 4: Intereses (Multi-select) ============ -->
            <div class="gf-step" id="step-4">
                <h2>¿Qué le interesa?</h2>
                <p class="sub">Puedes seleccionar varios (mínimo 1)</p>
                <div class="gf-options cols-4">
                    <?php foreach($intereses as $i): ?>
                    <div class="gf-card multi" onclick="gfToggleInterest('<?php echo $i['slug']; ?>', this)" data-keywords="<?php echo $i['keywords']; ?>">
                        <i class="fa-solid <?php echo $i['icon']; ?>"></i>
                        <span><?php echo $i['name']; ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="gf-counter"><strong id="interest-count">0</strong> intereses seleccionados</div>
                <button class="gf-btn-next" id="btn-interests" onclick="gfNextStep()" disabled>CONTINUAR →</button>
            </div>

            <!-- ============ PASO 5: Ocasión ============ -->
            <div class="gf-step" id="step-5">
                <h2>¿Cuál es la ocasión?</h2>
                <p class="sub">El contexto perfecto para el regalo perfecto</p>
                <div class="gf-options cols-3">
                    <?php foreach($ocasiones as $o): ?>
                    <div class="gf-card" onclick="gfFinish('<?php echo $o['slug']; ?>')">
                        <span class="emoji"><?php echo $o['emoji']; ?></span>
                        <span><?php echo $o['name']; ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- ============ LOADING ============ -->
            <div class="gf-step" id="step-loading">
                <div style="text-align:center; padding: 60px 0;">
                    <i class="fa-solid fa-wand-magic-sparkles fa-spin" style="font-size:60px; color:#a855f7; margin-bottom:30px;"></i>
                    <h2>Buscando el regalo perfecto...</h2>
                    <p class="sub">Analizando miles de opciones para ti</p>
                </div>
            </div>
        </div>
    </div>

    <!-- FEED DE RESULTADOS -->
    <div id="gf-feed"></div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Mover al body para z-index máximo
        const app = document.getElementById('giftia-app');
        const feed = document.getElementById('gf-feed');
        if(app) { document.body.appendChild(app); document.body.classList.add('gf-active'); }
        if(feed) document.body.appendChild(feed);
        
        // Inicializar sliders
        gfInitSliders();
        
        // Si hay parámetros de feed, mostrar resultados
        const params = new URLSearchParams(window.location.search);
        if(params.get('mode') === 'feed') {
            if(app) app.style.display = 'none';
            if(feed) feed.style.display = 'block';
        }
    });

    // ========== ESTADO GLOBAL ==========
    const gfState = {
        step: 1,
        destinatario: '',
        edad: '',
        priceMin: 20,
        priceMax: 100,
        intereses: [],
        ocasion: ''
    };

    // ========== NAVEGACIÓN ==========
    function gfGoToStep(n) {
        // Ocultar actual
        document.getElementById('step-' + gfState.step).classList.remove('active');
        // Mostrar nuevo
        gfState.step = n;
        const nextStep = document.getElementById('step-' + n) || document.getElementById('step-loading');
        nextStep.classList.add('active');
        
        // Actualizar dots
        for(let i = 1; i <= 5; i++) {
            const dot = document.getElementById('dot-' + i);
            dot.classList.remove('active', 'done');
            if(i < n) dot.classList.add('done');
            else if(i === n) dot.classList.add('active');
        }
        
        // Mostrar/ocultar botón back
        document.getElementById('btnBack').classList.toggle('show', n > 1 && n <= 5);
    }

    function gfNextStep() {
        gfGoToStep(gfState.step + 1);
    }

    function gfBack() {
        if(gfState.step > 1) gfGoToStep(gfState.step - 1);
    }

    // ========== SELECCIÓN SIMPLE ==========
    function gfSelect(field, value, el) {
        gfState[field] = value;
        // Visual feedback
        el.parentElement.querySelectorAll('.gf-card').forEach(c => c.classList.remove('selected'));
        el.classList.add('selected');
        // Auto-avanzar después de 300ms
        setTimeout(() => gfNextStep(), 300);
    }

    // ========== SLIDER DE PRECIO ==========
    function gfInitSliders() {
        const minSlider = document.getElementById('slider-min');
        const maxSlider = document.getElementById('slider-max');
        
        if(!minSlider || !maxSlider) return;
        
        minSlider.addEventListener('input', gfUpdatePrice);
        maxSlider.addEventListener('input', gfUpdatePrice);
        
        // Inicializar visual
        gfUpdatePrice();
    }

    function gfUpdatePrice() {
        const minSlider = document.getElementById('slider-min');
        const maxSlider = document.getElementById('slider-max');
        
        let minVal = parseInt(minSlider.value);
        let maxVal = parseInt(maxSlider.value);
        
        // Evitar cruce
        if(minVal >= maxVal - 10) {
            if(this && this.id === 'slider-min') {
                minVal = maxVal - 10;
                minSlider.value = minVal;
            } else {
                maxVal = minVal + 10;
                maxSlider.value = maxVal;
            }
        }
        
        gfState.priceMin = minVal;
        gfState.priceMax = maxVal;
        
        // Actualizar labels
        document.getElementById('price-min-label').textContent = minVal + '€';
        document.getElementById('price-max-label').textContent = maxVal >= 500 ? '+500€' : maxVal + '€';
        
        // Actualizar barra de relleno
        const fill = document.getElementById('slider-fill');
        const percent1 = (minVal / 500) * 100;
        const percent2 = (maxVal / 500) * 100;
        fill.style.left = percent1 + '%';
        fill.style.width = (percent2 - percent1) + '%';
    }

    function gfSetPrice(min, max) {
        document.getElementById('slider-min').value = min;
        document.getElementById('slider-max').value = max;
        gfUpdatePrice();
    }

    // ========== MULTI-SELECT INTERESES ==========
    function gfToggleInterest(slug, el) {
        const idx = gfState.intereses.indexOf(slug);
        if(idx > -1) {
            gfState.intereses.splice(idx, 1);
            el.classList.remove('selected');
        } else {
            gfState.intereses.push(slug);
            el.classList.add('selected');
        }
        
        // Actualizar contador
        document.getElementById('interest-count').textContent = gfState.intereses.length;
        
        // Habilitar/deshabilitar botón
        document.getElementById('btn-interests').disabled = gfState.intereses.length === 0;
    }

    // ========== FINALIZAR ==========
    function gfFinish(ocasion) {
        gfState.ocasion = ocasion;
        gfGoToStep(6); // Loading
        
        // Construir URL con todos los parámetros
        setTimeout(() => {
            const params = new URLSearchParams({
                mode: 'feed',
                dest: gfState.destinatario,
                edad: gfState.edad,
                min: gfState.priceMin,
                max: gfState.priceMax,
                intereses: gfState.intereses.join(','),
                ocasion: gfState.ocasion
            });
            window.location.search = params.toString();
        }, 1500);
    }
    </script>

    <?php
    // ========================================================================
    // LÓGICA PHP - BÚSQUEDA Y RESULTADOS
    // ========================================================================
    if(isset($_GET['mode']) && $_GET['mode'] == 'feed') {
        
        $dest = sanitize_text_field($_GET['dest'] ?? '');
        $edad = sanitize_text_field($_GET['edad'] ?? '');
        $min_price = max(0, (int)($_GET['min'] ?? 0));
        $max_price = min(9999, (int)($_GET['max'] ?? 500));
        $intereses_raw = sanitize_text_field($_GET['intereses'] ?? '');
        $intereses = array_filter(explode(',', $intereses_raw));
        $ocasion = sanitize_text_field($_GET['ocasion'] ?? '');
        
        // Convertir intereses a tags de BD
        $interest_to_tag = [
            'tech' => 'Tech', 'gaming' => 'Tech', 'cocina' => 'Gourmet', 'vino' => 'Gourmet',
            'deporte' => 'Deporte', 'outdoor' => 'Viajes', 'moda' => 'Moda', 'belleza' => 'Zen',
            'hogar' => 'Moda', 'libros' => 'Friki', 'musica' => 'Tech', 'arte' => 'Friki',
            'fandom' => 'Friki', 'mascotas' => 'Zen', 'bienestar' => 'Zen', 'foto' => 'Tech'
        ];
        
        $tags = [];
        foreach($intereses as $i) {
            if(isset($interest_to_tag[$i])) {
                $tags[] = $interest_to_tag[$i];
            }
        }
        $tags = array_unique($tags);
        if(empty($tags)) $tags = ['Friki', 'Tech', 'Gourmet'];
        
        global $wpdb;
        $table_offers = $wpdb->prefix . 'gf_affiliate_offers';
        
        // Obtener productos en rango de precio
        $price_ids = $wpdb->get_col($wpdb->prepare(
            "SELECT DISTINCT post_id FROM $table_offers WHERE price >= %f AND price <= %f AND is_active = 1",
            $min_price, $max_price
        ));
        
        // Si no hay productos, ampliar búsqueda
        if(empty($price_ids)) {
            $price_ids = $wpdb->get_col("SELECT DISTINCT post_id FROM $table_offers WHERE is_active = 1 LIMIT 100");
        }
        
        // Query principal
        $args = [
            'post_type' => 'gf_gift',
            'posts_per_page' => 30,
            'post_status' => 'publish',
            'post__in' => $price_ids,
            'orderby' => 'rand',
            'tax_query' => [
                ['taxonomy' => 'gf_interest', 'field' => 'name', 'terms' => $tags, 'operator' => 'IN']
            ]
        ];
        
        if(empty($price_ids)) unset($args['post__in']);
        
        $q = new WP_Query($args);
        
        // Si no hay resultados con tags, buscar sin filtro de taxonomía
        if(!$q->have_posts()) {
            unset($args['tax_query']);
            $q = new WP_Query($args);
        }
        
        echo '<div id="feed-data" style="display:none;">';
        $shown = 0;
        while($q->have_posts() && $shown < 20) {
            $q->the_post();
            $offers = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM $table_offers WHERE post_id = %d ORDER BY price ASC",
                get_the_ID()
            ));
            
            if(empty($offers)) continue;
            $offer = $offers[0];
            $shown++;
            
            $img = get_the_post_thumbnail_url(get_the_ID(), 'full');
            if(!$img) $img = 'https://images.unsplash.com/photo-1549465220-1a8b9238cd48?w=800';
            
            $terms = wp_get_post_terms(get_the_ID(), 'gf_interest', ['fields' => 'names']);
            ?>
            <div class="feed-item">
                <img src="<?php echo esc_url($img); ?>" class="feed-bg" loading="lazy">
                <div class="feed-gradient"></div>
                <div class="feed-content">
                    <div class="feed-tags">
                        <?php foreach(array_slice($terms, 0, 3) as $term): ?>
                        <span class="feed-tag"><?php echo esc_html($term); ?></span>
                        <?php endforeach; ?>
                    </div>
                    <div class="feed-price"><?php echo number_format($offer->price, 2); ?>€</div>
                    <div class="feed-title"><?php the_title(); ?></div>
                    <a href="<?php echo esc_url($offer->affiliate_url); ?>" target="_blank" rel="noopener" class="feed-cta">
                        VER REGALO →
                    </a>
                    <div class="feed-vendor">Vendido por <?php echo esc_html(ucfirst($offer->vendor_name)); ?></div>
                </div>
            </div>
            <?php
        }
        
        if($shown == 0) {
            ?>
            <div class="feed-item" style="background: linear-gradient(135deg, #1e1b4b, #0f172a);">
                <div class="feed-content" style="text-align:center; margin-top: 0;">
                    <div style="font-size:80px; margin-bottom:30px;">🔍</div>
                    <h2 style="font-size:24px; margin-bottom:15px;">No encontramos productos con esos filtros</h2>
                    <p style="color:#94a3b8; margin-bottom:30px;">Prueba ampliando el presupuesto o seleccionando otros intereses</p>
                    <a href="?" class="feed-cta">VOLVER A EMPEZAR</a>
                </div>
            </div>
            <?php
        }
        
        echo '</div>';
        echo '<script>document.getElementById("gf-feed").innerHTML = document.getElementById("feed-data").innerHTML;</script>';
    }
    
    return ob_get_clean();
}
