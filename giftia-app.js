// Giftia AI Profiler - Script Principal
(function() {
    'use strict';
    
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
    
    // Flujo de pasos
    window.gfFlow = ['step-who', 'step-vibe', 'step-budget', 'step-occasion', 'step-email', 'step-loading'];
    
    // Titulos
    window.gfTitles = {
        'step-who': 'Para quien es el regalo?',
        'step-age': 'Que edad tiene?',
        'step-vibe': 'Que le interesa?',
        'step-budget': 'Cual es tu presupuesto?',
        'step-occasion': 'Es para alguna ocasion?',
        'step-email': 'Quieres que te recordemos?'
    };
    
    // Typewriter
    window.gfTypeTitle = function(elementId, text) {
        var el = document.getElementById(elementId);
        if (!el) return;
        el.textContent = '';
        el.style.opacity = '1';
        var i = 0;
        function type() {
            if (i < text.length) {
                el.textContent += text.charAt(i);
                i++;
                setTimeout(type, 40);
            }
        }
        type();
    };
    
    // Renderizar progreso
    window.gfRenderProgress = function() {
        var container = document.getElementById('gf-progress');
        if (!container) return;
        var totalSteps = window.gfFlow.length - 1;
        var html = '';
        for (var i = 0; i < totalSteps; i++) {
            var cls = 'gf-progress-dot';
            if (i < window.gfState.step) cls += ' done';
            else if (i === window.gfState.step) cls += ' active';
            html += '<div class="' + cls + '"></div>';
        }
        container.innerHTML = html;
        var backBtn = document.getElementById('gf-back');
        if (backBtn) {
            if (window.gfState.step > 0 && window.gfFlow[window.gfState.step] !== 'step-loading') {
                backBtn.classList.add('visible');
            } else {
                backBtn.classList.remove('visible');
            }
        }
    };
    
    // Siguiente paso
    window.gfNextStep = function() {
        var current = window.gfFlow[window.gfState.step];
        var currentEl = document.getElementById(current);
        window.gfState.step++;
        var next = window.gfFlow[window.gfState.step];
        var nextEl = document.getElementById(next);
        if (currentEl) currentEl.classList.remove('active');
        if (nextEl) {
            nextEl.classList.add('active');
            var titleId = 'title-' + next.replace('step-', '');
            if (window.gfTitles[next]) {
                setTimeout(function() { window.gfTypeTitle(titleId, window.gfTitles[next]); }, 100);
            }
        }
        window.gfRenderProgress();
    };
    
    // Volver atras
    window.gfBack = function() {
        if (window.gfState.step <= 0) return;
        var current = window.gfFlow[window.gfState.step];
        var currentEl = document.getElementById(current);
        window.gfState.step--;
        var prev = window.gfFlow[window.gfState.step];
        var prevEl = document.getElementById(prev);
        if (currentEl) currentEl.classList.remove('active');
        if (prevEl) prevEl.classList.add('active');
        window.gfRenderProgress();
    };
    
    // Filtrar vibes
    window.gfFilterVibes = function() {
        var ageMax = window.gfState.age ? window.gfState.age.max : 99;
        var ageMin = window.gfState.age ? window.gfState.age.min : 0;
        var options = document.querySelectorAll('#options-vibe .gf-option');
        options.forEach(function(el) {
            try {
                var vibe = JSON.parse(el.dataset.vibe);
                var vibeMinAge = vibe.min_age || 0;
                var vibeMaxAge = vibe.max_age || 999;
                var isAppropriate = ageMin >= vibeMinAge || ageMax >= vibeMinAge;
                var notTooOld = ageMin <= vibeMaxAge;
                if (isAppropriate && notTooOld) {
                    el.classList.remove('hidden');
                } else {
                    el.classList.add('hidden');
                }
            } catch(e) {
                console.error('Error parsing vibe:', e);
            }
        });
        var hidden = document.querySelectorAll('#options-vibe .gf-option.hidden').length;
        var infoEl = document.getElementById('vibe-info');
        if (infoEl) {
            if (hidden > 0 && !window.gfState.allowAlcohol) {
                infoEl.classList.remove('hidden');
                var infoText = document.getElementById('vibe-info-text');
                if (infoText) infoText.textContent = 'Categorias filtradas segun la edad seleccionada';
            } else {
                infoEl.classList.add('hidden');
            }
        }
    };
    
    // Seleccionar perfil
    window.gfSelectProfile = function(el) {
        try {
            var profile = JSON.parse(el.dataset.profile);
            window.gfState.profile = profile;
            document.querySelectorAll('#options-who .gf-option').forEach(function(o) { o.classList.remove('selected'); });
            el.classList.add('selected');
            if (profile.ask_age) {
                if (window.gfFlow.indexOf('step-age') === -1) {
                    window.gfFlow.splice(1, 0, 'step-age');
                }
            } else {
                window.gfFlow = window.gfFlow.filter(function(s) { return s !== 'step-age'; });
                window.gfState.age = { min: profile.min_age, max: profile.max_age };
                window.gfState.allowAlcohol = profile.allow_alcohol;
            }
            window.gfFilterVibes();
            setTimeout(function() { window.gfNextStep(); }, 300);
        } catch(e) {
            console.error('Error in gfSelectProfile:', e);
        }
    };
    
    // Seleccionar edad
    window.gfSelectAge = function(el) {
        try {
            var age = JSON.parse(el.dataset.age);
            window.gfState.age = age;
            window.gfState.allowAlcohol = age.min >= 18;
            document.querySelectorAll('#options-age .gf-option').forEach(function(o) { o.classList.remove('selected'); });
            el.classList.add('selected');
            window.gfFilterVibes();
            setTimeout(function() { window.gfNextStep(); }, 300);
        } catch(e) {
            console.error('Error in gfSelectAge:', e);
        }
    };
    
    // Seleccionar vibe
    window.gfSelectVibe = function(el) {
        try {
            var vibe = JSON.parse(el.dataset.vibe);
            window.gfState.vibe = vibe;
            document.querySelectorAll('#options-vibe .gf-option').forEach(function(o) { o.classList.remove('selected'); });
            el.classList.add('selected');
            setTimeout(function() { window.gfNextStep(); }, 300);
        } catch(e) {
            console.error('Error in gfSelectVibe:', e);
        }
    };
    
    // Seleccionar presupuesto
    window.gfSelectBudget = function(el) {
        try {
            var budget = JSON.parse(el.dataset.budget);
            window.gfState.budget = budget;
            document.querySelectorAll('#options-budget .gf-option').forEach(function(o) { o.classList.remove('selected'); });
            el.classList.add('selected');
            setTimeout(function() { window.gfNextStep(); }, 300);
        } catch(e) {
            console.error('Error in gfSelectBudget:', e);
        }
    };
    
    // Seleccionar ocasion
    window.gfSelectOccasion = function(el) {
        try {
            var occasion = JSON.parse(el.dataset.occasion);
            window.gfState.occasion = occasion;
            document.querySelectorAll('#options-occasion .gf-option').forEach(function(o) { o.classList.remove('selected'); });
            el.classList.add('selected');
            var dateGroup = document.getElementById('date-group');
            if (dateGroup) {
                if (occasion.recurrent && occasion.slug !== 'christmas' && occasion.slug !== 'valentine') {
                    dateGroup.classList.remove('hidden');
                } else {
                    dateGroup.classList.add('hidden');
                }
            }
            setTimeout(function() { window.gfNextStep(); }, 500);
        } catch(e) {
            console.error('Error in gfSelectOccasion:', e);
        }
    };
    
    // Guardar email
    window.gfSaveEmail = function() {
        var emailEl = document.getElementById('user-email');
        var email = emailEl ? emailEl.value : '';
        if (email && email.indexOf('@') !== -1) {
            window.gfState.email = email;
            var dateEl = document.getElementById('occasion-date');
            window.gfState.occasionDate = dateEl ? dateEl.value : '';
            window.gfSaveLead();
        }
        window.gfStartSearch();
    };
    
    // Saltar email
    window.gfSkipEmail = function() {
        window.gfStartSearch();
    };
    
    // Guardar lead
    window.gfSaveLead = function() {
        if (!window.GIFTIA_AJAX_URL) return;
        var data = {
            action: 'giftia_save_lead',
            email: window.gfState.email || '',
            profile: window.gfState.profile ? window.gfState.profile.slug : '',
            profile_name: window.gfState.profile ? window.gfState.profile.name : '',
            age_range: window.gfState.age ? window.gfState.age.min + '-' + window.gfState.age.max : '',
            vibe: window.gfState.vibe ? window.gfState.vibe.slug : '',
            occasion: window.gfState.occasion ? window.gfState.occasion.slug : '',
            occasion_date: window.gfState.occasionDate || '',
            budget: window.gfState.budget ? window.gfState.budget.slug : ''
        };
        fetch(window.GIFTIA_AJAX_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams(data)
        }).catch(function(err) { console.log('Lead save error:', err); });
    };
    
    // Iniciar busqueda
    window.gfStartSearch = function() {
        document.querySelectorAll('.gf-step').forEach(function(s) { s.classList.remove('active'); });
        var loadingEl = document.getElementById('step-loading');
        if (loadingEl) loadingEl.classList.add('active');
        var backBtn = document.getElementById('gf-back');
        if (backBtn) backBtn.classList.remove('visible');
        
        var messages = [
            'Construyendo perfil inteligente...',
            'Analizando preferencias...',
            'Buscando productos relevantes...',
            'Consultando modelo AI...',
            'Ordenando recomendaciones...'
        ];
        var msgIndex = 0;
        var loadingInterval = setInterval(function() {
            msgIndex = (msgIndex + 1) % messages.length;
            var loadingStep = document.getElementById('loading-step');
            if (loadingStep) loadingStep.textContent = messages[msgIndex];
        }, 1500);
        
        var avatar = {
            relation: window.gfState.profile ? window.gfState.profile.slug : 'amigo',
            relation_name: window.gfState.profile ? window.gfState.profile.name : 'Amigo',
            age_min: window.gfState.age ? window.gfState.age.min : 18,
            age_max: window.gfState.age ? window.gfState.age.max : 40,
            interest_tags: window.gfState.vibe ? [window.gfState.vibe.name] : ['Tech'],
            vibe: window.gfState.vibe ? window.gfState.vibe.slug : 'tech',
            vibe_name: window.gfState.vibe ? window.gfState.vibe.name : 'Tech',
            min_price: window.gfState.budget ? window.gfState.budget.min : 30,
            max_price: window.gfState.budget ? window.gfState.budget.max : 100,
            allow_alcohol: window.gfState.allowAlcohol,
            occasion: window.gfState.occasion ? window.gfState.occasion.slug : 'other'
        };
        
        if (!window.GIFTIA_API_URL) {
            console.error('GIFTIA_API_URL not defined');
            return;
        }
        
        fetch(window.GIFTIA_API_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(avatar)
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            clearInterval(loadingInterval);
            if (data.products && data.products.length > 0) {
                sessionStorage.setItem('giftia_results', JSON.stringify(data));
                window.gfRenderFeed(data);
            } else {
                var loadingStep = document.getElementById('loading-step');
                if (loadingStep) loadingStep.textContent = 'No se encontraron productos. Intenta con otros filtros.';
            }
        })
        .catch(function(err) {
            clearInterval(loadingInterval);
            var loadingStep = document.getElementById('loading-step');
            if (loadingStep) loadingStep.textContent = 'Error de conexion. Intenta de nuevo.';
            console.error(err);
        });
    };
    
    // Renderizar feed
    window.gfRenderFeed = function(data) {
        var feed = document.getElementById('gf-feed');
        if (!feed) return;
        
        // Mover feed al body para evitar problemas de CSS
        if (feed.parentNode !== document.body) {
            document.body.appendChild(feed);
        }
        
        var html = '';
        html += '<div class="gf-feed-item" style="background: linear-gradient(180deg, #0a0a0f 0%, #1a1a2e 100%);">';
        html += '<div class="gf-feed-content" style="text-align: center;">';
        html += '<i class="fa-solid fa-wand-magic-sparkles" style="font-size: 48px; color: #6366f1; margin-bottom: 20px;"></i>';
        html += '<h1 style="font-size: 28px; margin-bottom: 15px;">Recomendaciones personalizadas</h1>';
        if (data.avatar_summary) {
            html += '<p style="color: #64748b; font-size: 14px; max-width: 300px; margin: 0 auto 30px;">' + data.avatar_summary + '</p>';
        }
        html += '<p style="color: #6366f1;"><i class="fa-solid fa-chevron-down"></i> Desliza para ver ' + data.products.length + ' productos</p>';
        html += '</div></div>';
        
        data.products.forEach(function(product, i) {
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
        
        html += '<div class="gf-feed-item" style="background: linear-gradient(180deg, #1a1a2e 0%, #0a0a0f 100%);">';
        html += '<div class="gf-feed-content" style="text-align: center;">';
        html += '<i class="fa-solid fa-rotate" style="font-size: 40px; color: #64748b; margin-bottom: 20px;"></i>';
        html += '<h2 style="margin-bottom: 15px;">No encontraste el regalo perfecto?</h2>';
        html += '<button onclick="location.reload()" style="background: linear-gradient(135deg, #6366f1, #8b5cf6); color: white; border: none; padding: 16px 32px; border-radius: 12px; font-weight: 600; cursor: pointer;">Empezar de nuevo</button>';
        html += '</div></div>';
        
        feed.innerHTML = html;
        feed.style.display = 'block';
        var app = document.getElementById('gf-app');
        if (app) app.style.display = 'none';
    };
    
    // Init
    window.gfInit = function() {
        // Mover app al body
        var app = document.getElementById('gf-app');
        if (app && app.parentNode !== document.body) {
            document.body.appendChild(app);
        }
        document.body.classList.add('gf-active');
        
        window.gfRenderProgress();
        window.gfTypeTitle('title-who', window.gfTitles['step-who']);
    };
    
    // Ejecutar cuando DOM listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', window.gfInit);
    } else {
        setTimeout(window.gfInit, 0);
    }
    
    console.log('Giftia: Script loaded successfully');
})();
