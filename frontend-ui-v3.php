<?php
/**
 * GIFTIA AI - Frontend v3
 * Sistema de perfilado inteligente con captura de leads
 * 
 * Características:
 * - Diseño minimalista AI-driven
 * - Efecto typewriter en textos
 * - Sin emojis, solo iconos
 * - Captura de email para remarketing
 */

if (!defined('ABSPATH')) exit;

add_shortcode('giftia_app', 'gf_render_ai_profiler_v3');

function gf_render_ai_profiler_v3($atts) {
    
    // Perfiles con restricciones
    $profiles = [
        ['slug'=>'bebe', 'name'=>'Bebé', 'icon'=>'fa-baby', 'min_age'=>0, 'max_age'=>2, 'allow_alcohol'=>false],
        ['slug'=>'nino', 'name'=>'Niño/a', 'icon'=>'fa-child-reaching', 'min_age'=>3, 'max_age'=>12, 'allow_alcohol'=>false],
        ['slug'=>'adolescente', 'name'=>'Adolescente', 'icon'=>'fa-user-graduate', 'min_age'=>13, 'max_age'=>17, 'allow_alcohol'=>false],
        ['slug'=>'joven', 'name'=>'Joven', 'icon'=>'fa-user', 'min_age'=>18, 'max_age'=>35, 'allow_alcohol'=>true],
        ['slug'=>'adulto', 'name'=>'Adulto', 'icon'=>'fa-user-tie', 'min_age'=>36, 'max_age'=>64, 'allow_alcohol'=>true],
        ['slug'=>'senior', 'name'=>'Senior', 'icon'=>'fa-person-cane', 'min_age'=>65, 'max_age'=>99, 'allow_alcohol'=>true],
        ['slug'=>'pareja', 'name'=>'Pareja', 'icon'=>'fa-heart', 'min_age'=>18, 'max_age'=>99, 'allow_alcohol'=>true, 'ask_age'=>true],
        ['slug'=>'amigo', 'name'=>'Amigo/a', 'icon'=>'fa-user-group', 'min_age'=>0, 'max_age'=>99, 'allow_alcohol'=>null, 'ask_age'=>true],
        ['slug'=>'familia', 'name'=>'Familiar', 'icon'=>'fa-house-chimney-heart', 'min_age'=>0, 'max_age'=>99, 'allow_alcohol'=>null, 'ask_age'=>true],
        ['slug'=>'colega', 'name'=>'Colega', 'icon'=>'fa-briefcase', 'min_age'=>18, 'max_age'=>99, 'allow_alcohol'=>true],
    ];

    // Rangos de edad
    $age_ranges = [
        ['slug'=>'baby', 'name'=>'0-2 años', 'icon'=>'fa-baby', 'min'=>0, 'max'=>2],
        ['slug'=>'child', 'name'=>'3-12 años', 'icon'=>'fa-child', 'min'=>3, 'max'=>12],
        ['slug'=>'teen', 'name'=>'13-17 años', 'icon'=>'fa-graduation-cap', 'min'=>13, 'max'=>17],
        ['slug'=>'young', 'name'=>'18-30 años', 'icon'=>'fa-user', 'min'=>18, 'max'=>30],
        ['slug'=>'adult', 'name'=>'31-55 años', 'icon'=>'fa-user-tie', 'min'=>31, 'max'=>55],
        ['slug'=>'senior', 'name'=>'55+ años', 'icon'=>'fa-person-cane', 'min'=>55, 'max'=>99],
    ];

    // Intereses/Vibes
    $vibes = [
        ['slug'=>'tech', 'name'=>'Tecnología', 'icon'=>'fa-microchip', 'min_age'=>8],
        ['slug'=>'gaming', 'name'=>'Gaming', 'icon'=>'fa-gamepad', 'min_age'=>6],
        ['slug'=>'sports', 'name'=>'Deporte', 'icon'=>'fa-dumbbell', 'min_age'=>5],
        ['slug'=>'fashion', 'name'=>'Moda', 'icon'=>'fa-shirt', 'min_age'=>13],
        ['slug'=>'home', 'name'=>'Hogar', 'icon'=>'fa-couch', 'min_age'=>18],
        ['slug'=>'gourmet', 'name'=>'Gourmet', 'icon'=>'fa-utensils', 'min_age'=>16],
        ['slug'=>'travel', 'name'=>'Viajes', 'icon'=>'fa-plane', 'min_age'=>16],
        ['slug'=>'wellness', 'name'=>'Bienestar', 'icon'=>'fa-spa', 'min_age'=>13],
        ['slug'=>'books', 'name'=>'Lectura', 'icon'=>'fa-book', 'min_age'=>6],
        ['slug'=>'music', 'name'=>'Música', 'icon'=>'fa-headphones', 'min_age'=>5],
        ['slug'=>'toys', 'name'=>'Juguetes', 'icon'=>'fa-puzzle-piece', 'min_age'=>0, 'max_age'=>14],
        ['slug'=>'art', 'name'=>'Arte', 'icon'=>'fa-palette', 'min_age'=>5],
    ];

    // Presupuestos
    $budgets = [
        ['slug'=>'small', 'name'=>'Detalle', 'range'=>'10-30', 'min'=>10, 'max'=>30],
        ['slug'=>'medium', 'name'=>'Regalo', 'range'=>'30-70', 'min'=>30, 'max'=>70],
        ['slug'=>'large', 'name'=>'Especial', 'range'=>'70-150', 'min'=>70, 'max'=>150],
        ['slug'=>'premium', 'name'=>'Premium', 'range'=>'150+', 'min'=>150, 'max'=>500],
    ];

    // Ocasiones para remarketing
    $occasions = [
        ['slug'=>'birthday', 'name'=>'Cumpleaños', 'icon'=>'fa-cake-candles', 'recurrent'=>true],
        ['slug'=>'christmas', 'name'=>'Navidad', 'icon'=>'fa-tree', 'recurrent'=>true, 'fixed_date'=>'12-25'],
        ['slug'=>'valentine', 'name'=>'San Valentín', 'icon'=>'fa-heart', 'recurrent'=>true, 'fixed_date'=>'02-14'],
        ['slug'=>'mother', 'name'=>'Día de la Madre', 'icon'=>'fa-person-dress', 'recurrent'=>true],
        ['slug'=>'father', 'name'=>'Día del Padre', 'icon'=>'fa-person', 'recurrent'=>true],
        ['slug'=>'anniversary', 'name'=>'Aniversario', 'icon'=>'fa-ring', 'recurrent'=>true],
        ['slug'=>'other', 'name'=>'Sin ocasión', 'icon'=>'fa-gift', 'recurrent'=>false],
    ];

    ob_start();
    ?>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        /* === RESET & BASE === */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        
        body.gf-active { 
            overflow: hidden !important; 
            margin: 0 !important; 
            padding: 0 !important; 
        }
        
        #gf-app {
            position: fixed !important;
            inset: 0 !important;
            width: 100% !important;
            height: 100% !important;
            background: linear-gradient(135deg, #0a0a0f 0%, #1a1a2e 50%, #0f0f1a 100%);
            color: #ffffff;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            z-index: 2147483647 !important;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        
        /* === FONDO ANIMADO AI === */
        .gf-bg {
            position: absolute;
            inset: 0;
            overflow: hidden;
            pointer-events: none;
            z-index: 0;
        }
        
        .gf-bg::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: 
                radial-gradient(ellipse at 20% 20%, rgba(99, 102, 241, 0.08) 0%, transparent 50%),
                radial-gradient(ellipse at 80% 80%, rgba(139, 92, 246, 0.06) 0%, transparent 50%),
                radial-gradient(ellipse at 50% 50%, rgba(59, 130, 246, 0.04) 0%, transparent 70%);
            animation: gf-pulse 20s ease-in-out infinite;
        }
        
        @keyframes gf-pulse {
            0%, 100% { transform: translate(0, 0) rotate(0deg); opacity: 1; }
            50% { transform: translate(-5%, -5%) rotate(180deg); opacity: 0.8; }
        }
        
        /* Grid pattern */
        .gf-grid {
            position: absolute;
            inset: 0;
            background-image: 
                linear-gradient(rgba(255,255,255,0.02) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.02) 1px, transparent 1px);
            background-size: 60px 60px;
            mask-image: radial-gradient(ellipse at center, black 0%, transparent 70%);
        }
        
        /* === HEADER === */
        .gf-header {
            position: relative;
            z-index: 10;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 30px;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        
        .gf-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
            font-size: 18px;
            letter-spacing: -0.5px;
        }
        
        .gf-logo i {
            font-size: 20px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .gf-btn-back {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            color: #94a3b8;
            width: 40px;
            height: 40px;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.2s;
            opacity: 0;
            pointer-events: none;
        }
        
        .gf-btn-back.visible {
            opacity: 1;
            pointer-events: all;
        }
        
        .gf-btn-back:hover {
            background: rgba(255,255,255,0.1);
            color: #fff;
        }
        
        /* Progress */
        .gf-progress {
            display: flex;
            gap: 6px;
        }
        
        .gf-progress-dot {
            width: 8px;
            height: 8px;
            border-radius: 4px;
            background: rgba(255,255,255,0.15);
            transition: all 0.3s;
        }
        
        .gf-progress-dot.active {
            width: 24px;
            background: linear-gradient(90deg, #6366f1, #8b5cf6);
        }
        
        .gf-progress-dot.done {
            background: #22c55e;
        }
        
        /* === MAIN CONTENT === */
        .gf-main {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            position: relative;
            z-index: 5;
            overflow-y: auto;
        }
        
        /* === STEPS === */
        .gf-step {
            position: absolute;
            width: 100%;
            max-width: 700px;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            opacity: 0;
            transform: translateY(30px);
            pointer-events: none;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .gf-step.active {
            opacity: 1;
            transform: translateY(0);
            pointer-events: all;
        }
        
        /* === TYPOGRAPHY === */
        .gf-title {
            font-size: 32px;
            font-weight: 700;
            letter-spacing: -1px;
            margin-bottom: 12px;
            line-height: 1.2;
        }
        
        .gf-subtitle {
            font-size: 16px;
            color: #64748b;
            margin-bottom: 40px;
            font-weight: 400;
            max-width: 400px;
        }
        
        /* Typewriter effect */
        .gf-typewriter {
            overflow: hidden;
            white-space: nowrap;
            border-right: 2px solid #6366f1;
            animation: gf-typing 0.8s steps(30, end), gf-blink 0.75s step-end infinite;
        }
        
        @keyframes gf-typing {
            from { width: 0; }
            to { width: 100%; }
        }
        
        @keyframes gf-blink {
            from, to { border-color: transparent; }
            50% { border-color: #6366f1; }
        }
        
        /* AI thinking indicator */
        .gf-ai-indicator {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: #6366f1;
            margin-bottom: 20px;
            padding: 8px 16px;
            background: rgba(99, 102, 241, 0.1);
            border-radius: 20px;
            border: 1px solid rgba(99, 102, 241, 0.2);
        }
        
        .gf-ai-indicator i {
            animation: gf-spin 2s linear infinite;
        }
        
        @keyframes gf-spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        /* === OPTION GRID === */
        .gf-options {
            display: grid;
            gap: 12px;
            width: 100%;
            grid-template-columns: repeat(5, 1fr);
        }
        
        .gf-options.cols-4 { grid-template-columns: repeat(4, 1fr); }
        .gf-options.cols-3 { grid-template-columns: repeat(3, 1fr); }
        .gf-options.cols-2 { grid-template-columns: repeat(2, 1fr); }
        
        /* === OPTION CARD === */
        .gf-option {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 16px;
            padding: 24px 16px;
            cursor: pointer;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
            position: relative;
            overflow: hidden;
        }
        
        .gf-option::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.1), rgba(139, 92, 246, 0.05));
            opacity: 0;
            transition: opacity 0.3s;
        }
        
        .gf-option:hover {
            border-color: rgba(99, 102, 241, 0.4);
            transform: translateY(-4px);
        }
        
        .gf-option:hover::before {
            opacity: 1;
        }
        
        .gf-option.selected {
            border-color: #6366f1;
            background: rgba(99, 102, 241, 0.15);
        }
        
        .gf-option.selected::after {
            content: '';
            position: absolute;
            top: 10px;
            right: 10px;
            width: 20px;
            height: 20px;
            background: #6366f1;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .gf-option i {
            font-size: 28px;
            color: #94a3b8;
            transition: color 0.2s;
            position: relative;
            z-index: 1;
        }
        
        .gf-option:hover i,
        .gf-option.selected i {
            color: #a5b4fc;
        }
        
        .gf-option span {
            font-size: 13px;
            font-weight: 500;
            color: #e2e8f0;
            position: relative;
            z-index: 1;
        }
        
        .gf-option.hidden {
            display: none;
        }
        
        /* === BUDGET CARDS === */
        .gf-budget {
            padding: 30px 20px;
        }
        
        .gf-budget .gf-range {
            font-size: 24px;
            font-weight: 700;
            color: #6366f1;
            margin-top: 8px;
        }
        
        /* === DATE INPUT === */
        .gf-date-group {
            display: flex;
            gap: 12px;
            margin-top: 20px;
        }
        
        .gf-input {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 12px;
            padding: 14px 18px;
            color: #fff;
            font-family: inherit;
            font-size: 15px;
            transition: all 0.2s;
            outline: none;
        }
        
        .gf-input:focus {
            border-color: #6366f1;
            background: rgba(99, 102, 241, 0.1);
        }
        
        .gf-input::placeholder {
            color: #475569;
        }
        
        /* === EMAIL CAPTURE === */
        .gf-email-section {
            width: 100%;
            max-width: 400px;
            margin-top: 30px;
        }
        
        .gf-email-input {
            width: 100%;
            padding: 16px 20px;
            font-size: 16px;
        }
        
        .gf-email-benefits {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-top: 24px;
            text-align: left;
        }
        
        .gf-benefit {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 14px;
            color: #94a3b8;
        }
        
        .gf-benefit i {
            color: #22c55e;
            font-size: 16px;
        }
        
        /* === BUTTONS === */
        .gf-btn {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            border: none;
            color: white;
            padding: 16px 40px;
            border-radius: 12px;
            font-family: inherit;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.25s;
            margin-top: 30px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        
        .gf-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 40px rgba(99, 102, 241, 0.3);
        }
        
        .gf-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }
        
        .gf-btn-secondary {
            background: transparent;
            border: 1px solid rgba(255,255,255,0.2);
            color: #94a3b8;
        }
        
        .gf-btn-secondary:hover {
            background: rgba(255,255,255,0.05);
            border-color: rgba(255,255,255,0.3);
            box-shadow: none;
        }
        
        .gf-skip {
            background: none;
            border: none;
            color: #64748b;
            font-size: 14px;
            cursor: pointer;
            margin-top: 20px;
            text-decoration: underline;
            text-underline-offset: 3px;
        }
        
        .gf-skip:hover {
            color: #94a3b8;
        }
        
        /* === LOADING === */
        .gf-loading {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 24px;
        }
        
        .gf-loader {
            width: 60px;
            height: 60px;
            border: 3px solid rgba(99, 102, 241, 0.2);
            border-top-color: #6366f1;
            border-radius: 50%;
            animation: gf-spin 1s linear infinite;
        }
        
        .gf-loading-text {
            font-size: 18px;
            color: #e2e8f0;
        }
        
        .gf-loading-step {
            font-size: 14px;
            color: #6366f1;
        }
        
        /* === INFO BOX === */
        .gf-info {
            background: rgba(99, 102, 241, 0.1);
            border: 1px solid rgba(99, 102, 241, 0.2);
            border-radius: 12px;
            padding: 14px 20px;
            font-size: 13px;
            color: #a5b4fc;
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 24px;
        }
        
        .gf-info.hidden { display: none; }
        
        /* === FEED OVERLAY === */
        #gf-feed {
            position: fixed;
            inset: 0;
            background: #000;
            z-index: 2147483648;
            overflow-y: scroll;
            scroll-snap-type: y mandatory;
            display: none;
        }
        
        .gf-feed-item {
            width: 100%;
            height: 100vh;
            scroll-snap-align: start;
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .gf-feed-bg {
            position: absolute;
            inset: 0;
            object-fit: cover;
            opacity: 0.4;
        }
        
        .gf-feed-content {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 480px;
            padding: 30px;
        }
        
        .gf-feed-card {
            background: rgba(0,0,0,0.8);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 24px;
            padding: 30px;
        }
        
        .gf-feed-rank {
            position: absolute;
            top: -15px;
            left: 50%;
            transform: translateX(-50%);
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 16px;
        }
        
        .gf-feed-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 12px;
            margin-top: 10px;
        }
        
        .gf-feed-price {
            font-size: 28px;
            font-weight: 700;
            color: #22c55e;
            margin: 16px 0;
        }
        
        .gf-feed-reason {
            background: rgba(99, 102, 241, 0.15);
            border-left: 3px solid #6366f1;
            padding: 12px 16px;
            border-radius: 0 12px 12px 0;
            font-size: 14px;
            color: #a5b4fc;
            margin: 16px 0;
        }
        
        .gf-feed-cta {
            display: block;
            background: linear-gradient(135deg, #22c55e, #16a34a);
            color: white;
            text-decoration: none;
            padding: 16px 30px;
            border-radius: 12px;
            font-weight: 600;
            text-align: center;
            margin-top: 20px;
            transition: transform 0.2s;
        }
        
        .gf-feed-cta:hover {
            transform: scale(1.02);
        }
        
        /* === RESPONSIVE === */
        @media (max-width: 768px) {
            .gf-options { grid-template-columns: repeat(2, 1fr) !important; }
            .gf-title { font-size: 26px; }
            .gf-subtitle { font-size: 14px; }
            .gf-option { padding: 18px 12px; }
            .gf-option i { font-size: 24px; }
            .gf-header { padding: 15px 20px; }
        }
        
        @media (max-width: 400px) {
            .gf-options { grid-template-columns: repeat(2, 1fr) !important; gap: 8px; }
            .gf-option { padding: 14px 8px; }
        }
    </style>

    <div id="gf-app">
        <div class="gf-bg">
            <div class="gf-grid"></div>
        </div>
        
        <header class="gf-header">
            <button class="gf-btn-back" id="gf-back" onclick="gfBack()">
                <i class="fa-solid fa-arrow-left"></i>
            </button>
            
            <div class="gf-logo">
                <i class="fa-solid fa-wand-magic-sparkles"></i>
                <span>Giftia</span>
            </div>
            
            <div class="gf-progress" id="gf-progress"></div>
        </header>

        <main class="gf-main">
            
            <!-- STEP 1: Para quién -->
            <section class="gf-step active" id="step-who">
                <div class="gf-ai-indicator">
                    <i class="fa-solid fa-circle-nodes"></i>
                    <span>AI lista para analizar</span>
                </div>
                <h1 class="gf-title" id="title-who"></h1>
                <p class="gf-subtitle">Selecciona para personalizar las recomendaciones</p>
                <div class="gf-options" id="options-who">
                    <?php foreach($profiles as $p): ?>
                    <div class="gf-option" data-value="<?php echo esc_attr($p['slug']); ?>" data-profile='<?php echo esc_attr(json_encode($p, JSON_HEX_APOS | JSON_HEX_QUOT)); ?>' onclick="gfSelectProfile(this)">
                        <i class="fa-solid <?php echo esc_attr($p['icon']); ?>"></i>
                        <span><?php echo esc_html($p['name']); ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <!-- STEP 2: Edad (condicional) -->
            <section class="gf-step" id="step-age">
                <h1 class="gf-title" id="title-age"></h1>
                <p class="gf-subtitle">Esto nos ayuda a filtrar productos apropiados</p>
                <div class="gf-options cols-3" id="options-age">
                    <?php foreach($age_ranges as $a): ?>
                    <div class="gf-option" data-value="<?php echo esc_attr($a['slug']); ?>" data-age='<?php echo esc_attr(json_encode($a, JSON_HEX_APOS | JSON_HEX_QUOT)); ?>' onclick="gfSelectAge(this)">
                        <i class="fa-solid <?php echo esc_attr($a['icon']); ?>"></i>
                        <span><?php echo esc_html($a['name']); ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <!-- STEP 3: Intereses -->
            <section class="gf-step" id="step-vibe">
                <h1 class="gf-title" id="title-vibe"></h1>
                <p class="gf-subtitle">Selecciona su principal interés</p>
                <div class="gf-info hidden" id="vibe-info">
                    <i class="fa-solid fa-shield-check"></i>
                    <span id="vibe-info-text"></span>
                </div>
                <div class="gf-options cols-4" id="options-vibe">
                    <?php foreach($vibes as $v): ?>
                    <div class="gf-option" data-value="<?php echo esc_attr($v['slug']); ?>" data-vibe='<?php echo esc_attr(json_encode($v, JSON_HEX_APOS | JSON_HEX_QUOT)); ?>' onclick="gfSelectVibe(this)">
                        <i class="fa-solid <?php echo esc_attr($v['icon']); ?>"></i>
                        <span><?php echo esc_html($v['name']); ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <!-- STEP 4: Presupuesto -->
            <section class="gf-step" id="step-budget">
                <h1 class="gf-title" id="title-budget"></h1>
                <p class="gf-subtitle">Define el rango de precio</p>
                <div class="gf-options cols-4" id="options-budget">
                    <?php foreach($budgets as $b): ?>
                    <div class="gf-option gf-budget" data-value="<?php echo esc_attr($b['slug']); ?>" data-budget='<?php echo esc_attr(json_encode($b, JSON_HEX_APOS | JSON_HEX_QUOT)); ?>' onclick="gfSelectBudget(this)">
                        <span><?php echo esc_html($b['name']); ?></span>
                        <span class="gf-range"><?php echo esc_html($b['range']); ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <!-- STEP 5: Ocasión -->
            <section class="gf-step" id="step-occasion">
                <h1 class="gf-title" id="title-occasion"></h1>
                <p class="gf-subtitle">Podremos recordarte en futuras ocasiones</p>
                <div class="gf-options cols-4" id="options-occasion">
                    <?php foreach($occasions as $o): ?>
                    <div class="gf-option" data-value="<?php echo esc_attr($o['slug']); ?>" data-occasion='<?php echo esc_attr(json_encode($o, JSON_HEX_APOS | JSON_HEX_QUOT)); ?>' onclick="gfSelectOccasion(this)">
                        <i class="fa-solid <?php echo esc_attr($o['icon']); ?>"></i>
                        <span><?php echo esc_html($o['name']); ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="gf-date-group hidden" id="date-group">
                    <input type="date" class="gf-input" id="occasion-date" placeholder="Fecha">
                </div>
            </section>

            <!-- STEP 6: Email (opcional) -->
            <section class="gf-step" id="step-email">
                <h1 class="gf-title" id="title-email"></h1>
                <p class="gf-subtitle">Te avisaremos antes de las fechas importantes</p>
                
                <div class="gf-email-section">
                    <input type="email" class="gf-input gf-email-input" id="user-email" placeholder="tu@email.com">
                    
                    <div class="gf-email-benefits">
                        <div class="gf-benefit">
                            <i class="fa-solid fa-check"></i>
                            <span>Recordatorio antes de cumpleaños y fechas especiales</span>
                        </div>
                        <div class="gf-benefit">
                            <i class="fa-solid fa-check"></i>
                            <span>Ideas personalizadas basadas en este perfil</span>
                        </div>
                        <div class="gf-benefit">
                            <i class="fa-solid fa-check"></i>
                            <span>Ofertas exclusivas para tus regalos</span>
                        </div>
                    </div>
                    
                    <button class="gf-btn" onclick="gfSaveEmail()">
                        <i class="fa-solid fa-wand-magic-sparkles"></i>
                        Ver recomendaciones
                    </button>
                    
                    <button class="gf-skip" onclick="gfSkipEmail()">
                        Continuar sin guardar
                    </button>
                </div>
            </section>

            <!-- STEP LOADING -->
            <section class="gf-step" id="step-loading">
                <div class="gf-loading">
                    <div class="gf-loader"></div>
                    <div class="gf-loading-text">Analizando perfil</div>
                    <div class="gf-loading-step" id="loading-step">Construyendo avatar inteligente...</div>
                </div>
            </section>

        </main>
    </div>

    <!-- FEED DE RESULTADOS -->
    <div id="gf-feed"></div>

    <script>
    (function() {
        'use strict';
        
        // Mover al body y activar
        document.addEventListener('DOMContentLoaded', function() {
            const app = document.getElementById('gf-app');
            const feed = document.getElementById('gf-feed');
            if (app) {
                document.body.appendChild(app);
                document.body.classList.add('gf-active');
            }
            if (feed) document.body.appendChild(feed);
            
            // Inicializar
            gfInit();
        });
        
        // Estado global
        window.gfState = {
            step: 0,
            profile: null,
            age: null,
            vibe: null,
            budget: null,
            occasion: null,
            occasionDate: null,
            email: null,
            allowAlcohol: false
        };
        
        // Flujo de pasos (dinámico)
        window.gfFlow = ['step-who', 'step-vibe', 'step-budget', 'step-occasion', 'step-email', 'step-loading'];
        
        // Títulos con efecto typewriter
        const titles = {
            'step-who': 'Para quien es el regalo?',
            'step-age': 'Que edad tiene?',
            'step-vibe': 'Que le interesa?',
            'step-budget': 'Cual es tu presupuesto?',
            'step-occasion': 'Es para alguna ocasion?',
            'step-email': 'Quieres que te recordemos?'
        };
        
        // Inicializar
        window.gfInit = function() {
            renderProgress();
            typeTitle('title-who', titles['step-who']);
        };
        
        // Efecto typewriter
        function typeTitle(elementId, text) {
            const el = document.getElementById(elementId);
            if (!el) return;
            
            el.textContent = '';
            el.style.opacity = '1';
            
            let i = 0;
            const speed = 40;
            
            function type() {
                if (i < text.length) {
                    el.textContent += text.charAt(i);
                    i++;
                    setTimeout(type, speed);
                }
            }
            type();
        }
        
        // Renderizar progreso
        function renderProgress() {
            const container = document.getElementById('gf-progress');
            const totalSteps = gfFlow.length - 1; // Sin loading
            let html = '';
            
            for (let i = 0; i < totalSteps; i++) {
                let cls = 'gf-progress-dot';
                if (i < gfState.step) cls += ' done';
                else if (i === gfState.step) cls += ' active';
                html += '<div class="' + cls + '"></div>';
            }
            
            container.innerHTML = html;
            
            // Botón back
            const backBtn = document.getElementById('gf-back');
            if (gfState.step > 0 && gfFlow[gfState.step] !== 'step-loading') {
                backBtn.classList.add('visible');
            } else {
                backBtn.classList.remove('visible');
            }
        }
        
        // Navegar al siguiente paso
        function gfNextStep() {
            const current = gfFlow[gfState.step];
            const currentEl = document.getElementById(current);
            
            gfState.step++;
            
            const next = gfFlow[gfState.step];
            const nextEl = document.getElementById(next);
            
            if (currentEl) currentEl.classList.remove('active');
            if (nextEl) {
                nextEl.classList.add('active');
                
                // Typewriter en título
                const titleId = 'title-' + next.replace('step-', '');
                if (titles[next]) {
                    setTimeout(() => typeTitle(titleId, titles[next]), 100);
                }
            }
            
            renderProgress();
        }
        
        // Volver atrás
        window.gfBack = function() {
            if (gfState.step <= 0) return;
            
            const current = gfFlow[gfState.step];
            const currentEl = document.getElementById(current);
            
            gfState.step--;
            
            const prev = gfFlow[gfState.step];
            const prevEl = document.getElementById(prev);
            
            if (currentEl) currentEl.classList.remove('active');
            if (prevEl) prevEl.classList.add('active');
            
            renderProgress();
        };
        
        // Seleccionar perfil
        window.gfSelectProfile = function(el) {
            const profile = JSON.parse(el.dataset.profile);
            gfState.profile = profile;
            
            // Visual feedback
            document.querySelectorAll('#options-who .gf-option').forEach(o => o.classList.remove('selected'));
            el.classList.add('selected');
            
            // Determinar si necesita preguntar edad
            if (profile.ask_age) {
                // Insertar paso de edad
                if (!gfFlow.includes('step-age')) {
                    gfFlow.splice(1, 0, 'step-age');
                }
            } else {
                // Quitar paso de edad si existe
                gfFlow = gfFlow.filter(s => s !== 'step-age');
                gfState.age = { min: profile.min_age, max: profile.max_age };
                gfState.allowAlcohol = profile.allow_alcohol;
            }
            
            // Filtrar vibes según edad
            filterVibesByAge();
            
            setTimeout(() => gfNextStep(), 300);
        };
        
        // Seleccionar edad
        window.gfSelectAge = function(el) {
            const age = JSON.parse(el.dataset.age);
            gfState.age = age;
            gfState.allowAlcohol = age.min >= 18;
            
            document.querySelectorAll('#options-age .gf-option').forEach(o => o.classList.remove('selected'));
            el.classList.add('selected');
            
            filterVibesByAge();
            
            setTimeout(() => gfNextStep(), 300);
        };
        
        // Filtrar vibes por edad
        function filterVibesByAge() {
            const ageMax = gfState.age ? gfState.age.max : 99;
            const ageMin = gfState.age ? gfState.age.min : 0;
            
            document.querySelectorAll('#options-vibe .gf-option').forEach(el => {
                const vibe = JSON.parse(el.dataset.vibe);
                const vibeMinAge = vibe.min_age || 0;
                const vibeMaxAge = vibe.max_age || 999;
                
                const isAppropriate = ageMin >= vibeMinAge || ageMax >= vibeMinAge;
                const notTooOld = ageMin <= vibeMaxAge;
                
                if (isAppropriate && notTooOld) {
                    el.classList.remove('hidden');
                } else {
                    el.classList.add('hidden');
                }
            });
            
            // Mostrar info si se filtró algo
            const hidden = document.querySelectorAll('#options-vibe .gf-option.hidden').length;
            const infoEl = document.getElementById('vibe-info');
            if (hidden > 0 && !gfState.allowAlcohol) {
                infoEl.classList.remove('hidden');
                document.getElementById('vibe-info-text').textContent = 'Categorias filtradas segun la edad seleccionada';
            } else {
                infoEl.classList.add('hidden');
            }
        }
        
        // Seleccionar vibe
        window.gfSelectVibe = function(el) {
            const vibe = JSON.parse(el.dataset.vibe);
            gfState.vibe = vibe;
            
            document.querySelectorAll('#options-vibe .gf-option').forEach(o => o.classList.remove('selected'));
            el.classList.add('selected');
            
            setTimeout(() => gfNextStep(), 300);
        };
        
        // Seleccionar presupuesto
        window.gfSelectBudget = function(el) {
            const budget = JSON.parse(el.dataset.budget);
            gfState.budget = budget;
            
            document.querySelectorAll('#options-budget .gf-option').forEach(o => o.classList.remove('selected'));
            el.classList.add('selected');
            
            setTimeout(() => gfNextStep(), 300);
        };
        
        // Seleccionar ocasión
        window.gfSelectOccasion = function(el) {
            const occasion = JSON.parse(el.dataset.occasion);
            gfState.occasion = occasion;
            
            document.querySelectorAll('#options-occasion .gf-option').forEach(o => o.classList.remove('selected'));
            el.classList.add('selected');
            
            // Mostrar selector de fecha si es recurrente
            const dateGroup = document.getElementById('date-group');
            if (occasion.recurrent && occasion.slug !== 'christmas' && occasion.slug !== 'valentine') {
                dateGroup.classList.remove('hidden');
            } else {
                dateGroup.classList.add('hidden');
            }
            
            setTimeout(() => gfNextStep(), 500);
        };
        
        // Guardar email
        window.gfSaveEmail = function() {
            const email = document.getElementById('user-email').value;
            
            if (email && email.includes('@')) {
                gfState.email = email;
                gfState.occasionDate = document.getElementById('occasion-date').value;
                
                // Enviar datos al servidor
                saveLeadData();
            }
            
            gfStartSearch();
        };
        
        // Saltar email
        window.gfSkipEmail = function() {
            gfStartSearch();
        };
        
        // Guardar lead en servidor
        function saveLeadData() {
            const data = {
                action: 'giftia_save_lead',
                email: gfState.email,
                profile: gfState.profile ? gfState.profile.slug : '',
                profile_name: gfState.profile ? gfState.profile.name : '',
                age_range: gfState.age ? gfState.age.min + '-' + gfState.age.max : '',
                vibe: gfState.vibe ? gfState.vibe.slug : '',
                occasion: gfState.occasion ? gfState.occasion.slug : '',
                occasion_date: gfState.occasionDate || '',
                budget: gfState.budget ? gfState.budget.slug : ''
            };
            
            // Enviar via AJAX (WordPress)
            fetch('<?php echo admin_url("admin-ajax.php"); ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams(data)
            }).catch(err => console.log('Lead save error:', err));
        }
        
        // Iniciar búsqueda
        function gfStartSearch() {
            // Ir al paso de loading
            document.querySelectorAll('.gf-step').forEach(s => s.classList.remove('active'));
            document.getElementById('step-loading').classList.add('active');
            document.getElementById('gf-back').classList.remove('visible');
            
            // Mensajes de loading
            const messages = [
                'Construyendo perfil inteligente...',
                'Analizando preferencias...',
                'Buscando productos relevantes...',
                'Consultando modelo AI...',
                'Ordenando recomendaciones...'
            ];
            
            let msgIndex = 0;
            const loadingInterval = setInterval(() => {
                msgIndex = (msgIndex + 1) % messages.length;
                document.getElementById('loading-step').textContent = messages[msgIndex];
            }, 1500);
            
            // Construir avatar para API
            const avatar = {
                relation: gfState.profile ? gfState.profile.slug : 'amigo',
                relation_name: gfState.profile ? gfState.profile.name : 'Amigo',
                age_min: gfState.age ? gfState.age.min : 18,
                age_max: gfState.age ? gfState.age.max : 40,
                interest_tags: gfState.vibe ? [gfState.vibe.name] : ['Tech'],
                vibe: gfState.vibe ? gfState.vibe.slug : 'tech',
                vibe_name: gfState.vibe ? gfState.vibe.name : 'Tech',
                min_price: gfState.budget ? gfState.budget.min : 30,
                max_price: gfState.budget ? gfState.budget.max : 100,
                allow_alcohol: gfState.allowAlcohol,
                occasion: gfState.occasion ? gfState.occasion.slug : 'other'
            };
            
            // Llamar API
            fetch('<?php echo plugins_url("api-recommend.php", __FILE__); ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(avatar)
            })
            .then(res => res.json())
            .then(data => {
                clearInterval(loadingInterval);
                
                if (data.products && data.products.length > 0) {
                    sessionStorage.setItem('giftia_results', JSON.stringify(data));
                    renderFeed(data);
                } else {
                    document.getElementById('loading-step').textContent = 'No se encontraron productos. Intenta con otros filtros.';
                }
            })
            .catch(err => {
                clearInterval(loadingInterval);
                document.getElementById('loading-step').textContent = 'Error de conexion. Intenta de nuevo.';
                console.error(err);
            });
        }
        
        // Renderizar feed de resultados
        function renderFeed(data) {
            const feed = document.getElementById('gf-feed');
            let html = '';
            
            // Header
            html += '<div class="gf-feed-item" style="background: linear-gradient(180deg, #0a0a0f 0%, #1a1a2e 100%);">';
            html += '<div class="gf-feed-content" style="text-align: center;">';
            html += '<i class="fa-solid fa-wand-magic-sparkles" style="font-size: 48px; color: #6366f1; margin-bottom: 20px;"></i>';
            html += '<h1 style="font-size: 28px; margin-bottom: 15px;">Recomendaciones personalizadas</h1>';
            if (data.avatar_summary) {
                html += '<p style="color: #64748b; font-size: 14px; max-width: 300px; margin: 0 auto 30px;">' + data.avatar_summary + '</p>';
            }
            html += '<p style="color: #6366f1;"><i class="fa-solid fa-chevron-down"></i> Desliza para ver ' + data.products.length + ' productos</p>';
            html += '</div></div>';
            
            // Productos
            data.products.forEach((product, i) => {
                html += '<div class="gf-feed-item">';
                html += '<img class="gf-feed-bg" src="' + (product.image || 'https://via.placeholder.com/800x1200/1a1a2e/6366f1?text=Gift') + '" alt="">';
                html += '<div class="gf-feed-content">';
                html += '<div class="gf-feed-card" style="position: relative;">';
                html += '<div class="gf-feed-rank">' + (i + 1) + '</div>';
                html += '<h2 class="gf-feed-title">' + product.title + '</h2>';
                html += '<div class="gf-feed-price">' + product.price.toFixed(2) + ' EUR</div>';
                if (product.reason) {
                    html += '<div class="gf-feed-reason"><i class="fa-solid fa-robot"></i> ' + product.reason + '</div>';
                }
                if (product.vendor) {
                    html += '<div style="color: #64748b; font-size: 12px; margin-bottom: 15px;">Vendido por: ' + product.vendor + '</div>';
                }
                html += '<a href="' + product.affiliate_url + '" target="_blank" class="gf-feed-cta"><i class="fa-solid fa-cart-shopping"></i> Comprar ahora</a>';
                html += '</div></div></div>';
            });
            
            // Footer
            html += '<div class="gf-feed-item" style="background: linear-gradient(180deg, #1a1a2e 0%, #0a0a0f 100%);">';
            html += '<div class="gf-feed-content" style="text-align: center;">';
            html += '<i class="fa-solid fa-rotate" style="font-size: 40px; color: #64748b; margin-bottom: 20px;"></i>';
            html += '<h2 style="margin-bottom: 15px;">No encontraste el regalo perfecto?</h2>';
            html += '<button onclick="location.reload()" style="background: linear-gradient(135deg, #6366f1, #8b5cf6); color: white; border: none; padding: 16px 32px; border-radius: 12px; font-weight: 600; cursor: pointer;">Empezar de nuevo</button>';
            html += '</div></div>';
            
            feed.innerHTML = html;
            feed.style.display = 'block';
            document.getElementById('gf-app').style.display = 'none';
        }
        
    })();
    </script>

    <?php
    return ob_get_clean();
}

