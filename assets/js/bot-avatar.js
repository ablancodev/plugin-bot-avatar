/**
 * Bot Avatar 3D Chatbot - Diseño Holográfico
 */

(function($) {
    'use strict';

    console.log('Bot Avatar JS loaded');

    // Verificar que botAvatarData existe
    if (typeof botAvatarData === 'undefined') {
        console.error('botAvatarData is not defined! The plugin may not work correctly.');
        return;
    }

    console.log('botAvatarData:', botAvatarData);

    class BotAvatar {
        constructor() {
            console.log('BotAvatar constructor called');

            this.isOpen = false;
            this.historyOpen = false;
            this.isRecording = false;
            this.audioMode = false;
            this.conversationHistory = [];
            this.recognition = null;
            this.audioElement = null;

            // Three.js variables
            this.scene = null;
            this.camera = null;
            this.renderer = null;
            this.model = null;
            this.mixer = null;
            this.animations = [];
            this.isAnimating = false;
            this.simpleRotation = false;

            // Inicializar
            this.init();
        }

        init() {
            console.log('Init called');
            $(document).ready(() => {
                console.log('DOM ready');
                this.setupElements();
                this.setupEventListeners();
                this.initThreeJS();
                this.initSpeechRecognition();
                this.loadSettings();
                console.log('Bot Avatar initialized successfully');
            });
        }

        setupElements() {
            console.log('Setting up elements...');

            this.$container = $('#bot-avatar-container');
            this.$toggle = $('#bot-avatar-toggle');
            this.$hologram = $('#bot-avatar-hologram');
            this.$closeBtn = $('#bot-close-btn');
            this.$messages = $('#bot-messages');
            this.$input = $('#bot-message-input');
            this.$sendBtn = $('#bot-send-btn');
            this.$voiceBtn = $('#bot-voice-btn');
            this.$modeSwitch = $('#bot-mode-switch');
            this.$historyToggle = $('#bot-history-toggle');
            this.$historyPanel = $('#bot-history-panel');
            this.$historyClose = $('#bot-history-close');

            console.log('Elements found:', {
                container: this.$container.length,
                toggle: this.$toggle.length,
                hologram: this.$hologram.length,
                messages: this.$messages.length,
                historyPanel: this.$historyPanel.length
            });

            // Canvas elements
            this.miniCanvas = document.getElementById('bot-avatar-mini-canvas');
            this.mainCanvas = document.getElementById('bot-avatar-canvas');

            console.log('Canvas elements:', {
                miniCanvas: !!this.miniCanvas,
                mainCanvas: !!this.mainCanvas
            });
        }

        setupEventListeners() {
            console.log('Setting up event listeners...');

            // Toggle chatbot
            this.$toggle.on('click', () => {
                console.log('Toggle button clicked!');
                this.toggleChat();
            });

            this.$closeBtn.on('click', () => {
                console.log('Close button clicked!');
                this.closeChat();
            });

            // Toggle historial
            this.$historyToggle.on('click', () => {
                this.toggleHistory();
            });

            this.$historyClose.on('click', () => {
                this.toggleHistory();
            });

            // Enviar mensaje
            this.$sendBtn.on('click', () => this.sendMessage());

            // Enter para enviar (Shift+Enter para nueva línea)
            this.$input.on('keydown', (e) => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    this.sendMessage();
                }
            });

            // Auto-resize textarea
            this.$input.on('input', () => this.autoResizeTextarea());

            // Toggle de modo de respuesta
            $('.bot-mode-option').on('click', (e) => {
                const mode = $(e.currentTarget).data('mode');
                this.setResponseMode(mode);
            });

            // Botón de voz (presionar y mantener)
            this.$voiceBtn.on('mousedown touchstart', (e) => {
                e.preventDefault();
                this.startRecording();
            });

            this.$voiceBtn.on('mouseup touchend mouseleave', (e) => {
                e.preventDefault();
                if (this.isRecording) {
                    this.stopRecording();
                }
            });

            // Click en el avatar para animar
            if (this.mainCanvas) {
                this.mainCanvas.addEventListener('click', () => {
                    this.playClickAnimation();
                });
                this.mainCanvas.style.cursor = 'pointer';
            }
        }

        loadSettings() {
            // Cargar modo por defecto
            if (botAvatarData.settings.defaultMode === 'audio') {
                this.setResponseMode('audio');
            }
        }

        toggleChat() {
            console.log('toggleChat called, current state:', this.isOpen);
            this.isOpen = !this.isOpen;

            if (this.isOpen) {
                console.log('Opening hologram');
                this.$hologram.removeClass('bot-hidden');
                this.$toggle.addClass('active');

                // Reconfigurar renderer cuando se abre
                if (this.renderer && this.mainCanvas) {
                    const width = this.mainCanvas.offsetWidth;
                    const height = this.mainCanvas.offsetHeight;

                    console.log('Reconfiguring renderer on open:', {width, height});

                    if (width > 0 && height > 0) {
                        this.renderer.setSize(width, height);

                        if (this.camera) {
                            this.camera.aspect = width / height;
                            this.camera.updateProjectionMatrix();
                        }

                        if (this.scene && this.camera) {
                            this.renderer.render(this.scene, this.camera);
                            console.log('Forced render on window open');
                        }
                    }
                }

                this.$input.focus();
            } else {
                this.closeChat();
            }
        }

        closeChat() {
            console.log('Closing hologram');
            this.isOpen = false;
            this.$hologram.addClass('bot-hidden');
            this.$toggle.removeClass('active');

            // Cerrar historial también si está abierto
            if (this.historyOpen) {
                this.$historyPanel.removeClass('bot-history-open');
                this.historyOpen = false;
            }
        }

        toggleHistory() {
            this.historyOpen = !this.historyOpen;
            this.$historyPanel.toggleClass('bot-history-open', this.historyOpen);
        }

        autoResizeTextarea() {
            const textarea = this.$input[0];
            textarea.style.height = 'auto';
            textarea.style.height = Math.min(textarea.scrollHeight, 120) + 'px';
        }

        setResponseMode(mode) {
            this.audioMode = (mode === 'audio');

            // Actualizar UI
            $('.bot-mode-option').removeClass('bot-mode-active');
            $(`.bot-mode-option[data-mode="${mode}"]`).addClass('bot-mode-active');

            this.$modeSwitch.attr('aria-checked', this.audioMode);
        }

        async sendMessage() {
            const message = this.$input.val().trim();

            if (!message) return;

            // Limpiar input
            this.$input.val('');
            this.autoResizeTextarea();

            // Agregar mensaje del usuario
            this.addMessage(message, 'user');

            // Agregar al historial
            this.conversationHistory.push({
                role: 'user',
                content: message
            });

            // Mostrar indicador de escritura
            this.showTypingIndicator();

            // Animar avatar (pensando)
            this.playAnimation('thinking');

            // Enviar a la API
            try {
                const response = await this.callChatAPI(message);

                // Remover indicador de escritura
                this.hideTypingIndicator();

                // Agregar respuesta del bot
                this.addMessage(response.message, 'assistant');

                // Agregar al historial
                this.conversationHistory.push({
                    role: 'assistant',
                    content: response.message
                });

                // Si está en modo audio, convertir a voz
                if (this.audioMode) {
                    await this.textToSpeech(response.message);
                } else {
                    // Modo texto: mostrar historial automáticamente
                    if (!this.historyOpen) {
                        this.toggleHistory();
                    }

                    // Solo animar hablando brevemente
                    this.playAnimation('talking');
                    setTimeout(() => {
                        this.stopAnimation();
                    }, 2000);
                }

            } catch (error) {
                this.hideTypingIndicator();
                this.showError(error.message || botAvatarData.strings.errorMessage);
                this.stopAnimation();
            }
        }

        addMessage(text, sender) {
            const isUser = sender === 'user';
            const time = new Date().toLocaleTimeString('es-ES', {
                hour: '2-digit',
                minute: '2-digit'
            });

            const $message = $(`
                <div class="bot-history-msg bot-msg-${sender === 'user' ? 'user' : 'assistant'}">
                    <div class="bot-msg-content">
                        ${this.escapeHtml(text)}
                    </div>
                    <div class="bot-msg-time">${time}</div>
                </div>
            `);

            this.$messages.append($message);
            this.scrollToBottom();
        }

        showTypingIndicator() {
            const $typing = $(`
                <div class="bot-history-msg bot-msg-assistant" id="bot-typing-indicator">
                    <div class="bot-msg-content">
                        <div class="bot-typing">
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>
                    </div>
                </div>
            `);

            this.$messages.append($typing);
            this.scrollToBottom();
        }

        hideTypingIndicator() {
            $('#bot-typing-indicator').remove();
        }

        showError(message) {
            const $error = $(`
                <div class="bot-history-msg bot-msg-assistant">
                    <div class="bot-msg-content" style="background: rgba(244, 135, 113, 0.2); border-color: rgba(244, 135, 113, 0.5);">
                        ${this.escapeHtml(message)}
                    </div>
                </div>
            `);
            this.$messages.append($error);
            this.scrollToBottom();
        }

        scrollToBottom() {
            this.$messages.animate({
                scrollTop: this.$messages[0].scrollHeight
            }, 300);
        }

        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        async callChatAPI(message) {
            return new Promise((resolve, reject) => {
                $.ajax({
                    url: botAvatarData.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'bot_avatar_chat',
                        nonce: botAvatarData.nonce,
                        message: message,
                        history: JSON.stringify(this.conversationHistory)
                    },
                    success: function(response) {
                        if (response.success) {
                            resolve(response.data);
                        } else {
                            reject(new Error(response.data.message));
                        }
                    },
                    error: function(xhr, status, error) {
                        reject(new Error(botAvatarData.strings.errorMessage));
                    }
                });
            });
        }

        // ========== FUNCIONES DE VOZ ==========

        initSpeechRecognition() {
            if (!('webkitSpeechRecognition' in window) && !('SpeechRecognition' in window)) {
                console.warn('Speech recognition not supported');
                this.$voiceBtn.prop('disabled', true);
                return;
            }

            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            this.recognition = new SpeechRecognition();
            this.recognition.continuous = false;
            this.recognition.interimResults = false;
            this.recognition.lang = botAvatarData.settings.language || 'es-ES';

            this.recognition.onresult = (event) => {
                const transcript = event.results[0][0].transcript;
                this.$input.val(transcript);
                this.sendMessage();
            };

            this.recognition.onerror = (event) => {
                console.error('Speech recognition error:', event.error);
                this.isRecording = false;
                this.$voiceBtn.removeClass('recording');
            };

            this.recognition.onend = () => {
                this.isRecording = false;
                this.$voiceBtn.removeClass('recording');
            };
        }

        startRecording() {
            if (!this.recognition || this.isRecording) return;

            this.isRecording = true;
            this.$voiceBtn.addClass('recording');

            try {
                this.recognition.start();
            } catch (error) {
                console.error('Error starting recognition:', error);
                this.isRecording = false;
                this.$voiceBtn.removeClass('recording');
            }
        }

        stopRecording() {
            if (!this.recognition || !this.isRecording) return;

            try {
                this.recognition.stop();
            } catch (error) {
                console.error('Error stopping recognition:', error);
            }
        }

        async textToSpeech(text) {
            // Animar avatar hablando
            this.playAnimation('talking');

            try {
                const response = await $.ajax({
                    url: botAvatarData.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'bot_avatar_text_to_speech',
                        nonce: botAvatarData.nonce,
                        text: text
                    }
                });

                if (response.success && response.data.audio) {
                    const audioData = 'data:audio/mp3;base64,' + response.data.audio;

                    if (this.audioElement) {
                        this.audioElement.pause();
                        this.audioElement = null;
                    }

                    this.audioElement = new Audio(audioData);

                    this.audioElement.onended = () => {
                        console.log('Audio finished, stopping animation');
                        this.stopAnimation();
                    };

                    await this.audioElement.play();
                } else {
                    this.stopAnimation();
                }
            } catch (error) {
                console.error('TTS error:', error);
                this.stopAnimation();
            }
        }

        // ========== THREE.JS FUNCTIONS ==========

        initThreeJS() {
            console.log('Initializing Three.js...');

            if (typeof THREE === 'undefined') {
                console.error('THREE is not loaded!');
                return;
            }

            console.log('THREE.js version:', THREE.REVISION);

            this.setupMainScene();
            this.loadModel();
            this.animate();
        }

        setupMainScene() {
            console.log('Setting up main scene...');

            if (!this.mainCanvas) {
                console.error('Main canvas not found!');
                return;
            }

            // Escena
            this.scene = new THREE.Scene();

            // Dimensiones
            const width = this.mainCanvas.offsetWidth || 400;
            const height = this.mainCanvas.offsetHeight || 400;

            // Cámara (más alejada para modelos más grandes)
            this.camera = new THREE.PerspectiveCamera(50, width / height, 0.1, 1000);
            this.camera.position.set(0, 0, 8);
            this.camera.lookAt(0, 0, 0);  // Mirar al centro donde estará el modelo

            // Renderer
            this.renderer = new THREE.WebGLRenderer({
                canvas: this.mainCanvas,
                alpha: true,
                antialias: true
            });
            this.renderer.setSize(width, height);
            this.renderer.setPixelRatio(window.devicePixelRatio);
            this.renderer.outputEncoding = THREE.sRGBEncoding;

            // Luces normales
            const ambientLight = new THREE.AmbientLight(0xffffff, 0.8);
            this.scene.add(ambientLight);

            const directionalLight = new THREE.DirectionalLight(0xffffff, 0.6);
            directionalLight.position.set(5, 5, 5);
            this.scene.add(directionalLight);

            const fillLight = new THREE.DirectionalLight(0x8b5cf6, 0.3);
            fillLight.position.set(-5, 0, -5);
            this.scene.add(fillLight);

            // Handle resize
            window.addEventListener('resize', () => this.onWindowResize());

            console.log('Main scene setup complete');
        }

        loadModel() {
            if (typeof THREE === 'undefined' || typeof THREE.GLTFLoader === 'undefined') {
                console.error('GLTFLoader not available');
                this.createFallbackAvatar();
                return;
            }

            const loader = new THREE.GLTFLoader();
            console.log('Loading model from:', botAvatarData.modelUrl);

            loader.load(
                botAvatarData.modelUrl,
                (gltf) => {
                    console.log('Model loaded successfully!', gltf);
                    this.model = gltf.scene;

                    // Calcular tamaño y escalar
                    const box = new THREE.Box3().setFromObject(gltf.scene);
                    const size = box.getSize(new THREE.Vector3());
                    const center = box.getCenter(new THREE.Vector3());

                    console.log('Original model size:', {
                        x: size.x.toFixed(2),
                        y: size.y.toFixed(2),
                        z: size.z.toFixed(2)
                    });
                    console.log('Model center:', {
                        x: center.x.toFixed(2),
                        y: center.y.toFixed(2),
                        z: center.z.toFixed(2)
                    });
                    console.log('Bounding box:', {
                        min: {x: box.min.x.toFixed(2), y: box.min.y.toFixed(2), z: box.min.z.toFixed(2)},
                        max: {x: box.max.x.toFixed(2), y: box.max.y.toFixed(2), z: box.max.z.toFixed(2)}
                    });

                    // Usar el tamaño configurado en los ajustes
                    const targetHeight = botAvatarData.settings.modelScale || 1.5;
                    const scale = targetHeight / size.y;

                    console.log('Target height from settings:', targetHeight, 'Calculated scale:', scale.toFixed(4));

                    this.model.scale.set(scale, scale, scale);

                    // Obtener ajuste de posición vertical desde settings
                    const positionYOffset = botAvatarData.settings.modelPositionY || 0;

                    // Centrar el modelo completamente (X, Z) para ignorar espacio vacío del GLB
                    // Y aplicar el offset vertical configurable
                    this.model.position.set(
                        -center.x * scale,
                        -center.y * scale + positionYOffset,
                        -center.z * scale
                    );

                    // La cámara siempre mira al centro (0, 0, 0), solo el modelo se mueve verticalmente
                    console.log('Model moved vertically with offset:', positionYOffset);

                    console.log('Final model position (centered + offset):', {
                        x: this.model.position.x.toFixed(2),
                        y: this.model.position.y.toFixed(2),
                        z: this.model.position.z.toFixed(2),
                        offset: positionYOffset
                    });
                    console.log('Final model scale:', {
                        x: this.model.scale.x.toFixed(2),
                        y: this.model.scale.y.toFixed(2),
                        z: this.model.scale.z.toFixed(2)
                    });

                    this.scene.add(this.model);
                    console.log('Model added to scene. Scene children:', this.scene.children.length);

                    // Configurar animaciones
                    if (gltf.animations && gltf.animations.length > 0) {
                        this.mixer = new THREE.AnimationMixer(this.model);
                        this.animations = gltf.animations;
                        console.log('Animations found:', gltf.animations.length);
                        gltf.animations.forEach((anim, i) => {
                            console.log(`  Animation ${i}: ${anim.name}, duration: ${anim.duration.toFixed(2)}s`);
                        });
                    } else {
                        console.log('No animations found in model');
                    }

                    // Render inicial
                    if (this.renderer && this.scene && this.camera) {
                        this.renderer.render(this.scene, this.camera);
                        console.log('Initial render executed');
                    }
                },
                (progress) => {
                    if (progress.total > 0) {
                        console.log('Loading:', (progress.loaded / progress.total * 100).toFixed(2) + '%');
                    }
                },
                (error) => {
                    console.error('Error loading model:', error);
                    this.createFallbackAvatar();
                }
            );
        }

        createFallbackAvatar() {
            console.log('Creating fallback avatar');

            const geometry = new THREE.SphereGeometry(0.5, 32, 32);
            const material = new THREE.MeshStandardMaterial({
                color: 0x6366f1,
                metalness: 0.5,
                roughness: 0.5
            });

            this.model = new THREE.Mesh(geometry, material);
            this.model.position.set(0, 1, 0);

            this.scene.add(this.model);
            this.addSimpleRotation();
        }

        addSimpleRotation() {
            this.simpleRotation = true;
            this.isAnimating = true;
        }

        playAnimation(type) {
            if (!this.mixer || !this.animations.length) {
                return;
            }

            this.mixer.stopAllAction();

            if (type === 'stop' || type === 'idle') {
                this.isAnimating = false;
                return;
            }

            const animationMap = {
                'talking': 0,
                'thinking': 0
            };

            const animIndex = animationMap[type] || 0;

            if (this.animations[animIndex]) {
                this.isAnimating = true;
                const action = this.mixer.clipAction(this.animations[animIndex]);
                action.setLoop(THREE.LoopRepeat);
                action.play();
                console.log('Playing animation:', type);
            }
        }

        stopAnimation() {
            if (this.mixer) {
                this.mixer.stopAllAction();
                this.isAnimating = false;
            }
            this.simpleRotation = false;
        }

        playClickAnimation() {
            console.log('Avatar clicked!');

            // Si no hay mixer o animaciones, solo rotar
            if (!this.mixer || !this.animations.length) {
                console.log('No animations available, rotating model');
                if (this.model) {
                    // Hacer una rotación rápida
                    this.isAnimating = true;
                    this.simpleRotation = true;
                    setTimeout(() => {
                        this.simpleRotation = false;
                        this.isAnimating = false;
                    }, 2000);
                }
                return;
            }

            // Detener animación actual
            this.mixer.stopAllAction();

            // Elegir animación: segunda si existe, sino la primera
            const animIndex = this.animations.length > 1 ? 1 : 0;

            if (this.animations[animIndex]) {
                this.isAnimating = true;
                const action = this.mixer.clipAction(this.animations[animIndex]);
                action.setLoop(THREE.LoopOnce); // Solo una vez
                action.clampWhenFinished = true; // Mantener último frame
                action.reset();
                action.play();

                console.log(`Playing animation ${animIndex + 1} of ${this.animations.length}`);

                // Detener después de que termine la animación
                const duration = this.animations[animIndex].duration * 1000; // convertir a ms
                setTimeout(() => {
                    this.stopAnimation();
                    console.log('Click animation finished');
                }, duration);
            }
        }

        animate() {
            requestAnimationFrame(() => this.animate());

            // Actualizar mixer
            if (this.mixer && this.isAnimating) {
                this.mixer.update(0.016);
            }

            // Rotación simple para fallback
            if (this.simpleRotation && this.model && this.isAnimating) {
                this.model.rotation.y += 0.005;
            }

            // Renderizar
            if (this.renderer && this.scene && this.camera) {
                this.renderer.render(this.scene, this.camera);
            }
        }

        onWindowResize() {
            if (!this.camera || !this.renderer || !this.mainCanvas) return;

            const width = this.mainCanvas.offsetWidth;
            const height = this.mainCanvas.offsetHeight;

            this.camera.aspect = width / height;
            this.camera.updateProjectionMatrix();

            this.renderer.setSize(width, height);
        }
    }

    // Inicializar el chatbot
    try {
        console.log('Creating BotAvatar instance...');
        window.BotAvatar = new BotAvatar();
        console.log('BotAvatar instance created successfully');
    } catch (error) {
        console.error('Error creating BotAvatar instance:', error);
    }

})(window.jQuery || window.$ || function() {
    console.error('jQuery is not loaded! Bot Avatar requires jQuery.');
});