// === AJAX: Guardar Lead ===
add_action('wp_ajax_giftia_save_lead', 'giftia_save_lead');
add_action('wp_ajax_nopriv_giftia_save_lead', 'giftia_save_lead');

function giftia_save_lead() {
    global $wpdb;
    
    $email = sanitize_email($_POST['email'] ?? '');
    if (empty($email)) {
        wp_send_json_error('Email requerido');
        return;
    }
    
    $table = $wpdb->prefix . 'gf_leads';
    
    // Crear tabla si no existe
    $wpdb->query("CREATE TABLE IF NOT EXISTS $table (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL,
        profile VARCHAR(50),
        profile_name VARCHAR(100),
        age_range VARCHAR(20),
        vibe VARCHAR(50),
        occasion VARCHAR(50),
        occasion_date DATE,
        budget VARCHAR(20),
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_email (email),
        INDEX idx_occasion (occasion, occasion_date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    
    // Insertar lead
    $result = $wpdb->insert($table, [
        'email' => $email,
        'profile' => sanitize_text_field($_POST['profile'] ?? ''),
        'profile_name' => sanitize_text_field($_POST['profile_name'] ?? ''),
        'age_range' => sanitize_text_field($_POST['age_range'] ?? ''),
        'vibe' => sanitize_text_field($_POST['vibe'] ?? ''),
        'occasion' => sanitize_text_field($_POST['occasion'] ?? ''),
        'occasion_date' => sanitize_text_field($_POST['occasion_date'] ?? null),
        'budget' => sanitize_text_field($_POST['budget'] ?? '')
    ]);
    
    if ($result) {
        wp_send_json_success(['id' => $wpdb->insert_id]);
    } else {
        wp_send_json_error('Error al guardar');
    }
}
